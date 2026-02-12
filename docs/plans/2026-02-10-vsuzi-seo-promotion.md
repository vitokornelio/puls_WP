# ВСУЗИ SEO Promotion — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Разблокировать индексацию страницы /vsuzi/, добавить недостающую SEO-разметку (BreadcrumbList, OG-теги), вынести CSS в отдельный файл и подготовить контентную стратегию для продвижения ВСУЗИ-кластера.

**Architecture:** Все SEO-хуки живут в `functions-new.php` (деплоится как `functions.php` на сервере). Шаблон страницы — `page-vsuzi-hub.php`. CSS выносится в отдельный файл и подключается через `wp_enqueue_style`. OG-теги добавляются через существующие Rank Math фильтры. Деплой — `scp` на VPS `85.198.96.28`.

**Tech Stack:** WordPress 6.9.1, WooCommerce 10.5.0, Rank Math SEO, Flatsome 3.13.1, PHP 8.3, nginx + FastCGI cache

**Аудит:** `docs/seo-audit-vsuzi-2026-02-10.md`

---

## Task 1: Снять noindex со страницы /vsuzi/

**Files:**
- Modify: `functions-new.php:457`

**Step 1: Убрать 'vsuzi' из noindex_pages**

В `functions-new.php:457` изменить:

```php
// Было:
'noindex_pages' => ['vsuzi'],

// Стало:
'noindex_pages' => [],
```

Массив остаётся на месте — его инфраструктура нужна для будущих noindex-страниц. Просто убираем единственный элемент.

**Step 2: Проверить локально**

Убедиться, что в `functions-new.php` фильтр `rank_math/frontend/robots` (строки 512-522) по-прежнему корректен — цикл `foreach` по пустому массиву просто не выполнится.

**Step 3: Деплоить на сервер**

```bash
scp functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
```

**Step 4: Проверить на сервере**

```bash
curl -s -o /dev/null -w "%{http_code}" https://tdpuls.com/vsuzi/
# Ожидаем: 200

curl -s https://tdpuls.com/vsuzi/ | grep -i 'noindex' || echo "OK: noindex отсутствует"
# Ожидаем: "OK: noindex отсутствует"

curl -s https://tdpuls.com/vsuzi/ | grep -i 'canonical'
# Ожидаем: <link rel="canonical" href="https://tdpuls.com/vsuzi/" />
```

**Step 5: Проверить sitemap**

```bash
curl -s https://tdpuls.com/page-sitemap.xml | grep 'vsuzi'
# Ожидаем: <loc>https://tdpuls.com/vsuzi/</loc>
```

Если URL не появился в sitemap — Rank Math может кэшировать. Сбросить кэш:

```bash
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp transient delete --all --allow-root && rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
```

**Step 6: Коммит**

```bash
git add functions-new.php
git commit -m "seo(vsuzi): remove noindex — enable search engine indexing for /vsuzi/ hub page"
```

---

## Task 2: Добавить BreadcrumbList schema

**Files:**
- Modify: `page-vsuzi-hub.php:1120-1163` (секция Schema.org structured data)

**Step 1: Добавить BreadcrumbList JSON-LD**

В `page-vsuzi-hub.php`, после определения `$faq_schema` (строка 1160) и перед закрывающим `?>` (строка 1161), добавить:

```php
$breadcrumb_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Главная',
            'item' => home_url('/'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Интервенционная рентгенология',
            'item' => home_url('/product-category/interventsionnaya-rentgenologiya/'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => 'ВСУЗИ оборудование',
        ],
    ],
];
```

**Step 2: Добавить вывод BreadcrumbList JSON-LD**

Рядом с существующими `<script type="application/ld+json">` блоками (строка 1162-1163), добавить третий:

```php
<script type="application/ld+json"><?php echo json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
```

**Step 3: Деплоить и проверить**

```bash
scp page-vsuzi-hub.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/page-vsuzi-hub.php
```

Проверить:

```bash
curl -s https://tdpuls.com/vsuzi/ | grep -o 'BreadcrumbList'
# Ожидаем: BreadcrumbList
```

