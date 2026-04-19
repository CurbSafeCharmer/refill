describe('Results page', () => {
  const sample = `Testing 123.<ref>https://apnews.com/article/iran-us-israel-war-ap-visit-daily-life-712a964141a72724971765850ca675ca</ref>

== References ==
{{Reflist}}
`;

  it('sends pasted wikicode to refill-api and gets success message', async () => {
    await browser.url('/?_=' + Date.now());

    // open the custom wikicode textarea
    const toggle = await $('#toggle-wikicode');
    await toggle.waitForExist({ timeout: 5000 });
    await toggle.click();

    // wait for the textarea produced by v-textarea (Vuetify may render it asynchronously)
    const ta = await $('textarea');
    await ta.waitForExist({ timeout: 15000 });
    // ensure it's focused and set the value
    await ta.click();
    await ta.setValue(sample);

    // submit
    const submit = await $('#submit-btn');
    await submit.waitForExist({ timeout: 5000 });
    await submit.click();

    // wait for navigation to result page
    await browser.waitUntil(async () => (await browser.getUrl()).includes('/result/'), { timeout: 10000, timeoutMsg: 'expected to navigate to result page' });

    // wait for the progress wrapper to contain Success
    const progress = await $('.progress-wrapper');
    await progress.waitForExist({ timeout: 20000 });
    await expect(progress).toHaveTextContaining('Success', { timeout: 20000 });
  });
});
