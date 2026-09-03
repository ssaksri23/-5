<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/**
 * 업종별 샘플 데이터(sample/<industry>/data.php)를 회사정보/서비스·절차·이유
 * 테이블에 채운다. 설치 마법사에서 "샘플 데이터 적용" 체크 시에만 호출된다.
 *
 * 안전 원칙: 이미 관리자가 입력한 내용이 있으면 절대 덮어쓰지 않고 건너뛴다.
 * - 회사정보: 회사명/헤드라인이 하나라도 채워져 있으면 스킵
 * - 서비스/절차/이유: 해당 타입(ts_type)에 이미 행이 하나라도 있으면 스킵
 * 반환값은 설치 마법사 화면에 그대로 보여줄 상태 요약 문자열이다.
 */
function smartbiz_apply_sample_data($industry)
{
    $industry = preg_replace('/[^a-z_]/', '', $industry);
    $file = G5_THEME_PATH.'/sample/'.$industry.'/data.php';
    if (!is_file($file)) return '샘플 파일 없음';

    $data = include $file;
    if (!is_array($data)) return '샘플 데이터 형식 오류';

    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    $company_status = '건너뜀(이미 입력됨)';

    if (!empty($data['company']) && is_array($data['company'])) {
        $existing = sql_fetch(" select * from g5_theme_company where tc_theme = '{$slug}' order by tc_id asc limit 1 ");
        $is_empty = !$existing || (!trim((string) $existing['tc_company_name']) && !trim((string) $existing['tc_headline']));

        if ($is_empty) {
            $cols = array(
                'tc_company_name', 'tc_headline', 'tc_subheadline', 'tc_phone', 'tc_email', 'tc_address',
                'tc_kakao_url', 'tc_naver_blog_url', 'tc_color_main', 'tc_color_sub',
                'tc_about_text', 'tc_footer_biz_info', 'tc_seo_title', 'tc_seo_description',
            );
            $sets = array();
            foreach ($cols as $col) {
                if (array_key_exists($col, $data['company'])) {
                    $sets[] = "`{$col}` = '".sql_real_escape_string($data['company'][$col])."'";
                }
            }
            if ($sets) {
                if ($existing) {
                    sql_query(" update g5_theme_company set ".implode(', ', $sets).", tc_updated_at = now() where tc_id = '{$existing['tc_id']}' ");
                } else {
                    sql_query(" insert into g5_theme_company (tc_theme, tc_updated_at) values ('{$slug}', now()) ");
                    $new_row = sql_fetch(" select tc_id from g5_theme_company where tc_theme = '{$slug}' order by tc_id desc limit 1 ");
                    if ($new_row) sql_query(" update g5_theme_company set ".implode(', ', $sets)." where tc_id = '{$new_row['tc_id']}' ");
                }
                $company_status = '적용됨';
            }
        }
    }

    $type_map = array('services' => 'service', 'process' => 'process', 'why_us' => 'why_us');
    $service_parts = array();

    foreach ($type_map as $data_key => $ts_type) {
        if (empty($data[$data_key]) || !is_array($data[$data_key])) continue;

        $type_esc = sql_real_escape_string($ts_type);
        $count_row = sql_fetch(" select count(*) as cnt from g5_theme_service where ts_theme = '{$slug}' and ts_type = '{$type_esc}' ");
        if ($count_row && (int) $count_row['cnt'] > 0) {
            $service_parts[] = $ts_type.'(건너뜀)';
            continue;
        }

        $order = 10;
        foreach ($data[$data_key] as $item) {
            $title = sql_real_escape_string($item['ts_title'] ?? '');
            $desc  = sql_real_escape_string($item['ts_description'] ?? '');
            $icon  = sql_real_escape_string($item['ts_icon'] ?? '');
            sql_query(" insert into g5_theme_service (ts_theme, ts_type, ts_title, ts_description, ts_icon, ts_order, ts_use)
                        values ('{$slug}', '{$type_esc}', '{$title}', '{$desc}', '{$icon}', {$order}, 1) ");
            $order += 10;
        }
        $service_parts[] = $ts_type.'(적용됨)';
    }

    return '회사정보: '.$company_status.($service_parts ? ' / '.implode(', ', $service_parts) : '');
}
