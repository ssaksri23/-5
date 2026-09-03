<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_CSS_URL.'/board.css">', 0);
?>
<div class="sb_board" id="faq_wrap">
    <div class="sb_board_head"><h2><?php echo get_text($g5['title']); ?></h2></div>

    <?php if ($himg_src) { ?><div style="margin-bottom:1.5rem"><img src="<?php echo $himg_src; ?>" alt=""></div><?php } ?>
    <?php if (!empty($fm['fm_head_html'])) echo '<div class="sb_form_notice">'.conv_content($fm['fm_head_html'], 1).'</div>'; ?>

    <form name="faq_search_form" method="get" class="sb_board_search">
        <input type="hidden" name="fm_id" value="<?php echo $fm_id; ?>">
        <label for="stx" class="sb_sound_only">검색어</label>
        <input type="text" name="stx" value="<?php echo get_text($stx); ?>" id="stx" placeholder="궁금한 내용을 검색해보세요" maxlength="20">
        <button type="submit" class="sb_btn sb_btn_ghost">검색</button>
    </form>

    <?php if (count($faq_master_list) > 1) { ?>
    <nav aria-label="FAQ 분류" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem">
        <?php foreach ($faq_master_list as $v) { ?>
        <a href="<?php echo $category_href; ?>?fm_id=<?php echo $v['fm_id']; ?>" class="sb_btn <?php echo $v['fm_id'] == $fm_id ? 'sb_btn_primary' : 'sb_btn_ghost'; ?>"><?php echo get_text($v['fm_subject']); ?></a>
        <?php } ?>
    </nav>
    <?php } ?>

    <?php if (count($faq_list)) { ?>
    <ul class="sb_list" id="faq_con" style="border-top:2px solid var(--sb-ink)">
        <?php foreach ($faq_list as $v) { if (empty($v)) continue; ?>
        <li>
            <button type="button" class="sb_faq_q" onclick="return smartbiz_faq_toggle(this);" style="width:100%;text-align:left;background:none;border:none;padding:1rem .25rem;font:inherit;cursor:pointer;display:flex;gap:.75rem">
                <strong style="color:var(--sb-main)">Q</strong>
                <span style="flex:1"><?php echo conv_content($v['fa_subject'], 1); ?></span>
            </button>
            <div class="sb_faq_a" style="display:none;padding:0 .25rem 1.25rem 2rem;color:var(--sb-ink-soft)"><?php echo conv_content($v['fa_content'], 1); ?></div>
        </li>
        <?php } ?>
    </ul>
    <?php } else { ?>
        <?php if ($stx) { ?>
        <p class="sb_list_empty">검색된 FAQ가 없습니다.</p>
        <?php } else { ?>
        <p class="sb_list_empty">등록된 FAQ가 없습니다.<?php if ($is_admin) { ?><br><a href="<?php echo G5_ADMIN_URL; ?>/faqmasterlist.php">FAQ 관리에서 등록하기</a><?php } ?></p>
        <?php } ?>
    <?php } ?>

    <div class="sb_paging"><?php echo get_paging($page_rows, $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page='); ?></div>

    <?php if (!empty($fm['fm_tail_html'])) echo '<div class="sb_form_notice">'.conv_content($fm['fm_tail_html'], 1).'</div>'; ?>
    <?php if ($timg_src) { ?><div style="margin-top:1.5rem"><img src="<?php echo $timg_src; ?>" alt=""></div><?php } ?>
    <?php if ($admin_href) { ?><div style="text-align:right;margin-top:1rem"><a href="<?php echo $admin_href; ?>" class="sb_btn sb_btn_ghost">FAQ 관리</a></div><?php } ?>
</div>
<script>
function smartbiz_faq_toggle(btn) {
    var li = btn.closest('li');
    var a = li.querySelector('.sb_faq_a');
    a.style.display = (a.style.display === 'none' || !a.style.display) ? 'block' : 'none';
    return false;
}
</script>
