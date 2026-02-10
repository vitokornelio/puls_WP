#!/bin/bash
# Import Mindray UZI products to WooCommerce
# Run on server: bash /tmp/import-mindray-uzi.sh

set -e
WP="wp --allow-root"
cd /var/www/tdpuls.com/public

echo "=== 1. Creating attribute terms ==="

# Manufacturer
$WP term create pa_proizvoditel "Mindray" --slug=mindray 2>/dev/null || echo "Mindray already exists"
MINDRAY_ID=$($WP term list pa_proizvoditel --slug=mindray --field=term_id)
echo "Mindray manufacturer ID: $MINDRAY_ID"

# Diagnostic class: add "Премиальный" and "Средний"
$WP term create pa_diagnosticheskij-klass "Премиальный" --slug=premialnyj 2>/dev/null || echo "Премиальный already exists"
$WP term create pa_diagnosticheskij-klass "Средний" --slug=srednij 2>/dev/null || echo "Средний already exists"

# Type: add "Планшетный"
$WP term create pa_tip-apparata "Планшетный" --slug=planshetnyj 2>/dev/null || echo "Планшетный already exists"

# Product lines (pa_linejka)
for LINE in "Resona:resona" "Consona:consona" "Imagyn:imagyn" "Recho:recho" "MX:mx" "TEAir:teair" "DC:dc" "ME:me" "M:m-series" "TE:te"; do
  NAME="${LINE%%:*}"
  SLUG="${LINE##*:}"
  $WP term create pa_linejka "$NAME" --slug="$SLUG" 2>/dev/null || echo "$NAME already exists"
done

# Назначение: add missing
$WP term create pa_naznachenie "Акушерство и гинекология" --slug=akusherstvo-i-ginekologiya 2>/dev/null || echo "Already exists"
$WP term create pa_naznachenie "Анестезиология и реанимация" --slug=anesteziologiya-i-reanimatsiya 2>/dev/null || echo "Already exists"
$WP term create pa_naznachenie "Онкология" --slug=onkologiya 2>/dev/null || echo "Already exists"

echo ""
echo "=== 2. Creating products ==="

create_product() {
  local TITLE="$1"
  local SLUG="$2"
  local EXCERPT="$3"
  local CONTENT="$4"
  local PRICE="$5"
  local CLASS_SLUG="$6"
  local TYPE_SLUG="$7"
  local LINE_SLUG="$8"
  local NAZNACHENIE="$9"
  local SEO_TITLE="${10}"
  local SEO_DESC="${11}"

  echo "Creating: $TITLE..."

  # Create product
  POST_ID=$($WP post create \
    --post_type=product \
    --post_title="$TITLE" \
    --post_name="$SLUG" \
    --post_excerpt="$EXCERPT" \
    --post_content="$CONTENT" \
    --post_status=draft \
    --porcelain)

  echo "  Created post ID: $POST_ID"

  # Set product type
  $WP post term set $POST_ID product_type simple

  # Set category: УЗИ системы (19) + parent УЛЬТРАЗВУКОВАЯ ДИАГНОСТИКА (18)
  $WP post term set $POST_ID product_cat 19 18

  # Set attributes as taxonomy terms
  $WP post term set $POST_ID pa_proizvoditel mindray
  $WP post term set $POST_ID pa_diagnosticheskij-klass "$CLASS_SLUG"
  $WP post term set $POST_ID pa_tip-apparata "$TYPE_SLUG"
  $WP post term set $POST_ID pa_linejka "$LINE_SLUG"

  # Set назначение (can be multiple)
  IFS=',' read -ra NAZN_ARRAY <<< "$NAZNACHENIE"
  $WP post term set $POST_ID pa_naznachenie "${NAZN_ARRAY[@]}"

  # WooCommerce meta
  $WP post meta update $POST_ID _visibility visible
  $WP post meta update $POST_ID _stock_status instock
  $WP post meta update $POST_ID _virtual no
  $WP post meta update $POST_ID _downloadable no
  $WP post meta update $POST_ID _manage_stock no
  $WP post meta update $POST_ID _sku "mindray-$SLUG"

  if [ "$PRICE" != "0" ]; then
    $WP post meta update $POST_ID _regular_price "$PRICE"
    $WP post meta update $POST_ID _price "$PRICE"
  fi

  # Product attributes meta (for WooCommerce to display them)
  $WP eval "
    \$attrs = array(
      'pa_diagnosticheskij-klass' => array('name'=>'pa_diagnosticheskij-klass','value'=>'','position'=>0,'is_visible'=>1,'is_variation'=>0,'is_taxonomy'=>1),
      'pa_proizvoditel' => array('name'=>'pa_proizvoditel','value'=>'','position'=>1,'is_visible'=>1,'is_variation'=>0,'is_taxonomy'=>1),
      'pa_tip-apparata' => array('name'=>'pa_tip-apparata','value'=>'','position'=>2,'is_visible'=>1,'is_variation'=>0,'is_taxonomy'=>1),
      'pa_linejka' => array('name'=>'pa_linejka','value'=>'','position'=>3,'is_visible'=>1,'is_variation'=>0,'is_taxonomy'=>1),
      'pa_naznachenie' => array('name'=>'pa_naznachenie','value'=>'','position'=>4,'is_visible'=>1,'is_variation'=>0,'is_taxonomy'=>1),
    );
    update_post_meta($POST_ID, '_product_attributes', \$attrs);
  "

  # Rank Math SEO
  $WP post meta update $POST_ID rank_math_title "$SEO_TITLE"
  $WP post meta update $POST_ID rank_math_description "$SEO_DESC"

  echo "  Done: $TITLE (ID: $POST_ID)"
}

# =============================================
# PRODUCT DATA
# =============================================

