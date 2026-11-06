document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const target = this.getAttribute('data-tab');
            document.querySelectorAll('.tab-content').forEach(function (content) {
                content.classList.toggle('active', content.id === target);
            });
            document.querySelectorAll('.tab-button').forEach(function (btn) {
                btn.classList.toggle('active', btn === button);
            });
        });
    });

    const wrap = document.getElementById('wrap');
    if (wrap) {
        const openLink = document.getElementById('open');
        const closeLink = document.getElementById('close');
        if (openLink) {
            openLink.addEventListener('click', function (event) {
                event.preventDefault();
                wrap.style.transform = 'translate3d(0, 0, 0)';
            });
        }
        if (closeLink) {
            closeLink.addEventListener('click', function (event) {
                event.preventDefault();
                wrap.style.transform = 'translate3d(-335px, 0, 0)';
            });
        }
    }
});
