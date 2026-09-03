<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

require_once(G5_THEME_PATH.'/inc/settings.helper.php');
$tc = smartbiz_get_company();

$g5_debug['php']['begin_time'] = $begin_time = get_microtime();

if (!isset($g5['title'])) {
    $g5['title'] = $config['cf_title'];
    $g5_head_title = $g5['title'];
} else {
    $g5_head_title = implode(' | ', array_filter(array($g5['title'], $config['cf_title'])));
}
$g5['title'] = strip_tags($g5['title']);
$g5_head_title = strip_tags($g5_head_title);

// 현재접속자(g5_login) 기록에 쓰인다 — 값이 없으면 core에서 "Undefined array
// key" 경고가 발생하므로 기본 테마 관례(head.sub.php)와 동일하게 채워준다.
$g5['lo_location'] = addslashes($g5['title']);
if (!$g5['lo_location']) {
    $g5['lo_location'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
}
$g5['lo_url'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
if (strstr($g5['lo_url'], '/'.G5_ADMIN_DIR.'/') || $is_admin == 'super') {
    $g5['lo_url'] = '';
}

add_javascript('<script src="'.G5_JS_URL.'/jquery-1.12.4.min.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery-migrate-1.4.1.min.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/common.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/wrest.js"></script>', 0);
add_javascript('<script src="'.G5_THEME_JS_URL.'/theme.js"></script>', 1);
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php if ($tc['tc_seo_description']) { ?>
<meta name="description" content="<?php echo get_text($tc['tc_seo_description']); ?>">
<?php } ?>
<?php if ($config['cf_add_meta']) echo $config['cf_add_meta'].PHP_EOL; ?>
<title><?php echo get_text($tc['tc_seo_title'] ?: $g5_head_title); ?></title>
<link rel="stylesheet" href="<?php echo get_versioned_asset_url(G5_THEME_CSS_URL.'/default.css'); ?>">
<?php if ($tc['tc_color_main'] || $tc['tc_color_sub']) { ?>
<style>
:root{
    --sb-main: <?php echo get_text($tc['tc_color_main'] ?: '#1f3a68'); ?>;
    --sb-sub: <?php echo get_text($tc['tc_color_sub'] ?: '#e8622c'); ?>;
}
</style>
<?php } ?>
<script>
var g5_url       = "<?php echo G5_URL ?>";
var g5_bbs_url   = "<?php echo G5_BBS_URL ?>";
var g5_is_member = "<?php echo isset($is_member) ? $is_member : ''; ?>";
var g5_is_admin  = "<?php echo isset($is_admin) ? $is_admin : ''; ?>";
var g5_is_mobile = "<?php echo G5_IS_MOBILE ?>";
var g5_bo_table  = "<?php echo isset($bo_table) ? $bo_table : ''; ?>";
var g5_editor    = "<?php echo ($config['cf_editor'] && isset($board['bo_use_dhtml_editor']) && $board['bo_use_dhtml_editor']) ? $config['cf_editor'] : ''; ?>";
var g5_cookie_domain = "<?php echo G5_COOKIE_DOMAIN ?>";
</script>
<?php if (!defined('G5_IS_ADMIN')) echo $config['cf_add_script']; ?>
</head>
<body>
<a href="#sb_content" class="sb_skip">본문 바로가기</a>

<header id="sb_header">
    <div class="sb_header_inner">
        <a href="<?php echo G5_URL ?>" class="sb_logo">
            <?php if ($tc['tc_logo']) { ?>
                <img src="<?php echo smartbiz_upload_url($tc['tc_logo']); ?>" alt="<?php echo get_text($tc['tc_company_name']); ?>">
            <?php } else { ?>
                <span class="sb_logo_text"><?php echo get_text($tc['tc_company_name'] ?: $config['cf_title']); ?></span>
            <?php } ?>
        </a>

        <nav id="sb_gnb" aria-label="주메뉴">
            <ul>
                <?php
                $sb_menu = get_menu_db(0, true);
                foreach ((array) $sb_menu as $row) {
                    if (empty($row)) continue;
                    echo '<li><a href="'.$row['me_link'].'" target="_'.$row['me_target'].'">'.get_text($row['me_name']).'</a></li>'.PHP_EOL;
                }
                if (empty($sb_menu) && $is_admin) {
                    echo '<li class="sb_menu_empty"><a href="'.G5_ADMIN_URL.'/menu_list.php">메뉴 준비 중 (관리자 &gt; 메뉴설정)</a></li>';
                }
                ?>
            </ul>
        </nav>

        <ul class="sb_header_actions">
            <?php if ($is_member) { ?>
                <li><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
                <?php if ($is_admin) { ?>
                <li><a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>">관리자</a></li>
                <?php } ?>
            <?php } else { ?>
                <li><a href="<?php echo G5_BBS_URL ?>/login.php">로그인</a></li>
            <?php } ?>
        </ul>

        <button type="button" id="sb_menu_btn" aria-expanded="false" aria-controls="sb_gnb">
            <span></span><span></span><span></span>
            <span class="sb_sound_only">메뉴 열기</span>
        </button>
    </div>
</header>

<main id="sb_content">
