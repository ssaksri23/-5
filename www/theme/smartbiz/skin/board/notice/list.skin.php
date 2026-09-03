<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_CSS_URL.'/board.css">', 0);
?>
<div class="sb_board" id="bo_list">
    <div class="sb_board_head">
        <h2><?php echo get_text($board['bo_subject']); ?></h2>
        <span class="sb_board_total">전체 <?php echo number_format($total_count); ?>건</span>
    </div>

    <?php if ($is_category) { ?>
    <nav aria-label="카테고리" style="margin-bottom:1rem;"><?php echo $category_option; ?></nav>
    <?php } ?>

    <form name="fsearch" method="get" class="sb_board_search">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <label for="sfl" class="sb_sound_only">검색대상</label>
        <select name="sfl" id="sfl"><?php echo get_board_sfl_select_options($sfl); ?></select>
        <label for="stx" class="sb_sound_only">검색어</label>
        <input type="text" name="stx" id="stx" value="<?php echo get_text(stripslashes($stx)); ?>" placeholder="검색어를 입력해주세요" maxlength="20">
        <button type="submit" class="sb_btn sb_btn_ghost">검색</button>
    </form>

    <ul class="sb_list">
        <?php if (!count($list)) { ?>
        <li class="sb_list_empty">등록된 게시물이 없습니다.</li>
        <?php } ?>
        <?php for ($i = 0; $i < count($list); $i++) { $row = $list[$i]; ?>
        <li>
            <a href="<?php echo $row['href']; ?>">
                <?php if ($row['is_notice']) { ?>
                    <span class="sb_list_notice">공지</span>
                <?php } else { ?>
                    <span class="sb_list_num"><?php echo $row['num']; ?></span>
                <?php } ?>
                <span class="sb_list_subject">
                    <?php if (isset($row['icon_secret']) && $row['icon_secret']) { ?><span class="sb_list_lock">🔒</span><?php } ?>
                    <?php echo $row['subject']; ?>
                    <?php if ($row['comment_cnt']) { ?> [<?php echo (int) $row['wr_comment']; ?>]<?php } ?>
                    <?php if (!empty($row['icon_new'])) { ?> <strong style="color:var(--sb-sub)">N</strong><?php } ?>
                </span>
                <span class="sb_list_meta">
                    <span><?php echo $row['name']; ?></span>
                    <span><?php echo $row['datetime2']; ?></span>
                    <span>조회 <?php echo (int) $row['wr_hit']; ?></span>
                </span>
            </a>
        </li>
        <?php } ?>
    </ul>

    <?php if ($write_href || $admin_href) { ?>
    <div class="sb_board_actions">
        <?php if ($admin_href) { ?><a href="<?php echo $admin_href; ?>" class="sb_btn sb_btn_ghost">관리자</a><?php } ?>
        <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="sb_btn sb_btn_primary">글쓰기</a><?php } ?>
    </div>
    <?php } ?>

    <div class="sb_paging"><?php echo $write_pages; ?></div>
</div>
