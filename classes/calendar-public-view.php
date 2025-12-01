<?php

include_once( 'config.php' );

class CalendarPublicView{

    function rc_calset_form($post_id){
        global $wpdb;
        /* 
        option取得
        ---------------------------------------------- */
        $option_table_name = $wpdb->prefix . RC_Config::OPTION_TABLE;
        $option_records = $wpdb->get_results("SELECT * FROM ".$option_table_name);
        $howlong = $option_records[array_search('公開月数', array_column($option_records, 'option_name'))];
        $howlong_num = $howlong->option_value;

        /* 
        カレンダー生成
        ---------------------------------------------- */
        $today_num      = date('j');
        $today_year     = date('Y');
        $today_month    = date('m');
        $montha = $today_month + 0;

        /* 
        テーブル作成
        ---------------------------------------------- */
        if($howlong_num == 1){
            $lastBlank = 'is-blank';
        }else{
            $lastBlank = '';
        }

        echo '<div class="rc-calendar__wrap" name="rcCalendarWrap">';
        echo '<div class="rc-calendar__header">';
        echo '<a class="rc-calendar__btn is-blank" name="rcPrevBtn">&lt;前の月</a>';
        echo '<h3 class="mb-5" name="rcCalendarMonthTtl"><span class="year">'.$today_year.'</span><span class="month">'.$montha.'</span></h3>';
        echo '<a class="rc-calendar__btn '. $lastBlank .'" name="rcNextBtn">次の月&gt;</a>';
        echo '</div>';

        for($i = 0; $i < $howlong_num; $i++){
            $montha = $today_month + 0;
            $weeks = $this->create_calendar($post_id,$today_year,$today_month);

            ?>
                <table class="rc-calendar__table <?php echo $i == 0 ? 'rc-calendar__table--first is-current': '';?> <?php echo $i == $howlong_num - 1 ? 'rc-calendar__table--last': '';?>">
                    <tr>
                        <th>日</th>
                        <th>月</th>
                        <th>火</th>
                        <th>水</th>
                        <th>木</th>
                        <th>金</th>
                        <th>土</th>
                    </tr>
                    <?php
                        foreach ($weeks as $week) {
                            echo $week;
                        }
                    ?>
                </table>
                <div class="rc-calendar__label" name="rcCalendarLabel"><span class="year"><?php echo $today_year;?></span><span class="month"><?php echo $montha;?></span></div>
            <?php

            if($today_month >= 12){
                $today_month = str_pad(1, 2, 0, STR_PAD_LEFT);
                $today_year = $today_year + 1;
            }else{
                $today_month = $today_month + 1;
                $today_month = str_pad($today_month, 2, 0, STR_PAD_LEFT);
            }
        }

        echo '</div>';
    }


    function balloon_checker($data){
        for($i = 0; $i < count($data); $i++){
            if($data[$i]['event_name'] !== ''){
                return true;
            }
        }
    }


    function create_balloon($date,$rc_events){

        $event_num = array_search('rc_events_'.$date, array_column($rc_events, 'meta_key'));
        $rc_eve_balloon = '';
        $rc_eve_btnclass = '';
        $bg_color = '';
        $hasValidEvent = false;

        if(is_int($event_num)){
            $rc_eve = $rc_events[$event_num];
            $rc_eve = str_replace("\'", "&#39;", $rc_eve);
            $rc_eve = str_replace('\"', "&quot;", $rc_eve);
            $rc_eve_array = json_decode($rc_eve['meta_value'],true);

            if($this->balloon_checker($rc_eve_array)){
                $rc_eve_balloon .= '<div class="rc_cal_balloon">';
                for($i = 0; $i < count($rc_eve_array); $i++){
                    if($rc_eve_array[$i]['event_name'] !== ''){
                        $hasValidEvent = true;

                        if($rc_eve_array[$i]['event_type'] == 'url'){
                            if($rc_eve_array[$i]['event_url'] !== ''){
                                $rc_eve_balloon .= '<a href="'.esc_url($rc_eve_array[$i]['event_url']).'">';
                                $rc_eve_balloon .= esc_html($rc_eve_array[$i]['event_name']).'</a>';
                            }else{
                                $rc_eve_balloon .= '<span>';
                                $rc_eve_balloon .= esc_html($rc_eve_array[$i]['event_name']).'</span>';
                            }
                        }else{
                            if($rc_eve_array[$i]['event_id'] !== ''){
                                $rc_eve_balloon .= '<a href="'.esc_url(get_permalink(intval($rc_eve_array[$i]['event_id']))).'">';
                                $rc_eve_balloon .= esc_html($rc_eve_array[$i]['event_name']).'</a>';
                            }
                        }
                        
                        $rc_eve_btnclass = 'rc_cal_btn--hasevent';
                        $bg_color = $rc_eve_array[$i]['event_color'];
                    }
                }
                $rc_eve_balloon .= '</div>';
            }

            $balloonArray = array(
                'rc_eve_btnclass' => $rc_eve_btnclass,
                'rc_eve_balloon' => $rc_eve_balloon,
                'bg_color' => $bg_color,
                'rc_eve_flg' => $hasValidEvent
            );

            return $balloonArray;

        }else{
            return false;
        }
    }


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

