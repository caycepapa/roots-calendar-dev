<?php
/*
Plugin Name: roots-calendar
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
        $cmt_db_version = '8.0';

        $installed_ver = get_option( 'cmt_meta_version' );

        if( $installed_ver != $cmt_db_version ) {
            $sql = "CREATE TABLE " . $this->table_name . " (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                state_name text NOT NULL,
                state_color text NOT NULL,
                state_txt text NOT NULL,
                state_mark text NOT NULL,
                UNIQUE KEY id (id)
            )
            CHARACTER SET 'utf8';";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            update_option('cmt_meta_version', $cmt_db_version);
        }
    }
}

class CustomOptionTable {

    var $table_name;

    public function __construct(){
        global $wpdb;
        $this->table_name = $wpdb->prefix.RC_Config::OPTION_TABLE;
        register_activation_hook (__FILE__, array($this, 'cot_activate'));
    }

    function cot_activate() {
        global $wpdb;
        $cot_db_version = '1.0';

        $installed_ver = get_option( 'cot_meta_version' );

        if( $installed_ver != $cot_db_version ) {
            $sql = "CREATE TABLE " . $this->table_name . " (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                option_name text NOT NULL,
                option_value text NOT NULL,
                UNIQUE KEY id (id)
            )
            CHARACTER SET 'utf8';";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            update_option('cot_meta_version', $cot_db_version);
        }
    }
}

/* 
メニュー表示
---------------------------------------------- */
class setMenu {
    public function __construct(){
        function rc_add_page() {

            $labels = array(
                'name'               => 'Calendar',
                'singular_name'      => 'Calendar',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New Calendar',
                'edit_item'          => 'Edit Calendar',
                'new_item'           => 'New Calendar',
                'all_items'          => 'All Calendar',
                'view_item'          => 'View Calendar',
                'search_items'       => 'Search Calendar',
                'not_found'          => 'No Calendar found',
                'not_found_in_trash' => 'No Calendar found in Trash',
                'parent_item_colon'  => '',
                'menu_name'          => 'Calendar'
            );

            register_post_type( RC_Config::NAME ,
                array(
                    'public' => true,
                    'label' => 'Calendar',
                    'show_in_rest' => false,
                    'supports' => array(
                        'title',
                        'author'
                    ),
                    'has_archive' => false,
                    'menu_position' => 99,
                    'menu_icon' => 'dashicons-calendar',
                    'hierarchical' => false,
                    'labels' => $labels,
                )
            );
        }
        add_action('init', 'rc_add_page');
    }
}

class RootsCalendar{

    public function __construct() {
        
        /* 
        ステータス設定用のDB生成クラス
        ---------------------------------------------- */
        new CustomMetaTable;

        /* 
        オプション設定用のDB生成クラス
        ---------------------------------------------- */
        new CustomOptionTable;

        /* 
        メニューセット
        ---------------------------------------------- */
        new setMenu;

        /* 
        カレンダー一覧画面カラムカスタマイズ
        ---------------------------------------------- */

        add_filter('manage_'.RC_Config::NAME.'_posts_columns', 'manage_add_columns');
        add_action('manage_'.RC_Config::NAME.'_posts_custom_column', 'add_custom_column', 10, 2);

        // カラム追加
        function manage_add_columns ($columns) {
            $columns = array(
                'title' => 'カレンダー名',
                'shortcode01' => 'カレンダーショートコード',
                'shortcode02' => '日別表示ショートコード',
                'shortcode03' => 'リスト表示ショートコード',
            );
            return $columns;
        }

        // カラム内容作成
        function add_custom_column ($column_name, $post_id) {
            if($column_name == 'shortcode01'){
                echo '[roots-calendar-key num='.$post_id.']';
            }
            if($column_name == 'shortcode02'){
                echo '[roots-calendar-key-day num='.$post_id.']';
            }
            if($column_name == 'shortcode03'){
                echo '[roots-calendar-key-list num='.$post_id.' listnum=10]';
            }
        }

        /* 
        ショートコード作成
        ---------------------------------------------- */
        function roots_calendar_key_func($atts){
            include_once( plugin_dir_path( __FILE__ ) . 'classes/calendar-public-view.php' );
            $calendar = new CalendarPublicView();
            ob_start();
            echo $calendar->rc_calset_form($atts['num']);
            return ob_get_clean();
        }
        add_shortcode('roots-calendar-key','roots_calendar_key_func');

        function roots_calendar_key_day_func($atts){
            include_once( plugin_dir_path( __FILE__ ) . 'classes/calendar-public-view-day.php' );
            $calendar = new CalendarPublicViewDay();
            ob_start();
            echo $calendar->rc_calset_form($atts['num']);
            return ob_get_clean();
        }
        add_shortcode('roots-calendar-key-day','roots_calendar_key_day_func');

        function roots_calendar_key_list_func($atts){
            include_once( plugin_dir_path( __FILE__ ) . 'classes/calendar-public-view-list.php' );
            $calendar = new CalendarPublicViewList();
            ob_start();
            echo $calendar->rc_calset_form($atts['num'],$atts['listnum']);
            return ob_get_clean();
        }
        add_shortcode('roots-calendar-key-list','roots_calendar_key_list_func');

        /* 
        js style読み込み
        ---------------------------------------------- */
        function rc_load_editor_scripts() {
            wp_enqueue_script(
                'editor_script',
                plugins_url( '', __FILE__ ) . '/editor.js',
            );
        }
        add_action('admin_print_footer_scripts','rc_load_editor_scripts');

        function rc_load_public_scripts() {
            wp_enqueue_script(
                'editor_script',
                plugins_url( '', __FILE__ ) . '/script.js',
                null,
                null,
                true
            );
        }
        add_action('wp_enqueue_scripts','rc_load_public_scripts');

        function rc_load_editor() {
            wp_enqueue_style(
                'editor_style',
                plugins_url( '', __FILE__ ) . '/editor.css',
            );
        }
        add_action('admin_print_styles','rc_load_editor');

        function rc_load_style() {
            wp_enqueue_style(
                'editor_style',
                plugins_url( '', __FILE__ ) . '/style.css',
            );
        }
        add_action('wp_enqueue_scripts','rc_load_style');

        /* 
        カレンダー設定画面表示
        ---------------------------------------------- */
        include_once( plugin_dir_path( __FILE__ ) . 'classes/calendar-setting-view.php' );
        new CalendarSettingView();

        /* 
        カレンダー設定画面表示
        ---------------------------------------------- */
        include_once( plugin_dir_path( __FILE__ ) . 'classes/calendar-option-view.php' );
        new CalendarOptionView();

        /* 
        カレンダー登録画面表示
        ---------------------------------------------- */
        include_once( plugin_dir_path( __FILE__ ) . 'classes/calendar-post-view.php' );
        new CalendarPostView();

    }

}

new RootsCalendar();