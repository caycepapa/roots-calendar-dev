<?php

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

        $rc_date01 = get_post_meta($post->ID, 'rc_date01', true);

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
        
        
        $date_str = $this_year.'-'.$this_month.'-01';
        echo $date_str;
        $this_day   = date('t', strtotime($date_str));

        ?>
            <h1><?php echo $this_month;?>月</h1>
            <p>今月は<?php echo $this_day;?></p>
            <input type="date" name="rc_date01" value="<?php echo $rc_date01; ?>">
            <table>
                <tr>
                    <th>日付</th>
                    <th>タイプ</th>
                    <th>表示テキスト</th>
                    <th>URL</th>
                </tr>
                <?php for($i = 1; $i < $this_day; $i++):?>
                <tr>
                    <td>
                        <?php echo $this_month;?>/<?php echo $i;?>
                    </td>
                    <td>
                        <select name="data[type]">
                            <option value="イベント">イベント</option>
                        </select>
                    </td>
                    <td>
                        <input name="data[text]" type="text" name="text">
                    </td>
                    <td>
                        <input type="text" name="data[link]">
                        <input type="hidden" name="rc_date_<?php echo $this_year.'-'.$this_month.'-'.$i;?>" value="{type:'イベント',text:'テキストです',url:'https://roots.run'}">
                    </td>
                </tr>
                <?php endfor;?>
            </table>
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

        if (isset($_POST['rc_date_2022-2-1'])) {
            $data = sanitize_text_field($_POST['rc_date_2022-2-1']);
            update_post_meta($post_id, 'rc_date_2022-2-1', $data);
        }

    }

}
