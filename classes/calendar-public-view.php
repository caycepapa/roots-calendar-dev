<?php

include_once( 'config.php' );

class CalendarPublicView{

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
        $ym = date('Y-m');

        // タイムスタンプを作成し、フォーマットをチェックする
        $timestamp = strtotime($ym . '-01');
        if ($timestamp === false) {
            $ym = date('Y-m');
            $timestamp = strtotime($ym . '-01');
        }


        $today = date('Y-m-d');
        $today_num = date('j');
        $today_month = date('m');

        $html_title = date('Y年n月', $timestamp);

        $day_count = date('t', $timestamp);
        $youbi = date('w', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp)));

        $weeks = [];
        $week = '';

        $week .= str_repeat('<td></td>', $youbi);

        for ( $day = 1; $day <= $day_count; $day++, $youbi++) {

            $date = $ym . '-' . str_pad($day, 2, 0, STR_PAD_LEFT);
            $bg_color        = '';
            $rc_eve_btnclass = '';
            $rc_eve_balloon  = '';

            $rc_date = $rc_status[array_search('rc_status_'.$date, array_column($rc_status, 'meta_key'))];
            $rc_status_flg = $setting_records[array_search($rc_date['meta_value'], array_column($setting_records, 'state_name'))];

            $bg_color = $rc_status_flg['state_color'];
            
            // イベント生成
            $balloonArray = $this->create_balloon($date, $rc_events);

            if($day < $today_num && $today_month == date('m', $timestamp)){
                // 過ぎてしまった日を入れる
                $week .= '<td class="rc_cal_day rc_cal_day--end"><div class="rc_cal_day_wrap"><p>' . $day . '</p></div>';
            }else{
                // 今日以降の日
                $today_flg = $today == $date ? 'rc_cal_today ' : '';
                if($balloonArray){
                    $rc_eve_btnclass     = $balloonArray['rc_eve_btnclass'];
                    $rc_eve_balloon      = $balloonArray['rc_eve_balloon'];
                    $bg_color            = $balloonArray['bg_color'];
                }
                $week .= '<td class="rc_cal_day '. $today_flg .$rc_eve_btnclass .'" data='.$date.' style="background-color:'.$bg_color.'"><div class="rc_cal_day_wrap"><p>' . $day . '</p>' . $rc_eve_balloon . '</div>';
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

        ?>
            <div class="rc-calendar__wrap" id="rc-calendar">
                <div class="rc-calendar__header">
                    <a name="rcPrevBtn">&lt;前の月</a> 
                    <h3 class="mb-5"><?php echo $html_title; ?></h3>
                    <a name="rcNextBtn">次の月&gt;</a>
                </div>
                <table class="rc-calendar__table">
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
            </div>
        <?php
    }

    function create_balloon($date,$rc_events){

        $event_num = array_search('rc_events_'.$date, array_column($rc_events, 'meta_key'));
        $rc_eve_balloon = '';
        $rc_eve_btnclass = '';

        if(is_int($event_num)){
            $rc_eve = $rc_events[$event_num];
            $rc_eve_array = json_decode($rc_eve['meta_value'],true);
            $rc_eve_balloon = '<div class="rc_cal_balloon">';

            for($i = 0; $i < count($rc_eve_array); $i++){
                if($rc_eve_array[$i]['event_name'] !== ''){
                    $rc_eve_balloon .= '<a href="'.$rc_eve_array[$i]['event_url'].'">';
                    $rc_eve_balloon .= $rc_eve_array[$i]['event_name'].'</a>';
                    
                    $rc_eve_btnclass = 'rc_cal_btn--hasevent';
                    $bg_color = $rc_eve_array[$i]['event_color'];
                }
            }

            $rc_eve_balloon .= '</div>';

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

    function create_calendar(){
        
    }

}
