import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

// Focus trap generik untuk seluruh dialog Alpine. Menjaga Tab tetap di dalam
// dialog dan mengembalikan fokus ke trigger setelah dialog ditutup.
const dialogFocusState = new WeakMap();
const focusableSelector = [
    'a[href]', 'button:not([disabled])', 'input:not([disabled])',
    'select:not([disabled])', 'textarea:not([disabled])', '[tabindex]:not([tabindex="-1"])',
].join(',');

const visibleDialogs = () => Array.from(document.querySelectorAll('[role="dialog"][aria-modal="true"]'))
    .filter((dialog) => dialog.getClientRects().length > 0 && getComputedStyle(dialog).visibility !== 'hidden');

const syncDialogFocus = () => {
    const visible = new Set(visibleDialogs());

    document.querySelectorAll('[role="dialog"][aria-modal="true"]').forEach((dialog) => {
        const state = dialogFocusState.get(dialog);
        if (visible.has(dialog) && !state?.open) {
            dialogFocusState.set(dialog, { open: true, returnTo: document.activeElement });
            queueMicrotask(() => {
                const target = dialog.querySelector('[autofocus], input:not([type="hidden"]), button:not([disabled]), a[href]');
                target?.focus({ preventScroll: true });
            });
        } else if (!visible.has(dialog) && state?.open) {
            dialogFocusState.set(dialog, { ...state, open: false });
            if (state.returnTo?.isConnected) queueMicrotask(() => state.returnTo.focus({ preventScroll: true }));
        }
    });
};

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Tab') return;
    const dialog = visibleDialogs().at(-1);
    if (!dialog) return;
    const items = Array.from(dialog.querySelectorAll(focusableSelector)).filter((item) => item.getClientRects().length > 0);
    if (!items.length) return;
    const first = items[0];
    const last = items.at(-1);
    if (event.shiftKey && (document.activeElement === first || !dialog.contains(document.activeElement))) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && (document.activeElement === last || !dialog.contains(document.activeElement))) {
        event.preventDefault();
        first.focus();
    }
}, true);

new MutationObserver(syncDialogFocus).observe(document.documentElement, {
    subtree: true,
    attributes: true,
    attributeFilter: ['style', 'class', 'aria-hidden'],
});
queueMicrotask(syncDialogFocus);

// Theme store (dark / light) — persist di localStorage, sinkron dengan class .dark pada <html>.
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try {
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        } catch (e) { /* abaikan storage error */ }
    },
});

// Navbar: state scroll, active section (scroll spy), dan kunci scroll saat menu mobile terbuka.
Alpine.data('siteNav', () => ({
    scrolled: false,
    open: false,
    active: 'hero',
    init() {
        const onScroll = () => (this.scrolled = window.scrollY > 24);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        // Kunci scroll body ketika drawer mobile terbuka dan beri sinyal ke floating controls.
        this.$watch('open', (v) => {
            document.body.classList.toggle('overflow-hidden', v);
            document.body.classList.toggle('site-drawer-open', v);
            document.documentElement.classList.toggle('site-drawer-open', v);
            document.querySelectorAll('.floating-actions').forEach((el) => {
                el.classList.toggle('is-hidden', v);
                el.toggleAttribute('aria-hidden', v);
                el.style.opacity = v ? '0' : '';
                el.style.pointerEvents = v ? 'none' : '';
                el.style.transform = v ? 'translateY(0.5rem)' : '';
            });
            if (v) this.$nextTick(() => this.$refs.drawerClose?.focus());
        });

        this._onResize = () => {
            if (window.innerWidth >= 1280 && this.open) this.closeMenu();
        };
        window.addEventListener('resize', this._onResize, { passive: true });
        this._onKeydown = (e) => {
            if (e.key === 'Escape') this.closeMenu();
        };
        window.addEventListener('keydown', this._onKeydown);
        window.addEventListener('keyup', this._onKeydown);

        // Scroll spy: tandai section yang sedang aktif.
        const sections = document.querySelectorAll('main section[id]');
        if ('IntersectionObserver' in window && sections.length) {
            const spy = new IntersectionObserver(
                (entries) => {
                    entries.forEach((e) => {
                        if (e.isIntersecting) this.active = e.target.id;
                    });
                },
                { rootMargin: '-45% 0px -50% 0px', threshold: 0 }
            );
            sections.forEach((s) => spy.observe(s));
        }
    },
    openMenu() {
        this.open = true;
    },
    closeMenu() {
        this.open = false;
    },
    destroy() {
        document.body.classList.remove('overflow-hidden', 'site-drawer-open');
        document.documentElement.classList.remove('site-drawer-open');
        document.querySelectorAll('.floating-actions').forEach((el) => {
            el.classList.remove('is-hidden');
            el.removeAttribute('aria-hidden');
            el.style.opacity = '';
            el.style.pointerEvents = '';
            el.style.transform = '';
        });
        if (this._onResize) window.removeEventListener('resize', this._onResize);
        if (this._onKeydown) window.removeEventListener('keydown', this._onKeydown);
        if (this._onKeydown) window.removeEventListener('keyup', this._onKeydown);
    },
}));

