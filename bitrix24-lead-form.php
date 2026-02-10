<?php
/**
 * Bitrix24 Lead Form — tdpuls.com
 * Кастомный модальный попап (замена Popup Maker)
 * Подключается в functions.php: include 'bitrix24-lead-form.php';
 */

// B24_WEBHOOK_URL задаётся в wp-config.php (вне webroot темы)

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

    if (empty($name) || empty($phone)) {
        wp_send_json_error('Заполните имя и телефон');
    }

    $phone_clean = preg_replace('/[^\d+]/', '', $phone);
    if (strlen($phone_clean) < 11) {
        wp_send_json_error('Некорректный номер телефона');
    }

    $title = $product ? "САЙТ! КП: {$product}" : 'САЙТ! Заявка с tdpuls.com';

    $fields = [
        'TITLE'              => $title,
        'NAME'               => $name,
        'PHONE'              => [['VALUE' => $phone_clean, 'VALUE_TYPE' => 'WORK']],
        'SOURCE_ID'          => 'WEB',
        'SOURCE_DESCRIPTION' => $page_url,
        'OPENED'             => 'Y',
        'ASSIGNED_BY_ID'     => 34,
    ];

    if (!empty($email)) {
        $fields['EMAIL'] = [['VALUE' => $email, 'VALUE_TYPE' => 'WORK']];
    }

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
        $lead_id = $body['result'];

        $contact_parts = [$phone_clean];
        if (!empty($email)) $contact_parts[] = $email;
        $task_desc = "Заявка с сайта tdpuls.com\n\n";
        $task_desc .= "Наименование: " . ($product ?: '(не указано)') . "\n";
        $task_desc .= "Контакт: {$name}, " . implode(', ', $contact_parts) . "\n";
        $task_desc .= "Страница: {$page_url}";

        wp_remote_post(B24_WEBHOOK_URL . 'tasks.task.add.json', [
            'body'    => json_encode(['fields' => [
                'TITLE'          => "Ответить по заявке с сайта: {$name}",
                'DESCRIPTION'    => $task_desc,
                'RESPONSIBLE_ID' => 34,
                'CREATED_BY'     => 1,
                'AUDITORS'       => [1],
                'PRIORITY'       => 2,
                'UF_CRM_TASK'    => ["L_{$lead_id}"],
            ]]),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 15,
        ]);

        wp_send_json_success('Заявка отправлена!');
    } else {
        wp_send_json_error('Ошибка CRM. Попробуйте позже.');
    }
}

/**
 * Модальный попап с формой — выводится в footer на всех страницах
 */
add_action('wp_footer', 'b24_render_modal');

