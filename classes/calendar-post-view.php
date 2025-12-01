<?php

include_once( 'config.php' );

class CalendarPostView{

    function __construct(){
        add_action('admin_menu', array($this,'rc_create_custom_fields'));
        add_action('save_post', array($this,'save_custom_fields'));
        add_action('wp_ajax_rc_migrate_to_default', array($this, 'ajax_migrate_to_default'));
    }

    function rc_create_custom_fields(){

        add_meta_box(
            'rc_calset',
            '例外日・イベント設定',
            array($this,'rc_calset_form'),
            RC_Config::NAME,
            'normal',
            'default',
        );
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

    /**
     * マイグレーション用AJAX処理
     */
    function ajax_migrate_to_default(){
        // nonce検証
        if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rc_migrate_nonce')){
            wp_send_json_error(array('message' => 'セキュリティエラー'));
        }

        if(!current_user_can('edit_posts')){
            wp_send_json_error(array('message' => '権限がありません'));
        }

        global $wpdb;
        $post_id = intval($_POST['post_id']);

        // グローバルデフォルト設定を取得
        $default_status = self::get_global_default_status();
        if(empty($default_status)){
            wp_send_json_error(array('message' => 'デフォルトステータスが設定されていません。「カレンダー > 設定」で設定してください。'));
        }

        // 全ての日付別ステータスを取得
        $sql_status = $wpdb->prepare(
            "SELECT meta_id, meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            'rc_status_%'
        );
        $status_records = $wpdb->get_results($sql_status);

        $deleted_count = 0;
        foreach($status_records as $record){
            // デフォルトと同じ値なら削除
            if($default_status === $record->meta_value){
                $wpdb->delete($wpdb->postmeta, array('meta_id' => $record->meta_id));
                $deleted_count++;
            }
        }

        wp_send_json_success(array('message' => $deleted_count . ' 件のレコードを削除しました'));
    }

    function rc_calset_form($post){

        wp_nonce_field('custom_field_save_meta_box_data', 'custom_field_meta_box_nonce');

        global $wpdb;

        // post_idを整数に変換（SQLインジェクション対策）
        $post_id = intval($post->ID);

        // グローバルデフォルトステータスを取得
        $global_default_status = self::get_global_default_status();

        // rc_statusをphpの配列からjsの配列へ（例外登録のみ）
        $sql_status = $wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            'rc_status_%'
        );
        $rc_status = $wpdb->get_results($sql_status,OBJECT);
        $existing_status_count = count($rc_status);
        // XSS対策: JSON出力を安全にエンコード
        echo '<script>var rc_status_array = ' . wp_json_encode($rc_status) . ';</script>';

