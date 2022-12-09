<?php
/*
Plugin Name: roots-calender
Description: rootsのカレンダー
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'roots_calender_init' );

/* 
カレンダー設定画面
---------------------------------------------- */
function roots_calender_init() {
	add_submenu_page('edit.php?post_type=roots_cal', 'My Plugin Options', '設定', 'manage_options', 'roots-calender', 'rc_top_view' );
}

function rc_top_view() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>オプション用のフォームをここに表示する。</p>';
	echo '</div>';
}

/* 
カレンダーメニュー表示
---------------------------------------------- */
function rc_add_page() {
    register_post_type( 'roots_cal',
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
        'roots_cal',
        'normal',
        'default',
    );
}
add_action('admin_menu', 'rc_create_custom_fields');

function rc_calset_form($post){
    //nounceフィールドの追加
    wp_nonce_field('custom_field_save_meta_box_data', 'custom_field_meta_box_nonce');

    //すでに保存されているデータを取得
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