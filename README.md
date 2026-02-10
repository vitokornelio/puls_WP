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
├── functions-new.php            # → functions.php на сервере (SEO, оптимизации, Bitrix24)
├── header-new.php               # → header.php на сервере (Schema.org)
├── bitrix24-lead-form.php       # Интеграция формы с Битрикс24 CRM + кастомный попап
├── single-product-redesign.php  # Альтернативный шаблон товара (?design=new)
├── page-vsuzi-hub.php           # Страница-хаб ВСУЗИ
├── vsuzi/
│   └── PLAN.md                  # План страницы ВСУЗИ
├── docs/
│   ├── credentials.md           # Доступы (SSH, API, БД) — НЕ в git!
│   ├── commands.md              # Команды обслуживания (устаревшее, для mchost)
│   ├── CHECKLIST.md             # Чеклист для VPS
│   ├── seo-audit-2026-02-07.md  # SEO-аудит (текущий)
│   ├── seo-improvement-plan.md  # План улучшения SEO
│   ├── optimization-results.md  # Результаты оптимизации (архив, mchost)
│   └── plans/                   # Архивные планы разработки
├── AD_context.md                # Контекст для рекламных кампаний
└── .gitignore
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

## Деплой

```bash
# Загрузить файл на сервер
scp functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php

# WP-CLI
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp plugin list --allow-root"

# Очистка кеша
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
```

Подробности: см. memory/deployment.md

---

## Доступы

Все доступы: `docs/credentials.md`

- **WP Admin:** https://tdpuls.com/wp-admin/
- **SSH:** `root@85.198.96.28` (ключ `~/.ssh/id_ed25519`)

---

*Обновлено: 2026-02-09*
