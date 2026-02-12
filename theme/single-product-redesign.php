<?php
/**
 * Альтернативный шаблон страницы товара — tdpuls.com
 * Доступен только по ?design=new
 *
 * @package flatsome
 */

if (!defined('ABSPATH')) exit;

get_header();

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

if (!$product) {
    echo '<p>Товар не найден.</p>';
    get_footer();
    return;
}

// Данные товара
$title = get_the_title();
$short_desc = $product->get_short_description();
$full_desc = $product->get_description();
$categories = wc_get_product_category_list($product->get_id(), ', ');
$gallery_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
$attributes = $product->get_attributes();

// Собираем все изображения
$images = [];
if ($main_image_id) {
    $images[] = [
        'full' => wp_get_attachment_image_url($main_image_id, 'full'),
        'large' => wp_get_attachment_image_url($main_image_id, 'large'),
        'thumb' => wp_get_attachment_image_url($main_image_id, 'thumbnail'),
        'alt' => get_post_meta($main_image_id, '_wp_attachment_image_alt', true) ?: $title,
    ];
}
foreach ($gallery_ids as $gid) {
    $images[] = [
        'full' => wp_get_attachment_image_url($gid, 'full'),
        'large' => wp_get_attachment_image_url($gid, 'large'),
        'thumb' => wp_get_attachment_image_url($gid, 'thumbnail'),
        'alt' => get_post_meta($gid, '_wp_attachment_image_alt', true) ?: $title,
    ];
}

// Атрибуты товара
$specs = [];
foreach ($attributes as $attr) {
    if ($attr->is_taxonomy()) {
        $terms = wc_get_product_terms($product->get_id(), $attr->get_name(), ['fields' => 'names']);
        $specs[] = [
            'label' => wc_attribute_label($attr->get_name()),
            'value' => implode(', ', $terms),
        ];
    } else {
        $specs[] = [
            'label' => $attr->get_name(),
            'value' => implode(', ', $attr->get_options()),
        ];
    }
}

// Категории
$cat_terms = get_the_terms($product->get_id(), 'product_cat');
$cat_name = '';
$parent_cat_name = '';
if ($cat_terms && !is_wp_error($cat_terms)) {
    foreach ($cat_terms as $ct) {
        if ($ct->parent) {
            $cat_name = $ct->name;
            $parent = get_term($ct->parent, 'product_cat');
            $parent_cat_name = $parent ? $parent->name : '';
        } else {
            if (!$parent_cat_name) $parent_cat_name = $ct->name;
        }
    }
    if (!$cat_name && $cat_terms) {
        $cat_name = $cat_terms[0]->name;
    }
}

// Брошюра — ищем PDF в кастомных полях или контенте
$brochure_url = '';
$raw_content = get_the_content();
if (preg_match('/href=["\']([^"\']*\.pdf)["\']/', $raw_content, $pdf_match)) {
    $brochure_url = $pdf_match[1];
}
// Также проверим short description
if (!$brochure_url && preg_match('/href=["\']([^"\']*\.pdf)["\']/', $short_desc, $pdf_match)) {
    $brochure_url = $pdf_match[1];
}

// Табы
$tabs_content = [];
// Описание
$desc_clean = strip_tags($full_desc, '<p><br><ul><li><ol><strong><em><b><i><h3><h4>');
if ($desc_clean) {
    $tabs_content['description'] = [
        'title' => 'Технологии',
        'content' => $desc_clean,
    ];
}

// Клиническое применение — из UX Builder / post meta
$clinical = get_post_meta($product->get_id(), '_tab_klinicheskoe_primenenie', true);
// Видео
$video = get_post_meta($product->get_id(), '_tab_video', true);

?>

<style>
/* ================================================================
   PRODUCT PAGE REDESIGN — tdpuls.com
   Все стили с префиксом .tdp- для изоляции от темы
   ================================================================ */

.tdp-product-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    color: #1a1a2e;
    line-height: 1.6;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Дев-банер */
.tdp-dev-banner {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
    padding: 10px 20px;
    border-radius: 8px;
    margin: 15px auto;
    max-width: 1200px;
    font-size: 13px;
    text-align: center;
}

