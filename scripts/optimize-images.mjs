/**
 * Recompresses images in place. Filenames and extensions never change, so every
 * path stored in the database or hardcoded in a Blade template stays valid.
 *
 * Everything runs through in-memory buffers: the new bytes are decoded back and
 * verified as a real image before the original is overwritten, and a result that
 * is empty or larger than the source is discarded.
 *
 * Usage: node scripts/optimize-images.mjs [--dry]
 */
import sharp from 'sharp';
import { readdir, readFile, writeFile, stat } from 'node:fs/promises';
import { join, extname, relative } from 'node:path';

const DRY = process.argv.includes('--dry');

// maxWidth is a ceiling, not a resize target: images already smaller keep their
// own size. Values are ~2x the largest rendered size, so retina stays sharp.
const RULES = [
    { match: /public[\\/]images[\\/]card[\\/]/i, maxWidth: 800 },
    { match: /logo-zzk\./i, maxWidth: 400 },
    { match: /storage[\\/]app[\\/]public[\\/]branding[\\/]/i, maxWidth: 400 },
    { match: /public[\\/]images[\\/]Logo[\\/]/i, maxWidth: 320 },
    { match: /public[\\/]images[\\/]testimonials[\\/]/i, maxWidth: 640 },
    { match: /kolase\./i, maxWidth: 900 },
    { match: /buzamzami/i, maxWidth: 623 },
    { match: /storage[\\/]app[\\/]public[\\/]hero[\\/]/i, maxWidth: 623 },
];

const SKIP = /favicon/i;

const roots = ['public/images', 'storage/app/public'];
const exts = /\.(png|jpe?g|webp)$/i;

async function* walk(dir) {
    let entries;
    try {
        entries = await readdir(dir, { withFileTypes: true });
    } catch {
        return;
    }
    for (const e of entries) {
        const p = join(dir, e.name);
        if (e.isDirectory()) yield* walk(p);
        else if (exts.test(e.name)) yield p;
    }
}

function encode(ext, pipeline) {
    switch (ext) {
        case '.png':
            // Photographic PNGs shrink dramatically once palettised; 90 keeps
            // banding invisible at the sizes these are actually rendered.
            return pipeline.png({ compressionLevel: 9, palette: true, quality: 90, effort: 10 });
        case '.jpg':
        case '.jpeg':
            return pipeline.jpeg({ quality: 82, mozjpeg: true, progressive: true });
        case '.webp':
            return pipeline.webp({ quality: 80, effort: 6 });
        default:
            return pipeline;
    }
}

let before = 0;
let after = 0;
let changed = 0;
const report = [];

for (const root of roots) {
    for await (const file of walk(root)) {
        if (SKIP.test(file)) continue;

        const rel = relative('.', file).replace(/\\/g, '/');
        const input = await readFile(file);
        const original = input.length;
        const meta = await sharp(input).metadata();
        const rule = RULES.find((r) => r.match.test(file));

        let pipeline = sharp(input, { failOn: 'error' }).rotate();
        if (rule && meta.width > rule.maxWidth) {
            pipeline = pipeline.resize({ width: rule.maxWidth, withoutEnlargement: true });
        }

        const output = await encode(extname(file).toLowerCase(), pipeline).toBuffer();

        if (output.length === 0 || output.length >= original) {
            before += original;
            after += original;
            report.push({ rel, from: original, to: original, note: 'dilewati (tidak lebih kecil)' });
            continue;
        }

        // The new bytes must decode as a real image before they replace anything.
        const check = await sharp(output).metadata();
        if (!check.width || !check.height) {
            throw new Error(`Hasil encode tidak valid untuk ${rel} — dibatalkan, file asli tidak disentuh.`);
        }

        if (!DRY) {
            await writeFile(file, output);
            const written = (await stat(file)).size;
            if (written !== output.length) {
                throw new Error(`Penulisan ${rel} tidak utuh (${written} != ${output.length}).`);
            }
        }

        before += original;
        after += output.length;
        changed++;
        report.push({
            rel,
            from: original,
            to: output.length,
            note: `${meta.width}x${meta.height} -> ${check.width}x${check.height}`,
        });
    }
}

const kb = (b) => Math.round(b / 1024);
report.sort((a, b) => b.from - b.to - (a.from - a.to));
for (const r of report) {
    const saved = kb(r.from) - kb(r.to);
    console.log(
        `${String(kb(r.from)).padStart(5)} -> ${String(kb(r.to)).padStart(5)} KB  (-${String(saved).padStart(4)} KB)  ${r.rel}  ${r.note}`
    );
}
console.log(
    `\n${DRY ? '[DRY RUN] ' : ''}Total: ${kb(before)} KB -> ${kb(after)} KB  (hemat ${kb(before - after)} KB, ${changed} file diubah)`
);
