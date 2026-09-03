<?php
$sub_menu = "950600";
require_once './_common.php';
require_once './theme_admin.lib.php';

smartbiz_admin_auth();

$slug = sql_real_escape_string(SMARTBIZ_SLUG);
$lic = sql_fetch(" select * from g5_theme_license where tl_theme = '{$slug}' order by tl_id desc limit 1 ");

// --- 라이선스 상태 판정 (정보 제공용) ---
// 이 판정은 절대로 사이트 기능을 막거나 데이터를 지우지 않는다. 관리자 화면에만
// 안내 배너로 표시한다. 외부 라이선스 서버 연동은 지금 구현하지 않고, 나중에
// 이 판정 로직을 서버 응답으로 교체할 수 있도록 자리만 만들어둔다.
$notice = '';
$notice_level = ''; // 'ok' | 'warn' | 'none'

if (!$lic) {
    $notice = '등록된 라이선스 정보가 없습니다. 판매자에게 받은 라이선스 정보를 아래에 입력해주세요.';
    $notice_level = 'none';
} else {
    $problems = array();

    if ($lic['tl_allowed_domain']) {
        $host = preg_replace('/:[0-9]+$/', '', $_SERVER['HTTP_HOST']);
        $allowed = $lic['tl_allowed_domain'];
        $host_ok = ($host === $allowed) || (substr($allowed, 0, 2) === '*.' && (substr($host, -(strlen($allowed) - 1)) === substr($allowed, 1)));
        if (!$host_ok) {
            $problems[] = "현재 접속 도메인({$host})이 라이선스에 등록된 도메인({$allowed})과 다릅니다.";
        }
    }

    if ($lic['tl_support_end_date'] && $lic['tl_support_end_date'] !== '1000-01-01') {
        if (strtotime($lic['tl_support_end_date']) < time()) {
            $problems[] = '기술지원 기간이 '.$lic['tl_support_end_date'].'에 종료되었습니다.';
        }
    }

    if ($problems) {
        $notice = implode(' ', $problems).' 이 문제는 안내용이며, 사이트 기능은 정상적으로 계속 동작합니다.';
        $notice_level = 'warn';
    } else {
        $notice = '라이선스 상태가 정상입니다.';
        $notice_level = 'ok';
    }
}

$new_status = $notice_level === 'ok' ? 'active' : ($notice_level === 'warn' ? 'mismatch' : 'active');
if ($lic) {
    sql_query(" update g5_theme_license set tl_status = '".sql_real_escape_string($new_status)."', tl_last_checked_at = now() where tl_id = '{$lic['tl_id']}' ");
}

