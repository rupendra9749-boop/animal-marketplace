import Alpine from 'alpinejs';
import { installFlexGapFallback } from './flex-gap';

window.Alpine = Alpine;

/**
 * State + city picker. The state is a normal list; the city is a type-to-search list of that state's
 * cities and towns (loaded once per state), so it stays usable on a phone even for states with 600+ towns.
 */
Alpine.data('locationPicker', (cfg) => ({
    state: cfg.state || '',
    city: cfg.city || '',
    query: cfg.city || '',
    open: false,
    loading: false,
    cities: [], // [{ n: 'Ludhiana', h: 'लुधियाना' }] - n is what is saved, h is what a Hindi reader sees
    cache: {},

    init() {
        if (this.state) {
            this.load(this.state).then(() => {
                if (this.city) {
                    this.query = this.labelFor(this.city);
                }
            });
        }
    },

    label(c) {
        return cfg.hi ? c.h : c.n;
    },

    labelFor(name) {
        const c = this.cities.find((x) => x.n === name);

        return c ? this.label(c) : name;
    },

    async load(state) {
        if (!state) {
            this.cities = [];
            return;
        }
        if (this.cache[state]) {
            this.cities = this.cache[state];
            return;
        }
        this.loading = true;
        try {
            const res = await fetch(cfg.url + '?state=' + encodeURIComponent(state) + (cfg.hi ? '&hi=1' : ''), { headers: { Accept: 'application/json' } });
            const rows = await res.json();
            this.cache[state] = rows.map((r) => (Array.isArray(r) ? { n: r[0], h: r[1] } : { n: r, h: r }));
            this.cities = this.cache[state];
        } catch (e) {
            this.cities = [];
        }
        this.loading = false;
    },

    changeState() {
        this.city = '';
        this.query = '';
        this.load(this.state);
    },

    // People may type the English or the Hindi spelling.
    get matches() {
        const q = this.query.trim().toLowerCase();
        if (!q) {
            return this.cities.slice(0, 60);
        }
        const at = (c) => Math.min(...[c.n, c.h].map((t) => { const i = t.toLowerCase().indexOf(q); return i < 0 ? 99 : i; }));
        const starts = this.cities.filter((c) => at(c) === 0);
        const inside = this.cities.filter((c) => at(c) > 0 && at(c) < 99);

        return starts.concat(inside).slice(0, 60);
    },

    pick(c) {
        this.city = c.n;
        this.query = this.label(c);
        this.open = false;
    },

    typed() {
        this.open = true;
        const q = this.query.trim().toLowerCase();
        const exact = this.cities.find((c) => c.n.toLowerCase() === q || c.h.toLowerCase() === q);
        this.city = exact ? exact.n : '';
    },

    enter() {
        if (this.matches.length) {
            this.pick(this.matches[0]);
        }
    },

    closeSoon() {
        setTimeout(() => {
            this.open = false;
            if (this.city) {
                this.query = this.labelFor(this.city);
            }
        }, 150);
    },
}));

/** "Use my location": GPS -> nearest city (server side) -> reload the page around that city. */
Alpine.data('locationDetect', (cfg) => ({
    busy: false,
    error: '',

    detect() {
        if (!navigator.geolocation) {
            this.error = cfg.messages.unsupported;
            return;
        }
        this.busy = true;
        this.error = '';

        const ask = (precise, ms) => new Promise((ok, fail) => navigator.geolocation.getCurrentPosition(ok, fail, { enableHighAccuracy: precise, timeout: ms, maximumAge: 600000 }));

        ask(false, 8000)
            .catch((err) => (err.code === 1 ? Promise.reject(err) : ask(true, 15000)))
            .then((pos) => fetch(cfg.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
            }))
            .then((res) => res.json().then((body) => ({ ok: res.ok, body })))
            .then(({ ok, body }) => {
                if (!ok) {
                    throw { message: body.message };
                }
                const url = new URL(window.location.href);
                ['state', 'city', 'page'].forEach((key) => url.searchParams.delete(key));
                window.location.href = url.toString();
            })
            .catch((err) => {
                this.busy = false;
                this.error = err.code === 1 ? cfg.messages.denied : (err.message || cfg.messages.failed);
            });
    },
}));

Alpine.start();
installFlexGapFallback();

/**
 * The back arrow (see components/back-button): shown on every page that has one. It goes to the page before this one, or to
 * the button's own address when the page names one (data-explicit) or the person arrived here directly.
 */
function initBackButtons() {
    document.querySelectorAll('[data-back]').forEach((button) => {
        button.classList.remove('hidden');
        button.classList.add('inline-flex');
        button.addEventListener('click', () => {
            if (button.hasAttribute('data-explicit') || window.history.length <= 1) {
                window.location.href = button.dataset.back;
            } else {
                window.history.back();
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBackButtons);
} else {
    initBackButtons();
}
