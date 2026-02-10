> **АРХИВ — ВЫПОЛНЕН 2026-02-05.** Popup Maker заменён кастомным попапом 08.02.2026.

# Bitrix24 Lead Form Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Заменить FormCraft 3 на лёгкую HTML-форму с прямой отправкой лидов в Битрикс24 CRM.

**Architecture:** Один PHP-файл (`bitrix24-lead-form.php`) подключается в `functions.php` темы Flatsome. Содержит: shortcode для HTML-формы, inline CSS/JS (маска телефона, AJAX), PHP AJAX-обработчик с `wp_remote_post` в Битрикс24 `crm.lead.add`. Кнопка «Получить КП» через JS передаёт название товара в скрытое поле попапа.

**Tech Stack:** WordPress 6.x, PHP (wp_remote_post), Vanilla JS, Bitrix24 REST API (crm.lead.add), FTP-деплой (mchost.ru)

**Credentials:** см. `docs/credentials.md`

---

### Task 1: Бэкап FormCraft

**Цель:** Сохранить плагин FormCraft и данные заявок перед удалением.

**Step 1: Скачать плагин FormCraft через FTP**

```bash
mkdir -p /Users/victorkornilov/WORK/tdpuls-wordpress/backups/formcraft-plugin
curl -s --user "USER:PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/wp-content/plugins/form/" \
  --list-only
```

Скачать всю папку (или архив через FTP). Главное — сохранить конфигурацию формы.

**Step 2: Экспортировать заявки через REST API**

```bash
AUTH=$(echo -n "USER:APP_PASSWORD" | base64)
curl -s -H "Authorization: Basic $AUTH" \
  "https://tdpuls.com/wp-json/wp/v2/pages" | python3 -m json.tool > /dev/null
```

Если REST не даёт доступ к FormCraft submissions — создать временный PHP-скрипт для экспорта в CSV.

**Step 3: Создать SQL-экспорт скрипт**

Файл: `export-formcraft.php` — загрузить на сервер, скачать CSV с 115 заявками, удалить скрипт.

```php
<?php
require_once('wp-load.php');
if ($_GET['key'] !== 'EXPORT_KEY') die('denied');
global $wpdb;
$rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}formcraft_3_submissions ORDER BY id DESC", ARRAY_A);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=formcraft-submissions.csv');
$out = fopen('php://output', 'w');
if ($rows) {
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $row) fputcsv($out, $row);
}
fclose($out);
```

```bash
# Загрузить
curl -T export-formcraft.php --user "USER:PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/export-formcraft.php"

# Скачать CSV
curl -s "https://tdpuls.com/export-formcraft.php?key=EXPORT_KEY" \
  > /Users/victorkornilov/WORK/tdpuls-wordpress/backups/formcraft-submissions.csv

# Удалить скрипт
curl --user "USER:PASS" "ftp://a265896.ftp.mchost.ru" \
  -Q "DELE httpdocs/export-formcraft.php"
```

**Verify:** Файл `backups/formcraft-submissions.csv` содержит строки с заявками.

---

### Task 2: Создать файл интеграции bitrix24-lead-form.php

**Files:**
- Create: `/Users/victorkornilov/WORK/tdpuls-wordpress/bitrix24-lead-form.php`

**Цель:** Один файл с формой, стилями, JS и AJAX-обработчиком.

**Step 1: Создать PHP-файл с конфигурацией и AJAX-обработчиком**

