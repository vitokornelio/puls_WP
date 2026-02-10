# Stack Optimization Implementation Plan

> **Status: ВЫПОЛНЕН 2026-02-08**

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Привести WordPress-плагины и серверную конфигурацию в соответствие с новым VPS-стеком (nginx + Redis + PHP 8.1), убрать дублирование, ускорить загрузку страниц.

**Architecture:** Удаляем 7 ненужных плагинов (включая WP Fastest Cache — дублирует nginx FastCGI cache). Добавляем nginx-level WebP content negotiation. Оптимизируем загрузку JS/CSS через хуки в `functions-new.php`. Чистим `.htaccess` от Apache-правил.

**Tech Stack:** nginx 1.24, PHP 8.1-FPM, Redis 7.0, WordPress 6.9.1, WooCommerce 10.5.0, Flatsome 3.13.1

---

## Предусловия

- **SSH:** `root@85.198.96.28` (ключ `~/.ssh/id_ed25519`)
- **Webroot:** `/var/www/tdpuls.com/public/`
- **Тема:** `/var/www/tdpuls.com/public/wp-content/themes/flatsome/`
- **WP-CLI:** `ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp <command> --allow-root"`
- **Деплой functions-new.php:** `scp functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php`
- **Локальный файл:** `/Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php`

### Замечания по текущему коду

`functions-new.php` уже содержит:
- WebP output buffer замену (строки 639-665) — `tdp_webp_replace_images()` через `ob_start`
- Деоптимизацию CSS: убирает `wp-block-editor`, `wp-components`, `wp-preferences`, `wc-blocks-style` (строки 603-621)
- Убирает IE fallbacks Flatsome (строка 627)
- Убирает Global Styles CSS (строки 632-633)

**Важно:** WebP через output buffer (PHP) менее эффективен, чем nginx `try_files`. После настройки nginx WebP — удалить PHP-буфер.

---

### Task 1: Замер baseline-метрик

**Files:** нет изменений

**Step 1: Записать текущие метрики**

```bash
# TTFB (3 замера)
for i in 1 2 3; do curl -s -o /dev/null -w "Run $i: %{time_starttransfer}s\n" "https://tdpuls.com/"; done

# Размер сжатой страницы
curl -s -H "Accept-Encoding: gzip" -w "Compressed: %{size_download} bytes\n" -o /dev/null "https://tdpuls.com/"

# PNG vs WebP
curl -sI "https://tdpuls.com/wp-content/uploads/2021/02/partnergoda.png" | grep -i content-length

# Количество ресурсов
curl -s https://tdpuls.com/ | grep -c '<script.*src='
curl -s https://tdpuls.com/ | grep -c '<link.*\.css'

# Список активных плагинов
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp plugin list --status=active --field=name --allow-root"
```

Expected: TTFB ~0.4-0.5s, compressed ~28KB, PNG 885136 bytes, ~20 JS, ~15 CSS, 18 плагинов.

Сохранить вывод для сравнения в конце.

---

### Task 2: Деактивировать 6 бесполезных плагинов

**Files:**
- Нет файловых изменений (только WP-CLI команды на сервере)

**Step 1: Деактивировать плагины**

```bash
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp plugin deactivate \
  php-compatibility-checker \
  export-categories \
  wordpress-importer \
  mousewheel-smooth-scroll \
  visual-link-preview \
  yikes-inc-easy-custom-woocommerce-product-tabs \
  --allow-root"
```

Expected:
```
Plugin 'php-compatibility-checker' deactivated.
Plugin 'export-categories' deactivated.
Plugin 'wordpress-importer' deactivated.
Plugin 'mousewheel-smooth-scroll' deactivated.
Plugin 'visual-link-preview' deactivated.
Plugin 'yikes-inc-easy-custom-woocommerce-product-tabs' deactivated.
Success: Deactivated 6 of 6 plugins.
```

**Step 2: Проверить сайт**

