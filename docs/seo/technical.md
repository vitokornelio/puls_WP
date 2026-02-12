# Технический SEO — tdpuls.com

---

## robots.txt

**Управление:** `$tdp_seo_config` в `theme/functions.php:424-457`

### Blocked params (Disallow + noindex)
`add_to_wishlist`, `_wpnonce`, `add-to-cart`, `design`, `s`, `lang`

### Filter attrs (Clean-param для Яндекса)
33 WooCommerce-атрибута: `filter_proizvoditel`, `filter_tip-oborudovaniya`, `filter_oblast-primeneniya` и др.

### Blocked paths
`/cart/`, `/checkout/`, `/my-account/`, `/wishlist/`, `/blocks/`, `/feed/`, `/*/feed/`

### Расширение
- Новый атрибут → в `filter_attrs`
- Новый параметр → в `blocked_params`
- Новый путь → в `blocked_paths`

---

## Sitemap (Rank Math)

| Sitemap | URL | Включён |
|---|---|---|
| Записи | `post-sitemap1.xml`, `post-sitemap2.xml` | Да |
| Страницы | `page-sitemap.xml` | Да |
| Товары | `product-sitemap1.xml`, `product-sitemap2.xml` | Да |
| Категории блога | `category-sitemap.xml` | Да |
| Категории товаров | `product_cat-sitemap.xml` | Да (включён 10.02) |

**Кэш sitemap:** `wp-content/uploads/rank-math/*.xml`
**Сброс:** `rm -f /var/www/tdpuls.com/public/wp-content/uploads/rank-math/*.xml && wp cache flush --allow-root`

---

## Schema.org

### Organization (theme/functions.php, `rank_math/json_ld`)
- name, logo, telephone, email, address (Ростов-на-Дону), contactPoint

### Product (theme/functions.php, `rank_math/json_ld`)
- brand (из `pa_proizvoditel`), sku (slug)
- Цена есть → `AggregateOffer` + `lowPrice`
- Цена пуста → `Offer` + `availability: InStock`

### BreadcrumbList
- Товары: автогенерация в `rank_math/json_ld` — Главная > Категория > Подкатегория > Товар
- Категории: Rank Math auto
- ВСУЗИ хаб: Rank Math auto (Главная → Каталог → ИР → ВСУЗИ)

### CollectionPage + FAQPage
- ВСУЗИ хаб: `page-vsuzi-hub.php` (inline JSON-LD)

---

## Редиректы

| Старый URL | Новый URL | Тип | Где |
|---|---|---|---|
| `/vsuzi/` | `/product-category/.../vsuzi/` | 301 | `theme/functions.php` |
| `/tag/*` | `/info/` | 301 | nginx |
| `/privacy-policy/` | policy.pdf | 301 | nginx |
| 4 старых товара ВСУЗИ | новые товары | 301 | `theme/functions.php` |

---

## noindex

**Управление:** `rank_math/frontend/robots` фильтр в `theme/functions.php`

- Параметры из `blocked_params` → noindex
- `?filter_*` → noindex
- `noindex_pages` → пустой массив (инфраструктура на месте)

---

## Кэширование

| Уровень | Технология | TTL | Сброс |
|---|---|---|---|
| nginx FastCGI | `/var/cache/nginx/fastcgi/` | 1 ГБ, inactive 60m | `rm -rf .../fastcgi/*` |
| Redis Object Cache | `allkeys-lru` | 256 МБ | `wp cache flush` |
| OPcache | 256 МБ | revalidate 60s | `systemctl reload php8.3-fpm` |
| Sitemap (Rank Math) | `wp-content/uploads/rank-math/*.xml` | до invalidation | удалить файлы + flush |

**Автоочистка FastCGI:** хуки `save_post`, `edited_term` в `theme/functions.php`

---

## Производительность

| Ресурс | До (07.02) | После (08.02) |
|---|---|---|
| JS файлов | 20 | 17 |
| CSS файлов | 15 | 9 |
| Плагинов | 18 | 11 |
| WebP | нет | nginx content negotiation |
