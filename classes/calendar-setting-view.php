<?php

include_once( 'config.php' );

class CalendarSettingView{

    public function __construct(){

        /* 
        カレンダー設定画面
        ---------------------------------------------- */
        function roots_calendar_init() {
            add_submenu_page(
                'edit.php?post_type='.RC_Config::NAME,
                RC_Config::NAME ,
                '設定',
                'manage_options',
                RC_Config::SETTING_NAME ,
                'rc_setting_view'
            );
        }

        add_action( 'admin_menu', 'roots_calendar_init' );

        function rc_setting_view() {

            if ( !current_user_can( 'manage_options' ) )  {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            global $wpdb;
            $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;

            /* 
            追加時実行
            ---------------------------------------------- */
            if(isset($_POST['state_name']) || isset($_POST['state_color']) || isset($_POST['state_mark']) || isset($_POST['state_txt']) ){
                $wpdb->insert(
                    $table_name,
                    array(
                        'state_name' => $_POST['state_name'],
                        'state_color' => $_POST['state_color'],
                        'state_mark' => $_POST['state_mark'],
                        'state_txt' => $_POST['state_txt'],
                    )
                );
            }

            /* 
            更新
            ---------------------------------------------- */
            if(isset($_POST['cal_setting'])){
                $cal_settings = $_POST['cal_setting'];
                foreach($cal_settings as $cal_setting){
                    if(isset($cal_setting['delete']) && $cal_setting['delete']){
                        $wpdb->delete(
                            $table_name,
                            array( 'id' =>  $cal_setting['state_id']) 
                        );
                    }else{
                        $wpdb->update(
                            $table_name,
                            array(
                                'state_name' => $cal_setting['state_name'],
                                'state_color' => $cal_setting['state_color'],
                                'state_mark' => $cal_setting['state_mark'],
                                'state_txt' => $cal_setting['state_txt'],
                            ),
                            array( 'id' =>  $cal_setting['state_id']) 
                        );
                    }
                }
            }

            /* 
            公開月数保存処理と取得処理
            ---------------------------------------------------------- */
            global $wpdb;
            $option_table = $wpdb->prefix . RC_Config::OPTION_TABLE;
            $option_name = '公開月数';

            // 保存処理
            if (isset($_POST['option_value'][0])) {
                $option_value = intval($_POST['option_value'][0]);
                $existing = $wpdb->get_var(
                    $wpdb->prepare("SELECT COUNT(*) FROM {$option_table} WHERE option_name = %s", $option_name)
                );
                if ($existing) {
                    $wpdb->update(
                        $option_table,
                        ['option_value' => $option_value],
                        ['option_name' => $option_name]
                    );
                } else {
                    $wpdb->insert(
                        $option_table,
                        ['option_name' => $option_name, 'option_value' => $option_value]
                    );
                }
            }

            // 表示用取得
            $howlong = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$option_table} WHERE option_name = %s", $option_name)
            );


            /* 
            画面表示
            ---------------------------------------------- */
            $records = $wpdb->get_results("SELECT * FROM ".$table_name);

            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">カレンダー設定</h1>
                <hr class="wp-header-end">
                <h2>ステータス設定</h2>
                <div class="rc-setting-nest">
                <h3>新規登録</h3>
                <form action='edit.php?post_type=<?php echo RC_Config::NAME;?>&page=<?php echo RC_Config::SETTING_NAME;?>' method='POST'>
                    <input type="text" name="state_name" value="">
                    <input type="color" name="state_color" value="">
                    <input type="text" name="state_txt">
                    <select name="state_mark">
                        <option value="true">◯</option>
                        <option value="false">✕</option>
                        <option value="other">△</option>
                    </select>
                    <input type="submit" value="追加">
                </form>
                </div>
                <div class="rc-setting-nest">
                <h3>一覧</h3>
                <div>
                    <form action='edit.php?post_type=<?php echo RC_Config::NAME;?>&page=<?php echo RC_Config::SETTING_NAME;?>' method='POST'>
                    <?php
                        $records = json_decode(json_encode($records), true);
                        foreach($records as $record):
                    ?>
                            <div>
                                <input type="text" name="cal_setting[<?php echo $record['id'];?>][state_name]" value="<?php echo $record['state_name'];?>">
                                <input type="color" name="cal_setting[<?php echo $record['id'];?>][state_color]" value="<?php echo $record['state_color'];?>">
                                <input type="text" name="cal_setting[<?php echo $record['id'];?>][state_txt]" value="<?php echo $record['state_txt'];?>">
                                <select name="cal_setting[<?php echo $record['id'];?>][state_mark]" id="">
                                    <option value="true" <?php echo $record['state_mark'] == 'true' ? 'selected': '' ?>>◯</option>
                                    <option value="false" <?php echo $record['state_mark'] == 'false' ? 'selected': '' ?>>✕</option>
                                    <option value="other" <?php echo $record['state_mark'] == 'other' ? 'selected': '' ?>>△</option>
                                </select>
                                <input type="hidden" name="cal_setting[<?php echo $record['id'];?>][state_id]" value="<?php echo $record['id'];?>">
                                <label><input type="checkbox" name="cal_setting[<?php echo $record['id'];?>][delete]">削除</label>
                            </div>
                    <?php
                        endforeach;
                    ?>
                    <div>
                        <input type="submit" value="更新">
                    </div>
                    </form>
                </div>
                </div>
            </div>
            <div class="wrap">
                <hr class="wp-header-end">
                <h2>公開月数</h2>
                <form action='edit.php?post_type=<?php echo RC_Config::NAME;?>&page=<?php echo RC_Config::SETTING_NAME;?>' method='POST'>
                    <select name="option_value[0]">
                        <?php
                            $selected_value = isset($howlong->option_value) ? intval($howlong->option_value) : 1;
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($i === $selected_value) ? 'selected' : '';
                                echo "<option value=\"{$i}\" {$selected}>{$i}</option>";
                            }
                        ?>
                    </select>
                    <input type="submit" value="更新">
                </form>
            </div>
            <div class="wrap">
                <hr class="wp-header-end">
                <h2>イベント設定</h2>
                <div class="rc-setting-nest">
                    <h3>取得する投稿</h3>
                </div>
            </div>
            <?php
        }
    }
}