        // rc_eventsをphpの配列からjsの配列へ
        $sql_events = $wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
            $post_id,
            'rc_events_%'
        );
        $rc_events = $wpdb->get_results($sql_events, OBJECT);
        // シングルクウォートのエスケープを取り除く
        foreach ($rc_events as &$event) {
            $event->meta_value = str_replace("\\'", "&#39;", $event->meta_value);
            $event->meta_value = str_replace('\\"', "&quot;", $event->meta_value);
        }
        // XSS対策: JSON出力を安全にエンコード
        echo '<script>var rc_events_array = ' . wp_json_encode($rc_events) . ';</script>';


        // カスタム投稿 eventsを取得し、jsの配列へ
        $sql_events_posts = "SELECT * FROM $wpdb->posts WHERE post_type = 'events' AND post_status = 'publish'";
        $rc_events_posts = $wpdb->get_results($sql_events_posts, OBJECT);
        // XSS対策: JSON出力を安全にエンコード
        echo '<script>var rc_events_posts_array = ' . wp_json_encode($rc_events_posts) . ';</script>';

        // setting
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $setting_records = $wpdb->get_results("SELECT * FROM ".$table_name);
        // XSS対策: JSON出力を安全にエンコード
        echo '<script>var setting_records_array = ' . wp_json_encode($setting_records) . ';</script>';

        // グローバルデフォルトステータスをJSに渡す
        echo '<script>var rc_default_status = ' . wp_json_encode($global_default_status) . ';</script>';

        ?>
            <div name="cal_container">
                <?php if(!empty($global_default_status)): ?>
                <div class="rc-default-info" style="margin-bottom: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #2271b1;">
                    <strong>デフォルトステータス:</strong> <?php echo esc_html($global_default_status); ?>
                    <span style="margin-left: 10px; color: #666;">（何も設定していない日はこのステータスが表示されます）</span>
                </div>
                <?php else: ?>
                <div class="rc-default-info" style="margin-bottom: 15px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107;">
                    <strong>注意:</strong> デフォルトステータスが設定されていません。
                    <a href="<?php echo admin_url('edit.php?post_type=' . RC_Config::NAME . '&page=' . RC_Config::SETTING_NAME); ?>">カレンダー設定</a>で設定してください。
                </div>
                <?php endif; ?>

                <?php if($existing_status_count > 0 && !empty($global_default_status)): ?>
                <div class="rc-migration-section" style="margin-bottom: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
                    <strong>データ最適化:</strong> 現在 <?php echo $existing_status_count; ?> 件の例外日登録があります。
                    <button type="button" id="rc-migrate-btn" class="button button-secondary" data-post-id="<?php echo $post_id; ?>" style="margin-left: 10px;">
                        デフォルトと同じレコードを削除
                    </button>
                    <span id="rc-migrate-result" style="margin-left: 10px;"></span>
                </div>
                <script>
                jQuery(document).ready(function($) {
                    $('#rc-migrate-btn').on('click', function() {
                        var btn = $(this);
                        var postId = btn.data('post-id');
                        btn.prop('disabled', true).text('処理中...');

                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'rc_migrate_to_default',
                                post_id: postId,
                                nonce: '<?php echo wp_create_nonce('rc_migrate_nonce'); ?>'
                            },
                            success: function(response) {
                                if(response.success) {
                                    $('#rc-migrate-result').html('<span style="color: green;">' + response.data.message + '</span>');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1500);
                                } else {
                                    $('#rc-migrate-result').html('<span style="color: red;">' + response.data.message + '</span>');
                                    btn.prop('disabled', false).text('デフォルトと同じレコードを削除');
                                }
                            },
                            error: function() {
                                $('#rc-migrate-result').html('<span style="color: red;">エラーが発生しました</span>');
                                btn.prop('disabled', false).text('デフォルトと同じレコードを削除');
                            }
                        });
                    });
                });
                </script>
                <?php endif; ?>

                <div class="rc-all__controller">
                    <div>
                        <label><input type="checkbox" name="calAllChangeCheckbox" value="allCheckFlg">すべて選択/解除</label>
                    </div>
                    <div>
                        <select name="allChangeSelect">
                        <?php foreach($setting_records as $record): ?>
                            <option value="<?php echo esc_attr($record->state_name);?>">
                            <?php echo esc_html($record->state_name); ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <a class="rc-all__controller__btn" name="allChangeBtn">一括変更</a>
                    </div>
                </div>
                <div class="rc-dayweek__controller">
                    <div class="weekday-toggles-global">
                        <?php
                        $weekdays = array('日', '月', '火', '水', '木', '金', '土');
                        foreach ($weekdays as $i => $day) {
                            echo '<label><input type="checkbox" class="weekday-toggle-global" data-weekday="'.$i.'">'.$day.'</label> ';
                        }
                        ?>
                    </div>
                </div>
                <div class="rc-WeekdayChangeCheckbox" value=""></div>
                <div name="cal_box">
                </div>
            </div>
        <?php
    }


    /*
    データ保存
    ---------------------------------------------- */
    function save_custom_fields($post_id){

        if (!isset($_POST['custom_field_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['custom_field_meta_box_nonce'], 'custom_field_save_meta_box_data')) {
            return;
        }

        // グローバルデフォルトステータスを取得
        $global_default_status = self::get_global_default_status();

        foreach($_POST as $key => $value){
            // キー名をサニタイズ
            $sanitized_key = sanitize_key($key);

            if(preg_match('/^rc_events_/', $sanitized_key)){
                // イベントデータをサニタイズ
                $events = array_map(function($event){
                    return array(
                        'event_name' => isset($event['event_name']) ? sanitize_text_field($event['event_name']) : '',
                        'event_url' => isset($event['event_url']) ? esc_url_raw($event['event_url']) : '',
                        'event_color' => isset($event['event_color']) ? sanitize_hex_color($event['event_color']) : '#ffffff',
                        'event_type' => isset($event['event_type']) ? sanitize_text_field($event['event_type']) : '',
                        'event_id' => isset($event['event_id']) ? intval($event['event_id']) : 0,
                    );
                }, (array)$value);
                $data = wp_json_encode($events, JSON_UNESCAPED_UNICODE);
                update_post_meta($post_id, $sanitized_key , $data);
            }elseif(preg_match('/^rc_status_/', $sanitized_key)){
                $data = sanitize_text_field($value);

                // グローバルデフォルトと同じ値の場合は保存しない（削除する）
                if($data === $global_default_status || $data === ''){
                    // デフォルトと同じか空なので削除
                    delete_post_meta($post_id, $sanitized_key);
                }else{
                    // 例外的な値なので保存
                    if(get_post_meta($post_id, $sanitized_key)){
                        delete_post_meta($post_id, $sanitized_key);
                    }
                    update_post_meta($post_id, $sanitized_key , $data);
                }
            }
        }

    }

}
