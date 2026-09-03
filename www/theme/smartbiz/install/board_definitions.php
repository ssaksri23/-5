<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 스마트비즈 테마가 필요로 하는 게시판 정의. 설치 마법사(theme_install_wizard.php)가
// 이 배열을 순회하며 smartbiz_create_or_update_board() 를 호출한다.
//
// notice/gallery 는 그누보드5 "최소설치"가 기본으로 만들어두는 게시판과 테이블명이
// 같다 — 이미 있으면 그 게시판(과 기존 글)은 그대로 두고 스킨/옵션만 우리 테마 것으로
// 맞춰 바꾼다(요청사항: 기존 게시판/데이터를 임의로 삭제·덮어쓰지 않음).
function smartbiz_board_definitions()
{
    return array(
        'notice' => array(
            'bo_subject' => '공지사항',
            'skin' => 'notice',
            'bo_use_secret' => 0,
            'writable_by_guest' => false, // 관리자만 작성 (write.skin.php 에서도 이중 체크)
        ),
        'inquiry' => array(
            'bo_subject' => '온라인문의',
            'skin' => 'inquiry',
            // 2 = "무조건" — bbs/write_update.php 가 관리자가 아닌 글쓴이의 글을
            // 예외 없이 비밀글로 강제 저장한다(요청 위조로도 우회 불가, 코어 로직).
            'bo_use_secret' => 2,
            'writable_by_guest' => true,
        ),
        'portfolio' => array(
            'bo_subject' => '포트폴리오',
            'skin' => 'portfolio',
            'bo_use_secret' => 0,
            'writable_by_guest' => false,
            'upload' => true,
        ),
        'gallery' => array(
            'bo_subject' => '갤러리',
            'skin' => 'gallery',
            'bo_use_secret' => 0,
            'writable_by_guest' => false,
            'upload' => true,
        ),
        'review' => array(
            'bo_subject' => '고객후기',
            'skin' => 'review',
            'bo_use_secret' => 0,
            'writable_by_guest' => true,
        ),
    );
}

/**
 * 게시판이 없으면 새로 만들고, 이미 있으면 스킨/옵션만 스마트비즈 값으로 맞춘다.
 * 기존 게시글(g5_write_*)은 절대 건드리지 않는다. 반환값은 상태 문자열
 * ('created'|'updated'|'skipped') — 설치 마법사 화면에 그대로 보여준다.
 */
function smartbiz_create_or_update_board($bo_table, $def)
{
    global $g5;

    $bo_table = preg_replace('/[^a-z0-9_]/i', '', $bo_table);
    if (!$bo_table) return 'skipped';

    $existing = sql_fetch(" select bo_table from {$g5['board_table']} where bo_table = '{$bo_table}' ");

    // 코어 get_skin_path('board', $bo_skin) 은 'theme/<스킨폴더명>' 형태를 기대한다
    // (basic 테마의 실제 관례와 동일) — 우리 스킨은 www/theme/smartbiz/skin/board/<스킨명>/ 에 있다.
    $skin_value = 'theme/'.$def['skin'];

    if ($existing) {
        sql_query(" update {$g5['board_table']} set
                        bo_skin = '".sql_real_escape_string($skin_value)."',
                        bo_mobile_skin = '".sql_real_escape_string($skin_value)."',
                        bo_use_secret = '".(int) $def['bo_use_secret']."'
                    where bo_table = '{$bo_table}' ");
        return 'updated';
    }

    $bo_subject = sql_real_escape_string($def['bo_subject']);
    $write_level = !empty($def['writable_by_guest']) ? 1 : 10; // 1=회원, 10=최고관리자만 (관리자전용 게시판)
    $upload_count = !empty($def['upload']) ? 5 : 1;
    $upload_size = !empty($def['upload']) ? 5242880 : 1048576; // 업로드형 5MB, 일반 1MB

    sql_query(" insert into {$g5['board_table']}
        (bo_table, gr_id, bo_subject, bo_mobile_subject, bo_skin, bo_mobile_skin,
         bo_use_secret, bo_use_dhtml_editor, bo_write_level, bo_read_level, bo_list_level,
         bo_upload_count, bo_upload_size, bo_page_rows, bo_mobile_page_rows,
         bo_category_list, bo_content_head, bo_mobile_content_head,
         bo_content_tail, bo_mobile_content_tail, bo_insert_content, bo_notice,
         bo_1_subj, bo_include_head, bo_include_tail)
        values
        ('{$bo_table}', 'community', '{$bo_subject}', '{$bo_subject}', '".sql_real_escape_string($skin_value)."', '".sql_real_escape_string($skin_value)."',
         '".(int) $def['bo_use_secret']."', 1, {$write_level}, 1, 1,
         {$upload_count}, {$upload_size}, 15, 15,
         '', '', '', '', '', '', '',
         '', '_head.php', '_tail.php') ");

    // 게시판 테이블 생성 (adm/board_form_update.php 와 동일한 방식: sql_write.sql 템플릿 치환)
    $write_table = $g5['write_prefix'].$bo_table;
    $sql_lines = file(G5_ADMIN_PATH.'/sql_write.sql');
    $sql_write = str_replace('__TABLE_NAME__', $write_table, implode('', $sql_lines));
    foreach (explode(';', $sql_write) as $sql_piece) {
        $sql_piece = trim($sql_piece);
        if ($sql_piece) sql_query($sql_piece, true);
    }

    return 'created';
}

/**
 * 게시판을 메인 메뉴(g5_menu)에 등록한다. 이미 같은 링크의 메뉴가 있으면
 * 건드리지 않는다(중복 등록 방지, 관리자가 메뉴를 직접 수정했어도 보존).
 */
function smartbiz_register_menu($bo_table, $me_name, $me_order)
{
    global $g5;
    $me_link = '/bbs/board.php?bo_table='.$bo_table;
    $existing = sql_fetch(" select me_id from g5_menu where me_link = '".sql_real_escape_string($me_link)."' ");
    if ($existing) return 'skipped';

    // 그누보드5 코어는 최상위 메뉴를 length(me_code) = 2 조건으로 조회한다
    // (lib/get_data.lib.php get_menu_db()) — 반드시 정확히 2자리여야 목록에 뜬다.
    // 기존에 쓰인 숫자형 2자리 코드 중 가장 큰 값 다음 번호를 사용해 충돌을 피한다.
    $used = sql_fetch(" select max(cast(me_code as unsigned)) as max_code from g5_menu where length(me_code) = 2 and me_code regexp '^[0-9]{2}$' ");
    $next = ($used && $used['max_code'] !== null) ? ((int) $used['max_code'] + 1) : 1;
    $me_code = str_pad((string) $next, 2, '0', STR_PAD_LEFT);
    if (strlen($me_code) !== 2) return 'skipped'; // 100개 이상이면(사실상 없음) 안전하게 건너뜀

    sql_query(" insert into g5_menu (me_code, me_name, me_link, me_target, me_order, me_use, me_mobile_use)
                values ('".sql_real_escape_string($me_code)."',
                        '".sql_real_escape_string($me_name)."', '".sql_real_escape_string($me_link)."',
                        'self', {$me_order}, 1, 1) ");
    return 'created';
}