# 1. Resona i9W
create_product \
  "Mindray Resona i9W — УЗИ аппарат" \
  "mindray-resona-i9w" \
  "Экспертная ультразвуковая система нового поколения на платформе ZST+ с технологиями UMA, iFusion и V Flow для многопрофильной диагностики" \
  '<h2>Mindray Resona i9W — экспертная УЗИ система</h2>
<p>Ультразвуковая диагностическая система экспертного класса нового поколения, построенная на платформе ZST+ (Zone Sonography Technology Plus). Обеспечивает высочайшее качество визуализации, интеллектуальную автоматизацию и широкий набор клинических инструментов.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 23.8" LED высокого разрешения</li>
<li>Сенсорный экран 15.6" с распознаванием жестов</li>
<li>5 активных портов для датчиков</li>
<li>Гибридный накопитель: 1TB HDD + 128GB SSD</li>
<li>Автономная работа до 4 часов</li>
<li>Внесён в Государственный реестр российской промышленной продукции</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>UMA</strong> — ультрачувствительная микрососудистая визуализация</li>
<li><strong>iFusion</strong> — совмещение УЗ-изображения с КТ/МРТ в реальном времени</li>
<li><strong>V Flow</strong> — векторное картирование кровотока</li>
<li><strong>Smart-модули</strong> — автоматический анализ образований и биометрия</li>
<li><strong>STIC и Smart Volume</strong> — 4D-визуализация сердца и анатомии плода</li>
<li><strong>Эластография</strong> — компрессионная и сдвиговой волны</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Кардиология (эхокардиография, стресс-эхо)</li>
<li>Акушерство и гинекология (биометрия плода, 4D-визуализация)</li>
<li>Гепатология (оценка стеатоза печени)</li>
<li>Онкология (щитовидная железа, молочные железы, яичники)</li>
<li>Ангиология (микрососудистая визуализация)</li>
<li>Радиология (совмещённые исследования с КТ/МРТ)</li>
</ul>' \
  "0" \
  "ekspertnyj" \
  "statsionarnyj" \
  "resona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,obshhaya-vizualizatsiya" \
  "Mindray Resona i9W — купить экспертную УЗИ систему по выгодной цене" \
  "Mindray Resona i9W — экспертная ультразвуковая система на платформе ZST+ с технологиями UMA, iFusion, V Flow. Многопрофильная диагностика: кардиология, акушерство, онкология."

# 2. Resona 7s
create_product \
  "Mindray Resona 7s — УЗИ аппарат" \
  "mindray-resona-7s" \
  "Премиальная ультразвуковая система на платформе ZST+ с эластографией сдвиговой волны и интеграцией КТ/МРТ" \
  '<h2>Mindray Resona 7s — премиальная УЗИ система</h2>
<p>Ультразвуковая система премиум-уровня, объединяющая передовые технологии и максимальную диагностическую гибкость. Построена на платформе ZST+ с канальной обработкой и зонным сканированием.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 21.5" LED высокого разрешения</li>
<li>Сенсорный экран 13.3" с распознаванием жестов</li>
<li>4 активных порта для датчиков</li>
<li>Гибридный накопитель: 1TB HDD + 120GB SSD</li>
<li>Встроенный Wi-Fi адаптер и подогреватель геля</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Sound Touch Elastography</strong> — эластография сдвиговой волны</li>
<li><strong>iFusion</strong> — совмещение УЗ с КТ/МРТ</li>
<li><strong>Tissue Tracking</strong> — оценка деформации миокарда</li>
<li><strong>Dynamic Pixel Focusing</strong> — автоматическая фокусировка</li>
<li>2D/3D/4D визуализация, контрастное УЗИ</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Кардиология</li>
<li>Акушерство и гинекология</li>
<li>Ангиология</li>
<li>Онкология</li>
<li>Неонатология и педиатрия</li>
</ul>' \
  "3724000" \
  "premialnyj" \
  "statsionarnyj" \
  "resona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,obshhaya-vizualizatsiya" \
  "Mindray Resona 7s — купить премиальную УЗИ систему по выгодной цене" \
  "Mindray Resona 7s — премиальная ультразвуковая система на платформе ZST+ от 3 724 000 ₽. Эластография, iFusion, 3D/4D. Кардиология, акушерство, онкология."

# 3. Consona N9P
create_product \
  "Mindray Consona N9P — УЗИ аппарат" \
  "mindray-consona-n9p" \
  "Ультразвуковая система высокого класса на платформе ZST+ с двумя сенсорными экранами. Произведено в России" \
  '<h2>Mindray Consona N9P — УЗИ система высокого класса</h2>
<p>Новинка линейки Consona, произведённая в России. Построена на инновационной платформе ZST+ с двумя сенсорными экранами 15.6" и 5 активными портами для датчиков.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Full HD LED монитор 23.8"</li>
<li>Два сенсорных экрана 15.6" с распознаванием жестов</li>
<li>5 активных портов для датчиков</li>
<li>Встроенный жесткий диск 1 ТБ</li>
<li>Встроенная батарея — 1 час работы</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart OB</strong> — автоматические измерения биометрии плода</li>
<li><strong>HR-flow</strong> — визуализация микрососудов высокого разрешения</li>
<li><strong>Glazing Flow</strong> — трёхмерная визуализация кровотока</li>
<li><strong>Smart Thyroid / Smart Breast</strong> — анализ по TI-RADS и BI-RADS</li>
<li><strong>Эластография</strong> — компрессионная и сдвиговая</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Акушерство — раннее выявление врождённых пороков развития</li>
<li>Онкология — дифференциация образований</li>
<li>Кардиология — контрастные методики</li>
<li>Общая диагностика</li>
</ul>' \
  "0" \
  "vysokij" \
  "statsionarnyj" \
  "consona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray Consona N9P — купить УЗИ систему высокого класса" \
  "Mindray Consona N9P — ультразвуковая система высокого класса на платформе ZST+. Произведено в России. Smart OB, HR-flow, эластография."

