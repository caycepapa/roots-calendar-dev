<?php

include_once( plugin_dir_path( __FILE__ ) . 'classes/config.php' );

class CalendarOptionView{

    public function __construct(){

        /* 
        カレンダー設定画面
        ---------------------------------------------- */
        function roots_option_calendar_init() {
            add_submenu_page(
                'edit.php?post_type='.RC_Config::NAME,
                RC_Config::NAME ,
                'オプション',
                'manage_options',
                RC_Config::OPTION_NAME ,
                'rc_option_view'
            );
        }

        add_action( 'admin_menu', 'roots_option_calendar_init' );

        function rc_option_view() {

            if ( !current_user_can( 'manage_options' ) )  {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            global $wpdb;
            $table_name = $wpdb->prefix . RC_Config::OPTION_TABLE;

            /* 
            追加または更新時実行
            ---------------------------------------------- */
            if($_POST['created']){
                $wpdb->update(
                    $table_name,
                    array(
                        'option_name' => $_POST['option_name'][0],
                        'option_value' => $_POST['option_value'][0]
                    ),
                    array( 'id' =>  $_POST['option_id'][0])
                );
            }else{
                $wpdb->insert(
                    $table_name,
                    array(
                        'option_name' => $_POST['option_name'][0],
                        'option_value' => $_POST['option_value'][0],
                    )
                );
            }

            /* 
            画面表示
            ---------------------------------------------- */
            $records = $wpdb->get_results("SELECT * FROM ".$table_name);

            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">カレンダーオプション</h1>
                <hr class="wp-header-end">
                <form action='edit.php?post_type=<?php echo RC_Config::NAME;?>&page=<?php echo RC_Config::OPTION_NAME;?>' method='POST'>
                    <?php
                        $howlong = $records[array_search('公開月数', array_column($records, 'option_name'))];
                    ?>
                    <?php
                        if(isset($howlong->option_value)):?>
                    <input type="hidden" name="created[0]" value="true">
                    <?php
                        else:
                    ?>
                    <input type="hidden" name="created[0]" value="false">
                    <?php
                        endif;
                    ?>
                    <input type="hidden" name="option_id[0]" value="1">
                    <input type="hidden" name="option_name[0]" value="公開月数">
                    公開月数： 
                    <select name="option_value[0]">
                        <?php
                            if(isset($howlong)){
                                for($i = 1; $i <= 12; $i++){
                                    if($howlong->option_value == $i){
                                        echo '<option value="'.$i.'" selected>'.$i.'</option>';
                                    }else{
                                        echo '<option value="'.$i.'">'.$i.'</option>';
                                    }
                                }
                            }else{
                                for($i = 1; $i < 12; $i++){
                                    if($i == 1){
                                        echo '<option value="'.$i.'" selected>'.$i.'</option>';
                                    }else{
                                        echo '<option value="'.$i.'">'.$i.'</option>';
                                    }
                                }
                            }
                        ?>
                    </select>
                    <input type="submit" value="更新">
                </form>
            </div>
            <?php
        }
    }
}