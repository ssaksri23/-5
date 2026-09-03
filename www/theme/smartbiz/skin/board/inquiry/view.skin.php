<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_CSS_URL.'/board.css">', 0);
?>
<article class="sb_board" id="bo_v">
    <header class="sb_view_head">
        <h2>
            <?php if (!empty($category_name)) { ?><span style="color:var(--sb-main)"><?php echo $view['ca_name']; ?></span> · <?php } ?>
            <?php echo cut_str(get_text($view['wr_subject']), 100); ?>
        </h2>
        <div class="sb_view_meta">
            <span><?php echo $view['name']; ?><?php if ($is_ip_view) echo '&nbsp;('.$ip.')'; ?></span>
            <span><?php echo date('Y-m-d H:i', strtotime($view['wr_datetime'])); ?></span>
            <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
            <span>댓글 <?php echo number_format($view['wr_comment']); ?></span>
        </div>
    </header>

    <?php
    // 이미지 확장자인 첨부파일은 다운로드 목록이 아니라 본문 위에 인라인으로 보여준다
    // (포트폴리오/갤러리가 사진 게시판으로 쓰이므로 필수) — 코어 basic 스킨과 동일한 관례.
    if (isset($view['file']) && count($view['file'])) {
        echo '<div class="sb_view_images">';
        foreach ($view['file'] as $view_file) {
            echo get_file_thumbnail($view_file);
        }
        echo '</div>';
    }
    ?>
    <div class="sb_view_content"><?php echo get_view_thumbnail($view['content']); ?></div>

    <?php
    $sb_file_cnt = 0;
    if (isset($view['file']['count']) && $view['file']['count']) {
        for ($i = 0; $i < count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) $sb_file_cnt++;
        }
    }
    ?>
    <?php if ($sb_file_cnt) { ?>
    <ul class="sb_view_files">
        <?php for ($i = 0; $i < count($view['file']); $i++) { if (empty($view['file'][$i]['source']) || $view['file'][$i]['view']) continue; ?>
        <li><a href="<?php echo $view['file'][$i]['href']; ?>" class="view_file_download"><?php echo $view['file'][$i]['source']; ?> (<?php echo $view['file'][$i]['size']; ?>) — <?php echo $view['file'][$i]['download']; ?>회 다운로드</a></li>
        <?php } ?>
    </ul>
    <?php } ?>

    <?php if ($prev_href || $next_href) { ?>
    <ul class="sb_view_nav">
        <?php if ($prev_href) { ?><li><span>이전글</span><a href="<?php echo $prev_href; ?>"><?php echo $prev_wr_subject; ?></a></li><?php } ?>
        <?php if ($next_href) { ?><li><span>다음글</span><a href="<?php echo $next_href; ?>"><?php echo $next_wr_subject; ?></a></li><?php } ?>
    </ul>
    <?php } ?>

    <div class="sb_form_actions" style="justify-content:space-between">
        <a href="<?php echo $list_href; ?>" class="sb_btn sb_btn_ghost">목록</a>
        <span style="display:flex;gap:.5rem">
            <?php if ($reply_href) { ?><a href="<?php echo $reply_href; ?>" class="sb_btn sb_btn_ghost">답변</a><?php } ?>
            <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="sb_btn sb_btn_ghost">글쓰기</a><?php } ?>
            <?php if ($update_href) { ?><a href="<?php echo $update_href; ?>" class="sb_btn sb_btn_ghost">수정</a><?php } ?>
            <?php if ($delete_href) { ?><a href="<?php echo $delete_href; ?>" class="sb_btn sb_btn_ghost" onclick="del(this.href); return false;">삭제</a><?php } ?>
        </span>
    </div>

    <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>
</article>
