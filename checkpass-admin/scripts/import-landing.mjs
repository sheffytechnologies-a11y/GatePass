import fs from 'node:fs';
import path from 'node:path';

const htmlPath = path.resolve(process.cwd(), '../../../Checkpass research/index.html');
const raw = fs.readFileSync(htmlPath, 'utf8');

const bodyMatch = raw.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
if (!bodyMatch) {
  console.error('Body tag not found');
  process.exit(1);
}
let bodyContent = bodyMatch[1];

// Extract script tags
const scriptMatch = bodyContent.match(/<script\b[^>]*>([\s\S]*?)<\/script>/gi);
let inlineScripts = '';
if (scriptMatch) {
  for (const s of scriptMatch) {
    if (!s.includes('src=')) {
      const inner = s.replace(/<script\b[^>]*>/i, '').replace(/<\/script>/i, '');
      inlineScripts += inner + '\n';
    }
  }
}

// Remove script tags from template body
bodyContent = bodyContent.replace(/<script\b[\s\S]*?<\/script>/gi, '');

// Fix asset paths from ./assets/ to /assets/
bodyContent = bodyContent.replace(/\.\/assets\//g, '/assets/');

// Replace modal login calls with direct navigation trigger
bodyContent = bodyContent.replace(/onclick="openLoginModal\(\)[^"]*"/g, 'onclick="window.goToLogin()"');

console.log('Body processed. Length:', bodyContent.length);

const vueComponent = `<template>
  <div class="landing-wrapper">
${bodyContent}
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

function goToLogin() {
  router.push('/login')
}

function goToRegister() {
  router.push('/register')
}

if (typeof window !== 'undefined') {
  (window as any).goToLogin = goToLogin;
  (window as any).goToRegister = goToRegister;
  (window as any).openLoginModal = goToLogin;
}

onMounted(() => {
  let styleEl = document.getElementById('landing-styles') as HTMLLinkElement
  if (!styleEl) {
    styleEl = document.createElement('link')
    styleEl.id = 'landing-styles'
    styleEl.rel = 'stylesheet'
    styleEl.href = '/landing.css'
    document.head.appendChild(styleEl)
  }

  const loadScript = (src: string): Promise<void> => {
    return new Promise((resolve) => {
      if (document.querySelector(\`script[src="\${src}"]\`)) {
        resolve()
        return
      }
      const s = document.createElement('script')
      s.src = src
      s.onload = () => resolve()
      document.body.appendChild(s)
    })
  }

  Promise.all([
    loadScript('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js'),
    loadScript('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js')
  ]).then(() => {
    try {
      const existingScript = document.getElementById('landing-inline-script')
      if (existingScript) existingScript.remove()
      const script = document.createElement('script')
      script.id = 'landing-inline-script'
      script.textContent = ${JSON.stringify(inlineScripts)}
      document.body.appendChild(script)
    } catch (err) {
      console.warn('Landing script init error:', err)
    }
  })
})

onUnmounted(() => {
  const styleEl = document.getElementById('landing-styles')
  if (styleEl) styleEl.remove()
  const scriptEl = document.getElementById('landing-inline-script')
  if (scriptEl) scriptEl.remove()
})
</script>
`;

fs.writeFileSync('src/views/LandingView.vue', vueComponent, 'utf8');
console.log('LandingView.vue successfully created!');
