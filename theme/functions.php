<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

// Theme include ... нда, подключаем всё :-(
require get_template_directory() . '/inc/init.php';


// custom include
include 'include/common.php';
if (is_admin())
    include 'include/admin.php';
elseif (is_ajax())
    include 'include/ajax.php';
else
    include 'include/front.php';

// Bitrix24 Lead Form
include get_template_directory() . '/bitrix24-lead-form.php';

// Заглушки для неактивных YITH-плагинов — чтобы шорткоды не выводились как текст
add_shortcode('yith_wcwl_add_to_wishlist', '__return_empty_string');
add_shortcode('yith_compare_button', '__return_empty_string');


/**
 *
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */


add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
function woo_remove_product_tabs( $tabs ){
unset( $tabs['description'] ); // Remove the description tab
unset( $tabs['reviews'] ); // Remove the reviews tab
// unset( $tabs['additional_information'] ); // Remove the additional information tab
return $tabs;
}



/**
 * Fix WooCommerce Loop Title
 */
function woocommerce_template_loop_product_title()
{
    global $post;
    echo '</p>';
    echo '<p class="name product-title ' . esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ) . '">';
    woocommerce_template_loop_product_link_open();
    echo get_the_title();  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    woocommerce_template_loop_product_link_close();

    $short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt);

    if ($short_description):
        $text = strip_tags($short_description);
        $text = trim(str_replace(array('Брошюра', 'В избранное', 'Сравнить', '[yith_wcwl_add_to_wishlist]', '[yith_compare_button]', '&nbsp;', "\r", "\n"), '', $text));
        $text = str_replace('&nbsp;', ' ', htmlentities($text));
        $text = trim(preg_replace('/\s+/', ' ', $text));


        // $text = mb_substr($description, 0, 150);

        // if (mb_strlen($text) == 150)
        // {
        //     $text = mb_substr($text, 0, mb_strrpos($text, ' '));
        //     $text .= '...';
        // }

        ?>
        <p>
            <?php echo $text;?>
        </p>
    <?php
    endif;
    ?>
    <a href="#b24-modal" class="button button--big">
        <span>Получить КП</span>
    </a>
    <?php
}


function flatsome_content_nav( $nav_id )
{
    global $wp_query, $post;

    // Don't print empty markup on single pages if there's nowhere to navigate.
    if ( is_single() ) {
        $previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
        $next = get_adjacent_post( false, '', false );

        if ( ! $next && ! $previous )
            return;
    }

    // Don't print empty markup in archives if there's only one page.
    if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
        return;

    $nav_class = ( is_single() ) ? 'navigation-post' : 'navigation-paging';

    ?>
    <?php if ( is_single() ) : // navigation links for single posts ?>

        <nav role="navigation" id="<?php echo esc_attr( $nav_id ); ?>" class="<?php echo $nav_class; ?>">

            <div class="flex-row next-prev-nav bt bb">
                <div class="flex-col flex-grow nav-prev text-left">
                        <?php previous_post_link( '<div class="nav-previous">%link</div>','<span class="hide-for-small">' .get_flatsome_icon('icon-angle-left').'</span> Назад' ); ?>

                </div>
                <div class="flex-col flex-grow nav-next text-right">
                        <?php next_post_link( '<div class="nav-next">%link</div>', 'Вперёд <span class="hide-for-small">'. get_flatsome_icon('icon-angle-right').'</span>' ); ?>
                </div>
            </div>

    <?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

        <div class="flex-row">
            <div class="flex-col flex-grow">

                <?php if ( get_next_posts_link() ) : ?>

                    <div class="nav-previous"><?php next_posts_link( __( '<span class="icon-angle-left"></span> Older posts', 'flatsome' ) ); ?></div>

                <?php endif; ?>

            </div>

            <div class="flex-col flex-grow">

                <?php if ( get_previous_posts_link() ) : ?>

                    <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="icon-angle-right"></span>', 'flatsome' ) ); ?></div>

                <?php endif; ?>

            </div>
        </div>

    <?php endif; ?>

    </nav>

    <?php
}


/**
 * Альтернативный дизайн страницы товара — по ?design=new
 */
