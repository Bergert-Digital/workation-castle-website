import { test, expect } from '@playwright/test';
import { seedConsent } from './utils';

test.beforeEach(async ({ page }) => {
	await seedConsent(page);
});

test('enhances into a range field and hides the native fallback', async ({ page }) => {
	await page.goto('/');
	await expect(page.locator('.wc-rangepicker__field')).toBeVisible();
	await expect(page.locator('.wc-rangepicker__fallback')).toBeHidden();
});

test('selecting a range writes checkIn/checkOut and updates the label', async ({ page }) => {
	await page.goto('/');
	const field = page.locator('.wc-rangepicker__field');
	await field.click();

	const pop = page.locator('.wc-rangepicker__pop');
	await expect(pop).toBeVisible();

	const days = pop.locator('.wc-rangepicker__day:not([disabled])');
	await days.nth(1).click();
	await days.nth(4).click();

	// Popover closes once both ends are chosen.
	await expect(pop).toBeHidden();

	const checkIn = await page
		.locator('.wc-rangepicker [data-role="checkin"]')
		.inputValue();
	const checkOut = await page
		.locator('.wc-rangepicker [data-role="checkout"]')
		.inputValue();
	expect(checkIn).toMatch(/^\d{4}-\d{2}-\d{2}$/);
	expect(checkOut).toMatch(/^\d{4}-\d{2}-\d{2}$/);
	await expect(field).toContainText('–');
});

test('desktop shows two months and past months are disabled', async ({ page }) => {
	await page.goto('/');
	await page.locator('.wc-rangepicker__field').click();
	await expect(page.locator('.wc-rangepicker__month')).toHaveCount(2);

	await page.locator('.wc-rangepicker__prev').click();
	const firstMonth = page.locator('.wc-rangepicker__month').first();
	await expect(
		firstMonth.locator('.wc-rangepicker__day:not([disabled])')
	).toHaveCount(0);
});

test('Escape closes the popover', async ({ page }) => {
	await page.goto('/');
	await page.locator('.wc-rangepicker__field').click();
	await expect(page.locator('.wc-rangepicker__pop')).toBeVisible();
	await page.keyboard.press('Escape');
	await expect(page.locator('.wc-rangepicker__pop')).toBeHidden();
});
