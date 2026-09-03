<?php
$sub_menu = "950200";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();
check_admin_token();

require_once G5_THEME_PATH.'/inc/settings.helper.php';

$valid_keys = array('slide','services','about','process','why_us','portfolio','testimonials','faq','contact','map');
$se_use = isset($_POST['se_use']) && is_array($_POST['se_use']) ? $_POST['se_use'] : array();
$se_order = isset($_POST['se_order']) && is_array($_POST['se_order']) ? $_POST['se_order'] : array();

$slug = sql_real_escape_string(SMARTBIZ_SLUG);

foreach ($valid_keys as $key) {
    $use = isset($se_use[$key]) ? 1 : 0;
    $order = isset($se_order[$key]) ? (int) $se_order[$key] : 0;
    $key_esc = sql_real_escape_string($key);

    // 행이 있으면 갱신, 없으면 새로 삽입 (se_theme+se_key UNIQUE 이므로 안전)
    $exists = sql_fetch(" select se_id from g5_theme_section where se_theme = '{$slug}' and se_key = '{$key_esc}' ");
    if ($exists) {
        sql_query(" update g5_theme_section set se_use = {$use}, se_order = {$order} where se_id = '{$exists['se_id']}' ");
    } else {
        sql_query(" insert into g5_theme_section (se_theme, se_key, se_order, se_use) values ('{$slug}', '{$key_esc}', {$order}, {$use}) ");
    }
}

goto_url('./theme_section_list.php');
