<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// SMARTBIZ_SLUG / SMARTBIZ_UPLOAD_DIR / SMARTBIZ_UPLOAD_PATH / SMARTBIZ_UPLOAD_URL 는
// www/extend/smartbiz-theme.extend.php 에서 정의된다(common.php가 모든 요청에서
// 이 테마 파일들보다 먼저 자동 include 하므로 항상 이 시점엔 이미 정의되어 있다).
// 여기서는 방어적으로만 폴백을 둔다(테마가 통째로 재사용되어 extend 파일 이름이
// 바뀌는 등의 예외적인 경우 대비).
if (!defined('SMARTBIZ_SLUG')) define('SMARTBIZ_SLUG', 'smartbiz');
if (!defined('SMARTBIZ_UPLOAD_DIR')) define('SMARTBIZ_UPLOAD_DIR', 'theme_'.SMARTBIZ_SLUG);
if (!defined('SMARTBIZ_UPLOAD_PATH')) define('SMARTBIZ_UPLOAD_PATH', G5_DATA_PATH.'/'.SMARTBIZ_UPLOAD_DIR);
if (!defined('SMARTBIZ_UPLOAD_URL')) define('SMARTBIZ_UPLOAD_URL', G5_DATA_URL.'/'.SMARTBIZ_UPLOAD_DIR);

/**
 * 업로드된 테마 이미지(로고/대표이미지/배너 등)의 공개 URL을 반환한다.
 * 값이 비어있으면 빈 문자열을 반환한다(호출부에서 존재 여부로 분기).
 */
function smartbiz_upload_url($filename)
{
    if (!$filename) return '';
    return SMARTBIZ_UPLOAD_URL.'/'.rawurlencode($filename);
}

/**
 * g5_theme_company 는 테마당 1행만 사용한다. 테이블/행이 아직 없어도
 * (설치 직후, extend 자동로드가 아직 테이블을 만들기 전 등) 항상 안전한
 * 기본값 배열을 반환해 프런트 렌더링이 절대 깨지지 않게 한다.
 */
function smartbiz_get_company()
{
    static $cache = null;
    if ($cache !== null) return $cache;

    $defaults = array(
        'tc_company_name' => '', 'tc_logo' => '', 'tc_hero_image' => '',
        'tc_headline' => '', 'tc_subheadline' => '',
        'tc_phone' => '', 'tc_email' => '', 'tc_address' => '',
        'tc_kakao_url' => '', 'tc_naver_blog_url' => '',
        'tc_color_main' => '#1f3a68', 'tc_color_sub' => '#e8622c',
        'tc_about_text' => '', 'tc_footer_biz_info' => '',
        'tc_seo_title' => '', 'tc_seo_description' => '',
    );

    if (!function_exists('sql_fetch') || !smartbiz_table_exists('g5_theme_company')) {
        $cache = $defaults;
        return $cache;
    }

    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    $row = sql_fetch(" select * from g5_theme_company where tc_theme = '{$slug}' order by tc_id asc limit 1 ");
    if (!$row) {
        $cache = $defaults;
        return $cache;
    }

    $cache = array_merge($defaults, $row);
    return $cache;
}

/**
 * g5_theme_service 목록(노출된 것만, 순서대로).
 * $type: 'service'(주요 서비스소개) | 'process'(이용절차) | 'why_us'(선택 이유) — 세 섹션이
 * 아이콘+제목+설명+순서 구조가 동일해 ts_type 컬럼으로 한 테이블을 공유한다.
 */
function smartbiz_get_services($type = 'service')
{
    if (!function_exists('sql_query') || !smartbiz_table_exists('g5_theme_service')) return array();
    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    $type_esc = sql_real_escape_string($type);
    $rows = array();
    $result = sql_query(" select * from g5_theme_service where ts_theme = '{$slug}' and ts_type = '{$type_esc}' and ts_use = 1 order by ts_order asc, ts_id asc ");
    while ($row = sql_fetch_array($result)) $rows[] = $row;
    return $rows;
}

/**
 * g5_theme_section 노출/순서 설정을 key => row 배열로 반환한다.
 * 행이 없는 섹션 키는 "기본 노출, 순서는 기본값" 취급하도록 호출부(section.render.php)에서 처리한다.
 */
function smartbiz_get_sections()
{
    if (!function_exists('sql_query') || !smartbiz_table_exists('g5_theme_section')) return array();
    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    $rows = array();
    $result = sql_query(" select * from g5_theme_section where se_theme = '{$slug}' ");
    while ($row = sql_fetch_array($result)) $rows[$row['se_key']] = $row;
    return $rows;
}

/** g5_theme_banner 목록(위치별, 노출된 것만, 순서대로) */
function smartbiz_get_banners($position = '')
{
    if (!function_exists('sql_query') || !smartbiz_table_exists('g5_theme_banner')) return array();
    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    $where = " tb_theme = '{$slug}' and tb_use = 1 ";
    if ($position) $where .= " and tb_position = '".sql_real_escape_string($position)."' ";
    $rows = array();
    $result = sql_query(" select * from g5_theme_banner where {$where} order by tb_order asc, tb_id asc ");
    while ($row = sql_fetch_array($result)) $rows[] = $row;
    return $rows;
}

/**
 * 특정 게시판(bo_table)의 최신글을 간단히 가져온다. 게시판이 아직 만들어지지
 * 않았어도(설치 마법사 실행 전) 빈 배열을 반환해 메인페이지가 절대 깨지지 않는다.
 * 비밀글(wr_option에 'secret' 포함)은 메인페이지 미리보기에서 제외한다.
 */
function smartbiz_get_latest_posts($bo_table, $limit = 3)
{
    global $g5;
    if (!function_exists('sql_query')) return array();

    $write_prefix = isset($g5['write_prefix']) ? $g5['write_prefix'] : 'g5_write_';
    $bo_table_esc = preg_replace('/[^A-Za-z0-9_]/', '', $bo_table);
    $write_table = $write_prefix.$bo_table_esc;

    if (!smartbiz_table_exists($write_table)) return array();

    $limit = (int) $limit;
    $rows = array();
    $sql = " select wr_id, wr_subject, wr_content, wr_name, wr_datetime
             from `{$write_table}`
             where wr_is_comment = 0 and wr_option not like '%secret%'
             order by wr_num asc
             limit {$limit} ";
    $result = sql_query($sql, false);
    if (!$result) return array();

    while ($row = sql_fetch_array($result)) {
        $row['thumb_url'] = '';
        if (smartbiz_table_exists('g5_board_file')) {
            $bo_table_lit = sql_real_escape_string($bo_table_esc);
            $file = sql_fetch(" select bf_fileurl, bf_thumburl from g5_board_file
                                 where bo_table = '{$bo_table_lit}' and wr_id = '{$row['wr_id']}' and bf_no = 0 ", false);
            if ($file) $row['thumb_url'] = $file['bf_thumburl'] ?: $file['bf_fileurl'];
        }
        $rows[] = $row;
    }
    return $rows;
}

/** information_schema 조회 없이 가벼운 존재 확인 (연결마다 반복 호출되므로 요청당 1회 캐시) */
function smartbiz_table_exists($table)
{
    static $checked = array();
    if (array_key_exists($table, $checked)) return $checked[$table];

    $result = sql_query("show tables like '".sql_real_escape_string($table)."'", false);
    $exists = $result && sql_fetch_array($result);
    $checked[$table] = (bool) $exists;
    return $checked[$table];
}
