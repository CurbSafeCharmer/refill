describe('Home page', () => {
  it('loads the Vue app', async () => {
    // add the date as a param to prevent caching issues
    await browser.url('/?_=' + Date.now());
    const tagline = await $('h2.tagline');
    await tagline.waitForExist({ timeout: 5000 });
    await expect(tagline).toHaveTextContaining('Expand bare references with ease');
  });
});