# 4. Consona N7P
create_product \
  "Mindray Consona N7P — УЗИ аппарат" \
  "mindray-consona-n7p" \
  "Универсальная ультразвуковая система высокого класса на платформе ZST+ с 5 портами для датчиков. Произведено в России" \
  '<h2>Mindray Consona N7P — универсальная УЗИ система</h2>
<p>Система высокого класса, обеспечивающая высокое качество изображений благодаря технологии ZST+ и интеллектуальным функциям автоматического анализа. Произведена в России.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Full HD LED монитор 21.5"</li>
<li>Сенсорный экран 13.3" с распознаванием жестов</li>
<li>5 активных портов для датчиков</li>
<li>Встроенный жесткий диск 1 ТБ</li>
<li>Встроенная батарея — 1 час работы</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart OB</strong> — автоматическое измерение биометрии плода</li>
<li><strong>Smart HRI</strong> — определение гепаторенального индекса</li>
<li><strong>iNeedle</strong> — визуализация биопсийной иглы</li>
<li><strong>Glazing Flow</strong> — 3D-визуализация кровотока</li>
<li><strong>Эластография</strong> — STE, STQ, компрессионная</li>
<li><strong>Tissue Tracking QA, AutoEF, Stress Echo</strong></li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Абдоминальные исследования</li>
<li>Кардиология и сосуды</li>
<li>Акушерство и гинекология</li>
<li>Педиатрия</li>
</ul>' \
  "0" \
  "vysokij" \
  "statsionarnyj" \
  "consona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,obshhaya-vizualizatsiya" \
  "Mindray Consona N7P — купить универсальную УЗИ систему высокого класса" \
  "Mindray Consona N7P — универсальная ультразвуковая система высокого класса на ZST+. Произведено в России. Smart OB, iNeedle, эластография."

# 5. Resona R9
create_product \
  "Mindray Resona R9 — УЗИ аппарат" \
  "mindray-resona-r9" \
  "Премиальная ультразвуковая система на платформе ZST+ с технологией Ultra Micro Angiography для сложных клинических случаев" \
  '<h2>Mindray Resona R9 — премиальная УЗИ система</h2>
<p>Стационарная ультразвуковая система премиального уровня для решения широкого спектра клинических задач. Построена на платформе ZST+ с высокой детализацией и дифференциацией тканей.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 23.8" LED высокого разрешения</li>
<li>Сенсорный экран 13.3" с распознаванием жестов</li>
<li>Гибридный накопитель: 1TB HDD + 120GB SSD</li>
<li>Более 20 типов совместимых датчиков</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Ultra Micro Angiography</strong> — визуализация микроциркуляции</li>
<li><strong>HiFR CEUS</strong> — высокочастотный контрастный режим</li>
<li><strong>M-Reference</strong> — комплексный подход к диагностике</li>
<li><strong>Количественная оценка</strong> стеатоза и фиброза печени</li>
<li><strong>STE</strong> — эластография сдвиговой волны</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Абдоминальная диагностика</li>
<li>Онкология и эндокринология</li>
<li>Гепатология</li>
<li>Сосудистые исследования</li>
</ul>' \
  "6463000" \
  "premialnyj" \
  "statsionarnyj" \
  "resona" \
  "obshhie-issledovaniya,onkologiya,obshhaya-vizualizatsiya" \
  "Mindray Resona R9 — купить премиальную УЗИ систему" \
  "Mindray Resona R9 — премиальная ультразвуковая система от 6 463 000 ₽. Ultra Micro Angiography, HiFR CEUS, оценка стеатоза/фиброза печени."

# 6. Imagyn R9
create_product \
  "Mindray Imagyn R9 — УЗИ аппарат" \
  "mindray-imagyn-r9" \
  "Премиальная ультразвуковая система для женского здоровья — акушерство, гинекология, репродуктивные технологии и ЭКО" \
  '<h2>Mindray Imagyn R9 — премиальная УЗИ система для женского здоровья</h2>
<p>Специализированная стационарная система премиального уровня для клинических задач в сфере женского здоровья, включая акушерство, гинекологию и репродуктивные технологии. Построена на платформе ZST+.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 23.8" LED высокого разрешения</li>
<li>Сенсорный экран 13.3" с распознаванием жестов</li>
<li>Гибридный накопитель: 1TB HDD + 120GB SSD</li>
<li>16 типов совместимых датчиков</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>SSC</strong> — автоматическая оптимизация скорости волн</li>
<li><strong>Focus Free</strong> — распределённая фокусировка</li>
<li><strong>Smart Doppler, Smart Track, iTouch</strong> — автоматическая оптимизация</li>
<li>3D-визуализация и эластография</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Комплексная оценка плода</li>
<li>Диагностика эндометрия и яичников</li>
<li>Исследования органов малого таза</li>
<li>ЭКО и репродуктивные технологии</li>
</ul>' \
  "6090000" \
  "premialnyj" \
  "statsionarnyj" \
  "imagyn" \
  "zhenskoe-zdorove,akusherstvo-i-ginekologiya" \
  "Mindray Imagyn R9 — купить премиальную УЗИ систему для акушерства" \
  "Mindray Imagyn R9 — премиальная УЗИ система для женского здоровья от 6 090 000 ₽. Акушерство, гинекология, репродуктология, ЭКО."

# 7. Recho N9
create_product \
  "Mindray Recho N9 — УЗИ аппарат" \
  "mindray-recho-n9" \
  "Экспертная ультразвуковая система для кардиологии и ангиологии с Auto EF и стресс-эхокардиографией" \
  '<h2>Mindray Recho N9 — экспертная УЗИ система для кардиологии</h2>
