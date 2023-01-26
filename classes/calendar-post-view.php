<?php

include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );

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
        $rc_status_array = json_encode($rc_status);
        echo '<script>var rc_status_array = '.$rc_status_array.'</script>';

        // rc_eventsをphpの配列からjsの配列へ
        $sql_events = "SELECT * FROM $wpdb->postmeta WHERE post_id =".$post->ID." AND meta_key LIKE 'rc_events_%'";
        $rc_events = $wpdb->get_results($sql_events,OBJECT);
        $rc_events_array = json_encode($rc_events);
        echo '<script>var rc_events_array = '.$rc_events_array.'</script>';

        // setting
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name);
        $setting_records_array = json_encode($setting_records);
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
        $this_day = date('t', strtotime($date_str));

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
                update_post_meta($post_id, $key , $data);
            }
        }

    }

}
