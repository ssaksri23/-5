<?php
$sub_menu = "950300";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();
check_admin_token();

require_once G5_THEME_PATH.'/inc/settings.helper.php';

$tb_id = isset($_POST['tb_id']) ? (int) $_POST['tb_id'] : 0;
$slug = sql_real_escape_string(SMARTBIZ_SLUG);

$old_image = '';
if ($tb_id) {
    $row = sql_fetch(" select tb_image from g5_theme_banner where tb_id = '{$tb_id}' and tb_theme = '{$slug}' ");
    if ($row) $old_image = $row['tb_image'];
}
$tb_image = smartbiz_handle_upload('tb_image', $old_image);

$tb_title = isset($_POST['tb_title']) ? trim(clean_xss_tags($_POST['tb_title'], 0, 1)) : '';
$tb_link_url = isset($_POST['tb_link_url']) ? trim(clean_xss_tags($_POST['tb_link_url'], 0, 1)) : '';
$tb_position = (isset($_POST['tb_position']) && $_POST['tb_position'] === 'main_slide') ? 'main_slide' : 'main_slide';
$tb_order = isset($_POST['tb_order']) ? (int) $_POST['tb_order'] : 0;
$tb_use = isset($_POST['tb_use']) ? 1 : 0;

if ($tb_id) {
    sql_query(" update g5_theme_banner set
                    tb_title = '".sql_real_escape_string($tb_title)."',
                    tb_image = '".sql_real_escape_string($tb_image)."',
                    tb_link_url = '".sql_real_escape_string($tb_link_url)."',
                    tb_position = '".sql_real_escape_string($tb_position)."',
                    tb_order = {$tb_order},
                    tb_use = {$tb_use}
                where tb_id = '{$tb_id}' and tb_theme = '{$slug}' ");
} else {
    sql_query(" insert into g5_theme_banner
                    (tb_theme, tb_title, tb_image, tb_link_url, tb_position, tb_order, tb_use)
                values
                    ('{$slug}', '".sql_real_escape_string($tb_title)."', '".sql_real_escape_string($tb_image)."',
                     '".sql_real_escape_string($tb_link_url)."', '".sql_real_escape_string($tb_position)."', {$tb_order}, {$tb_use}) ");
}

goto_url('./theme_banner_list.php');
