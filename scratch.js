import fs from 'fs';
import path from 'path';

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

walkDir('c:/laragon/www/quartz/resources/js', (filePath) => {
    if (filePath.endsWith('.vue')) {
        let content = fs.readFileSync(filePath, 'utf8');
        
        let newContent = content
            .replace(/<h1 class="/g, '<h1 class="font-serif ')
            .replace(/<h2 class="/g, '<h2 class="font-serif ')
            .replace(/<h3 class="/g, '<h3 class="font-serif ');

        // fix if double font-serif is added
        newContent = newContent.replace(/font-serif font-serif/g, 'font-serif');

        if (content !== newContent) {
            fs.writeFileSync(filePath, newContent, 'utf8');
            console.log(`Updated ${filePath}`);
        }
    }
});
