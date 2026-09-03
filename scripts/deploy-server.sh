#!/usr/bin/env bash
# 외부 리눅스 서버(예: 129.121.127.196)에 GNUBoard5 + 스마트비즈 테마를 통째로
# 배포하는 스크립트다. 이 서버에 root로 SSH 접속한 뒤, 이 저장소(또는 압축 해제한
# 사본)가 있는 디렉터리에서 그 서버 위에서 직접 실행한다(원격 실행 아님).
#
# 이 한 번의 실행으로 끝까지 자동으로 처리된다: Docker 기동 → 그누보드5 설치 →
# 스마트비즈 테마 적용 → 업종 샘플 데이터로 채우기 → 게시판/메뉴 생성 →
# Nginx 리버스 프록시 → Let's Encrypt SSL. 브라우저로 설치 마법사를 직접 클릭할
# 필요가 없다.
#
# 사용 예:
#   sudo bash scripts/deploy-server.sh mysite.example.com admin@example.com
#   sudo bash scripts/deploy-server.sh mysite.example.com admin@example.com construction
#   (세 번째 인자는 업종 샘플: staffing/outsourcing/labor/construction/cleaning/
#    autoparts/generic, 생략하면 cleaning)
#
# 이 서버에 이미 다른 사이트/서비스가 떠 있을 수 있으므로:
# - 80/443 포트를 이미 쓰는 웹서버가 있으면 건드리지 않고, 우리 컨테이너는
#   내부적으로 비어있는 다른 포트(기본 8081, 사용 중이면 자동으로 다음 번호)로만
#   띄운 뒤 Nginx가 도메인 이름 기준으로만 그 포트로 연결해준다.
# - 기존 Nginx 설정 파일은 수정하지 않고, 새 도메인 전용 설정 파일만 추가한다.
# - 이미 설치되어 있는 상태에서 다시 실행해도 안전하다(설치 단계는 건너뛰고,
#   테마 적용/샘플 데이터/게시판 생성은 이미 채워진 값은 덮어쓰지 않는다).
set -euo pipefail

DOMAIN="${1:-}"
EMAIL="${2:-}"
INDUSTRY="${3:-cleaning}"
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WEB_PORT_START="${WEB_PORT_START:-8081}"
ADMIN_ID="admin"

if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
    echo "사용법: sudo bash scripts/deploy-server.sh <도메인> <SSL 알림용 이메일> [업종]" >&2
    echo "예:     sudo bash scripts/deploy-server.sh mysite.example.com admin@example.com cleaning" >&2
    exit 1
fi

if [ "$(id -u)" -ne 0 ]; then
    echo "root 권한으로 실행해주세요 (sudo bash scripts/deploy-server.sh ...)" >&2
    exit 1
fi

cd "$APP_DIR"

echo "==> 1) 빈 포트 찾기 (${WEB_PORT_START}부터)"
WEB_PORT="$WEB_PORT_START"
while ss -ltn 2>/dev/null | awk '{print $4}' | grep -q ":${WEB_PORT}\$"; do
    WEB_PORT=$((WEB_PORT + 1))
done
echo "    사용할 컨테이너 포트: ${WEB_PORT} (11-1단계에서 외부 직접 접근은 방화벽으로 차단됨)"

echo "==> 2) 필수 패키지 설치 확인 (docker, docker compose, nginx, certbot)"
if ! command -v docker >/dev/null 2>&1; then
    curl -fsSL https://get.docker.com | sh
fi
if ! docker compose version >/dev/null 2>&1; then
    apt-get update -y && apt-get install -y docker-compose-plugin
fi
apt-get update -y
apt-get install -y nginx certbot python3-certbot-nginx

echo "==> 3) 환경 변수 파일 생성 (.env) — 이미 있으면 그대로 둔다"
if [ ! -f .env ]; then
    DB_PW="$(head -c 24 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 20)"
    DB_ROOT_PW="$(head -c 24 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 20)"
    cat > .env <<EOF
WEB_PORT=${WEB_PORT}
MYSQL_DATABASE=gnuboard5
MYSQL_USER=gnuboard5
MYSQL_PASSWORD=${DB_PW}
MYSQL_ROOT_PASSWORD=${DB_ROOT_PW}
EOF
    echo "    .env 새로 생성함 (DB 비밀번호 자동 생성됨, ${APP_DIR}/.env 에서 확인 가능)"
else
    if grep -q '^WEB_PORT=' .env; then
        sed -i "s/^WEB_PORT=.*/WEB_PORT=${WEB_PORT}/" .env
    else
        echo "WEB_PORT=${WEB_PORT}" >> .env
    fi
    echo "    기존 .env 유지, WEB_PORT만 ${WEB_PORT}로 갱신"
