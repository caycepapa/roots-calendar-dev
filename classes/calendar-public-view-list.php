<?php

include_once( 'config.php' );

class CalendarPublicViewList{

    function rc_calset_form($post_id,$listnum){
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

        $today_num      = date('d');
        $today_year     = date('Y');
        $today_month    = date('m');
        $litnum_txt     = '+'.$listnum.' day';

        $start = $today_year.'-'.$today_month.'-'.$today_num;
        $end   = date('Y-m-d', strtotime($today_year . $litnum_txt));
        $listdom = '';

        for ( $day = $start; $day <= $end; $day = date('Y-m-d', strtotime($day . '+1 day'))) {
            
            $date = date('Y',strtotime($day)).'-'.date('m',strtotime($day)). '-' . str_pad(date('j',strtotime($day)), 2, 0, STR_PAD_LEFT);

            $week = date('w', strtotime($date));
            $weekday = ["日", "月", "火", "水", "木", "金", "土"][$week];

            $balloonArray   = $this->create_balloon($date, $rc_events);

            if($balloonArray && $balloonArray['rc_eve_type'] !== ''){
                $listdom .= '<li class="event">';
                $listdom .= '<div>'.date('n',strtotime($day)).'/'.date('j',strtotime($day)).'（'.$weekday.'）'.'</div>';
                $listdom .= '<div>'.$balloonArray['rc_eve_balloon'].'</div>';
                $listdom .= '</li>';
            }elseif($listnum == 0){
                $listdom .= '<li class="noevent">本日のみのイベントはありません。</li>';
            }
        }

        echo $listdom;

    }

    function create_balloon($date,$rc_events){

        $event_num = array_search('rc_events_'.$date, array_column($rc_events, 'meta_key'));

        if(is_int($event_num)){
            $rc_eve = $rc_events[$event_num];
            $rc_eve_array = json_decode($rc_eve['meta_value'],true);
            $rc_eve_balloon = '';
            $rc_eve_type = '';
            $rc_eve_btnclass = '';
            $bg_color = '';

            for($i = 0; $i < count($rc_eve_array); $i++){
                if($rc_eve_array[$i]['event_type'] == 'url'){
                    if($rc_eve_array[$i]['event_name'] !== '' && $rc_eve_array[$i]['event_url'] !== ''){
                        $rc_eve_balloon .= '<a href="'.$rc_eve_array[$i]['event_url'].'">';
                        $rc_eve_balloon .= $rc_eve_array[$i]['event_name'].'</a>';
                        $rc_eve_btnclass = 'rc_cal_btn--hasevent';
                        $bg_color = $rc_eve_array[$i]['event_color'];
                    }elseif($rc_eve_array[$i]['event_url'] == ''){
                        $rc_eve_balloon .= '<span>';
                        $rc_eve_balloon .= $rc_eve_array[$i]['event_name'].'</span>';
                        $rc_eve_btnclass = 'rc_cal_btn--hasevent';
                        $bg_color = $rc_eve_array[$i]['event_color'];
                    }
                    $rc_eve_type = 'url';
                }elseif($rc_eve_array[$i]['event_type'] == 'post'){
                    if($rc_eve_array[$i]['event_id'] !== '' && $rc_eve_array[$i]['event_name'] !== ''){
                        $rc_eve_balloon .= '<a href="'.get_permalink($rc_eve_array[$i]['event_id']).'">';
                        // event_idからevents-categoryを取得
                        $rc_eve_balloon .= '<span class="rc_cal_event_category">'.get_the_terms($rc_eve_array[$i]['event_id'], 'events-category')[0]->name.'</span>';
                        
                        $rc_eve_balloon .= $rc_eve_array[$i]['event_name'].'</a>';
                        $rc_eve_btnclass = 'rc_cal_btn--hasevent';
                        $bg_color = $rc_eve_array[$i]['event_color'];
                    }
                    $rc_eve_type = 'post';
                }
            }

            $balloonArray = array(
                'rc_eve_btnclass' => $rc_eve_btnclass,
                'rc_eve_balloon' => $rc_eve_balloon,
                'bg_color' => $bg_color,
                'rc_eve_type' => $rc_eve_type,
            );

            return $balloonArray;

        }else{
            return false;
        }
    }

}