Валидировать: https://search.google.com/test/rich-results?url=https://tdpuls.com/vsuzi/

**Step 4: Коммит**

```bash
git add page-vsuzi-hub.php
git commit -m "seo(vsuzi): add BreadcrumbList JSON-LD schema for rich snippets"
```

---

## Task 3: Добавить Open Graph теги для /vsuzi/

**Files:**
- Modify: `functions-new.php:683-695` (секция OG Title/Description фильтров)

OG-теги добавляются через существующие Rank Math фильтры. Паттерн уже задан для `is_front_page()` — добавляем аналогичные проверки для `is_page('vsuzi')`.

**Step 1: Добавить OG Title для vsuzi**

В `functions-new.php`, в фильтре `rank_math/opengraph/facebook/og_title` (строка 683), после проверки `is_front_page()` добавить:

```php
add_filter('rank_math/opengraph/facebook/og_title', function($title) {
    if (is_front_page()) {
        return 'Медицинское оборудование Philips, GE, Siemens — ТД «Пульс»';
    }
    if (is_page('vsuzi')) {
        return 'ВСУЗИ оборудование Philips Volcano — катетеры, проводники, платформы';
    }
    return $title;
});
```

Аналогично для twitter-фильтра (строка 690).

**Step 2: Добавить OG Description для vsuzi**

В фильтрах `rank_math/opengraph/facebook/og_description` (строка 646) и `twitter/og_description` (строка 664), после `is_front_page()`:

```php
if (is_page('vsuzi')) {
    return 'Полная линейка ВСУЗИ оборудования Philips Volcano: катетеры Eagle Eye Platinum, Refinity, проводник OmniWire, платформы IntraSight и Core Mobile. Поставка по РФ.';
}
```

**Step 3: Добавить OG Image для vsuzi**

Добавить новый фильтр (после блока OG Title, ~строка 695):

```php
// OG Image — ВСУЗИ hub
add_filter('rank_math/opengraph/facebook/image', function($image) {
    if (is_page('vsuzi')) {
        return home_url('/wp-content/uploads/vsuzi/og-vsuzi-hub.jpg');
    }
    return $image;
});
```

> **Требуется:** создать OG-изображение `og-vsuzi-hub.jpg` (1200x630px) — коллаж ВСУЗИ оборудования с логотипом ТД Пульс. Загрузить в `/wp-content/uploads/vsuzi/`.

**Step 4: Деплоить и проверить**

```bash
scp functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
```

```bash
curl -s https://tdpuls.com/vsuzi/ | grep 'og:title'
# Ожидаем: content="ВСУЗИ оборудование Philips Volcano..."

curl -s https://tdpuls.com/vsuzi/ | grep 'og:description'
# Ожидаем: content="Полная линейка ВСУЗИ..."

curl -s https://tdpuls.com/vsuzi/ | grep 'og:image'
# Ожидаем: content="https://tdpuls.com/wp-content/uploads/vsuzi/og-vsuzi-hub.jpg"
```

**Step 5: Коммит**

```bash
git add functions-new.php
git commit -m "seo(vsuzi): add Open Graph tags (title, description, image) for social sharing"
```

---

## Task 4: Вынести CSS в отдельный файл

**Files:**
- Create: `vsuzi-hub.css` (новый файл)
- Modify: `page-vsuzi-hub.php:143-751` (удалить инлайн `<style>`)
- Modify: `functions-new.php` (добавить `wp_enqueue_style`)

**Step 1: Извлечь CSS в отдельный файл**

Создать файл `vsuzi-hub.css` — содержимое берётся из `page-vsuzi-hub.php`, строки 144-751 (всё внутри тега `<style>`). Убрать тег `<style>` / `</style>`, оставить только CSS.

**Step 2: Удалить инлайн стили из шаблона**

В `page-vsuzi-hub.php` удалить блок со строки 143 (`<style>`) по строку 751 (`</style>`) включительно.

**Step 3: Подключить CSS через wp_enqueue_style**

В `functions-new.php`, в одном из существующих `wp_enqueue_scripts` хуков (строка 700), или отдельным хуком:

