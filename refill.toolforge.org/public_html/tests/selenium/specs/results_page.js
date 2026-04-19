describe('Results page', () => {
  beforeEach(async () => {
    await browser.url('/?_=' + Date.now());

    // open the custom wikicode textarea
    const toggle = await $('#toggle-wikicode');
    await toggle.waitForExist();
    await toggle.click();

    // wait for the textarea produced by v-textarea (Vuetify may render it asynchronously)
    const ta = await $('textarea');
    await ta.waitForExist();
    // ensure it's focused and set the value
    await ta.click();
    const sample = `Testing 123.<ref>https://apnews.com/article/iran-us-israel-war-ap-visit-daily-life-712a964141a72724971765850ca675ca</ref>

== References ==
{{Reflist}}
`;
    await ta.setValue(sample);

    // submit
    const submit = await $('#submit-btn');
    await submit.waitForExist();
    await submit.click();

    // wait for navigation to result page
    await browser.waitUntil(async () => (await browser.getUrl()).includes('/result/'), { timeoutMsg: 'expected to navigate to result page' });
  });

  it('no Webpack runtime errors', async () => {
    const runtimeError = await $('#webpack-dev-server-client-overlay');
    await expect(runtimeError).not.toBeExisting();
  });

  it('shows success message', async () => {
    const progress = await $('.progress-wrapper');
    await progress.waitForExist();
    await expect(progress).toHaveTextContaining('Success');
  });

  it('produces a diff with WikEdDiff', async () => {
    const diffInsert = await $('.wikEdDiffInsert');
    await expect(diffInsert).toBeExisting();
  });
});