```php
<?php
/**
 * Bitrix24 Lead Form — tdpuls.com
 * Подключается в functions.php: include 'bitrix24-lead-form.php';
 */

define('B24_WEBHOOK_URL', 'https://crm.tdpuls.com/rest/1/<SECRET>/'); // см. credentials.md

/**
 * AJAX handler: отправка лида в Битрикс24
 */
add_action('wp_ajax_b24_submit_lead', 'b24_submit_lead');
add_action('wp_ajax_nopriv_b24_submit_lead', 'b24_submit_lead');

function b24_submit_lead() {
    check_ajax_referer('b24_lead_form', 'nonce');

    $name  = sanitize_text_field($_POST['name'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $product = sanitize_text_field($_POST['product'] ?? '');
    $page_url = esc_url_raw($_POST['page_url'] ?? '');

    // Валидация
    if (empty($name) || empty($phone)) {
        wp_send_json_error('Заполните имя и телефон');
    }

    // Очистка телефона — только цифры и +
    $phone_clean = preg_replace('/[^\d+]/', '', $phone);
    if (strlen($phone_clean) < 11) {
        wp_send_json_error('Некорректный номер телефона');
    }

    // Формируем заголовок лида
    $title = $product ? "КП: {$product}" : 'Заявка с сайта tdpuls.com';

    // Поля лида
    $fields = [
        'TITLE'              => $title,
        'NAME'               => $name,
        'PHONE'              => [['VALUE' => $phone_clean, 'VALUE_TYPE' => 'WORK']],
        'SOURCE_ID'          => 'WEB',
        'SOURCE_DESCRIPTION' => $page_url,
        'OPENED'             => 'Y',
    ];

    if (!empty($email)) {
        $fields['EMAIL'] = [['VALUE' => $email, 'VALUE_TYPE' => 'WORK']];
    }

    // Отправка в Битрикс24
    $response = wp_remote_post(B24_WEBHOOK_URL . 'crm.lead.add.json', [
        'body'    => json_encode(['fields' => $fields, 'params' => ['REGISTER_SONET_EVENT' => 'Y']]),
        'headers' => ['Content-Type' => 'application/json'],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error('Ошибка отправки. Попробуйте позже.');
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($body['result'])) {
        wp_send_json_success('Заявка отправлена!');
    } else {
        wp_send_json_error('Ошибка CRM. Попробуйте позже.');
    }
}
```

**Step 2: Добавить shortcode с HTML-формой и inline CSS/JS**

```php
/**
 * Shortcode [bitrix24_form]
 */
add_shortcode('bitrix24_form', 'b24_render_form');

function b24_render_form() {
    ob_start();
    ?>
    <style>
    .b24-form { max-width: 100%; font-family: inherit; }
    .b24-form .b24-field { margin-bottom: 14px; }
    .b24-form label { display: block; font-size: 13px; color: #555; margin-bottom: 4px; }
    .b24-form input[type="text"],
    .b24-form input[type="tel"],
    .b24-form input[type="email"] {
        width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px;
        font-size: 15px; font-family: inherit; box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .b24-form input:focus { border-color: #00a4e4; outline: none; }
    .b24-form .b24-submit {
        width: 100%; padding: 12px; background: #00a4e4; color: #fff; border: none;
        border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer;
        transition: background 0.2s;
    }
    .b24-form .b24-submit:hover { background: #0090cc; }
    .b24-form .b24-submit:disabled { background: #aaa; cursor: not-allowed; }
    .b24-form .b24-msg { text-align: center; padding: 8px; margin-top: 8px; border-radius: 4px; display: none; }
    .b24-form .b24-msg.success { background: #e8f5e9; color: #2e7d32; display: block; }
    .b24-form .b24-msg.error { background: #ffebee; color: #c62828; display: block; }
    </style>

    <form class="b24-form" id="b24-lead-form" novalidate>
        <div class="b24-field">
            <label for="b24-name">Имя <span style="color:#c62828">*</span></label>
            <input type="text" id="b24-name" name="name" required placeholder="Ваше имя">
        </div>
        <div class="b24-field">
            <label for="b24-phone">Телефон <span style="color:#c62828">*</span></label>
            <input type="tel" id="b24-phone" name="phone" required
                   placeholder="+7 (___) ___-__-__" value="+7 ">
        </div>
        <div class="b24-field">
            <label for="b24-email">Email</label>
            <input type="email" id="b24-email" name="email" placeholder="email@example.com">
        </div>
        <input type="hidden" id="b24-product" name="product" value="">
        <input type="hidden" id="b24-page-url" name="page_url" value="">
        <div class="b24-field">
            <button type="submit" class="b24-submit">Отправить заявку</button>
        </div>
        <div class="b24-msg" id="b24-msg"></div>
    </form>

    <script>
    (function() {
        // Маска телефона: +7 (XXX) XXX-XX-XX
        var phoneInput = document.getElementById('b24-phone');
        phoneInput.addEventListener('input', function(e) {
            var x = this.value.replace(/\D/g, '');
            if (x.length === 0) { this.value = '+7 '; return; }
            if (x[0] === '8') x = '7' + x.substring(1);
            if (x[0] !== '7') x = '7' + x;
            var formatted = '+7';
            if (x.length > 1) formatted += ' (' + x.substring(1, 4);
            if (x.length >= 4) formatted += ') ' + x.substring(4, 7);
            if (x.length >= 7) formatted += '-' + x.substring(7, 9);
            if (x.length >= 9) formatted += '-' + x.substring(9, 11);
            this.value = formatted;
        });
        phoneInput.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value.length <= 3) e.preventDefault();
        });
        phoneInput.addEventListener('focus', function() {
            if (!this.value || this.value === '+7') this.value = '+7 ';
        });

        // Отправка формы
        var form = document.getElementById('b24-lead-form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var msg = document.getElementById('b24-msg');
            var btn = form.querySelector('.b24-submit');
            var name = document.getElementById('b24-name').value.trim();
            var phone = document.getElementById('b24-phone').value;
            var phoneDigits = phone.replace(/\D/g, '');

            msg.className = 'b24-msg';
            msg.style.display = 'none';

            if (!name) { msg.textContent = 'Введите имя'; msg.className = 'b24-msg error'; msg.style.display = 'block'; return; }
            if (phoneDigits.length < 11) { msg.textContent = 'Введите корректный номер телефона'; msg.className = 'b24-msg error'; msg.style.display = 'block'; return; }

            btn.disabled = true;
            btn.textContent = 'Отправка...';

            var data = new FormData();
            data.append('action', 'b24_submit_lead');
            data.append('nonce', '<?php echo wp_create_nonce("b24_lead_form"); ?>');
            data.append('name', name);
            data.append('phone', phone);
            data.append('email', document.getElementById('b24-email').value.trim());
            data.append('product', document.getElementById('b24-product').value);
            data.append('page_url', document.getElementById('b24-page-url').value);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: data
            })
            .then(function(r) { return r.json(); })
            .then(function(r) {
                if (r.success) {
                    msg.textContent = 'Спасибо! Мы свяжемся с вами.';
                    msg.className = 'b24-msg success';
                    msg.style.display = 'block';
                    form.reset();
                    document.getElementById('b24-phone').value = '+7 ';
                    setTimeout(function() {
                        var popup = jQuery('#pum-8502');
                        if (popup.length) PUM.close(popup);
                    }, 2000);
                } else {
                    msg.textContent = r.data || 'Ошибка. Попробуйте позже.';
                    msg.className = 'b24-msg error';
                    msg.style.display = 'block';
                }
            })
            .catch(function() {
                msg.textContent = 'Ошибка сети. Попробуйте позже.';
                msg.className = 'b24-msg error';
                msg.style.display = 'block';
            })
            .finally(function() {
                btn.disabled = false;
                btn.textContent = 'Отправить заявку';
            });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
```

