> **АРХИВ — ВЫПОЛНЕН 2026-01-24** на mchost.ru. Сайт перенесён на VPS Beget 08.02.2026.

# tdpuls.com Site Optimization Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Optimize tdpuls.com WordPress site to reduce TTFB from 3.25s to <1s and improve PageSpeed score from ~30 to 70+

**Architecture:** Server-side optimization via .htaccess (caching, gzip), CSS/JS optimization via Autoptimize plugin configuration, image optimization via WebP conversion, WordPress cleanup (plugins, database)

**Tech Stack:** WordPress 6.x, Flatsome 3.13.1 theme, WooCommerce, Apache (.htaccess), FTP access

**FTP Credentials:** см. `docs/credentials.md`

---

## Task 1: Create Backup

**Files:**
- Backup: `httpdocs/.htaccess`
- Backup: `httpdocs/wp-config.php`

**Step 1: Download current .htaccess**

```bash
curl -s --user "USER:PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/.htaccess" \
  > /Users/victorkornilov/WORK/TDPULS_site/backups/htaccess.backup.txt
```

Expected: File downloaded successfully

**Step 2: Download wp-config.php backup**

```bash
curl -s --user "USER:PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/wp-config.php" \
  > /Users/victorkornilov/WORK/TDPULS_site/backups/wp-config.backup.php
```

Expected: File downloaded successfully

**Step 3: Verify backups exist**

```bash
ls -la /Users/victorkornilov/WORK/TDPULS_site/backups/
```

Expected: Both backup files present with content

---

## Task 2: Add Browser Caching to .htaccess

**Files:**
- Modify: `httpdocs/.htaccess`

**Step 1: Create new .htaccess with caching rules**

Create local file `/Users/victorkornilov/WORK/TDPULS_site/htaccess-optimized.txt`:

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

# ===== OPTIMIZATION START =====

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresDefault "access plus 1 month"

    # Images - 1 year
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"

    # CSS and JS - 1 month
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"

    # Fonts - 1 year
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType application/font-woff "access plus 1 year"
    ExpiresByType application/font-woff2 "access plus 1 year"
</IfModule>

# GZIP Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE image/svg+xml
</IfModule>

# Cache-Control Headers
<IfModule mod_headers.c>
    <FilesMatch "\.(ico|pdf|flv|jpg|jpeg|png|gif|webp|js|css|swf|woff|woff2)$">
        Header set Cache-Control "max-age=31536000, public"
    </FilesMatch>

    <FilesMatch "\.(html|htm|php)$">
        Header set Cache-Control "max-age=0, private, must-revalidate"
    </FilesMatch>

    # Security headers
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# WebP Support
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTP_ACCEPT} image/webp
    RewriteCond %{REQUEST_FILENAME} (.+)\.(jpe?g|png)$
    RewriteCond %{REQUEST_FILENAME}.webp -f
    RewriteRule ^(.+)\.(jpe?g|png)$ $1.$2.webp [T=image/webp,L]
</IfModule>

# ===== OPTIMIZATION END =====

# 21-07-2025
Redirect 301 /privacy-policy/ https://tdpuls.com/wp-content/uploads/policy.pdf

# 01-08-2025 13.40.40 COUNT LINES = 19
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

**Step 2: Upload new .htaccess to server**

```bash
curl -T /Users/victorkornilov/WORK/TDPULS_site/htaccess-optimized.txt \
  --user "USER:PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/.htaccess"
```

Expected: Upload successful, no errors

**Step 3: Verify site still works**

```bash
curl -sI "https://tdpuls.com/" | head -20
```

Expected: HTTP 200 response

**Step 4: Verify caching headers applied**

```bash
curl -sI "https://tdpuls.com/wp-content/uploads/woocommerce-placeholder.png" | grep -i cache
```

Expected: `Cache-Control: max-age=31536000` header present

**Step 5: Verify GZIP compression**

```bash
curl -sI -H "Accept-Encoding: gzip" "https://tdpuls.com/" | grep -i encoding
```

Expected: `Content-Encoding: gzip` header present

---

## Task 3: Measure Performance Improvement

**Step 1: Measure new TTFB**

```bash
curl -w "DNS: %{time_namelookup}s\nConnect: %{time_connect}s\nTTFB: %{time_starttransfer}s\nTotal: %{time_total}s\nSize: %{size_download} bytes\n" \
  -o /dev/null -s "https://tdpuls.com/"
```

Expected: TTFB improved (should be <2s now with gzip)

**Step 2: Document results**

Save results to `/Users/victorkornilov/WORK/TDPULS_site/optimization-results.md`

---

## Task 4: Configure Autoptimize Plugin (via WordPress Admin)

**Note:** This task requires WordPress admin access at https://tdpuls.com/wp-admin/

**Step 1: Login to WordPress admin**

URL: `https://tdpuls.com/wp-admin/`
Navigate to: Settings > Autoptimize

**Step 2: Configure JavaScript settings**

- [x] Optimize JavaScript Code
- [x] Aggregate JS-files
- [x] Also aggregate inline JS
- [ ] Force JavaScript in <head> (leave unchecked)
- [x] Add try-catch wrapping

**Step 3: Configure CSS settings**

- [x] Optimize CSS Code
- [x] Aggregate CSS-files
- [x] Also aggregate inline CSS
- [x] Generate data: URIs for images

