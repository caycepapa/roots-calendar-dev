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

            // setting add
            if(isset($_POST['event_name']) || isset($_POST['event_color'])){
                $wpdb->insert(
                    $table_name,
                    array(
                        'event_name' => $_POST['event_name'],
                        'event_color' => $_POST['event_color'],
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
                            array( 'id' =>  $cal_setting['event_id']) 
                        );
                    }else{
                        $wpdb->update(
                            $table_name,
                            array(
                                'event_name' => $cal_setting['event_name'],
                                'event_color' => $cal_setting['event_color'],
                            ),
                            array( 'id' =>  $cal_setting['event_id']) 
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
                    <input type="text" name="event_name" value="">
                    <input type="color" name="event_color" value="">
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
                                <input type="text" name="cal_setting[<?php echo $record['id'];?>][event_name]" value="<?php echo $record['event_name'];?>">
                                <input type="color" name="cal_setting[<?php echo $record['id'];?>][event_color]" value="<?php echo $record['event_color'];?>">
                                <input type="hidden" name="cal_setting[<?php echo $record['id'];?>][event_id]" value="<?php echo $record['id'];?>">
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

        /* 
        カレンダー登録画面
        ---------------------------------------------- */
        include_once( plugin_dir_path( __FILE__ ) . 'classes/calender.php' );
        new CalenderPostView();

    }

}

new RootsCalender();