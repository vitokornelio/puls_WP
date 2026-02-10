> **АРХИВ — ВЫПОЛНЕН 2026-02-08.** Миграция завершена. Актуальный IP: `85.198.96.28`. PHP обновлён до 8.3 (в плане указан 8.1). Все пароли удалены из этого файла — см. `docs/credentials.md`.

# VPS Migration: tdpuls.com → Beget VPS

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Перенести WordPress+WooCommerce сайт tdpuls.com с shared-хостинга mchost.ru на VPS Beget с полной оптимизацией стека.

**Architecture:** Ubuntu 24.04 + nginx (с FastCGI cache) + PHP 8.1-FPM + MariaDB + Redis. Все конфиги оптимизированы под 6 ГБ RAM. SSH-доступ по ключам, безопасность через UFW + fail2ban + sysctl hardening.

**Tech Stack:** Ubuntu 24.04, nginx, PHP 8.1-FPM, MariaDB 10.11, Redis 7, certbot, fail2ban, UFW

---

## Уроки из первой попытки (не повторять!)

1. **SSH-ключи ПЕРЕД hardening** — никогда не ограничивать SSH до настройки ключей
2. **fail2ban ПОСЛЕ проверки SSH** — иначе банит при неудачных попытках
3. **Проверять SSH после КАЖДОГО изменения** sshd конфига
4. **MaxAuthTries >= 6** — SSH-клиент пробует несколько методов аутентификации
5. **Не использовать `Protocol 2`** — устаревшая директива в OpenSSH 9.x
6. **Ubuntu 24.04 cloud-init** — по умолчанию `PasswordAuthentication no`, нужно включить до работы
7. **`DEBIAN_FRONTEND=noninteractive`** — для всех apt-get, иначе зависает на интерактивных промптах
8. **sshpass** — использовать ТОЛЬКО с `-o PubkeyAuthentication=no`, иначе тратит попытки на ключи

---

## Конфигурация сервера

- **IP:** ~~155.212.131.67~~ → актуальный: `85.198.96.28`
- **OS:** Ubuntu 24.04 LTS (чистая установка)
- **CPU:** 2 ядра, **RAM:** 6 ГБ, **Диск:** 30 ГБ NVMe
- **Root пароль:** см. `docs/credentials.md`
- **Локация:** Москва

## Доступы к исходному серверу

> Все пароли: см. `docs/credentials.md`

---

## Переменные (обновить после переустановки)

```
VPS_IP=85.198.96.28  # актуальный IP
# Пароли: см. docs/credentials.md
```

---

## Фаза 1: Первый доступ и SSH-ключи

> **ПРИНЦИП:** Сначала настроить ключи, потом закручивать гайки.

### Task 1.1: Проверка доступа после переустановки

> Пользователь должен сообщить новый IP и пароль (если изменились).

**Шаг 1:** Проверить связь:
```bash
nc -z -w 5 $VPS_IP 22 && echo "Port 22 open" || echo "Port 22 closed"
```
Expected: `Port 22 open`

**Шаг 2:** Проверить SSH-доступ:
```bash
$SSH_CMD "echo 'SSH OK' && uname -a && free -m"
```
Expected: `SSH OK`, Ubuntu 24.04, ~6 ГБ RAM

**Если PasswordAuthentication отключён (Permission denied):**
> Пользователь выполняет в VNC-консоли Beget:
> ```
> echo "PasswordAuthentication yes" > /etc/ssh/sshd_config.d/60-password.conf
> systemctl restart ssh
> ```

### Task 1.2: Настроить SSH-ключи

**Шаг 1:** Сгенерировать ключ локально (если нет):
```bash
[ -f ~/.ssh/id_ed25519 ] || ssh-keygen -t ed25519 -C "tdpuls-deploy" -f ~/.ssh/id_ed25519 -N ""
```

