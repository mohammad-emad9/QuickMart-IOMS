# Frontend asset build

QuickMart keeps readable CSS and JavaScript source under `assets/` and
checked-in production assets under `dist/`. The generated files are the
runtime defaults used by the PHP views and login page.

## Rebuild production assets

From the repository root:

```text
npm ci
npm run build
```

`clean-css@5.3.3` and `terser@5.50.0` are pinned in `package.json` and
`package-lock.json`. The combined build runs `build:css` and `build:js`.
Readable source files remain under `assets/`; edit source files, not generated
output.

### CSS output

`npm run build:css` minifies the project stylesheets, the local Poppins
stylesheet, and the local Bootstrap stylesheet into `dist/css/*.min.css`.

### JavaScript output

`npm run build:js` minifies the project-owned runtime scripts into
`dist/js/*.min.js`. Terser preserves top-level names because the procedural
application exposes shared functions and legacy callbacks across script
boundaries. Bootstrap's already-minified vendor bundle is not copied into
`dist/js/` or minified again.

## Local vendor assets

- Bootstrap `5.3.0` CSS and bundle are stored in `assets/vendor/bootstrap/`.
- Poppins Latin weights 400, 500, 600, and 700 are stored in `assets/fonts/`.
- Poppins is distributed under SIL Open Font License 1.1; the included
  `assets/fonts/OFL-Poppins.txt` is the authoritative license copy.
- Arabic glyphs continue to use the existing system Arabic fallback stack.

The application pages do not require network access to load Bootstrap, Poppins,
or project JavaScript. If `dist/` is absent in a fresh checkout, run `npm ci`
followed by `npm run build` before opening the application; no runtime CDN or
source-file fallback is used.
