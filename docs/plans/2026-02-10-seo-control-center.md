# SEO Control Center — Централизация robots.txt, noindex, Clean-param

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Единый конфиг-массив `$tdp_seo_config` в `functions-new.php` управляет robots.txt (Disallow + Clean-param), noindex meta и исключениями из sitemap.

**Architecture:** Массив `$tdp_seo_config` определяет все SEO-исключения в одном месте. Три хука ниже читают этот массив: `robots_txt` генерирует Disallow + Clean-param для Яндекса; `rank_math/frontend/robots` ставит noindex по параметрам/страницам; Rank Math автоматически убирает noindex-страницы из sitemap.

**Tech Stack:** WordPress 6.9.1, WooCommerce 10.5.0, Rank Math SEO, PHP 8.3, Flatsome 3.13.1

---

## Контекст проблемы

- **15 988 неиндексированных URL** в Яндекс Вебмастере (wishlist+nonce дубли, фильтры, ?lang=en)
- SEO-контроль разбросан: `robots_txt` фильтр, `rank_math/frontend/robots` фильтр, per-page мета, Rank Math DB-опции
- `Clean-param` (Яндекс) — переносит метрики с дублей на канонический URL (в отличие от Disallow, который теряет метрики)
- `Clean-param` НЕ поддерживает wildcard для имён параметров — нужно перечислить все 33 filter-атрибута

### Важно: поведение Clean-param в robots.txt

- `Clean-param` — **intersectional** директива: работает независимо от `User-agent` блока
- Можно размещать в любом месте файла, даже после `User-agent: *`
- Если добавить `User-agent: Yandex` блок с Disallow/Allow, то `User-agent: *` **будет проигнорирован** Яндексом
- **Поэтому:** Clean-param кладём БЕЗ отдельного `User-agent: Yandex` блока — просто в конец файла

---

## Task 1: Собрать все filter-атрибуты WooCommerce

**Files:**
- Reference: вывод WP-CLI `wc_get_attribute_taxonomies()`

**Step 1: Зафиксировать список атрибутов**

Список из 33 атрибутов уже получен. Для Clean-param нужны только имена параметров (без `filter_` префикса), т.к. Clean-param работает с точными именами:

```
amplituda, apertura-gentri-sm, ch-o-d-g-v-n-m-p, d-m-p-d,
diagnosticheskij-klass, form-faktor, glubina-s-dugi-sm, gradienty-tl-m-s,
katetery, klinicheskij-segment, kol-vo-rabochih-mest, kolichestvo-rch-kanalov,
kolichestvo-srezov, linejka, linejki-oborudovaniya, mg-sd-tl, model,
moshhnost-generatora-kvt, napryazhyonnost-polya-t, naznachenie,
parametry-detektora, proizvoditel, shirina-detektora, tehnologiya,
tip-anoda-s-dugi, tip-apparata, tip-issledovaniya, tip-krepleniya, tip-kt,
tip-matritsy, tip-oborudovaniya, tip-priyomnika-s-dugi, tip-sistemy
```

Clean-param формат: `Clean-param: filter_amplituda&filter_apertura-gentri-sm&...`

Но длина одной строки Clean-param — макс 500 символов. Нужно разбить на несколько строк.

---

## Task 2: Заменить текущий SEO-код на централизованный конфиг

**Files:**
- Modify: `functions-new.php:424-471` (удалить старый код, заменить новым)

**Step 1: Удалить старый код**

Удалить строки 424-471 (комментарий `SEO Optimization`, фильтр `robots_txt`, фильтр `rank_math/frontend/robots`).

**Step 2: Вставить новый код — конфиг + хуки**

Вставить на место удалённого (после строки ~423, перед `// Title для главной страницы`):

