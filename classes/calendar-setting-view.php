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
            $option_table_name = $wpdb->prefix . RC_Config::OPTION_TABLE;

            /*
            公開月数の更新
            ---------------------------------------------- */
            if(isset($_POST['option_value'])){
                // CSRF対策: nonce検証
                if(!isset($_POST['rc_setting_nonce']) || !wp_verify_nonce($_POST['rc_setting_nonce'], 'rc_setting_action')){
                    wp_die( __( 'Security check failed.' ) );
                }

                $option_records = $wpdb->get_results("SELECT * FROM ".$option_table_name);
                $howlong_exists = false;
                if(!empty($option_records)){
                    foreach($option_records as $opt){
                        if($opt->option_name === '公開月数'){
                            $howlong_exists = true;
                            break;
                        }
                    }
                }

                if($howlong_exists){
                    $wpdb->update(
                        $option_table_name,
                        array(
                            'option_name' => '公開月数',
                            'option_value' => intval($_POST['option_value'])
                        ),
                        array('option_name' => '公開月数')
                    );
                }else{
                    $wpdb->insert(
                        $option_table_name,
                        array(
                            'option_name' => '公開月数',
                            'option_value' => intval($_POST['option_value']),
                        )
                    );
                }
            }

            /*
            デフォルトステータスの更新
            ---------------------------------------------- */
            if(isset($_POST['default_status'])){
                // CSRF対策: nonce検証
                if(!isset($_POST['rc_setting_nonce']) || !wp_verify_nonce($_POST['rc_setting_nonce'], 'rc_setting_action')){
                    wp_die( __( 'Security check failed.' ) );
                }

                $option_records = $wpdb->get_results("SELECT * FROM ".$option_table_name);
                $default_exists = false;
                if(!empty($option_records)){
                    foreach($option_records as $opt){
                        if($opt->option_name === 'デフォルトステータス'){
                            $default_exists = true;
                            break;
                        }
                    }
                }

                $default_status_value = sanitize_text_field($_POST['default_status']);

                if($default_exists){
                    $wpdb->update(
                        $option_table_name,
                        array(
                            'option_name' => 'デフォルトステータス',
                            'option_value' => $default_status_value
                        ),
                        array('option_name' => 'デフォルトステータス')
                    );
                }else{
                    $wpdb->insert(
                        $option_table_name,
                        array(
                            'option_name' => 'デフォルトステータス',
                            'option_value' => $default_status_value,
                        )
                    );
                }
            }

            /*
            追加時実行
            ---------------------------------------------- */
            if(isset($_POST['state_name']) || isset($_POST['state_color']) || isset($_POST['state_mark']) || isset($_POST['state_txt']) ){
                // CSRF対策: nonce検証
                if(!isset($_POST['rc_setting_nonce']) || !wp_verify_nonce($_POST['rc_setting_nonce'], 'rc_setting_action')){
                    wp_die( __( 'Security check failed.' ) );
                }
                // 入力値をサニタイズ
                $wpdb->insert(
                    $table_name,
                    array(
                        'state_name' => sanitize_text_field($_POST['state_name']),
                        'state_color' => sanitize_hex_color($_POST['state_color']),
                        'state_mark' => sanitize_text_field($_POST['state_mark']),
                        'state_txt' => sanitize_text_field($_POST['state_txt']),
                    )
                );
            }

            /*
            更新
            ---------------------------------------------- */
            if(isset($_POST['cal_setting'])){
                // CSRF対策: nonce検証
                if(!isset($_POST['rc_setting_nonce']) || !wp_verify_nonce($_POST['rc_setting_nonce'], 'rc_setting_action')){
                    wp_die( __( 'Security check failed.' ) );
                }
                $cal_settings = $_POST['cal_setting'];
                foreach($cal_settings as $cal_setting){
                    // IDを整数に変換
                    $state_id = intval($cal_setting['state_id']);
                    if(isset($cal_setting['delete']) && $cal_setting['delete']){
                        $wpdb->delete(
                            $table_name,
                            array( 'id' => $state_id)
                        );
                    }else{
                        $wpdb->update(
                            $table_name,
                            array(
                                'state_name' => sanitize_text_field($cal_setting['state_name']),
                                'state_color' => sanitize_hex_color($cal_setting['state_color']),
                                'state_mark' => sanitize_text_field($cal_setting['state_mark']),
                                'state_txt' => sanitize_text_field($cal_setting['state_txt']),
                            ),
                            array( 'id' => $state_id)
                        );
                    }
                }
            }

            /*
            画面表示
            ---------------------------------------------- */
            $records = $wpdb->get_results("SELECT * FROM ".$table_name);

            // 公開月数を取得
            $option_records = $wpdb->get_results("SELECT * FROM ".$option_table_name);
            $howlong = null;
            $default_status = null;
            if(!empty($option_records)){
                foreach($option_records as $opt){
                    if($opt->option_name === '公開月数'){
                        $howlong = $opt;
                    }
                    if($opt->option_name === 'デフォルトステータス'){
                        $default_status = $opt;
                    }
                }
            }

            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">カレンダー設定</h1>
                <hr class="wp-header-end">

                <h2>公開月数</h2>
                <form action='edit.php?post_type=<?php echo esc_attr(RC_Config::NAME);?>&page=<?php echo esc_attr(RC_Config::SETTING_NAME);?>' method='POST'>
                    <?php wp_nonce_field('rc_setting_action', 'rc_setting_nonce'); ?>
                    公開月数：
                    <select name="option_value">
                        <?php
                            for($i = 1; $i <= 12; $i++){
                                $selected = (isset($howlong->option_value) && $howlong->option_value == $i) ? 'selected' : ($i == 1 && !isset($howlong->option_value) ? 'selected' : '');
                                echo '<option value="'.esc_attr($i).'" '.$selected.'>'.esc_html($i).'</option>';
                            }
                        ?>
                    </select>
                    <input type="submit" value="更新">
                </form>

                <hr style="margin: 30px 0;">

                <h2>デフォルトステータス</h2>
                <p>カレンダーで何も設定していない日に表示されるステータスを選択してください。</p>
                <form action='edit.php?post_type=<?php echo esc_attr(RC_Config::NAME);?>&page=<?php echo esc_attr(RC_Config::SETTING_NAME);?>' method='POST'>
                    <?php wp_nonce_field('rc_setting_action', 'rc_setting_nonce'); ?>
                    <select name="default_status" style="min-width: 200px;">
                        <option value="">-- 未設定 --</option>
                        <?php foreach($records as $record): ?>
                            <option value="<?php echo esc_attr($record->state_name); ?>"
                                <?php selected(isset($default_status->option_value) ? $default_status->option_value : '', $record->state_name); ?>
                                style="background-color: <?php echo esc_attr($record->state_color); ?>">
                                <?php echo esc_html($record->state_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="submit" value="更新">
                    <?php if(isset($default_status->option_value) && $default_status->option_value !== ''): ?>
                        <span style="margin-left: 10px; padding: 4px 8px; background: #e7f3ff; border-radius: 4px;">
                            現在の設定: <strong><?php echo esc_html($default_status->option_value); ?></strong>
                        </span>
                    <?php endif; ?>
                </form>

                <hr style="margin: 30px 0;">

                <h2>ステータス</h2>
                <h4>ステータス追加</h4>
                <form action='edit.php?post_type=<?php echo esc_attr(RC_Config::NAME);?>&page=<?php echo esc_attr(RC_Config::SETTING_NAME);?>' method='POST'>
                    <?php wp_nonce_field('rc_setting_action', 'rc_setting_nonce'); ?>
                    <input type="text" name="state_name" value="" placeholder="ステータス名">
                    <input type="color" name="state_color" value="">
                    <input type="text" name="state_txt" placeholder="説明テキスト">
                    <select name="state_mark">
                        <option value="true">◯</option>
                        <option value="false">✕</option>
                        <option value="other">△</option>
                    </select>
                    <input type="submit" value="追加">
                </form>
                <h4>ステータス一覧</h4>
                <div>
                    <form action='edit.php?post_type=<?php echo esc_attr(RC_Config::NAME);?>&page=<?php echo esc_attr(RC_Config::SETTING_NAME);?>' method='POST'>
                    <?php wp_nonce_field('rc_setting_action', 'rc_setting_nonce'); ?>
                    <?php
                        $records = json_decode(json_encode($records), true);
                        foreach($records as $record):
                    ?>
                            <div>
                                <input type="text" name="cal_setting[<?php echo esc_attr($record['id']);?>][state_name]" value="<?php echo esc_attr($record['state_name']);?>">
                                <input type="color" name="cal_setting[<?php echo esc_attr($record['id']);?>][state_color]" value="<?php echo esc_attr($record['state_color']);?>">
                                <input type="text" name="cal_setting[<?php echo esc_attr($record['id']);?>][state_txt]" value="<?php echo esc_attr($record['state_txt']);?>">
                                <select name="cal_setting[<?php echo esc_attr($record['id']);?>][state_mark]" id="">
                                    <option value="true" <?php echo $record['state_mark'] == 'true' ? 'selected': '' ?>>◯</option>
                                    <option value="false" <?php echo $record['state_mark'] == 'false' ? 'selected': '' ?>>✕</option>
                                    <option value="other" <?php echo $record['state_mark'] == 'other' ? 'selected': '' ?>>△</option>
                                </select>
                                <input type="hidden" name="cal_setting[<?php echo esc_attr($record['id']);?>][state_id]" value="<?php echo esc_attr($record['id']);?>">
                                <label><input type="checkbox" name="cal_setting[<?php echo esc_attr($record['id']);?>][delete]">削除</label>
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
            <?php
        }
    }
}