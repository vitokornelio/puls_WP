# SEO Changelog — tdpuls.com

> Лог всех SEO-изменений. Используйте даты для корреляции с метриками в METRICS.md.

---

## 2026-02-10

### ВСУЗИ: SEO-продвижение кластера
- **[noindex]** Снят noindex со страницы ВСУЗИ, добавлена в sitemap
- **[structure]** Создана категория товаров `ВСУЗИ` под `Интервенционная рентгенология`
- **[url]** Хаб перенесён с `/vsuzi/` → `/product-category/interventsionnaya-rentgenologiya/vsuzi/`
- **[redirect]** 301: `/vsuzi/` → каталожный URL
- **[schema]** BreadcrumbList (Rank Math auto), CollectionPage, FAQPage — на хаб-странице
- **[og]** OG Title/Description/Image для хаба
- **[css]** Inline CSS вынесен в отдельный кэшируемый `vsuzi-hub.css`
- **[menu]** ВСУЗИ добавлен в меню каталога
- **[products]** 10 товаров перенесены из «Катетеры» → «ВСУЗИ», 4 дубля → черновик + 301
- **[sitemap]** Включён `product_cat-sitemap.xml` в Rank Math

### SEO Control Center
- **[robots.txt]** Единый конфиг `$tdp_seo_config` — Disallow, Clean-param, noindex
- **[noindex]** Фильтр `rank_math/frontend/robots` для параметров и filter-атрибутов
- **[clean-param]** 33 атрибута WooCommerce для Яндекса (intersectional)

---

## 2026-02-09

### Schema.org Enhancement
- **[schema]** Organization: logo, telephone, email, address, contactPoint
- **[schema]** Product: brand (из `pa_proizvoditel`), sku (slug), offers (AggregateOffer/Offer)
- **[schema]** BreadcrumbList для всех товаров: Главная > Категория > Подкатегория > Товар
- **Файл:** `theme/functions.php`, фильтр `rank_math/json_ld` (приоритет 20)

### Аудит и мониторинг
- **[audit]** Полный SEO-аудит: `docs/seo-audit-2026-02-07.md`
- **[metrics]** Снимок статистики: `docs/seo-status-2026-02-09.md`

---

## 2026-02-08

### Оптимизация стека
- **[plugins]** Удалено 7 плагинов (18 → 11), JS 20 → 17, CSS 15 → 9
- **[performance]** scroll-behavior:smooth, defer JS, dashicons убран, font-display:swap
- **[webp]** nginx content negotiation для WebP (PNG → WebP -89%)
- **[cache]** FastCGI cache auto-clear через save_post/edited_term хуки

### Popup Maker → кастомный попап
- **[ux]** Popup Maker удалён, заменён на vanilla JS модалку (1.5 КБ)

### Meta descriptions для категорий
- **[meta]** Все 28 категорий получили SEO-описания через WP REST API
- **[meta]** Фильтр `rank_math/frontend/description` для категорий

---

## 2026-02-07

### Titles и descriptions товаров
- **[title]** Шаблон: `{Модель} — {тип} | Купить в ТД Пульс` (smart title case, сокращения)
- **[meta]** Очистка description от шорткодов Flatsome (`strip_shortcodes` + `preg_replace`)
- **[meta]** Автогенерация description для товаров без текста

### Технические исправления
- **[fix]** Убран дубль meta description на главной
- **[robots]** robots.txt + noindex для параметров-дублей (`?add_to_wishlist`, `?filter_*`, `?_wpnonce`, `?lang=en`)
- **[webmaster]** Верификация в Яндекс Вебмастере

---

## 2026-02-05

### Битрикс24 интеграция
- **[leads]** Форма заявки «Получить КП» → лид в Б24 через webhook
- **[modal]** Модалка брошюры: email/WhatsApp → лид

---

## Формат записи

```
## ГГГГ-ММ-ДД

### Название блока изменений
- **[тег]** Описание изменения
- **Ожидаемый эффект:** что должно измениться в метриках
- **Файлы:** какие файлы затронуты
```

Теги: `[title]` `[meta]` `[schema]` `[robots]` `[redirect]` `[sitemap]` `[noindex]` `[structure]` `[content]` `[performance]` `[og]` `[fix]` `[plugins]` `[webp]` `[cache]` `[leads]` `[modal]` `[menu]` `[products]` `[css]` `[url]` `[audit]` `[metrics]` `[webmaster]` `[ux]`
