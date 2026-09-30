// Android WebViews older than Chrome 84 ignore `gap` on flex containers, so buttons, badges and
// icons end up touching each other. Detect that once and, only then, emulate the gap with margins.

function flexGapSupported() {
    const box = document.createElement('div');
    box.style.cssText = 'display:flex;flex-direction:column;row-gap:1px;position:absolute;visibility:hidden';
    box.appendChild(document.createElement('div'));
    box.appendChild(document.createElement('div'));
    document.body.appendChild(box);
    const supported = box.scrollHeight === 1;
    box.remove();

    return supported;
}

function px(value) {
    const n = parseFloat(value);

    return Number.isNaN(n) ? 0 : n;
}

function emulateFlexGap() {
    document.querySelectorAll('.flex, .inline-flex').forEach((el) => {
        const style = getComputedStyle(el);
        const rowGap = px(style.rowGap);
        const colGap = px(style.columnGap);
        const kids = Array.prototype.slice.call(el.children);

        kids.forEach((kid) => {
            if (kid.getAttribute('data-gap-fix')) {
                kid.style.marginTop = '';
                kid.style.marginLeft = '';
                kid.style.marginRight = '';
                kid.style.marginBottom = '';
                kid.removeAttribute('data-gap-fix');
            }
        });

        if (!rowGap && !colGap) {
            return;
        }

        const column = style.flexDirection.indexOf('column') === 0;
        const wraps = style.flexWrap !== 'nowrap';

        kids.forEach((kid, index) => {
            if (wraps) {
                kid.style.marginRight = colGap + 'px';
                kid.style.marginBottom = rowGap + 'px';
            } else if (index > 0 && column) {
                kid.style.marginTop = rowGap + 'px';
            } else if (index > 0) {
                kid.style.marginLeft = colGap + 'px';
            } else {
                return;
            }
            kid.setAttribute('data-gap-fix', '1');
        });
    });
}

export function installFlexGapFallback() {
    const start = () => {
        if (flexGapSupported()) {
            return;
        }

        let timer = null;
        const run = () => {
            clearTimeout(timer);
            timer = setTimeout(emulateFlexGap, 50);
        };

        emulateFlexGap();
        window.addEventListener('resize', run);
        window.addEventListener('load', run);
        new MutationObserver(run).observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
}