```bash
curl -sI https://tdpuls.com/ | head -5
```

Expected: `HTTP/1.1 200 OK`

**Step 3: Проверить, что JS mousewheel-smooth-scroll пропал**

```bash
curl -s https://tdpuls.com/ | grep -c 'mousewheel-smooth-scroll\|SmoothScroll\|wpmss'
```

Expected: `0`

**Step 4: Проверить, что visual-link-preview CSS пропал**

```bash
curl -s https://tdpuls.com/ | grep -c 'visual-link-preview'
```

Expected: `0`

---

### Task 3: Добавить scroll-behavior + defer JS + dashicons + font-display в functions-new.php

**Files:**
- Modify: `/Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php` (после строки 633, перед WebP-секцией на строке 638)

**Step 1: Добавить блок оптимизаций перед WebP-секцией**

Вставить после строки 633 (`remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');`):

```php
/**
 * Performance: scroll-behavior smooth (замена плагина mousewheel-smooth-scroll)
 */
add_action('wp_head', function() {
    echo '<style>html{scroll-behavior:smooth}</style>';
}, 1);

/**
 * Performance: defer всех JS кроме jQuery
 */
add_filter('script_loader_tag', function($tag, $handle) {
    $no_defer = ['jquery-core', 'jquery-migrate'];
    if (is_admin() || in_array($handle, $no_defer)) return $tag;
    if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) return $tag;
    return str_replace(' src', ' defer src', $tag);
}, 10, 2);

/**
 * Performance: убрать dashicons.css с фронтенда (58 KB, нужен только в админке)
 */
add_action('wp_enqueue_scripts', function() {
    if (!is_admin_bar_showing()) {
        wp_dequeue_style('dashicons');
    }
}, 100);

/**
 * Performance: font-display:swap для Google Fonts (убирает FOIT)
 */
add_filter('style_loader_tag', function($tag, $handle) {
    if (strpos($tag, 'fonts.googleapis.com') !== false && strpos($tag, 'display=') === false) {
        $tag = str_replace('family=', 'display=swap&family=', $tag);
    }
    return $tag;
}, 10, 2);
```

**Step 2: Деплой на сервер**

```bash
scp /Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
```

**Step 3: Очистить nginx cache и проверить**

```bash
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
```

**Step 4: Проверить scroll-behavior**

```bash
curl -s https://tdpuls.com/ | grep 'scroll-behavior'
```

Expected: `<style>html{scroll-behavior:smooth}</style>`

**Step 5: Проверить defer на скриптах**

```bash
curl -s https://tdpuls.com/ | grep -oP '<script[^>]+src[^>]+>' | head -10
```

Expected: jQuery БЕЗ defer, остальные С defer.

**Step 6: Проверить отсутствие dashicons**

```bash
curl -s https://tdpuls.com/ | grep -c 'dashicons'
```

Expected: `0` (если админбар не показывается для анонимных)

**Step 7: Проверить font-display в Google Fonts**

```bash
curl -s https://tdpuls.com/ | grep -o 'fonts.googleapis.com[^"]*'
```

Expected: URL содержит `display=swap`

**Step 8: Проверить что сайт работает (навигация, попапы)**

```bash
curl -sI https://tdpuls.com/ | head -3
curl -sI "https://tdpuls.com/product/uzi-apparat-philips-epiq-7/" | head -3
curl -sI "https://tdpuls.com/product-category/uzi-apparaty/" | head -3
```

Expected: все `HTTP/1.1 200 OK`

---

### Task 4: Деактивировать WP Fastest Cache + хук очистки nginx cache

**Files:**
- Modify: `/Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php`

**Step 1: Деактивировать WPFC**

```bash
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp plugin deactivate wp-fastest-cache --allow-root"
```

Expected: `Plugin 'wp-fastest-cache' deactivated.`

**Step 2: Удалить кэш WPFC**