**Шаг 2:** Скопировать ключ на сервер:
```bash
sshpass -p $VPS_PASS ssh-copy-id -o StrictHostKeyChecking=no -o PubkeyAuthentication=no root@$VPS_IP
```

**Шаг 3:** Проверить вход по ключу (без пароля):
```bash
ssh root@$VPS_IP "echo 'Key auth works!'"
```
Expected: `Key auth works!`

**Шаг 4:** Отключить вход по паролю:
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
echo "PasswordAuthentication no" > /etc/ssh/sshd_config.d/60-password.conf
systemctl restart ssh
echo "Password auth disabled"
SCRIPT
```

**Шаг 5:** Проверить что ключ всё ещё работает:
```bash
ssh root@$VPS_IP "echo 'Still connected by key!'"
```
Expected: `Still connected by key!`

> **СТОП-ТОЧКА:** Если шаг 5 не прошёл — НЕ продолжать. Починить SSH через VNC-консоль.

---

## Фаза 2: Обновление системы + swap

### Task 2.1: Обновить пакеты и настроить swap

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
set -e
export DEBIAN_FRONTEND=noninteractive

echo "=== Обновление пакетов ==="
apt-get update -qq
apt-get upgrade -y -qq

echo "=== Swap 2 ГБ ==="
if [ ! -f /swapfile ]; then
  fallocate -l 2G /swapfile
  chmod 600 /swapfile
  mkswap /swapfile
  swapon /swapfile
  echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi
sysctl vm.swappiness=10
grep -q 'vm.swappiness' /etc/sysctl.conf || echo 'vm.swappiness=10' >> /etc/sysctl.conf

echo "=== Проверка ==="
free -m | grep Swap
echo "=== ГОТОВО ==="
SCRIPT
```
Expected: Swap ~2047 МБ

---

## Фаза 3: Установка стека (один скрипт)

> Все сервисы ставим одним блоком, чтобы минимизировать SSH-сессии.

### Task 3.1: Установить nginx + PHP 8.1 + MariaDB + Redis + certbot

**Шаг 1:** Добавить репозиторий PHP и установить всё:
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
set -e
export DEBIAN_FRONTEND=noninteractive

echo "=== 1/5: Репозиторий PHP 8.1 ==="
apt-get install -y -qq software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update -qq

echo "=== 2/5: nginx ==="
apt-get install -y -qq nginx
systemctl enable nginx

echo "=== 3/5: PHP 8.1-FPM + расширения ==="
apt-get install -y -qq \
  php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring \
  php8.1-curl php8.1-zip php8.1-gd php8.1-intl \
  php8.1-soap php8.1-bcmath php8.1-opcache \
  php8.1-redis php8.1-imagick
systemctl enable php8.1-fpm

echo "=== 4/5: MariaDB ==="
apt-get install -y -qq mariadb-server mariadb-client
systemctl enable mariadb

echo "=== 5/5: Redis + certbot ==="
apt-get install -y -qq redis-server certbot python3-certbot-nginx
systemctl enable redis-server

echo "=== Проверка всех сервисов ==="
for svc in nginx php8.1-fpm mariadb redis-server; do
  printf "%-20s %s\n" "$svc" "$(systemctl is-active $svc)"
done

echo "=== Версии ==="
nginx -v 2>&1
php8.1 -v | head -1
mariadb --version
redis-cli --version
certbot --version 2>&1

echo "=== RAM после установки ==="
free -m

