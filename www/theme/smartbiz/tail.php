<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

require_once(G5_THEME_PATH.'/inc/settings.helper.php');
$tc = smartbiz_get_company();
?>
</main>

<div id="sb_fixed_actions">
    <?php if ($tc['tc_phone']) { ?>
    <a href="tel:<?php echo get_text($tc['tc_phone']); ?>" class="sb_fixed_btn sb_fixed_call">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        <span>전화상담</span>
    </a>
    <?php } ?>
    <?php if ($tc['tc_kakao_url']) { ?>
    <a href="<?php echo get_text($tc['tc_kakao_url']); ?>" class="sb_fixed_btn sb_fixed_kakao" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 3C6.48 3 2 6.48 2 10.8c0 2.76 1.86 5.19 4.66 6.58-.2.75-.73 2.73-.84 3.15-.13.52.19.51.4.37.16-.11 2.6-1.77 3.66-2.49.68.1 1.38.15 2.12.15 5.52 0 10-3.48 10-7.76S17.52 3 12 3z"/></svg>
        <span>카카오 상담</span>
    </a>
    <?php } ?>
</div>

<footer id="sb_footer">
    <div class="sb_footer_inner">
        <div class="sb_footer_top">
            <div class="sb_footer_brand">
                <?php if ($tc['tc_logo']) { ?>
                    <img src="<?php echo smartbiz_upload_url($tc['tc_logo']); ?>" alt="<?php echo get_text($tc['tc_company_name']); ?>" class="sb_footer_logo">
                <?php } else { ?>
                    <strong><?php echo get_text($tc['tc_company_name'] ?: $config['cf_title']); ?></strong>
                <?php } ?>
                <?php if ($tc['tc_naver_blog_url']) { ?>
                <a href="<?php echo get_text($tc['tc_naver_blog_url']); ?>" target="_blank" rel="noopener">네이버 블로그</a>
                <?php } ?>
            </div>
            <ul class="sb_footer_links">
                <li><a href="<?php echo get_pretty_url('content', 'privacy'); ?>">개인정보처리방침</a></li>
                <li><a href="<?php echo get_pretty_url('content', 'provision'); ?>">서비스이용약관</a></li>
                <?php if ($is_admin) { ?>
                <li><a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>">관리자</a></li>
                <?php } ?>
            </ul>
        </div>
        <div class="sb_footer_bottom">
            <?php if ($tc['tc_footer_biz_info']) { ?>
            <p class="sb_footer_biz"><?php echo nl2br(get_text($tc['tc_footer_biz_info'])); ?></p>
            <?php } ?>
            <?php if ($tc['tc_address'] || $tc['tc_phone'] || $tc['tc_email']) { ?>
            <p class="sb_footer_contact">
                <?php if ($tc['tc_address']) echo get_text($tc['tc_address']); ?>
                <?php if ($tc['tc_phone']) echo ' · '.get_text($tc['tc_phone']); ?>
                <?php if ($tc['tc_email']) echo ' · '.get_text($tc['tc_email']); ?>
            </p>
            <?php } ?>
            <p class="sb_footer_copy">&copy; <?php echo date('Y'); ?> <?php echo get_text($tc['tc_company_name'] ?: $config['cf_title']); ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php if ($config['cf_analytics']) echo $config['cf_analytics']; ?>
<?php run_event('tail_sub'); ?>
</body>
</html>
<?php
// common.php 가 ob_start() 로 페이지 전체를 버퍼링해두었다가, add_javascript()/
// add_stylesheet() 로 쌓인 태그를 여기서 실제 위치에 끼워넣고 최종 출력한다.
// 이 호출이 없으면 큐에 쌓인 스크립트(jQuery, common.js, theme.js 등)가 전부
// 유실된다 — 코어 tail.sub.php의 필수 관례.
echo html_end();