fi
# shellcheck disable=SC1091
source .env

echo "==> 4) 컨테이너 포트 바인딩 확인"
# docker-compose.yml 자체가 이미 "\${WEB_PORT:-8080}:80" 으로 포트를 발행하므로
# (.env 의 WEB_PORT 값을 그대로 씀) 별도 오버라이드 파일이 필요 없다. 예전
# 버전은 127.0.0.1 전용 바인딩을 위해 오버라이드 파일을 추가로 만들었는데,
# Docker Compose는 여러 파일의 ports 목록을 "교체"가 아니라 "추가"로 합치기
# 때문에 같은 호스트 포트를 두 번 바인딩하려다 "address already in use" 오류가
# 났다. 원본 compose 파일 그대로 쓰고, 외부 노출은 아래 8단계의 방화벽 규칙으로
# 막는다.
rm -f docker-compose.override.yml

echo "==> 5) 컨테이너 빌드 및 기동"
docker compose up -d --build

echo "==> 6) 사이트가 뜰 때까지 대기"
BASE="http://127.0.0.1:${WEB_PORT}"
for i in $(seq 1 30); do
    if curl -fsS "${BASE}/" >/dev/null 2>&1; then
        echo "    사이트 응답 확인됨"
        break
    fi
    sleep 2
done

COOKIE_JAR="$(mktemp)"
trap 'rm -f "$COOKIE_JAR"' EXIT

echo "==> 7) 그누보드5 설치 확인"
ALREADY_INSTALLED=0
if docker compose exec -T web test -f /app/data/dbconfig.php >/dev/null 2>&1; then
    ALREADY_INSTALLED=1
    echo "    이미 설치되어 있음 — 설치 단계는 건너뜀"
fi

if [ "$ALREADY_INSTALLED" -eq 0 ]; then
    echo "==> 7-1) 그누보드5 자동 설치 진행"
    ADMIN_PASS="$(head -c 24 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 16)"

    STEP1="$(curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "${BASE}/install/install_config.php" \
        --data-urlencode "agree=동의함")"
    AJAX_TOKEN="$(echo "$STEP1" | grep -oE 'name="ajax_token" value="[^"]+"' | head -1 | sed -E 's/.*value="([^"]+)"/\1/')"

    if [ -z "$AJAX_TOKEN" ]; then
        echo "    설치 1단계에서 토큰을 못 찾았습니다. 이미 설치되어 있거나 사이트 상태를 확인해주세요." >&2
        exit 1
    fi

    curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "${BASE}/install/install_db.php" \
        --data-urlencode "mysql_host=db" \
        --data-urlencode "mysql_user=${MYSQL_USER}" \
        --data-urlencode "mysql_pass=${MYSQL_PASSWORD}" \
        --data-urlencode "mysql_db=${MYSQL_DATABASE}" \
        --data-urlencode "table_prefix=g5_" \
        --data-urlencode "g5_install=1" \
        --data-urlencode "g5_shop_install=" \
        --data-urlencode "g5_shop_prefix=yc5_" \
        --data-urlencode "ajax_token=${AJAX_TOKEN}" \
        --data-urlencode "admin_id=${ADMIN_ID}" \
        --data-urlencode "admin_pass=${ADMIN_PASS}" \
        --data-urlencode "admin_name=최고관리자" \
        --data-urlencode "admin_email=${EMAIL}" \
        -o /tmp/smartbiz_install_result.html

    if ! grep -q "설치가 완료되었습니다" /tmp/smartbiz_install_result.html; then
        echo "    그누보드5 설치에 실패한 것으로 보입니다. 아래 응답을 확인해주세요:" >&2
        cat /tmp/smartbiz_install_result.html >&2
        exit 1
    fi
    echo "    그누보드5 설치 완료. 관리자 계정: ${ADMIN_ID} / ${ADMIN_PASS}"
    echo "${ADMIN_ID} ${ADMIN_PASS}" > "${APP_DIR}/.smartbiz_admin_credentials"
    chmod 600 "${APP_DIR}/.smartbiz_admin_credentials"
else
    if [ -f "${APP_DIR}/.smartbiz_admin_credentials" ]; then
        ADMIN_PASS="$(awk '{print $2}' "${APP_DIR}/.smartbiz_admin_credentials")"
    else
        echo "    관리자 비밀번호 기록 파일이 없어 이후 자동화(테마 적용/샘플 데이터)를 건너뜁니다."
        echo "    관리자 화면에서 직접 테마를 적용해주세요: 환경설정 → 테마 설정 → 스마트비즈"
        ADMIN_PASS=""
    fi