<p>Система экспертного класса, специально разработанная для точной и быстрой диагностики в кардиологии и ангиологии. Объединяет высокое качество визуализации и интеллектуальные функции на платформе ZST+.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Сенсорный экран 15.6" с регулируемым углом наклона</li>
<li>5 активных портов для датчиков</li>
<li>SSD 512 ГБ</li>
<li>Встроенная батарея — 1 час работы</li>
<li>Wi-Fi адаптер</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Auto EF</strong> — автоматический расчёт фракции выброса</li>
<li><strong>Stress Echo</strong> — протокол стресс-эхокардиографии</li>
<li><strong>TDI QA</strong> — количественный анализ тканевого допплера</li>
<li><strong>R-VQS</strong> — анализ жёсткости сосудистой стенки</li>
<li>3D/4D визуализация</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Кардиология</li>
<li>Ангиология</li>
<li>Акушерство</li>
<li>Общая диагностика</li>
</ul>' \
  "2204000" \
  "ekspertnyj" \
  "statsionarnyj" \
  "recho" \
  "kardiologiya,obshhie-issledovaniya" \
  "Mindray Recho N9 — купить экспертную кардиологическую УЗИ систему" \
  "Mindray Recho N9 — экспертная УЗИ система для кардиологии от 2 204 000 ₽. Auto EF, стресс-эхо, TDI QA, R-VQS."

# 8. MX7
create_product \
  "Mindray MX7 — УЗИ аппарат" \
  "mindray-mx7" \
  "Экспертный портативный УЗИ-сканер на платформе ZST+ — один из самых лёгких на рынке с автономностью до 8 часов" \
  '<h2>Mindray MX7 — экспертный портативный УЗИ сканер</h2>
<p>Один из самых лёгких и тонких УЗИ-сканеров на рынке в формате ноутбука. Экспертный портативный аппарат на платформе ZST+ с автономностью до 8 часов.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Ультратонкий корпус в формате ноутбука</li>
<li>Накопитель 128 ГБ, выход HDMI</li>
<li>Автономная работа до 8 часов</li>
<li>Платформа ZST+</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>HD Scope</strong> — улучшение пространственного разрешения</li>
<li><strong>iBeam</strong> — многолучевое компаундирование</li>
<li><strong>iClear</strong> — адаптивное шумоподавление</li>
<li><strong>Smart OB / Smart NT</strong> — автоматическая биометрия плода</li>
<li><strong>TDI</strong> — тканевой допплер с анализом деформации</li>
<li><strong>R-IMT</strong> — измерение интима-медиа в реальном времени</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Онкология — компрессионная эластография</li>
<li>Кардиология — оценка клапанов и миокарда</li>
<li>Акушерство — патология по триместрам</li>
<li>Гинекология и урология</li>
</ul>' \
  "1650500" \
  "ekspertnyj" \
  "portativnyj" \
  "mx" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,obshhaya-vizualizatsiya" \
  "Mindray MX7 — купить экспертный портативный УЗИ сканер" \
  "Mindray MX7 — экспертный портативный УЗИ аппарат от 1 650 500 ₽. Платформа ZST+, до 8 часов автономности. Кардиология, акушерство, онкология."

# 9. TEAir i3P
create_product \
  "Mindray TEAir i3P — УЗИ аппарат" \
  "mindray-teair-i3p" \
  "Беспроводной портативный ультразвуковой зонд с защитой IP68 и магнитной зарядкой для экстренной медицины" \
  '<h2>Mindray TEAir i3P — беспроводной портативный УЗИ зонд</h2>
<p>Беспроводная портативная ультразвуковая система класса Point of Care, сочетающая компактность, интеллектуальную автоматизацию и высокое качество визуализации. Идеальна для работы в ограниченных пространствах.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Беспроводной зонд — без кабелей</li>
<li>Защита IP68 — водо- и пыленепроницаемость</li>
<li>Беспроводная зарядка и капсула хранения</li>
<li>Магнитный USB Type-A кабель для зарядки</li>
<li>Платформа eWave</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>iClear</strong> — адаптивное шумоподавление</li>
<li><strong>iTouch</strong> — автоматическая оптимизация изображения</li>
<li><strong>Smart Bladder</strong> — автоматическое вычисление объёма</li>
<li><strong>AutoEF</strong> — автоматический расчёт фракции выброса</li>
<li><strong>TDI</strong> — тканевой допплер</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Интенсивная терапия</li>
<li>Анестезиология</li>
<li>Экстренная медицина и травматология</li>
<li>Спортивная медицина</li>
</ul>' \
  "240000" \
  "bazovyj" \
  "portativnyj" \
  "teair" \
  "ekstrennaya-diagnostika,anesteziologiya-i-reanimatsiya" \
  "Mindray TEAir i3P — купить беспроводной портативный УЗИ зонд" \
  "Mindray TEAir i3P — беспроводной УЗИ зонд от 240 000 ₽. IP68, магнитная зарядка, Smart Bladder, AutoEF. Экстренная медицина, анестезиология."

# 10. Imagyn i9
create_product \
  "Mindray Imagyn i9 — УЗИ аппарат" \
  "mindray-imagyn-i9" \
  "Экспертная ультразвуковая система для акушерства и гинекологии с технологиями Smart Pelvic и Smart Hip на платформе ZST+" \
  '<h2>Mindray Imagyn i9 — экспертная УЗИ система для акушерства</h2>
