import PptxGenJS from 'pptxgenjs';
import path from 'node:path';

const pptx = new PptxGenJS();
pptx.layout = 'LAYOUT_WIDE';
pptx.author = 'Gatepass Team';
pptx.company = 'Gatepass';
pptx.subject = 'Gatepass Project Overview';
pptx.title = 'Gatepass Project Presentation';
pptx.lang = 'en-US';

const COLORS = {
  navy: '0B132B',
  blue: '1C2541',
  cyan: '3A86FF',
  sky: '5BC0EB',
  mint: '2EC4B6',
  text: '102A43',
  light: 'F5F7FA',
  white: 'FFFFFF',
  accent: 'E76F51',
};

function titleSlide(title, subtitle) {
  const slide = pptx.addSlide();
  slide.background = { color: COLORS.navy };
  slide.addShape(pptx.ShapeType.rect, {
    x: 0,
    y: 0,
    w: 13.33,
    h: 0.6,
    fill: { color: COLORS.cyan },
    line: { color: COLORS.cyan },
  });
  slide.addText(title, {
    x: 0.8,
    y: 1.6,
    w: 11.8,
    h: 1,
    fontFace: 'Aptos Display',
    fontSize: 42,
    bold: true,
    color: COLORS.white,
  });
  slide.addText(subtitle, {
    x: 0.8,
    y: 2.8,
    w: 11,
    h: 1,
    fontFace: 'Aptos',
    fontSize: 21,
    color: 'D9E2EC',
  });
  slide.addText('Gatepass Platform', {
    x: 0.8,
    y: 6.6,
    w: 4,
    h: 0.4,
    fontFace: 'Aptos',
    fontSize: 13,
    color: '9FB3C8',
  });
}

function sectionTitle(slide, title, subtitle = '') {
  slide.background = { color: COLORS.light };
  slide.addShape(pptx.ShapeType.roundRect, {
    x: 0.6,
    y: 0.45,
    w: 12.1,
    h: 0.7,
    radius: 0.08,
    fill: { color: COLORS.blue },
    line: { color: COLORS.blue },
  });
  slide.addText(title, {
    x: 0.95,
    y: 0.62,
    w: 8,
    h: 0.4,
    fontFace: 'Aptos Display',
    fontSize: 24,
    bold: true,
    color: COLORS.white,
  });
  if (subtitle) {
    slide.addText(subtitle, {
      x: 0.95,
      y: 1.28,
      w: 11.8,
      h: 0.4,
      fontFace: 'Aptos',
      fontSize: 13,
      color: '486581',
    });
  }
}

function addBullets(slide, items, x = 0.9, y = 1.9, w = 11.5, h = 4.8) {
  const runs = items.map((t) => ({
    text: t,
    options: {
      bullet: { indent: 18 },
      breakLine: true,
      color: COLORS.text,
      fontFace: 'Aptos',
      fontSize: 19,
    },
  }));

  slide.addText(runs, {
    x,
    y,
    w,
    h,
    valign: 'top',
    margin: 4,
  });
}

titleSlide(
  'Gatepass Project Overview',
  'Resident, Security, and Admin workflows on one integrated platform'
);

{
  const slide = pptx.addSlide();
  sectionTitle(slide, '1. Project Snapshot', 'What Gatepass solves');
  addBullets(slide, [
    'Digital visitor management for gated communities and estates.',
    'Single backend API powers resident app and admin/security dashboard.',
    'Core modules: passes, item declaration, emergencies, notifications, fees, and profile.',
    'Role-based access keeps each user experience focused and secure.',
  ]);
}

{
  const slide = pptx.addSlide();
  sectionTitle(slide, '2. Product Surfaces', 'Three apps, one ecosystem');

  const cards = [
    { title: 'gatepass-api', body: 'Laravel 12, Sanctum auth, role-aware API endpoints.', color: COLORS.cyan },
    { title: 'gatepass-admin', body: 'Vue 3 + TypeScript web dashboard for admins and security.', color: COLORS.mint },
    { title: 'gatepass-app', body: 'Ionic Vue + Capacitor mobile app for residents.', color: COLORS.accent },
  ];

  cards.forEach((card, idx) => {
    const x = 0.9 + idx * 4.2;
    slide.addShape(pptx.ShapeType.roundRect, {
      x,
      y: 2.0,
      w: 3.8,
      h: 3.3,
      radius: 0.08,
      fill: { color: COLORS.white },
      line: { color: 'D9E2EC', pt: 1 },
      shadow: { type: 'outer', color: 'CBD2D9', blur: 2, angle: 45, distance: 2, opacity: 0.2 },
    });

    slide.addShape(pptx.ShapeType.rect, {
      x,
      y: 2.0,
      w: 3.8,
      h: 0.55,
      fill: { color: card.color },
      line: { color: card.color },
    });

    slide.addText(card.title, {
      x: x + 0.2,
      y: 2.12,
      w: 3.3,
      h: 0.3,
      fontFace: 'Aptos Display',
      fontSize: 14,
      bold: true,
      color: COLORS.white,
    });

    slide.addText(card.body, {
      x: x + 0.22,
      y: 2.78,
      w: 3.35,
      h: 2.3,
      fontFace: 'Aptos',
      fontSize: 15,
      color: COLORS.text,
      valign: 'top',
    });
  });
}

