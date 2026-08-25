const fs = require('node:fs');
const path = require('node:path');
const Terser = require('terser');

const root = path.resolve(__dirname, '..');
const entries = [
    ['assets/common.js', 'dist/js/common.min.js'],
    ['assets/login-signup/login.js', 'dist/js/login.min.js'],
    ['assets/dashboard/dashboard.js', 'dist/js/dashboard.min.js'],
    ['assets/products/products.js', 'dist/js/products.min.js'],
    ['assets/orders/orders.js', 'dist/js/orders.min.js'],
    ['assets/create-order/create-order.js', 'dist/js/create-order.min.js'],
    ['assets/order-details/order-details.js', 'dist/js/order-details.min.js'],
    ['assets/reports/reports.js', 'dist/js/reports.min.js'],
    ['assets/staff/staff.js', 'dist/js/staff.min.js'],
    ['assets/profile/profile.js', 'dist/js/profile.min.js'],
];

async function buildJavaScript() {
    for (const [sourceName, outputName] of entries) {
        const sourcePath = path.join(root, sourceName);
        const outputPath = path.join(root, outputName);
        if (!fs.existsSync(sourcePath)) {
            throw new Error(`JavaScript source file not found: ${sourceName}`);
        }

        const source = fs.readFileSync(sourcePath, 'utf8');
        const result = await Terser.minify(source, {
            compress: { passes: 2 },
            // Page scripts intentionally expose legacy globals and callbacks.
            mangle: { toplevel: false },
            format: { comments: /^!/ },
        });
        if (result.error) {
            throw new Error(`${sourceName}: ${result.error.message}`);
        }

        fs.mkdirSync(path.dirname(outputPath), { recursive: true });
        fs.writeFileSync(outputPath, `${result.code}\n`, 'utf8');
        process.stdout.write(`${sourceName} -> ${outputName} (${result.code.length} bytes)\n`);
    }
}

buildJavaScript().catch((error) => {
    process.stderr.write(`${error.stack || error.message || String(error)}\n`);
    process.exitCode = 1;
});
