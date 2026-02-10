# P0 Fix Briefing (tdpuls-wordpress)

## Objective

Fix P0 issues on production:

1. JavaScript runtime error: `ReferenceError: ym is not defined`.
2. Broken CTA target: some `Получить КП` links point to `#popmake-8502` while the active modal target is `#b24-modal`.

This document is for a second AI assistant that will implement and verify the fix.

## Project Architecture (only what matters for P0)

- Theme entrypoint logic is in `functions-new.php` (this file is deployed as server `functions.php` according to README).
- Bitrix modal form is implemented in `bitrix24-lead-form.php` and included from `functions-new.php`.
- Product/listing CTA links are generated from theme templates/PHP overrides and should target `#b24-modal`.

### Runtime flow for CTA

1. A page renders `a[href="#b24-modal"]` trigger links.
2. `bitrix24-lead-form.php` renders modal HTML (`id="b24-modal"`) in `wp_footer`.
3. JS in `bitrix24-lead-form.php` listens for clicks on `a[href="#b24-modal"]` and opens the modal.

If any trigger points to `#popmake-8502`, modal open will fail unless old Popup Maker markup exists.

### Runtime flow for analytics

1. `functions-new.php` injects deferred analytics script in `wp_footer`.
2. Script async-loads GA and Yandex (`https://mc.yandex.ru/metrika/tag.js`).
3. Current code calls `ym(...)` in `y.onload`.

Risk: `ym` may still be unavailable at call time (or blocked), causing `ReferenceError`.

## Files Map (how they connect)

- `functions-new.php`
  - Includes `bitrix24-lead-form.php`.
  - Contains WooCommerce loop CTA markup (`Получить КП` link target).
  - Contains deferred analytics initialization code (GA + Yandex).
- `bitrix24-lead-form.php`
  - Renders modal with `id="b24-modal"`.
  - Handles click delegation for `a[href="#b24-modal"]`.
- `single-product-redesign.php`
  - Contains CTA links to modal in redesigned product template.
- `page-vsuzi-hub.php`
  - Contains CTA links to modal on VSUZI hub page.

## Important findings from current codebase

- Active PHP files already use `#b24-modal`.
- `#popmake-8502` is found only in `backups/` and `docs/`, not in active PHP sources.
- Therefore, if production still shows `#popmake-8502`, likely causes are:
  - stale deployed server `functions.php`,
  - stale cache/minified HTML,
  - legacy content in DB-generated markup (not in this repo files).

## Implementation target for the next AI assistant

1. Harden Yandex init in `functions-new.php`:
   - guard against missing `window.ym`,
   - avoid direct unguarded `ym(...)`,
   - tolerate blocked script/adblock conditions without throwing.
2. Enforce a single CTA target (`#b24-modal`) in all active templates listed above.
3. Verify no active source references `#popmake-8502`.
4. Add smoke verification steps for:
   - homepage,
   - catalog,
   - one product page.

## Validation checklist (must pass)

- Browser console: no `ym is not defined` error on key pages.
- Every visible `Получить КП` opens Bitrix modal.
- `rg -n "popmake-8502|#popmake" -S` has no matches outside `backups/` and `docs/`.
- CTA click behavior works both for listing cards and product detail page.

## Final file paths to focus on

- `/Users/victorkornilov/WORK/tdpuls-wordpress/functions-new.php`
- `/Users/victorkornilov/WORK/tdpuls-wordpress/bitrix24-lead-form.php`
- `/Users/victorkornilov/WORK/tdpuls-wordpress/single-product-redesign.php`
- `/Users/victorkornilov/WORK/tdpuls-wordpress/page-vsuzi-hub.php`
- `/Users/victorkornilov/WORK/tdpuls-wordpress/README.md` (deploy mapping: `functions-new.php` -> server `functions.php`)
