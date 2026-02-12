#!/bin/bash
# Import VSUZI products to WooCommerce
# Run on server: bash /tmp/import-vsuzi-products.sh

set -e
WP="wp --allow-root"
cd /var/www/tdpuls.com/public

UPLOADS_DIR="/var/www/tdpuls.com/public/wp-content/uploads/vsuzi"

echo "=== Создание товаров ВСУЗИ Philips Volcano ==="
echo ""

# Ensure Philips manufacturer term exists
$WP term create pa_proizvoditel "Philips" --slug=philips 2>/dev/null || echo "Philips already exists"

# Get category IDs
IR_CAT=$($WP term list product_cat --slug=interventsionnaya-rentgenologiya --field=term_id 2>/dev/null || echo "")
KATETERY_CAT=$($WP term list product_cat --slug=katetery --field=term_id 2>/dev/null || echo "")
IS_CAT=$($WP term list product_cat --slug=informatsionnye-sistemy --field=term_id 2>/dev/null || echo "")

echo "Categories: IR=$IR_CAT, Katetery=$KATETERY_CAT, IS=$IS_CAT"
echo ""

# Function: upload image and return attachment ID
upload_image() {
  local FILE_PATH="$1"
  local TITLE="$2"

  if [ ! -f "$FILE_PATH" ]; then
    echo "0"
    return
  fi

  # Use wp media import
  ATTACH_ID=$($WP media import "$FILE_PATH" --title="$TITLE" --porcelain 2>/dev/null || echo "0")
  echo "$ATTACH_ID"
}

# Function: create product
create_vsuzi_product() {
  local TITLE="$1"
  local SLUG="$2"
  local EXCERPT="$3"
  local CONTENT="$4"
  local SKU="$5"
  local IMAGE_FILE="$6"
  local CAT_IDS="$7"
  local SEO_TITLE="$8"
  local SEO_DESC="$9"

  echo "→ $TITLE"

  # Check if already exists
  EXISTING=$($WP post list --post_type=product --name="$SLUG" --field=ID 2>/dev/null || echo "")
  if [ -n "$EXISTING" ]; then
    echo "  [=] Уже существует (ID: $EXISTING), пропускаем"
    echo ""
    return
  fi

  # Create product
  POST_ID=$($WP post create \
    --post_type=product \
    --post_title="$TITLE" \
    --post_name="$SLUG" \
    --post_excerpt="$EXCERPT" \
    --post_content="$CONTENT" \
    --post_status=publish \
    --porcelain)

  echo "  [+] Создан (ID: $POST_ID)"

  # Set product type
  $WP post term set $POST_ID product_type simple

  # Set categories
  if [ -n "$CAT_IDS" ]; then
    $WP post term set $POST_ID product_cat $CAT_IDS
  fi

  # Set manufacturer
  $WP post term set $POST_ID pa_proizvoditel philips

  # WooCommerce meta
  $WP post meta update $POST_ID _visibility visible
  $WP post meta update $POST_ID _stock_status instock
  $WP post meta update $POST_ID _virtual no
  $WP post meta update $POST_ID _downloadable no
  $WP post meta update $POST_ID _manage_stock no
  $WP post meta update $POST_ID _sku "$SKU"

  # Product attributes meta
  $WP eval "
    \$attrs = array(
      'pa_proizvoditel' => array('name'=>'pa_proizvoditel','value'=>'','position'=>0,'is_visible'=>1,'is_variation'=>0,'is_taxonomy'=>1),
    );
    update_post_meta($POST_ID, '_product_attributes', \$attrs);
  "

  # Upload image
  if [ -n "$IMAGE_FILE" ] && [ -f "$UPLOADS_DIR/$IMAGE_FILE" ]; then
    ATTACH_ID=$(upload_image "$UPLOADS_DIR/$IMAGE_FILE" "$TITLE")
    if [ "$ATTACH_ID" != "0" ] && [ -n "$ATTACH_ID" ]; then
      $WP post meta update $POST_ID _thumbnail_id "$ATTACH_ID"
      echo "  [+] Изображение (attach ID: $ATTACH_ID)"
    fi
  fi

  # Rank Math SEO
  if [ -n "$SEO_TITLE" ]; then
    $WP post meta update $POST_ID rank_math_title "$SEO_TITLE"
  fi
  if [ -n "$SEO_DESC" ]; then
    $WP post meta update $POST_ID rank_math_description "$SEO_DESC"
  fi

  echo ""
}

