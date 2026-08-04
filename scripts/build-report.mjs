/**
 * Membangun laporan/laporanfinalku.pdf dari laporan/src/*.html.
 *
 *   npm install pdf-lib --no-save     (sekali, bila belum ada)
 *   node scripts/build-report.mjs
 *
 * Membutuhkan pdftotext (poppler/xpdf) pada PATH dan Chrome/Chromium untuk
 * render. Keluaran: laporan/laporanfinalku.pdf
 *
 * Alur:
 *   1. Render isi (Bab I-V, pustaka, lampiran) menjadi PDF.
 *   2. Baca ulang PDF tersebut dengan pdftotext untuk mengetahui halaman
 *      sebenarnya dari setiap judul, caption gambar, dan caption tabel.
 *   3. Susun Daftar Isi, Daftar Gambar, dan Daftar Tabel dari hasil langkah 2,
 *      lalu render bagian awal. Bagian awal dirender berulang sampai nomor
 *      halaman romawi di dalamnya stabil.
 *   4. Gabungkan kedua PDF dan bubuhkan nomor halaman: romawi kecil untuk
 *      bagian awal (sampul tanpa nomor) dan angka arab untuk isi.
 */
import { chromium } from 'playwright-core';
import { PDFDocument, StandardFonts, rgb } from 'pdf-lib';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const SRC = path.join(ROOT, 'laporan', 'src');
const TMP = path.join(ROOT, 'laporan', '.build');
const OUT = path.join(ROOT, 'laporan', 'laporanfinalku.pdf');

const MARGIN = { top: '4cm', right: '3cm', bottom: '3cm', left: '4cm' };
const FRONT_ANCHORS = ['HALAMAN PERSETUJUAN', 'KATA PENGANTAR', 'DAFTAR ISI', 'DAFTAR GAMBAR', 'DAFTAR TABEL'];

fs.rmSync(TMP, { recursive: true, force: true });
fs.mkdirSync(TMP, { recursive: true });

// --------------------------------------------------------------- utilitas
const ENTITIES = {
    amp: '&', lt: '<', gt: '>', quot: '"', apos: "'", nbsp: ' ',
    ndash: '–', mdash: '—', rarr: '→', times: '×',
    ldquo: '“', rdquo: '”', lsquo: '‘', rsquo: '’',
    oacute: 'ó', aacute: 'á', eacute: 'é', uuml: 'ü',
};

function decode(text) {
    return text.replace(/&(#x?[0-9a-fA-F]+|[a-zA-Z]+);/g, (whole, name) => {
        if (name.startsWith('#x') || name.startsWith('#X')) return String.fromCodePoint(parseInt(name.slice(2), 16));
        if (name.startsWith('#')) return String.fromCodePoint(parseInt(name.slice(1), 10));
        return ENTITIES[name] ?? whole;
    });
}

const stripTags = (html) => decode(html.replace(/<br\s*\/?>/gi, ' ').replace(/<[^>]+>/g, ''));
const norm = (text) => text.replace(/[ \s]+/g, ' ').replace(/[‐-―]/g, '-').trim().toLowerCase();
const escapeHtml = (text) => text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

function toRoman(n) {
    const table = [[1000, 'm'], [900, 'cm'], [500, 'd'], [400, 'cd'], [100, 'c'], [90, 'xc'],
        [50, 'l'], [40, 'xl'], [10, 'x'], [9, 'ix'], [5, 'v'], [4, 'iv'], [1, 'i']];
    let out = '';
    for (const [value, sym] of table) { while (n >= value) { out += sym; n -= value; } }
    return out;
}

function pdfPages(pdfPath) {
    const txtPath = pdfPath.replace(/\.pdf$/, '.txt');
    execFileSync('pdftotext', ['-layout', pdfPath, txtPath], { stdio: 'pipe' });
    const parts = fs.readFileSync(txtPath, 'utf8').split('\f');
    // pdftotext membubuhkan form feed setelah halaman terakhir; potongan kosong
    // di ujung bukan halaman dan akan menggeser seluruh penomoran bila dihitung.
    while (parts.length > 1 && parts[parts.length - 1].trim() === '') parts.pop();
    return parts.map(norm);
}

