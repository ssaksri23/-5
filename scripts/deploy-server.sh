#!/usr/bin/env bash
# 외부 리눅스 서버(예: 129.121.127.196)에 이 저장소의 GNUBoard5 + 스마트비즈 테마를
# Docker Compose로 배포하고, 지정한 도메인에 Nginx 리버스 프록시 + Let's Encrypt
# SSL을 붙이는 스크립트다. 이 서버에 root로 SSH 접속한 뒤 그 서버 위에서 직접
# 실행한다(이 저장소 세션 안에서 원격으로 실행하는 스크립트가 아니다).
#
# 사용 예:
#   sudo bash deploy-server.sh mysite.example.com admin@example.com
#
# 이 서버에 이미 다른 사이트/서비스가 떠 있을 수 있으므로:
# - 80/443 포트를 이미 쓰는 웹서버가 있으면 건드리지 않고, 우리 컨테이너는
#   내부적으로 비어있는 다른 포트(기본 8081, 사용 중이면 자동으로 다음 번호)로만
#   띄운 뒤 Nginx가 도메인 이름 기준으로만 그 포트로 연결해준다.
# - 기존 Nginx 설정 파일은 수정하지 않고, 새 도메인 전용 설정 파일만 추가한다.
set -euo pipefail

DOMAIN="${1:-}"
EMAIL="${2:-}"
REPO_URL="${REPO_URL:-https://github.com/ssaksri23/-5.git}"
REPO_BRANCH="${REPO_BRANCH:-claude/gnuboard5-sellable-theme}"
APP_DIR="${APP_DIR:-/opt/smartbiz-gnuboard5}"
WEB_PORT_START="${WEB_PORT_START:-8081}"

if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
    echo "사용법: sudo bash deploy-server.sh <도메인> <SSL 인증서 알림용 이메일>" >&2
    echo "예:     sudo bash deploy-server.sh mysite.example.com admin@example.com" >&2
    exit 1
fi

if [ "$(id -u)" -ne 0 ]; then
    echo "root 권한으로 실행해주세요 (sudo bash deploy-server.sh ...)" >&2
    exit 1
fi

echo "==> 1) 빈 포트 찾기 (${WEB_PORT_START}부터)"
WEB_PORT="$WEB_PORT_START"
while ss -ltn 2>/dev/null | awk '{print $4}' | grep -q ":${WEB_PORT}\$"; do
    WEB_PORT=$((WEB_PORT + 1))
done
echo "    사용할 컨테이너 포트: ${WEB_PORT} (127.0.0.1에만 바인딩, 외부에는 열지 않음)"

echo "==> 2) 필수 패키지 설치 확인 (docker, docker compose, git, nginx, certbot)"
if ! command -v docker >/dev/null 2>&1; then
    curl -fsSL https://get.docker.com | sh
fi
if ! docker compose version >/dev/null 2>&1; then
    apt-get update -y && apt-get install -y docker-compose-plugin
fi
apt-get update -y
apt-get install -y git nginx certbot python3-certbot-nginx

echo "==> 3) 소스 코드 준비 (${APP_DIR})"
if [ -d "$APP_DIR/.git" ]; then
    git -C "$APP_DIR" fetch origin "$REPO_BRANCH"
    git -C "$APP_DIR" checkout "$REPO_BRANCH"
    git -C "$APP_DIR" reset --hard "origin/$REPO_BRANCH"
else
    git clone --branch "$REPO_BRANCH" "$REPO_URL" "$APP_DIR"
fi
cd "$APP_DIR"

echo "==> 4) 환경 변수 파일 생성 (.env) — 이미 있으면 그대로 둔다"
if [ ! -f .env ]; then
    RAND_PW="$(head -c 24 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 20)"
    RAND_ROOT_PW="$(head -c 24 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 20)"
    cat > .env <<EOF
WEB_PORT=${WEB_PORT}
MYSQL_DATABASE=gnuboard5
MYSQL_USER=gnuboard5
MYSQL_PASSWORD=${RAND_PW}
MYSQL_ROOT_PASSWORD=${RAND_ROOT_PW}
EOF
    echo "    .env 새로 생성함 (DB 비밀번호 자동 생성됨, ${APP_DIR}/.env 에서 확인 가능)"
else
    # 기존 .env는 유지하되 WEB_PORT만 이번에 고른 빈 포트로 맞춰준다.
    if grep -q '^WEB_PORT=' .env; then
        sed -i "s/^WEB_PORT=.*/WEB_PORT=${WEB_PORT}/" .env
    else
        echo "WEB_PORT=${WEB_PORT}" >> .env
    fi
    echo "    기존 .env 유지, WEB_PORT만 ${WEB_PORT}로 갱신"
fi

echo "==> 5) 컨테이너를 127.0.0.1 에만 바인딩하도록 오버라이드 파일 생성"
# 원본 docker-compose.yml 은 건드리지 않는다(다른 브랜치와 충돌 방지).
# 서버 전체 80 포트가 아니라 로컬호스트의 지정 포트에만 바인딩해서, 외부에서는
# 반드시 Nginx(도메인 이름 기반)를 거치도록 강제한다.
cat > docker-compose.override.yml <<EOF
services:
  web:
    ports:
      - "127.0.0.1:${WEB_PORT}:80"
EOF

echo "==> 6) 컨테이너 빌드 및 기동"
docker compose up -d --build

echo "==> 7) 사이트가 뜰 때까지 대기"
for i in $(seq 1 30); do
    if curl -fsS "http://127.0.0.1:${WEB_PORT}/" >/dev/null 2>&1; then
        echo "    사이트 응답 확인됨"
        break
    fi
    sleep 2
done

echo "==> 8) Nginx 사이트 설정 추가 (${DOMAIN} 전용 파일만 새로 생성, 기존 설정은 유지)"
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

echo "==> 9) Let's Encrypt SSL 발급 및 자동 HTTPS 전환 (certbot)"
certbot --nginx -d "$DOMAIN" -m "$EMAIL" --agree-tos --redirect --non-interactive

echo ""
echo "완료! https://${DOMAIN} 로 접속해보세요."
echo "관리자 로그인은 그누보드5 설치가 아직 안 되어 있다면 https://${DOMAIN}/install/ 로 먼저 설치를 진행하세요."
echo "테마 활성화: 관리자 → 환경설정 → 테마 설정 → 스마트비즈 선택 → 적용"
echo "이후 관리자 메뉴의 '테마 설정' 탭 → 설치 마법사에서 게시판을 생성하세요."
