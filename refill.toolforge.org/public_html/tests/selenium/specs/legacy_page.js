describe('Legacy page', () => {
  it('redirects to results page and gets success message', async () => {
    await browser.url('/result.php?page=Main_Page&defaults=y&nowatch=y&wiki=en&_=' + Date.now());

    // wait for navigation to result page
    await browser.waitUntil(async () => (await browser.getUrl()).includes('/result/'), { timeout: 10000, timeoutMsg: 'expected to navigate to result page' });

    // wait for the progress wrapper to contain Success
    const progress = await $('.progress-wrapper');
    await progress.waitForExist({ timeout: 20000 });
    await expect(progress).toHaveTextContaining('Success', { timeout: 20000 });

	// assert that enwiki's Main Page wikitext was read correctly
    const resultWrapper = await $('[name="wpTextbox1"');
    await resultWrapper.waitForExist({ timeout: 10000 });
    await expect(resultWrapper).toHaveValueContaining('Main page of the English Wikipedia', { timeout: 10000 });
  });
});