/* Хлебные крошки */
.tdp-breadcrumbs {
    padding: 20px 0 10px;
    font-size: 13px;
    color: #888;
}
.tdp-breadcrumbs a {
    color: #888;
    text-decoration: none;
    transition: color 0.2s;
}
.tdp-breadcrumbs a:hover {
    color: #00a4e4;
}
.tdp-breadcrumbs span {
    margin: 0 6px;
    color: #ccc;
}

/* Hero секция */
.tdp-hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    padding: 20px 0 30px;
    align-items: start;
}

/* Галерея */
.tdp-gallery {
    position: relative;
}
.tdp-gallery-main {
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    background: #f8f9fa;
    max-height: 480px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: zoom-in;
    padding: 20px;
}
.tdp-gallery-main img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.3s;
}
.tdp-gallery-main:hover img {
    transform: scale(1.05);
}
.tdp-gallery-thumbs {
    display: flex;
    gap: 10px;
    margin-top: 12px;
}
.tdp-gallery-thumb {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid transparent;
    cursor: pointer;
    background: #f8f9fa;
    transition: border-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.tdp-gallery-thumb.active,
.tdp-gallery-thumb:hover {
    border-color: #00a4e4;
}
.tdp-gallery-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.tdp-badge-stock {
    position: absolute;
    top: 16px;
    left: 16px;
    background: #059669;
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 6px;
}
.tdp-badge-stock svg {
    width: 14px;
    height: 14px;
}
.tdp-badge-sub {
    display: block;
    font-size: 10px;
    font-weight: 500;
    opacity: 0.9;
    letter-spacing: 0;
    margin-top: 1px;
}

/* Info */
.tdp-info {
    padding-top: 5px;
}
.tdp-category {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #00a4e4;
    font-weight: 600;
    margin-bottom: 8px;
}
.tdp-title {
    font-size: 32px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 8px;
    color: #1a1a2e;
}
.tdp-subtitle {
    font-size: 15px;
    color: #666;
    margin-bottom: 16px;
    line-height: 1.5;
}

/* Ключевые характеристики */
.tdp-key-specs {
    background: #f0f4f8;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 16px;
}
.tdp-key-specs-title {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #666;
    font-weight: 600;
    margin-bottom: 14px;
}
.tdp-key-specs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.tdp-spec-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.tdp-spec-icon {
    width: 36px;
    height: 36px;
    background: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #00a4e4;
}
.tdp-spec-icon svg {
    width: 18px;
    height: 18px;
}
.tdp-spec-label {
    font-size: 11px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.tdp-spec-value {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a2e;
}

/* Лизинг якорь */
.tdp-leasing {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    margin-bottom: 16px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    color: inherit;
}
.tdp-leasing:hover {
    border-color: #00a4e4;
    background: #e0f2fe;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 164, 228, 0.12);
    color: inherit;
}
.tdp-leasing-icon {
    width: 40px;
    height: 40px;
    background: #00a4e4;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
}
.tdp-leasing-icon svg {
    width: 20px;
    height: 20px;
}
.tdp-leasing-text {
    font-size: 14px;
    color: #1e40af;
    flex: 1;
}
.tdp-leasing-text strong {
    font-size: 15px;
    display: block;
    margin-top: 2px;
}
.tdp-leasing-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #00a4e4;
    border-radius: 8px;
    color: #fff;
    flex-shrink: 0;
    transition: background 0.2s;
}
.tdp-leasing:hover .tdp-leasing-arrow {
    background: #0090cc;
}
.tdp-leasing-arrow svg {
    width: 16px;
    height: 16px;
}

/* CTA блок */
.tdp-cta-group {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}
.tdp-btn-primary {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 28px;
    background: #00a4e4;
    color: #fff;
    border: 2px solid #00a4e4;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}
.tdp-btn-primary:hover {
    background: #0090cc;
    border-color: #0090cc;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
}
.tdp-btn-primary svg {
    width: 18px;
    height: 18px;
}
.tdp-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 24px;
    background: #fff;
    color: #00a4e4;
    border: 2px solid #d1d5db;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}
.tdp-btn-secondary:hover {
    border-color: #00a4e4;
    background: #f0f4ff;
    color: #00a4e4;
}
.tdp-btn-secondary svg {
    width: 18px;
    height: 18px;
}

/* Trust signals */
.tdp-trust {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.tdp-trust-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 13px;
    color: #555;
}
.tdp-trust-item strong {
    color: #1a1a2e;
    font-weight: 700;
}
.tdp-trust-icon {
    width: 32px;
    height: 32px;
    background: #e8f5e9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #059669;
}
.tdp-trust-icon svg {
    width: 16px;
    height: 16px;
}

/* ================================================================
   Секция табов
   ================================================================ */
.tdp-tabs-section {
    margin: 50px 0;
}
.tdp-tabs-nav {
    display: flex;
    border-bottom: 2px solid #e5e7eb;
    gap: 0;
    margin-bottom: 30px;
}
.tdp-tab-btn {
    padding: 14px 24px;
    font-size: 15px;
    font-weight: 600;
    color: #666;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.tdp-tab-btn:hover {
    color: #00a4e4;
}
.tdp-tab-btn.active {
    color: #00a4e4;
    border-bottom-color: #00a4e4;
}
.tdp-tab-content {
    display: none;
    animation: tdpFadeIn 0.3s ease;
}
.tdp-tab-content.active {
    display: block;
}
@keyframes tdpFadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Таб: Характеристики */
.tdp-specs-table {
    width: 100%;
    border-collapse: collapse;
}
.tdp-specs-table tr:nth-child(even) {
    background: #f8f9fa;
}
.tdp-specs-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 15px;
}
.tdp-specs-table td:first-child {
    color: #666;
    font-weight: 500;
    width: 40%;
}
.tdp-specs-table td:last-child {
    font-weight: 600;
    color: #1a1a2e;
}

/* Таб: Описание (технологии) */
.tdp-tech-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.tdp-tech-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 24px;
    border-left: 4px solid #00a4e4;
}
.tdp-tech-card h4 {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 8px;
}
.tdp-tech-card p {
    font-size: 14px;
    color: #555;
    margin: 0;
    line-height: 1.6;
}