fi

if [ -n "${ADMIN_PASS:-}" ]; then
    echo "==> 8) 관리자 로그인"
    curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "${BASE}/bbs/login_check.php" \
        --data-urlencode "mb_id=${ADMIN_ID}" --data-urlencode "mb_password=${ADMIN_PASS}" \
        --data-urlencode "url=" -o /dev/null

    echo "==> 9) 스마트비즈 테마 적용"
    curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "${BASE}/adm/theme_update.php" \
        -e "${BASE}/adm/theme.php" \
        --data-urlencode "theme=smartbiz" \
        --data-urlencode "type=active" \
        --data-urlencode "set_default_skin=0" \
        -o /dev/null

    echo "==> 10) 테마 확장 테이블 초기화 (홈페이지 1회 호출)"
    curl -s "${BASE}/" -o /dev/null

    echo "==> 11) 게시판/메뉴 생성 + 업종 샘플 데이터 적용 (${INDUSTRY})"
    WIZARD_HTML="$(curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" "${BASE}/adm/theme_install_wizard.php")"
    WIZARD_TOKEN="$(echo "$WIZARD_HTML" | grep -oE 'name="token" value="[^"]+"' | head -1 | sed -E 's/.*value="([^"]+)"/\1/')"

    curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "${BASE}/adm/theme_install_wizard.php" \
        --data-urlencode "token=${WIZARD_TOKEN}" --data-urlencode "run=1" \
        --data-urlencode "apply_sample=1" --data-urlencode "sample_industry=${INDUSTRY}" \
        -o /tmp/smartbiz_wizard_result.html
    echo "    완료 (자세한 결과는 관리자 화면의 '테마 설정 → 설치 마법사'에서도 확인 가능)"
fi

echo "==> 11-1) 컨테이너 포트(${WEB_PORT}) 외부 직접 접근 차단 (Nginx를 통해서만 접속되게)"
# 4단계에서 설명한 이유로 컨테이너 자체는 0.0.0.0(모든 인터페이스)에 바인딩된다.
# ufw가 있으면 그 포트로의 외부 접근만 막아서, 반드시 Nginx(80/443, 도메인 이름
# 기반)를 거치도록 한다. ufw가 없는 서버에서는 이 단계를 건너뛴다(치명적이지
# 않음 — 방화벽 도구가 없다는 뜻이라 서버 전체가 이미 그런 정책일 가능성이 큼).
if command -v ufw >/dev/null 2>&1; then
    ufw deny "${WEB_PORT}/tcp" || true
else
    echo "    ufw가 설치되어 있지 않아 이 단계는 건너뜁니다."
fi

echo "==> 12) Nginx 사이트 설정 추가 (${DOMAIN} 전용 파일만 새로 생성, 기존 설정은 유지)"
NGINX_CONF="/etc/nginx/sites-available/${DOMAIN}.conf"
cat > "$NGINX_CONF" <<EOF
server {
    listen 80;
    server_name ${DOMAIN};

    location / {
        proxy_pass http://127.0.0.1:${WEB_PORT};
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        client_max_body_size 20m;
    }
}
EOF
ln -sf "$NGINX_CONF" "/etc/nginx/sites-enabled/${DOMAIN}.conf"
nginx -t
systemctl reload nginx

echo "==> 13) Let's Encrypt SSL 발급 및 자동 HTTPS 전환 (certbot)"
if ! certbot --nginx -d "$DOMAIN" -m "$EMAIL" --agree-tos --redirect --non-interactive; then
    echo ""
    echo "SSL 발급에 실패했습니다 — 보통 도메인의 DNS(A레코드)가 아직 이 서버로"
    echo "전파되지 않아서입니다. 'dig +short ${DOMAIN}' 결과가 이 서버 IP로 나오는지"
    echo "확인한 뒤 이 스크립트를 다시 실행해주세요(이미 된 부분은 다시 건드리지 않습니다)."
fi

echo ""
echo "=================================================="
echo "완료! http://${DOMAIN} (또는 SSL이 붙었다면 https://${DOMAIN}) 로 접속해보세요."
if [ -f "${APP_DIR}/.smartbiz_admin_credentials" ]; then
    echo "관리자 로그인: $(cat "${APP_DIR}/.smartbiz_admin_credentials")"
    echo "  (이 정보는 서버의 ${APP_DIR}/.smartbiz_admin_credentials 파일에도 저장되어 있습니다)"
fi
echo "적용된 업종 샘플: ${INDUSTRY}"
echo "관리자 → '테마 설정' 탭에서 회사정보/색상/사진을 원하는 대로 바꿀 수 있습니다."
echo "=================================================="
