import { test, expect } from '@playwright/test';

test( 'grid shows activity cards linking to single pages', async ( { page } ) => {
	await page.goto( '/activities/' );

	const cards = page.locator( '.activity-card' );
	await expect( cards.first() ).toBeVisible();
	const count = await cards.count();
	expect( count ).toBeGreaterThanOrEqual( 1 );

	const href = await cards.first().getAttribute( 'href' );
	expect( href ).toContain( '/activities/' );
} );

test( 'a single activity page renders title and content', async ( { page } ) => {
	await page.goto( '/activities/' );

	const firstCard = page.locator( '.activity-card' ).first();
	await expect( firstCard ).toBeVisible();
	await firstCard.click();

	await expect( page.locator( 'main h1' ) ).toBeVisible();
	await expect( page.locator( '.back-to-activities' ) ).toBeVisible();
} );
