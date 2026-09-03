<?php
$sub_menu = "950400";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();

require_once G5_THEME_PATH.'/inc/settings.helper.php';

$type_labels = array('service' => '주요 서비스', 'process' => '이용 절차', 'why_us' => '선택하는 이유');

$ts_id = isset($_GET['ts_id']) ? (int) $_GET['ts_id'] : 0;
$item = array('ts_id' => 0, 'ts_type' => 'service', 'ts_title' => '', 'ts_description' => '', 'ts_icon' => '', 'ts_order' => 0, 'ts_use' => 1);
if ($ts_id) {
    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    $row = sql_fetch(" select * from g5_theme_service where ts_id = '{$ts_id}' and ts_theme = '{$slug}' ");
    if ($row) $item = $row;
} elseif (isset($_GET['ts_type']) && isset($type_labels[$_GET['ts_type']])) {
    $item['ts_type'] = $_GET['ts_type'];
}

$token = get_admin_token();

$g5['title'] = "테마 설정 - 항목 " . ($ts_id ? "수정" : "추가");
include_once('./admin.head.php');
?>
<form name="fsmartbizitem" method="post" action="./theme_list_item_form_update.php" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="<?php echo $token; ?>">
<input type="hidden" name="ts_id" value="<?php echo (int) $item['ts_id']; ?>">
<div class="tbl_frm01 tbl_wrap">
<table>
<caption>항목 정보</caption>
<tbody>
<tr>
    <th scope="row"><label for="ts_type">분류</label></th>
    <td>
        <select name="ts_type" id="ts_type">
            <?php foreach ($type_labels as $key => $label) { ?>
            <option value="<?php echo $key; ?>" <?php echo $item['ts_type'] == $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php } ?>
        </select>
    </td>
</tr>
<tr>
    <th scope="row"><label for="ts_title">제목</label></th>
    <td><input type="text" name="ts_title" value="<?php echo get_sanitize_input($item['ts_title']); ?>" id="ts_title" class="frm_input" size="40"></td>
</tr>
<tr>
    <th scope="row"><label for="ts_description">설명</label></th>
    <td><textarea name="ts_description" id="ts_description" class="frm_input" rows="4" style="width:100%"><?php echo get_sanitize_input($item['ts_description']); ?></textarea></td>
</tr>
<tr>
    <th scope="row"><label for="ts_icon">아이콘 이미지</label></th>
    <td>
        <?php if ($item['ts_icon']) { ?><img src="<?php echo smartbiz_upload_url($item['ts_icon']); ?>" alt="" style="max-height:60px;display:block;margin-bottom:.5rem"><?php } ?>
        <input type="file" name="ts_icon" id="ts_icon" accept="image/*">
    </td>
</tr>
<tr>
    <th scope="row"><label for="ts_order">순서</label></th>
    <td><input type="text" name="ts_order" value="<?php echo (int) $item['ts_order']; ?>" id="ts_order" class="frm_input" size="5"></td>
</tr>
<tr>
    <th scope="row">노출</th>
    <td><label><input type="checkbox" name="ts_use" value="1" <?php echo $item['ts_use'] ? 'checked' : ''; ?>> 노출함</label></td>
</tr>
</tbody>
</table>
</div>
<div class="btn_confirm">
    <button type="submit" id="btn_submit" class="btn_submit">저장하기</button>
</div>
</form>
<?php
include_once('./admin.tail.php');
