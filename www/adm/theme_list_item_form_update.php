<?php
$sub_menu = "950400";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();
check_admin_token();

require_once G5_THEME_PATH.'/inc/settings.helper.php';

$valid_types = array('service', 'process', 'why_us');
$ts_id = isset($_POST['ts_id']) ? (int) $_POST['ts_id'] : 0;
$slug = sql_real_escape_string(SMARTBIZ_SLUG);

$old_icon = '';
if ($ts_id) {
    $row = sql_fetch(" select ts_icon from g5_theme_service where ts_id = '{$ts_id}' and ts_theme = '{$slug}' ");
    if ($row) $old_icon = $row['ts_icon'];
}
$ts_icon = smartbiz_handle_upload('ts_icon', $old_icon);

$ts_type = (isset($_POST['ts_type']) && in_array($_POST['ts_type'], $valid_types, true)) ? $_POST['ts_type'] : 'service';
$ts_title = isset($_POST['ts_title']) ? trim(clean_xss_tags($_POST['ts_title'], 0, 1)) : '';
$ts_description = isset($_POST['ts_description']) ? trim(clean_xss_tags($_POST['ts_description'], 0, 1, 0, 0)) : '';
$ts_order = isset($_POST['ts_order']) ? (int) $_POST['ts_order'] : 0;
$ts_use = isset($_POST['ts_use']) ? 1 : 0;

if ($ts_id) {
    sql_query(" update g5_theme_service set
                    ts_type = '".sql_real_escape_string($ts_type)."',
                    ts_title = '".sql_real_escape_string($ts_title)."',
                    ts_description = '".sql_real_escape_string($ts_description)."',
                    ts_icon = '".sql_real_escape_string($ts_icon)."',
                    ts_order = {$ts_order},
                    ts_use = {$ts_use}
                where ts_id = '{$ts_id}' and ts_theme = '{$slug}' ");
} else {
    sql_query(" insert into g5_theme_service
                    (ts_theme, ts_type, ts_title, ts_description, ts_icon, ts_order, ts_use)
                values
                    ('{$slug}', '".sql_real_escape_string($ts_type)."', '".sql_real_escape_string($ts_title)."',
                     '".sql_real_escape_string($ts_description)."', '".sql_real_escape_string($ts_icon)."', {$ts_order}, {$ts_use}) ");
}

goto_url('./theme_list_item_list.php?ts_type='.$ts_type);