/* Описание raw */
.tdp-desc-content {
    font-size: 15px;
    color: #444;
    line-height: 1.8;
}
.tdp-desc-content p {
    margin-bottom: 16px;
}
.tdp-desc-content strong {
    color: #1a1a2e;
}

/* ================================================================
   CTA Баннер
   ================================================================ */
.tdp-cta-banner {
    background: linear-gradient(135deg, #00a4e4 0%, #0080c0 100%);
    border-radius: 16px;
    padding: 50px 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    margin: 50px 0;
    color: #fff;
}
.tdp-cta-banner-text h3 {
    font-size: 26px;
    font-weight: 700;
    margin: 0 0 8px;
}
.tdp-cta-banner-text p {
    font-size: 16px;
    opacity: 0.85;
    margin: 0;
}
.tdp-cta-banner-actions {
    display: flex;
    gap: 12px;
    flex-shrink: 0;
}
.tdp-btn-white {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: #fff;
    color: #00a4e4;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}
.tdp-btn-white:hover {
    background: #f0f4ff;
    color: #0090cc;
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}
.tdp-btn-outline-white {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: transparent;
    color: #fff;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}
.tdp-btn-outline-white:hover {
    border-color: #fff;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

/* ================================================================
   Похожие товары
   ================================================================ */
.tdp-related {
    margin: 50px 0 60px;
}
.tdp-section-title {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 30px;
}
.tdp-related-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}
.tdp-related-card {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    transition: all 0.3s;
    background: #fff;
    text-decoration: none;
    color: inherit;
    display: block;
}
.tdp-related-card:hover {
    border-color: #00a4e4;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    transform: translateY(-4px);
    color: inherit;
}
.tdp-related-card-img {
    width: 100%;
    aspect-ratio: 1;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
}
.tdp-related-card-img img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.tdp-related-card-body {
    padding: 16px;
}
.tdp-related-card-cat {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #00a4e4;
    font-weight: 600;
    margin-bottom: 4px;
}
.tdp-related-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 6px;
    line-height: 1.3;
}
.tdp-related-card-desc {
    font-size: 12px;
    color: #888;
    line-height: 1.4;
}
.tdp-related-card-action {
    padding: 0 16px 16px;
}
.tdp-related-card-link {
    font-size: 13px;
    color: #00a4e4;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.tdp-related-card-link:hover {
    color: #0090cc;
}

/* ================================================================
   Responsive
   ================================================================ */
@media (max-width: 968px) {
    .tdp-hero {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    .tdp-key-specs-grid {
        grid-template-columns: 1fr;
    }
    .tdp-tech-grid {
        grid-template-columns: 1fr;
    }
    .tdp-cta-banner {
        flex-direction: column;
        padding: 30px;
        text-align: center;
    }
    .tdp-cta-banner-actions {
        flex-direction: column;
        width: 100%;
    }
    .tdp-cta-banner-actions a {
        text-align: center;
        justify-content: center;
    }
    .tdp-related-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .tdp-tabs-nav {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}

@media (max-width: 600px) {
    .tdp-title {
        font-size: 24px;
    }
    .tdp-cta-group {
        flex-direction: column;
    }
    .tdp-trust {
        grid-template-columns: 1fr;
    }
    .tdp-related-grid {
        grid-template-columns: 1fr;
    }
    .tdp-tab-btn {
        padding: 12px 16px;
        font-size: 14px;
    }
}

/* Lightbox */
.tdp-lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
}
.tdp-lightbox.active {
    display: flex;
}
.tdp-lightbox img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 8px;
}
.tdp-lightbox-close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: #fff;
    font-size: 36px;
    cursor: pointer;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    transition: background 0.2s;
    border: none;
}
.tdp-lightbox-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* Модалка брошюры */
.tdp-brochure-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 99998;
    align-items: center;
    justify-content: center;
}
.tdp-brochure-modal.active {
    display: flex;
}
.tdp-brochure-modal-box {
    background: #fff;
    border-radius: 16px;
    max-width: 420px;
    width: 90vw;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
    animation: tdpFadeIn 0.3s ease;
    position: relative;
}
.tdp-brochure-modal-header {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    padding: 24px 28px 20px;
    color: #fff;
}
.tdp-brochure-modal-header h3 {
    margin: 0 0 6px;
    font-size: 18px;
    font-weight: 700;
}
.tdp-brochure-modal-header p {
    margin: 0;
    font-size: 13px;
    opacity: 0.85;
}
.tdp-brochure-modal-body {
    padding: 24px 28px;
}
.tdp-brochure-field {
    margin-bottom: 14px;
}
.tdp-brochure-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.tdp-brochure-field input {
    width: 100%;
    padding: 12px 14px;
    border: 1.5px solid #d1d5db;
    border-radius: 10px;
    font-size: 15px;
    font-family: inherit;
    box-sizing: border-box;
    background: #f9fafb;
    transition: all 0.2s;
}
.tdp-brochure-field input:focus {
    border-color: #059669;
    outline: none;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}