/** Halaman pertama (1-indexed) yang memuat label; 0 bila tidak ditemukan. */
function findPage(pages, label, fromPage = 0) {
    const needle = norm(label);
    const floor = Math.min(12, needle.length);
    for (let length = needle.length; length >= floor; length -= 6) {
        const probe = needle.slice(0, length);
        for (let i = fromPage; i < pages.length; i += 1) {
            if (pages[i].includes(probe)) return i + 1;
        }
    }
    return 0;
}

// ------------------------------------------------- ekstraksi entri dari isi
const bodyHtml = fs.readFileSync(path.join(SRC, 'body.html'), 'utf8');

const ENTRY_RE = /<h1 class="(?:bab|judul-lepas)"[^>]*>([\s\S]*?)<\/h1>|<h2>([\s\S]*?)<\/h2>|<h3>([\s\S]*?)<\/h3>|<p class="caption-gambar">([\s\S]*?)<\/p>|<p class="caption-tabel">([\s\S]*?)<\/p>/g;

const isi = [];
const gambar = [];
const tabel = [];

for (const match of bodyHtml.matchAll(ENTRY_RE)) {
    const [, h1, h2, h3, cg, ct] = match;
    if (h1 !== undefined) isi.push({ level: 1, label: stripTags(h1).replace(/\s+/g, ' ').trim() });
    else if (h2 !== undefined) isi.push({ level: 2, label: stripTags(h2).trim() });
    else if (h3 !== undefined) isi.push({ level: 3, label: stripTags(h3).trim() });
    else if (cg !== undefined) gambar.push({ label: stripTags(cg).trim() });
    else if (ct !== undefined) tabel.push({ label: stripTags(ct).trim() });
}

console.log(`Entri terdeteksi: ${isi.length} judul, ${gambar.length} gambar, ${tabel.length} tabel`);

// ------------------------------------------------------------------ render
const browser = await chromium.launch({ channel: 'chrome' }).catch(() => chromium.launch());
const page = await browser.newPage();

async function render(htmlPath, pdfPath) {
    await page.goto(pathToFileURL(htmlPath).href, { waitUntil: 'networkidle' });
    await page.emulateMedia({ media: 'print' });
    await page.pdf({ path: pdfPath, format: 'A4', printBackground: true, margin: MARGIN });
}

// 1) isi
const bodyPdf = path.join(TMP, 'body.pdf');
await render(path.join(SRC, 'body.html'), bodyPdf);
const bodyPages = pdfPages(bodyPdf);
console.log(`Isi laporan: ${bodyPages.length} halaman`);

let cursor = 0;
for (const entry of isi) {
    entry.page = findPage(bodyPages, entry.label, Math.max(0, cursor - 1));
    if (entry.page) cursor = entry.page;
    else { entry.page = cursor || 1; console.warn(`  ! judul tidak terdeteksi: ${entry.label}`); }
}
for (const list of [gambar, tabel]) {
    let seek = 0;
    for (const entry of list) {
        entry.page = findPage(bodyPages, entry.label, Math.max(0, seek - 1));
        if (entry.page) seek = entry.page;
        else { entry.page = seek || 1; console.warn(`  ! caption tidak terdeteksi: ${entry.label}`); }
    }
}

// 2) bagian awal, dirender ulang sampai nomor romawinya stabil
const baris = (cls, label, halaman) =>
    `<div class="baris ${cls}"><span class="teks">${escapeHtml(label)}</span>`
    + `<span class="titik"></span><span class="hal">${halaman}</span></div>`;

const frontTemplate = fs.readFileSync(path.join(SRC, 'front.html'), 'utf8');
const frontHtmlPath = path.join(SRC, '.front-generated.html');
const frontPdf = path.join(TMP, 'front.pdf');

