<?php

include_once( 'config.php' );

class CalendarPublicViewDay{

    function rc_calset_form($post_id){
        global $wpdb;

        // post_idを整数に変換（SQLインジェクション対策）
        $post_id = intval($post_id);

        $date = $ym = date('Y-m') . '-' . str_pad(date('j'), 2, 0, STR_PAD_LEFT);

        /*
        status取得
        ---------------------------------------------- */
        $searchtxt = 'rc_status_'.$date;
        $sql_status = $wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            $searchtxt
        );
        $rc_status = $wpdb->get_results($sql_status , ARRAY_A);

        $rc_today_status = isset($rc_status[0]['meta_value']) ? $rc_status[0]['meta_value'] : '';

        /*
        status設定取得
        ---------------------------------------------- */
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name , ARRAY_A);

        $rc_status_flg = $setting_records[array_search($rc_today_status, array_column($setting_records, 'state_name'))];

        // XSS対策: 出力をエスケープ
        $state_mark = isset($rc_status_flg['state_mark']) ? esc_attr($rc_status_flg['state_mark']) : '';
        $state_name = isset($rc_status_flg['state_name']) ? esc_html($rc_status_flg['state_name']) : '';
        $state_txt = isset($rc_status_flg['state_txt']) ? esc_html($rc_status_flg['state_txt']) : '';

        echo '<div class="rc-cal-day__mark g-today__status rc-cal-day__mark--'.$state_mark.'">'.$state_name.'</div>';
        echo '<div class="rc-cal-day__txt">'.$state_txt.'</div>';
        
    }

}
