<?php
$sub_menu = "950300";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();

require_once G5_THEME_PATH.'/inc/settings.helper.php';

$tb_id = isset($_GET['tb_id']) ? (int) $_GET['tb_id'] : 0;
$banner = array('tb_id' => 0, 'tb_title' => '', 'tb_image' => '', 'tb_link_url' => '', 'tb_position' => 'main_slide', 'tb_order' => 0, 'tb_use' => 1);
if ($tb_id) {
    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    $row = sql_fetch(" select * from g5_theme_banner where tb_id = '{$tb_id}' and tb_theme = '{$slug}' ");
    if ($row) $banner = $row;
}

$token = get_admin_token();

$g5['title'] = "테마 설정 - 배너 " . ($tb_id ? "수정" : "추가");
include_once('./admin.head.php');
?>
<form name="fsmartbizbanner" method="post" action="./theme_banner_form_update.php" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="<?php echo $token; ?>">
<input type="hidden" name="tb_id" value="<?php echo (int) $banner['tb_id']; ?>">
<div class="tbl_frm01 tbl_wrap">
<table>
<caption>배너 정보</caption>
<tbody>
<tr>
    <th scope="row"><label for="tb_title">제목</label></th>
    <td><input type="text" name="tb_title" value="<?php echo get_sanitize_input($banner['tb_title']); ?>" id="tb_title" class="frm_input" size="40"></td>
</tr>
<tr>
    <th scope="row"><label for="tb_image">이미지</label></th>
    <td>
        <?php if ($banner['tb_image']) { ?><img src="<?php echo smartbiz_upload_url($banner['tb_image']); ?>" alt="" style="max-height:80px;display:block;margin-bottom:.5rem"><?php } ?>
        <input type="file" name="tb_image" id="tb_image" accept="image/*">
    </td>
</tr>
<tr>
    <th scope="row"><label for="tb_link_url">연결 링크</label></th>
    <td><input type="text" name="tb_link_url" value="<?php echo get_sanitize_input($banner['tb_link_url']); ?>" id="tb_link_url" class="frm_input" size="50"></td>
</tr>
<tr>
    <th scope="row"><label for="tb_position">위치</label></th>
    <td>
        <select name="tb_position" id="tb_position">
            <option value="main_slide" <?php echo $banner['tb_position'] == 'main_slide' ? 'selected' : ''; ?>>메인 슬라이드</option>
        </select>
    </td>
</tr>
<tr>
    <th scope="row"><label for="tb_order">순서</label></th>
    <td><input type="text" name="tb_order" value="<?php echo (int) $banner['tb_order']; ?>" id="tb_order" class="frm_input" size="5"></td>
</tr>
<tr>
    <th scope="row">노출</th>
    <td><label><input type="checkbox" name="tb_use" value="1" <?php echo $banner['tb_use'] ? 'checked' : ''; ?>> 노출함</label></td>
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
