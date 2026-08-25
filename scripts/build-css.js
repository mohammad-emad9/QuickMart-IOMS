const fs = require('node:fs');
const path = require('node:path');
const CleanCSS = require('clean-css');

const root = path.resolve(__dirname, '..');
const entries = [
    ['assets/vendor/bootstrap/bootstrap.min.css', 'dist/css/bootstrap.min.css'],
    ['assets/fonts/poppins.css', 'dist/css/poppins.min.css'],
    ['assets/common.css', 'dist/css/common.min.css'],
    ['assets/dashboard/dashboard.css', 'dist/css/dashboard.min.css'],
    ['assets/products/products.css', 'dist/css/products.min.css'],
    ['assets/orders/orders.css', 'dist/css/orders.min.css'],
    ['assets/create-order/create-order.css', 'dist/css/create-order.min.css'],
    ['assets/order-details/order-details.css', 'dist/css/order-details.min.css'],
    ['assets/reports/reports.css', 'dist/css/reports.min.css'],
    ['assets/staff/staff.css', 'dist/css/staff.min.css'],
    ['assets/profile/profile.css', 'dist/css/profile.min.css'],
    ['assets/login-signup/style.css', 'dist/css/login.min.css'],
];

const minifier = new CleanCSS({ level: 2 });

for (const [sourceName, outputName] of entries) {
    const sourcePath = path.join(root, sourceName);
    const outputPath = path.join(root, outputName);
    if (!fs.existsSync(sourcePath)) {
        throw new Error(`CSS source file not found: ${sourceName}`);
    }

    const result = minifier.minify(fs.readFileSync(sourcePath, 'utf8'));
    if (result.errors.length > 0) {
        throw new Error(`${sourceName}: ${result.errors.join('; ')}`);
    }

    let outputStyles = result.styles;
    if (sourceName === 'assets/fonts/poppins.css') {
        // The readable source lives beside the font files; the generated CSS lives in dist/css.
        outputStyles = outputStyles.replace(
            /url\((['"]?)\.\/(Poppins-[^'")]+\.woff2)\1\)/g,
            'url(../../assets/fonts/$2)'
        );
    }

    fs.mkdirSync(path.dirname(outputPath), { recursive: true });
    fs.writeFileSync(outputPath, `${outputStyles}\n`, 'utf8');
    process.stdout.write(`${sourceName} -> ${outputName} (${outputStyles.length} bytes)\n`);
}