Alpine.data('floatingControls', () => ({
    showTop: false,
    hideWhatsApp: false,
    reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    init() {
        const contactSection = document.querySelector('#kontak');
        this._onScroll = () => {
            const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
            const threshold = maxScroll < 650 ? Math.max(140, maxScroll * 0.45) : 500;
            this.showTop = window.scrollY > threshold;

            if (contactSection) {
                const rect = contactSection.getBoundingClientRect();
                this.hideWhatsApp = window.innerWidth < 1280
                    && rect.bottom > 0
                    && rect.top < window.innerHeight;
            }
        };
        this._onScroll();
        window.addEventListener('scroll', this._onScroll, { passive: true });
        this._onResize = () => this._onScroll();
        window.addEventListener('resize', this._onResize, { passive: true });
    },
    toTop() {
        window.scrollTo({ top: 0, behavior: this.reduce ? 'auto' : 'smooth' });
    },
    destroy() {
        if (this._onScroll) window.removeEventListener('scroll', this._onScroll);
        if (this._onResize) window.removeEventListener('resize', this._onResize);
    },
}));

Alpine.data('whatsappLeadForm', (cfg = {}) => ({
    open: false,
    inline: Boolean(cfg.inline),
    mode: cfg.inline ? 'inline' : 'undecided',
    waNumber: cfg.waNumber || '',
    services: Array.isArray(cfg.services) ? cfg.services : [],
    values: {
        name: '',
        service: '',
        business: '',
        domicile: '',
        needs: '',
    },
    errors: {},
    formError: '',
    // Daftar kata kasar/terlarang multibahasa untuk moderasi input.
    // Deteksi juga menoleransi typo/plesetan lewat kunci fonetik (_confusable).
    bannedWords: [
        // Indonesia — umpatan umum
        'anjing', 'anjeng', 'anjay', 'anjir', 'anjrit', 'anjas', 'asu', 'asw',
        'njir', 'njeng', 'njing', 'njay', 'njrit',
        'bangsat', 'bajingan', 'bangke', 'bangsad',
        'kontol', 'kntl', 'kontl', 'memek', 'meki', 'mek', 'itil',
        'ngentot', 'ngentod', 'entot', 'ngewe', 'ewe', 'ngentotin', 'ngentod',
        'pepek', 'pepex', 'tempik', 'tempek', 'titit', 'jembut', 'jembud',
        'coli', 'colmek', 'peju', 'peler', 'pelir', 'burit', 'silit', 'kanjut',
        'taik', 'tai', 'tae', 'taek', 'eek',
        'tolol', 'goblok', 'goblog', 'bego', 'dungu', 'idiot', 'oon', 'geblek',
        'kampret', 'brengsek', 'keparat', 'laknat', 'bacot', 'bacod', 'cangkem',
        'jancok', 'jancuk', 'jancuq', 'cuk', 'cok', 'cukimai', 'cukimay', 'kimak', 'cukima',
        'pantek', 'pantat', 'setan', 'sialan', 'sinting', 'gendeng', 'gemblung', 'sarap',
        'pelacur', 'lonte', 'sundal', 'perek', 'jablay', 'bispak', 'pecun', 'sundala',
        'monyet', 'monyong', 'kunyuk', 'babi', 'bagong', 'kirik', 'kadal',
        'bencong', 'banci', 'maho', 'homo', 'lesbi',
        // Jawa / Sunda / daerah
        'matamu', 'raimu', 'ndasmu', 'picek', 'telek', 'silit', 'kenthu', 'kentu',
        'kehed', 'belegug', 'goblog', 'borokoko', 'sia', 'siah', 'jancik',
        // Melayu / Malaysia — umpatan
        'pukima', 'pukimak', 'pundek', 'cibai', 'cibmai', 'lancau', 'sohai', 'babisss',
        // Inggris — umpatan
        'fuck', 'fuk', 'fucker', 'fucking', 'motherfucker', 'shit', 'bullshit', 'bitch',
        'bastard', 'asshole', 'ass', 'dick', 'dickhead', 'pussy', 'cunt', 'cock',
        'whore', 'slut', 'nigger', 'nigga', 'faggot', 'fag', 'retard', 'moron',
        'douche', 'wanker', 'bollocks', 'prick', 'twat', 'jackass', 'dumbass',
        // Spanyol / lainnya
        'puta', 'mierda', 'cabron', 'pendejo', 'joder', 'cono', 'chinga',
        // Terlarang / SARA / vulgar
        'kafir', 'perkosa', 'porno', 'bokep', 'ngentotin', 'bispak',
    ],
    // Kata sah yang tidak boleh dianggap kasar (pelindung dari salah deteksi).
    allowWords: [
        'asuransi', 'asumsi', 'kontrol', 'protokol', 'konstruksi', 'kontrak',
        'pantai', 'santai', 'cukup', 'sukuk', 'sukses', 'spesialis', 'analisis',
        'bisnis', 'konsultasi', 'dokumen', 'produk', 'produksi', 'distribusi',
        'administrasi', 'registrasi', 'sertifikasi', 'klasifikasi', 'identifikasi',
        'kabupaten', 'bangunan', 'gedung', 'dagang', 'pedagang', 'perdagangan',
        'tabungan', 'kemasan', 'pengemasan', 'lonteng', 'kontingen', 'ekonomi',
        'peternakan', 'pertanian', 'perikanan', 'koperasi', 'notaris', 'pajak',
    ],
    _bannedCanon: null,
    init() {
        // Form di section Kontak selalu tampil dan tidak menangani trigger modal global.
        if (this.inline) {
            this._bannedCanon = new Set(
                this.bannedWords.filter((w) => w.length >= 4).map((w) => this._confusable(w))
            );
            return;
        }

        this._openHandler = (event) => this.openForm(event.detail || {});
        this._clickHandler = (event) => {
            const trigger = event.target.closest && event.target.closest('[data-whatsapp-lead]');
            if (!trigger) return;

            event.preventDefault();
            this.openForm({
                mode: trigger.dataset.mode || '',
                service: trigger.dataset.service || '',
                needs: trigger.dataset.needs || '',
            });
        };

        window.addEventListener('open-whatsapp-lead', this._openHandler);
        document.addEventListener('click', this._clickHandler);

        this.$watch('open', (value) => {
            document.body.classList.toggle('overflow-hidden', value);
            document.body.classList.toggle('whatsapp-lead-open', value);
            document.documentElement.classList.toggle('whatsapp-lead-open', value);
        });

        // Precompute kunci fonetik untuk kata terlarang (>=4 huruf) — toleransi typo.
        this._bannedCanon = new Set(
            this.bannedWords.filter((w) => w.length >= 4).map((w) => this._confusable(w))
        );
    },
    destroy() {
        if (this.inline) return;
        if (this._openHandler) window.removeEventListener('open-whatsapp-lead', this._openHandler);
        if (this._clickHandler) document.removeEventListener('click', this._clickHandler);
        document.body.classList.remove('whatsapp-lead-open');
        document.documentElement.classList.remove('whatsapp-lead-open');
    },
    openForm(detail = {}) {
        this.errors = {};
        this.formError = '';
        const requestedMode = detail.mode || (detail.service ? 'service' : 'undecided');
        this.mode = requestedMode === 'service' ? 'service' : 'undecided';

        if (this.mode === 'service') {
            this.values.service = detail.service || '';
        } else {
            this.values.service = '';
        }

        if (detail.needs) this.values.needs = detail.needs;
        this.open = true;
        this.$nextTick(() => this.$refs.name?.focus());
    },
    close() {
        this.open = false;
        this.errors = {};
        this.formError = '';
    },
    clearError(field) {
        if (this.errors[field]) this.errors[field] = '';
        if (this.formError) this.formError = '';
    },
    // Normalisasi teks agar penyamaran (leetspeak, spasi, huruf berulang) tetap terdeteksi.
    _normalizeForFilter(text) {
        const subs = {
            '@': 'a', '4': 'a', '8': 'b', '(': 'c', '3': 'e', '6': 'g',
            '1': 'i', '!': 'i', '|': 'i', '0': 'o', '5': 's', '$': 's',
            '7': 't', '+': 't', '9': 'g', '2': 'z',
        };
        return String(text || '')
            .toLowerCase()
            .normalize('NFKD').replace(/[̀-ͯ]/g, '') // buang aksen: coño -> cono
            .replace(/[@48(3619!|05$7+92]/g, (c) => subs[c] || c);
    },
    // Kunci fonetik: menyamakan konsonan mirip agar typo/plesetan tetap kena.
    // Contoh: jembut/jembud, goblok/goblog, bangsat/bangsad -> kunci sama.
    _confusable(word) {
        return String(word || '')
            .replace(/ph/g, 'f')
            .replace(/ck/g, 'k')
            .replace(/kh/g, 'k')
            .replace(/sy|sh/g, 's')
            .replace(/x/g, 'k')
            .replace(/w/g, 'v')
            .replace(/[dt]/g, 't')   // d <-> t
            .replace(/[kgq]/g, 'k')  // k <-> g <-> q
            .replace(/[sz]/g, 's')   // s <-> z
            .replace(/[fv]/g, 'f')   // f <-> v <-> w
            .replace(/j/g, 'c')      // j <-> c
            .replace(/(.)\1+/g, '$1'); // huruf berulang: anjenggg -> anjeng
    },
    // Deteksi kata terlarang: token exact, dedup, kunci fonetik, & versi rapat.
    hasBadWords(text) {
        const base = this._normalizeForFilter(text);
        if (!base.trim()) return false;

        const canonSet = this._bannedCanon || new Set(
            this.bannedWords.filter((w) => w.length >= 4).map((w) => this._confusable(w))
        );

        const tokens = base.split(/[^a-z]+/).filter(Boolean);
        for (const token of tokens) {
            if (this.allowWords.includes(token)) continue; // lindungi kata sah
            const dedup = token.replace(/(.)\1+/g, '$1'); // "anjenggg" -> "anjeng"
            if (this.bannedWords.includes(token) || this.bannedWords.includes(dedup)) {
                return true;
            }
            const key = this._confusable(token); // toleransi typo/plesetan
            if (key.length >= 4 && canonSet.has(key)) return true;
        }

        // Tangkap penyamaran dengan sisipan: "a-n-j-i-n-g" / "b a n g s a t".
        const collapsed = base.replace(/[^a-z]/g, '');
        const collapsedCanon = this._confusable(collapsed);
        for (const word of this.bannedWords) {
            if (word.length < 5) continue;
            if (collapsed.includes(word) || collapsedCanon.includes(this._confusable(word))) {
                return true;
            }
        }

        // Kata pendek yang aman dicek sebagai substring (mis. "f*ck", "c u n t").
        const tight = ['fuck', 'fck', 'cunt', 'kntl', 'anjng', 'bngst', 'memek'];
        for (const word of tight) {
            if (collapsed.includes(word)) return true;
        }

        return false;
    },
    validate() {
        const errors = {};
        if (!this.values.name.trim()) errors.name = 'Nama wajib diisi.';
        if (!this.values.service.trim()) {
            errors.service = this.mode === 'service'
                ? 'Layanan wajib terisi dari card layanan.'
                : 'Pilih salah satu layanan yang dibutuhkan.';
        }
        if (!this.values.needs.trim()) {
            errors.needs = this.mode === 'service'
                ? 'Kebutuhan singkat wajib diisi.'
                : 'Kebutuhan atau kendala wajib diisi.';
        }

        // Moderasi kata kasar/terlarang pada seluruh isian teks bebas.
        const badLabels = {
            name: 'Nama',
            business: 'Jenis usaha/produk',
            domicile: 'Domisili',
            needs: 'Kebutuhan',
        };
        let profanityFound = false;
        for (const field of Object.keys(badLabels)) {
            if (!errors[field] && this.hasBadWords(this.values[field])) {
                errors[field] = `${badLabels[field]} mengandung kata yang tidak pantas. Mohon dibenahi terlebih dahulu.`;
                profanityFound = true;
            }
        }

        this.errors = errors;
        this.formError = profanityFound
            ? 'Terdeteksi kata-kata kasar atau terlarang. Mohon dibenahi terlebih dahulu sebelum melanjutkan ke WhatsApp.'
            : '';

        if (Object.keys(errors).length) {
            this.$nextTick(() => {
                const first = this.$el.querySelector('.wa-lead-input--error');
                if (first) first.focus({ preventScroll: false });
            });
            return false;
        }

        return true;
    },
    normalizedNumber() {
        let number = String(this.waNumber || '').replace(/\D/g, '');
        if (number.startsWith('08')) number = `62${number.slice(1)}`;
        else if (number.startsWith('8')) number = `62${number}`;
        return number;
    },
    message() {
        const business = this.values.business.trim() || 'Belum diisi';
        const domicile = this.values.domicile.trim() || 'Belum diisi';

        if (this.mode === 'service') {
            const service = this.values.service.trim();

            return [
                'Halo PT Zam Zam Khan, saya ingin konsultasi layanan.',
                '',
                `Nama: ${this.values.name.trim()}`,
                `Layanan: ${service}`,
                `Jenis usaha/produk: ${business}`,
                `Domisili: ${domicile}`,
                `Kebutuhan: ${this.values.needs.trim()}`,
                '',
                'Mohon arahan untuk proses selanjutnya.',
            ].join('\n');
        }

        return [
            'Halo PT Zam Zam Khan, saya ingin berkonsultasi mengenai layanan Anda.',
            '',
            `Nama: ${this.values.name.trim()}`,
            `Layanan: ${this.values.service.trim()}`,
            `Jenis usaha/produk: ${business}`,
            `Domisili: ${domicile}`,
            `Kebutuhan: ${this.values.needs.trim()}`,
            '',
            'Mohon arahan untuk proses selanjutnya.',
        ].join('\n');
    },
    submit() {
        if (!this.validate()) return;
        const number = this.normalizedNumber();
        const url = `https://wa.me/${number}?text=${encodeURIComponent(this.message())}`;
        window.open(url, '_blank', 'noopener');
        this.close();
    },
}));

// Testimonial / dokumentasi slider — scroll-snap + kontrol manual + autoplay halus.
Alpine.data('testimonialSlider', () => ({
    index: 0,
    count: 0,              // total kartu
    perView: 1,            // kartu terlihat per viewport (responsif)
    stops: 1,              // jumlah posisi geser unik = count - perView + 1
    autoTimer: null,       // interval autoplay
    resumeTimer: null,     // timeout untuk lanjut setelah interaksi manual
    paused: false,         // pause dari hover / focus
    reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    AUTO_MS: 5000,         // interval geser otomatis
    RESUME_MS: 7000,       // jeda lanjut autoplay setelah interaksi manual (6–8 dtk)
    init() {
        const track = this.$refs.track;
        this.count = track ? track.children.length : 0;
        this.$nextTick(() => { this.measure(); this.onScroll(); });
        this.startAuto();
        this._onVis = () => (document.hidden ? this.stopAuto() : this.tryStartAuto());
        document.addEventListener('visibilitychange', this._onVis);
        // Recompute saat breakpoint / lebar / toggle tema berubah (lebar kartu ikut berubah).
        this._onResize = () => {
            if (this._raf) return;
            this._raf = requestAnimationFrame(() => {
                this._raf = null;
                this.measure();
                this.goTo(this.index); // realign posisi bila perView berubah
            });
        };
        window.addEventListener('resize', this._onResize);
    },
    destroy() {
        this.stopAuto();
        clearTimeout(this.resumeTimer);
        if (this._raf) cancelAnimationFrame(this._raf);
        document.removeEventListener('visibilitychange', this._onVis);
        window.removeEventListener('resize', this._onResize);
    },
    step() {
        const track = this.$refs.track;
        const first = track && track.children[0];
        if (!first) return track ? track.clientWidth : 0;
        const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '0') || 0;
        return first.getBoundingClientRect().width + gap;
    },
    // Hitung kartu-terlihat & jumlah stop unik. Inti perbaikan "mentok 9/11":
    // dgn 3 kartu terlihat, posisi geser = 11 - 3 + 1 = 9, jadi 09/09 = benar-benar tuntas.
    measure() {
        const track = this.$refs.track;
        if (!track) return;
        const step = this.step();
        this.perView = step > 0 ? Math.max(1, Math.round(track.clientWidth / step)) : 1;
        this.stops = Math.max(1, this.count - this.perView + 1);
        if (this.index > this.stops - 1) this.index = this.stops - 1;
    },
    onScroll() {
        const track = this.$refs.track;
        if (!track) return;
        this.index = Math.max(0, Math.min(Math.round(track.scrollLeft / this.step()), this.stops - 1));
    },
    goTo(i) {
        const track = this.$refs.track;
        if (!track) return;
        i = Math.max(0, Math.min(i, this.stops - 1));
        this.index = i;
        track.scrollTo({ left: i * this.step(), behavior: this.reduce ? 'auto' : 'smooth' });
    },
    // Geser ke kiri → posisi berikutnya; loop halus ke awal di ujung.
    next() { this.goTo(this.index >= this.stops - 1 ? 0 : this.index + 1); },
    prev() { this.goTo(this.index <= 0 ? this.stops - 1 : this.index - 1); },

    /* ---------- Autoplay ---------- */
    canAuto() { return !this.reduce && !this.paused && !document.hidden && this.stops > 1; },
    startAuto() {
        if (this.autoTimer || !this.canAuto()) return;
        this.autoTimer = setInterval(() => this.next(), this.AUTO_MS);
    },
    stopAuto() {
        if (this.autoTimer) { clearInterval(this.autoTimer); this.autoTimer = null; }
    },
    tryStartAuto() { if (this.canAuto()) this.startAuto(); },

    // Hover / focus: pause langsung, lanjut saat keluar (jika tak ada interaksi tertunda).
    hoverPause() { this.paused = true; this.stopAuto(); },
    hoverResume() { this.paused = false; clearTimeout(this.resumeTimer); this.tryStartAuto(); },

    // Interaksi manual (arrow / dot / swipe-drag): reset timer, lanjut setelah RESUME_MS.
    userInteract() {
        this.stopAuto();
        clearTimeout(this.resumeTimer);
        this.resumeTimer = setTimeout(() => this.tryStartAuto(), this.RESUME_MS);
    },
    onPrev() { this.prev(); this.userInteract(); },
    onNext() { this.next(); this.userInteract(); },
    onDot(i) { this.goTo(i); this.userInteract(); },
}));