if (isset($_POST['save']) && $_POST['save'] == '1') {
    check_admin_token();

    $fields = array(
        'tl_license_key'      => trim(clean_xss_tags($_POST['tl_license_key'] ?? '', 0, 1)),
        'tl_buyer_name'        => trim(clean_xss_tags($_POST['tl_buyer_name'] ?? '', 0, 1)),
        'tl_allowed_domain'    => trim(clean_xss_tags($_POST['tl_allowed_domain'] ?? '', 0, 1)),
        'tl_purchase_date'     => trim($_POST['tl_purchase_date'] ?? '') ?: '1000-01-01',
        'tl_max_installs'      => (int) ($_POST['tl_max_installs'] ?? 1),
        'tl_support_end_date'  => trim($_POST['tl_support_end_date'] ?? '') ?: '1000-01-01',
    );

    if ($lic) {
        $sets = array();
        foreach ($fields as $col => $val) $sets[] = "`{$col}` = '".sql_real_escape_string($val)."'";
        sql_query(" update g5_theme_license set ".implode(', ', $sets)." where tl_id = '{$lic['tl_id']}' ");
    } else {
        sql_query(" insert into g5_theme_license
                        (tl_theme, tl_license_key, tl_buyer_name, tl_allowed_domain, tl_purchase_date, tl_max_installs, tl_support_end_date, tl_status)
                    values
                        ('{$slug}', '".sql_real_escape_string($fields['tl_license_key'])."', '".sql_real_escape_string($fields['tl_buyer_name'])."',
                         '".sql_real_escape_string($fields['tl_allowed_domain'])."', '".sql_real_escape_string($fields['tl_purchase_date'])."',
                         {$fields['tl_max_installs']}, '".sql_real_escape_string($fields['tl_support_end_date'])."', 'active') ");
    }

    goto_url('./theme_license_view.php');
}

// 저장 후 리다이렉트로 최신값 다시 조회
$lic = sql_fetch(" select * from g5_theme_license where tl_theme = '{$slug}' order by tl_id desc limit 1 ");

$token = get_admin_token();

$g5['title'] = "테마 설정 - 라이선스 정보";
include_once('./admin.head.php');
?>
<?php if ($notice_level === 'warn') { ?>
<p class="win_msg" style="background:#fff3cd;border:1px solid #ffe58f"><?php echo get_sanitize_input($notice); ?></p>
<?php } elseif ($notice_level === 'ok') { ?>
<p class="win_msg" style="background:#e6f7e6;border:1px solid #b7e6b7"><?php echo get_sanitize_input($notice); ?></p>
<?php } else { ?>
<p class="win_msg"><?php echo get_sanitize_input($notice); ?></p>
<?php } ?>

<p style="color:#888;font-size:.85rem">참고: 라이선스 확인 결과는 이 관리자 화면에만 안내로 표시되며, 어떤 경우에도 사이트 접속이나 기능을 막거나 데이터를 삭제하지 않습니다. 현재는 외부 라이선스 서버와 연동하지 않고 아래 정보를 직접 입력해 관리합니다.</p>

<form name="fsmartbizlicense" method="post" action="./theme_license_view.php" autocomplete="off">
<input type="hidden" name="token" value="<?php echo $token; ?>">
<input type="hidden" name="save" value="1">
<div class="tbl_frm01 tbl_wrap">
<table>
<caption>라이선스 정보</caption>
<tbody>
<tr>
    <th scope="row"><label for="tl_license_key">라이선스 번호</label></th>
    <td><input type="text" name="tl_license_key" value="<?php echo get_sanitize_input($lic['tl_license_key'] ?? ''); ?>" id="tl_license_key" class="frm_input" size="40"></td>
</tr>
<tr>
    <th scope="row"><label for="tl_buyer_name">구매자명</label></th>
    <td><input type="text" name="tl_buyer_name" value="<?php echo get_sanitize_input($lic['tl_buyer_name'] ?? ''); ?>" id="tl_buyer_name" class="frm_input" size="30"></td>
</tr>
<tr>
    <th scope="row"><label for="tl_allowed_domain">허용 도메인</label></th>
    <td><input type="text" name="tl_allowed_domain" value="<?php echo get_sanitize_input($lic['tl_allowed_domain'] ?? ''); ?>" id="tl_allowed_domain" class="frm_input" size="30" placeholder="example.com 또는 *.example.com"></td>
</tr>
<tr>
    <th scope="row"><label for="tl_purchase_date">구매일</label></th>
    <td><input type="date" name="tl_purchase_date" value="<?php echo ($lic['tl_purchase_date'] ?? '') !== '1000-01-01' ? ($lic['tl_purchase_date'] ?? '') : ''; ?>" id="tl_purchase_date"></td>
</tr>
<tr>
    <th scope="row"><label for="tl_max_installs">설치 가능 사이트 수</label></th>
    <td><input type="text" name="tl_max_installs" value="<?php echo (int) ($lic['tl_max_installs'] ?? 1); ?>" id="tl_max_installs" class="frm_input" size="5"></td>
</tr>
<tr>
    <th scope="row"><label for="tl_support_end_date">지원 종료일</label></th>
    <td><input type="date" name="tl_support_end_date" value="<?php echo ($lic['tl_support_end_date'] ?? '') !== '1000-01-01' ? ($lic['tl_support_end_date'] ?? '') : ''; ?>" id="tl_support_end_date"></td>
</tr>
<?php if ($lic) { ?>
<tr>
    <th scope="row">마지막 확인</th>
    <td><?php echo get_sanitize_input($lic['tl_last_checked_at']); ?> (상태: <?php echo get_sanitize_input($lic['tl_status']); ?>)</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<div class="btn_confirm">
    <button type="submit" class="btn_submit">저장하기</button>
</div>
</form>
<?php
include_once('./admin.tail.php');