add_filter('template_include', function($template) {
    if (is_product() && isset($_GET['design']) && $_GET['design'] === 'new') {
        $custom = get_template_directory() . '/single-product-redesign.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 999);

// SEO Title — ВСУЗИ страница + товары
add_filter('rank_math/frontend/title', function($title) {
    // ВСУЗИ hub page
    if (is_tax('product_cat', 'vsuzi')) {
        return 'ВСУЗИ оборудование Philips Volcano — катетеры, проводники, платформы | ТД «Пульс»';
    }

    // Product pages — SEO-оптимизированный title
    if (is_singular('product')) {
        $seo_title = tdp_product_seo_title();
        if ($seo_title) return $seo_title;
    }

    return $title;
});

/**
 * Генерация SEO title для товара
 * Формат: {Модель} — {тип оборудования} | Купить по выгодной цене
 */
function tdp_product_seo_title() {
    global $post;
    $product = wc_get_product($post->ID);
    if (!$product) return false;

    $name = $product->get_name();

    // Убираем мусор
    $name = preg_replace('/\s*\(Копировать\)\s*/u', '', $name);

    // Разделяем на модель и тип по —, – или "- " перед заглавной буквой
    $parts = preg_split('/\s*[—–]\s*|\s*-\s+(?=[А-ЯЁA-Z])/u', $name, 2);
    $model_raw = trim($parts[0]);
    $type_raw = isset($parts[1]) ? trim($parts[1]) : '';

    // Форматируем модель
    $model = tdp_format_model($model_raw);

    // Форматируем тип оборудования
    $type = $type_raw ? tdp_format_equipment_type($type_raw) : '';

    // Если тип не найден в названии — берём из категории
    if (!$type) {
        $type = tdp_get_leaf_category_name($post->ID);
    }

    // Собираем title — каскад по длине
    $base = $type ? "{$model} — {$type}" : $model;

    $seo_title = "{$base} | Купить по выгодной цене";
    if (mb_strlen($seo_title, 'UTF-8') > 70) {
        $seo_title = "{$base} | Купить в ТД Пульс";
    }
    if (mb_strlen($seo_title, 'UTF-8') > 70) {
        $seo_title = "{$base} | ТД Пульс";
    }

    return $seo_title;
}

/**
 * Smart title case для модели: GENERAL ELECTRIC VIVID IQ → GE Vivid IQ
 */
function tdp_format_model($str) {
    // Сокращаем GENERAL ELECTRIC → GE
    $str = preg_replace('/^GENERAL\s+ELECTRIC\b/iu', 'GE', $str);

    // Обработка строк в кавычках: «ГРАДИЕНТ-4M» → «Градиент-4M»
    if (preg_match('/^([«"])(.+)([»"])$/u', $str, $m)) {
        return $m[1] . tdp_format_model($m[2]) . $m[3];
    }

    $tokens = preg_split('/(\s+)/u', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
    $result = [];

    foreach ($tokens as $token) {
        if (preg_match('/^\s+$/u', $token)) {
            $result[] = $token;
            continue;
        }
        // Дефисные слова: обработка каждой части
        if (strpos($token, '-') !== false && !preg_match('/^\d/', $token)) {
            $parts = explode('-', $token);
            $result[] = implode('-', array_map('tdp_title_case_word', $parts));
        } else {
            $result[] = tdp_title_case_word($token);
        }
    }

    return implode('', $result);
}

/**
 * Title case для одного слова с сохранением аббревиатур и mixed case
 */
function tdp_title_case_word($word) {
    if (empty($word)) return $word;

    $upper = mb_strtoupper($word, 'UTF-8');
    $lower = mb_strtolower($word, 'UTF-8');

    // Mixed case (Free.Max, Allia, Discovery) — оставить как есть
    if ($word !== $upper && $word !== $lower) {
        return $word;
    }

    // ALL CAPS + только буквы + >= 4 символов → Title Case
    if ($word === $upper && preg_match('/^[a-zA-Zа-яёА-ЯЁ]{4,}$/u', $word)) {
        return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
             . mb_strtolower(mb_substr($word, 1, null, 'UTF-8'), 'UTF-8');
    }

    // Остальное (короткие, с цифрами, спецсимволы) — оставить как есть
    return $word;
}

/**
 * Маппинг типов оборудования: официальное → SEO-дружественное
 */
function tdp_format_equipment_type($type_raw) {
    $type_upper = mb_strtoupper(trim($type_raw), 'UTF-8');

    // Прямой маппинг (точное совпадение после uppercase)
    $map = [
        'УЛЬТРАЗВУКОВАЯ СИСТЕМА'       => 'УЗИ аппарат',
        'МР-ТОМОГРАФ'                  => 'МРТ аппарат',
        'КОМПЬЮТЕРНЫЙ ТОМОГРАФ'        => 'КТ аппарат',
        'РЕНТГЕНОВСКАЯ СИСТЕМА'        => 'рентген-аппарат',
        'АНГИОГРАФИЧЕСКАЯ СИСТЕМА'     => 'ангиограф',
        'МАММОГРАФ'                    => 'маммограф',
        'МОНИТОР ПАЦИЕНТА'             => 'монитор пациента',
        'ФЕТАЛЬНЫЙ МОНИТОР'            => 'фетальный монитор',
        'ДЕФИБРИЛЛЯТОР-МОНИТОР'        => 'дефибриллятор',
        'ДЕФИБРИЛЛЯТОР АВТОМАТИЧЕСКИЙ НАРУЖНЫЙ' => 'дефибриллятор АВД',
        'КАРДИОГРАФ'                   => 'ЭКГ аппарат',
        'ПОРТАТИВНЫЙ ЭЛЕКТРОКАРДИОГРАФ'=> 'портативный ЭКГ',
        'АППАРАТ ИВЛ'                  => 'аппарат ИВЛ',
        'ПОРТАТИВНЫЙ АППАРАТ ИВЛ'      => 'портативный аппарат ИВЛ',
        'ИНЖЕКТОР'                     => 'инжектор',
        'ИНЖЕКТОР ДЛЯ КТ'             => 'инжектор для КТ',
        'ИНЖЕКТОР ДЛЯ МРТ/КТ'         => 'инжектор для МРТ/КТ',
        'МОБИЛЬНАЯ ХИРУРГИЧЕСКАЯ С-ДУГА'=> 'мобильная С-дуга',
        'ОФЭКТ'                        => 'ОФЭКТ',
        'ОФЭКТ/КТ'                     => 'ОФЭКТ/КТ',
        'ПЭТ/КТ'                       => 'ПЭТ/КТ',
        'ПЭТ-КТ'                       => 'ПЭТ/КТ',
        'ПЭТ/МРТ'                      => 'ПЭТ/МРТ',
        'SPECT-КАМЕРА'                 => 'ОФЭКТ камера',
        'SPECT-КТ'                     => 'ОФЭКТ/КТ',
        'ИНФОРМАЦИОННАЯ СИСТЕМА'       => 'информационная система',
        'ИНФОРМАЦИОННЫЙ ПОРТАЛ'        => 'информационная система',
        'РАДИОХИРУРГИЧЕСКАЯ СИСТЕМА (КИБЕРНОЖ)' => 'кибернож',
        'РАДИОТЕРАПЕВТИЧЕСКАЯ СИСТЕМА' => 'система лучевой терапии',
        'АППАРАТ МАГНИТОТЕРАПЕВТИЧЕСКИЙ'=> 'аппарат магнитотерапии',
        'АППАРАТ ЭЛЕКТРОТЕРАПИИ'       => 'аппарат электротерапии',
    ];

    if (isset($map[$type_upper])) {
        return $map[$type_upper];
    }

    // Частичный маппинг: С-дуга
    if (strpos($type_upper, 'С-ДУГА') !== false || strpos($type_upper, 'РЕНТГЕНОХИРУРГИЧЕСКИЙ') !== false) {
        return 'С-дуга';
    }
    // Катетеры ВСУЗИ
    if (strpos($type_upper, 'ВСУЗИ') !== false) {
        return 'катетер ВСУЗИ';
    }
    // Баллонные катетеры
    if (strpos($type_upper, 'КАТЕТЕР') !== false && strpos($type_upper, 'БАЛЛОН') !== false) {
        return 'баллонный катетер';
    }
    // Лазерные катетеры
    if (strpos($type_upper, 'ЛАЗЕРН') !== false && strpos($type_upper, 'КАТЕТЕР') !== false) {
        return 'лазерный катетер';
    }
    // Прочие катетеры
    if (strpos($type_upper, 'КАТЕТЕР') !== false) {
        return 'катетер';
    }
    // Мониторинг при МРТ
    if (strpos($type_upper, 'МОНИТОРИНГ') !== false && strpos($type_upper, 'МРТ') !== false) {
        return 'МРТ-совместимый монитор';
    }
    // Холтер
    if (strpos($type_upper, 'ХОЛТЕР') !== false) {
        return 'холтер-монитор';
    }
    // Центральная станция
    if (strpos($type_upper, 'ЦЕНТРАЛЬНАЯ СТАНЦИЯ') !== false) {
        return 'центральная станция мониторинга';
    }

    // Не нашли маппинг — просто lowercase с восстановлением аббревиатур
    return tdp_type_to_lower($type_raw);
}

/**
 * Lowercase типа с восстановлением медицинских аббревиатур
 */
function tdp_type_to_lower($type) {
    $type = mb_strtolower(trim($type), 'UTF-8');
    // Восстанавливаем аббревиатуры
    $type = str_replace(
        ['офэкт/кт', 'пэт/кт', 'пэт/мрт', 'всузи', 'офэкт', 'пэт'],
        ['ОФЭКТ/КТ', 'ПЭТ/КТ', 'ПЭТ/МРТ', 'ВСУЗИ', 'ОФЭКТ', 'ПЭТ'],
        $type
    );
    $type = ' ' . $type . ' ';
    $type = str_replace(
        [' мрт ', ' кт ', ' узи ', ' ивл ', ' экг ', ' чкв ', ' орит '],
        [' МРТ ', ' КТ ', ' УЗИ ', ' ИВЛ ', ' ЭКГ ', ' ЧКВ ', ' ОРИТ '],
        $type
    );
    $type = str_replace('мр-', 'МР-', $type);
    return trim($type);
}

/**
 * Получить название «листовой» подкатегории товара (не ROOT)
 */
function tdp_get_leaf_category_name($product_id) {
    $cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'all']);
    if (empty($cats) || is_wp_error($cats)) return '';

    // Ищем подкатегорию (у которой есть parent)
    foreach ($cats as $cat) {
        if ($cat->parent > 0) {
            return mb_strtolower($cat->name, 'UTF-8');
        }
    }
    // Если нет подкатегории — берём первую
    return mb_strtolower($cats[0]->name, 'UTF-8');
}

add_filter('rank_math/frontend/description', function($desc) {
    if (is_tax('product_cat', 'vsuzi')) {
        return 'Полная линейка ВСУЗИ оборудования Philips Volcano: катетеры Eagle Eye Platinum и Refinity, проводник OmniWire FFR/iFR, платформы IntraSight, ПО SyncVision. Доставка по РФ.';
    }
    return $desc;
});

/**
 * SEO Control Center - TDPULS
 * Единый конфиг для robots.txt, noindex, Clean-param
 * Менять параметры ТОЛЬКО здесь — хуки ниже читают этот массив
 */
$tdp_seo_config = [
    // GET-параметры: Disallow (все краулеры) + Clean-param (Яндекс) + noindex meta
    'blocked_params' => [
        'add_to_wishlist', '_wpnonce', 'add-to-cart', 'design', 's', 'lang',
    ],

    // Префикс фильтров WooCommerce — для Disallow wildcard
    'filter_prefix' => 'filter_',

    // Конкретные filter-атрибуты — для Clean-param (Яндекс не поддерживает wildcard)
    'filter_attrs' => [
        'amplituda', 'apertura-gentri-sm', 'ch-o-d-g-v-n-m-p', 'd-m-p-d',
        'diagnosticheskij-klass', 'form-faktor', 'glubina-s-dugi-sm', 'gradienty-tl-m-s',
        'katetery', 'klinicheskij-segment', 'kol-vo-rabochih-mest', 'kolichestvo-rch-kanalov',
        'kolichestvo-srezov', 'linejka', 'linejki-oborudovaniya', 'mg-sd-tl', 'model',
        'moshhnost-generatora-kvt', 'napryazhyonnost-polya-t', 'naznachenie',
        'parametry-detektora', 'proizvoditel', 'shirina-detektora', 'tehnologiya',
        'tip-anoda-s-dugi', 'tip-apparata', 'tip-issledovaniya', 'tip-krepleniya', 'tip-kt',
        'tip-matritsy', 'tip-oborudovaniya', 'tip-priyomnika-s-dugi', 'tip-sistemy',
    ],

    // Пути для Disallow в robots.txt
    'blocked_paths' => [
        '/cart/', '/checkout/', '/my-account/', '/wishlist/',
        '/blocks/', '/feed/', '/*/feed/',
    ],

    // Страницы с noindex (slug) — не индексировать
    'noindex_pages' => [],
];

// --- robots.txt: Disallow + Clean-param ---
add_filter('robots_txt', function($output, $public) {
    global $tdp_seo_config;

    // 1. Disallow для GET-параметров (все краулеры)
    $extra  = "\n# Block duplicate content parameters\n";
    foreach ($tdp_seo_config['blocked_params'] as $param) {
        $extra .= "Disallow: /*?{$param}=\n";
        $extra .= "Disallow: /*?*{$param}=\n";
    }
    // Фильтры — wildcard по префиксу
    $extra .= "Disallow: /*?{$tdp_seo_config['filter_prefix']}*\n";
    $extra .= "Disallow: /*?*{$tdp_seo_config['filter_prefix']}*\n";

    // 2. Disallow для путей (сервисные страницы)
    $extra .= "\n# Block internal/service pages\n";
    foreach ($tdp_seo_config['blocked_paths'] as $path) {
        $extra .= "Disallow: {$path}\n";
    }

    // 3. Clean-param для Яндекса (склеивание дублей, перенос метрик)
    // Директива intersectional — работает без User-agent: Yandex
    // Макс длина строки 500 символов — разбиваем на группы
    $extra .= "\n# Yandex Clean-param: merge duplicate URLs, transfer metrics\n";

    // 3a. Основные параметры
    $extra .= "Clean-param: " . implode('&', $tdp_seo_config['blocked_params']) . "\n";

    // 3b. Filter-атрибуты (разбиваем на строки по ~450 символов)
    $filter_params = array_map(function($attr) {
        global $tdp_seo_config;
        return $tdp_seo_config['filter_prefix'] . $attr;
    }, $tdp_seo_config['filter_attrs']);

    $line = '';
    foreach ($filter_params as $fp) {
        $candidate = $line === '' ? $fp : $line . '&' . $fp;
        if (strlen('Clean-param: ' . $candidate) > 450) {
            $extra .= "Clean-param: {$line}\n";
            $line = $fp;
        } else {
            $line = $candidate;
        }
    }
    if ($line !== '') {
        $extra .= "Clean-param: {$line}\n";
    }

    return $output . $extra;
}, 999, 2);

// --- noindex для URL с параметрами-дублями ---
add_filter('rank_math/frontend/robots', function($robots) {
    global $tdp_seo_config;

    // noindex для конкретных страниц
    foreach ($tdp_seo_config['noindex_pages'] as $slug) {
        if (is_page($slug)) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'nofollow';
            return $robots;
        }
    }

    // noindex для GET-параметров из конфига
    foreach ($tdp_seo_config['blocked_params'] as $param) {
        if (isset($_GET[$param])) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'nofollow';
            return $robots;
        }
    }

    // noindex для filter_* параметров
    foreach (array_keys($_GET) as $key) {
        if (strpos($key, $tdp_seo_config['filter_prefix']) === 0) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'nofollow';
            return $robots;
        }
    }

    return $robots;
});