echo "=== СТЕК УСТАНОВЛЕН ==="
SCRIPT
```
Expected: все сервисы `active`, все версии выводятся, RAM < 2 ГБ used

---

## Фаза 4: Конфигурация стека (один скрипт)

### Task 4.1: MariaDB — безопасность + оптимизация

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
set -e

echo "=== MariaDB: безопасность ==="
mariadb -e "
ALTER USER 'root'@'localhost' IDENTIFIED BY 'DB_ROOT_PASS';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
"

echo "=== MariaDB: оптимизация под 6 ГБ ==="
cat > /etc/mysql/mariadb.conf.d/60-optimize.cnf << 'DBCONF'
[mysqld]
# InnoDB
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1

# Connections
max_connections = 50
wait_timeout = 300
interactive_timeout = 300

# Query tuning
tmp_table_size = 64M
max_heap_table_size = 64M
join_buffer_size = 2M
sort_buffer_size = 2M
read_buffer_size = 1M

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
DBCONF

systemctl restart mariadb
mariadb -u root -pDB_ROOT_PASS -e "SHOW VARIABLES LIKE 'innodb_buffer_pool_size'" 2>/dev/null
echo "=== MariaDB ГОТОВО ==="
SCRIPT
```
Expected: `innodb_buffer_pool_size | 2147483648`

### Task 4.2: PHP-FPM — пул WordPress + OPcache

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
set -e

echo "=== PHP-FPM: пул WordPress ==="
cat > /etc/php/8.1/fpm/pool.d/wordpress.conf << 'PHPPOOL'
[wordpress]
user = www-data
group = www-data
listen = /run/php/php8.1-fpm-wordpress.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 8
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 5
pm.max_requests = 500
pm.process_idle_timeout = 10s

php_admin_value[error_log] = /var/log/php/wordpress-error.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_vars] = 3000
PHPPOOL

mkdir -p /var/log/php
chown www-data:www-data /var/log/php

# Отключить дефолтный пул
mv /etc/php/8.1/fpm/pool.d/www.conf /etc/php/8.1/fpm/pool.d/www.conf.disabled 2>/dev/null || true

echo "=== OPcache ==="
cat > /etc/php/8.1/mods-available/opcache-custom.ini << 'OPCACHE'
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 60
opcache.validate_timestamps = 1
opcache.save_comments = 1
OPCACHE

systemctl restart php8.1-fpm
ls -la /run/php/php8.1-fpm-wordpress.sock
echo "=== PHP-FPM ГОТОВО ==="
SCRIPT
```
Expected: сокет `/run/php/php8.1-fpm-wordpress.sock` существует

### Task 4.3: Redis — ограничение памяти

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
set -e

# Добавить настройки в основной конфиг
sed -i '/^# maxmemory /a maxmemory 256mb' /etc/redis/redis.conf
sed -i '/^# maxmemory-policy/a maxmemory-policy allkeys-lru' /etc/redis/redis.conf

systemctl restart redis-server
redis-cli CONFIG GET maxmemory
echo "=== Redis ГОТОВО ==="
SCRIPT
```
Expected: `maxmemory 268435456`

---

## Фаза 5: Настройка nginx

### Task 5.1: Главный конфиг nginx

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
set -e
cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup

