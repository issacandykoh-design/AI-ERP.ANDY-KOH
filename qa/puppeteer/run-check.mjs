import puppeteer from 'puppeteer'
import fs from 'fs/promises'
import path from 'path'
import { setTimeout as wait } from 'timers/promises'

const BASE = 'http://127.0.0.1:8000'
const TEST_EMAIL = 'superadmin@example.com'
const TEST_PASSWORD = '12345678'
const OUTPUT_DIR = path.resolve('./qa/puppeteer/output')

async function ensureDir(dir) {
  try { await fs.mkdir(dir, { recursive: true }) } catch {}
}

async function visit(browser, url, name) {
  const page = await browser.newPage()
  const logs = []
  const requests = []
  const responses = []

  page.on('console', msg => {
    logs.push({ type: msg.type(), text: msg.text() })
  })
  page.on('request', req => {
    requests.push({ method: req.method(), url: req.url() })
  })
  page.on('response', res => {
    responses.push({ status: res.status(), url: res.url() })
  })

  let status = null
  let finalUrl = null
  let error = null

  try {
    const response = await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 })
    status = response ? response.status() : null
    finalUrl = page.url()
  } catch (e) {
    error = String(e)
  }

  const shotPath = path.join(OUTPUT_DIR, `${name}.png`)
  await page.screenshot({ path: shotPath, fullPage: true })
  await page.close()

  return { name, url, status, finalUrl, error, logs, requests, responses, screenshot: shotPath }
}

async function main() {
  await ensureDir(OUTPUT_DIR)
  const browser = await puppeteer.launch({ headless: 'new' })
  const results = []

  results.push(await visit(browser, `${BASE}/telescope`, 'telescope'))
  results.push(await visit(browser, `${BASE}/telescope/views`, 'telescope-views`'))
  // Try to visit while logged out
  results.push(await visit(browser, `${BASE}/account/settings/language-settings`, 'language-settings-logged-out`'))

  // Login flow
  const page = await browser.newPage()
  try {
    await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2', timeout: 60000 })
    await page.type('#email', TEST_EMAIL, { delay: 5 })
    // If Next button exists, click to reveal password section
    const nextBtn = await page.$('#submit-next')
    if (nextBtn) {
      await nextBtn.click()
      await page.waitForSelector('#password', { timeout: 15000 })
    }
    await page.type('#password', TEST_PASSWORD, { delay: 5 })
    await Promise.all([
      page.click('#submit-login'),
      page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 })
    ])
    const shotLogin = path.join(OUTPUT_DIR, `login-after.png`)
    await page.screenshot({ path: shotLogin, fullPage: true })
    results.push({ name: 'login', url: `${BASE}/login`, status: 200, finalUrl: page.url(), error: null, screenshot: shotLogin })
  } catch (e) {
    const shotLoginErr = path.join(OUTPUT_DIR, `login-error.png`)
    await page.screenshot({ path: shotLoginErr, fullPage: true })
    results.push({ name: 'login-error', url: `${BASE}/login`, status: null, finalUrl: page.url(), error: String(e), screenshot: shotLoginErr })
  }

  // Visit language settings after login
  try {
    const response = await page.goto(`${BASE}/account/settings/language-settings`, { waitUntil: 'networkidle2', timeout: 60000 })
    const shotLang = path.join(OUTPUT_DIR, `language-settings-after-login.png`)
    await page.screenshot({ path: shotLang, fullPage: true })
    results.push({ name: 'language-settings-after-login', url: `${BASE}/account/settings/language-settings`, status: response ? response.status() : null, finalUrl: page.url(), error: null, screenshot: shotLang })
  } catch (e) {
    const shotLangErr = path.join(OUTPUT_DIR, `language-settings-after-login-error.png`)
    await page.screenshot({ path: shotLangErr, fullPage: true })
    results.push({ name: 'language-settings-after-login-error', url: `${BASE}/account/settings/language-settings`, status: null, finalUrl: page.url(), error: String(e), screenshot: shotLangErr })
  }

  await page.close()

  // Fetch debug_error content (requires session; use a fresh page with cookies preserved by default context)
  const page2 = await browser.newPage()
  try {
    await page2.goto(`${BASE}/account/settings/language-settings?debug_error=1`, { waitUntil: 'networkidle2', timeout: 60000 })
    const html = await page2.content()
    await fs.writeFile(path.join(OUTPUT_DIR, 'language-settings-debug.html'), html)
    const shotLangDebug = path.join(OUTPUT_DIR, `language-settings-debug.png`)
    await page2.screenshot({ path: shotLangDebug, fullPage: true })
    results.push({ name: 'language-settings-debug', url: `${BASE}/account/settings/language-settings?debug_error=1`, status: 200, finalUrl: page2.url(), error: null, screenshot: shotLangDebug })
  } catch (e) {
    results.push({ name: 'language-settings-debug-error', url: `${BASE}/account/settings/language-settings?debug_error=1`, status: null, finalUrl: null, error: String(e), screenshot: null })
  }
  await page2.close()

  // Pull exceptions from Telescope API
  try {
    await wait(1000)
    const apiUrl = `${BASE}/telescope/telescope-api/exceptions?tag=&before=&take=50&family_hash=`
    const res = await fetch(apiUrl)
    const data = await res.json()
    await fs.writeFile(path.join(OUTPUT_DIR, 'exceptions.json'), JSON.stringify(data, null, 2))
    results.push({ name: 'telescope-exceptions-api', url: apiUrl, status: res.status, finalUrl: apiUrl, error: null, screenshot: null })
  } catch (e) {
    results.push({ name: 'telescope-exceptions-api-error', url: `${BASE}/telescope/telescope-api/exceptions`, status: null, finalUrl: null, error: String(e), screenshot: null })
  }

  await browser.close()

  const statusPath = path.join(OUTPUT_DIR, 'status.json')
  await fs.writeFile(statusPath, JSON.stringify({ ts: new Date().toISOString(), results }, null, 2))
  console.log('Saved:', { statusPath, outputDir: OUTPUT_DIR })
}

main().catch(async (e) => {
  try {
    await fs.writeFile(path.join(OUTPUT_DIR, 'error.txt'), String(e))
  } catch {}
  console.error(e)
  process.exit(1)
})
