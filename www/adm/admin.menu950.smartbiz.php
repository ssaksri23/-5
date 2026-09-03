<?php
// 스마트비즈 테마 전용 관리자 탭. 파일명이 admin.menu[0-9]{3}*.php 패턴과 일치하면
// admin.head.php 가 dir() 스캔으로 자동 include 하므로, 기존 admin.menu*.php 는
// 전혀 건드리지 않는다.
$menu['menu950'] = array(
    array('950100', '테마 설정',       G5_ADMIN_URL . '/theme_company_form.php', 'cf_smartbiz', 1),
    array('950200', '섹션 노출/순서',   G5_ADMIN_URL . '/theme_section_list.php', 'cf_smartbiz', 1),
    array('950300', '배너 관리',        G5_ADMIN_URL . '/theme_banner_list.php',  'cf_smartbiz', 1),
    array('950400', '서비스/절차/이유', G5_ADMIN_URL . '/theme_list_item_list.php', 'cf_smartbiz', 1),
    array('950500', '테마 설치 마법사', G5_ADMIN_URL . '/theme_install_wizard.php', 'cf_smartbiz', 1),
    array('950600', '라이선스 정보',    G5_ADMIN_URL . '/theme_license_view.php', 'cf_smartbiz', 1),
);