window.Alpine = Alpine;
Alpine.start();

let analyticsCharts = [];
let chartLib = null;

const destroyAnalyticsCharts = () => {
    analyticsCharts.forEach((chart) => chart.destroy());
    analyticsCharts = [];
};

// Chart.js weighs ~200 KB and only the admin analytics page draws anything, so
// it is fetched on demand rather than shipped to every visitor of the homepage.
const loadChart = async () => {
    if (!chartLib) {
        const { Chart, registerables } = await import('chart.js');
        Chart.register(...registerables);
        chartLib = Chart;
    }

    return chartLib;
};

const initVisitorAnalytics = async () => {
    const dataNode = document.querySelector('#visitor-analytics-data');
    const trendCanvas = document.querySelector('#visitor-trend-chart');
    const deviceCanvas = document.querySelector('#visitor-device-chart');
    if (!dataNode || !trendCanvas || !deviceCanvas) return;

    destroyAnalyticsCharts();

    let analytics;
    try {
        analytics = JSON.parse(dataNode.textContent);
    } catch (error) {
        return;
    }

    const Chart = await loadChart();

    // An admin partial navigation can swap the page out while the chunk loads.
    if (!trendCanvas.isConnected || !deviceCanvas.isConnected) return;

    const dark = document.body.dataset.adminTheme === 'dark';
    const textColor = dark ? 'rgba(255,255,255,.72)' : '#62585a';
    const gridColor = dark ? 'rgba(255,255,255,.08)' : 'rgba(100,60,65,.10)';
    const makeDatasets = (type) => [
        {
            label: 'Halaman dibuka',
            data: analytics.series.pageViews,
            borderColor: '#963741',
            backgroundColor: type === 'area' ? 'rgba(150,55,65,.20)' : 'rgba(150,55,65,.78)',
            pointBackgroundColor: '#963741',
            pointRadius: type === 'bar' ? 0 : 2.5,
            borderWidth: 2.2,
            fill: type === 'area',
            tension: 0.34,
        },
        {
            label: 'Sesi pengunjung',
            data: analytics.series.uniqueVisitors,
            borderColor: '#168b87',
            backgroundColor: type === 'area' ? 'rgba(22,139,135,.14)' : 'rgba(22,139,135,.72)',
            pointBackgroundColor: '#168b87',
            pointRadius: type === 'bar' ? 0 : 2.5,
            borderWidth: 2.2,
            fill: type === 'area',
            tension: 0.34,
        },
    ];

    let trendChart;
    const renderTrend = (selectedType = 'line') => {
        if (trendChart) trendChart.destroy();
        const chartType = selectedType === 'bar' ? 'bar' : 'line';
        trendChart = new Chart(trendCanvas, {
            type: chartType,
            data: { labels: analytics.series.labels, datasets: makeDatasets(selectedType) },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                animation: { duration: 500 },
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#241d1f', padding: 11, cornerRadius: 9 },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, precision: 0 } },
                },
            },
        });
        analyticsCharts[0] = trendChart;
    };

    renderTrend();
    document.querySelectorAll('[data-chart-type]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('[data-chart-type]').forEach((item) => item.classList.remove('is-active'));
            button.classList.add('is-active');
            renderTrend(button.dataset.chartType);
        });
    });

    analyticsCharts[1] = new Chart(deviceCanvas, {
        type: 'doughnut',
        data: {
            labels: analytics.devices.labels,
            datasets: [{
                data: analytics.devices.values,
                backgroundColor: ['#963741', '#168b87', '#d39b43'],
                borderColor: dark ? '#1b1718' : '#fff',
                borderWidth: 4,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#241d1f', padding: 10, cornerRadius: 9 },
            },
        },
    });
};