// Title для главной страницы (с учётом Polylang)
add_filter('pre_get_document_title', function($title) {
    if (is_front_page()) {
        if (function_exists('pll_current_language') && pll_current_language() === 'en') {
            return 'Medical Equipment Philips, GE, Siemens — TD Puls';
        }
        return 'Медицинское оборудование Philips, GE, Siemens — ТД «Пульс»';
    }
    return $title;
}, 999);

// H1 для главной: визуально скрытый, но доступный для поисковиков
add_action('flatsome_after_header', function() {
    if (is_front_page()) {
        if (function_exists('pll_current_language') && pll_current_language() === 'en') {
            echo '<h1 class="tdp-seo-h1" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;">Medical Equipment Philips, GE, Siemens — Buy from TD Puls</h1>';
        } else {
            echo '<h1 class="tdp-seo-h1" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;">Медицинское оборудование Philips, GE, Siemens — купить в ТД «Пульс»</h1>';
        }
    }
});

// H1 для страниц категорий WooCommerce
add_action('woocommerce_archive_description', function() {
    if (is_product_category()) {
        $cat = get_queried_object();
        if ($cat) {
            echo '<h1 class="category-title">' . esc_html($cat->name) . '</h1>';
        }
    }
}, 5);

// Очистка description от шорткодов Flatsome + генерация описания для товаров
function tdp_clean_product_description() {
    if (!is_singular('product')) {
        return false;
    }

    global $post;
    $product = wc_get_product($post->ID);
    if (!$product) return false;

    // Берём short_description, чистим от шорткодов и HTML
    $short = $product->get_short_description();
    $clean = strip_shortcodes($short);
    $clean = preg_replace('/\[[^\]]*\]/', '', $clean); // Flatsome шорткоды типа [row_inner_1]
    $clean = wp_strip_all_tags($clean);
    $clean = preg_replace('/\s+/', ' ', $clean);
    $clean = trim(str_replace('&nbsp;', ' ', $clean));

    // Если осталось достаточно текста — используем
    if (mb_strlen($clean) >= 50) {
        return mb_substr($clean, 0, 160);
    }

    // Иначе генерируем из названия + категории + синоним типа оборудования
    $name = $product->get_name();
    $cats = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'names']);
    $cat = !empty($cats) ? $cats[0] : 'медицинское оборудование';

    // Добавляем синоним для SEO-покрытия
    $synonym = '';
    if (mb_stripos($name, 'КТ аппарат') !== false) {
        $synonym = ' (компьютерный томограф)';
    } elseif (mb_stripos($name, 'МРТ аппарат') !== false) {
        $synonym = ' (магнитно-резонансный томограф)';
    }

    return "{$name}{$synonym} — {$cat}. Поставка по РФ, монтаж и сервисное обслуживание. ТД «Пульс».";
}

