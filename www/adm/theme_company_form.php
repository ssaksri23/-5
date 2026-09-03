<?php
$sub_menu = "950100";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();

require_once G5_THEME_PATH.'/inc/settings.helper.php';
$tc = smartbiz_get_company();

$token = get_admin_token();

$g5['title'] = "테마 설정 - 회사정보";
include_once('./admin.head.php');
?>
<form name="fsmartbizcompany" method="post" action="./theme_company_form_update.php" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="<?php echo $token; ?>">

<div class="tbl_frm01 tbl_wrap">
<table>
<caption>회사 기본정보</caption>
<tbody>
<tr>
    <th scope="row"><label for="tc_company_name">회사명</label></th>
    <td><input type="text" name="tc_company_name" value="<?php echo get_sanitize_input($tc['tc_company_name']); ?>" id="tc_company_name" class="frm_input" size="40"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_headline">메인 문구</label></th>
    <td><input type="text" name="tc_headline" value="<?php echo get_sanitize_input($tc['tc_headline']); ?>" id="tc_headline" class="frm_input" size="60"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_subheadline">서브 문구</label></th>
    <td><input type="text" name="tc_subheadline" value="<?php echo get_sanitize_input($tc['tc_subheadline']); ?>" id="tc_subheadline" class="frm_input" size="60"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_phone">대표 전화번호</label></th>
    <td><input type="text" name="tc_phone" value="<?php echo get_sanitize_input($tc['tc_phone']); ?>" id="tc_phone" class="frm_input" size="20" placeholder="02-1234-5678"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_email">이메일</label></th>
    <td><input type="text" name="tc_email" value="<?php echo get_sanitize_input($tc['tc_email']); ?>" id="tc_email" class="frm_input" size="30"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_address">회사 주소</label></th>
    <td><input type="text" name="tc_address" value="<?php echo get_sanitize_input($tc['tc_address']); ?>" id="tc_address" class="frm_input" size="60"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_kakao_url">카카오톡 상담 링크</label></th>
    <td><input type="text" name="tc_kakao_url" value="<?php echo get_sanitize_input($tc['tc_kakao_url']); ?>" id="tc_kakao_url" class="frm_input" size="60" placeholder="https://pf.kakao.com/..."></td>
</tr>
<tr>
    <th scope="row"><label for="tc_naver_blog_url">네이버 블로그 링크</label></th>
    <td><input type="text" name="tc_naver_blog_url" value="<?php echo get_sanitize_input($tc['tc_naver_blog_url']); ?>" id="tc_naver_blog_url" class="frm_input" size="60" placeholder="https://blog.naver.com/..."></td>
</tr>
<tr>
    <th scope="row"><label for="tc_color_main">메인 색상</label></th>
    <td><input type="color" name="tc_color_main" value="<?php echo get_sanitize_input($tc['tc_color_main'] ?: '#1f3a68'); ?>" id="tc_color_main"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_color_sub">보조 색상</label></th>
    <td><input type="color" name="tc_color_sub" value="<?php echo get_sanitize_input($tc['tc_color_sub'] ?: '#e8622c'); ?>" id="tc_color_sub"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_logo">로고</label></th>
    <td>
        <?php if ($tc['tc_logo']) { ?><img src="<?php echo smartbiz_upload_url($tc['tc_logo']); ?>" alt="" style="max-height:40px;display:block;margin-bottom:.5rem"><?php } ?>
        <input type="file" name="tc_logo" id="tc_logo" accept="image/*">
    </td>
</tr>
<tr>
    <th scope="row"><label for="tc_hero_image">대표 이미지</label></th>
    <td>
        <?php if ($tc['tc_hero_image']) { ?><img src="<?php echo smartbiz_upload_url($tc['tc_hero_image']); ?>" alt="" style="max-height:120px;display:block;margin-bottom:.5rem"><?php } ?>
        <input type="file" name="tc_hero_image" id="tc_hero_image" accept="image/*">
    </td>
</tr>
<tr>
    <th scope="row"><label for="tc_about_text">회사소개</label></th>
    <td><textarea name="tc_about_text" id="tc_about_text" class="frm_input" rows="6" style="width:100%"><?php echo get_sanitize_input($tc['tc_about_text']); ?></textarea></td>
</tr>
<tr>
    <th scope="row"><label for="tc_footer_biz_info">푸터 사업자 정보</label></th>
    <td><textarea name="tc_footer_biz_info" id="tc_footer_biz_info" class="frm_input" rows="4" style="width:100%" placeholder="상호 / 대표자 / 사업자등록번호 / 통신판매업신고번호 등"><?php echo get_sanitize_input($tc['tc_footer_biz_info']); ?></textarea></td>
</tr>
<tr>
    <th scope="row"><label for="tc_seo_title">SEO 제목</label></th>
    <td><input type="text" name="tc_seo_title" value="<?php echo get_sanitize_input($tc['tc_seo_title']); ?>" id="tc_seo_title" class="frm_input" size="60"></td>
</tr>
<tr>
    <th scope="row"><label for="tc_seo_description">SEO 설명</label></th>
    <td><textarea name="tc_seo_description" id="tc_seo_description" class="frm_input" rows="3" style="width:100%"><?php echo get_sanitize_input($tc['tc_seo_description']); ?></textarea></td>
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
