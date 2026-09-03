<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 스마트비즈 관리자 화면 공용 함수. adm/ 안의 개별 페이지에서 include 해서 쓴다.
// 코어 admin.menu*.php 파일은 건드리지 않는다.

define('SMARTBIZ_UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('SMARTBIZ_UPLOAD_ALLOWED_EXT', array('jpg', 'jpeg', 'png', 'gif', 'webp'));

/** 최고관리자만 접근 가능하도록 게이트. 그누보드5 코어 관례(is_admin=='super')를 그대로 따른다. */
function smartbiz_admin_auth()
{
    global $is_admin;
    if ($is_admin != 'super') {
        alert('최고관리자만 접근 가능합니다.');
    }
}

/**
 * 이미지 업로드 처리. 확장자 화이트리스트 + 용량 제한 + getimagesize()로 실제
 * 이미지인지 확인(위장 업로드 방지) 후 SMARTBIZ_UPLOAD_PATH 에 저장한다.
 * 새 파일이 없으면 $old_filename 을 그대로 반환(기존 값 유지).
 * 실패 시 alert() 로 즉시 중단한다(관리자 전용 화면이므로 안내 후 종료가 UX상 적절).
 */
function smartbiz_handle_upload($field_name, $old_filename = '')
{
    if (!isset($_FILES[$field_name]) || !is_uploaded_file($_FILES[$field_name]['tmp_name'])) {
        return $old_filename;
    }
    if ($_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        return $old_filename;
    }

    $tmp_name = $_FILES[$field_name]['tmp_name'];
    $orig_name = get_safe_filename($_FILES[$field_name]['name']);
    $size = (int) $_FILES[$field_name]['size'];

    if ($size <= 0 || $size > SMARTBIZ_UPLOAD_MAX_SIZE) {
        alert('이미지 용량은 5MB 이하만 업로드할 수 있습니다.');
    }

    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
    if (!in_array($ext, SMARTBIZ_UPLOAD_ALLOWED_EXT, true)) {
        alert('jpg, jpeg, png, gif, webp 형식의 이미지만 업로드할 수 있습니다.');
    }

    // 실제 이미지 파일인지 검증 (확장자 위장 업로드 방지)
    $image_info = @getimagesize($tmp_name);
    if ($image_info === false) {
        alert('올바른 이미지 파일이 아닙니다.');
    }

    if (!is_dir(SMARTBIZ_UPLOAD_PATH)) {
        @mkdir(SMARTBIZ_UPLOAD_PATH, 0705, true);
    }

    $new_filename = SMARTBIZ_SLUG.'_'.$field_name.'_'.time().'_'.substr(md5(mt_rand()), 0, 8).'.'.$ext;
    $dest = SMARTBIZ_UPLOAD_PATH.'/'.$new_filename;

    if (!move_uploaded_file($tmp_name, $dest)) {
        alert('이미지 업로드에 실패했습니다.');
    }
    @chmod($dest, 0644);

    // 기존 파일 교체 시 이전 파일 정리 (실패해도 치명적이지 않으므로 조용히 무시)
    if ($old_filename && $old_filename !== $new_filename) {
        $old_path = SMARTBIZ_UPLOAD_PATH.'/'.$old_filename;
        if (is_file($old_path)) @unlink($old_path);
    }

    return $new_filename;
}