// Получить чистое описание категории WooCommerce для meta description
function tdp_get_category_description() {
    if (!is_product_category()) return false;
    $cat = get_queried_object();
    if (!$cat || empty($cat->description)) return false;
    $desc = wp_strip_all_tags($cat->description);
    $desc = preg_replace('/\s+/', ' ', trim($desc));
    return mb_substr($desc, 0, 160);
}

// Meta Description — главная + товары + категории
add_filter('rank_math/frontend/description', function($description) {
    if (is_front_page()) {
        return 'Поставки медицинского оборудования от ведущих производителей: Philips, GE, Siemens. УЗИ, МРТ, КТ, рентген. Монтаж и сервисное обслуживание по всей России.';
    }

    $product_desc = tdp_clean_product_description();
    if ($product_desc) {
        return $product_desc;
    }

    $cat_desc = tdp_get_category_description();
    if ($cat_desc) {
        return $cat_desc;
    }

    return $description;
});

// OG Description — главная + товары + категории
add_filter('rank_math/opengraph/facebook/og_description', function($description) {
    if (is_front_page()) {
        return 'Поставки медицинского оборудования от ведущих производителей: Philips, GE, Siemens. УЗИ, МРТ, КТ, рентген. Монтаж и сервисное обслуживание по всей России.';
    }
    if (is_tax('product_cat', 'vsuzi')) {
        return 'Полная линейка ВСУЗИ оборудования Philips Volcano: катетеры Eagle Eye Platinum, Refinity, проводник OmniWire, платформы IntraSight и Core Mobile. Поставка по РФ.';
    }

    $product_desc = tdp_clean_product_description();
    if ($product_desc) {
        return $product_desc;
    }

    $cat_desc = tdp_get_category_description();
    if ($cat_desc) {
        return $cat_desc;
    }

    return $description;
});

