const fs = require('fs');
const puppeteer = require('puppeteer');

async function run() {
  const raw = fs.readFileSync('image_result.json', 'utf-8');
  const obj = JSON.parse(raw);
  const url = obj?.data?.data?.results?.[0]?.response?.data?.image?.s3url;
  if (!url) {
    console.log('No URL found in image_result.json');
    process.exit(1);
  }
  const browser = await puppeteer.launch({ headless: 'new' });
  const page = await browser.newPage();
  const resp = await page.goto(url, { waitUntil: 'networkidle0' });
  console.log(`Navigate status: ${resp?.status()} ok=${resp?.ok()}`);
  await page.screenshot({ path: 'puppeteer_image_check.png' });
  await browser.close();
  console.log('Saved puppeteer_image_check.png');
}

run().catch(e => { console.error(e); process.exit(1); });

