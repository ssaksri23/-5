<?php
$sub_menu = "950300";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();

require_once G5_THEME_PATH.'/inc/settings.helper.php';

// 삭제 처리 (check_admin_token() 은 실패 시 내부에서 alert() 로 종료함)
if (isset($_GET['tb_id']) && $_GET['tb_id'] && isset($_GET['token'])) {
    check_admin_token();
    $tb_id = (int) $_GET['tb_id'];
    $slug = sql_real_escape_string(SMARTBIZ_SLUG);
    sql_query(" delete from g5_theme_banner where tb_id = '{$tb_id}' and tb_theme = '{$slug}' ");
    goto_url('./theme_banner_list.php');
}

// smartbiz_get_banners() 는 노출된 것만 반환하므로, 관리자 목록에서는 숨김 항목도 보이도록 직접 조회
$slug = sql_real_escape_string(SMARTBIZ_SLUG);
$banners = array();
$result = sql_query(" select * from g5_theme_banner where tb_theme = '{$slug}' order by tb_order asc, tb_id asc ");
while ($row = sql_fetch_array($result)) $banners[] = $row;

$token = get_admin_token();

$g5['title'] = "테마 설정 - 배너 관리";
include_once('./admin.head.php');
?>
<p class="win_msg">메인 슬라이드 영역(main_slide)에 노출되는 이미지 배너입니다. 로고/대표이미지와 별개로, 여러 장을 순서대로 보여줄 때 사용합니다.</p>
<div class="btn_confirm">
    <a href="./theme_banner_form.php" class="btn btn_02">배너 추가</a>
</div>
<div class="tbl_head01 tbl_wrap">
<table>
<caption>배너 목록</caption>
<thead>
<tr><th>썸네일</th><th>제목</th><th>위치</th><th>순서</th><th>노출</th><th>관리</th></tr>
</thead>
<tbody>
<?php if (!$banners) { ?>
<tr><td colspan="6">등록된 배너가 없습니다.</td></tr>
<?php } ?>
<?php foreach ($banners as $b) { ?>
<tr>
    <td><?php if ($b['tb_image']) { ?><img src="<?php echo smartbiz_upload_url($b['tb_image']); ?>" alt="" style="max-height:40px"><?php } ?></td>
    <td><?php echo get_sanitize_input($b['tb_title']); ?></td>
    <td><?php echo get_sanitize_input($b['tb_position']); ?></td>
    <td><?php echo (int) $b['tb_order']; ?></td>
    <td><?php echo $b['tb_use'] ? '노출' : '숨김'; ?></td>
    <td>
        <a href="./theme_banner_form.php?tb_id=<?php echo $b['tb_id']; ?>">수정</a>
        <a href="./theme_banner_list.php?tb_id=<?php echo $b['tb_id']; ?>&amp;token=<?php echo $token; ?>" onclick="return confirm('삭제하시겠습니까?');">삭제</a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php
include_once('./admin.tail.php');