```php
// ВСУЗИ hub CSS
add_action('wp_enqueue_scripts', function() {
    if (is_page('vsuzi')) {
        wp_enqueue_style(
            'vsuzi-hub',
            get_template_directory_uri() . '/vsuzi-hub.css',
            [],
            '1.0.0'
        );
    }
});
```

CSS загружается **только** на странице `/vsuzi/`, не на всём сайте.

**Step 4: Деплоить оба файла**

```bash
scp vsuzi-hub.css root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/vsuzi-hub.css
scp page-vsuzi-hub.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/page-vsuzi-hub.php
scp functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
```

**Step 5: Проверить**

Открыть https://tdpuls.com/vsuzi/ — визуально должно выглядеть идентично. Проверить DevTools Network — `vsuzi-hub.css` загружается отдельным запросом и кэшируется.

```bash
curl -s https://tdpuls.com/vsuzi/ | grep 'vsuzi-hub.css'
# Ожидаем: <link rel='stylesheet' ... href='.../vsuzi-hub.css?ver=1.0.0' ...>
```

**Step 6: Коммит**

```bash
git add vsuzi-hub.css page-vsuzi-hub.php functions-new.php
git commit -m "perf(vsuzi): extract inline CSS to separate cacheable file vsuzi-hub.css"
```

---

## Task 5: Запросить индексацию в поисковых системах

**Ручные действия (не код):**

**Step 1: Яндекс Вебмастер**

1. Открыть https://webmaster.yandex.ru/site/https:tdpuls.com:443/indexing/recheck/
2. Ввести URL: `https://tdpuls.com/vsuzi/`
3. Нажать «Отправить на переобход»

**Step 2: Google Search Console**

1. Открыть https://search.google.com/search-console
2. Ввести URL `https://tdpuls.com/vsuzi/` в инспектор URL
3. Нажать «Запросить индексирование»

**Step 3: Проверить rich results**

1. https://search.google.com/test/rich-results?url=https://tdpuls.com/vsuzi/
2. Ожидаем: FAQPage + BreadcrumbList + CollectionPage — всё валидно

---

## Task 6: Контентная стратегия — информационные статьи (план)

**Это НЕ код — это контент-план для создания статей.**

Статьи создаются как записи WordPress (post type `post`), публикуются в категории «Блог» / «Статьи».

### Приоритет 1 — Информационный якорь (неделя 2)

**Статья: «Что такое ВСУЗИ: полное руководство»**
- URL: `/blog/chto-takoe-vsuzi/`
- Целевые запросы: `всузи` (777/мес), `внутрисосудистое узи` (90/мес), `внутрисосудистый ультразвук` (34/мес)
- Объём: 2500-3500 слов
- Структура:
  - H1: Что такое ВСУЗИ: полное руководство по внутрисосудистому ультразвуку
  - H2: Что такое ВСУЗИ
  - H2: Как работает ВСУЗИ
  - H2: Показания к ВСУЗИ
  - H2: ВСУЗИ при стентировании коронарных артерий
  - H2: ВСУЗИ vs коронарография: в чём разница
  - H2: Оборудование для ВСУЗИ (перелинковка на /vsuzi/)
  - H2: Клинические доказательства эффективности ВСУЗИ
  - H2: FAQ
- Перелинковка: ссылка на `/vsuzi/` (hub), на каждый товар ВСУЗИ, на статью ВСУЗИ vs ОКТ
- Schema: Article + FAQPage

### Приоритет 2 — Сравнительный контент (неделя 3)

**Статья: «ВСУЗИ vs ОКТ: что выбрать для интервенционной кардиологии»**
- URL: `/blog/vsuzi-vs-okt/`
- Целевые запросы: `всузи` (777), `окт коронарных артерий` (12), `оптическая когерентная томография` (3 742 — частичный захват)
- Объём: 2000-3000 слов
- Структура:
  - H1: ВСУЗИ vs ОКТ: что выбрать для интервенционной кардиологии
  - H2: Принцип работы ВСУЗИ
  - H2: Принцип работы ОКТ
  - H2: Сравнительная таблица (расширенная, 15+ параметров)
  - H2: Когда выбрать ВСУЗИ
  - H2: Когда выбрать ОКТ
  - H2: Клинические рекомендации (ESC, ACC/AHA)
  - H2: Оборудование для ВСУЗИ (перелинковка на /vsuzi/)
  - H2: FAQ