cat > /etc/nginx/nginx.conf << 'NGINXMAIN'
user www-data;
worker_processes auto;
pid /run/nginx.pid;
include /etc/nginx/modules-enabled/*.conf;

events {
    worker_connections 1024;
    multi_accept on;
    use epoll;
}

http {
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 30;
    types_hash_max_size 2048;
    server_tokens off;
    client_max_body_size 64m;

    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 5;
    gzip_min_length 256;
    gzip_types text/plain text/css text/xml text/javascript
               application/json application/javascript application/xml
               application/rss+xml image/svg+xml;

    # FastCGI Cache
    fastcgi_cache_path /var/cache/nginx/fastcgi levels=1:2
        keys_zone=wpcache:64m max_size=1g inactive=60m use_temp_path=off;
    fastcgi_cache_key "$scheme$request_method$host$request_uri";

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=wp_login:10m rate=1r/s;

    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
NGINXMAIN

mkdir -p /var/cache/nginx/fastcgi
nginx -t && echo "nginx.conf OK"
SCRIPT
```
Expected: `nginx.conf OK`

### Task 5.2: Server block tdpuls.com

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
set -e

cat > /etc/nginx/sites-available/tdpuls.com << 'SITECONF'
server {
    listen 80;
    server_name www.tdpuls.com;
    return 301 $scheme://tdpuls.com$request_uri;
}

server {
    listen 80;
    server_name tdpuls.com;
    root /var/www/tdpuls.com/public;
    index index.php index.html;

    access_log /var/log/nginx/tdpuls-access.log;
    error_log /var/log/nginx/tdpuls-error.log;

    # FastCGI cache conditions
    set $skip_cache 0;
    if ($request_method = POST) { set $skip_cache 1; }
    if ($query_string != "") { set $skip_cache 1; }
    if ($http_cookie ~* "wordpress_logged_in|woocommerce_cart_hash|woocommerce_items_in_cart") {
        set $skip_cache 1;
    }
    if ($request_uri ~* "/cart.*|/checkout.*|/my-account.*|/wp-admin.*") {
        set $skip_cache 1;
    }

    # Static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files $uri =404;
    }

    # Block sensitive files
    location ~* /(wp-config\.php|readme\.html|license\.txt|xmlrpc\.php) { deny all; }
    location ~ /\.(ht|git|svn) { deny all; }
    location ~* /uploads/.*\.php$ { deny all; }

    # WordPress
    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    # PHP with FastCGI cache
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php8.1-fpm-wordpress.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        fastcgi_cache wpcache;
        fastcgi_cache_valid 200 60m;
        fastcgi_cache_valid 404 1m;
        fastcgi_cache_bypass $skip_cache;
        fastcgi_no_cache $skip_cache;
        add_header X-FastCGI-Cache $upstream_cache_status;

        fastcgi_connect_timeout 60;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
    }

    # Rate limit wp-login
    location = /wp-login.php {
        limit_req zone=wp_login burst=3 nodelay;
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.1-fpm-wordpress.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Sitemap
    location ~ ^/sitemap.*\.xml$ {
        try_files $uri /index.php?$args;
    }
}
SITECONF

ln -sf /etc/nginx/sites-available/tdpuls.com /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

mkdir -p /var/www/tdpuls.com/public
chown -R www-data:www-data /var/www/tdpuls.com

nginx -t && systemctl reload nginx
echo "=== Сайт настроен ==="
SCRIPT
```
Expected: `Сайт настроен`

### Task 5.3: Проверить PHP через nginx

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
echo "<?php echo PHP_VERSION;" > /var/www/tdpuls.com/public/test.php
chown www-data:www-data /var/www/tdpuls.com/public/test.php
RESULT=$(curl -s http://localhost/test.php -H "Host: tdpuls.com")
rm /var/www/tdpuls.com/public/test.php
echo "PHP version: $RESULT"
SCRIPT
```
Expected: `PHP version: 8.1.x`

---

## Фаза 6: Подготовка WordPress

### Task 6.1: Создать БД и пользователя

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
mariadb -u root -pDB_ROOT_PASS -e "
CREATE DATABASE IF NOT EXISTS tdpuls_wp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'tdpuls'@'localhost' IDENTIFIED BY 'DB_WP_PASS';
GRANT ALL PRIVILEGES ON tdpuls_wp.* TO 'tdpuls'@'localhost';
FLUSH PRIVILEGES;
" 2>/dev/null
mariadb -u root -pDB_ROOT_PASS -e "SHOW DATABASES" 2>/dev/null
SCRIPT
```
Expected: `tdpuls_wp` в списке

### Task 6.2: Установить WP-CLI

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp
wp --info --allow-root 2>/dev/null | head -5
SCRIPT
```
Expected: `WP-CLI` version info

---

## Фаза 7: Миграция данных с mchost

### Task 7.1: Экспорт БД через временный скрипт

**Шаг 1:** Создать и загрузить скрипт экспорта:
```bash
cat > /tmp/db-export.php << 'PHPEOF'
<?php
if ($_GET['key'] !== 'EXPORT_KEY') { http_response_code(404); exit; }
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="dump.sql"');
$cmd = 'mysqldump -h a265896.mysql.mchost.ru -u a265896_pulsnew -pMYSQL_PASS a265896_pulsnew --single-transaction --quick --lock-tables=false 2>/dev/null';
passthru($cmd);
PHPEOF

curl -T /tmp/db-export.php --user "a265896_tdpuls:FTP_PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/db-export.php"
```

**Шаг 2:** Скачать дамп:
```bash
curl -s "https://tdpuls.com/db-export.php?key=EXPORT_KEY" -o /tmp/tdpuls-db.sql
ls -lh /tmp/tdpuls-db.sql
head -3 /tmp/tdpuls-db.sql
```
Expected: SQL-файл > 1 МБ

**Шаг 3:** СРАЗУ удалить скрипт (безопасность!):
```bash
curl --user "a265896_tdpuls:FTP_PASS" "ftp://a265896.ftp.mchost.ru" \
  -Q "DELE httpdocs/db-export.php" -o /dev/null -s
curl -sI "https://tdpuls.com/db-export.php" | head -1
```
Expected: 404

### Task 7.2: Скачать файлы WordPress

**Шаг 1:** Рекурсивное скачивание через wget:
```bash
wget -m --ftp-user="a265896_tdpuls" --ftp-password="FTP_PASS" \
  "ftp://a265896.ftp.mchost.ru/httpdocs/" \
  -P /tmp/mchost-backup/ --reject="*.log,error_log" -nH --cut-dirs=1 -q
```
> 10-30 минут. Запустить в фоне или через screen.

**Шаг 2:** Проверка:
```bash
du -sh /tmp/mchost-backup/
ls /tmp/mchost-backup/ | head -10
```
Expected: wp-admin, wp-content, wp-includes, index.php, wp-config.php

### Task 7.3: Залить на VPS

**Шаг 1:**
```bash
rsync -avz --progress /tmp/mchost-backup/ root@$VPS_IP:/var/www/tdpuls.com/public/
ssh root@$VPS_IP "chown -R www-data:www-data /var/www/tdpuls.com/public"
```

**Шаг 2:** Проверка:
```bash
ssh root@$VPS_IP "ls /var/www/tdpuls.com/public/wp-config.php && du -sh /var/www/tdpuls.com/public/"
```
Expected: wp-config.php существует

### Task 7.4: Импорт БД

**Шаг 1:**
```bash
scp /tmp/tdpuls-db.sql root@$VPS_IP:/tmp/
ssh root@$VPS_IP "mariadb -u tdpuls -pDB_WP_PASS tdpuls_wp < /tmp/tdpuls-db.sql 2>/dev/null && echo 'DB imported OK'"
```

**Шаг 2:** Проверка:
```bash
ssh root@$VPS_IP "mariadb -u tdpuls -pDB_WP_PASS tdpuls_wp -e 'SHOW TABLES' 2>/dev/null | wc -l"
```
Expected: > 30 таблиц

### Task 7.5: Обновить wp-config.php

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
WP=/var/www/tdpuls.com/public/wp-config.php
cp $WP ${WP}.mchost-backup

sed -i "s/define( *'DB_NAME'.*/define('DB_NAME', 'tdpuls_wp');/" $WP
sed -i "s/define( *'DB_USER'.*/define('DB_USER', 'tdpuls');/" $WP
sed -i "s/define( *'DB_PASSWORD'.*/define('DB_PASSWORD', 'DB_WP_PASS');/" $WP
sed -i "s/define( *'DB_HOST'.*/define('DB_HOST', 'localhost');/" $WP

