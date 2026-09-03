<?php
// 샘플 업종: 청소업체
if (!defined('_GNUBOARD_')) exit;

return array(
    'company' => array(
        'tc_company_name'      => '클린프로 청소업체',
        'tc_headline'          => '깨끗함의 기준, 클린프로',
        'tc_subheadline'       => '입주청소부터 정기 사무실 청소까지 믿고 맡기는 청소 전문 서비스',
        'tc_phone'             => '02-5678-9012',
        'tc_email'             => 'help@cleanpro.example',
        'tc_address'           => '서울특별시 마포구 예시길 12 클린프로빌딩 2층',
        'tc_kakao_url'         => 'https://pf.kakao.com/_example5',
        'tc_naver_blog_url'    => 'https://blog.naver.com/example5',
        'tc_color_main'        => '#0e7490',
        'tc_color_sub'         => '#22c55e',
        'tc_about_text'        => "클린프로 청소업체는 가정, 사무실, 상업시설을 대상으로 입주청소·정기청소·특수청소 서비스를 제공하는 청소 전문기업입니다.\n엄격한 위생 기준과 친환경 세정 방식을 적용해 고객이 안심하고 맡길 수 있는 청소 서비스를 약속합니다.",
        'tc_footer_biz_info'   => "클린프로 청소업체 | 대표 정미경 | 사업자등록번호 567-89-01234\n서울특별시 마포구 예시길 12",
        'tc_seo_title'         => '클린프로 청소업체 - 입주·정기청소 전문',
        'tc_seo_description'   => '가정, 사무실, 상업시설 청소 전문업체 클린프로. 친환경 세정으로 안심 청소.',
    ),
    'services' => array(
        array('ts_title' => '입주청소', 'ts_description' => '이사 전후 신축·구축 주택의 구석구석을 꼼꼼하게 청소합니다.', 'ts_icon' => ''),
        array('ts_title' => '사무실 정기청소', 'ts_description' => '사무공간을 정기적으로 방문해 쾌적한 근무환경을 유지합니다.', 'ts_icon' => ''),
        array('ts_title' => '특수청소', 'ts_description' => '준공청소, 바닥왁싱 등 전문 장비가 필요한 특수청소를 지원합니다.', 'ts_icon' => ''),
    ),
    'process' => array(
        array('ts_title' => '01. 견적 문의', 'ts_description' => '공간 크기와 상태를 알려주시면 정확한 견적을 안내합니다.', 'ts_icon' => ''),
        array('ts_title' => '02. 일정 예약', 'ts_description' => '원하시는 날짜에 맞춰 청소 일정을 예약해드립니다.', 'ts_icon' => ''),
        array('ts_title' => '03. 청소 진행·확인', 'ts_description' => '전문 인력이 방문해 청소 후 결과를 함께 확인합니다.', 'ts_icon' => ''),
    ),
    'why_us' => array(
        array('ts_title' => '친환경 세정제', 'ts_description' => '인체에 안전한 친환경 세정제만 사용합니다.', 'ts_icon' => ''),
        array('ts_title' => '검증된 인력', 'ts_description' => '신원이 검증된 숙련 인력만 현장에 투입합니다.', 'ts_icon' => ''),
        array('ts_title' => '만족 보장', 'ts_description' => '청소 결과에 만족하지 못하시면 재청소를 진행합니다.', 'ts_icon' => ''),
    ),
);
