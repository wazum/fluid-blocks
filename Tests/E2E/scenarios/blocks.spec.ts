import { expect, test, type APIRequestContext } from '@playwright/test'

const placeholder = '<!--fluid-blocks:'

async function fetchTwice(request: APIRequestContext, path: string) {
  const first = await request.get(path)
  const second = await request.get(path)
  expect(first.ok()).toBe(true)
  expect(second.ok()).toBe(true)

  return { first, second }
}

test('content elements fill slots rendered before them', async ({ page }) => {
  await page.goto('/')

  await expect(page.locator('#stage h1')).toHaveText('Welcome <b>home</b>')
  await expect(page.locator('#page')).toHaveClass('home')
  await expect(page.locator('#scripts .script')).toHaveText(['first script', 'second script'])
  await expect(page.locator('head meta[name="e2e-script"]')).toHaveCount(2)
})

test('slots show their fallback when no element wrote a block', async ({ page }) => {
  await page.goto('/fallbacks')

  await expect(page.locator('#stage h1')).toHaveText('Fallbacks')
  await expect(page.locator('#page')).toHaveClass('page')
  await expect(page.locator('#scripts')).toBeEmpty()
})

test('a slot inside a block is filled by a later element', async ({ page }) => {
  await page.goto('/nested')

  await expect(page.locator('#stage h1')).toHaveText('Nested stage')
  await expect(page.locator('#subline')).toHaveText('Subline from a later element')
  await expect(page.locator('#scripts .script')).toHaveText(['badge 10'])
})

test('the page cache stores the resolved page', async ({ request }) => {
  const { second } = await fetchTwice(request, '/')
  const html = await second.text()

  expect(second.headers()['x-typo3-debug-cache']).toContain('Cached page generated')
  expect(html).toContain('<h1>Welcome &lt;b&gt;home&lt;/b&gt;</h1>')
  expect(html).not.toContain(placeholder)
})

test('a block from an uncached element does not reach the cached slot', async ({ request }) => {
  const { first, second } = await fetchTwice(request, '/uncached-element')

  for (const response of [first, second]) {
    const html = await response.text()
    expect(html).toContain('<p id="uncached">Uncached element body</p>')
    expect(html).toContain('<span class="script">cached script</span>')
    expect(html).not.toContain('uncached script')
    expect(html).not.toContain(placeholder)
  }
})

test('slots are resolved on a page without page cache', async ({ request }) => {
  const { first, second } = await fetchTwice(request, '/no-cache')

  expect(second.headers()['x-typo3-debug-cache']).toBeUndefined()
  for (const response of [first, second]) {
    const html = await response.text()
    expect(html).toContain('<h1>Stage without page cache</h1>')
    expect(html).toContain('<span class="script">no cache script</span>')
    expect(html).not.toContain(placeholder)
  }
})

test('the error page does not show blocks of the page that failed', async ({ request }) => {
  const response = await request.get('/broken')
  const html = await response.text()

  expect(response.status()).toBe(404)
  expect(html).toContain('<h1>Not found</h1>')
  expect(html).not.toContain('Stage of the broken page')
  expect(html).not.toContain('broken page script')
  expect(html).not.toContain(placeholder)
})

test('no page leaks a placeholder', async ({ request }) => {
  for (const path of ['/', '/fallbacks', '/nested', '/uncached-element', '/no-cache']) {
    const response = await request.get(path)
    expect(await response.text(), path).not.toContain(placeholder)
  }
})
