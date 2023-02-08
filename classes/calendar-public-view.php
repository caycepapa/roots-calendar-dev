<?php

include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );

class CalendarPublicView{

    function rc_calset_form($post_id){

        global $wpdb;

        $sql_status = "SELECT * FROM $wpdb->postmeta WHERE post_id =".$post_id." AND meta_key LIKE 'rc_status_%'";
        $rc_status = $wpdb->get_results($sql_status,ARRAY_A);
        $rc_date = $rc_status[array_search('rc_status_2023-01-22', array_column($rc_status, 'meta_key'))];

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

        $today = date('Y-m-j');

        $html_title = date('Y年n月', $timestamp);

        $prev = date('Y-m', mktime(0, 0, 0, date('m', $timestamp)-1, 1, date('Y', $timestamp)));
        $next = date('Y-m', mktime(0, 0, 0, date('m', $timestamp)+1, 1, date('Y', $timestamp)));

        $day_count = date('t', $timestamp);
        $youbi = date('w', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp)));

        $weeks = [];
        $week = '';

        $week .= str_repeat('<td></td>', $youbi);

        for ( $day = 1; $day <= $day_count; $day++, $youbi++) {

            $date = $ym . '-' . str_pad($day, 2, 0, STR_PAD_LEFT);;
            $rc_date = $rc_status[array_search('rc_status_'.$date, array_column($rc_status, 'meta_key'))];

            if ($today == $date) {
                $week .= '<td class="today" data='.$date.'>' . $day.'<p>'.$rc_date['meta_value'].'</p>';
            } else {
                $week .= '<td data='.$date.'>' . $day .'<p>'.$rc_date['meta_value'].'</p>';
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
                <h3 class="mb-5"><?php echo $html_title; ?></h3>
                <a href="?ym=<?php echo $prev; ?>#rc-calendar">&lt;</a> 
                <a href="?ym=<?php echo $next; ?>#rc-calendar">&gt;</a>
                <table>
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
