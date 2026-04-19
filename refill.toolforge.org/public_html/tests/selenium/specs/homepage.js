describe('Homepage', () => {
  it('loads the Vue app in English', async () => {
    // add the date as a param to prevent caching issues
    await browser.url('/?_=' + Date.now());
    const tagline = await $('h2.tagline');
    await tagline.waitForExist({ timeout: 5000 });
    await expect(tagline).toHaveTextContaining('Expand bare references with ease');
  });
  it('loads the Vue app in Spanish', async () => {
    await browser.setCookies({ name: 'TsIntuition_userlang', value: 'es' });
    await browser.url('/?_=' + Date.now());
    const tagline = await $('h2.tagline');
    await tagline.waitForExist({ timeout: 5000 });
    await expect(tagline).toHaveTextContaining('Ampliar las referencias sencillas con facilidad');
  });
});