initVisitorAnalytics().catch(() => {});

// Gaya yang nilainya berasal dari server (URL gambar hero, delay animasi FAQ)
// dipasang lewat CSSOM, bukan atribut style di HTML: CSP style-src kita tidak
// mengizinkan unsafe-inline, sedangkan penulisan via el.style tidak diblokir.
const applyDynamicStyles = (root = document) => {
    root.querySelectorAll('[data-bg]').forEach((el) => {
        // Buang kutip dan backslash supaya nilai dari database tidak bisa keluar
        // dari url("...") dan menyuntikkan deklarasi CSS lain.
        const url = (el.dataset.bg || '').replace(/["'\\]/g, '');
        if (url) el.style.setProperty('background-image', `url("${url}")`);
    });

    root.querySelectorAll('[data-delay]').forEach((el) => {
        el.style.setProperty('--d', `${parseInt(el.dataset.delay, 10) || 0}ms`);
    });
};

applyDynamicStyles();

// Reveal-on-scroll (ringan, IntersectionObserver)
document.addEventListener('DOMContentLoaded', () => {
    applyDynamicStyles();

    const els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window) || els.length === 0) {
        els.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    // Replay: animasi berjalan setiap kali elemen masuk viewport (scroll bawah maupun atas).
    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const delay = entry.target.dataset.revealDelay || 0;
                    entry.target.style.transitionDelay = `${delay}ms`;
                    entry.target.classList.add('is-visible');
                } else {
                    entry.target.style.transitionDelay = '0ms';
                    entry.target.classList.remove('is-visible');
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -10% 0px' }
    );
    els.forEach((el) => io.observe(el));

    // Animated counter — jalan sekali saat terlihat. Elemen: [data-count]
    const counters = document.querySelectorAll('[data-count]');
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (counters.length) {
        const run = (el) => {
            const target = parseFloat(el.dataset.count);
            const suffix = el.dataset.suffix || '';
            if (reduce) { el.textContent = target + suffix; return; }
            const dur = 1400;
            const start = performance.now();
            const step = (now) => {
                const p = Math.min((now - start) / dur, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * eased) + suffix;
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };
        // Replay: hitung ulang setiap kali kartu statistik masuk viewport.
        const cio = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) run(e.target);
                });
            },
            { threshold: 0.4 }
        );
        counters.forEach((el) => cio.observe(el));
    }

    // Smooth scroll ter-easing untuk klik anchor (lebih halus dari native).
    const header = document.querySelector('header');
    const easeInOutCubic = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
    const scrollToY = (targetY, duration = 780) => {
        const startY = window.scrollY;
        const diff = targetY - startY;
        const rootStyle = document.documentElement.style;
        const prev = rootStyle.scrollBehavior;
        rootStyle.scrollBehavior = 'auto'; // hindari konflik dengan native smooth
        let start;
        const step = (ts) => {
            if (start === undefined) start = ts;
            const p = Math.min((ts - start) / duration, 1);
            window.scrollTo(0, startY + diff * easeInOutCubic(p));
            if (p < 1) requestAnimationFrame(step);
            else rootStyle.scrollBehavior = prev;
        };
        requestAnimationFrame(step);
    };

    document.querySelectorAll('a[href^="#"]').forEach((a) => {
        const hash = a.getAttribute('href');
        if (!hash || hash.length < 2) return;
        a.addEventListener('click', (e) => {
            const target = document.querySelector(hash);
            if (!target) return;
            e.preventDefault();
            const offset = (header ? header.offsetHeight : 80) + 14;
            const top = target.getBoundingClientRect().top + window.scrollY - offset;
            if (reduce) window.scrollTo(0, top);
            else scrollToY(top);
            history.pushState(null, '', hash);
        });
    });
});