# ─── Product 1: Refinity ───
create_vsuzi_product \
  "Refinity — Ротационный катетер для ВСУЗИ" \
  "refinity-rotatsionnyj-kateter-dlya-vsuzi" \
  "Ротационный IVUS-катетер нового поколения Philips Volcano. Частота 45 МГц для максимально чёткого изображения. GlyDx-покрытие, мягкий гибкий кончик. Совместим с радиальным доступом и направляющими катетерами от 5F." \
  '<h2>Refinity — ротационный IVUS-катетер нового поколения</h2>
<p>Philips Refinity представляет новое поколение ротационных катетеров для внутрисосудистого ультразвукового исследования (ВСУЗИ). Рабочая частота 45 МГц обеспечивает максимально чёткое и детализированное изображение стенки сосуда.</p>

<h3>Ключевые преимущества</h3>
<ul>
<li><strong>45 МГц</strong> — высокое разрешение для детальной визуализации структуры бляшки и стенки сосуда</li>
<li><strong>Исключительная лёгкость доставки</strong> — низкопрофильный кончик и GlyDx гидрофильное покрытие</li>
<li><strong>Радиальный доступ</strong> — совместим с лучевым доступом для сложных ЧКВ</li>
<li><strong>Мягкий гибкий кончик</strong> — безопасная навигация в дистальных и извитых сегментах</li>
<li><strong>Совместимость с 5F</strong> — минимальный размер направляющего катетера</li>
</ul>

