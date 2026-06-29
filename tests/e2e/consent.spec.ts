import { test, expect } from '@playwright/test';

test.describe('GDPR consent manager', () => {
  test('blocking modal appears on first visit', async ({ page }) => {
    await page.goto('/');
    const modal = page.locator('.wc-consent-modal[role="dialog"]');
    await expect(modal).toBeVisible();
    await expect(page.locator('.wc-consent-modal__accept-all')).toBeVisible();
    await expect(page.locator('.wc-consent-modal__reject-all')).toBeVisible();
    await expect(page.locator('.wc-consent-modal__customize')).toBeVisible();
    // Page is scroll-locked while the modal is up.
    await expect(page.locator('html')).toHaveClass(/wc-consent-locked/);
  });

  test('reject all keeps Google Maps iframes defused on the arrival page', async ({ page }) => {
    await page.goto('/guide/arrival/');
    // Defused: real src absent, placeholder present.
    await expect(page.locator('.wc-consent-embed')).toHaveCount(2);
    await expect(page.locator('iframe[src*="maps.google"]')).toHaveCount(0);

    await page.locator('.wc-consent-modal__reject-all').click();
    await expect(page.locator('.wc-consent-modal')).toBeHidden();
    // Still defused after rejection.
    await expect(page.locator('iframe[src*="maps.google"]')).toHaveCount(0);
  });

  test('accept all loads the iframes and persists across reload', async ({ page }) => {
    await page.goto('/guide/arrival/');
    await page.locator('.wc-consent-modal__accept-all').click();
    await expect(page.locator('.wc-consent-modal')).toBeHidden();
    await expect(page.locator('iframe[src*="maps.google"]')).toHaveCount(2);

    // Reload: choice persisted, no modal, iframes still live.
    await page.reload();
    await expect(page.locator('.wc-consent-modal')).toBeHidden();
    await expect(page.locator('iframe[src*="maps.google"]')).toHaveCount(2);
  });

  test('customize lets you grant only functional', async ({ page }) => {
    await page.goto('/guide/arrival/');
    await page.locator('.wc-consent-modal__customize').click();
    await page.locator('.wc-consent-toggle[data-category="functional"]').check();
    await page.locator('.wc-consent-modal__save').click();
    await expect(page.locator('iframe[src*="maps.google"]')).toHaveCount(2);
  });

  test('footer "Cookie settings" reopens the modal', async ({ page }) => {
    await page.goto('/');
    await page.locator('.wc-consent-modal__accept-all').click();
    await expect(page.locator('.wc-consent-modal')).toBeHidden();

    await page.locator('.wc-consent-settings-link').click();
    await expect(page.locator('.wc-consent-modal[role="dialog"]')).toBeVisible();
    // Detail view with toggles is shown on reopen.
    await expect(page.locator('.wc-consent-toggle[data-category="analytics"]')).toBeVisible();
  });
});