function b24_render_modal() {
    ?>
    <div id="b24-modal" class="b24-overlay" style="display:none">
        <div class="b24-modal">
            <button type="button" class="b24-close" aria-label="Закрыть">&times;</button>
            <form class="b24-form" id="b24-lead-form" novalidate>
                <div class="b24-form-header">
                    <h3>Получить коммерческое предложение</h3>
                    <p>Оставьте контакты — мы подготовим КП в течение 2 часов</p>
                </div>
                <div class="b24-form-body">
                    <div class="b24-field">
                        <label for="b24-name">Имя <span class="b24-req">*</span></label>
                        <input type="text" id="b24-name" name="name" required placeholder="Ваше имя">
                    </div>
                    <div class="b24-field">
                        <label for="b24-phone">Телефон <span class="b24-req">*</span></label>
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
                    <div class="b24-privacy">Нажимая кнопку, вы соглашаетесь на обработку персональных данных</div>
                </div>
            </form>
        </div>
    </div>
    <style>
    .b24-overlay{position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s}
    .b24-overlay.b24-active{opacity:1}
    .b24-modal{position:relative;max-width:480px;width:90vw;border-radius:16px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.15),0 8px 20px rgba(0,0,0,.08);transform:translateY(20px) scale(.97);transition:transform .25s}
    .b24-overlay.b24-active .b24-modal{transform:translateY(0) scale(1)}
    .b24-close{position:absolute;top:12px;right:14px;z-index:10;background:none;border:none;color:#fff;font-size:28px;width:36px;height:36px;line-height:36px;border-radius:50%;cursor:pointer;transition:all .2s;opacity:.7}
    .b24-close:hover{background:rgba(255,255,255,.2);opacity:1}
    .b24-form{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
    .b24-form-header{background:linear-gradient(135deg,#00a4e4 0%,#0080c0 100%);padding:28px 32px 24px;color:#fff}
    .b24-form-header h3{margin:0 0 6px;font-size:20px;font-weight:700;line-height:1.3}
    .b24-form-header p{margin:0;font-size:14px;opacity:.85;line-height:1.4}
    .b24-form-body{padding:24px 32px 28px;background:#fff}
    .b24-form .b24-field{margin-bottom:16px}
    .b24-form label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
    .b24-form label .b24-req{color:#ef4444}
    .b24-form input[type="text"],.b24-form input[type="tel"],.b24-form input[type="email"]{width:100%;padding:12px 14px;border:1.5px solid #d1d5db;border-radius:10px;font-size:15px;font-family:inherit;box-sizing:border-box;transition:all .2s;background:#f9fafb;color:#1f2937}
    .b24-form input::placeholder{color:#9ca3af}
    .b24-form input:focus{border-color:#00a4e4;outline:none;background:#fff;box-shadow:0 0 0 3px rgba(0,164,228,.1)}
    .b24-form .b24-submit{width:100%;padding:14px;background:#00a4e4;color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer;transition:all .2s;letter-spacing:.3px}
    .b24-form .b24-submit:hover{background:#0090cc;transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,164,228,.3)}
    .b24-form .b24-submit:disabled{background:#9ca3af;cursor:not-allowed;transform:none;box-shadow:none}
    .b24-form .b24-privacy{text-align:center;font-size:11px;color:#9ca3af;margin-top:12px;line-height:1.4}
    .b24-form .b24-msg{text-align:center;padding:10px 14px;margin-top:12px;border-radius:10px;display:none;font-size:14px}
    .b24-form .b24-msg.success{background:#ecfdf5;color:#065f46;display:block;border:1px solid #a7f3d0}
    .b24-form .b24-msg.error{background:#fef2f2;color:#991b1b;display:block;border:1px solid #fecaca}
    </style>
    <script>
    (function(){
        var overlay = document.getElementById('b24-modal');
        if (!overlay) return;

        // Открытие модалки по клику на любую ссылку с href="#b24-modal"
        document.addEventListener('click', function(e) {
            var trigger = e.target.closest('a[href="#b24-modal"]');
            if (!trigger) return;
            e.preventDefault();

            // Захват названия товара
            var card = trigger.closest('.product') || trigger.closest('.product-small') || trigger.closest('li');
            var title = '', url = window.location.href;
            if (card) {
                var titleEl = card.querySelector('.woocommerce-loop-product__title, .product-title, h2');
                if (titleEl) {
                    var link = titleEl.querySelector('a');
                    title = (link || titleEl).textContent.trim();
                    if (link) url = link.href;
                }
            }
            // На странице товара — берём заголовок из h1
            var h1 = document.querySelector('.product_title, h1.entry-title');
            if (!title && h1) title = h1.textContent.trim();

            var f = document.getElementById('b24-product');
            var u = document.getElementById('b24-page-url');
            if (f) f.value = title;
            if (u) u.value = url;

            // Показ модалки
            overlay.style.display = 'flex';
            requestAnimationFrame(function(){ overlay.classList.add('b24-active'); });
            document.body.style.overflow = 'hidden';
        });

        // Закрытие
        function closeModal() {
            overlay.classList.remove('b24-active');
            setTimeout(function(){ overlay.style.display = 'none'; }, 200);
            document.body.style.overflow = '';
        }
        overlay.querySelector('.b24-close').addEventListener('click', closeModal);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('b24-active')) closeModal();
        });

        // Маска телефона
        var phoneInput = document.getElementById('b24-phone');
        phoneInput.addEventListener('input', function() {
            var x = this.value.replace(/\D/g, '');
            if (x.length === 0) { this.value = '+7 '; return; }
            if (x[0] === '8') x = '7' + x.substring(1);
            if (x[0] !== '7') x = '7' + x;
            var f = '+7';
            if (x.length > 1) f += ' (' + x.substring(1, 4);
            if (x.length >= 4) f += ') ' + x.substring(4, 7);
            if (x.length >= 7) f += '-' + x.substring(7, 9);
            if (x.length >= 9) f += '-' + x.substring(9, 11);
            this.value = f;
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

            msg.className = 'b24-msg'; msg.style.display = 'none';

            if (!name) { msg.textContent = 'Введите имя'; msg.className = 'b24-msg error'; msg.style.display = 'block'; return; }
            if (phoneDigits.length < 11) { msg.textContent = 'Введите корректный номер телефона'; msg.className = 'b24-msg error'; msg.style.display = 'block'; return; }

            btn.disabled = true; btn.textContent = 'Отправка...';

            var data = new FormData();
            data.append('action', 'b24_submit_lead');
            data.append('nonce', '<?php echo wp_create_nonce("b24_lead_form"); ?>');
            data.append('name', name);
            data.append('phone', phone);
            data.append('email', document.getElementById('b24-email').value.trim());
            data.append('product', document.getElementById('b24-product').value);
            data.append('page_url', document.getElementById('b24-page-url').value);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: data })
            .then(function(r) { return r.json(); })
            .then(function(r) {
                if (r.success) {
                    msg.textContent = 'Спасибо! Мы свяжемся с вами.';
                    msg.className = 'b24-msg success'; msg.style.display = 'block';
                    form.reset();
                    document.getElementById('b24-phone').value = '+7 ';
                    setTimeout(closeModal, 2000);
                } else {
                    msg.textContent = r.data || 'Ошибка. Попробуйте позже.';
                    msg.className = 'b24-msg error'; msg.style.display = 'block';
                }
            })
            .catch(function() {
                msg.textContent = 'Ошибка сети. Попробуйте позже.';
                msg.className = 'b24-msg error'; msg.style.display = 'block';
            })
            .finally(function() {
                btn.disabled = false; btn.textContent = 'Отправить заявку';
            });
        });
    })();
    </script>
    <?php
}