**Step 3: Добавить JS для передачи названия товара в попап**

```php
/**
 * JS: при клике на «Получить КП» — записать название товара в скрытое поле формы
 */
add_action('wp_footer', 'b24_product_capture_js');

function b24_product_capture_js() {
    ?>
    <script>
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('a[href="#popmake-8502"]');
        if (!btn) return;
        var card = btn.closest('.product') || btn.closest('.product-small') || btn.closest('li');
        var title = '';
        var url = window.location.href;
        if (card) {
            var titleEl = card.querySelector('.woocommerce-loop-product__title, .product-title, h2');
            if (titleEl) {
                var link = titleEl.querySelector('a');
                title = (link || titleEl).textContent.trim();
                if (link) url = link.href;
            }
        }
        setTimeout(function() {
            var f = document.getElementById('b24-product');
            var u = document.getElementById('b24-page-url');
            if (f) f.value = title;
            if (u) u.value = url;
        }, 100);
    });
    </script>
    <?php
}
```

**Verify:** Файл `bitrix24-lead-form.php` создан, содержит 3 функции: `b24_submit_lead`, `b24_render_form`, `b24_product_capture_js`.

---

### Task 3: Обновить functions.php — подключить интеграцию

**Files:**
- Modify: `functions-new.php` (локально)
- Deploy: `/httpdocs/wp-content/themes/flatsome/functions.php` (на сервере)

**Step 1: Добавить include в functions.php**

В `functions-new.php` после строки `include 'include/front.php';` добавить:

```php
// Bitrix24 Lead Form
include get_template_directory() . '/bitrix24-lead-form.php';
```

**Step 2: Загрузить файлы на сервер**

