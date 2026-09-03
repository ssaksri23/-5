<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<section class="sb_comments" id="bo_vc">
    <h3>댓글 <?php echo (int) $view['wr_comment']; ?>개</h3>
    <ul class="sb_comment_list">
        <?php $cmt_amt = count($list); ?>
        <?php for ($i = 0; $i < $cmt_amt; $i++) { $c = $list[$i]; ?>
        <li class="sb_comment_item" id="c_<?php echo $c['wr_id']; ?>">
            <div class="sb_comment_meta">
                <strong><?php echo get_text($c['wr_name']); ?></strong>
                <span><?php echo $c['datetime']; ?></span>
            </div>
            <div class="sb_comment_content">
                <?php if (strstr($c['wr_option'], 'secret') && !($is_admin || ($member['mb_id'] && $member['mb_id'] == $c['mb_id']))) { ?>
                    <em>비밀 댓글입니다.</em>
                <?php } else { ?>
                    <?php echo $c['content']; ?>
                <?php } ?>
            </div>
        </li>
        <?php } ?>
        <?php if (!$cmt_amt) { ?>
        <li class="sb_comment_item">등록된 댓글이 없습니다.</li>
        <?php } ?>
    </ul>

    <?php if ($is_comment_write) {
        if ($w == '') $w = 'c';
    ?>
    <form name="fviewcomment" id="fviewcomment" action="<?php echo $comment_action_url; ?>" onsubmit="return smartbiz_comment_submit(this);" method="post" autocomplete="off">
        <input type="hidden" name="w" value="<?php echo $w; ?>">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
        <input type="hidden" name="comment_id" value="">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="is_good" value="">

        <div class="sb_form_row">
            <label for="wr_content" class="sb_sound_only">댓글 내용</label>
            <textarea id="wr_content" name="wr_content" maxlength="2000" required placeholder="댓글을 입력해주세요"></textarea>
        </div>

        <?php if ($is_guest) { ?>
        <div class="sb_form_row" style="display:flex;gap:.5rem">
            <input type="text" name="wr_name" required placeholder="이름" style="flex:1">
            <input type="password" name="wr_password" required placeholder="비밀번호" style="flex:1">
        </div>
        <?php if ($captcha_html) { ?><div class="sb_form_row"><?php echo $captcha_html; ?></div><?php } ?>
        <?php } ?>

        <div class="sb_form_actions">
            <button type="submit" class="sb_btn sb_btn_primary">댓글 등록</button>
        </div>
    </form>
    <script>
    function smartbiz_comment_submit(f) {
        if (!f.wr_content.value.trim()) { alert('댓글을 입력하여 주십시오.'); return false; }
        if (typeof f.wr_name != 'undefined' && !f.wr_name.value.trim()) { alert('이름을 입력해주세요.'); return false; }
        if (typeof f.wr_password != 'undefined' && !f.wr_password.value.trim()) { alert('비밀번호를 입력해주세요.'); return false; }
        <?php if ($is_guest) echo chk_captcha_js(); ?>
        set_comment_token(f);
        return true;
    }
    </script>
    <?php } ?>
</section>
