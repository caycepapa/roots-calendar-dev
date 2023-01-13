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

            // setting add
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

            // setting update
            if(isset($_POST['cal_setting'])){
                $cal_settings = $_POST['cal_setting'];
                foreach($cal_settings as $cal_setting){
                    if($cal_setting['delete']){
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

            // setting view
            $records = $wpdb->get_results("SELECT * FROM ".$table_name);
            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">カレンダー設定</h1>
                <hr class="wp-header-end">
                <h2>新規登録</h2>
                <form action='edit.php?post_type=<?php echo RC_Config::NAME;?>&page=<?php echo RC_Config::SETTING_NAME;?>' method='POST'>
                    <input type="text" name="state_name" value="">
                    <select name="state_mark">
                        <option value="◯">◯</option>
                        <option value="✕">✕</option>
                    </select>
                    <input type="color" name="state_color" value="">
                    <input type="text" name="state_txt">
                    <input type="submit" value="追加">
                </form>
                <h2>一覧</h2>
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
                                    <?php if($record['state_mark'] == '◯'): ?>
                                        <option value="◯" selected>◯</option>
                                    <?php else: ?>
                                        <option value="✕" selected>✕</option>
                                    <?php endif;?>
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
            <?php
        }

        /* 
        メニュー表示
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

        function rc_load_scripts() {
            wp_enqueue_script(
                'editor_script',
                plugins_url( '', __FILE__ ) . '/editor.js',
            );
        }
        add_action('admin_print_footer_scripts','rc_load_scripts');

        function rc_load_style() {
            wp_enqueue_style(
                'editor_style',
                plugins_url( '', __FILE__ ) . '/editor.css',
            );
        }
        add_action('admin_print_styles','rc_load_style');

        /* 
        カレンダー登録画面
        ---------------------------------------------- */
        include_once( plugin_dir_path( __FILE__ ) . 'classes/calender.php' );
        new CalenderPostView();

    }

}

new RootsCalender();