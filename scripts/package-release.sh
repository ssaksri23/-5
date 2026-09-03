#!/usr/bin/env bash
# 스마트비즈(SmartBiz) 그누보드5 테마 판매용 zip 조립 스크립트.
#
# www/ 아래 실제 소스(테마 본체 + 관리자 화면 + extend 자동로드 파일)와
# release/smartbiz/ 아래 문서(README/LICENSE/CHANGELOG/설치설명서)를 모아
# 구매자에게 전달할 zip 하나로 조립한다. www/ 안의 실제 라이브 사이트
# 파일에는 아무 영향도 주지 않는다(읽기만 한다).
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WWW="$REPO_ROOT/www"
RELEASE_SRC="$REPO_ROOT/release/smartbiz"
DIST_DIR="$REPO_ROOT/dist"

VERSION="$(sed -n 's/^Version:[[:space:]]*//p' "$WWW/theme/smartbiz/readme.txt" | head -1 | tr -d '\r')"
if [ -z "$VERSION" ]; then
    echo "오류: theme/smartbiz/readme.txt 에서 Version 값을 찾지 못했습니다." >&2
    exit 1
fi

PKG_NAME="smartbiz-gnuboard5-theme-${VERSION}"
STAGE_DIR="$(mktemp -d)"
trap 'rm -rf "$STAGE_DIR"' EXIT

PKG_ROOT="$STAGE_DIR/$PKG_NAME"
mkdir -p "$PKG_ROOT"

echo "==> 테마 본체 복사 (theme/smartbiz/)"
mkdir -p "$PKG_ROOT/theme"
cp -R "$WWW/theme/smartbiz" "$PKG_ROOT/theme/smartbiz"

echo "==> 관리자 설정 화면 복사 (adm/)"
mkdir -p "$PKG_ROOT/adm"
cp "$WWW/adm/admin.menu950.smartbiz.php" "$PKG_ROOT/adm/"
cp "$WWW/adm/theme_admin.lib.php" "$PKG_ROOT/adm/"
cp "$WWW/adm/theme_company_form.php" "$WWW/adm/theme_company_form_update.php" "$PKG_ROOT/adm/"
cp "$WWW/adm/theme_section_list.php" "$WWW/adm/theme_section_update.php" "$PKG_ROOT/adm/"
cp "$WWW/adm/theme_banner_list.php" "$WWW/adm/theme_banner_form.php" "$WWW/adm/theme_banner_form_update.php" "$PKG_ROOT/adm/"
cp "$WWW/adm/theme_list_item_list.php" "$WWW/adm/theme_list_item_form.php" "$WWW/adm/theme_list_item_form_update.php" "$PKG_ROOT/adm/"
cp "$WWW/adm/theme_install_wizard.php" "$PKG_ROOT/adm/"
cp "$WWW/adm/theme_license_view.php" "$PKG_ROOT/adm/"

echo "==> 확장 자동로드 파일 복사 (extend/)"
mkdir -p "$PKG_ROOT/extend"
cp "$WWW/extend/smartbiz-theme.extend.php" "$PKG_ROOT/extend/"

echo "==> 미리보기/참고용 사본 복사 (skin/, install/, sample/)"
mkdir -p "$PKG_ROOT/skin" "$PKG_ROOT/install" "$PKG_ROOT/sample"
cp -R "$WWW/theme/smartbiz/skin/." "$PKG_ROOT/skin/"
cp -R "$WWW/theme/smartbiz/install/." "$PKG_ROOT/install/"
cp -R "$WWW/theme/smartbiz/sample/." "$PKG_ROOT/sample/"

echo "==> plugin/ (예약된 빈 폴더)"
mkdir -p "$PKG_ROOT/plugin"
cp "$RELEASE_SRC/plugin/README.md" "$PKG_ROOT/plugin/"

echo "==> 문서 복사 (docs/, README.md, LICENSE.txt, CHANGELOG.md)"
mkdir -p "$PKG_ROOT/docs"
cp "$RELEASE_SRC/docs/install-guide.md" "$PKG_ROOT/docs/"
cp "$RELEASE_SRC/README.md" "$PKG_ROOT/README.md"
cp "$RELEASE_SRC/LICENSE.txt" "$PKG_ROOT/LICENSE.txt"
cp "$RELEASE_SRC/CHANGELOG.md" "$PKG_ROOT/CHANGELOG.md"

mkdir -p "$DIST_DIR"
ZIP_PATH="$DIST_DIR/${PKG_NAME}.zip"
rm -f "$ZIP_PATH"

echo "==> 압축 (${ZIP_PATH})"
(cd "$STAGE_DIR" && zip -rq "$ZIP_PATH" "$PKG_NAME")

echo ""
echo "완료: $ZIP_PATH"
echo ""
echo "포함된 최상위 구성:"
(cd "$PKG_ROOT" && find . -maxdepth 1 -mindepth 1 | sed 's|^\./|  - |' | sort)
