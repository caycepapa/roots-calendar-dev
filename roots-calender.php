<?php
/*
Plugin Name: roots-calender
Description: rootsのカレンダー
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );

class CustomMetaTable {

    var $table_name;

    public function __construct(){
        global $wpdb;
        $this->table_name = $wpdb->prefix.RC_Config::SETTING_TABLE;
        register_activation_hook (__FILE__, array($this, 'cmt_activate'));
    }

    function cmt_activate() {
        global $wpdb;
        $cmt_db_version = '4.0';

        $installed_ver = get_option( 'cmt_meta_version' );

        if( $installed_ver != $cmt_db_version ) {
            $sql = "CREATE TABLE " . $this->table_name . " (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                event_name text NOT NULL,
                event_color text NOT NULL,
                UNIQUE KEY id (id)
            )
            CHARACTER SET 'utf8';";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            update_option('cmt_meta_version', $cmt_db_version);
        }
    }
}

class RootsCalender{

    public function __construct() {
        
        $exmeta = new CustomMetaTable;

        /* 
        カレンダー設定画面
        ---------------------------------------------- */
        function roots_calender_init() {
            add_submenu_page(
                'edit.php?post_type='.RC_Config::NAME,
                RC_Config::NAME ,
                '設定',
                'manage_options',
                RC_Config::SETTING_NAME ,
                'rc_top_view'
            );
        }
        add_action( 'admin_menu', 'roots_calender_init' );

        function rc_top_view() {

            if ( !current_user_can( 'manage_options' ) )  {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            global $wpdb;
            $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;

            if(isset($_POST['event_name']) || isset($_POST['event_color'])){
                $wpdb->insert(
                    $table_name,
                    array(
                        'event_name' => $_POST['event_name'],
                        'event_color' => $_POST['event_color'],
                    )
                );
            }

            $records = $wpdb->get_results("SELECT * FROM ".$table_name);
            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">カレンダー設定</h1>
                <hr class="wp-header-end">
                <h2>新規登録</h2>
                <form action='edit.php?post_type=<?php echo RC_Config::NAME;?>&page=<?php echo RC_Config::SETTING_NAME;?>' method='POST'>
                    <input type="text" name="event_name" value="">
                    <input type="color" name="event_color" value="">
                    <input type="submit" value="追加">
                </form>
                <h2>一覧</h2>
                <div>
                    <?php
                        $records = json_decode(json_encode($records), true);
                        foreach($records as $record):
                    ?>

                            <div><?php echo $record['id'];?> <?php echo $record['event_name'];?> <?php echo $record['event_color'];?></div>
                    <?php
                        endforeach;
                    ?>
                </div>
            </div>
            <?php
        }

        /* 
        カレンダーメニュー表示
        ---------------------------------------------- */
        function rc_add_page() {
            register_post_type( RC_Config::NAME ,
                array(
                    'public' => true,
                    'label' => 'カレンダー',
                    'show_in_rest' => false,
                    'supports' => array('title'),
                    'has_archive' => false,
                    'menu_position' => 0,
                    'menu_icon' => 'dashicons-calendar',
                    'hierarchical' => true,
                    'labels' => array(
                        'menu_name' => 'カレンダー',
                        'all_items' => 'カレンダー一覧',
                        'add_new' => '新規カレンダー追加',
                        'exclude_from_search' => false,
                    ),
                )
            );
        }
        add_action('init', 'rc_add_page');

        /* 
        カレンダーページカスタムフィールド
        ---------------------------------------------- */
        function rc_create_custom_fields(){
            add_meta_box(
                'rc_calset',
                '休日・イベント設定',
                'rc_calset_form',
                RC_Config::NAME,
                'normal',
                'default',
            );
        }
        add_action('admin_menu', 'rc_create_custom_fields');

        function rc_calset_form($post){

            wp_nonce_field('custom_field_save_meta_box_data', 'custom_field_meta_box_nonce');

            $rc_date01 = get_post_meta($post->ID, 'rc_date01', true);

            ?>
                <input type="date" name="rc_date01" value="<?php echo $rc_date01; ?>">
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

            if (isset($_POST['rc_date01'])) {
                $data = sanitize_text_field($_POST['rc_date01']);
                update_post_meta($post_id, 'rc_date01', $data);
            }

        }
        add_action('save_post', 'save_custom_fields');

    }

}

new RootsCalender();