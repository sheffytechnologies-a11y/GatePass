import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const sharedLogoDir = path.join(root, 'shared-brand-assets', 'logo');
const adminPublic = path.join(root, 'checkpass-admin', 'public');
const appPublic = path.join(root, 'checkpass-app', 'public');
const adminLandingLogo = path.join(adminPublic, 'assets', 'logo');

if (!fs.existsSync(sharedLogoDir)) {
  console.error('Error: shared-brand-assets/logo folder not found.');
  process.exit(1);
}

if (!fs.existsSync(adminPublic)) fs.mkdirSync(adminPublic, { recursive: true });
if (!fs.existsSync(appPublic)) fs.mkdirSync(appPublic, { recursive: true });
if (!fs.existsSync(adminLandingLogo)) fs.mkdirSync(adminLandingLogo, { recursive: true });

const files = fs.readdirSync(sharedLogoDir);

for (const file of files) {
  const src = path.join(sharedLogoDir, file);
  if (!fs.statSync(src).isFile() || file.endsWith('.md')) continue;

  // Sync to root public folders of admin and app
  fs.copyFileSync(src, path.join(adminPublic, file));
  fs.copyFileSync(src, path.join(appPublic, file));
  console.log(`Synced ${file} -> checkpass-admin/public/ and checkpass-app/public/`);

  // Map to marketing landing page assets if matching
  if (file === 'logo-dark.png') {
    fs.copyFileSync(src, path.join(adminLandingLogo, 'for white background.png'));
    console.log(`Synced logo-dark.png -> checkpass-admin/public/assets/logo/for white background.png`);
  } else if (file === 'logo-light.png') {
    fs.copyFileSync(src, path.join(adminLandingLogo, 'for dark background.png'));
    console.log(`Synced logo-light.png -> checkpass-admin/public/assets/logo/for dark background.png`);
  }
}

console.log('\nAll logo assets have been synchronized across the board successfully!');
