<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 이 테마는 PC/모바일을 별도 스킨 트리로 나누지 않고 CSS 미디어쿼리로만
// 반응형을 구현한다 (mobile/ 폴더 없음). G5_THEME_DEVICE='pc'로 고정해
// G5_IS_MOBILE 분기 자체를 우회한다.
if (!defined('G5_THEME_DEVICE')) define('G5_THEME_DEVICE', 'pc');

// 커뮤니티(게시판) 헤더/푸터를 사용. 쇼핑몰 기능은 사용하지 않는다.
if (!defined('G5_COMMUNITY_USE')) define('G5_COMMUNITY_USE', true);

$theme_config = array(
    'set_default_skin'          => false,
    'preview_board_skin'        => 'notice',
    'cf_faq_skin'                => 'smartbiz', // 코어 FAQ 모듈(g5_faq)의 테마 스킨. 커스텀 게시판인
                                                  // "온라인문의"(inquiry)와는 별개이며, qa_skin(1:1문의
                                                  // 모듈)은 이 테마에서 사용하지 않으므로 지정하지 않는다.
);
