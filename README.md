# GNUBoard5 설치

Docker Compose로 그누보드5(PHP + MariaDB)를 실행하기 위한 구성입니다.
`www/` 디렉터리에 [gnuboard/gnuboard5](https://github.com/gnuboard/gnuboard5)
공식 소스가 그대로 포함되어 있습니다.

## 요구 사항

- Docker, Docker Compose

## 설치 순서

1. 환경 변수 파일 생성 및 값 수정 (특히 비밀번호)

   ```bash
   cp .env.example .env
   ```

2. 빌드 및 실행

   ```bash
   docker compose up -d --build
   ```

3. 브라우저에서 설치 마법사 접속

   ```
   http://localhost:8080/install/
   ```

   설치 마법사에서 DB 정보를 입력합니다 (`.env`에 설정한 값과 동일하게):

   - DB 호스트: `db`
   - DB 이름 / 사용자 / 비밀번호: `.env`의 `MYSQL_DATABASE` / `MYSQL_USER` / `MYSQL_PASSWORD`

4. 설치가 끝나면 `/install` 디렉터리를 반드시 제거하세요 (보안을 위해 자동으로
   지워지지 않습니다):

   ```bash
   docker compose exec -u root web rm -rf /app/install
   ```

## 데이터 보존

- 업로드/설정 파일: `gnuboard5_data` 볼륨 (`/app/data`)
- DB 데이터: `gnuboard5_db` 볼륨

## 소스 업데이트

`www/` 디렉터리의 소스를 최신 버전으로 갱신하려면 `gnuboard/gnuboard5` 저장소의
최신 내용을 받아 `www/`에 덮어쓴 뒤 다시 빌드하세요.
