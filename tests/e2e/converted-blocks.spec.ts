import { test, expect, Page } from '@playwright/test';
import { seedConsent } from './utils';

/**
 * The guide and legal pages were converted from the legacy pediment/* blocks
 * to core blocks with named styles (is-style-card, is-style-prose,
 * is-style-steps, pediment/icon is-style-tile). These checks pin the look the
 * legacy blocks had, read from computed style at desktop width.
 */

/** Resolve a CSS length token (e.g. a spacing preset) to its computed px value. */
async function tokenPx(page: Page, token: string): Promise<string> {
  return page.evaluate((t) => {
    const probe = document.createElement('div');
    probe.style.marginTop = `var(${t})`;
    document.body.appendChild(probe);
    const value = getComputedStyle(probe).marginTop;
    probe.remove();
    return value;
  }, token);
}

/** Resolve a colour token to its computed rgb() value. */
async function tokenColor(page: Page, token: string): Promise<string> {
  return page.evaluate((t) => {
    const probe = document.createElement('div');
    probe.style.color = `var(${t})`;
    document.body.appendChild(probe);
    const value = getComputedStyle(probe).color;
    probe.remove();
    return value;
  }, token);
}

test.describe('Converted pediment blocks', () => {
  test.beforeEach(async ({ page }) => {
    await seedConsent(page);
    await page.setViewportSize({ width: 1440, height: 1100 });
  });

  test('guide card is a left-aligned white panel with the card shadow', async ({ page }) => {
    await page.goto('/guide/');
    const card = page.locator('.guide-cards .is-style-card').first();
    const style = await card.evaluate((el) => {
      const cs = getComputedStyle(el);
      return {
        textAlign: cs.textAlign,
        radius: cs.borderTopLeftRadius,
        shadow: cs.boxShadow,
        background: cs.backgroundColor,
      };
    });
    expect(style.textAlign).toBe('left');
    expect(style.radius).toBe('24px');
    expect(style.shadow).toContain('rgba(58, 38, 22, 0.4)');
    expect(style.background).toBe(await tokenColor(page, '--wp--preset--color--surface-elevated'));
  });

  test('guide icon tile is solid amber with a dark glyph', async ({ page }) => {
    await page.goto('/guide/');
    const tile = page.locator('.guide-cards .wp-block-pediment-icon__glyph').first();
    const { bg, glyph } = await tile.evaluate((el) => ({
      bg: getComputedStyle(el).backgroundColor,
      glyph: getComputedStyle(el.querySelector('svg')!).color,
    }));
    expect(bg).toBe('rgb(254, 198, 1)');
    expect(glyph).toBe('rgb(36, 28, 18)');
  });

  test('card title and text keep the 10px gap', async ({ page }) => {
    await page.goto('/guide/');
    const card = page.locator('.guide-cards .is-style-card').first();
    const title = await card.locator('h3').boundingBox();
    const text = await card.locator('h3 + p').boundingBox();
    expect(title).not.toBeNull();
    expect(text).not.toBeNull();
    expect(Math.round(text!.y - (title!.y + title!.height))).toBe(10);
  });

  test('prose keeps its vertical rhythm and amber link underline', async ({ page }) => {
    await page.goto('/imprint/');
    const prose = page.locator('.is-style-prose').first();
    // On /imprint/ the second child is an h2; the list after it is the first
    // plain block that follows a sibling, so it carries the body rhythm.
    const list = prose.locator('> h2 + ul').first();
    const h2 = prose.locator('> h2').first();
    const listTop = await list.evaluate((el) => getComputedStyle(el).marginBlockStart);
    const h2Top = await h2.evaluate((el) => getComputedStyle(el).marginBlockStart);
    expect(listTop).toBe(await tokenPx(page, '--wp--preset--spacing--30'));
    expect(h2Top).toBe(await tokenPx(page, '--wp--preset--spacing--60'));

    const link = prose.locator('a').first();
    const deco = await link.evaluate((el) => {
      const cs = getComputedStyle(el);
      return { line: cs.textDecorationLine, color: cs.textDecorationColor };
    });
    expect(deco.line).toBe('underline');
    // --wc-amber, the underline colour of the legacy prose links.
    expect(deco.color).toBe(await tokenColor(page, '--wc-amber'));
  });

  test('arrival steps keep the legacy number badges and their titles do not overlap', async ({ page }) => {
    await page.goto('/guide/arrival/');
    const steps = page.locator('.is-style-steps').first();
    // No cream card around the steps: the legacy card rule is scoped to
    // body.parent-pageid-179, which matches no guide page (production's guide
    // is page 201), so the legacy steps rendered on the band background.
    const background = await steps.evaluate((el) => getComputedStyle(el).backgroundColor);
    expect(background).toBe('rgba(0, 0, 0, 0)');

    // Each step's number badge: a surface-coloured tile with the legacy 10px radius.
    const badge = await steps.locator('> *').first().evaluate((el) => {
      const cs = getComputedStyle(el, '::before');
      return { background: cs.backgroundColor, radius: cs.borderTopLeftRadius, content: cs.content };
    });
    expect(badge.background).toBe(await tokenColor(page, '--wp--preset--color--surface'));
    expect(badge.radius).toBe('10px');
    expect(badge.content).not.toBe('none');

    const titles = steps.locator('> * > h3');
    const count = await titles.count();
    expect(count).toBeGreaterThan(1);
    for (let i = 0; i < count - 1; i++) {
      const a = (await titles.nth(i).boundingBox())!;
      const b = (await titles.nth(i + 1).boundingBox())!;
      const overlapX = a.x < b.x + b.width && b.x < a.x + a.width;
      const overlapY = a.y < b.y + b.height && b.y < a.y + a.height;
      expect(overlapX && overlapY, `step titles ${i} and ${i + 1} overlap`).toBe(false);
    }
  });

  test('FAQ accordion keeps its rounded panel and opens to show the answer', async ({ page }) => {
    await page.goto('/guide/faq/');
    const item = page.locator('details.is-style-accordion').first();
    const summary = item.locator('> summary');

    const style = await item.evaluate((el) => {
      const cs = getComputedStyle(el);
      return { radius: cs.borderTopLeftRadius, background: cs.backgroundColor };
    });
    // --wp--custom--radius--md-lg
    expect(style.radius).toBe('16px');
    expect(style.background).toBe(await tokenColor(page, '--wp--preset--color--surface-elevated'));
    // The legacy accordion's tall, easy-to-tap question row.
    expect(await summary.evaluate((el) => getComputedStyle(el).minHeight)).toBe('78px');

    const answer = item.locator('> :not(summary)').first();
    await expect(item).not.toHaveAttribute('open', '');
    await summary.click();
    await expect(item).toHaveAttribute('open', '');
    await expect(answer).toBeVisible();
    expect((await answer.innerText()).trim()).not.toBe('');
  });

  test('converted cards take part in the scroll reveal', async ({ page }) => {
    // reveal.js marks the converted selectors (.is-style-card, ...) with
    // data-reveal and adds is-in once they scroll into view. The CSS that hides
    // them is gated behind html.anim and turned off by prefers-reduced-motion,
    // so this test asks for motion explicitly.
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await page.goto('/guide/');
    await expect(page.locator('html')).toHaveClass(/\banim\b/);

    const cards = page.locator('.guide-cards .is-style-card');
    const card = cards.last();
    await expect(card).toHaveAttribute('data-reveal', '');

    await card.scrollIntoViewIfNeeded();
    await expect(card).toHaveClass(/\bis-in\b/);
    await expect(card).toHaveCSS('opacity', '1');
  });
});