# Redis
grep -q "WP_REDIS_HOST" $WP || cat >> $WP << 'WPEOF'

/* Redis Object Cache */
define('WP_REDIS_HOST', '127.0.0.1');
define('WP_REDIS_PORT', 6379);
define('WP_REDIS_DATABASE', 0);
WPEOF

grep "DB_NAME\|DB_USER\|DB_HOST\|WP_REDIS" $WP
echo "=== wp-config.php обновлён ==="
SCRIPT
```
Expected: новые значения БД

### Task 7.6: Проверить WordPress по IP

**Шаг 1:** Тест через curl с подменой Host:
```bash
ssh root@$VPS_IP "curl -sI http://localhost -H 'Host: tdpuls.com' | head -5"
```
Expected: HTTP 200 или 301/302 (WordPress redirect)

---

## Фаза 8: DNS + SSL

### Task 8.1: Переключить DNS

> **ДЕЙСТВИЕ ПОЛЬЗОВАТЕЛЯ**

Изменить A-записи в панели DNS-провайдера:
- `tdpuls.com` → `155.212.131.67`
- `www.tdpuls.com` → `155.212.131.67`

**Проверка (подождать 5-30 мин):**
```bash
dig +short tdpuls.com
```
Expected: `155.212.131.67`

### Task 8.2: SSL-сертификат

**Шаг 1:** После того как DNS обновился:
```bash
ssh root@$VPS_IP "certbot --nginx -d tdpuls.com -d www.tdpuls.com --non-interactive --agree-tos --email admin@tdpuls.com"
```
Expected: `Successfully received certificate`

**Шаг 2:** Проверка:
```bash
curl -sI https://tdpuls.com/ | head -5
```
Expected: HTTP 200

**Шаг 3:** Автообновление:
```bash
ssh root@$VPS_IP "certbot renew --dry-run"
```
Expected: `all simulated renewals succeeded`

---

## Фаза 9: Redis Object Cache + финализация WordPress

### Task 9.1: Активировать Redis в WordPress

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
cd /var/www/tdpuls.com/public
wp plugin install redis-cache --activate --allow-root
wp redis enable --allow-root
wp redis status --allow-root
SCRIPT
```
Expected: `Status: Connected`

