/**
 * Membangun laporan/kekurangan.pdf dari laporan/src/kekurangan.html.
 *
 *   npm install pdf-lib --no-save     (sekali, bila belum ada)
 *   node scripts/build-kekurangan.mjs
 *
 * Dokumen kerja satu berkas: margin lebih ringkas daripada naskah laporan dan
 * seluruh halaman diberi nomor arab.
 */
import { chromium } from 'playwright-core';
import { PDFDocument, StandardFonts, rgb } from 'pdf-lib';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const SOURCE = path.join(ROOT, 'laporan', 'src', 'kekurangan.html');
const OUT = path.join(ROOT, 'laporan', 'kekurangan.pdf');
const TMP = path.join(ROOT, 'laporan', '.build-kekurangan.pdf');

const browser = await chromium.launch({ channel: 'chrome' }).catch(() => chromium.launch());
const page = await browser.newPage();
await page.goto(pathToFileURL(SOURCE).href, { waitUntil: 'networkidle' });
await page.emulateMedia({ media: 'print' });
await page.pdf({
    path: TMP,
    format: 'A4',
    printBackground: true,
    margin: { top: '2.5cm', right: '2cm', bottom: '2.5cm', left: '2.5cm' },
});
await browser.close();

const pdf = await PDFDocument.load(fs.readFileSync(TMP));
const font = await pdf.embedFont(StandardFonts.TimesRoman);
const total = pdf.getPageCount();

pdf.getPages().forEach((sheet, index) => {
    const label = `${index + 1} dari ${total}`;
    const width = font.widthOfTextAtSize(label, 10);
    sheet.drawText(label, {
        x: (sheet.getWidth() - width) / 2,
        y: 42.5,
        size: 10,
        font,
        color: rgb(0, 0, 0),
    });
});

pdf.setTitle('Daftar Kekurangan dan Pekerjaan Tersisa - PKL PT Zam Zam Khan');
pdf.setAuthor('Bagus Achmad Syahputra');
pdf.setSubject('Daftar item yang belum selesai pada laporan PKL dan deployment');

fs.writeFileSync(OUT, await pdf.save());
fs.rmSync(TMP, { force: true });

console.log('KEKURANGAN.PDF DIBUAT');
console.log(`  Berkas  : ${OUT}`);
console.log(`  Halaman : ${total}`);
console.log(`  Ukuran  : ${(fs.statSync(OUT).size / 1024).toFixed(0)} KB`);