.tdp-brochure-field input::placeholder {
    color: #9ca3af;
}
.tdp-brochure-btns {
    display: flex;
    gap: 10px;
}
.tdp-brochure-submit {
    flex: 1;
    padding: 13px;
    background: #059669;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.tdp-brochure-submit:hover {
    background: #047857;
}
.tdp-brochure-skip {
    padding: 13px 18px;
    background: none;
    color: #6b7280;
    border: 1.5px solid #d1d5db;
    border-radius: 10px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}
.tdp-brochure-skip:hover {
    border-color: #9ca3af;
    color: #374151;
}
.tdp-brochure-close {
    position: absolute;
    top: 12px;
    right: 14px;
    background: rgba(255,255,255,0.15);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.tdp-brochure-close:hover {
    background: rgba(255,255,255,0.25);
}
.tdp-brochure-msg {
    text-align: center;
    padding: 8px;
    margin-top: 10px;
    border-radius: 8px;
    font-size: 13px;
    display: none;
}
.tdp-brochure-msg.success {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    display: block;
}
</style>

<!-- Dev Banner -->
<div class="tdp-dev-banner">
    ⚙️ Режим предпросмотра нового дизайна — виден только по ссылке с <code>?design=new</code>
</div>

<div class="tdp-product-page">

    <!-- Хлебные крошки -->
    <div class="tdp-breadcrumbs">
        <a href="<?php echo home_url(); ?>">Главная</a>
        <span>/</span>
        <a href="<?php echo home_url('/shop/'); ?>">Каталог</a>
        <?php if ($parent_cat_name): ?>
            <span>/</span>
            <a href="<?php echo get_term_link($cat_terms[0]->parent ?: $cat_terms[0]->term_id, 'product_cat'); ?>"><?php echo esc_html($parent_cat_name); ?></a>
        <?php endif; ?>
        <?php if ($cat_name && $cat_name !== $parent_cat_name): ?>
            <span>/</span>
            <span><?php echo esc_html($cat_name); ?></span>
        <?php endif; ?>
    </div>

    <!-- HERO -->
    <div class="tdp-hero">

        <!-- Галерея -->
        <div class="tdp-gallery">
            <div class="tdp-badge-stock">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                <span>На складе в РФ<small class="tdp-badge-sub">Отгрузка за 3 дня</small></span>
            </div>
            <div class="tdp-gallery-main" id="tdpGalleryMain">
                <?php if (!empty($images[0])): ?>
                    <img src="<?php echo esc_url($images[0]['large']); ?>"
                         alt="<?php echo esc_attr($images[0]['alt']); ?>"
                         data-full="<?php echo esc_url($images[0]['full']); ?>"
                         id="tdpMainImg">
                <?php endif; ?>
            </div>
            <?php if (count($images) > 1): ?>
                <div class="tdp-gallery-thumbs">
                    <?php foreach ($images as $idx => $img): ?>
                        <div class="tdp-gallery-thumb <?php echo $idx === 0 ? 'active' : ''; ?>"
                             data-large="<?php echo esc_url($img['large']); ?>"
                             data-full="<?php echo esc_url($img['full']); ?>">
                            <img src="<?php echo esc_url($img['thumb']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Информация -->
        <div class="tdp-info">
            <div class="tdp-category"><?php echo esc_html($cat_name ?: $parent_cat_name); ?></div>
            <h1 class="tdp-title"><?php echo esc_html($title); ?></h1>
            <p class="tdp-subtitle"><?php
                $clean_desc = strip_shortcodes($short_desc);
                $clean_desc = preg_replace('/\[[^\]]*\]/', '', $clean_desc);
                $clean_desc = strip_tags($clean_desc);
                $clean_desc = trim(str_replace(['Брошюра', 'В избранное', 'Сравнить', '&nbsp;'], '', $clean_desc));
                $clean_desc = trim(preg_replace('/\s+/', ' ', $clean_desc));
                echo esc_html($clean_desc);
            ?></p>

            <!-- Ключевые характеристики -->
            <?php if (!empty($specs)): ?>
            <div class="tdp-key-specs">
                <div class="tdp-key-specs-title">Ключевые параметры</div>
                <div class="tdp-key-specs-grid">
                    <?php
                    // Показываем до 6 ключевых характеристик, пропускаем «Производитель» (дублирует заголовок)
                    $skip_labels = ['Производитель', 'производитель', 'Бренд', 'Brand'];
                    $shown = 0;
                    foreach ($specs as $spec):
                        if ($shown >= 6) break;
                        if (in_array($spec['label'], $skip_labels)) continue;
                        $shown++;
                    ?>
                        <div class="tdp-spec-item">
                            <div class="tdp-spec-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            </div>
                            <div>
                                <div class="tdp-spec-label"><?php echo esc_html($spec['label']); ?></div>
                                <div class="tdp-spec-value"><?php echo esc_html($spec['value']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Лизинг -->
            <a href="#b24-modal" class="tdp-leasing">
                <div class="tdp-leasing-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                </div>
                <div class="tdp-leasing-text">
                    Доступен лизинг и рассрочка
                    <strong>Рассчитать график платежей</strong>
                </div>
                <div class="tdp-leasing-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
            </a>

            <!-- CTA кнопки -->
            <div class="tdp-cta-group">
                <a href="#b24-modal" class="tdp-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Получить КП
                </a>
                <?php if ($brochure_url): ?>
                <a href="#" class="tdp-btn-secondary" id="tdpBrochureBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Брошюра
                </a>
                <?php endif; ?>
            </div>

            <!-- Trust signals -->
            <div class="tdp-trust">
                <div class="tdp-trust-item">
                    <div class="tdp-trust-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <strong>Гарантия</strong> от 12 мес.
                </div>
                <div class="tdp-trust-item">
                    <div class="tdp-trust-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="23 7 16 12 16 7"/></svg>
                    </div>
                    <strong>Доставка</strong> по всей РФ
                </div>
                <div class="tdp-trust-item">
                    <div class="tdp-trust-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </div>
                    <strong>Монтаж</strong> и запуск
                </div>
                <div class="tdp-trust-item">
                    <div class="tdp-trust-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    <strong>Обучение</strong> персонала
                </div>
            </div>

        </div>
    </div>

    <!-- ================================================================
         ТАБЫ
         ================================================================ -->
    <div class="tdp-tabs-section">
        <div class="tdp-tabs-nav" id="tdpTabsNav">
            <?php if (!empty($specs)): ?>
                <button class="tdp-tab-btn active" data-tab="specs">Характеристики</button>
            <?php endif; ?>
            <?php if (!empty($full_desc)): ?>
                <button class="tdp-tab-btn" data-tab="description">Описание</button>
            <?php endif; ?>
            <?php
            // Получаем табы из WooCommerce (кастомные табы Flatsome)
            $custom_tabs = get_post_meta($product->get_id(), '_custom_tab_content', true);
            // Проверим наличие кастомных табов из Flatsome
            $wc_tabs = apply_filters('woocommerce_product_tabs', []);
            $extra_tabs = [];
            foreach ($wc_tabs as $key => $tab) {
                if (!in_array($key, ['description', 'additional_information', 'reviews'])) {
                    $extra_tabs[$key] = $tab;
                }
            }
            foreach ($extra_tabs as $key => $tab): ?>
                <button class="tdp-tab-btn" data-tab="<?php echo esc_attr($key); ?>"><?php echo esc_html($tab['title']); ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Таб: Характеристики -->
        <?php if (!empty($specs)): ?>
        <div class="tdp-tab-content active" data-tab="specs">
            <table class="tdp-specs-table">
                <?php foreach ($specs as $spec): ?>
                <tr>
                    <td><?php echo esc_html($spec['label']); ?></td>
                    <td><?php echo esc_html($spec['value']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <!-- Таб: Описание -->
        <?php if (!empty($full_desc)): ?>
        <div class="tdp-tab-content" data-tab="description">
            <div class="tdp-desc-content">
                <?php echo apply_filters('the_content', $full_desc); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Кастомные табы -->
        <?php foreach ($extra_tabs as $key => $tab): ?>
        <div class="tdp-tab-content" data-tab="<?php echo esc_attr($key); ?>">
            <div class="tdp-desc-content">
                <?php
                if (isset($tab['callback'])) {
                    call_user_func($tab['callback'], $key, $tab);
                }
                ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- CTA Баннер -->
    <div class="tdp-cta-banner">
        <div class="tdp-cta-banner-text">
            <h3>Нужна консультация по <?php echo esc_html($title); ?>?</h3>
            <p>Получите персональное коммерческое предложение с расчётом лизинга</p>
        </div>
        <div class="tdp-cta-banner-actions">
            <a href="#b24-modal" class="tdp-btn-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Получить КП
            </a>
            <?php if ($brochure_url): ?>
            <a href="#" class="tdp-btn-outline-white tdp-brochure-trigger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Скачать брошюру
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Похожие товары -->
    <?php
    $related_ids = wc_get_related_products($product->get_id(), 4);
    if (!empty($related_ids)):
    ?>
    <div class="tdp-related">
        <h2 class="tdp-section-title">Похожие модели</h2>
        <div class="tdp-related-grid">
            <?php foreach ($related_ids as $rid):
                $rp = wc_get_product($rid);
                if (!$rp) continue;
                $rimg = wp_get_attachment_image_url($rp->get_image_id(), 'woocommerce_thumbnail');
                $rcats = get_the_terms($rid, 'product_cat');
                $rcat_name = ($rcats && !is_wp_error($rcats)) ? $rcats[0]->name : '';
                $rshort = strip_shortcodes($rp->get_short_description());
                $rshort = preg_replace('/\[[^\]]*\]/', '', $rshort);
                $rshort = strip_tags($rshort);
                $rshort = trim(str_replace(['Брошюра', 'В избранное', 'Сравнить', '&nbsp;'], '', $rshort));
                $rshort = trim(preg_replace('/\s+/', ' ', $rshort));
                if (mb_strlen($rshort) > 80) {
                    $rshort = mb_substr($rshort, 0, mb_strrpos(mb_substr($rshort, 0, 80), ' ')) . '…';
                }
                $rlink = get_permalink($rid);
            ?>
            <a href="<?php echo esc_url($rlink); ?>" class="tdp-related-card">
                <div class="tdp-related-card-img">
                    <?php if ($rimg): ?>
                        <img src="<?php echo esc_url($rimg); ?>" alt="<?php echo esc_attr($rp->get_name()); ?>">
                    <?php endif; ?>
                </div>
                <div class="tdp-related-card-body">
                    <?php if ($rcat_name): ?>
                        <div class="tdp-related-card-cat"><?php echo esc_html($rcat_name); ?></div>
                    <?php endif; ?>
                    <div class="tdp-related-card-title"><?php echo esc_html($rp->get_name()); ?></div>
                    <?php if ($rshort): ?>
                        <div class="tdp-related-card-desc"><?php echo esc_html($rshort); ?></div>
                    <?php endif; ?>
                </div>
                <div class="tdp-related-card-action">
                    <span class="tdp-related-card-link">
                        Подробнее
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Модалка брошюры -->
<?php if ($brochure_url): ?>
<div class="tdp-brochure-modal" id="tdpBrochureModal">
    <div class="tdp-brochure-modal-box">
        <button class="tdp-brochure-close" id="tdpBrochureClose">&times;</button>
        <div class="tdp-brochure-modal-header">
            <h3>Куда отправить брошюру и спецификацию?</h3>
            <p>Пришлём полную документацию на email или WhatsApp</p>
        </div>
        <div class="tdp-brochure-modal-body">
            <div class="tdp-brochure-field">
                <label>Email или WhatsApp</label>
                <input type="text" id="tdpBrochureContact" placeholder="email@clinic.ru или +7 900 000-00-00">
            </div>
            <div class="tdp-brochure-btns">
                <button class="tdp-brochure-submit" id="tdpBrochureSend">Отправить брошюру</button>
                <button class="tdp-brochure-skip" id="tdpBrochureSkip">Скачать</button>
            </div>
            <div class="tdp-brochure-msg" id="tdpBrochureMsg"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Lightbox -->
<div class="tdp-lightbox" id="tdpLightbox">
    <button class="tdp-lightbox-close" id="tdpLightboxClose">&times;</button>
    <img src="" alt="" id="tdpLightboxImg">
</div>

<script>
(function() {
    // Галерея: переключение изображений
    var thumbs = document.querySelectorAll('.tdp-gallery-thumb');
    var mainImg = document.getElementById('tdpMainImg');

    thumbs.forEach(function(thumb) {
        thumb.addEventListener('click', function() {
            thumbs.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');
            if (mainImg) {
                mainImg.src = this.getAttribute('data-large');
                mainImg.setAttribute('data-full', this.getAttribute('data-full'));
            }
        });
    });

    // Lightbox
    var lightbox = document.getElementById('tdpLightbox');
    var lightboxImg = document.getElementById('tdpLightboxImg');
    var galleryMain = document.getElementById('tdpGalleryMain');

    if (galleryMain && lightbox) {
        galleryMain.addEventListener('click', function() {
            var img = this.querySelector('img');
            if (img) {
                lightboxImg.src = img.getAttribute('data-full') || img.src;
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });

        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox || e.target.id === 'tdpLightboxClose' || e.target.closest('#tdpLightboxClose')) {
                lightbox.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.classList.contains('active')) {
                lightbox.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // Табы
    var tabBtns = document.querySelectorAll('.tdp-tab-btn');
    var tabContents = document.querySelectorAll('.tdp-tab-content');

    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tab = this.getAttribute('data-tab');
            tabBtns.forEach(function(b) { b.classList.remove('active'); });
            tabContents.forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            var content = document.querySelector('.tdp-tab-content[data-tab="' + tab + '"]');
            if (content) content.classList.add('active');
        });
    });

    // Модалка брошюры
    var brochureModal = document.getElementById('tdpBrochureModal');
    var brochureUrl = <?php echo json_encode($brochure_url); ?>;

    if (brochureModal) {
        function openBrochureModal(e) {
            e.preventDefault();
            brochureModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            var input = document.getElementById('tdpBrochureContact');
            if (input) setTimeout(function() { input.focus(); }, 200);
        }
        function closeBrochureModal() {
            brochureModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Открытие по обеим кнопкам «Брошюра»
        var brBtn = document.getElementById('tdpBrochureBtn');
        if (brBtn) brBtn.addEventListener('click', openBrochureModal);
        document.querySelectorAll('.tdp-brochure-trigger').forEach(function(b) {
            b.addEventListener('click', openBrochureModal);
        });

        // Закрытие
        document.getElementById('tdpBrochureClose').addEventListener('click', closeBrochureModal);
        brochureModal.addEventListener('click', function(e) {
            if (e.target === brochureModal) closeBrochureModal();
        });

        // «Скачать» — просто открываем PDF
        document.getElementById('tdpBrochureSkip').addEventListener('click', function() {
            window.open(brochureUrl, '_blank');
            closeBrochureModal();
        });

        // «Отправить брошюру» — лид в Б24 + скачка
        document.getElementById('tdpBrochureSend').addEventListener('click', function() {
            var contact = document.getElementById('tdpBrochureContact').value.trim();
            var msg = document.getElementById('tdpBrochureMsg');
            if (!contact) {
                document.getElementById('tdpBrochureContact').focus();
                return;
            }

            // Определяем тип контакта
            var isEmail = contact.indexOf('@') > -1;
            var isPhone = /[\d\+]/.test(contact) && contact.replace(/\D/g, '').length >= 10;

            // Отправляем лид в Б24 через AJAX
            var data = new FormData();
            data.append('action', 'b24_submit_lead');
            data.append('nonce', document.querySelector('#b24-lead-form input[name="nonce"]') ?
                document.querySelector('#b24-lead-form input[name="nonce"]').value :
                '<?php echo wp_create_nonce("b24_lead_form"); ?>');
            data.append('name', isEmail ? contact.split('@')[0] : 'Запрос брошюры');
            data.append('phone', isPhone ? contact : '');
            data.append('email', isEmail ? contact : '');
            data.append('product', 'Брошюра: ' + <?php echo json_encode($title); ?>);
            data.append('page_url', window.location.href.split('?')[0]);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: data
            }).then(function() {
                msg.textContent = 'Отправлено! Открываем брошюру...';
                msg.className = 'tdp-brochure-msg success';
                setTimeout(function() {
                    window.open(brochureUrl, '_blank');
                    closeBrochureModal();
                    msg.style.display = 'none';
                    msg.className = 'tdp-brochure-msg';
                }, 1500);
            }).catch(function() {
                window.open(brochureUrl, '_blank');
                closeBrochureModal();
            });
        });
    }
})();
</script>

<?php get_footer(); ?>
