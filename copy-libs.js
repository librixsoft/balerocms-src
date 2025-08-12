const fs = require("fs");
const path = require("path");

function copyFile(src, dest) {
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.copyFileSync(src, dest);
}

function copyFolder(srcDir, destDir) {
    fs.mkdirSync(destDir, { recursive: true });
    fs.readdirSync(srcDir).forEach(file => {
        const srcFile = path.join(srcDir, file);
        const destFile = path.join(destDir, file);
        if (fs.lstatSync(srcFile).isDirectory()) {
            copyFolder(srcFile, destFile);
        } else {
            fs.copyFileSync(srcFile, destFile);
        }
    });
}

console.log("📦 Copiando librerías a public/assets...");

// Bootstrap
copyFile(
    "node_modules/bootstrap/dist/css/bootstrap.min.css",
    "public/assets/css/bootstrap.min.css"
);
copyFile(
    "node_modules/bootstrap/dist/js/bootstrap.bundle.min.js",
    "public/assets/js/bootstrap.bundle.min.js"
);

// jQuery
copyFile(
    "node_modules/jquery/dist/jquery.min.js",
    "public/assets/js/jquery.min.js"
);

// Font Awesome 6
copyFile(
    "node_modules/@fortawesome/fontawesome-free/css/all.min.css",
    "public/assets/css/fontawesome.min.css"
);
copyFolder(
    "node_modules/@fortawesome/fontawesome-free/webfonts",
    "public/assets/webfonts"
);

// Summernote 0.9.1
copyFile(
    "node_modules/summernote/dist/summernote.min.css",
    "public/assets/css/summernote.min.css"
);
copyFile(
    "node_modules/summernote/dist/summernote.min.js",
    "public/assets/js/summernote.min.js"
);

console.log("✅ Librerías copiadas con éxito.");
