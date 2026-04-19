describe('Legacy page', () => {
  it('redirects to results page and gets success message', async () => {
    await browser.url('/result.php?page=Main_Page&defaults=y&nowatch=y&wiki=en&_=' + Date.now());

    // wait for navigation to result page
    await browser.waitUntil(async () => (await browser.getUrl()).includes('/result/'), { timeoutMsg: 'expected to navigate to result page' });

    // wait for the progress wrapper to contain Success
    const progress = await $('.progress-wrapper');
    await progress.waitForExist();
    await expect(progress).toHaveTextContaining('Success');

	// assert that enwiki's Main Page wikitext was read correctly
    const resultWrapper = await $('[name="wpTextbox1"');
    await resultWrapper.waitForExist();
    await expect(resultWrapper).toHaveValueContaining('Main page of the English Wikipedia');
  });
  it('no Webpack runtime errors', async () => {
    await browser.url('/result.php?page=Main_Page&defaults=y&nowatch=y&wiki=en&_=' + Date.now());
    const runtimeError = await $('#webpack-dev-server-client-overlay');
    await expect(runtimeError).not.toBeExisting();
  });
});
