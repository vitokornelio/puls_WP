> **УСТАРЕЛО** (2026-02-09): Этот файл описывает работу через FTP на mchost.ru. Сайт перенесён на VPS Beget 08.02.2026. Актуальные команды: см. [CHECKLIST.md](CHECKLIST.md).

# Команды обслуживания (mchost.ru — архив)

## FTP (mchost.ru — НЕ АКТИВЕН)

```bash
# Доступы: см. docs/credentials.md
curl -s --user "USER:PASS" "ftp://a265896.ftp.mchost.ru/httpdocs/"
```

---

## Проверка производительности

```bash
# TTFB и время загрузки
curl -w "TTFB: %{time_starttransfer}s | Total: %{time_total}s\n" \
  -o /dev/null -s "https://tdpuls.com/"

# GZIP
curl -sI -H "Accept-Encoding: gzip" "https://tdpuls.com/" | grep content-encoding

# Кэширование
curl -sI "https://tdpuls.com/wp-content/uploads/woocommerce-placeholder.png" | grep cache-control

# HTTP статус
curl -sI "https://tdpuls.com/" | head -1
```

---

## Яндекс.Вордстат API

```bash
# Токен: см. docs/credentials.md
TOKEN="<TOKEN>"

# Частотность запросов
curl -s -XPOST \
  -H "Content-type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"phrases":["узи аппарат","мрт аппарат"],"numPhrases":30}' \
  https://api.wordstat.yandex.net/v1/topRequests

# Проверка квоты
curl -s -H "Authorization: Bearer $TOKEN" \
  https://api.wordstat.yandex.net/v1/userInfo
```

---

*Обновлено: 2026-02-09*
