<?php

include_once( 'config.php' );

class CalendarPublicViewList{

    function rc_calset_form($post_id){
        global $wpdb;

        /* 
        status取得
        ---------------------------------------------- */
        $sql_status = "SELECT * FROM $wpdb->postmeta WHERE post_id =".$post_id." AND meta_key LIKE 'rc_status_%'";
        $rc_status = $wpdb->get_results($sql_status , ARRAY_A);

        /* 
        events取得
        ---------------------------------------------- */
        $sql_events = "SELECT * FROM $wpdb->postmeta WHERE post_id =".$post_id." AND meta_key LIKE 'rc_events_%'";
        $rc_events = $wpdb->get_results($sql_events , ARRAY_A);

        /* 
        status設定取得
        ---------------------------------------------- */
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name , ARRAY_A);

        $today_num      = date('j');
        $today_year     = date('Y');
        $today_month    = date('m');

        for ( $day = $today_num; $day <= $today_num + 10; $day++) {

            $date = $today_year.'-'.$today_month. '-' . str_pad($day, 2, 0, STR_PAD_LEFT);

            $balloonArray   = $this->create_balloon($date, $rc_events);

            $rc_date = $rc_status[array_search('rc_status_'.$date, array_column($rc_status, 'meta_key'))];

            $rc_status_flg = $setting_records[array_search($rc_date['meta_value'], array_column($setting_records, 'state_name'))];

            echo '<li>';

            if($balloonArray){
                echo $balloonArray['rc_eve_balloon'];
            }else{
                echo $rc_status_flg['state_txt'];
            }

            echo '</li>';
        }

    }

    function create_balloon($date,$rc_events){

        $event_num = array_search('rc_events_'.$date, array_column($rc_events, 'meta_key'));

        if(is_int($event_num)){
            $rc_eve = $rc_events[$event_num];
            $rc_eve_array = json_decode($rc_eve['meta_value'],true);
            $rc_eve_balloon = '';

            for($i = 0; $i < count($rc_eve_array); $i++){
                if($rc_eve_array[$i]['event_name'] !== ''){
                    $rc_eve_balloon .= '<a href="'.$rc_eve_array[$i]['event_url'].'">';
                    $rc_eve_balloon .= $rc_eve_array[$i]['event_name'].'</a>';
                    $rc_eve_btnclass = 'rc_cal_btn--hasevent';
                    $bg_color = $rc_eve_array[$i]['event_color'];
                }
            }

            $balloonArray = array(
                'rc_eve_btnclass' => $rc_eve_btnclass,
                'rc_eve_balloon' => $rc_eve_balloon,
                'bg_color' => $bg_color
            );

            return $balloonArray;

        }else{
            return false;
        }
    }

}
