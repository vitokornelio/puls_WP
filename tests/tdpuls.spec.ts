import { test, expect } from '@playwright/test';

test.describe('Главная страница', () => {
  test('загружается и содержит заголовок', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Пульс/);
  });

  test('навигация отображается', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('link', { name: 'КАТАЛОГ', exact: true })).toBeVisible();
  });

  test('блок «Специальное предложение» виден', async ({ page }) => {
    await page.goto('/');
    const section = page.getByText('Специальное предложение').first();
    await expect(section).toBeVisible();
  });
});

test.describe('Каталог товаров', () => {
  test('категория КТ загружается', async ({ page }) => {
    await page.goto('/product-category/kt/');
    await expect(page.locator('.products').first()).toBeVisible();
  });

  test('карточки товаров отображаются', async ({ page }) => {
    await page.goto('/product-category/kt/');
    const products = page.locator('.product-small, .product').first();
    await expect(products).toBeVisible();
  });
});

test.describe('Страница товара', () => {
  test('Philips Access CT загружается', async ({ page }) => {
    await page.goto('/shop/kt/philips-access-ct-kompyuternyj-tomograf/');
    await expect(page.locator('h1')).toContainText(/Access CT/i);
  });

  test('кнопка «Получить КП» присутствует', async ({ page }) => {
    await page.goto('/shop/kt/philips-access-ct-kompyuternyj-tomograf/');
    const btn = page.getByText('Получить КП').first();
    await expect(btn).toBeVisible();
  });
});

test.describe('ВСУЗИ хаб', () => {
  test('страница /vsuzi/ загружается', async ({ page }) => {
    await page.goto('/vsuzi/');
    await expect(page).toHaveTitle(/ВСУЗИ|IVUS/i);
  });
});

test.describe('Модалка Битрикс24', () => {
  test('модальное окно открывается по клику', async ({ page }) => {
    await page.goto('/shop/kt/philips-access-ct-kompyuternyj-tomograf/');
    const trigger = page.locator('a[href="#b24-modal"]').first();
    await trigger.click();
    const modal = page.locator('#b24-modal');
    await expect(modal).toHaveClass(/b24-active/);
  });
});
