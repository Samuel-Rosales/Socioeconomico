#!/usr/bin/env node
/* eslint-disable no-console */

const { spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

function ensureDir(dirPath) {
  fs.mkdirSync(dirPath, { recursive: true });
}

function copyFile(src, dest) {
  ensureDir(path.dirname(dest));
  fs.copyFileSync(src, dest);
}

function copyDir(srcDir, destDir) {
  ensureDir(destDir);
  for (const entry of fs.readdirSync(srcDir, { withFileTypes: true })) {
    const srcPath = path.join(srcDir, entry.name);
    const destPath = path.join(destDir, entry.name);

    if (entry.isDirectory()) {
      copyDir(srcPath, destPath);
      continue;
    }

    if (entry.isFile()) {
      copyFile(srcPath, destPath);
    }
  }
}

function runTailwind(args) {
  const isWin = process.platform === 'win32';
  const bin = path.join(
    __dirname,
    '..',
    'node_modules',
    '.bin',
    isWin ? 'tailwindcss.cmd' : 'tailwindcss'
  );

  if (!fs.existsSync(bin)) {
    console.error('Tailwind CLI not found. Run: npm install');
    process.exit(1);
  }

  const result = spawnSync(bin, args, { stdio: 'inherit', shell: isWin });
  if (typeof result.status === 'number' && result.status !== 0) {
    process.exit(result.status);
  }
}

function main() {
  const projectRoot = path.resolve(__dirname, '..');
  const srcInput = path.join(projectRoot, 'src', 'input.css');
  const outCss = path.join(projectRoot,'public', 'assets', 'css', 'output.css');

  // Keep fonts under assets so @font-face urls in compiled CSS resolve correctly.
  const publicFonts = path.join(projectRoot, 'public', 'fonts');
  const assetsFonts = path.join(projectRoot, 'assets', 'fonts');
  if (fs.existsSync(publicFonts)) {
    copyDir(publicFonts, assetsFonts);
  }

  const extraArgs = process.argv.slice(2);
  runTailwind(['-i', srcInput, '-o', outCss, ...extraArgs]);
}

main();