**Step 4: Configure HTML settings**

- [x] Optimize HTML Code
- [x] Keep HTML comments (for conditionals)

**Step 5: Configure Images settings**

- [x] Lazy-load images
- [ ] Lazy-load iframes (test first)

**Step 6: Save and clear cache**

Click "Save Changes and Empty Cache"

**Step 7: Verify site works**

```bash
curl -sI "https://tdpuls.com/" | head -5
```

Expected: HTTP 200, site loads correctly

---

## Task 5: Install and Configure Image Optimization Plugin

**Note:** This task requires WordPress admin access

**Step 1: Install ShortPixel Image Optimizer**

WordPress Admin > Plugins > Add New > Search "ShortPixel"
Install and activate

**Step 2: Get ShortPixel API key**

- Visit https://shortpixel.com/
- Register for free account (100 images/month free)
- Copy API key

**Step 3: Configure ShortPixel**

- Settings > ShortPixel
- Paste API key
- Compression type: Lossy (best compression)
- [x] Create WebP versions
- [x] Deliver WebP versions

**Step 4: Bulk optimize existing images**

Media > Bulk ShortPixel > Start Optimizing

Expected: Images compressed 50-80%, WebP versions created

---

## Task 6: Cleanup Unnecessary Plugins

**Note:** This task requires WordPress admin access

**Step 1: Deactivate mousewheel-smooth-scroll**

Plugins > Installed Plugins > Find "Mousewheel Smooth Scroll" > Deactivate > Delete

**Step 2: Deactivate export-categories**

Plugins > Find "Export Categories" > Deactivate > Delete

**Step 3: Deactivate wordpress-importer (if not in use)**

Plugins > Find "WordPress Importer" > Deactivate > Delete

**Step 4: Verify site works after each deletion**

```bash
curl -sI "https://tdpuls.com/" | head -5
```

Expected: HTTP 200 after each plugin removal

---

## Task 7: Update Flatsome Theme

**Files:**
- Update: `httpdocs/wp-content/themes/flatsome/`

**Step 1: Check current version**

Current: 3.13.1
Latest: 3.19.x

**Step 2: Backup current theme**

```bash
mkdir -p /Users/victorkornilov/WORK/TDPULS_site/backups/flatsome
# Download theme folder (large, may take time)
```

**Step 3: Update via WordPress admin**

Appearance > Themes > Flatsome > Update

**Note:** Requires valid Flatsome license for updates

**Step 4: Verify site after update**

```bash
curl -sI "https://tdpuls.com/" | head -5
```

Expected: HTTP 200, design intact

---

## Task 8: Configure SEO Settings (All in One SEO)

**Note:** This task requires WordPress admin access

**Step 1: Navigate to SEO settings**

All in One SEO > Search Appearance > Global Settings

**Step 2: Configure homepage SEO**

- Title: "ТД Пульс - Медицинское оборудование | Официальный дистрибьютор"
- Meta Description: "Официальный дистрибьютор медицинского оборудования Philips. Продажа, сервис, гарантия. Работаем по всей России."

**Step 3: Enable Open Graph**

All in One SEO > Social Networks
- [x] Enable Open Graph Markup
- Set default image for sharing

**Step 4: Enable Twitter Cards**

- [x] Enable Twitter Card
- Card Type: Summary with Large Image

**Step 5: Verify meta tags**

```bash
curl -s "https://tdpuls.com/" | grep -E "<meta.*description|og:|twitter:"
```

Expected: Meta tags present in HTML

---

## Task 9: Final Performance Test

**Step 1: Clear all caches**

- Autoptimize > Clear Cache
- Browser cache
- CDN cache (if any)

**Step 2: Measure final TTFB**

```bash
curl -w "DNS: %{time_namelookup}s\nConnect: %{time_connect}s\nTTFB: %{time_starttransfer}s\nTotal: %{time_total}s\nSize: %{size_download} bytes\n" \
  -o /dev/null -s "https://tdpuls.com/"
```

Expected: TTFB < 1.5s, Size < 100KB

**Step 3: Run PageSpeed test**

Visit: https://pagespeed.web.dev/analysis?url=https%3A%2F%2Ftdpuls.com%2F

Expected scores:
- Mobile: 70+
- Desktop: 85+

**Step 4: Document final results**

Update `/Users/victorkornilov/WORK/TDPULS_site/optimization-results.md` with:
- Before/after TTFB
- Before/after page size
- Before/after PageSpeed scores
- Screenshots

---

## Summary Checklist

- [ ] Task 1: Backup created
- [ ] Task 2: .htaccess optimized (caching, gzip, headers)
- [ ] Task 3: Performance measured
- [ ] Task 4: Autoptimize configured
- [ ] Task 5: Image optimization plugin installed
- [ ] Task 6: Unnecessary plugins removed
- [ ] Task 7: Flatsome theme updated
- [ ] Task 8: SEO settings configured
- [ ] Task 9: Final testing completed

**Expected Results:**
| Metric | Before | After |
|--------|--------|-------|
| TTFB | 3.25s | <1s |
| Page Size | 190KB | ~60-80KB |
| PageSpeed Mobile | ~30 | 70+ |
| PageSpeed Desktop | ~50 | 85+ |
