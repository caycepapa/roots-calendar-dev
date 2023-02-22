<?php

include_once( 'config.php' );

class CalendarPublicViewDay{

    function rc_calset_form($post_id){
        global $wpdb;

        $date = $ym = date('Y-m') . '-' . str_pad(date('j'), 2, 0, STR_PAD_LEFT);

        /* 
        status取得
        ---------------------------------------------- */
        $searchtxt = 'rc_status_'.$date;
        $sql_status = "SELECT * FROM $wpdb->postmeta WHERE post_id =".$post_id." AND meta_key LIKE '".$searchtxt."'";
        $rc_status = $wpdb->get_results($sql_status , ARRAY_A);

        $rc_today_status = $rc_status[0]['meta_value'];

        /* 
        status設定取得
        ---------------------------------------------- */
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name , ARRAY_A);

        $rc_status_flg = $setting_records[array_search($rc_today_status, array_column($setting_records, 'state_name'))];

        echo '<div class="rc-cal-day">';
        echo '<div class="rc-cal-day__ttl">'.get_the_title( $post_id ).'</div>';
        echo '<div class="rc-cal-day__mark rc-cal-day__mark--'.$rc_status_flg['state_mark'].'">'.$rc_status_flg['state_mark'].'</div>';
        echo '<div class="rc-cal-day__txt">'.$rc_status_flg['state_txt'].'</div>';
        echo '</div>';
        
    }

}