<p>Аппарат экспертного уровня на платформе ZST+ с широким спектром технологий для качественной и количественной оценки в акушерстве, гинекологии и неонатологии.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 23.8" LED высокого разрешения</li>
<li>Сенсорный экран 15.6" с распознаванием жестов</li>
<li>5 портов для датчиков, USB, Type-C, DVD</li>
<li>Гибридный накопитель: 1TB HDD + 128GB SSD</li>
<li>Автономная работа до 4 часов</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart OB / Smart Face / Smart NT</strong> — акушерская автоматизация</li>
<li><strong>Smart Pelvic</strong> — мышцы тазового дна</li>
<li><strong>Smart Hip</strong> — диагностика дисплазии суставов</li>
<li><strong>iLive</strong> — виртуальная подсветка объёмных изображений</li>
<li><strong>Эластография</strong> — Natural Touch, Endocavity STE</li>
<li><strong>CEUS</strong> — контрастная визуализация</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Акушерство и гинекология</li>
<li>Неонатология</li>
<li>Кардиология</li>
<li>Общая диагностика</li>
</ul>' \
  "3792000" \
  "ekspertnyj" \
  "statsionarnyj" \
  "imagyn" \
  "zhenskoe-zdorove,akusherstvo-i-ginekologiya,obshhie-issledovaniya" \
  "Mindray Imagyn i9 — купить экспертную УЗИ систему для акушерства" \
  "Mindray Imagyn i9 — экспертная УЗИ система от 3 792 000 ₽. Smart Pelvic, Smart Hip, iLive, CEUS. Акушерство, гинекология, неонатология."

# 11. DC-80
create_product \
  "Mindray DC-80 — УЗИ аппарат" \
  "mindray-dc-80" \
  "Экспертная стационарная ультразвуковая система на платформе X-Insight с эластографией сдвиговой волны" \
  '<h2>Mindray DC-80 — экспертная УЗИ система</h2>
<p>Стационарный ультразвуковой сканер экспертного класса на платформе X-Insight для высокоточной диагностики различных органов и систем.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Full HD LED монитор 23.8"</li>
<li>Сенсорный экран 13.3"</li>
<li>4 активных порта для датчиков</li>
<li>Гибридный накопитель: 1TB HDD + 128GB SSD</li>
<li>Встроенные Wi-Fi адаптер и батарея</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Sound Touch Elastography</strong> — сдвиговая эластография</li>
<li><strong>Color 3D</strong> — трёхмерное изображение кровотока</li>
<li><strong>Auto-IMT</strong> — автоматическое измерение интима-медиа</li>
<li><strong>Smart OB / Smart NT / Smart Face</strong></li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Кардиология</li>
<li>Акушерство и гинекология</li>
<li>Онкология</li>
<li>Ангиология</li>
</ul>' \
  "2617000" \
  "ekspertnyj" \
  "statsionarnyj" \
  "dc" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray DC-80 — купить экспертную стационарную УЗИ систему" \
  "Mindray DC-80 — экспертная стационарная УЗИ система от 2 617 000 ₽. Платформа X-Insight, Sound Touch Elastography, Color 3D."

# 12. ME7
create_product \
  "Mindray ME7 — УЗИ аппарат" \
  "mindray-me7" \
  "Компактный экспертный портативный УЗИ аппарат весом 3 кг на платформе ZST+ для анестезиологии и интенсивной терапии" \
  '<h2>Mindray ME7 — компактный экспертный портативный УЗИ</h2>
<p>Компактный экспертный аппарат толщиной 44 мм и весом 3 кг, объединяющий высокое качество визуализации, быстроту отклика и продуманную эргономику. Построен на платформе ZST+.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 15.6" LED с сенсорным управлением</li>
<li>Толщина 44 мм, вес 3 кг</li>
<li>SSD 256 ГБ</li>
<li>Литий-ионная батарея</li>
<li>Wi-Fi, HDMI, USB 3.0, модуль ЭКГ</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>iNeedle</strong> — визуализация биопсийной иглы</li>
<li><strong>Smart B-line</strong> — автоматический расчёт B-линий лёгких</li>
<li><strong>LVO</strong> — исследование левого желудочка с контрастом</li>
<li><strong>Smart 3D</strong> — трёхмерная реконструкция</li>
<li><strong>AutoEF</strong> — автоматический расчёт фракции выброса</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Анестезиология</li>
<li>Интенсивная терапия</li>
<li>Неотложная помощь</li>
<li>Кардиология</li>
</ul>' \
  "0" \
  "ekspertnyj" \
  "portativnyj" \
  "me" \
  "kardiologiya,ekstrennaya-diagnostika,anesteziologiya-i-reanimatsiya" \
  "Mindray ME7 — купить компактный экспертный портативный УЗИ" \
  "Mindray ME7 — экспертный портативный УЗИ аппарат, 3 кг. Платформа ZST+, iNeedle, Smart B-line, LVO. Анестезиология, реанимация, неотложная помощь."

# 13. Resona i9
create_product \
  "Mindray Resona i9 — УЗИ аппарат" \
  "mindray-resona-i9" \
  "Экспертная ультразвуковая система на платформе ZST+ с технологиями iFusion и Smart Breast/Thyroid для комплексных исследований" \
  '<h2>Mindray Resona i9 — экспертная УЗИ система</h2>
<p>Аппарат экспертного уровня на платформе ZST+ для комплексных ультразвуковых исследований в абдоминальной, кардиологической, сосудистой, гинекологической и урологической диагностике.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 23.8" LED высокого разрешения</li>
<li>Сенсорный экран 15.6" с распознаванием жестов</li>
<li>5 портов для датчиков</li>
<li>Гибридный накопитель: 1TB HDD + 128GB SSD</li>
<li>Автономная работа до 4 часов</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Natural Touch / Sound Touch Elastography</strong></li>
<li><strong>Smart Breast</strong> — анализ по BI-RADS</li>
<li><strong>Smart Thyroid</strong> — оценка по TI-RADS</li>
<li><strong>iFusion</strong> — совмещение с МРТ/КТ</li>
<li>3D/4D визуализация, контрастная эхография</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Абдоминальная диагностика</li>
<li>Кардиология и сосуды</li>
<li>Гинекология и урология</li>
<li>Онкология</li>
</ul>' \
  "3700000" \
  "ekspertnyj" \
  "statsionarnyj" \
  "resona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray Resona i9 — купить экспертную УЗИ систему" \
  "Mindray Resona i9 — экспертная УЗИ система от 3 700 000 ₽. iFusion, Smart Breast/Thyroid, эластография. Кардиология, гинекология, онкология."