```bash
ssh root@85.198.96.28 "rm -rf /var/www/tdpuls.com/public/wp-content/cache/all/ /var/www/tdpuls.com/public/wp-content/cache/wpfc-minified/"
```

**Step 3: Добавить хук очистки nginx cache в functions-new.php**

Вставить после блока `remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');` (строка 633), перед блоком scroll-behavior:

```php
/**
 * Performance: автоочистка nginx FastCGI cache при обновлении контента
 * Заменяет кнопку "Clear Cache" из WP Fastest Cache
 */
add_action('save_post', function($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if (is_dir('/var/cache/nginx/fastcgi')) {
        exec('rm -rf /var/cache/nginx/fastcgi/*');
    }
}, 99);

// Также очищаем при обновлении term (категорий, тегов)
add_action('edited_term', function() {
    if (is_dir('/var/cache/nginx/fastcgi')) {
        exec('rm -rf /var/cache/nginx/fastcgi/*');
    }
}, 99);

// Кнопка "Очистить кэш" в админ-баре
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!current_user_can('manage_options')) return;
    $wp_admin_bar->add_node([
        'id' => 'tdp-clear-cache',
        'title' => 'Очистить кэш',
        'href' => wp_nonce_url(admin_url('admin-post.php?action=tdp_clear_cache'), 'tdp_clear_cache'),
    ]);
}, 100);

add_action('admin_post_tdp_clear_cache', function() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'], 'tdp_clear_cache')) {
        wp_die('Доступ запрещён');
    }
    exec('rm -rf /var/cache/nginx/fastcgi/*');
    wp_safe_redirect(wp_get_referer() ?: admin_url());
    exit;
});
```

**Step 4: Деплой**

```bash
scp /Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
```

**Step 5: Очистить кэш и проверить**

```bash
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
```

**Step 6: Проверить что сайт работает без WPFC**

```bash
curl -sI https://tdpuls.com/ | head -5
```

Expected: `HTTP/1.1 200 OK`. Заголовок `X-FastCGI-Cache: MISS` (первый запрос), затем `HIT`.

**Step 7: Проверить что минификация WPFC не влияет**

```bash
curl -s https://tdpuls.com/ | grep -c 'wpfc-minified'
```

Expected: `0` (WPFC минифицированные файлы больше не подключаются)

---

### Task 5: Настроить nginx WebP content negotiation + удалить PHP WebP buffer

**Files:**
- Modify: `/etc/nginx/nginx.conf` (на сервере, http блок)
- Modify: `/etc/nginx/sites-available/tdpuls.com` (на сервере)
- Modify: `/Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php` (удалить PHP WebP buffer)

**Step 1: Добавить map в nginx.conf**

```bash
ssh root@85.198.96.28 "cat /etc/nginx/nginx.conf"
```

Добавить в http блок, перед `include /etc/nginx/conf.d/*.conf;`:

```bash
ssh root@85.198.96.28 "sed -i '/include \/etc\/nginx\/conf.d\/\*\.conf;/i\\
    # WebP content negotiation\\
    map \$http_accept \$webp_suffix {\\
        default \"\";\\
        \"~*webp\" \".webp\";\\
    }' /etc/nginx/nginx.conf"
```

Проверить:

```bash
ssh root@85.198.96.28 "grep -A3 'webp_suffix' /etc/nginx/nginx.conf"
```

Expected: map блок с `$webp_suffix`

**Step 2: Разделить location для статики в site config**

Заменить текущий единый location:
```nginx
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
    expires 30d;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}
```

На два location:

