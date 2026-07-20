import PptxGenJS from 'pptxgenjs';
import path from 'node:path';

const pptx = new PptxGenJS();
pptx.layout = 'LAYOUT_WIDE';
pptx.author = 'Gatepass Team';
pptx.company = 'Gatepass';
pptx.subject = 'Gatepass Investor Demo';
pptx.title = 'Gatepass - 5 Slide Investor Demo';

const C = {
  bg: '0B132B',
  card: 'FFFFFF',
  text: '102A43',
  light: 'F5F7FA',
  accent: '3A86FF',
  accent2: '2EC4B6',
  accent3: 'E76F51',
  white: 'FFFFFF',
};

function header(slide, title, subtitle) {
  slide.background = { color: C.light };
  slide.addShape(pptx.ShapeType.rect, {
    x: 0,
    y: 0,
    w: 13.33,
    h: 0.9,
    fill: { color: C.bg },
    line: { color: C.bg },
  });
  slide.addText(title, {
    x: 0.65,
    y: 0.2,
    w: 8,
    h: 0.35,
    fontFace: 'Aptos Display',
    fontSize: 21,
    bold: true,
    color: C.white,
  });
  if (subtitle) {
    slide.addText(subtitle, {
      x: 0.65,
      y: 0.95,
      w: 12,
      h: 0.35,
      fontFace: 'Aptos',
      fontSize: 12,
      color: '486581',
    });
  }
}

function bullets(slide, lines, x, y, w, h, size = 20) {
  const runs = lines.map((line) => ({
    text: line,
    options: {
      bullet: { indent: 18 },
      breakLine: true,
      color: C.text,
      fontFace: 'Aptos',
      fontSize: size,
    },
  }));

  slide.addText(runs, {
    x,
    y,
    w,
    h,
    margin: 4,
    valign: 'top',
  });
}

// Slide 1
{
  const slide = pptx.addSlide();
  slide.background = { color: C.bg };
  slide.addText('Gatepass', {
    x: 0.8,
    y: 1.75,
    w: 6,
    h: 1.0,
    fontFace: 'Aptos Display',
    fontSize: 52,
    bold: true,
    color: C.white,
  });
  slide.addText('A digital visitor access platform for gated communities', {
    x: 0.82,
    y: 3.0,
    w: 8.2,
    h: 0.6,
    fontFace: 'Aptos',
    fontSize: 22,
    color: 'D9E2EC',
  });

  slide.addShape(pptx.ShapeType.roundRect, {
    x: 8.0,
    y: 1.4,
    w: 4.6,
    h: 3.8,
    radius: 0.1,
    fill: { color: '1C2541' },
    line: { color: '1C2541' },
  });
  slide.addText('Resident app\nSecurity access\nAdmin console\nUnified API', {
    x: 8.35,
    y: 1.9,
    w: 3.9,
    h: 2.8,
    fontFace: 'Aptos',
    fontSize: 20,
    color: C.white,
    valign: 'mid',
  });
}

// Slide 2
{
  const slide = pptx.addSlide();
  header(slide, 'Problem and Opportunity', 'Manual gate operations are slow, error-prone, and hard to audit.');

  bullets(slide, [
    'Visitor entry is often managed with paper logs or ad-hoc messaging.',
    'Security teams spend time validating identity without clear pass status.',
    'Residents and administrators lack one reliable source of truth.',
    'Gatepass digitizes the full visitor lifecycle from creation to exit.',
  ], 0.9, 1.6, 11.7, 4.8, 19);
}

// Slide 3
{
  const slide = pptx.addSlide();
  header(slide, 'Solution and Product', 'Three-role workflow in one platform');

  const cards = [
    { x: 0.8, title: 'Resident', body: 'Create one-time or recurring passes, manage profile, receive updates.', color: C.accent },
    { x: 4.45, title: 'Security', body: 'Find passes quickly, allow entry, mark exits, monitor flagged items.', color: C.accent2 },
    { x: 8.1, title: 'Admin', body: 'Manage users, residents, passes, emergencies, fees, and communications.', color: C.accent3 },
  ];

  for (const card of cards) {
    slide.addShape(pptx.ShapeType.roundRect, {
      x: card.x,
      y: 1.9,
      w: 3.25,
      h: 3.6,
      radius: 0.08,
      fill: { color: C.card },
      line: { color: 'D9E2EC', pt: 1 },
    });
    slide.addShape(pptx.ShapeType.rect, {
      x: card.x,
      y: 1.9,
      w: 3.25,
      h: 0.55,
      fill: { color: card.color },
      line: { color: card.color },
    });
    slide.addText(card.title, {
      x: card.x + 0.16,
      y: 2.08,
      w: 2.9,
      h: 0.3,
      fontFace: 'Aptos Display',
      fontSize: 14,
      bold: true,
      color: C.white,
    });
    slide.addText(card.body, {
      x: card.x + 0.2,
      y: 2.78,
      w: 2.85,
      h: 2.6,
      fontFace: 'Aptos',
      fontSize: 15,
      color: C.text,
      valign: 'top',
    });
  }
}

// Slide 4
{
  const slide = pptx.addSlide();
  header(slide, 'Why It Matters', 'Operational outcomes for estates and communities');

  bullets(slide, [
    'Faster gate throughput with searchable digital passes.',
    'Reduced misuse through status controls (pending, on-site, exited, revoked, expired).',
    'Improved accountability with event history and role-based actions.',
    'Scalable architecture: Laravel API + Vue web + Ionic mobile.',
  ], 0.9, 1.6, 11.7, 4.8, 19);
}

// Slide 5
{
  const slide = pptx.addSlide();
  header(slide, 'Roadmap and Ask', 'Next execution priorities');

  bullets(slide, [
    'Expand analytics dashboards for gate operations and incidents.',
    'Deepen automated test coverage across critical permission flows.',
    'Scale onboarding for more estates and larger resident populations.',
    'Ask: support pilot expansion and operational rollout.',
  ], 0.9, 1.6, 11.7, 4.8, 19);

  slide.addShape(pptx.ShapeType.roundRect, {
    x: 8.6,
    y: 5.75,
    w: 3.95,
    h: 0.8,
    radius: 0.08,
    fill: { color: C.bg },
    line: { color: C.bg },
  });
  slide.addText('Thank you', {
    x: 9.85,
    y: 5.98,
    w: 1.9,
    h: 0.35,
    fontFace: 'Aptos Display',
    fontSize: 18,
    bold: true,
    color: C.white,
    align: 'center',
  });
}

const out = path.resolve(process.cwd(), '..', 'Gatepass-Investor-Demo-5-Slides.pptx');
await pptx.writeFile({ fileName: out });
console.log(`Created: ${out}`);
