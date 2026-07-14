import sharp from 'sharp';
import { readdir, stat } from 'node:fs/promises';
import { join, relative } from 'node:path';

const roots = ['public/images', 'storage/app/public'];
const exts = /\.(png|jpe?g|webp|avif)$/i;

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

const rows = [];
for (const root of roots) {
    for await (const file of walk(root)) {
        const { size } = await stat(file);
        let meta = {};
        try {
            meta = await sharp(file).metadata();
        } catch {
            meta = { width: '?', height: '?' };
        }
        rows.push({
            file: relative('.', file).replace(/\\/g, '/'),
            kb: Math.round(size / 1024),
            dim: `${meta.width}x${meta.height}`,
        });
    }
}

rows.sort((a, b) => b.kb - a.kb);
for (const r of rows) {
    console.log(String(r.kb).padStart(5) + ' KB  ' + r.dim.padEnd(12) + '  ' + r.file);
}
console.log('\nTOTAL: ' + rows.reduce((s, r) => s + r.kb, 0) + ' KB across ' + rows.length + ' files');
