const fs = require('fs');
const path = require('path');

const nodeModulesPath = path.join(__dirname, 'node_modules');
const assetsPath = path.join(__dirname, 'public', 'assets');

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

function copyFolderSync(src, dest) {
    if (!fs.existsSync(src)) {
        console.warn(`⚠️ No existe carpeta: ${src}`);
        return;
    }
    if (!fs.existsSync(dest)) {
        fs.mkdirSync(dest, { recursive: true });
    }
    fs.readdirSync(src).forEach(file => {
        const srcFile = path.join(src, file);
        const destFile = path.join(dest, file);
        fs.copyFileSync(srcFile, destFile);
        console.log(`✅ Copiado: ${srcFile} -> ${destFile}`);
    });
}

// Bootstrap
copyFileSafe(
    path.join(nodeModulesPath, 'bootstrap', 'dist', 'css', 'bootstrap.min.css'),
    path.join(assetsPath, 'css', 'bootstrap.min.css')
);
copyFileSafe(
    path.join(nodeModulesPath, 'bootstrap', 'dist', 'js', 'bootstrap.bundle.min.js'),
    path.join(assetsPath, 'js', 'bootstrap.bundle.min.js')
);

// jQuery
copyFileSafe(
    path.join(nodeModulesPath, 'jquery', 'dist', 'jquery.min.js'),
    path.join(assetsPath, 'js', 'jquery.min.js')
);

// FontAwesome CSS + webfonts
copyFileSafe(
    path.join(nodeModulesPath, '@fortawesome', 'fontawesome-free', 'css', 'all.min.css'),
    path.join(assetsPath, 'css', 'all.min.css')
);
copyFolderSync(
    path.join(nodeModulesPath, '@fortawesome', 'fontawesome-free', 'webfonts'),
    path.join(assetsPath, 'webfonts')
);

// Summernote (usa minificado si existe, si no copia versión normal y renombra)
const summernoteDist = path.join(nodeModulesPath, 'summernote', 'dist');

if (fs.existsSync(path.join(summernoteDist, 'summernote.min.css'))) {
    copyFileSafe(
        path.join(summernoteDist, 'summernote.min.css'),
        path.join(assetsPath, 'css', 'summernote.min.css')
    );
} else {
    copyFileSafe(
        path.join(summernoteDist, 'summernote.css'),
        path.join(assetsPath, 'css', 'summernote.min.css')
    );
}

if (fs.existsSync(path.join(summernoteDist, 'summernote.min.js'))) {
    copyFileSafe(
        path.join(summernoteDist, 'summernote.min.js'),
        path.join(assetsPath, 'js', 'summernote.min.js')
    );
} else {
    copyFileSafe(
        path.join(summernoteDist, 'summernote.js'),
        path.join(assetsPath, 'js', 'summernote.min.js')
    );
}

console.log('📦 Copia de librerías completada.');
