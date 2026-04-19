describe('Results page', () => {
  const sample = `Testing 123.<ref>https://apnews.com/article/iran-us-israel-war-ap-visit-daily-life-712a964141a72724971765850ca675ca</ref>

== References ==
{{Reflist}}
`;

  it('sends pasted wikicode to refill-api and gets success message', async () => {
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
    await ta.setValue(sample);

    // submit
    const submit = await $('#submit-btn');
    await submit.waitForExist();
    await submit.click();

    // wait for navigation to result page
    await browser.waitUntil(async () => (await browser.getUrl()).includes('/result/'), { timeoutMsg: 'expected to navigate to result page' });

    // wait for the progress wrapper to contain Success
    const progress = await $('.progress-wrapper');
    await progress.waitForExist();
    await expect(progress).toHaveTextContaining('Success');
  });
});
