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

        // post_idを整数に変換（SQLインジェクション対策）
        $post_id = intval($post->ID);

        // rc_statusをphpの配列からjsの配列へ
        $sql_status = $wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            'rc_status_%'
        );
        $rc_status = $wpdb->get_results($sql_status,OBJECT);
        // XSS対策: JSON出力を安全にエンコード
        echo '<script>var rc_status_array = ' . wp_json_encode($rc_status) . ';</script>';

        // rc_eventsをphpの配列からjsの配列へ
        $sql_events = $wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            'rc_events_%'
        );
        $rc_events = $wpdb->get_results($sql_events, OBJECT);
        // シングルクウォートのエスケープを取り除く
        foreach ($rc_events as &$event) {
            $event->meta_value = str_replace("\\'", "&#39;", $event->meta_value);
            $event->meta_value = str_replace('\\"', "&quot;", $event->meta_value);
        }
        // XSS対策: JSON出力を安全にエンコード
        echo '<script>var rc_events_array = ' . wp_json_encode($rc_events) . ';</script>';


        // カスタム投稿 eventsを取得し、jsの配列へ
        $sql_events_posts = "SELECT * FROM $wpdb->posts WHERE post_type = 'events' AND post_status = 'publish'";
        $rc_events_posts = $wpdb->get_results($sql_events_posts, OBJECT);
        // XSS対策: JSON出力を安全にエンコード
        echo '<script>var rc_events_posts_array = ' . wp_json_encode($rc_events_posts) . ';</script>';

        // setting
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name);
        // XSS対策: JSON出力を安全にエンコード
        echo '<script>var setting_records_array = ' . wp_json_encode($setting_records) . ';</script>';

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
                        <?php
                            foreach($setting_records as $key => $value):
                        ?>
                            <option value="<?php echo esc_attr($setting_records[$key]->state_name);?>">
                            <?php echo esc_html($setting_records[$key]->state_name); ?>
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
            // キー名をサニタイズ
            $key = sanitize_key($key);

            if(preg_match('/^rc_events_/', $key)){
                // イベントデータをサニタイズ
                $events = array_map(function($event){
                    return array(
                        'event_name' => isset($event['event_name']) ? sanitize_text_field($event['event_name']) : '',
                        'event_url' => isset($event['event_url']) ? esc_url_raw($event['event_url']) : '',
                        'event_color' => isset($event['event_color']) ? sanitize_hex_color($event['event_color']) : '#ffffff',
                        'event_type' => isset($event['event_type']) ? sanitize_text_field($event['event_type']) : '',
                        'event_id' => isset($event['event_id']) ? intval($event['event_id']) : 0,
                    );
                }, (array)$_POST[$key]);
                $data = wp_json_encode($events, JSON_UNESCAPED_UNICODE);
                update_post_meta($post_id, $key , $data);
            }elseif(preg_match('/^rc_status_/', $key)){
                $data = sanitize_text_field($_POST[$key]);

                if(get_post_meta($post_id, $key)){
                    delete_post_meta($post_id, $key);
                }

                update_post_meta($post_id, $key , $data);
            }
        }

    }

}