// ---------------------------------------------------------------------------
// Navigasi instan (semua halaman: public + admin)
// Prerender via Speculation Rules saat hover + fallback prefetch link.
// ---------------------------------------------------------------------------
(function () {
    const internalGet = (a) =>
        a &&
        a.href &&
        a.target !== '_blank' &&
        !a.hasAttribute('download') &&
        !a.hasAttribute('data-no-prefetch') &&
        a.origin === location.origin &&
        !/\/admin\/logout/.test(a.pathname) &&
        !a.getAttribute('href').startsWith('#');

    // 1) Speculation Rules — prerender halaman internal saat hover (Chromium).
    if (HTMLScriptElement.supports && HTMLScriptElement.supports('speculationrules')) {
        const rules = {
            prerender: [{
                source: 'document',
                where: { and: [
                    { href_matches: '/*' },
                    { not: { href_matches: '/admin/logout' } },
                    { not: { href_matches: '/admin/reports/history/*/download' } },
                    { not: { selector_matches: '[data-no-prefetch]' } },
                ] },
                eagerness: 'moderate',
            }],
        };
        const s = document.createElement('script');
        s.type = 'speculationrules';
        s.nonce = document.querySelector('meta[name="csp-nonce"]')?.content || '';
        s.textContent = JSON.stringify(rules);
        document.head.appendChild(s);
    } else {
        // 2) Fallback: prefetch HTML saat kursor menyentuh link internal.
        const seen = new Set();
        const onHover = (e) => {
            const a = e.target.closest && e.target.closest('a');
            if (!internalGet(a) || seen.has(a.href)) return;
            seen.add(a.href);
            const l = document.createElement('link');
            l.rel = 'prefetch';
            l.href = a.href;
            document.head.appendChild(l);
        };
        document.addEventListener('mouseover', onHover, { passive: true });
        document.addEventListener('touchstart', onHover, { passive: true });
    }

    // 3) Feedback instan: tombol submit langsung "sibuk" (cegah double-submit).
    document.addEventListener('submit', (e) => {
        if (e.target.closest && e.target.closest('.wa-lead-panel')) return;
        const btn = e.target.querySelector('button[type="submit"], button:not([type])');
        if (btn && !btn.disabled) {
            btn.classList.add('opacity-70', 'cursor-wait', 'pointer-events-none');
            setTimeout(() => { btn.disabled = true; }, 0);

            // Respons download tidak mengganti halaman, jadi tombol harus aktif kembali.
            if (e.target.matches('[data-download-form]')) {
                setTimeout(() => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-70', 'cursor-wait', 'pointer-events-none');
                }, 1500);
            }
        }
    }, true);
})();