add_filter('rank_math/opengraph/twitter/og_description', function($description) {
    if (is_front_page()) {
        return 'Поставки медицинского оборудования от ведущих производителей: Philips, GE, Siemens. УЗИ, МРТ, КТ, рентген. Монтаж и сервисное обслуживание по всей России.';
    }
    if (is_tax('product_cat', 'vsuzi')) {
        return 'Полная линейка ВСУЗИ оборудования Philips Volcano: катетеры Eagle Eye Platinum, Refinity, проводник OmniWire, платформы IntraSight и Core Mobile. Поставка по РФ.';
    }

    $product_desc = tdp_clean_product_description();
    if ($product_desc) {
        return $product_desc;
    }

    $cat_desc = tdp_get_category_description();
    if ($cat_desc) {
        return $cat_desc;
    }

    return $description;
});

// OG Title — главная
add_filter('rank_math/opengraph/facebook/og_title', function($title) {
    if (is_front_page()) {
        return 'Медицинское оборудование Philips, GE, Siemens — ТД «Пульс»';
    }
    if (is_tax('product_cat', 'vsuzi')) {
        return 'ВСУЗИ оборудование Philips Volcano — катетеры, проводники, платформы';
    }
    return $title;
});

add_filter('rank_math/opengraph/twitter/og_title', function($title) {
    if (is_front_page()) {
        return 'Медицинское оборудование Philips, GE, Siemens — ТД «Пульс»';
    }
    if (is_tax('product_cat', 'vsuzi')) {
        return 'ВСУЗИ оборудование Philips Volcano — катетеры, проводники, платформы';
    }
    return $title;
});

// OG Image — ВСУЗИ hub
add_filter('rank_math/opengraph/facebook/image', function($image) {
    if (is_tax('product_cat', 'vsuzi')) {
        return home_url('/wp-content/uploads/vsuzi/og-vsuzi-hub.jpg');
    }
    return $image;
});

