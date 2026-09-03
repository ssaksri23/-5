<?php
// 스마트비즈 테마 확장 파일. www/common.php 가 매 요청마다 자동으로
// include_once 하므로(등록 불필요), 코어 파일은 전혀 건드리지 않는다.
// 그누보드5 관례("common.php 파일을 수정할 필요가 없도록 확장합니다")를 그대로 따른다.
if (!defined('_GNUBOARD_')) exit;

if (!defined('SMARTBIZ_SLUG')) define('SMARTBIZ_SLUG', 'smartbiz');
if (!defined('SMARTBIZ_UPLOAD_DIR')) define('SMARTBIZ_UPLOAD_DIR', 'theme_'.SMARTBIZ_SLUG);
if (!defined('SMARTBIZ_UPLOAD_PATH')) define('SMARTBIZ_UPLOAD_PATH', G5_DATA_PATH.'/'.SMARTBIZ_UPLOAD_DIR);
if (!defined('SMARTBIZ_UPLOAD_URL')) define('SMARTBIZ_UPLOAD_URL', G5_DATA_URL.'/'.SMARTBIZ_UPLOAD_DIR);

// 테이블이 이미 있으면(대부분의 요청) 저렴한 존재 확인 쿼리 한 번으로 끝낸다.
// CREATE TABLE IF NOT EXISTS 자체는 멱등이지만, DDL 5개를 매 요청 실행하는
// 비용을 피하기 위해 캐노니컬 테이블 하나로만 먼저 확인한다.
$smartbiz_canary = sql_query("show tables like 'g5_theme_company'", false);
if (!$smartbiz_canary || !sql_fetch_array($smartbiz_canary)) {

    sql_query("
        CREATE TABLE IF NOT EXISTS `g5_theme_company` (
          `tc_id` int(11) NOT NULL AUTO_INCREMENT,
          `tc_theme` varchar(255) NOT NULL DEFAULT '',
          `tc_company_name` varchar(255) NOT NULL DEFAULT '',
          `tc_logo` varchar(255) NOT NULL DEFAULT '',
          `tc_hero_image` varchar(255) NOT NULL DEFAULT '',
          `tc_headline` varchar(255) NOT NULL DEFAULT '',
          `tc_subheadline` varchar(255) NOT NULL DEFAULT '',
          `tc_phone` varchar(50) NOT NULL DEFAULT '',
          `tc_email` varchar(100) NOT NULL DEFAULT '',
          `tc_address` varchar(255) NOT NULL DEFAULT '',
          `tc_kakao_url` varchar(255) NOT NULL DEFAULT '',
          `tc_naver_blog_url` varchar(255) NOT NULL DEFAULT '',
          `tc_color_main` varchar(20) NOT NULL DEFAULT '#1f3a68',
          `tc_color_sub` varchar(20) NOT NULL DEFAULT '#e8622c',
          `tc_about_text` text NOT NULL,
          `tc_footer_biz_info` text NOT NULL,
          `tc_seo_title` varchar(255) NOT NULL DEFAULT '',
          `tc_seo_description` varchar(500) NOT NULL DEFAULT '',
          `tc_updated_at` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
          PRIMARY KEY (`tc_id`), KEY `tc_theme` (`tc_theme`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ", true);

    // ts_type: 'service'(주요 서비스소개) | 'process'(이용절차) | 'why_us'(선택 이유)
    // 세 섹션 모두 아이콘+제목+설명+순서 구조가 동일해 한 테이블을 공유한다.
    sql_query("
        CREATE TABLE IF NOT EXISTS `g5_theme_service` (
          `ts_id` int(11) NOT NULL AUTO_INCREMENT,
          `ts_theme` varchar(255) NOT NULL DEFAULT '',
          `ts_type` varchar(20) NOT NULL DEFAULT 'service',
          `ts_title` varchar(255) NOT NULL DEFAULT '',
          `ts_description` text NOT NULL,
          `ts_icon` varchar(255) NOT NULL DEFAULT '',
          `ts_order` int(11) NOT NULL DEFAULT '0',
          `ts_use` tinyint(4) NOT NULL DEFAULT '1',
          PRIMARY KEY (`ts_id`), KEY `ts_theme_type_order` (`ts_theme`,`ts_type`,`ts_order`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ", true);

    sql_query("
        CREATE TABLE IF NOT EXISTS `g5_theme_section` (
          `se_id` int(11) NOT NULL AUTO_INCREMENT,
          `se_theme` varchar(255) NOT NULL DEFAULT '',
          `se_key` varchar(50) NOT NULL DEFAULT '',
          `se_order` int(11) NOT NULL DEFAULT '0',
          `se_use` tinyint(4) NOT NULL DEFAULT '1',
          PRIMARY KEY (`se_id`), UNIQUE KEY `se_theme_key` (`se_theme`,`se_key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ", true);

    sql_query("
        CREATE TABLE IF NOT EXISTS `g5_theme_banner` (
          `tb_id` int(11) NOT NULL AUTO_INCREMENT,
          `tb_theme` varchar(255) NOT NULL DEFAULT '',
          `tb_title` varchar(255) NOT NULL DEFAULT '',
          `tb_image` varchar(255) NOT NULL DEFAULT '',
          `tb_link_url` varchar(255) NOT NULL DEFAULT '',
          `tb_position` varchar(50) NOT NULL DEFAULT '',
          `tb_order` int(11) NOT NULL DEFAULT '0',
          `tb_use` tinyint(4) NOT NULL DEFAULT '1',
          PRIMARY KEY (`tb_id`), KEY `tb_theme_pos` (`tb_theme`,`tb_position`,`tb_use`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ", true);

    sql_query("
        CREATE TABLE IF NOT EXISTS `g5_theme_license` (
          `tl_id` int(11) NOT NULL AUTO_INCREMENT,
          `tl_theme` varchar(255) NOT NULL DEFAULT '',
          `tl_license_key` varchar(255) NOT NULL DEFAULT '',
          `tl_buyer_name` varchar(255) NOT NULL DEFAULT '',
          `tl_allowed_domain` varchar(255) NOT NULL DEFAULT '',
          `tl_purchase_date` date NOT NULL DEFAULT '1000-01-01',
          `tl_max_installs` int(11) NOT NULL DEFAULT '1',
          `tl_support_end_date` date NOT NULL DEFAULT '1000-01-01',
          `tl_status` varchar(20) NOT NULL DEFAULT 'active',
          `tl_last_checked_at` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
          `tl_note` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`tl_id`), KEY `tl_theme` (`tl_theme`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ", true);

    // 최초 생성 시에만 기본 회사정보 1행 + 섹션 기본 순서를 심어둔다.
    // (이미 있으면 절대 덮어쓰지 않음 — 이 블록은 g5_theme_company가 없을 때만 진입)
    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    sql_query(" insert into g5_theme_company (tc_theme, tc_updated_at) values ('{$slug}', now()) ");

    $default_sections = array(
        'slide' => 10, 'services' => 20, 'about' => 30, 'process' => 40,
        'why_us' => 50, 'portfolio' => 60, 'testimonials' => 70,
        'faq' => 80, 'contact' => 90, 'map' => 100,
    );
    foreach ($default_sections as $se_key => $se_order) {
        $se_key_esc = sql_real_escape_string($se_key);
        sql_query(" insert into g5_theme_section (se_theme, se_key, se_order, se_use) values ('{$slug}', '{$se_key_esc}', {$se_order}, 1) ");
    }
}
