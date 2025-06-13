<?php

include_once( 'config.php' );

class CalendarPostView{

    function __construct(){
        add_action('admin_menu', array($this,'rc_create_custom_fields'));
        add_action('save_post', array($this,'save_custom_fields'));
    }
    
    function rc_create_custom_fields(){

        add_meta_box(
            'rc_calset',
            '休日・イベント設定',
            array($this,'rc_calset_form'),
            RC_Config::NAME,
            'normal',
            'default',
        );
    }
    
    function rc_calset_form($post){

        wp_nonce_field('custom_field_save_meta_box_data', 'custom_field_meta_box_nonce');

        global $wpdb;

        // rc_statusをphpの配列からjsの配列へ
        $sql_status = "SELECT * FROM $wpdb->postmeta WHERE post_id =".$post->ID." AND meta_key LIKE 'rc_status_%'";
        $rc_status = $wpdb->get_results($sql_status,OBJECT);
        $rc_status_array = json_encode($rc_status,JSON_PRETTY_PRINT);
        echo '<script>var rc_status_array = '.$rc_status_array.'</script>';

        // rc_eventsをphpの配列からjsの配列へ
        $sql_events = "SELECT * FROM $wpdb->postmeta WHERE post_id =".$post->ID." AND meta_key LIKE 'rc_events_%'";
        $rc_events = $wpdb->get_results($sql_events, OBJECT);
        // シングルクウォートのエスケープを取り除く
        foreach ($rc_events as &$event) {
            $event->meta_value = str_replace("\\'", "&#39;", $event->meta_value);
            $event->meta_value = str_replace('\\"', "&quot;", $event->meta_value);
        }
        $rc_events_array = json_encode($rc_events, JSON_PRETTY_PRINT);
        echo '<script>var rc_events_array = '.$rc_events_array.'</script>';


        // 投稿タイプ設定を取得
        $option_table = $wpdb->prefix . RC_Config::OPTION_TABLE;
        $post_type_row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$option_table} WHERE option_name = %s", 'rc_enabled_post_types')
        );
        $enabled_post_types = $post_type_row ? explode(',', $post_type_row->option_value) : ['events'];

        $placeholders = implode(',', array_fill(0, count($enabled_post_types), '%s'));
        $sql_events_posts = $wpdb->prepare(
            "SELECT * FROM $wpdb->posts WHERE post_type IN ($placeholders) AND post_status = 'publish'",
            ...$enabled_post_types
        );
        $rc_events_posts = $wpdb->get_results($sql_events_posts, OBJECT);
        $rc_events_posts_array = json_encode($rc_events_posts, JSON_PRETTY_PRINT);
        echo '<script>var rc_events_posts_array = '.$rc_events_posts_array.'</script>';

        // setting
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name);
        $setting_records_array = json_encode($setting_records,JSON_PRETTY_PRINT);
        echo '<script>var setting_records_array = '.$setting_records_array.'</script>';

        $month  = '2';
        $year   = '2022';

        if($month == ''){
            $this_month = date("m");
        }else{
            $this_month = $month;
        }

        if($year == ''){
            $this_year  = date("Y");
        }else{
            $this_year = $year;
        }

        // その月の日数
        $this_day = date('t');

        ?>
            <div name="cal_container">
                <div class="rc-all__controller">
                    <div>
                        <label><input type="checkbox" name="calAllChangeCheckbox" value="allCheckFlg">すべて選択/解除</label>
                    </div>
                    <div>
                        <select name="allChangeSelect">
                            <option value="">--</option>
                        <?php 
                            foreach($setting_records as $key => $value):
                        ?>
                            <option value="<?php echo $setting_records[$key]->state_name;?>">
                            <?php echo $setting_records[$key]->state_name; ?>
                            </option>
                        <?php
                            endforeach;
                        ?>
                        </select>
                    </div>
                    <div>
                        <a class="rc-all__controller__btn" name="allChangeBtn">一括変更</a>
                    </div>
                </div>
                <div class="rc-dayweek__controller">
                    <div class="weekday-toggles-global">
                        <?php
                        $weekdays = array('日', '月', '火', '水', '木', '金', '土');
                        foreach ($weekdays as $i => $day) {
                            echo '<label><input type="checkbox" class="weekday-toggle-global" data-weekday="'.$i.'">'.$day.'</label> ';
                        }
                        ?>
                    </div>
                </div>   
                <div class="rc-WeekdayChangeCheckbox" value=""></div>
                <div name="cal_box">
                </div>
            </div>
        <?php
    }


    /* 
    データ保存
    ---------------------------------------------- */
    function save_custom_fields($post_id){

        if (!isset($_POST['custom_field_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['custom_field_meta_box_nonce'], 'custom_field_save_meta_box_data')) {
            return;
        }

        foreach($_POST as $key => $value){
            if(preg_match('/rc_events_/', $key)){
                $data = json_encode($_POST[$key], JSON_UNESCAPED_UNICODE);
                update_post_meta($post_id, $key , $data);
            }elseif(preg_match('/rc_status_/', $key)){
                $data = sanitize_text_field($_POST[$key]);

                if(get_post_meta($post_id, $key)){
                    delete_post_meta($post_id, $key);
                }
                
                update_post_meta($post_id, $key , $data);
            }
        }

    }

}