// ВСУЗИ: редиректы старых URL
add_action('template_redirect', function() {
    $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Старая хаб-страница → категория
    if ($uri === 'vsuzi') {
        wp_redirect(home_url('/product-category/interventsionnaya-rentgenologiya/vsuzi/'), 301);
        exit;
    }

    // Дедупликация товаров: старые slug → новые
    $product_redirects = [
        'refinity-rotatsionnyj-kateter-dlya-provedeniya-vsuzi' => '/shop/interventsionnaya-rentgenologiya/vsuzi/refinity-rotatsionnyj-kateter-dlya-vsuzi/',
        'syncvision-sistema-dlya-vypolneniya-vysokotochnoj-terapii' => '/shop/interventsionnaya-rentgenologiya/vsuzi/syncvision-sistema-tochnoj-navigatsii/',
        'verrata-plus-provodnik-s-datchikom-davleniya' => '/shop/interventsionnaya-rentgenologiya/vsuzi/omniwire-provodnik-s-datchikom-davleniya/',
        'visions-pv-0-018-dyujma-tsifrovoj-kateter-dlya-vsuzi' => '/shop/interventsionnaya-rentgenologiya/vsuzi/reconnaissance-pv-018-otw-tsifrovoj-kateter-dlya-vsuzi/',
    ];
    $slug = basename($uri);
    if (isset($product_redirects[$slug]) && is_404()) {
        wp_redirect(home_url($product_redirects[$slug]), 301);
        exit;
    }
});

// ВСУЗИ: кастомный шаблон для категории
add_filter('template_include', function($template) {
    if (is_tax('product_cat', 'vsuzi')) {
        $custom = get_template_directory() . '/page-vsuzi-hub.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 999);

// ВСУЗИ hub CSS
add_action('wp_enqueue_scripts', function() {
    if (is_tax('product_cat', 'vsuzi')) {
        wp_enqueue_style(
            'vsuzi-hub',
            get_template_directory_uri() . '/vsuzi-hub.css',
            [],
            '1.0.0'
        );
    }
});

/**
 * Performance: удаляем ненужные CSS/JS с фронтенда
 */
add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;
    // WooCommerce Blocks CSS (3 KB) — priority 999 т.к. WC перерегистрирует
    wp_dequeue_style('wc-blocks-style');
    wp_deregister_style('wc-blocks-style');
    // WordPress Block Library CSS — используем Classic Editor
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    // Classic theme styles
    wp_dequeue_style('classic-theme-styles');
    // Global styles
    wp_dequeue_style('global-styles');
}, 999);

// IE fallbacks — IE мёртв с 2022
remove_action('wp_head', 'flatsome_add_fallbacks');

// WordPress emoji (7KB JS + CSS, не нужны)
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('emoji_svg_url', '__return_false');

/**
 * Performance: автоочистка nginx FastCGI cache при обновлении контента
 * Заменяет кнопку "Clear Cache" из WP Fastest Cache
 */
add_action('save_post', function($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if (is_dir('/var/cache/nginx/fastcgi')) {
        exec('rm -rf /var/cache/nginx/fastcgi/*');
    }
}, 99);

// Также очищаем при обновлении term (категорий, тегов)
add_action('edited_term', function() {
    if (is_dir('/var/cache/nginx/fastcgi')) {
        exec('rm -rf /var/cache/nginx/fastcgi/*');
    }
}, 99);

// Кнопка "Очистить кэш" в админ-баре
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!current_user_can('manage_options')) return;
    $wp_admin_bar->add_node([
        'id' => 'tdp-clear-cache',
        'title' => 'Очистить кэш',
        'href' => wp_nonce_url(admin_url('admin-post.php?action=tdp_clear_cache'), 'tdp_clear_cache'),
    ]);
}, 100);

add_action('admin_post_tdp_clear_cache', function() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['_wpnonce'], 'tdp_clear_cache')) {
        wp_die('Доступ запрещён');
    }
    exec('rm -rf /var/cache/nginx/fastcgi/*');
    wp_safe_redirect(wp_get_referer() ?: admin_url());
    exit;
});

/**
 * Performance: scroll-behavior smooth (замена плагина mousewheel-smooth-scroll)
 */
add_action('wp_head', function() {
    echo '<style>html{scroll-behavior:smooth}</style>';
    if (is_front_page()) {
        echo '<style>
.section-banner .slider-wrapper{min-height:440px}
.section-banner-tablet .slider-wrapper{min-height:500px}
@media(max-width:849px){.section-banner-tablet .slider-wrapper{min-height:400px}}
@media(max-width:549px){.section-banner-tablet .slider-wrapper{min-height:300px}}
.product-small .box-image{aspect-ratio:1/1;overflow:hidden}
.product-small .box-image img{width:300px;height:300px}
.blog-anons-slider{min-height:320px}
@media(max-width:549px){.blog-anons-slider{min-height:280px}}
</style>';
    }
}, 1);

/**
 * Performance: preload LCP image + preconnect + fl-icons font-display:swap
 */
add_action('wp_head', function() {
    if (is_admin()) return;
    // Preconnect
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    // fl-icons + Manrope: override font-display → swap (prevents CLS from font loading)
    echo '<style>@font-face{font-family:"fl-icons";font-display:swap}@font-face{font-family:"Manrope";font-display:swap}</style>' . "\n";
    // Homepage: preload LCP banner image + fl-icons font
    if (is_front_page()) {
        echo '<link rel="preload" as="image" href="/wp-content/uploads/2021/03/uzi11.jpg" fetchpriority="high">' . "\n";
        echo '<link rel="preload" as="font" type="font/woff2" href="/wp-content/themes/flatsome/assets/css/icons/fl-icons.woff2" crossorigin>' . "\n";
    }
}, 3);

