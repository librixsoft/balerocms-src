const fs = require('fs');
const path = require('path');

const nodeModulesPath = path.join(__dirname, 'node_modules');
const assetsPath = path.join(__dirname, 'public', 'assets');

// Función para copiar un archivo, crea carpetas destino si no existen
function copyFileSafe(src, dest) {
    const destDir = path.dirname(dest);
    if (!fs.existsSync(destDir)) {
        fs.mkdirSync(destDir, { recursive: true });
    }
    if (fs.existsSync(src)) {
        fs.copyFileSync(src, dest);
        console.log(`✅ Copiado: ${src} -> ${dest}`);
    } else {
        console.warn(`⚠️ No existe archivo: ${src}`);
    }
}

// Copiar Bootstrap CSS y JS (en carpetas css y js)
copyFileSafe(
    path.join(nodeModulesPath, 'bootstrap', 'dist', 'css', 'bootstrap.min.css'),
    path.join(assetsPath, 'css', 'bootstrap.min.css')
);
copyFileSafe(
    path.join(nodeModulesPath, 'bootstrap', 'dist', 'js', 'bootstrap.bundle.min.js'),
    path.join(assetsPath, 'js', 'bootstrap.bundle.min.js')
);

// Copiar jQuery (archivo único)
copyFileSafe(
    path.join(nodeModulesPath, 'jquery', 'dist', 'jquery.min.js'),
    path.join(assetsPath, 'js', 'jquery.min.js')
);

// Copiar FontAwesome CSS y webfonts
copyFileSafe(
    path.join(nodeModulesPath, '@fortawesome', 'fontawesome-free', 'css', 'all.min.css'),
    path.join(assetsPath, 'css', 'all.min.css')
);
const faWebfontsSrc = path.join(nodeModulesPath, '@fortawesome', 'fontawesome-free', 'webfonts');
const faWebfontsDest = path.join(assetsPath, 'webfonts');
// Copiar carpeta webfonts completa
function copyFolderSync(src, dest) {
    if (!fs.existsSync(src)) return console.warn(`⚠️ No existe carpeta: ${src}`);
    if (!fs.existsSync(dest)) fs.mkdirSync(dest, { recursive: true });
    fs.readdirSync(src).forEach(file => {
        const srcFile = path.join(src, file);
        const destFile = path.join(dest, file);
        fs.copyFileSync(srcFile, destFile);
        console.log(`✅ Copiado: ${srcFile} -> ${destFile}`);
    });
}
copyFolderSync(faWebfontsSrc, faWebfontsDest);

// Copiar Summernote (archivos dentro de dist/, sin carpeta js)
const summernoteDist = path.join(nodeModulesPath, 'summernote', 'dist');
copyFileSafe(
    path.join(summernoteDist, 'summernote.min.css'),  // si existe versión minificada
    path.join(assetsPath, 'css', 'summernote.min.css')
);
copyFileSafe(
    path.join(summernoteDist, 'summernote.css'),      // fallback a sin minificar si la min no existe
    path.join(assetsPath, 'css', 'summernote.min.css')
);
copyFileSafe(
    path.join(summernoteDist, 'summernote.min.js'),
    path.join(assetsPath, 'js', 'summernote.min.js')
);
copyFileSafe(
    path.join(summernoteDist, 'summernote.js'),
    path.join(assetsPath, 'js', 'summernote.min.js')
);

console.log('📦 Copia de librerías completada.');
