<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

require_once(G5_THEME_PATH.'/inc/settings.helper.php');

/**
 * 메인페이지 본문 섹션을 관리자 설정(g5_theme_section)의 노출/순서에 맞춰
 * 전부 렌더링한다. 아직 설정이 없는 섹션(설치 직후 등)은 extend 파일이 심어둔
 * 기본 순서를 그대로 쓰고, 그마저 없으면 DEFAULT_ORDER 순서로 전부 노출한다.
 */
function smartbiz_render_sections()
{
    static $default_order = array(
        'slide' => 10, 'services' => 20, 'about' => 30, 'process' => 40,
        'why_us' => 50, 'portfolio' => 60, 'testimonials' => 70,
        'faq' => 80, 'contact' => 90, 'map' => 100,
    );

    $configured = smartbiz_get_sections();

    $sections = array();
    foreach ($default_order as $key => $order) {
        if (isset($configured[$key])) {
            $sections[$key] = array('use' => (bool) $configured[$key]['se_use'], 'order' => (int) $configured[$key]['se_order']);
        } else {
            $sections[$key] = array('use' => true, 'order' => $order);
        }
    }
    uasort($sections, function ($a, $b) { return $a['order'] <=> $b['order']; });

    foreach ($sections as $key => $meta) {
        if (!$meta['use']) continue;
        $func = 'smartbiz_section_'.$key;
        if (function_exists($func)) $func();
    }
}

function smartbiz_section_slide()
{
    $tc = smartbiz_get_company();
    $slides = smartbiz_get_banners('main_slide');
    ?>
    <section class="sb_section sb_hero" id="sb-slide">
        <div class="sb_hero_media">
            <?php if ($tc['tc_hero_image']) { ?>
                <img src="<?php echo smartbiz_upload_url($tc['tc_hero_image']); ?>" alt="" class="sb_hero_img">
            <?php } ?>
        </div>
        <div class="sb_hero_copy">
            <h1><?php echo nl2br(get_text($tc['tc_headline'] ?: $tc['tc_company_name'])); ?></h1>
            <?php if ($tc['tc_subheadline']) { ?>
            <p><?php echo nl2br(get_text($tc['tc_subheadline'])); ?></p>
            <?php } ?>
            <div class="sb_hero_cta">
                <a href="#sb-contact" class="sb_btn sb_btn_primary">온라인 문의하기</a>
                <?php if ($tc['tc_phone']) { ?>
                <a href="tel:<?php echo get_text($tc['tc_phone']); ?>" class="sb_btn sb_btn_ghost"><?php echo get_text($tc['tc_phone']); ?></a>
                <?php } ?>
            </div>
        </div>
        <?php if ($slides) { ?>
        <div class="sb_hero_banners">
            <?php foreach ($slides as $b) { ?>
            <a href="<?php echo $b['tb_link_url'] ? get_text($b['tb_link_url']) : '#'; ?>" class="sb_hero_banner">
                <img src="<?php echo smartbiz_upload_url($b['tb_image']); ?>" alt="<?php echo get_text($b['tb_title']); ?>">
            </a>
            <?php } ?>
        </div>
        <?php } ?>
    </section>
    <?php
}

function smartbiz_section_services()
{
    $items = smartbiz_get_services('service');
    if (!$items) return;
    ?>
    <section class="sb_section" id="sb-services">
        <h2 class="sb_section_title">주요 서비스</h2>
        <div class="sb_grid sb_grid_3">
            <?php foreach ($items as $it) { ?>
            <article class="sb_card">
                <?php if ($it['ts_icon']) { ?><img src="<?php echo smartbiz_upload_url($it['ts_icon']); ?>" alt="" class="sb_card_icon"><?php } ?>
                <h3><?php echo get_text($it['ts_title']); ?></h3>
                <p><?php echo nl2br(get_text($it['ts_description'])); ?></p>
            </article>
            <?php } ?>
        </div>
    </section>
    <?php
}

function smartbiz_section_about()
{
    $tc = smartbiz_get_company();
    if (!$tc['tc_about_text']) return;
    ?>
    <section class="sb_section sb_section_alt" id="sb-about">
        <h2 class="sb_section_title">회사소개</h2>
        <div class="sb_prose"><?php echo nl2br(get_text($tc['tc_about_text'])); ?></div>
    </section>
    <?php
}

function smartbiz_section_process()
{
    $items = smartbiz_get_services('process');
    if (!$items) return;
    ?>
    <section class="sb_section" id="sb-process">
        <h2 class="sb_section_title">이용 절차</h2>
        <ol class="sb_process">
            <?php foreach ($items as $i => $it) { ?>
            <li class="sb_process_step">
                <span class="sb_process_num"><?php echo $i + 1; ?></span>
                <h3><?php echo get_text($it['ts_title']); ?></h3>
                <p><?php echo nl2br(get_text($it['ts_description'])); ?></p>
            </li>
            <?php } ?>
        </ol>
    </section>
    <?php
}

