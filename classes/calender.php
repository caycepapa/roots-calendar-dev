<?php

include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );

class CalenderPostView{

    function __construct(){
        add_action('admin_menu', array($this,'rc_create_custom_fields'));
        add_action('save_post', array($this,'save_custom_fields'));
    }
    
    function rc_create_custom_fields(){
        add_meta_box(
            'rc_calset',
            '休日・イベント設定',
            array($this,'rc_calset_form'),
            RC_Config::NAME,
            'normal',
            'default',
        );
    }
    
    function rc_calset_form($post){

        wp_nonce_field('custom_field_save_meta_box_data', 'custom_field_meta_box_nonce');

        global $wpdb;

        $sql = "SELECT * FROM $wpdb->postmeta WHERE post_id =".$post->ID." AND meta_key LIKE 'rc_date_%'";

        $rc_date = $wpdb->get_results($sql,OBJECT);

        echo $rc_date[0]->meta_value;

        $month  = '2';
        $year   = '2022';

        if($month == ''){
            $this_month = date("m");
        }else{
            $this_month = $month;
        }

        if($year == ''){
            $this_year  = date("Y");
        }else{
            $this_year = $year;
        }

        // その月の日数
        $this_day = date('t', strtotime($date_str));

        // setting
        $table_name = $wpdb->prefix . RC_Config::SETTING_TABLE;
        $records = $wpdb->get_results("SELECT * FROM ".$table_name);
        ?>
            <ul class="rc_statelist" name="rc_statelist">
            <?php foreach($records as $record): ?>
                    <li><?php echo $record->state_name;?></li>
            <?php endforeach;?>
            </ul>
            <h1><?php echo $this_month;?>月</h1>
            <div name="calContainer">
                <div name="calBox">
                </div>
            </div>
            <input type="hidden" name="rc_date_<?php echo $this_year.'-'.$this_month.'-';?>1-1" value="{type:'イベント',text:'テキストです',url:'https://roots.run'}">
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
        //var_dump($_POST);
        foreach($_POST as $key => $value){
            if(preg_match('/rc_date_/', $key)){
                $data = sanitize_text_field($_POST[$key]);
                update_post_meta($post_id, $key , $data);
            }
        }

    }

}