<h3>Технические характеристики</h3>
<table>
<tr><td>Частота</td><td>45 МГц</td></tr>
<tr><td>Диаметр шафта</td><td>3.0 F</td></tr>
<tr><td>Мин. направляющий катетер</td><td>5F (≥ 0.065")</td></tr>
<tr><td>Совместимость с проводником</td><td>0.014"</td></tr>
<tr><td>Макс. глубина проникновения</td><td>14 мм</td></tr>
<tr><td>Рабочая длина</td><td>135 см</td></tr>
<tr><td>Расстояние кончик—датчик</td><td>20.5 мм</td></tr>
</table>' \
  "989604315691" \
  "refinity.webp" \
  "$IR_CAT $KATETERY_CAT" \
  "Refinity — ротационный IVUS-катетер 45 МГц | Купить в ТД Пульс" \
  "Philips Refinity — ротационный IVUS-катетер нового поколения. 45 МГц, GlyDx-покрытие, радиальный доступ, 3.0F. Поставка по РФ."

# ─── Product 2: Reconnaissance PV .018 OTW ───
create_vsuzi_product \
  "Reconnaissance PV .018 OTW — Цифровой катетер для ВСУЗИ" \
  "reconnaissance-pv-018-otw-tsifrovoj-kateter-dlya-vsuzi" \
  "Периферический цифровой IVUS-катетер Philips Volcano с доставкой по проводнику 0.018\" (OTW). Конический кончик, твёрдый сердечник, гидрофильное покрытие. Функция ChromaFlo для оценки периферических сосудов." \
  '<h2>Reconnaissance PV .018 OTW — периферический IVUS-катетер</h2>
<p>Philips Reconnaissance PV .018 OTW — уникальный цифровой IVUS-катетер с доставкой по проводнику (Over-The-Wire), оптимизированный для визуализации периферических сосудов.</p>

<h3>Ключевые преимущества</h3>
<ul>
<li><strong>OTW-доставка по проводнику 0.018"</strong> — стабильная навигация в периферических сосудах</li>
<li><strong>Конический кончик и твёрдый сердечник</strong> — уверенное продвижение через сложные поражения</li>
<li><strong>Гидрофильное покрытие</strong> — улучшенное скольжение и трекинг</li>
<li><strong>ChromaFlo</strong> — визуализация кровотока красным цветом для оценки прилегания стента</li>
<li><strong>Совместимость с 5F</strong> — работает с катетерами от 5 French</li>
</ul>

<h3>Технические характеристики</h3>
<table>
<tr><td>Частота</td><td>20 МГц</td></tr>
<tr><td>Диаметр проводника</td><td>0.018"</td></tr>
<tr><td>Мин. направляющий катетер</td><td>5 F</td></tr>
<tr><td>Макс. диаметр визуализации</td><td>20 мм</td></tr>
<tr><td>Рабочая длина</td><td>150 см</td></tr>
<tr><td>Тип доставки</td><td>Over-The-Wire (OTW)</td></tr>
</table>' \
  "RPV018OTW" \
  "reconnaissance-pv.webp" \
  "$IR_CAT $KATETERY_CAT" \
  "Reconnaissance PV .018 OTW — периферический IVUS-катетер | ТД Пульс" \
  "Philips Reconnaissance PV .018 OTW — периферический IVUS-катетер, OTW 0.018\", 20 МГц, ChromaFlo. Поставка по РФ."

# ─── Product 3: OmniWire ───
create_vsuzi_product \
  "OmniWire — Проводник с датчиком давления" \
  "omniwire-provodnik-s-datchikom-davleniya" \
  "Проводник с датчиком давления Philips OmniWire для измерения FFR и iFR. Жёсткий сердечник проксимальной части, нитиноловый дистальный сегмент. Ко-регистрация данных iFR на ангиограмме. РЗН 2023/21858." \
  '<h2>Philips OmniWire — проводник с датчиком давления FFR/iFR</h2>
<p>OmniWire объединяет уникальную конструкцию с жёстким сердечником и подтверждённую прогностическую эффективность FFR и iFR, а также возможность ко-регистрации данных непосредственно на ангиограмме.</p>

<h3>Конструкция</h3>
<ul>
<li><strong>Жёсткий сердечник проксимальной части</strong> — характеристики, схожие с рабочим проводником; встроенные токопроводящие ленты повышают крутящий момент и устойчивость к изгибам</li>
<li><strong>Нитиноловый сердечник дистальной части</strong> — восстановление формы, удобство при длительных процедурах с множественными поражениями; цельное строение без стыков</li>
<li><strong>Ко-регистрация iFR</strong> — значения отображаются непосредственно на ангиограмме, помогая выявить области, вызывающие ишемию</li>
</ul>

<h3>Технические характеристики</h3>
<table>
<tr><td>Измеряемые индексы</td><td>FFR, iFR</td></tr>
<tr><td>Диаметр</td><td>0.014"</td></tr>
<tr><td>Длина</td><td>185 см</td></tr>
<tr><td>Варианты кончика</td><td>Прямой (арт. 89185), J-образный (арт. 89185J)</td></tr>
<tr><td>Ко-регистрация</td><td>iFR на ангиограмме</td></tr>
<tr><td>Регистрационное удостоверение</td><td>РЗН 2023/21858 от 05.07.2024</td></tr>
</table>' \
  "89185" \
  "omniwire.webp" \
  "$IR_CAT $KATETERY_CAT" \
  "OmniWire — проводник давления FFR/iFR Philips | ТД Пульс" \
  "Philips OmniWire — проводник с датчиком давления для FFR и iFR. 0.014\", 185 см, ко-регистрация iFR. РЗН 2023/21858. Поставка по РФ."

# ─── Product 4: SyncVision ───
create_vsuzi_product \
  "SyncVision — Система точной навигации" \
  "syncvision-sistema-tochnoj-navigatsii" \
  "Программное обеспечение Philips SyncVision для ко-регистрации IVUS и iFR с ангиограммой в реальном времени. Функция Angio+ улучшает визуализацию извитых сосудов. Автоматический расчёт размеров просвета и степени стеноза." \
  '<h2>SyncVision — ПО для ко-регистрации и высокоточного анализа</h2>
<p>Philips SyncVision — программное обеспечение для высокоточной обработки изображений, ко-регистрации и три-регистрации данных IVUS и iFR с ангиограммой. Оптимизирует оценку поражений, определение размеров сосудов и точную доставку терапии.</p>

<h3>Ключевые функции</h3>
<ul>
<li><strong>Angio+</strong> — улучшенная визуализация извитых, перекрывающихся и стенозированных сосудов; потенциально снижает количество снимков при диагностике</li>
<li><strong>Ко-регистрация iFR</strong> — устраняет необходимость в гиперемических агентах и устройствах pullback; повышает надёжность диагностики</li>
<li><strong>Ко-регистрация IVUS</strong> — совмещает IVUS-изображения с ангиограммой для комплексной оценки</li>
<li><strong>iFR Scout</strong> — технология pullback для выявления значимых градиентов при диффузных проксимальных поражениях</li>
<li><strong>Автоматический расчёт</strong> — размеры просвета и степень стеноза в реальном времени</li>
</ul>

<h3>Клинические применения</h3>
<ul>
<li>Визуализация позиционирования устройств для снижения риска смещения</li>
<li>Мониторинг раскрытия баллона при инфляции</li>
<li>Оптимизация стентирования с использованием флюороскопии</li>
<li>Оценка бляшки: объём, морфология, кальцификация, тромбоз</li>
</ul>' \
  "SYNCVISION" \
  "syncvision.webp" \
  "$IS_CAT" \
  "SyncVision — ПО для ко-регистрации IVUS и iFR | ТД Пульс" \
  "Philips SyncVision — ПО для ко-регистрации IVUS и iFR с ангиограммой. Angio+, iFR Scout, автоматический расчёт стеноза. Поставка по РФ."

# ─── Update hub links ───
echo "=== Обновление ссылок в hub ==="

HUB_FILE="$THEME_DIR/page-vsuzi-hub.php"

# Refinity
ssh $SERVER "sed -i \"/'title' => 'Refinity',/{n;n;n;n;s|'url' => '#b24-modal'|'url' => '/shop/interventsionnaya-rentgenologiya/katetery/refinity-rotatsionnyj-kateter-dlya-vsuzi/'|}\" $HUB_FILE" && echo "  [+] Refinity URL" || echo "  [-] Refinity URL (возможно уже обновлён)"

# Reconnaissance PV
ssh $SERVER "sed -i \"/'title' => 'Reconnaissance PV .018 OTW',/{n;n;n;n;s|'url' => '#b24-modal'|'url' => '/shop/interventsionnaya-rentgenologiya/katetery/reconnaissance-pv-018-otw-tsifrovoj-kateter-dlya-vsuzi/'|}\" $HUB_FILE" && echo "  [+] Reconnaissance PV URL" || echo "  [-] Reconnaissance PV URL"

# OmniWire
ssh $SERVER "sed -i \"/'title' => 'OmniWire',/{n;n;n;n;s|'url' => '#b24-modal'|'url' => '/shop/interventsionnaya-rentgenologiya/katetery/omniwire-provodnik-s-datchikom-davleniya/'|}\" $HUB_FILE" && echo "  [+] OmniWire URL" || echo "  [-] OmniWire URL"

# SyncVision
ssh $SERVER "sed -i \"/'title' => 'SyncVision',/{n;n;n;n;s|'url' => '#b24-modal'|'url' => '/shop/informatsionnye-sistemy/syncvision-sistema-tochnoj-navigatsii/'|}\" $HUB_FILE" && echo "  [+] SyncVision URL" || echo "  [-] SyncVision URL"

echo ""
echo "=== Импорт ВСУЗИ завершён ==="
echo "Проверьте товары: https://tdpuls.com/wp-admin/edit.php?post_type=product&s=vsuzi"
echo ""