# 14. MX7s
create_product \
  "Mindray MX7s — УЗИ аппарат" \
  "mindray-mx7s" \
  "Экспертный портативный УЗИ-сканер весом 3 кг на платформе ZST+ с автономностью до 8 часов" \
  '<h2>Mindray MX7s — экспертный портативный УЗИ</h2>
<p>Компактная портативная система экспертного класса весом 3 кг и автономностью до 8 часов. Идеальное решение для мобильной диагностики в любых условиях.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 15.6" LED</li>
<li>Платформа ZST+</li>
<li>SSD 256 ГБ</li>
<li>Вес 3 кг, до 8 часов работы</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>iNeedle</strong> — визуализация биопсийной иглы</li>
<li><strong>Natural Touch Elastography</strong> — оценка эластичности тканей</li>
<li><strong>Smart NT</strong> — измерение воротникового пространства</li>
<li><strong>HR Flow</strong> — оценка микроциркуляции</li>
<li><strong>TDI</strong> — тканевой допплер</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Кардиология</li>
<li>Анестезиология и интенсивная терапия</li>
<li>Акушерство и гинекология</li>
<li>Урология, скорая помощь</li>
</ul>' \
  "979000" \
  "ekspertnyj" \
  "portativnyj" \
  "mx" \
  "kardiologiya,zhenskoe-zdorove,ekstrennaya-diagnostika,anesteziologiya-i-reanimatsiya" \
  "Mindray MX7s — купить экспертный портативный УЗИ сканер" \
  "Mindray MX7s — экспертный портативный УЗИ от 979 000 ₽. Всего 3 кг, до 8 часов. ZST+, iNeedle, HR Flow. Кардиология, анестезиология."

# 15. M6
create_product \
  "Mindray M6 — УЗИ аппарат" \
  "mindray-m6" \
  "Универсальный УЗИ-аппарат среднего класса с мобильной тележкой и автономностью 90 минут для плановой и экстренной диагностики" \
  '<h2>Mindray M6 — универсальный УЗИ аппарат</h2>
<p>Универсальный аппарат среднего класса для плановой и экстренной диагностики. Оборудован мобильной тележкой с регулировкой по высоте и встроенной литий-ионной батареей.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 15" высокого разрешения</li>
<li>Литий-ионная батарея — 90 минут работы</li>
<li>Мобильная тележка с регулировкой</li>
<li>DICOM совместимость</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart OB</strong> — автоматическая биометрия плода</li>
<li><strong>TDI / TDI QA</strong> — тканевой допплер</li>
<li><strong>Strain Elastography</strong> — компрессионная эластография</li>
<li><strong>iNeedle</strong> — визуализация биопсийной иглы</li>
<li><strong>UWN+ Contrast Imaging</strong> — контрастная визуализация</li>
<li><strong>Auto-IMT</strong> — измерение интима-медиа</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Акушерство и гинекология</li>
<li>Кардиология</li>
<li>Общая диагностика</li>
<li>Биопсия и пункция под контролем УЗ</li>
</ul>' \
  "609400" \
  "srednij" \
  "portativnyj" \
  "m-series" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya" \
  "Mindray M6 — купить универсальный УЗИ аппарат среднего класса" \
  "Mindray M6 — универсальный УЗИ аппарат от 609 400 ₽. Smart OB, эластография, iNeedle. Акушерство, кардиология, общая диагностика."

# 16. DC-90
create_product \
  "Mindray DC-90 — УЗИ аппарат" \
  "mindray-dc-90" \
  "Экспертная стационарная ультразвуковая система с технологией iFusion для интеграции КТ/МРТ/ПЭТ" \
  '<h2>Mindray DC-90 — экспертная стационарная УЗИ система</h2>
<p>Комплексная диагностическая система экспертного класса на базе технологий X-Insight и ZST+, обеспечивающая высокое качество визуализации и удобство эксплуатации.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Full HD LED монитор 23.8"</li>
<li>Сенсорный экран 13.3"</li>
<li>5 активных портов для датчиков</li>
<li>Гибридный накопитель: 1TB HDD + 120GB SSD</li>
<li>Встроенная батарея</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>iFusion</strong> — совмещение УЗ с КТ/МРТ/ПЭТ</li>
<li><strong>Эластография</strong> — компрессионная и сдвиговой волны</li>
<li><strong>Smart OB / Smart Pelvic / Smart FLC</strong></li>
<li>3D/4D визуализация, контрастное исследование</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Кардиология</li>
<li>Акушерство и гинекология</li>
<li>Онкология</li>
<li>Ангиология и радиология</li>
</ul>' \
  "2700000" \
  "ekspertnyj" \
  "statsionarnyj" \
  "dc" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray DC-90 — купить экспертную стационарную УЗИ систему" \
  "Mindray DC-90 — экспертная стационарная УЗИ система от 2 700 000 ₽. iFusion для КТ/МРТ/ПЭТ, эластография, Smart OB. Многопрофильная диагностика."

# 17. Consona N8
create_product \
  "Mindray Consona N8 — УЗИ аппарат" \
  "mindray-consona-n8" \
  "Ультразвуковая система высокого класса на платформе ZST+ с технологиями Smart Pelvic и Smart Hip" \
  '<h2>Mindray Consona N8 — УЗИ система высокого класса</h2>
