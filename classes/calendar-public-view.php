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

        // $howlong->option_value

        if (isset($_GET['ym'])) {
            $ym = $_GET['ym'];
        } else {
            $ym = date('Y-m');
        }

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

        /* 
        翌月のボタン
        ---------------------------------------------- */
        $nextMonth =  date('m', $timestamp) + 1;
        $prevMonth =  date('m', $timestamp);
        $optionValue = $howlong->option_value;
        $viewMonth = date('m') + $optionValue;

        if($viewMonth >= 12){
            $viewMonth = $viewMonth - 12;
        }

        if($optionValue == 1){
            $prev = '';
            $next = '';
        }else{
            if($nextMonth == $viewMonth){
                $prev = date('Y-m', mktime(0, 0, 0, date('m', $timestamp)-1, 1, date('Y', $timestamp)));
                $next = '';
            }elseif($today_month == $prevMonth){
                $prev = '';
                $next = date('Y-m', mktime(0, 0, 0, date('m', $timestamp)+1, 1, date('Y', $timestamp)));
            }else{
                $prev = date('Y-m', mktime(0, 0, 0, date('m', $timestamp)-1, 1, date('Y', $timestamp)));
                $next = date('Y-m', mktime(0, 0, 0, date('m', $timestamp)+1, 1, date('Y', $timestamp))); 
            }
        }


        $day_count = date('t', $timestamp);
        $youbi = date('w', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp)));

        $weeks = [];
        $week = '';

        $week .= str_repeat('<td></td>', $youbi);

        for ( $day = 1; $day <= $day_count; $day++, $youbi++) {

            $date = $ym . '-' . str_pad($day, 2, 0, STR_PAD_LEFT);
            $bg_color = '';

            $rc_date = $rc_status[array_search('rc_status_'.$date, array_column($rc_status, 'meta_key'))];
            $rc_status_flg = $setting_records[array_search($rc_date['meta_value'], array_column($setting_records, 'state_name'))];

            $bg_color = $rc_status_flg['state_color'];
            
            // eventsの配列番号を返す
            $event_num = array_search('rc_events_'.$date, array_column($rc_events, 'meta_key'));
            $rc_eve_balloon = '';
            $rc_eve_btnclass = '';

            /* 
            eventが存在する場合
            ---------------------------------------------- */
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
            }

            if ($today == $date) {
                $week .= '<td class="rc_cal_day rc_cal_today '.$rc_eve_btnclass.'" data='.$date.' style="background-color:'.$bg_color.'"><div class="rc_cal_day_wrap"><p>' . $day . '</p>' . $rc_eve_balloon . '</div>';
            } else {
                if($day < $today_num && $today_month == date('m', $timestamp)){
                    $week .= '<td class="rc_cal_day rc_cal_day--end"><div class="rc_cal_day_wrap"><p>' . $day . '</p></div>';
                }else{
                    $week .= '<td class="rc_cal_day '.$rc_eve_btnclass.'" data='.$date.' style="background-color:'.$bg_color.'"><div class="rc_cal_day_wrap"><p>' . $day . '</p>' . $rc_eve_balloon . '</div>';
                }
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
                    <?php if(!empty($prev)):?>
                    <a href="?ym=<?php echo $prev; ?>#rc-calendar">&lt;前の月</a> 
                    <?php else: ?>
                    <span>&lt;前の月</span> 
                    <?php endif; ?>
                    <h3 class="mb-5"><?php echo $html_title; ?></h3>
                    <?php if(!empty($next)): ?>
                    <a href="?ym=<?php echo $next; ?>#rc-calendar">次の月&gt;</a>
                    <?php else: ?>
                    <span>次の月&gt;</span>
                    <?php endif;?>
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

}