```bash
ssh root@85.198.96.28 "cat > /tmp/nginx-static-patch.py << 'PYEOF'
import re

with open('/etc/nginx/sites-available/tdpuls.com', 'r') as f:
    content = f.read()

old = '''    # Static files
    location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)\$ {
        expires 30d;
        add_header Cache-Control \"public, immutable\";
        access_log off;
        try_files \$uri =404;
    }'''

new = '''    # Static files: images with WebP content negotiation
    location ~* \\.(png|jpg|jpeg|gif)\$ {
        expires 30d;
        add_header Cache-Control \"public, immutable\";
        add_header Vary Accept;
        access_log off;
        try_files \$uri\$webp_suffix \$uri =404;
    }

    # Static files: non-image assets (no WebP negotiation)
    location ~* \\.(js|css|ico|svg|webp|woff|woff2|ttf|eot)\$ {
        expires 30d;
        add_header Cache-Control \"public, immutable\";
        access_log off;
        try_files \$uri =404;
    }'''

content = content.replace(old, new)

with open('/etc/nginx/sites-available/tdpuls.com', 'w') as f:
    f.write(content)

print('OK')
PYEOF
python3 /tmp/nginx-static-patch.py && rm /tmp/nginx-static-patch.py"
```

**Step 3: Проверить конфиг nginx**

```bash
ssh root@85.198.96.28 "nginx -t"
```

Expected: `syntax is ok`, `test is successful`

**Step 4: Перезагрузить nginx**

```bash
ssh root@85.198.96.28 "systemctl reload nginx"
```

**Step 5: Проверить WebP отдачу**

```bash
# С Accept: webp → должен вернуть image/webp
curl -sI -H "Accept: image/webp,image/*" "https://tdpuls.com/wp-content/uploads/2021/02/partnergoda.png" | grep -iE 'content-type|content-length'

# Без Accept: webp → должен вернуть image/png
curl -sI -H "Accept: image/*" "https://tdpuls.com/wp-content/uploads/2021/02/partnergoda.png" | grep -iE 'content-type|content-length'
```

Expected:
- С webp: `Content-Type: image/webp`, `Content-Length: 93332` (93 KB)
- Без webp: `Content-Type: image/png`, `Content-Length: 885136` (885 KB)

**Step 6: Проверить Vary заголовок**

```bash
curl -sI "https://tdpuls.com/wp-content/uploads/2021/02/partnergoda.png" | grep -i vary
```

Expected: `Vary: Accept`

**Step 7: Удалить PHP WebP buffer из functions-new.php**

Удалить строки 638-665 (блок `WebP: автоподмена JPEG/PNG → WebP в HTML`):

```php
// УДАЛИТЬ ВСЁ ЭТО:
/**
 * WebP: автоподмена JPEG/PNG → WebP в HTML
 * Nginx не поддерживает mod_rewrite, поэтому используем output buffer
 */
add_action('template_redirect', function() { ... });
function tdp_webp_replace_images($html) { ... }
```

Nginx WebP content negotiation делает это эффективнее — без PHP overhead, без буферизации всего HTML.

**Step 8: Деплой обновлённого functions-new.php**

```bash
scp /Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
```

**Step 9: Очистить кэш и финальная проверка**

```bash
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
curl -sI https://tdpuls.com/ | head -5
```

Expected: `HTTP/1.1 200 OK`

---

### Task 6: Почистить .htaccess от Apache-правил

**Files:**
- Modify: `/var/www/tdpuls.com/public/.htaccess` (на сервере)

**Step 1: Сделать бэкап**

```bash
ssh root@85.198.96.28 "cp /var/www/tdpuls.com/public/.htaccess /var/www/tdpuls.com/public/.htaccess.bak-$(date +%Y%m%d)"
```

**Step 2: Оставить только WordPress rewrite и редиректы**

Содержимое нового `.htaccess`:

```apache
# BEGIN WordPress
# Директивы (строки) между `BEGIN WordPress` и `END WordPress`
# созданы автоматически и подлежат изменению только через фильтры WordPress.
# Сделанные вручную изменения между этими маркерами будут перезаписаны.
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>

# END WordPress

# Redirects
Redirect 301 /privacy-policy/ https://tdpuls.com/wp-content/uploads/policy.pdf

Redirect 301 /tag/urologiya/ https://tdpuls.com/info/
Redirect 301 /tag/prazdnik/ https://tdpuls.com/info/
Redirect 301 /tag/philips/ https://tdpuls.com/info/
Redirect 301 /tag/pediatriya/ https://tdpuls.com/info/
Redirect 301 /tag/partnery/ https://tdpuls.com/info/
Redirect 301 /tag/onkologiya/ https://tdpuls.com/info/
Redirect 301 /tag/novoe-oborudovanie/ https://tdpuls.com/info/
Redirect 301 /tag/nagrada/ https://tdpuls.com/info/
Redirect 301 /tag/lumify/ https://tdpuls.com/info/
Redirect 301 /tag/luchevaya-diagnostika/ https://tdpuls.com/info/
Redirect 301 /tag/kardiologiya/ https://tdpuls.com/info/
Redirect 301 /tag/ivl/ https://tdpuls.com/info/
Redirect 301 /tag/informatsionnaya-sistema/ https://tdpuls.com/info/
Redirect 301 /tag/ginekologiya/ https://tdpuls.com/info/
Redirect 301 /tag/epiq/ https://tdpuls.com/info/
Redirect 301 /tag/covid-19/ https://tdpuls.com/info/
Redirect 301 /tag/azurion/ https://tdpuls.com/info/
Redirect 301 /tag/angiografiya/ https://tdpuls.com/info/
Redirect 301 /tag/akusherstvo/ https://tdpuls.com/info/
```

Удалено: `mod_expires`, `mod_deflate`, `mod_headers`, `FileETag None` — всё это уже настроено в nginx.

```bash
ssh root@85.198.96.28 "cat > /var/www/tdpuls.com/public/.htaccess << 'HTEOF'
<содержимое выше>
HTEOF"
```

**Step 3: Проверить что редиректы работают**

```bash
curl -sI "https://tdpuls.com/tag/philips/" | head -3
```

Expected: `HTTP/1.1 301 Moved Permanently` → `Location: https://tdpuls.com/info/`

Примечание: эти редиректы через .htaccess на nginx работают через WordPress (fallback на index.php). Если нужна производительность — перенести в nginx location блоки. Но это не критично (редкие URL).

---

### Task 7: Финальный замер и сравнение

**Files:** нет изменений

**Step 1: Замер после всех оптимизаций**

```bash
# Очистить кэш перед замером
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/*"

# TTFB
for i in 1 2 3; do curl -s -o /dev/null -w "Run $i: %{time_starttransfer}s\n" "https://tdpuls.com/"; done

# Размер сжатой страницы
curl -s -H "Accept-Encoding: gzip" -w "Compressed: %{size_download} bytes\n" -o /dev/null "https://tdpuls.com/"

# WebP работает?
curl -sI -H "Accept: image/webp,image/*" "https://tdpuls.com/wp-content/uploads/2021/02/partnergoda.png" | grep -iE 'content-type|content-length'

# Количество ресурсов
curl -s https://tdpuls.com/ | grep -c '<script.*src='
curl -s https://tdpuls.com/ | grep -c '<link.*\.css'

# Активные плагины
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp plugin list --status=active --field=name --allow-root"
```

**Step 2: Сравнить с baseline**

| Метрика | До | После | Изменение |
|---|---|---|---|
| TTFB | ~0.48s | ? | ? |
| Compressed page | 28453 bytes | ? | ? |
| JS файлов | ~20 | ? | ? |
| CSS файлов | ~15 | ? | ? |
| Активных плагинов | 18 | 11 | -7 |
| WebP image (partnergoda) | 885 KB (PNG) | 93 KB (WebP) | -89% |

**Step 3: Полная проверка работоспособности**