    function create_calendar($post_id,$year,$month){
        global $wpdb;

        // post_idを整数に変換（SQLインジェクション対策）
        $post_id = intval($post_id);

        /*
        グローバルデフォルトステータス取得
        ---------------------------------------------- */
        $default_status = self::get_global_default_status();

        /*
        例外status取得（個別登録分のみ）
        ---------------------------------------------- */
        $sql_status = $wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            'rc_status_%'
        );
        $rc_status = $wpdb->get_results($sql_status , ARRAY_A);

        /*
        events取得
        ---------------------------------------------- */
        $sql_events = $wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            'rc_events_%'
        );
        $rc_events = $wpdb->get_results($sql_events , ARRAY_A);

        /*
        status設定取得
        ---------------------------------------------- */
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name , ARRAY_A);

        $youbi = date('w', mktime(0, 0, 0, $month, 1, $year));
        $day_count = date('t', strtotime($year.'-'.$month.'-01'));

        /*
        カレンダー生成
        ---------------------------------------------- */

        $weeks              = [];
        $week               = '';

        $week .= str_repeat('<td></td>', $youbi);

        for ( $day = 1; $day <= $day_count; $day++, $youbi++) {

            $bg_color           = '';
            $rc_eve_btnclass    = '';
            $rc_eve_balloon     = '';

            $date = $year.'-'.$month. '-' . str_pad($day, 2, 0, STR_PAD_LEFT);

            // まず例外登録をチェック
            $exception_index = array_search('rc_status_'.$date, array_column($rc_status, 'meta_key'));
            $status_value = '';

            if($exception_index !== false){
                // 例外登録がある場合はそちらを優先
                $status_value = $rc_status[$exception_index]['meta_value'];
            }elseif(!empty($default_status)){
                // グローバルデフォルト設定を使用
                $status_value = $default_status;
            }

            // ステータスの色を取得
            if(!empty($status_value)){
                $status_index = array_search($status_value, array_column($setting_records, 'state_name'));
                if($status_index !== false){
                    $bg_color = $setting_records[$status_index]['state_color'];
                }else{
                    $bg_color = "#FFF";
                }
            }else{
                $bg_color = "#FFF";
            }

            $balloonArray   = $this->create_balloon($date, $rc_events);
            
            $today_num      = date('j');

            if($day < $today_num && $month == date('m')){
                $week .= '<td class="rc_cal_day rc_cal_day--end"><div class="rc_cal_day_wrap"><p>' . $day . '</p></div>';
            }else{
                $today_flg = date('Y-m-d') == $date ? 'rc_cal_today ' : '';

                if (is_array($balloonArray) && isset($balloonArray['rc_eve_flg']) && $balloonArray['rc_eve_flg'] == true) {
                    $rc_eve_btnclass = $balloonArray['rc_eve_btnclass'];
                    $rc_eve_balloon  = $balloonArray['rc_eve_balloon'];
                    $bg_color        = $balloonArray['bg_color'];
                } else {
                    $rc_eve_btnclass = '';
                    $rc_eve_balloon  = '';
                }
				
				$week .= '<td class="rc_cal_day '. esc_attr($today_flg . $rc_eve_btnclass) .'" data="'.esc_attr($date).'" style="background-color:'.esc_attr($bg_color).'"><div class="rc_cal_day_wrap"><p>' . esc_html($day) . '</p>' . $rc_eve_balloon . '</div>';
                
            }
            $week .= '</td>';

            if ($youbi % 7 == 6 || $day == $day_count) {
                if ($day == $day_count) {
                    $week .= str_repeat('<td></td>', 6 - $youbi % 7);
                }
                $weeks[] = '<tr>' . $week . '</tr>';
                $week = '';
            }
        }

        return $weeks;
    }

}
