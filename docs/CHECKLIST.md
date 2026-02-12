# Чеклист обслуживания tdpuls.com

## Быстрые команды

### Проверка работоспособности
```bash
curl -sI "https://tdpuls.com/" | head -1
# Ожидаемо: HTTP/2 200
```

### Проверка производительности
```bash
curl -w "HTTP: %{http_code} | TTFB: %{time_starttransfer}s | Total: %{time_total}s\n" -o /dev/null -s "https://tdpuls.com/"
# Ожидаемо: TTFB < 0.5s (с FastCGI cache), Total < 1s
```

### Проверка GZIP
```bash
curl -sI -H "Accept-Encoding: gzip" "https://tdpuls.com/" | grep content-encoding
# Ожидаемо: content-encoding: gzip
```

### Проверка FastCGI Cache
```bash
curl -sI "https://tdpuls.com/" | grep X-FastCGI-Cache
# Первый запрос: MISS, повторный: HIT
```

### Проверка кэширования статики
```bash
curl -sI "https://tdpuls.com/wp-content/themes/flatsome/assets/css/flatsome.css" | grep cache-control
# Ожидаемо: max-age=2592000
```

### Проверка SEO Title
```bash
curl -s "https://tdpuls.com/" | grep "<title"
# Ожидаемо: Медицинское оборудование Philips, GE, Siemens — ГК «Пульс»
```

### Проверка Schema.org
```bash
curl -s "https://tdpuls.com/" | grep -A 5 'application/ld+json'
# Ожидаемо: @type: Organization
```

---

## Деплой

### Автоматический (через git)
```bash
git push production main
# Post-receive hook: rsync theme/ → flatsome, uploads/ → uploads, webroot/ → public
# Автоматически: chown, очистка FastCGI + OPcache + Redis
```

### Ручной (один файл)
```bash
scp theme/functions.php root@85.198.96.28:/var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php
ssh root@85.198.96.28 "chown www-data:www-data /var/www/tdpuls.com/public/wp-content/themes/flatsome/functions.php"
```

---

## Очистка кешей

### FastCGI cache (также автоматически при save_post/edited_term)
```bash
ssh root@85.198.96.28 "rm -rf /var/cache/nginx/fastcgi/* && systemctl reload nginx"
```

### Redis object cache
```bash
ssh root@85.198.96.28 "redis-cli FLUSHDB"
```

### WP object cache
```bash
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp cache flush --allow-root"
```

### OPcache
```bash
ssh root@85.198.96.28 "systemctl restart php8.3-fpm"
```

---

## WP-CLI

```bash
# Список плагинов
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp plugin list --allow-root"

# Обновить WordPress
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp core update --allow-root"

# Обновить плагины
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp plugin update --all --allow-root"

# Состояние Redis
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp redis status --allow-root"

# Состояние cron
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public && wp cron event list --allow-root"
```

---

## Серверные проверки

```bash
# Статус сервисов
ssh root@85.198.96.28 "systemctl is-active nginx php8.3-fpm mariadb redis-server fail2ban"

# RAM и диск
ssh root@85.198.96.28 "free -m && echo '---' && df -h /"

# Логи ошибок nginx
ssh root@85.198.96.28 "tail -20 /var/log/nginx/tdpuls-error.log"

# Логи ошибок PHP
ssh root@85.198.96.28 "tail -20 /var/log/php/wordpress-error.log"

# Медленные запросы БД
ssh root@85.198.96.28 "tail -20 /var/log/mysql/slow.log"

# SSL-сертификат
ssh root@85.198.96.28 "certbot certificates"
```

---

## Аварийное восстановление

### Откат functions.php
```bash
# Последний рабочий бэкап есть на сервере
ssh root@85.198.96.28 "cd /var/www/tdpuls.com/public/wp-content/themes/flatsome && cp functions.php.bak functions.php"
```

### Перезапуск стека
```bash
ssh root@85.198.96.28 "systemctl restart php8.3-fpm nginx mariadb redis-server"
```

### Откат на mchost.ru
> mchost.ru НЕ АКТИВЕН с 08.02.2026. Откат: вернуть A-запись в DNS.
> Не удалять данные на mchost до 22.02.2026.

---

## База данных

### Подключение
```bash
ssh root@85.198.96.28 "mariadb -u tdpuls -p tdpuls_wp"
# Пароль: см. docs/credentials.md
```

### Полезные SQL-запросы
```sql
-- Проверить количество ревизий
SELECT COUNT(*) FROM wp_posts WHERE post_type = 'revision';

-- Размер таблиц
SELECT table_name, ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'Size_MB'
FROM information_schema.TABLES WHERE table_schema = 'tdpuls_wp'
ORDER BY (data_length + index_length) DESC LIMIT 10;
```

---

## Важные ссылки

| Ресурс | URL |
|--------|-----|
| Сайт | https://tdpuls.com |
| WP Admin | https://tdpuls.com/wp-admin/ |
| PageSpeed | https://pagespeed.web.dev/analysis?url=https://tdpuls.com |
| Яндекс Вебмастер | https://webmaster.yandex.ru |
| Яндекс Метрика | https://metrika.yandex.ru |

---

## Конфиги сервера

| Конфиг | Путь |
|---|---|
| nginx main | `/etc/nginx/nginx.conf` |
| nginx site | `/etc/nginx/sites-available/tdpuls.com` |
| PHP-FPM pool | `/etc/php/8.3/fpm/pool.d/wordpress.conf` |
| MariaDB | `/etc/mysql/mariadb.conf.d/60-optimize.cnf` |
| Redis | `/etc/redis/redis.conf` |
| fail2ban | `/etc/fail2ban/jail.local` |
| SSH | `/etc/ssh/sshd_config.d/hardening.conf` |

---

*Обновлено: 2026-02-12*