---

## Фаза 10: Безопасность (ПОСЛЕДНИЙ ЭТАП!)

> **ВАЖНО:** Безопасность настраиваем ПОСЛЕ того, как всё работает. Проверяем SSH после каждого шага.

### Task 10.1: UFW Firewall

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
echo "y" | ufw enable
ufw status
SCRIPT
```
Expected: `Status: active`, порты 22/80/443 открыты

**Шаг 2:** Проверить SSH:
```bash
ssh root@$VPS_IP "echo 'SSH still works after UFW'"
```
Expected: `SSH still works after UFW`

### Task 10.2: SSH Hardening

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
cat > /etc/ssh/sshd_config.d/hardening.conf << 'SSHCONF'
PermitRootLogin prohibit-password
LoginGraceTime 60
MaxAuthTries 6
MaxSessions 5
PermitEmptyPasswords no
X11Forwarding no
AllowTcpForwarding no
AllowAgentForwarding no
ClientAliveInterval 300
ClientAliveCountMax 3
LogLevel VERBOSE
SSHCONF
sshd -t && systemctl restart ssh && echo "SSH hardened OK"
SCRIPT
```

**Шаг 2:** Проверить SSH:
```bash
ssh root@$VPS_IP "echo 'SSH works after hardening'"
```
Expected: `SSH works after hardening`

### Task 10.3: fail2ban

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
apt-get install -y -qq fail2ban
cat > /etc/fail2ban/jail.local << 'F2B'
[DEFAULT]
bantime = 1800
findtime = 600
maxretry = 5
banaction = ufw
ignoreip = 127.0.0.1/8

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 5
F2B
systemctl enable fail2ban
systemctl restart fail2ban
fail2ban-client status sshd
SCRIPT
```
Expected: jail `sshd` active, 0 banned

### Task 10.4: Sysctl hardening

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
cat > /etc/sysctl.d/99-security.conf << 'SYSCTL'
net.ipv4.tcp_syncookies = 1
net.ipv4.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv4.conf.all.send_redirects = 0
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1
net.ipv4.icmp_echo_ignore_broadcasts = 1
net.ipv4.conf.all.log_martians = 1
kernel.randomize_va_space = 2
SYSCTL
sysctl --system > /dev/null 2>&1
echo "Sysctl hardened"
SCRIPT
```