let frontMap = { 'HALAMAN PERSETUJUAN': 2, 'KATA PENGANTAR': 3, 'DAFTAR ISI': 4, 'DAFTAR GAMBAR': 5, 'DAFTAR TABEL': 6 };
let frontPageCount = 6;

for (let pass = 1; pass <= 4; pass += 1) {
    const tocIsi = [
        ...FRONT_ANCHORS.map((a) => baris('lv1', a, toRoman(frontMap[a]))),
        ...isi.map((e) => baris(`lv${e.level}`, e.label, e.page)),
    ].join('\n');

    const html = frontTemplate
        .replace('<!--TOC-ISI-->', tocIsi)
        .replace('<!--TOC-GAMBAR-->', gambar.map((e) => baris('lv2', e.label, e.page)).join('\n'))
        .replace('<!--TOC-TABEL-->', tabel.map((e) => baris('lv2', e.label, e.page)).join('\n'));

    fs.writeFileSync(frontHtmlPath, html);
    await render(frontHtmlPath, frontPdf);

    const pages = pdfPages(frontPdf);
    frontPageCount = pages.length;
    const detected = {};
    let seek = 0;
    for (const anchor of FRONT_ANCHORS) {
        const found = findPage(pages, anchor, seek);
        detected[anchor] = found || frontMap[anchor];
        if (found) seek = found;
    }

    const stable = FRONT_ANCHORS.every((a) => detected[a] === frontMap[a]);
    frontMap = detected;
    console.log(`Bagian awal pass ${pass}: ${frontPageCount} halaman, ${stable ? 'stabil' : 'menyesuaikan ulang'}`);
    if (stable) break;
}

await browser.close();

// 3) gabungkan dan bubuhkan nomor halaman
const merged = await PDFDocument.create();
const frontDoc = await PDFDocument.load(fs.readFileSync(frontPdf));
const bodyDoc = await PDFDocument.load(fs.readFileSync(bodyPdf));

for (const p of await merged.copyPages(frontDoc, frontDoc.getPageIndices())) merged.addPage(p);
for (const p of await merged.copyPages(bodyDoc, bodyDoc.getPageIndices())) merged.addPage(p);

const font = await merged.embedFont(StandardFonts.TimesRoman);
const SIZE = 12;
const BOTTOM = 56.7; // 2 cm dari tepi bawah

merged.getPages().forEach((pdfPage, index) => {
    if (index === 0) return; // sampul tidak diberi nomor
    const label = index < frontPageCount
        ? toRoman(index + 1)
        : String(index - frontPageCount + 1);
    const width = font.widthOfTextAtSize(label, SIZE);
    pdfPage.drawText(label, {
        x: (pdfPage.getWidth() - width) / 2,
        y: BOTTOM,
        size: SIZE,
        font,
        color: rgb(0, 0, 0),
    });
});

merged.setTitle('Integrasi Modul Kontak, Pengujian Black-Box, dan Deployment Sistem Informasi Layanan Konsultasi Halal dan Legalitas Usaha Berbasis Web di PT Zam Zam Khan');
merged.setAuthor('Bagus Achmad Syahputra');
merged.setSubject('Laporan Praktik Kerja Lapangan');
merged.setProducer('Chromium + pdf-lib');

fs.writeFileSync(OUT, await merged.save());
fs.rmSync(frontHtmlPath, { force: true });

console.log('');
console.log('LAPORAN FINAL DIBUAT');
console.log(`  Berkas        : ${OUT}`);
console.log(`  Bagian awal   : ${frontPageCount} halaman (i s.d. ${toRoman(frontPageCount)})`);
console.log(`  Isi laporan   : ${bodyPages.length} halaman (1 s.d. ${bodyPages.length})`);
console.log(`  Total         : ${merged.getPageCount()} halaman`);
console.log(`  Ukuran        : ${(fs.statSync(OUT).size / 1024 / 1024).toFixed(2)} MB`);
