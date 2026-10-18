// Copies vendor assets that must be served as static files (unprocessed by
// Vite) into public/vendor. Run automatically via `npm install` (postinstall).
import { cpSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.dirname(path.dirname(fileURLToPath(import.meta.url)));

const copies = [
    ['node_modules/bootstrap-icons/font', 'public/vendor/bootstrap-icons'],
];

for (const [from, to] of copies) {
    const src = path.join(root, from);
    const dest = path.join(root, to);

    if (!existsSync(src)) {
        console.warn(`[copy-static-vendor-assets] skipped, not found: ${from}`);
        continue;
    }

    cpSync(src, dest, { recursive: true });
    console.log(`[copy-static-vendor-assets] ${from} -> ${to}`);
}