### Task 10.5: Автоматические обновления безопасности

**Шаг 1:**
```bash
ssh root@$VPS_IP 'bash -s' << 'SCRIPT'
export DEBIAN_FRONTEND=noninteractive
apt-get install -y -qq unattended-upgrades
cat > /etc/apt/apt.conf.d/20auto-upgrades << 'AUTO'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
APT::Periodic::AutocleanInterval "7";
AUTO
echo "Auto-updates configured"
SCRIPT
```

---

## Фаза 11: Финальная проверка

### Task 11.1: Полный чеклист

**Шаг 1:** Производительность:
```bash
curl -w "HTTP: %{http_code} | TTFB: %{time_starttransfer}s | Total: %{time_total}s\n" \
  -o /dev/null -s "https://tdpuls.com/"
```
Expected: HTTP 200, TTFB < 0.5s

**Шаг 2:** FastCGI cache:
```bash
curl -sI "https://tdpuls.com/" | grep -i "x-fastcgi-cache"
sleep 1
curl -sI "https://tdpuls.com/" | grep -i "x-fastcgi-cache"
```
Expected: MISS → HIT

**Шаг 3:** Ключевые страницы:
```bash
for url in "/" "/shop/" "/wp-admin/" "/product-category/uzi-apparaty/"; do
  code=$(curl -sI -o /dev/null -w "%{http_code}" "https://tdpuls.com${url}")
  echo "https://tdpuls.com${url} → $code"
done
```
Expected: 200 или 302

**Шаг 4:** Ресурсы:
```bash
ssh root@$VPS_IP 'free -m && echo "---" && df -h / && echo "---" && systemctl is-active nginx php8.1-fpm mariadb redis-server fail2ban'
```
Expected: RAM < 3 ГБ, disk < 50%, все active

**Шаг 5:** В браузере:
- [ ] Главная загружается
- [ ] Каталог товаров работает
- [ ] Карточка товара открывается
- [ ] «Получить КП» → попап → лид в Битрикс24
- [ ] wp-admin доступен
- [ ] SSL (замочек в адресной строке)

### Task 11.2: Очистка

```bash
ssh root@$VPS_IP "rm -f /tmp/tdpuls-db.sql"
rm -f /tmp/db-export.php /tmp/tdpuls-db.sql
rm -rf /tmp/mchost-backup
```

---

## Чеклист отката

1. **DNS** → вернуть A-запись на старый IP mchost
2. **Старый сайт на mchost** → НЕ УДАЛЯТЬ минимум 2 недели
3. **Бэкап БД** → `/tmp/tdpuls-db.sql` сохранить локально
4. **wp-config.php** → `wp-config.php.mchost-backup` на VPS

---

## Порядок и время

| Фаза | Что | Зависимости | ~Время |
|------|-----|-------------|--------|
| 1 | SSH-ключи | Переустановка ОС | 3 мин |
| 2 | Swap + обновление | Фаза 1 | 5 мин |
| 3 | Установка стека | Фаза 2 | 10 мин |
| 4 | Конфигурация стека | Фаза 3 | 5 мин |
| 5 | nginx | Фаза 4 | 5 мин |
| 6 | Подготовка WP | Фаза 4 | 3 мин |
| 7 | Миграция данных | Фазы 5, 6 | 20-40 мин |
| 8 | DNS + SSL | Фаза 7 + пользователь | 10-30 мин |
| 9 | Redis в WP | Фаза 8 | 3 мин |
| 10 | Безопасность | Фаза 9 | 5 мин |
| 11 | Проверка | Фаза 10 | 10 мин |
| | **ИТОГО** | | **~80-120 мин** |
