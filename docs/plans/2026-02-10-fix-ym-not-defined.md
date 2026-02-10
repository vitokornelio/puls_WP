# Fix «ym is not defined» — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Устранить JS-ошибку `ReferenceError: ym is not defined` на всех страницах tdpuls.com.

**Architecture:** Текущий код загружает `tag.js` асинхронно и вызывает `ym()` в `y.onload`, но не создаёт stub-функцию заранее. Если скрипт заблокирован (adblock) или `onload` не сработал — `ym()` бросает ReferenceError. Решение: использовать официальный паттерн Яндекс Метрики, который создаёт stub-очередь ДО загрузки скрипта. Также проверить/подтвердить, что все CTA-ссылки в активных файлах ведут на `#b24-modal`.

**Tech Stack:** PHP (WordPress wp_footer hook), inline JS, Яндекс Метрика tag.js

---

## Анализ проблемы

**Текущий код** (`functions-new.php:1023-1042`):

```javascript
(function(){
    var d=false;
    function l(){
        if(d)return;d=true;
        // ... GA4 ...
        var y=document.createElement('script');
        y.src='https://mc.yandex.ru/metrika/tag.js';
        y.async=true;
        document.head.appendChild(y);
        y.onload=function(){ym(55564327,'init',{...});};
    }
    // triggers: scroll/click/touch/mousemove/keydown + setTimeout 5s
})();
```

**Проблемы:**
1. `ym` не объявлена до загрузки `tag.js` — если скрипт заблокирован adblock'ом, `y.onload` не вызовется, но при любом другом пути вызова `ym()` будет ReferenceError.
2. Нет `y.onerror` — если скрипт не загрузился, ошибка молча проглатывается (ок), но если кто-то вызовет `ym()` из другого места — упадёт.
3. Официальный паттерн Яндекс Метрики создаёт stub-функцию `window.ym` **до** загрузки скрипта. Stub складывает вызовы в очередь `ym.a`, реальный `tag.js` при загрузке подхватывает очередь.

**Официальный паттерн (из документации Яндекс):**

```javascript
(function(m,e,t,r,i,k,a){
    m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();
    // ... создание script-элемента ...
})(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
ym(55564327, "init", { clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true });
```

Ключевое отличие: `m[i]=m[i]||function(){...}` — создаёт `window.ym` как функцию-очередь ещё до подключения скрипта. ReferenceError невозможен.

---

## Проверка CTA-ссылок

Проведена grep-проверка всех активных файлов:

| Файл | Ссылки | Статус |
|---|---|---|
| `functions-new.php:82` | `#b24-modal` | OK |
| `single-product-redesign.php:1094,1109,1223` | `#b24-modal` | OK |
| `page-vsuzi-hub.php:878,1170` | `#b24-modal` | OK |
| `bitrix24-lead-form.php:99,160,165` | `#b24-modal` | OK |

`#popmake-8502` встречается только в `backups/` и `docs/` — в активном коде отсутствует.

---

### Task 1: Заменить deferred-аналитику на безопасный паттерн

**Files:**
- Modify: `functions-new.php:1023-1042`

**Step 1: Заменить блок deferred analytics**

Заменить весь PHP-блок `add_action('wp_footer', function() {...}, 99)` (строки 1023-1042) на:

```php
/**
 * Performance: Deferred Analytics — GA4 + Yandex Metrika
 * Загружаются после взаимодействия пользователя или через 5 секунд
 * Экономит ~100KB из критического пути загрузки
 * YM: stub-очередь создаётся до загрузки tag.js (официальный паттерн) — нет ReferenceError
 */
add_action('wp_footer', function() {
    if (is_admin()) return;
    ?>
    <script>
    (function(){
        var d=false;
        function l(){
            if(d)return;d=true;
            var g=document.createElement('script');g.src='https://www.googletagmanager.com/gtag/js?id=G-XVT53K06KW';g.async=true;document.head.appendChild(g);
            window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}window.gtag=gtag;gtag('js',new Date());gtag('config','G-XVT53K06KW');
            (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();k=e.createElement(t);a=e.getElementsByTagName(t)[0];k.async=1;k.src=r;a.parentNode.insertBefore(k,a)})(window,document,"script","https://mc.yandex.ru/metrika/tag.js","ym");
            ym(55564327,"init",{clickmap:true,trackLinks:true,accurateTrackBounce:true,webvisor:true});
        }
        ['scroll','click','touchstart','mousemove','keydown'].forEach(function(e){document.addEventListener(e,l,{once:true,passive:true});});
        setTimeout(l,5000);
    })();
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/55564327" style="position:absolute;left:-9999px;" alt=""/></div></noscript>
    <?php
}, 99);
```

**Что изменилось:**
- Убран `var y = document.createElement('script'); y.onload = function(){ym(...)}`
- Добавлен официальный stub Яндекса: `m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)}`
- `ym()` вызывается сразу после stub — вызов безопасен даже если `tag.js` ещё не загрузился или заблокирован
- GA4 код не изменён

**Step 2: Проверить синтаксис PHP**

Run: `php -l functions-new.php`
Expected: `No syntax errors detected`

**Step 3: Деплой на сервер**

Run: `scp functions-new.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php`

**Step 4: Сбросить кеш nginx**

Run: `ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"`

**Step 5: Проверить отсутствие ошибки на продакшене**

Открыть в браузере DevTools → Console на каждой из страниц:
- `https://tdpuls.com/`
- `https://tdpuls.com/shop/`
- `https://tdpuls.com/product/access-ct/` (или любая карточка товара)

Expected: **Нет** `ReferenceError: ym is not defined` ни на одной странице.

Дополнительно: прокрутить страницу или кликнуть — через ~1 сек в Network должен появиться запрос к `mc.yandex.ru/metrika/tag.js` и `mc.yandex.ru/watch/55564327`.

**Step 6: Проверить CTA-кнопки**

На каждой из трёх страниц выше кликнуть «Получить КП» — должна открыться модалка Bitrix24.

---

### Task 2: Подтвердить отсутствие #popmake-8502 в активном коде

**Files:**
- Verify: все `*.php` файлы в корне проекта (кроме `backups/`)

**Step 1: Grep по активным файлам**

Run: `grep -rn "popmake-8502\|#popmake" --include="*.php" . --exclude-dir=backups --exclude-dir=docs`
Expected: **Нет совпадений** (пустой вывод)

Если совпадения найдены — заменить `#popmake-8502` на `#b24-modal` в каждом найденном файле.

---

## Чеклист валидации (все пункты должны пройти)

- [ ] `php -l functions-new.php` — No syntax errors
- [ ] Консоль браузера на `tdpuls.com` — нет `ym is not defined`
- [ ] Консоль браузера на `tdpuls.com/shop/` — нет `ym is not defined`
- [ ] Консоль браузера на карточке товара — нет `ym is not defined`
- [ ] «Получить КП» на главной — открывает модалку
- [ ] «Получить КП» в каталоге — открывает модалку
- [ ] «Получить КП» на карточке товара — открывает модалку
- [ ] `grep -rn "popmake" --include="*.php" . --exclude-dir=backups --exclude-dir=docs` — пусто
- [ ] В Network tab видно загрузку `tag.js` после взаимодействия/5 сек