- Перелинковка: `/vsuzi/`, `/blog/chto-takoe-vsuzi/`, товарные страницы

### Приоритет 3 — Коммерческий захват (неделя 4)

**Статья: «ВСУЗИ при стентировании коронарных артерий»**
- URL: `/blog/vsuzi-pri-stentirovanii/`
- Целевые запросы: `стентирование коронарных артерий` (2 870/мес) — частичный захват
- Объём: 2000-2500 слов
- Перелинковка: `/vsuzi/`, `/blog/chto-takoe-vsuzi/`, `/blog/vsuzi-vs-okt/`

---

## Task 7: Расширить описания товарных страниц ВСУЗИ

**Это контентная задача — обновление через WP Admin или WooCommerce REST API.**

Текущее состояние: товарные страницы ВСУЗИ содержат ~150 слов.
Цель: расширить до 500-800 слов каждую.

### Товары для расширения (8 шт.):

| Товар | URL | Добавить |
|-------|-----|----------|
| Eagle Eye Platinum | `/shop/.../eagle-eye-platinum.../` | Клиническое применение, совместимость, ChromaFlo, FAQ (3-4 вопроса) |
| Eagle Eye Platinum ST | `/shop/.../eagle-eye-platinum-st.../` | Отличия от обычного EEP, показания (дистальные поражения), FAQ |
| Refinity | `/shop/.../refinity.../` | Преимущества 45 МГц, радиальный доступ, сравнение с EEP, FAQ |
| Reconnaissance PV | `/shop/.../reconnaissance-pv.../` | Периферические применения, отличия от коронарных катетеров, FAQ |
| OmniWire | `/shop/.../omniwire.../` | FFR vs iFR, ко-регистрация, показания к физиологической оценке, FAQ |
| IntraSight | `/shop/.../philips-intrasight.../` | Тройная ко-регистрация, модульность, обучение, FAQ |
| Core Mobile | `/shop/.../core-mobile.../` | Мультимодальность, мобильность, интеграция, FAQ |
| SyncVision | `/shop/.../syncvision.../` | Angio+, iFR Scout, совместимость с платформами, FAQ |

### Шаблон описания для каждого товара:

```
1. Вводный абзац (50-80 слов) — что это, для чего, ключевое преимущество
2. H3: Ключевые характеристики (100-150 слов) — буллет-лист 5-7 пунктов
3. H3: Клиническое применение (100-150 слов) — когда использовать
4. H3: Совместимость (50-80 слов) — с какими платформами/катетерами работает
5. H3: Часто задаваемые вопросы (150-200 слов) — 3-4 Q&A (→ Product FAQ schema)
```

### Перелинковка в описаниях:

- Каждый товар → hub `/vsuzi/` (якорь «Все оборудование ВСУЗИ»)
- Катетеры → платформы IntraSight/Core Mobile (совместимость)
- OmniWire → SyncVision (ко-регистрация)
- Каждый товар → статья `/blog/chto-takoe-vsuzi/` (для информационного контекста)

---

## Порядок выполнения и зависимости

```
Task 1 (noindex) ──→ Task 5 (индексация)
     │
     ├──→ Task 2 (BreadcrumbList)  ── параллельно с ──→ Task 3 (OG-теги)
     │
     └──→ Task 4 (CSS extraction)

Task 6 (статьи) — можно начинать параллельно с Task 1-4
Task 7 (описания товаров) — можно начинать параллельно с Task 1-4
```

**Критический путь:** Task 1 → Task 5 (без снятия noindex всё остальное бесполезно для SEO)

**Оценка:**
- Tasks 1-4: технические, могут быть выполнены за 1 сессию
- Task 5: ручные действия, 10 минут
- Task 6: контент, 3-4 недели (по 1 статье/неделю)
- Task 7: контент, 2-3 недели (по 2-3 товара/неделю)