<p>Инновационная система высокого класса на платформе ZST+ для общей визуализации, кардиодиагностики и исследований органов малого таза.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Full HD LED монитор 21.5"</li>
<li>Сенсорный экран 15.6"</li>
<li>5 активных портов для датчиков</li>
<li>Встроенный жесткий диск 1 ТБ</li>
<li>Встроенная батарея — 1 час, Wi-Fi</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart OB</strong> — автоматические акушерские измерения</li>
<li><strong>Smart Pelvic</strong> — оценка мышц тазового дна</li>
<li><strong>Smart Hip</strong> — диагностика дисплазии у детей</li>
<li><strong>Auto EF</strong> — расчёт фракции выброса</li>
<li><strong>Эластография</strong> — компрессионная и сдвиговая</li>
<li>3D/4D визуализация</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Кардиология</li>
<li>Акушерство и гинекология</li>
<li>Онкология</li>
<li>Ангиология и педиатрия</li>
</ul>' \
  "1771000" \
  "vysokij" \
  "statsionarnyj" \
  "consona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray Consona N8 — купить УЗИ систему высокого класса" \
  "Mindray Consona N8 — УЗИ система высокого класса от 1 771 000 ₽. Smart Pelvic, Smart Hip, Auto EF, эластография. Кардиология, акушерство, педиатрия."

# 18. Consona N7
create_product \
  "Mindray Consona N7 — УЗИ аппарат" \
  "mindray-consona-n7" \
  "Многопрофильная ультразвуковая система высокого класса на платформе ZST+ с контрастным исследованием" \
  '<h2>Mindray Consona N7 — многопрофильная УЗИ система</h2>
<p>Система высокого класса на платформе ZST+ для многопрофильного использования в абдоминальных, кардиологических и акушерских исследованиях.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Full HD LED монитор 21.5"</li>
<li>Сенсорный экран 13.3" с жестовым управлением</li>
<li>4 активных порта для датчиков</li>
<li>Встроенный жесткий диск 1 ТБ</li>
<li>Встроенная батарея — 1 час</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart HRI</strong> — гепаторенальный индекс</li>
<li><strong>Smart OB / Smart Face</strong> — акушерская автоматизация</li>
<li><strong>Sound Touch Elastography</strong> — сдвиговая эластография</li>
<li><strong>CEUS</strong> — контрастное исследование</li>
<li><strong>TDI</strong> — тканевой допплер</li>
<li>3D/4D визуализация</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Абдоминальные исследования</li>
<li>Кардиология</li>
<li>Акушерство и гинекология</li>
<li>Онкология и ангиология</li>
</ul>' \
  "1546000" \
  "vysokij" \
  "statsionarnyj" \
  "consona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray Consona N7 — купить многопрофильную УЗИ систему высокого класса" \
  "Mindray Consona N7 — многопрофильная УЗИ система от 1 546 000 ₽. ZST+, Smart HRI, CEUS, эластография. Кардиология, акушерство, онкология."

# 19. Consona N9
create_product \
  "Mindray Consona N9 — УЗИ аппарат" \
  "mindray-consona-n9" \
  "Ультразвуковая система высокого класса — первый аппарат линейки Consona на платформе ZST+ с технологией iFusion" \
  '<h2>Mindray Consona N9 — УЗИ система высокого класса</h2>
<p>Первый аппарат линейки Consona с высочайшим качеством изображения благодаря технологии зонного сканирования ZST+. Предназначен для комплексной диагностики в различных областях медицины.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 23.8"</li>
<li>Сенсорный экран 15.6" с жестовым управлением</li>
<li>5 активных портов для датчиков</li>
<li>SSD 512 ГБ</li>
<li>Встроенная батарея — 1 час, Wi-Fi</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart OB / Smart NT</strong> — автоматические измерения плода</li>
<li><strong>iFusion</strong> — интеграция КТ/МРТ</li>
<li><strong>Эластография</strong> — компрессионная и сдвиговая</li>
<li><strong>TDI</strong> — тканевой допплер</li>
<li>3D/4D сканирование, контрастная визуализация</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Акушерство</li>
<li>Кардиология</li>
<li>Онкология</li>
<li>Ангиология и неонатология</li>
</ul>' \
  "2558000" \
  "vysokij" \
  "statsionarnyj" \
  "consona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray Consona N9 — купить УЗИ систему высокого класса" \
  "Mindray Consona N9 — УЗИ система высокого класса от 2 558 000 ₽. ZST+, iFusion, Smart OB, эластография. Акушерство, кардиология, онкология."

# 20. Consona N6
create_product \
  "Mindray Consona N6 — УЗИ аппарат" \
  "mindray-consona-n6" \
  "Ультразвуковая система среднего класса линейки Consona на платформе ZST+ для широкого спектра диагностических исследований" \
  '<h2>Mindray Consona N6 — УЗИ система среднего класса</h2>
<p>Аппарат среднего класса последней серии Consona на платформе ZST+ для обширного спектра диагностических исследований.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Full HD LED монитор 21.5"</li>
<li>Сенсорный экран 13.3" с жестовым управлением</li>
<li>4 активных порта для датчиков</li>
<li>Встроенный жесткий диск 1 ТБ</li>
<li>Встроенная батарея — 1 час, Wi-Fi</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart OB</strong> — автоматические акушерские измерения</li>
<li><strong>Tissue Tracking</strong> — оценка деформации миокарда</li>
<li><strong>Эластография</strong> — оценка злокачественности образований</li>
<li>3D/4D сканирование, контрастирование</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Акушерство и гинекология</li>
<li>Кардиология</li>
<li>Онкология</li>
<li>Ангиология</li>
</ul>' \
  "1052000" \
  "srednij" \
  "statsionarnyj" \
  "consona" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray Consona N6 — купить УЗИ систему среднего класса" \
  "Mindray Consona N6 — УЗИ система среднего класса от 1 052 000 ₽. ZST+, Smart OB, Tissue Tracking, эластография. Акушерство, кардиология."