/**
 * Performance: убираем скрипты mousewheel-smooth-scroll (плагин деактивирован)
 */
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_script('wpmssab');
    wp_deregister_script('wpmssab');
    wp_dequeue_script('SmoothScroll');
    wp_deregister_script('SmoothScroll');
    wp_dequeue_script('wpmss');
    wp_deregister_script('wpmss');
}, 999);

/**
 * Performance: defer всех JS кроме jQuery
 */
add_filter('script_loader_tag', function($tag, $handle) {
    $no_defer = ['jquery-core', 'jquery-migrate'];
    if (is_admin() || in_array($handle, $no_defer)) return $tag;
    if (strpos($tag, 'defer') !== false || strpos($tag, 'async') !== false) return $tag;
    return str_replace(' src', ' defer src', $tag);
}, 10, 2);

/**
 * Performance: убрать dashicons.css с фронтенда (58 KB, нужен только в админке)
 * + CSS-фикс стрелок Max Mega Menu (вместо dashicons-шрифта — CSS-треугольники)
 */
add_action('wp_enqueue_scripts', function() {
    if (!is_admin_bar_showing()) {
        wp_dequeue_style('dashicons');
        wp_add_inline_style('megamenu', '
            #mega-menu-wrap-primary .mega-indicator:after {
                font-family: Arial, sans-serif !important;
                content: "\25BE" !important;
                font-size: 0.85em;
            }
            #mega-menu-wrap-primary .mega-toggle-on > a > .mega-indicator:after {
                content: "\25B4" !important;
            }
            @media (min-width:769px) {
                #mega-menu-wrap-primary li.mega-menu-flyout .mega-indicator:after {
                    content: "\25B8" !important;
                }
                #mega-menu-wrap-primary .mega-align-bottom-right .mega-indicator:after {
                    content: "\25C2" !important;
                }
            }
        ');
    }
}, 100);

/**
 * Performance: font-display:swap для Google Fonts (убирает FOIT)
 */
add_filter('style_loader_tag', function($tag, $handle) {
    if (strpos($tag, 'fonts.googleapis.com') !== false && strpos($tag, 'display=') === false) {
        $tag = str_replace('family=', 'display=swap&family=', $tag);
    }
    return $tag;
}, 10, 2);

/**
 * Performance: async загрузка некритичного CSS
 * media="print" → загружается без блокировки → переключается на media="all"
 */
add_filter('style_loader_tag', function($tag, $handle) {
    if (is_admin()) return $tag;
    $async_handles = ['flatsome-shop'];
    if (in_array($handle, $async_handles)) {
        $tag = str_replace("media='all'", "media='print' onload=\"this.media='all'\"", $tag);
    }
    return $tag;
}, 20, 2);

// WebP: теперь обрабатывается nginx content negotiation (map $webp_rewrite + try_files)
// PHP output buffer удалён — нет overhead на буферизацию HTML

/**
 * Schema Enhancement — расширенная структурированная разметка
 * Улучшает сниппеты в Яндексе и Google: цена, бренд, наличие, хлебные крошки, данные компании
 */
add_filter('rank_math/json_ld', function($data, $jsonld) {

    // --- 1. Organization: полные данные компании ---
    foreach ($data as $key => &$entity) {
        if (isset($entity['@type']) && $entity['@type'] === 'Organization') {
            $entity['url'] = 'https://tdpuls.com';
            $entity['logo'] = [
                '@type' => 'ImageObject',
                'url'   => 'https://tdpuls.com/wp-content/uploads/2025/07/logo.png',
            ];
            $entity['telephone'] = '+7 (863) 310-08-07';
            $entity['email']     = 'sales@tdpuls.com';
            $entity['address']   = [
                '@type'           => 'PostalAddress',
                'addressLocality' => 'Ростов-на-Дону',
                'addressRegion'   => 'Ростовская область',
                'addressCountry'  => 'RU',
            ];
            $entity['contactPoint'] = [
                '@type'             => 'ContactPoint',
                'telephone'         => '+7 (863) 310-08-07',
                'contactType'       => 'sales',
                'areaServed'        => 'RU',
                'availableLanguage' => 'Russian',
            ];
            break;
        }
    }
    unset($entity);

    // --- 2. Product: brand, sku, offers ---
    if (is_singular('product')) {
        global $post;
        $product = wc_get_product($post->ID);

        if ($product) {
            foreach ($data as $key => &$entity) {
                if (!isset($entity['@type']) || $entity['@type'] !== 'Product') continue;

                // Brand из атрибута «Производитель»
                $brand_terms = wp_get_post_terms($post->ID, 'pa_proizvoditel', ['fields' => 'names']);
                if (!empty($brand_terms) && !is_wp_error($brand_terms)) {
                    $entity['brand'] = [
                        '@type' => 'Brand',
                        'name'  => $brand_terms[0],
                    ];
                }

                // SKU (WooCommerce или slug)
                $sku = $product->get_sku();
                $entity['sku'] = !empty($sku) ? $sku : $product->get_slug();

                // Чистое название без маркетинговых хвостов
                $entity['name'] = $product->get_name();

                // Offers — цена «от» и наличие
                $price = $product->get_price();
                if (!empty($price) && floatval($price) > 0) {
                    $entity['offers'] = [
                        '@type'         => 'AggregateOffer',
                        'url'           => get_permalink($post->ID),
                        'priceCurrency' => 'RUB',
                        'lowPrice'      => floatval($price),
                        'availability'  => 'https://schema.org/InStock',
                        'offerCount'    => 1,
                        'seller'        => ['@id' => 'https://tdpuls.com/#organization'],
                    ];
                } else {
                    $entity['offers'] = [
                        '@type'         => 'Offer',
                        'url'           => get_permalink($post->ID),
                        'priceCurrency' => 'RUB',
                        'availability'  => 'https://schema.org/InStock',
                        'seller'        => ['@id' => 'https://tdpuls.com/#organization'],
                    ];
                }
                break;
            }
            unset($entity);

            // --- 3. BreadcrumbList ---
            $crumbs = [[
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => 'Главная',
                'item'     => 'https://tdpuls.com/',
            ]];

            $cats = wp_get_post_terms($post->ID, 'product_cat', ['fields' => 'all']);
            if (!empty($cats) && !is_wp_error($cats)) {
                usort($cats, function($a, $b) {
                    return ($a->parent === 0 ? 0 : 1) - ($b->parent === 0 ? 0 : 1);
                });
                $pos = 2;
                foreach ($cats as $cat) {
                    $link = get_term_link($cat);
                    if (!is_wp_error($link)) {
                        $crumbs[] = [
                            '@type'    => 'ListItem',
                            'position' => $pos++,
                            'name'     => $cat->name,
                            'item'     => $link,
                        ];
                    }
                }
            }

            $crumbs[] = [
                '@type'    => 'ListItem',
                'position' => count($crumbs) + 1,
                'name'     => $product->get_name(),
            ];

            $data['BreadcrumbList'] = [
                '@type'           => 'BreadcrumbList',
                '@id'             => get_permalink($post->ID) . '#breadcrumb',
                'itemListElement' => $crumbs,
            ];
        }
    }

    return $data;
}, 20, 2);

