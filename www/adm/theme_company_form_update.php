<?php
$sub_menu = "950100";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();
check_admin_token();

require_once G5_THEME_PATH.'/inc/settings.helper.php';
$tc = smartbiz_get_company();

function sb_post($key, $multiline = false)
{
    if (!isset($_POST[$key])) return '';
    // 저장 시점에 태그 제거(방어), 출력 시점에도 get_text()로 다시 이스케이프한다(이중 방어).
    // 여러 줄 입력(회사소개 등)은 trim_both=0 으로 줄바꿈을 보존한다.
    return trim(clean_xss_tags($_POST[$key], 0, 1, 0, $multiline ? 0 : 1));
}

$tc_logo = smartbiz_handle_upload('tc_logo', $tc['tc_logo']);
$tc_hero_image = smartbiz_handle_upload('tc_hero_image', $tc['tc_hero_image']);

$fields = array(
    'tc_company_name'    => sb_post('tc_company_name'),
    'tc_headline'         => sb_post('tc_headline'),
    'tc_subheadline'      => sb_post('tc_subheadline'),
    'tc_phone'            => sb_post('tc_phone'),
    'tc_email'            => sb_post('tc_email'),
    'tc_address'          => sb_post('tc_address'),
    'tc_kakao_url'        => sb_post('tc_kakao_url'),
    'tc_naver_blog_url'   => sb_post('tc_naver_blog_url'),
    'tc_color_main'       => preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['tc_color_main'] ?? '') ? $_POST['tc_color_main'] : '#1f3a68',
    'tc_color_sub'        => preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['tc_color_sub'] ?? '') ? $_POST['tc_color_sub'] : '#e8622c',
    'tc_about_text'       => sb_post('tc_about_text', true),
    'tc_footer_biz_info'  => sb_post('tc_footer_biz_info', true),
    'tc_seo_title'        => sb_post('tc_seo_title'),
    'tc_seo_description'  => sb_post('tc_seo_description', true),
    'tc_logo'             => $tc_logo,
    'tc_hero_image'       => $tc_hero_image,
);

$sets = array();
foreach ($fields as $col => $val) {
    $sets[] = "`{$col}` = '".sql_real_escape_string($val)."'";
}
$sets[] = "tc_updated_at = now()";

$slug = sql_real_escape_string(SMARTBIZ_SLUG);
sql_query(" update g5_theme_company set ".implode(', ', $sets)." where tc_theme = '{$slug}' ");

goto_url('./theme_company_form.php');
