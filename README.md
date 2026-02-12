# tdpuls-wordpress

> WordPress сайт tdpuls.com — медицинское оборудование (Philips, GE, Siemens)

---

## О проекте

| Параметр | Значение |
|----------|----------|
| Домен | tdpuls.com |
| CMS | WordPress 6.9.1 + WooCommerce 10.5.0 |
| Тема | Flatsome 3.13.1 |
| Сервер | Beget VPS (Ubuntu 24.04) |
| Стек | nginx + PHP 8.3-FPM + MariaDB 10.11 + Redis 7.0 |
| PHP | 8.3.30 |

---

## Структура

```
tdpuls-wordpress/
├── theme/                              # → /wp-content/themes/flatsome/
│   ├── functions.php                   # SEO, оптимизации, Bitrix24, кеш
│   ├── header.php                      # Schema.org
│   ├── bitrix24-lead-form.php          # Интеграция формы с Битрикс24 CRM
│   ├── single-product-redesign.php     # Альтернативный шаблон товара (?design=new)
│   ├── page-vsuzi-hub.php              # Страница-хаб ВСУЗИ
│   └── vsuzi-hub.css                   # CSS для ВСУЗИ-хаба
│
├── uploads/                            # → /wp-content/uploads/
│   └── vsuzi/                          # Изображения продуктов (WebP, 800px)
│
├── webroot/                            # → /var/www/tdpuls.com/public/
│   └── yandex_*.html                   # Yandex Webmaster verification
│
├── scripts/                            # НЕ деплоится — утилитные скрипты
│   ├── deploy-vsuzi.sh
│   ├── import-vsuzi-products.sh
│   ├── import-mindray-uzi.sh
│   ├── rename-*.php
│   └── webp-*.php
│
├── tests/                              # НЕ деплоится — Playwright E2E
│   └── tdpuls.spec.ts
├── docs/                               # НЕ деплоится — документация
├── vsuzi/                              # НЕ деплоится — VSUZI проектные docs
│
├── .gitignore
├── CLAUDE.md
├── README.md
├── package.json
└── playwright.config.ts
```

---

## Деплой

### Автоматический (через git)

```bash
git push production main
```

Post-receive hook автоматически:
1. Rsync `theme/` → `/wp-content/themes/flatsome/`
2. Rsync `uploads/` → `/wp-content/uploads/`
3. Rsync `webroot/` → webroot сервера
4. Очищает FastCGI кеш + OPcache

### Ручной (один файл)

```bash
scp theme/functions.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
```

### WP-CLI

```bash
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp plugin list --allow-root"
```

### Очистка кеша

```bash
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
```

---

## Активные плагины (10)

| Плагин | Назначение |
|--------|------------|
| WooCommerce 10.5.0 | E-commerce |
| Rank Math SEO | SEO (title, description, Schema.org) |
| Polylang | Мультиязычность (ru/en, поддомены) |
| Redis Object Cache | Кеширование объектов |
| Max Mega Menu | Мега-меню |
| Classic Editor | Классический редактор |
| Classic Widgets | Классические виджеты |
| Cyr-To-Lat | Транслитерация slug |
| Disable Comments | Отключение комментариев |
| TinyMCE Advanced | Расширенный редактор |

### Интеграции

| Сервис | Назначение |
|--------|------------|
| Битрикс24 CRM | Лиды с формы заявки → crm.lead.add |
| Яндекс Метрика | Аналитика (counter 55564327) |
| Яндекс Вебмастер | Индексация, позиции |

---

## Серверная инфраструктура

| Компонент | Конфигурация |
|-----------|-------------|
| nginx | FastCGI cache 1 ГБ, gzip, WebP content negotiation, rate limiting |
| PHP-FPM | Пул `wordpress`, OPcache 256 МБ, short_open_tag=On |
| MariaDB | InnoDB buffer 2 ГБ, slow query log |
| Redis | 256 МБ, allkeys-lru |
| SSL | Let's Encrypt (tdpuls.com + www + en.tdpuls.com) |
| Безопасность | UFW, SSH по ключам, fail2ban, sysctl hardening |

---

## E2E тесты (Playwright)

```bash
npx playwright test                    # все тесты, все браузеры
npx playwright test --project=chromium # только Chromium
npx playwright test --project=mobile   # только мобильный (iPhone 14)
npx playwright test --ui               # интерактивный UI-режим
npx playwright test --headed           # с видимым браузером
npx playwright show-report             # HTML-отчёт
```

Тесты проверяют:
- Главная страница (заголовок, навигация, «Специальное предложение»)
- Каталог товаров (категория КТ, карточки)
- Страница товара (Philips Access CT, кнопка «Получить КП»)
- ВСУЗИ хаб
- Модалка Битрикс24

---

## Доступы

Все доступы: `docs/credentials.md`

- **WP Admin:** https://tdpuls.com/wp-admin/
- **SSH:** `root@85.198.96.28` (ключ `~/.ssh/id_ed25519`)

---

*Обновлено: 2026-02-12*
