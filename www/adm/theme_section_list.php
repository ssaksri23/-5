<?php
$sub_menu = "950200";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();

require_once G5_THEME_PATH.'/inc/settings.helper.php';

$section_labels = array(
    'slide' => '메인 슬라이드', 'services' => '주요 서비스 소개', 'about' => '회사소개',
    'process' => '이용 절차', 'why_us' => '고객이 선택하는 이유', 'portfolio' => '실적/포트폴리오',
    'testimonials' => '고객 후기', 'faq' => '자주 묻는 질문', 'contact' => '온라인 문의',
    'map' => '오시는 길',
);

$configured = smartbiz_get_sections();
$rows = array();
foreach ($section_labels as $key => $label) {
    $rows[$key] = isset($configured[$key])
        ? array('order' => (int) $configured[$key]['se_order'], 'use' => (bool) $configured[$key]['se_use'])
        : array('order' => 0, 'use' => true);
}
uasort($rows, function ($a, $b) { return $a['order'] <=> $b['order']; });

$token = get_admin_token();

$g5['title'] = "테마 설정 - 섹션 노출/순서";
include_once('./admin.head.php');
?>
<p class="win_msg">숫자가 작을수록 메인페이지 위쪽에 표시됩니다. 상단 로고/메뉴와 하단 회사정보(푸터)는 항상 표시되며 이 목록에는 없습니다.</p>
<form name="fsmartbizsection" method="post" action="./theme_section_update.php" autocomplete="off">
<input type="hidden" name="token" value="<?php echo $token; ?>">
<div class="tbl_head01 tbl_wrap">
<table>
<caption>섹션 노출/순서</caption>
<thead>
<tr>
    <th scope="col">섹션</th>
    <th scope="col">노출</th>
    <th scope="col">순서</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $key => $row) { ?>
<tr>
    <td><?php echo get_sanitize_input($section_labels[$key]); ?></td>
    <td><input type="checkbox" name="se_use[<?php echo $key; ?>]" value="1" <?php echo $row['use'] ? 'checked' : ''; ?>></td>
    <td><input type="text" name="se_order[<?php echo $key; ?>]" value="<?php echo $row['order']; ?>" class="frm_input" size="4"></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<div class="btn_confirm">
    <button type="submit" id="btn_submit" class="btn_submit">저장하기</button>
</div>
</form>
<?php
include_once('./admin.tail.php');