/**
 * Сортировка каталога: товары «в наличии» (instock) всегда первыми
 */
add_filter('posts_clauses', function($clauses, $query) {
    global $wpdb;
    if (!$query->get('wc_query') || is_admin()) return $clauses;

    $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS pm_stock_sort ON ({$wpdb->posts}.ID = pm_stock_sort.post_id AND pm_stock_sort.meta_key = '_stock_status')";
    $clauses['orderby'] = "CASE WHEN pm_stock_sort.meta_value = 'instock' THEN 0 WHEN pm_stock_sort.meta_value = 'onbackorder' THEN 1 ELSE 2 END ASC, " . $clauses['orderby'];

    return $clauses;
}, 10, 2);

// Скрываем выбор сортировки в каталоге (сортировка фиксированная: instock первыми)
// Flatsome перевешивает сортировку на свой хук flatsome_category_title_alt
add_action('init', function() {
    remove_action('flatsome_category_title_alt', 'woocommerce_catalog_ordering', 30);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
});

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

// ─── Карточки товаров: скрыть стандартную цену + вывести бейдж на изображении ───

// 1. Скрываем стандартную цену WooCommerce в карточках.
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

if (!function_exists('tdp_format_compact_price')) {
    function tdp_format_compact_price($value) {
        $val = floatval($value);
        if ($val >= 1000000) {
            $m = round($val / 1000000, 1);
            return ($m == intval($m)) ? intval($m) . ' млн' : str_replace('.', ',', $m) . ' млн';
        }
        if ($val >= 1000) {
            $t = round($val / 1000);
            return $t . ' тыс';
        }
        return number_format($val, 0, ',', ' ');
    }
}

if (!function_exists('tdp_get_badge_price_value')) {
    function tdp_get_badge_price_value($product) {
        if (!($product instanceof WC_Product)) return 0;

        if ($product->is_type('variable')) {
            return floatval($product->get_variation_price('min', true));
        }

        return floatval($product->get_price());
    }
}

// 2. Рендерим бейдж цены в штатный badge-container Flatsome (без JS-переноса).
add_filter('flatsome_product_labels', function ($text, $post, $product, $badge_style) {
    $price = tdp_get_badge_price_value($product);
    if ($price <= 0) return $text;

    $text .= '<span class="tdp-price-badge">от ' . esc_html(tdp_format_compact_price($price)) . ' ₽</span>';
    return $text;
}, 20, 4);

// 3. Стили бейджа цены.
add_action('wp_head', function () {
    if (is_admin()) return;
    echo '<style>
    .badge-container .tdp-price-badge{
        position:absolute;
        top:46px;
        left:-10px;
        z-index:3;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:30px;
        border-radius:4px;
        padding:4px 12px;
        background:#00a4e4;
        color:#fff;
        font-size:13px;
        font-weight:500;
        line-height:1.2;
        white-space:nowrap;
        pointer-events:none;
    }
    .single-product .product .product-images{position:relative}
    .single-product .product .product-images .tdp-price-badge{
        top:45px;
    }
    </style>';
}, 11);

// 3. Бейдж «В наличии» на странице товара (product-images не имеет data-status)
add_action('wp_head', function () {
    if (!is_product()) return;
    echo '<style>
    .single-product .product.instock .product-images::after {
        content: "В наличии";
        position: absolute;
        top: 10px;
        left: -10px;
        z-index: 1;
        border-radius: 4px;
        width: 100px;
        height: 30px;
        font-weight: 500;
        font-size: 14px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #2CBE4C;
    }
    </style>';
});