```php
/**
 * SEO Control Center - TDPULS
 * Единый конфиг для robots.txt, noindex, Clean-param
 * Менять параметры ТОЛЬКО здесь — хуки ниже читают этот массив
 */
$tdp_seo_config = [
    // GET-параметры: Disallow (все краулеры) + Clean-param (Яндекс) + noindex meta
    'blocked_params' => [
        'add_to_wishlist', '_wpnonce', 'add-to-cart', 'design', 's', 'lang',
    ],

    // Префикс фильтров WooCommerce — для Disallow wildcard
    'filter_prefix' => 'filter_',

    // Конкретные filter-атрибуты — для Clean-param (Яндекс не поддерживает wildcard)
    'filter_attrs' => [
        'amplituda', 'apertura-gentri-sm', 'ch-o-d-g-v-n-m-p', 'd-m-p-d',
        'diagnosticheskij-klass', 'form-faktor', 'glubina-s-dugi-sm', 'gradienty-tl-m-s',
        'katetery', 'klinicheskij-segment', 'kol-vo-rabochih-mest', 'kolichestvo-rch-kanalov',
        'kolichestvo-srezov', 'linejka', 'linejki-oborudovaniya', 'mg-sd-tl', 'model',
        'moshhnost-generatora-kvt', 'napryazhyonnost-polya-t', 'naznachenie',
        'parametry-detektora', 'proizvoditel', 'shirina-detektora', 'tehnologiya',
        'tip-anoda-s-dugi', 'tip-apparata', 'tip-issledovaniya', 'tip-krepleniya', 'tip-kt',
        'tip-matritsy', 'tip-oborudovaniya', 'tip-priyomnika-s-dugi', 'tip-sistemy',
    ],

    // Пути для Disallow в robots.txt
    'blocked_paths' => [
        '/cart/', '/checkout/', '/my-account/', '/wishlist/',
        '/blocks/', '/feed/', '/*/feed/',
    ],

    // Страницы с noindex (slug) — не индексировать
    'noindex_pages' => ['vsuzi'],
];

// --- robots.txt: Disallow + Clean-param ---
add_filter('robots_txt', function($output, $public) {
    global $tdp_seo_config;

    // 1. Disallow для GET-параметров (все краулеры)
    $extra  = "\n# Block duplicate content parameters\n";
    foreach ($tdp_seo_config['blocked_params'] as $param) {
        $extra .= "Disallow: /*?{$param}=\n";
        $extra .= "Disallow: /*?*{$param}=\n";
    }
    // Фильтры — wildcard по префиксу
    $extra .= "Disallow: /*?{$tdp_seo_config['filter_prefix']}*\n";
    $extra .= "Disallow: /*?*{$tdp_seo_config['filter_prefix']}*\n";

    // 2. Disallow для путей (сервисные страницы)
    $extra .= "\n# Block internal/service pages\n";
    foreach ($tdp_seo_config['blocked_paths'] as $path) {
        $extra .= "Disallow: {$path}\n";
    }

    // 3. Clean-param для Яндекса (склеивание дублей, перенос метрик)
    // Директива intersectional — работает без User-agent: Yandex
    // Макс длина строки 500 символов — разбиваем на группы
    $extra .= "\n# Yandex Clean-param: merge duplicate URLs, transfer metrics\n";

    // 3a. Основные параметры
    $extra .= "Clean-param: " . implode('&', $tdp_seo_config['blocked_params']) . "\n";

    // 3b. Filter-атрибуты (разбиваем на строки по ~450 символов)
    $filter_params = array_map(function($attr) {
        global $tdp_seo_config;
        return $tdp_seo_config['filter_prefix'] . $attr;
    }, $tdp_seo_config['filter_attrs']);

    $line = '';
    foreach ($filter_params as $fp) {
        $candidate = $line === '' ? $fp : $line . '&' . $fp;
        if (strlen('Clean-param: ' . $candidate) > 450) {
            $extra .= "Clean-param: {$line}\n";
            $line = $fp;
        } else {
            $line = $candidate;
        }
    }
    if ($line !== '') {
        $extra .= "Clean-param: {$line}\n";
    }

    return $output . $extra;
}, 999, 2);

// --- noindex для URL с параметрами-дублями ---
add_filter('rank_math/frontend/robots', function($robots) {
    global $tdp_seo_config;

    // noindex для конкретных страниц
    foreach ($tdp_seo_config['noindex_pages'] as $slug) {
        if (is_page($slug)) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'nofollow';
            return $robots;
        }
    }

    // noindex для GET-параметров из конфига
    foreach ($tdp_seo_config['blocked_params'] as $param) {
        if (isset($_GET[$param])) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'nofollow';
            return $robots;
        }
    }

    // noindex для filter_* параметров
    foreach (array_keys($_GET) as $key) {
        if (strpos($key, $tdp_seo_config['filter_prefix']) === 0) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'nofollow';
            return $robots;
        }
    }

    return $robots;
});
```

---

## Task 3: Деплой на сервер

**Step 1: Загрузить файл**

```bash
scp /Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php \
    root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
```

**Step 2: Очистить кеши**

```bash
ssh root@85.198.96.28 "redis-cli FLUSHALL && rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx && systemctl reload php8.3-fpm"
```

---

## Task 4: Верификация robots.txt

**Step 1: Проверить robots.txt**

```bash
ssh root@85.198.96.28 "curl -s https://tdpuls.com/robots.txt"
```