```bash
# Загрузить bitrix24-lead-form.php в папку темы
curl -T bitrix24-lead-form.php --user "USER:PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/wp-content/themes/flatsome/bitrix24-lead-form.php"

# Загрузить обновлённый functions.php
curl -T functions-new.php --user "USER:PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/wp-content/themes/flatsome/functions.php"
```

**Verify:** `curl -sI "https://tdpuls.com/"` — сайт отвечает HTTP 200, не сломался.

---

### Task 4: Обновить попап Popup Maker

**Цель:** Заменить содержимое попапа pum-8502 с FormCraft шорткода на новый шорткод.

**Step 1: В WP Admin → Popup Maker → попап "ostavit-zayavku" (ID 8502)**

Заменить содержимое:
- **Было:** `[fc id='2']` (или HTML с FormCraft формой)
- **Стало:** `[bitrix24_form]`

Сохранить попап.

**Verify:** Открыть страницу товара, нажать «Получить КП» — должна появиться новая форма с полями Имя, Телефон, Email.

---

### Task 5: Тестирование интеграции

**Step 1: Проверить маску телефона**

- Открыть попап, кликнуть в поле телефона
- Ожидание: `+7 ` предзаполнено
- Набрать `9001234567`
- Ожидание: отображается `+7 (900) 123-45-67`

**Step 2: Отправить тестовую заявку**

- Имя: `Тест`
- Телефон: `+7 (900) 123-45-67`
- Email: оставить пустым
- Нажать «Отправить заявку»
- Ожидание: сообщение «Спасибо! Мы свяжемся с вами.»

**Step 3: Проверить лид в Битрикс24**

```bash
curl -s -X POST \
  -H 'Content-Type: application/json' \
  "https://crm.tdpuls.com/rest/1/<SECRET>/crm.lead.list.json" \
  -d '{"order":{"ID":"DESC"},"select":["ID","TITLE","NAME","PHONE","EMAIL","SOURCE_ID"],"filter":{">=ID":1},"start":0}' \
  | python3 -m json.tool | head -30
```

Ожидание: лид с TITLE «КП: [название товара]», NAME «Тест», PHONE «79001234567».

**Step 4: Проверить передачу товара**

- Открыть конкретную страницу товара (не главную)
- Нажать «Получить КП»
- Отправить заявку
- В Битрикс24 TITLE лида должен содержать название этого товара

**Step 5: Проверить отправку с email**

- Отправить заявку с email: `test@test.com`
- В Битрикс24 лид должен содержать EMAIL

---

### Task 6: Деактивировать FormCraft

**Step 1: В WP Admin → Плагины → FormCraft → Деактивировать**

Не удалять — просто деактивировать. Бэкап данных уже сделан в Task 1.

**Step 2: Проверить сайт**

```bash
curl -w "TTFB: %{time_starttransfer}s | Total: %{time_total}s\n" -o /dev/null -s "https://tdpuls.com/"
```

Ожидание:
- Сайт работает (HTTP 200)
- FormCraft CSS/JS больше не грузятся
- Форма в попапе работает через новый shortcode

**Step 3: Убедиться что FormCraft CSS/JS не грузятся**

```bash
curl -s "https://tdpuls.com/" | grep -i formcraft
```

Ожидание: пустой вывод (нет упоминаний formcraft).

---

### Task 7: Обновить документацию

**Files:**
- Modify: `docs/credentials.md` — добавить Bitrix24 вебхук
- Modify: `README.md` — обновить список плагинов

**Step 1: Добавить в credentials.md секцию Битрикс24**

```markdown
## Битрикс24 CRM

| Параметр | Значение |
|----------|----------|
| Портал | crm.tdpuls.com |
| Webhook URL | `https://crm.tdpuls.com/rest/1/<SECRET>/` |
| Права | CRM (crm) |
| Метод | crm.lead.add |
```

**Step 2: Обновить README — убрать FormCraft из плагинов, добавить интеграцию**

---

## Порядок выполнения

| Task | Зависимость | Описание |
|------|-------------|----------|
| 1 | — | Бэкап FormCraft |
| 2 | — | Написать bitrix24-lead-form.php |
| 3 | 2 | Подключить в functions.php + деплой на сервер |
| 4 | 3 | Заменить шорткод в попапе (WP Admin) |
| 5 | 4 | Тестирование |
| 6 | 5 | Деактивировать FormCraft |
| 7 | 6 | Обновить документацию |

Tasks 1 и 2 можно выполнять параллельно.
