# GNUBoard5 설치

Docker Compose로 그누보드5(PHP + MariaDB)를 실행하기 위한 구성입니다.
저장소 자체에는 그누보드5 소스코드를 포함하지 않으며, 이미지 빌드 시점에
[gnuboard/g5](https://github.com/gnuboard/g5) 공식 저장소에서 소스를 내려받습니다.

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

4. 설치가 끝나면 `/install` 디렉터리는 자동으로 잠깁니다. 완전히 제거하려면:

   ```bash
   docker compose exec web rm -rf /var/www/html/install
   ```

## 데이터 보존

- 업로드/설정 파일: `gnuboard5_data` 볼륨 (`/var/www/html/data`)
- DB 데이터: `gnuboard5_db` 볼륨

## 버전 고정

기본값은 `master` 브랜치입니다. 특정 태그로 고정하려면 `.env`의
`GNUBOARD5_REF`를 원하는 태그명(예: `5.5.1`)으로 지정한 뒤 다시 빌드하세요.
