<?php
$sub_menu = "950400";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();

require_once G5_THEME_PATH.'/inc/settings.helper.php';

$type_labels = array('service' => '주요 서비스', 'process' => '이용 절차', 'why_us' => '선택하는 이유');
$ts_type = isset($_GET['ts_type']) && isset($type_labels[$_GET['ts_type']]) ? $_GET['ts_type'] : 'service';

// 삭제 처리
if (isset($_GET['ts_id']) && $_GET['ts_id'] && isset($_GET['token'])) {
    check_admin_token();
    $ts_id = (int) $_GET['ts_id'];
    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    sql_query(" delete from g5_theme_service where ts_id = '{$ts_id}' and ts_theme = '{$slug}' ");
    goto_url('./theme_list_item_list.php?ts_type='.$ts_type);
}

$slug = sql_real_escape_string(SMARTBIZ_SLUG);
$type_esc = sql_real_escape_string($ts_type);
$items = array();
$result = sql_query(" select * from g5_theme_service where ts_theme = '{$slug}' and ts_type = '{$type_esc}' order by ts_order asc, ts_id asc ");
while ($row = sql_fetch_array($result)) $items[] = $row;

$token = get_admin_token();

$g5['title'] = "테마 설정 - 서비스/절차/이유";
include_once('./admin.head.php');
?>
<ul class="tab">
<?php foreach ($type_labels as $key => $label) { ?>
    <li><a href="./theme_list_item_list.php?ts_type=<?php echo $key; ?>" <?php echo $key == $ts_type ? 'class="on"' : ''; ?>><?php echo $label; ?></a></li>
<?php } ?>
</ul>
<p class="win_msg">"주요 서비스", "이용 절차", "고객이 선택하는 이유" 세 섹션은 같은 형태(아이콘+제목+설명)라 이 화면에서 함께 관리합니다.</p>
<div class="btn_confirm">
    <a href="./theme_list_item_form.php?ts_type=<?php echo $ts_type; ?>" class="btn btn_02">항목 추가</a>
</div>
<div class="tbl_head01 tbl_wrap">
<table>
<caption><?php echo $type_labels[$ts_type]; ?> 목록</caption>
<thead>
<tr><th>아이콘</th><th>제목</th><th>순서</th><th>노출</th><th>관리</th></tr>
</thead>
<tbody>
<?php if (!$items) { ?>
<tr><td colspan="5">등록된 항목이 없습니다.</td></tr>
<?php } ?>
<?php foreach ($items as $it) { ?>
<tr>
    <td><?php if ($it['ts_icon']) { ?><img src="<?php echo smartbiz_upload_url($it['ts_icon']); ?>" alt="" style="max-height:30px"><?php } ?></td>
    <td><?php echo get_sanitize_input($it['ts_title']); ?></td>
    <td><?php echo (int) $it['ts_order']; ?></td>
    <td><?php echo $it['ts_use'] ? '노출' : '숨김'; ?></td>
    <td>
        <a href="./theme_list_item_form.php?ts_id=<?php echo $it['ts_id']; ?>">수정</a>
        <a href="./theme_list_item_list.php?ts_type=<?php echo $ts_type; ?>&amp;ts_id=<?php echo $it['ts_id']; ?>&amp;token=<?php echo $token; ?>" onclick="return confirm('삭제하시겠습니까?');">삭제</a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php
include_once('./admin.tail.php');