```bash
# Главная
curl -sI https://tdpuls.com/ | head -3

# Каталог
curl -sI "https://tdpuls.com/shop/" | head -3

# Категория
curl -sI "https://tdpuls.com/product-category/uzi-apparaty/" | head -3

# Товар
curl -sI "https://tdpuls.com/product/uzi-apparat-philips-epiq-7/" | head -3

# Английская версия
curl -sI "https://en.tdpuls.com/" | head -3

# Попап (AJAX)
curl -s -X POST "https://tdpuls.com/wp-admin/admin-ajax.php" -d "action=b24_submit_lead&name=test&phone=test" | head -5
```

Expected: все `200 OK`, AJAX возвращает JSON.

---

## Итог: что получаем

**Удалённые плагины (7):**
1. php-compatibility-checker
2. export-categories
3. wordpress-importer
4. mousewheel-smooth-scroll
5. visual-link-preview
6. yikes-inc-easy-custom-woocommerce-product-tabs
7. wp-fastest-cache

**Оставшиеся активные плагины (11):**
1. polylang — мультиязычность
2. classic-editor — редактор
3. classic-widgets — виджеты
4. cyr3lat — транслитерация URL
5. disable-comments — отключение комментариев
6. megamenu — навигация
7. popup-maker — формы Б24
8. seo-by-rank-math — SEO
9. redis-cache — object cache
10. tinymce-advanced — расширение редактора
11. woocommerce — каталог

**Серверные оптимизации:**
- nginx WebP content negotiation (без PHP overhead)
- JS defer (кроме jQuery)
- dashicons.css убран с фронтенда
- font-display: swap для Google Fonts
- scroll-behavior: smooth (замена плагина)
- nginx cache auto-clear на save_post + кнопка в админ-баре
- .htaccess очищен от Apache-правил

## Риски и откат

- **Откат плагинов:** `wp plugin activate <name> --allow-root`
- **Откат nginx:** `cp /etc/nginx/nginx.conf.bak /etc/nginx/nginx.conf && systemctl reload nginx`
- **Откат .htaccess:** `cp .htaccess.bak-YYYYMMDD .htaccess`
- **Откат functions.php:** предыдущая версия в git или `scp` оригинал
- **Если defer ломает меню:** добавить `'mega-menu-main-menu'` в массив `$no_defer`

---

## Результаты выполнения (2026-02-08)

| Метрика | До | После | Изменение |
|---|---|---|---|
| TTFB (avg, warm cache) | ~0.43s | ~0.42s | ~= |
| Compressed page | 28,453 bytes | 30,598 bytes | +7.5% (добавились inline хуки) |
| JS файлов | 20 | 17 | **-15%** |
| CSS файлов | 15 | 9 | **-40%** |
| Активных плагинов | 18 | 11 | **-39%** |
| WebP (partnergoda.png) | 885 KB (PNG) | 93 KB (WebP) | **-89%** |

### Отклонения от плана
1. **WebP формат файлов:** WebP лежат как `image.webp` (не `image.png.webp`). Потребовался другой подход к nginx map — `map $webp_accept$uri $webp_rewrite` с regex для замены расширения.
2. **mousewheel-smooth-scroll:** Скрипты оставались после деактивации плагина (видимо зарегистрированы через cron/transient). Добавлен `wp_dequeue_script`/`wp_deregister_script` для 3 хэндлов.
3. **WP Fastest Cache:** Уже был удалён с диска ранее, но WPFC file cache (`wp-content/cache/all/`) всё ещё существовал — удалён.
4. **.htaccess redirects:** `Redirect 301` из .htaccess не работают на nginx. Перенесены в nginx location blocks — теперь реально работают (301).
5. **WPFC l10n warnings:** WordPress пытается загрузить l10n файлы удалённого WPFC — безвредные warnings, уйдут после очистки update transients.
6. **Тестовые URL в плане:** `/product/uzi-apparat-philips-epiq-7/` и `/product-category/uzi-apparaty/` не существуют — заменены на реальные URL при проверке.