function smartbiz_section_why_us()
{
    $items = smartbiz_get_services('why_us');
    if (!$items) return;
    ?>
    <section class="sb_section sb_section_alt" id="sb-why-us">
        <h2 class="sb_section_title">고객이 저희를 선택하는 이유</h2>
        <div class="sb_grid sb_grid_4">
            <?php foreach ($items as $it) { ?>
            <article class="sb_card sb_card_compact">
                <?php if ($it['ts_icon']) { ?><img src="<?php echo smartbiz_upload_url($it['ts_icon']); ?>" alt="" class="sb_card_icon"><?php } ?>
                <h3><?php echo get_text($it['ts_title']); ?></h3>
                <p><?php echo nl2br(get_text($it['ts_description'])); ?></p>
            </article>
            <?php } ?>
        </div>
    </section>
    <?php
}

function smartbiz_section_portfolio()
{
    $posts = smartbiz_get_latest_posts('portfolio', 6);
    if (!$posts) return;
    ?>
    <section class="sb_section" id="sb-portfolio">
        <h2 class="sb_section_title">실적 &amp; 포트폴리오</h2>
        <div class="sb_grid sb_grid_3">
            <?php foreach ($posts as $p) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=portfolio&amp;wr_id=<?php echo $p['wr_id']; ?>" class="sb_card sb_card_thumb">
                <?php if ($p['thumb_url']) { ?>
                <span class="sb_thumb"><img src="<?php echo get_text($p['thumb_url']); ?>" alt=""></span>
                <?php } ?>
                <h3><?php echo get_text(cut_str($p['wr_subject'], 40)); ?></h3>
            </a>
            <?php } ?>
        </div>
        <div class="sb_section_more"><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=portfolio">전체보기 →</a></div>
    </section>
    <?php
}

function smartbiz_section_testimonials()
{
    $posts = smartbiz_get_latest_posts('review', 4);
    if (!$posts) return;
    ?>
    <section class="sb_section sb_section_alt" id="sb-testimonials">
        <h2 class="sb_section_title">고객 후기</h2>
        <div class="sb_grid sb_grid_2">
            <?php foreach ($posts as $p) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=review&amp;wr_id=<?php echo $p['wr_id']; ?>" class="sb_card sb_card_quote">
                <p>&ldquo;<?php echo get_text(cut_str(strip_tags($p['wr_content']), 90)); ?>&rdquo;</p>
                <span class="sb_card_meta"><?php echo get_text($p['wr_name']); ?></span>
            </a>
            <?php } ?>
        </div>
        <div class="sb_section_more"><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=review">후기 더보기 →</a></div>
    </section>
    <?php
}

function smartbiz_section_faq()
{
    if (!smartbiz_table_exists('g5_faq_master')) return;
    ?>
    <section class="sb_section" id="sb-faq">
        <h2 class="sb_section_title">자주 묻는 질문</h2>
        <div class="sb_section_more"><a href="<?php echo G5_BBS_URL; ?>/faq.php" class="sb_btn sb_btn_ghost">FAQ 전체보기 →</a></div>
    </section>
    <?php
}

function smartbiz_section_contact()
{
    $tc = smartbiz_get_company();
    ?>
    <section class="sb_section sb_section_alt" id="sb-contact">
        <h2 class="sb_section_title">온라인 문의</h2>
        <p class="sb_section_desc">문의 내용은 비밀글로 등록되며, 관리자만 확인할 수 있습니다.</p>
        <div class="sb_contact_actions">
            <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=inquiry" class="sb_btn sb_btn_primary">문의 작성하기</a>
            <?php if ($tc['tc_phone']) { ?><a href="tel:<?php echo get_text($tc['tc_phone']); ?>" class="sb_btn sb_btn_ghost">전화 문의: <?php echo get_text($tc['tc_phone']); ?></a><?php } ?>
            <?php if ($tc['tc_kakao_url']) { ?><a href="<?php echo get_text($tc['tc_kakao_url']); ?>" target="_blank" rel="noopener" class="sb_btn sb_btn_ghost">카카오 상담</a><?php } ?>
        </div>
    </section>
    <?php
}

function smartbiz_section_map()
{
    $tc = smartbiz_get_company();
    if (!$tc['tc_address']) return;
    $q = rawurlencode($tc['tc_address']);
    ?>
    <section class="sb_section" id="sb-map">
        <h2 class="sb_section_title">오시는 길</h2>
        <p class="sb_map_address"><?php echo get_text($tc['tc_address']); ?></p>
        <div class="sb_map_embed">
            <iframe src="https://maps.google.com/maps?q=<?php echo $q; ?>&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="오시는 길 지도"></iframe>
        </div>
        <a href="https://map.kakao.com/?q=<?php echo $q; ?>" target="_blank" rel="noopener" class="sb_btn sb_btn_ghost">카카오맵에서 보기 →</a>
    </section>
    <?php
}