{
  const slide = pptx.addSlide();
  sectionTitle(slide, '3. Key User Flows', 'End-to-end visitor lifecycle');
  addBullets(slide, [
    'Resident creates one-time or recurring pass with visitor details and expiry.',
    'Security validates pass by ULID or phone lookup at gate.',
    'Security allows entry, tracks on-site state, and marks exit.',
    'Resident can flag visitor items while on-site for additional control.',
    'Pass auto-expiration protects against stale or reused entries.',
  ]);
}

{
  const slide = pptx.addSlide();
  sectionTitle(slide, '4. API Capability Highlights', 'Route groups under /api/v1');
  addBullets(slide, [
    'Auth: login, refresh, logout, me, password change.',
    'Passes: list, create, detail, revoke, extend, allow-entry, mark-exited, flag-item, find-by-phone.',
    'Resident modules: home summary, notifications, emergency, profile, fees, news.',
    'Admin modules: users, residents, passes, emergencies, notifications, estates, units, fees, news.',
    'Protected with Sanctum plus middleware-based role checks.',
  ]);
}

{
  const slide = pptx.addSlide();
  sectionTitle(slide, '5. Technology Stack', 'Current implementation');
  addBullets(slide, [
    'Backend: PHP 8.2, Laravel 12, Sanctum, Telescope, PHPUnit 11.',
    'Web Admin: Vue 3, TypeScript, Vite, Pinia, Vue Router, Axios.',
    'Mobile App: Ionic Vue, Capacitor (Android/iOS), Camera and Push plugins.',
    'Build/Dev: Vite pipelines for frontend apps and Composer scripts for backend.',
  ]);
}

{
  const slide = pptx.addSlide();
  sectionTitle(slide, '6. Security and Access Model', 'Role-aware behavior by design');
  addBullets(slide, [
    'Token-based auth using Laravel Sanctum for protected APIs.',
    'Role-specific pathways: resident, security, and admin.',
    'Security-only actions: allow-entry, mark-exited, phone search at the gate.',
    'Admin-only route group for operational and reference-data management.',
    'Input validation and explicit error codes across controller actions.',
  ]);
}

{
  const slide = pptx.addSlide();
  sectionTitle(slide, '7. Operational Benefits', 'What this delivers for stakeholders');
  addBullets(slide, [
    'Faster gate processing with searchable digital passes.',
    'Improved visibility through on-site, flagged-item, and exit statuses.',
    'Lower manual workload for resident communications and emergency handling.',
    'Clear auditability of visitor events and payment-related actions.',
  ]);
}

{
  const slide = pptx.addSlide();
  sectionTitle(slide, '8. Next Milestones', 'Recommended roadmap');
  addBullets(slide, [
    'Add analytics dashboards for gate throughput and incident trends.',
    'Introduce background jobs for notifications and scheduled pass cleanup.',
    'Expand automated test coverage for critical API flows and permission checks.',
    'Harden production monitoring and alerting around auth and pass operations.',
  ]);
}

{
  const slide = pptx.addSlide();
  slide.background = { color: COLORS.blue };
  slide.addText('Thank You', {
    x: 0.8,
    y: 2.4,
    w: 5,
    h: 0.9,
    fontFace: 'Aptos Display',
    fontSize: 48,
    bold: true,
    color: COLORS.white,
  });
  slide.addText('Questions and discussion', {
    x: 0.85,
    y: 3.4,
    w: 6,
    h: 0.5,
    fontFace: 'Aptos',
    fontSize: 22,
    color: 'D9E2EC',
  });
  slide.addShape(pptx.ShapeType.ellipse, {
    x: 8.8,
    y: 1.6,
    w: 3.6,
    h: 3.6,
    fill: { color: COLORS.cyan, transparency: 35 },
    line: { color: COLORS.cyan, transparency: 100 },
  });
  slide.addShape(pptx.ShapeType.ellipse, {
    x: 10.3,
    y: 3.4,
    w: 2.2,
    h: 2.2,
    fill: { color: COLORS.mint, transparency: 30 },
    line: { color: COLORS.mint, transparency: 100 },
  });
}

const outputPath = path.resolve(process.cwd(), '..', 'Gatepass-Project-Presentation.pptx');
await pptx.writeFile({ fileName: outputPath });
console.log(`Presentation created at: ${outputPath}`);
