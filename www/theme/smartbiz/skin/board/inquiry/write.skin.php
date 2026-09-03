<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 관리자만 글을 작성할 수 있는 게시판(공지사항/포트폴리오/갤러리). "온라인문의"와
// "고객후기"는 방문자가 직접 작성하므로 이 배열에 없다.
$smartbiz_admin_only_boards = array('notice', 'portfolio', 'gallery');
if (!$is_admin && in_array($bo_table, $smartbiz_admin_only_boards, true)) {
    alert('관리자만 작성할 수 있는 게시판입니다.', G5_BBS_URL.'/board.php?bo_table='.$bo_table);
}

// "온라인문의"는 항상 비밀글로 등록되어야 한다 (요청사항: 관리자만 전체 내용 확인 가능).
// 스킨에서 값을 강제로 끼워넣는 대신, 설치 마법사가 이 게시판을 bo_use_secret=2
// ("비밀글 무조건 사용")로 만든다 — core(bbs/write_update.php)가 관리자가 아닌
// 글쓴이의 글을 예외 없이 비밀글로 강제 저장하므로(요청 위조로도 우회 불가),
// 이 화면은 안내 문구만 보여주면 된다.
$smartbiz_force_secret = ($bo_table === 'inquiry');

add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_CSS_URL.'/board.css">', 0);
?>
<section class="sb_board" id="bo_w">
    <div class="sb_board_head"><h2><?php echo get_text($g5['title']); ?></h2></div>

    <?php if ($smartbiz_force_secret) { ?>
    <p class="sb_form_notice">문의 내용은 <strong>비밀글</strong>로 등록되며, 작성자 본인과 관리자만 확인할 수 있습니다.</p>
    <?php } ?>

    <form name="fwrite" id="fwrite" action="<?php echo $action_url; ?>" method="post" enctype="multipart/form-data" autocomplete="off" onsubmit="return fwrite_submit(this);">
    <!-- bbs/write_update.php 가 매 게시글 저장 요청마다 무조건 check_write_token()을
         호출해 이 값을 세션과 대조한다(그누보드5 코어의 표준 게시판 CSRF 방어) -->
    <input type="hidden" name="token" value="<?php echo get_write_token($bo_table); ?>">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
    <input type="hidden" name="sca" value="<?php echo $sca; ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
    <input type="hidden" name="stx" value="<?php echo $stx; ?>">
    <input type="hidden" name="spt" value="<?php echo $spt; ?>">
    <input type="hidden" name="sst" value="<?php echo $sst; ?>">
    <input type="hidden" name="sod" value="<?php echo $sod; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">

    <?php if ($is_notice && $is_admin) { ?>
    <div class="sb_form_row sb_form_check">
        <input type="checkbox" id="notice" name="notice" value="1" <?php echo $notice_checked; ?>>
        <label for="notice" style="margin:0">공지글로 등록</label>
    </div>
    <?php } ?>

    <?php if ($is_category) { ?>
    <div class="sb_form_row">
        <label for="ca_name">분류</label>
        <select name="ca_name" id="ca_name" required>
            <option value="">분류를 선택하세요</option>
            <?php echo $category_option; ?>
        </select>
    </div>
    <?php } ?>

    <?php if ($is_name) { ?>
    <div class="sb_form_row">
        <label for="wr_name">이름</label>
        <input type="text" name="wr_name" value="<?php echo $name; ?>" id="wr_name" required>
    </div>
    <?php } ?>

    <?php if ($is_password) { ?>
    <div class="sb_form_row">
        <label for="wr_password">비밀번호</label>
        <input type="password" name="wr_password" id="wr_password" <?php echo $password_required; ?>>
    </div>
    <?php } ?>

    <?php if ($is_email) { ?>
    <div class="sb_form_row">
        <label for="wr_email">이메일</label>
        <input type="text" name="wr_email" value="<?php echo $email; ?>" id="wr_email">
    </div>
    <?php } ?>

    <div class="sb_form_row">
        <label for="wr_subject">제목</label>
        <input type="text" name="wr_subject" value="<?php echo $subject; ?>" id="wr_subject" required maxlength="255">
    </div>

    <div class="sb_form_row">
        <label for="wr_content">내용</label>
        <?php if ($write_min || $write_max) { ?>
        <p style="font-size:.82rem;color:var(--sb-ink-faint);margin:0 0 .4rem">최소 <?php echo $write_min; ?>자 ~ 최대 <?php echo $write_max; ?>자</p>
        <?php } ?>
        <?php echo $editor_html; ?>
    </div>

    <?php for ($i = 0; $is_file && $i < $file_count; $i++) { ?>
    <div class="sb_form_row">
        <label for="bf_file_<?php echo $i + 1; ?>">첨부파일 #<?php echo $i + 1; ?> (최대 <?php echo $upload_max_filesize; ?>)</label>
        <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>">
        <?php if ($w == 'u' && isset($file[$i]['file']) && $file[$i]['file']) { ?>
        <span class="sb_form_check" style="margin-top:.4rem">
            <input type="checkbox" id="bf_file_del<?php echo $i; ?>" name="bf_file_del[<?php echo $i; ?>]" value="1">
            <label for="bf_file_del<?php echo $i; ?>" style="margin:0;font-weight:400"><?php echo $file[$i]['source']; ?> 삭제</label>
        </span>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if ($is_use_captcha) { ?>
    <div class="sb_form_row"><?php echo $captcha_html; ?></div>
    <?php } ?>

    <div class="sb_form_actions">
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="sb_btn sb_btn_ghost">취소</a>
        <button type="submit" id="btn_submit" class="sb_btn sb_btn_primary">작성완료</button>
    </div>
    </form>
</section>
<?php echo $editor_content_js; // 이미 <script src="...">로 감싸진 완결된 태그라 그대로 출력 ?>
<script>
// $editor_js / $captcha_js 는 core가 완결된 <script> 태그가 아니라 조각(raw) JS로
// 반환한다 — onsubmit 핸들러 함수 안에 그대로 끼워 넣어 쓰라는 전제라, 태그 없이
// 페이지에 그대로 echo하면 브라우저가 스크립트가 아니라 본문 텍스트로 렌더링해버린다.
function fwrite_submit(f)
{
    <?php echo $editor_js; ?>
    <?php echo $captcha_js; ?>
    return true;
}
</script>
