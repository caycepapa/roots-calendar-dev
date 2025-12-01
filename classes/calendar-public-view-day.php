<?php

include_once( 'config.php' );

class CalendarPublicViewDay{

    /**
     * グローバルデフォルトステータスを取得
     */
    static function get_global_default_status(){
        global $wpdb;
        $option_table_name = $wpdb->prefix . RC_Config::OPTION_TABLE;
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT option_value FROM $option_table_name WHERE option_name = %s",
            'デフォルトステータス'
        ));
        return $result ? $result->option_value : '';
    }

    function rc_calset_form($post_id){
        global $wpdb;

        // post_idを整数に変換（SQLインジェクション対策）
        $post_id = intval($post_id);

        $date = date('Y-m') . '-' . str_pad(date('j'), 2, 0, STR_PAD_LEFT);

        /*
        グローバルデフォルトステータス取得
        ---------------------------------------------- */
        $default_status = self::get_global_default_status();

        /*
        例外status取得
        ---------------------------------------------- */
        $searchtxt = 'rc_status_'.$date;
        $sql_status = $wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            $searchtxt
        );
        $rc_status = $wpdb->get_results($sql_status , ARRAY_A);

        // まず例外登録をチェック、なければグローバルデフォルトを使用
        $rc_today_status = '';
        if(isset($rc_status[0]['meta_value']) && $rc_status[0]['meta_value'] !== ''){
            $rc_today_status = $rc_status[0]['meta_value'];
        }elseif(!empty($default_status)){
            $rc_today_status = $default_status;
        }

        /*
        status設定取得
        ---------------------------------------------- */
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name , ARRAY_A);

        $rc_status_flg = array();
        if(!empty($rc_today_status)){
            $status_index = array_search($rc_today_status, array_column($setting_records, 'state_name'));
            if($status_index !== false){
                $rc_status_flg = $setting_records[$status_index];
            }
        }

        // XSS対策: 出力をエスケープ
        $state_mark = isset($rc_status_flg['state_mark']) ? esc_attr($rc_status_flg['state_mark']) : '';
        $state_name = isset($rc_status_flg['state_name']) ? esc_html($rc_status_flg['state_name']) : '';
        $state_txt = isset($rc_status_flg['state_txt']) ? esc_html($rc_status_flg['state_txt']) : '';

        echo '<div class="rc-cal-day__mark g-today__status rc-cal-day__mark--'.$state_mark.'">'.$state_name.'</div>';
        echo '<div class="rc-cal-day__txt">'.$state_txt.'</div>';

    }

}
