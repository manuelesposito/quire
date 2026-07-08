// Capture WooCommerce React screens at quire=0 vs quire=1 (hooks only).
// Uses the cached Chromium so nothing downloads. Besides pixels, reads
// COMPUTED STYLES off accent-bearing elements — ground truth the pixel
// sampler can't fake (lesson learned from the menu-backdrop miss).
import { chromium } from 'playwright-core';
import os from 'os';
import path from 'path';

const EXE = path.join(
  os.homedir(),
  'Library/Caches/ms-playwright/chromium-1228/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing'
);
const BASE = 'http://127.0.0.1:8881';

const SHOTS = [
  { name: 'woohome', url: '/wp-admin/admin.php?page=wc-admin' },
  { name: 'wooanalytics', url: '/wp-admin/admin.php?page=wc-admin&path=%2Fanalytics%2Foverview' },
];

const browser = await chromium.launch({ executablePath: EXE });
const ctx = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await ctx.newPage();

// prime the auto-login once
await page.goto(BASE + '/wp-admin/', { waitUntil: 'domcontentloaded', timeout: 60000 });

for (const level of [0, 1]) {
  for (const shot of SHOTS) {
    const join = shot.url.includes('?') ? '&' : '?';
    await page.goto(BASE + shot.url + join + 'quire=' + level, {
      waitUntil: 'networkidle', timeout: 90000,
    }).catch(e => console.log(`  (networkidle timeout on ${shot.name}, continuing)`));
    await page.waitForTimeout(6000); // let React settle
    await page.screenshot({ path: `${shot.name}-q${level}.png` });

    // computed-style probe: grab accent-ish elements the wc-admin app renders
    const styles = await page.evaluate(() => {
      const pick = (sel) => {
        const el = document.querySelector(sel);
        if (!el) return null;
        const cs = getComputedStyle(el);
        return { sel, color: cs.color, bg: cs.backgroundColor, borderBottom: cs.borderBottomColor };
      };
      return {
        themeColor: getComputedStyle(document.documentElement).getPropertyValue('--wp-admin-theme-color').trim(),
        componentsAccent: getComputedStyle(document.documentElement).getPropertyValue('--wp-components-color-accent').trim(),
        candidates: [
          pick('.components-button.is-primary'),
          pick('.woocommerce-layout__activity-panel-tab.is-active'),
          pick('.components-tab-panel__tabs-item.is-active'),
          pick('.woocommerce-summary__item-value'),
          pick('a.components-button'),
          pick('.woocommerce-layout__header a'),
          pick('.woocommerce-task-list__item .woocommerce-task-list__item-title'),
        ].filter(Boolean),
      };
    });
    console.log(`${shot.name} q${level}:`, JSON.stringify(styles, null, 1));
  }
}

await browser.close();
console.log('done');
