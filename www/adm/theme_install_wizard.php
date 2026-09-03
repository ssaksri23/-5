<?php
$sub_menu = "950500";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();

require_once G5_THEME_PATH.'/install/board_definitions.php';

$results = array();
$did_run = false;

if (isset($_POST['run']) && $_POST['run'] == '1') {
    check_admin_token();
    $did_run = true;

    $order = 10;
    foreach (smartbiz_board_definitions() as $bo_table => $def) {
        $status = smartbiz_create_or_update_board($bo_table, $def);
        $menu_status = smartbiz_register_menu($bo_table, $def['bo_subject'], $order);
        $order += 10;
        $results[] = array('bo_table' => $bo_table, 'bo_subject' => $def['bo_subject'], 'board' => $status, 'menu' => $menu_status);
    }

    if (isset($_POST['apply_sample']) && $_POST['apply_sample'] == '1' && !empty($_POST['sample_industry'])) {
        $industry = preg_replace('/[^a-z_]/', '', $_POST['sample_industry']);
        $sample_file = G5_THEME_PATH.'/sample/'.$industry.'/data.php';
        if (is_file($sample_file)) {
            require_once G5_THEME_PATH.'/install/sample_loader.php';
            $sample_result = smartbiz_apply_sample_data($industry);
            $results[] = array('bo_table' => '', 'bo_subject' => '샘플 데이터('.$industry.')', 'board' => $sample_result, 'menu' => '-');
        }
    }
}

// 설치 상태 요약 (이미 만들어진 게시판 확인용)
$board_status = array();
foreach (smartbiz_board_definitions() as $bo_table => $def) {
    $row = sql_fetch(" select bo_table, bo_skin from {$g5['board_table']} where bo_table = '".sql_real_escape_string($bo_table)."' ");
    $board_status[$bo_table] = $row ? $row['bo_skin'] : null;
}

$sample_dirs = array();
$sample_root = G5_THEME_PATH.'/sample';
if (is_dir($sample_root)) {
    foreach (scandir($sample_root) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        if (is_dir($sample_root.'/'.$entry)) $sample_dirs[] = $entry;
    }
}

$token = get_admin_token();

$g5['title'] = "테마 설정 - 설치 마법사";
include_once('./admin.head.php');
?>
<p class="win_msg">아래 버튼을 누르면 스마트비즈 테마에 필요한 게시판(공지사항/온라인문의/포트폴리오/갤러리/고객후기)을 만들고 메인 메뉴에 등록합니다. <strong>이미 존재하는 게시판과 게시글은 삭제·덮어쓰지 않으며</strong>, 스킨 설정만 이 테마에 맞게 갱신합니다.</p>

<div class="tbl_head01 tbl_wrap">
<table>
<caption>게시판 설치 상태</caption>
<thead><tr><th>게시판</th><th>테이블명</th><th>현재 스킨</th><th>상태</th></tr></thead>
<tbody>
<?php foreach (smartbiz_board_definitions() as $bo_table => $def) { ?>
<tr>
    <td><?php echo get_sanitize_input($def['bo_subject']); ?></td>
    <td><?php echo get_sanitize_input($bo_table); ?></td>
    <td><?php echo get_sanitize_input($board_status[$bo_table] ?? '-'); ?></td>
    <td><?php echo $board_status[$bo_table] === null ? '미설치' : (strpos((string) $board_status[$bo_table], 'theme/') === 0 ? '설치됨' : '기존 게시판(스킨 미적용)'); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<?php if ($did_run) { ?>
<div class="tbl_head01 tbl_wrap">
<table>
<caption>실행 결과</caption>
<thead><tr><th>항목</th><th>게시판</th><th>메뉴</th></tr></thead>
<tbody>
<?php foreach ($results as $r) { ?>
<tr>
    <td><?php echo get_sanitize_input($r['bo_subject']); ?></td>
    <td><?php echo get_sanitize_input($r['board']); ?></td>
    <td><?php echo get_sanitize_input($r['menu']); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } ?>

<form name="fsmartbizinstall" method="post" action="./theme_install_wizard.php" autocomplete="off">
<input type="hidden" name="token" value="<?php echo $token; ?>">
<input type="hidden" name="run" value="1">
<div class="tbl_frm01 tbl_wrap">
<table>
<caption>샘플 데이터 적용(선택)</caption>
<tbody>
<tr>
    <th scope="row">샘플 데이터 적용</th>
    <td><label><input type="checkbox" name="apply_sample" value="1"> 아래 업종 샘플로 회사정보/서비스를 채웁니다 (기존에 입력한 내용이 있으면 덮어쓰지 않고 건너뜁니다)</label></td>
</tr>
<tr>
    <th scope="row">업종 선택</th>
    <td>
        <select name="sample_industry">
            <?php foreach ($sample_dirs as $d) { ?>
            <option value="<?php echo get_sanitize_input($d); ?>"><?php echo get_sanitize_input($d); ?></option>
            <?php } ?>
        </select>
    </td>
</tr>
</tbody>
</table>
</div>
<div class="btn_confirm">
    <button type="submit" class="btn_submit">게시판 설치/갱신 실행</button>
</div>
</form>
<?php
include_once('./admin.tail.php');
