# Checkpass Brand Logo Folder

Drop your logo files here. They will be distributed across the entire project (Admin Web Portal, Mobile App, and Landing Page).

---

### Expected Logo Files & Formats

| Filename | Description | Usage Across Board |
| :--- | :--- | :--- |
| **`logo-dark.png`** | Dark-colored logo with transparent background | • Admin Web Sidebar (`/logo-dark.png`)<br>• Admin Login & Register (`/logo-dark.png`)<br>• Mobile App Login (`/logo-dark.png`)<br>• Landing Page light sections (`/assets/logo/for white background.png`) |
| **`logo-light.png`** | White / light-colored logo with transparent background | • Landing page dark sections / hero (`/assets/logo/for dark background.png`)<br>• Dark-mode views |
| **`logo-icon.png`** | Square emblem / icon badge (PNG or SVG) | • Favicons and mobile app icon badges |

---

### How to Apply New Logos
Whenever you replace or drop new logos into this folder:
Run the sync script from the project root in your terminal:
```bash
node sync-logo.mjs
```
The script will automatically distribute the files into:
- `checkpass-admin/public/`
- `checkpass-admin/public/assets/logo/`
- `checkpass-app/public/`
