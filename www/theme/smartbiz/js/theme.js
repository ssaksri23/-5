(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var menuBtn = document.getElementById('sb_menu_btn');
        var gnb = document.getElementById('sb_gnb');
        if (menuBtn && gnb) {
            menuBtn.addEventListener('click', function () {
                var open = gnb.classList.toggle('sb_open');
                menuBtn.classList.toggle('sb_open', open);
                menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                document.body.classList.toggle('sb_menu_open', open);
            });

            gnb.addEventListener('click', function (e) {
                if (e.target.tagName === 'A' && gnb.classList.contains('sb_open')) {
                    gnb.classList.remove('sb_open');
                    menuBtn.classList.remove('sb_open');
                    menuBtn.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('sb_menu_open');
                }
            });
        }

        // 헤더 높이만큼 섹션 스크롤 오프셋을 줘서 앵커 이동 시 헤더에 안 가리게 함
        var header = document.getElementById('sb_header');
        if (header) {
            var setOffset = function () {
                document.documentElement.style.setProperty('--sb-header-h', header.offsetHeight + 'px');
            };
            setOffset();
            window.addEventListener('resize', setOffset);
        }
    });
})();