**Ожидаемый результат:**
```
User-agent: *
Disallow: /wp-content/uploads/wc-logs/
Disallow: /wp-content/uploads/woocommerce_transient_files/
Disallow: /wp-content/uploads/woocommerce_uploads/
Disallow: /*?add-to-cart=
Disallow: /*?*add-to-cart=
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://tdpuls.com/sitemap_index.xml

# Block duplicate content parameters
Disallow: /*?add_to_wishlist=
Disallow: /*?*add_to_wishlist=
Disallow: /*?_wpnonce=
Disallow: /*?*_wpnonce=
Disallow: /*?add-to-cart=
Disallow: /*?*add-to-cart=
Disallow: /*?design=
Disallow: /*?*design=
Disallow: /*?s=
Disallow: /*?*s=
Disallow: /*?lang=
Disallow: /*?*lang=
Disallow: /*?filter_*
Disallow: /*?*filter_*

# Block internal/service pages
Disallow: /cart/
Disallow: /checkout/
Disallow: /my-account/
Disallow: /wishlist/
Disallow: /blocks/
Disallow: /feed/
Disallow: /*/feed/

# Yandex Clean-param: merge duplicate URLs, transfer metrics
Clean-param: add_to_wishlist&_wpnonce&add-to-cart&design&s&lang
Clean-param: filter_amplituda&filter_apertura-gentri-sm&filter_ch-o-d-g-v-n-m-p&...
Clean-param: ... (продолжение filter-атрибутов)
```

**Проверить:**
- [ ] Есть Clean-param строки
- [ ] Каждая Clean-param строка < 500 символов
- [ ] Disallow правила на месте
- [ ] Нет `User-agent: Yandex` блока (чтобы не перекрыть `User-agent: *`)

---

## Task 5: Верификация noindex

**Step 1: Проверить noindex для параметров**

```bash
ssh root@85.198.96.28 "curl -s 'https://tdpuls.com/?add_to_wishlist=8685&_wpnonce=test123' | grep -i 'noindex'"
```

**Ожидаемый результат:** `<meta name="robots" content="noindex, follow"/>`

**Step 2: Проверить noindex для фильтров**

```bash
ssh root@85.198.96.28 "curl -s 'https://tdpuls.com/product-category/mrt/?filter_naznachenie=test' | grep -i 'noindex'"
```

**Ожидаемый результат:** `<meta name="robots" content="noindex, ..."/>`

**Step 3: Проверить что обычные страницы НЕ noindex**

```bash
ssh root@85.198.96.28 "curl -s 'https://tdpuls.com/o-nas/' | grep -i 'robots'"
```

**Ожидаемый результат:** `<meta name="robots" content="index, follow, ..."/>` (без noindex)

---

## Task 6: Верификация sitemap

**Step 1: Проверить sitemap_index.xml**

```bash
ssh root@85.198.96.28 "curl -s https://tdpuls.com/sitemap_index.xml"
```

**Ожидаемый результат:** Нет `blocks-sitemap.xml`, есть posts, pages, products, categories.

**Step 2: Проверить page-sitemap.xml**

```bash
ssh root@85.198.96.28 "curl -s https://tdpuls.com/page-sitemap.xml | grep -E 'cart|checkout|my-account|wishlist|/en/'"
```

**Ожидаемый результат:** Пустой вывод (эти страницы убраны из sitemap через noindex мета).

---

## Статус выполнения

**Выполнено 2026-02-10:**

| Task | Статус | Результат |
|------|--------|-----------|
| 1. Собрать filter-атрибуты | Done | 33 атрибута зафиксированы |
| 2. Заменить SEO-код | Done | `functions-new.php:424-543` |
| 3. Деплой | Done | SCP + flush Redis/FastCGI/nginx/php-fpm |
| 4. Верификация robots.txt | Done | Clean-param: 3 строки (63, 426, 351 символов), все < 500 |
| 5. Верификация noindex | Done | Параметры → noindex, фильтры → noindex, обычные страницы → index |
| 6. Верификация sitemap | Done | Нет blocks-sitemap, нет cart/checkout/wishlist в page-sitemap |

**Переобход Яндексом (2026-02-10):**
- robots.txt — отправлен на переобход через Webmaster API
- 23 URL отправлены: главная + 14 корневых категорий + 8 подкатегорий
- Использовано 24/500 квоты (обновляется ежесуточно)

---

## Контрольные точки — снять метрику

- [ ] **2026-02-13 (через 3 дня)** — проверить в Яндекс Вебмастере что robots.txt подхватился (Инструменты → Анализ robots.txt)
- [ ] **2026-02-24 (через 2 недели)** — снять метрику: кол-во исключённых страниц, кол-во страниц в индексе, сравнить с baseline (15 988 дублей, 1754 в индексе, 420 исключено)
- [ ] **2026-03-10 (через месяц)** — финальная оценка: снижение дублей, CTR в Яндексе, позиции по категориям

**Baseline (2026-02-10):**
- ИКС: 20
- В индексе: 1754 страницы
- Исключено: 420
- Неиндексированных URL: ~15 988 (wishlist/nonce дубли, фильтры, ?lang=en)

---

## Что изменить в будущем

При добавлении нового WooCommerce атрибута — добавить его slug в `$tdp_seo_config['filter_attrs']`.

При необходимости закрыть новый параметр — добавить в `$tdp_seo_config['blocked_params']`.

При необходимости закрыть новый путь — добавить в `$tdp_seo_config['blocked_paths']`.

**Всё в одном массиве, одном месте.**