// ---------------------------------------------------------------------------
// Navigasi admin parsial: preload HTML, pertahankan shell, lalu tukar konten.
// ---------------------------------------------------------------------------
(function () {
    const shell = document.querySelector('.admin-shell');
    if (!shell) return;

    const pageCache = new Map();
    const CACHE_MS = 5 * 60_000;
    let navigationId = 0;

    const isInternalGet = (a) => a
        && a.href
        && a.target !== '_blank'
        && !a.hasAttribute('download')
        && !a.hasAttribute('data-no-prefetch')
        && a.origin === location.origin
        && a.pathname.startsWith('/admin')
        && !/\/admin\/logout/.test(a.pathname)
        && !a.getAttribute('href').startsWith('#');

    const fetchPage = (url) => {
        const cached = pageCache.get(url);
        if (cached && Date.now() - cached.createdAt < CACHE_MS) return cached.promise;

        const promise = fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'text/html',
                'X-Admin-Navigation': 'partial',
            },
        }).then(async (response) => {
            const type = response.headers.get('content-type') || '';
            if (!response.ok || !type.includes('text/html')) {
                throw new Error(`Admin navigation failed: ${response.status}`);
            }

            return {
                html: await response.text(),
                responseUrl: response.url,
            };
        });

        pageCache.set(url, { createdAt: Date.now(), promise });
        promise.catch(() => pageCache.delete(url));
        return promise;
    };

    const warmModule = (selector) => {
        const a = document.querySelector(selector);
        if (isInternalGet(a) && a.href !== location.href) fetchPage(a.href).catch(() => {});
    };

    const warmAdjacentModules = () => {
        // Tujuan berikutnya dipanaskan segera karena merupakan aksi utama workflow edit.
        warmModule('.admin-module-nav__item--next[data-admin-prefetch]');

        // Tujuan sebelumnya tetap low-priority agar tidak bersaing dengan render awal.
        const warmPrevious = () => warmModule('.admin-module-nav__item--prev[data-admin-prefetch]');
        if ('requestIdleCallback' in window) window.requestIdleCallback(warmPrevious, { timeout: 1200 });
        else window.setTimeout(warmPrevious, 350);
    };

    warmAdjacentModules();

    const warmFromEvent = (event) => {
        const a = event.target.closest && event.target.closest('a');
        if (isInternalGet(a) && a.href !== location.href) fetchPage(a.href).catch(() => {});
    };
    document.addEventListener('pointerover', warmFromEvent, { passive: true });
    document.addEventListener('focusin', warmFromEvent);
    document.addEventListener('touchstart', warmFromEvent, { passive: true });

    const clearNavigating = () => {
        shell.classList.remove('admin-is-navigating');
        shell.removeAttribute('aria-busy');
    };

    const syncPersistentShell = (nextDocument) => {
        const nextTitle = nextDocument.querySelector('.admin-topbar__center');
        const currentTitle = document.querySelector('.admin-topbar__center');
        if (nextTitle && currentTitle) currentTitle.innerHTML = nextTitle.innerHTML;

        const currentLinks = [...document.querySelectorAll('.admin-drawer__link')];
        const nextLinks = [...nextDocument.querySelectorAll('.admin-drawer__link')];
        currentLinks.forEach((link, index) => {
            const source = nextLinks[index];
            if (!source) return;
            link.className = source.className;
            if (source.hasAttribute('aria-current')) link.setAttribute('aria-current', source.getAttribute('aria-current'));
            else link.removeAttribute('aria-current');
        });
    };

    const replaceMain = (nextDocument, url, push) => {
        const currentMain = document.querySelector('main.admin-content');
        const nextMain = nextDocument.querySelector('main.admin-content');
        if (!currentMain || !nextMain) throw new Error('Admin content container not found.');

        const update = () => {
            destroyAnalyticsCharts();
            if (window.Alpine && Alpine.mutateDom) {
                Alpine.mutateDom(() => {
                    if (Alpine.destroyTree) Alpine.destroyTree(currentMain);
                    currentMain.innerHTML = nextMain.innerHTML;
                });
                Alpine.initTree(currentMain);
            } else {
                currentMain.innerHTML = nextMain.innerHTML;
            }

            document.title = nextDocument.title;
            syncPersistentShell(nextDocument);
            if (push) history.pushState({ adminNavigation: true }, '', url);
            window.scrollTo({ top: 0, behavior: 'auto' });
            clearNavigating();
            initVisitorAnalytics().catch(() => {});
        };

        if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            const transition = document.startViewTransition(update);
            transition.finished.finally(() => {
                clearNavigating();
                warmAdjacentModules();
            });
        } else {
            update();
            warmAdjacentModules();
        }
    };

    const navigate = async (url, push = true) => {
        const currentNavigation = ++navigationId;

        shell.classList.add('admin-is-navigating');
        shell.setAttribute('aria-busy', 'true');

        try {
            const page = await fetchPage(url);
            if (currentNavigation !== navigationId) return;
            if (!new URL(page.responseUrl).pathname.startsWith('/admin') || new URL(page.responseUrl).pathname === '/admin/login') {
                location.assign(page.responseUrl);
                return;
            }

            const nextDocument = new DOMParser().parseFromString(page.html, 'text/html');
            replaceMain(nextDocument, page.responseUrl, push);
        } catch (error) {
            location.assign(url);
        }
    };

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const a = event.target.closest && event.target.closest('a');
        if (!isInternalGet(a) || a.href === location.href) return;

        event.preventDefault();
        navigate(a.href);
    }, true);

    window.addEventListener('popstate', () => navigate(location.href, false));

    window.addEventListener('pageshow', clearNavigating);
})();