# 21. TE7
create_product \
  "Mindray TE7 — УЗИ аппарат" \
  "mindray-te7" \
  "Планшетный УЗИ-аппарат высокого класса для диагностики в операционной с технологией iNeedle и голосовым управлением" \
  '<h2>Mindray TE7 — планшетный УЗИ для операционной</h2>
<p>Планшетный ультразвуковой аппарат высокого класса, предназначенный для диагностики в условиях операционной. Содержит ряд технологий для повышения эффективности интервенционных вмешательств.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Сенсорный монитор 15"</li>
<li>Жесткий диск 120 ГБ</li>
<li>4 держателя для датчиков</li>
<li>HDMI, 4x USB 3.0</li>
<li>Батарея до 2 часов, голосовое управление</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>iNeedle</strong> — визуализация биопсийной иглы</li>
<li><strong>Free Xros M</strong> — анатомический М-режим</li>
<li><strong>Auto-IMT</strong> — автоматическое измерение интима-медиа</li>
<li><strong>Smart 3D</strong> — трёхмерная реконструкция</li>
<li><strong>TDI</strong> — тканевой допплер</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Операционная и интервенционная диагностика</li>
<li>Анестезиология</li>
<li>Сосудистая диагностика</li>
</ul>' \
  "0" \
  "vysokij" \
  "planshetnyj" \
  "te" \
  "ekstrennaya-diagnostika,anesteziologiya-i-reanimatsiya,obshhie-issledovaniya" \
  "Mindray TE7 — купить планшетный УЗИ аппарат для операционной" \
  "Mindray TE7 — планшетный УЗИ для операционной. iNeedle, голосовое управление, батарея 2 часа. Анестезиология, интервенционная диагностика."

# 22. M8
create_product \
  "Mindray M8 — УЗИ аппарат" \
  "mindray-m8" \
  "Портативный УЗИ-аппарат высокого класса на платформе mQudro для реанимации, хирургии и травматологии" \
  '<h2>Mindray M8 — портативный УЗИ высокого класса</h2>
<p>Портативный аппарат высокого класса на платформе mQudro для проведения исследований в реанимации, хирургии, травматологии, ортопедии и кардиологии.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 15" высокого разрешения</li>
<li>SSD 256 ГБ</li>
<li>Платформа mQudro</li>
<li>Wi-Fi, DICOM</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Natural Touch Elastography</strong> — оценка эластичности</li>
<li><strong>TDI / TDI QA</strong> — анализ сократимости сердца</li>
<li><strong>Smart OB / Smart NT</strong> — акушерские измерения</li>
<li><strong>iPage+</strong> — ультразвуковая томография</li>
<li>3D/4D сканирование</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Реанимация и хирургия</li>
<li>Травматология и ортопедия</li>
<li>Кардиология</li>
<li>Онкология (щитовидная, молочные железы)</li>
</ul>' \
  "1527000" \
  "vysokij" \
  "portativnyj" \
  "m-series" \
  "kardiologiya,ekstrennaya-diagnostika,obshhie-issledovaniya,onkologiya" \
  "Mindray M8 — купить портативный УЗИ высокого класса" \
  "Mindray M8 — портативный УЗИ высокого класса от 1 527 000 ₽. Платформа mQudro, эластография, TDI, 3D/4D. Реанимация, хирургия, кардиология."

# 23. M9T
create_product \
  "Mindray M9T — УЗИ аппарат" \
  "mindray-m9t" \
  "Портативный УЗИ-аппарат экспертного класса на платформе mQudro для акушерства и кардиологии" \
  '<h2>Mindray M9T — портативный экспертный УЗИ</h2>
<p>Портативный аппарат экспертного класса на платформе mQudro, обеспечивающий высокое качество визуализации для оценки злокачественности образований, инфарктных изменений миокарда и врождённых пороков развития плода.</p>

<h3>Основные характеристики</h3>
<ul>
<li>Монитор 15" LED широкоформатный</li>
<li>SSD 256 ГБ</li>
<li>Батарея до 1.5 часов</li>
<li>HDMI, USB 3.0, Wi-Fi</li>
</ul>

<h3>Ключевые технологии</h3>
<ul>
<li><strong>Smart OB / Smart NT</strong> — измерения параметров плода</li>
<li><strong>TDI / TDI QA</strong> — тканевой допплер</li>
<li><strong>Natural Touch Elastography</strong> — компрессионная эластография</li>
<li><strong>UWN+ Contrast Imaging</strong> — контрастная визуализация</li>
<li><strong>iPage+</strong> — ультразвуковая томография</li>
<li>3D/4D сканирование</li>
</ul>

<h3>Области применения</h3>
<ul>
<li>Акушерство и гинекология</li>
<li>Кардиология</li>
<li>Онкология</li>
<li>Стресс-эхокардиография</li>
</ul>' \
  "1646000" \
  "ekspertnyj" \
  "portativnyj" \
  "m-series" \
  "kardiologiya,zhenskoe-zdorove,obshhie-issledovaniya,onkologiya" \
  "Mindray M9T — купить портативный экспертный УЗИ аппарат" \
  "Mindray M9T — портативный экспертный УЗИ от 1 646 000 ₽. Платформа mQudro, Smart OB, UWN+ Contrast, эластография. Акушерство, кардиология."

echo ""
echo "=== IMPORT COMPLETE ==="
echo "Total products created: 23"
