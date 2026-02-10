<?php
/**
 * Hub-лендинг: ВСУЗИ оборудование Philips Volcano
 * URL: /vsuzi/
 *
 * @package flatsome
 */

if (!defined('ABSPATH')) exit;

get_header();

// Продукты ВСУЗИ — ссылки на существующие карточки
$products = [
    [
        'title' => 'Eagle Eye Platinum',
        'subtitle' => 'Цифровой IVUS-катетер №1 в мире',
        'desc' => 'Plug-and-play технология, частота 20 МГц, совместимость с 5F катетерами. Гидрофильное покрытие GlyDx, 3 рентгеноконтрастных маркера.',
        'specs' => ['20 МГц', 'Цифровой', '3.5F', '0.014"'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/eagle-eye-platinum-tsifrovoj-kateter-dlya-vsuzi/',
        'tag' => 'Бестселлер',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
    ],
    [
        'title' => 'Eagle Eye Platinum ST',
        'subtitle' => 'Короткий кончик для дистальных поражений',
        'desc' => 'Расстояние кончик-датчик всего 2,5 мм. Визуализация дистальных участков коронарных артерий, недоступных стандартным катетерам.',
        'specs' => ['20 МГц', 'Short Tip', '3.5F', '5F совм.'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/eagle-eye-platinum-st-tsifrovoj-kateter-dlya-vsuzi-s-korotkim-konchikom/',
        'tag' => 'Short Tip',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>',
    ],
    [
        'title' => 'Philips IntraSight Mobile',
        'subtitle' => 'Мобильная платформа ВСУЗИ + FFR/iFR',
        'desc' => 'Интервенционная платформа нового поколения. Сочетает внутрисосудистый ультразвук и оценку физиологии коронарных артерий в одной системе.',
        'specs' => ['IVUS + FFR', 'SyncVision', 'Мобильная', 'Azurion'],
        'url' => '/shop/informatsionnye-sistemy/philips-intrasight-mobile-mobilnaya-platforma-dlya-intervenionnyh-vmeshatelstv/',
        'tag' => 'Платформа',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>',
    ],
    [
        'title' => 'Volcano Core Mobile',
        'subtitle' => 'Система высокоточной визуализации',
        'desc' => 'Мобильная система, объединяющая методы визуализации и анализа физиологических параметров для принятия решений при ЧКВ.',
        'specs' => ['IVUS + Физ.', 'Мобильная', 'Мультимод.', 'ЧКВ'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/core-mobile-sistema-dlya-vypolneniya-vysokotochnoj-terapii/',
        'tag' => 'Система',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
    ],
];

// FAQ
$faqs = [
    [
        'q' => 'Что такое ВСУЗИ и для чего оно применяется?',
        'a' => 'ВСУЗИ (внутрисосудистое ультразвуковое исследование) — метод визуализации коронарных артерий изнутри с помощью миниатюрного ультразвукового датчика на кончике катетера. Применяется для точной оценки атеросклеротических поражений, оптимизации стентирования и контроля результатов ЧКВ (чрескожного коронарного вмешательства).',
    ],
    [
        'q' => 'Чем ВСУЗИ отличается от обычной коронарографии?',
        'a' => 'Коронарография показывает только силуэт (люминограмму) сосуда, тогда как ВСУЗИ визуализирует стенку артерии в поперечном сечении — состав бляшки, степень стеноза, размер сосуда. Это позволяет точнее подобрать размер стента и оценить результат имплантации.',
    ],
    [
        'q' => 'Какие ВСУЗИ-катетеры Philips Volcano вы предлагаете?',
        'a' => 'Мы поставляем полную линейку Philips Volcano для коронарного ВСУЗИ: цифровые катетеры Eagle Eye Platinum и Eagle Eye Platinum ST, а также платформы IntraSight и Core Mobile для визуализации и оценки физиологии.',
    ],
    [
        'q' => 'Сколько стоит оборудование ВСУЗИ?',
        'a' => 'Стоимость зависит от конфигурации: катетеры — расходный материал для каждой процедуры, платформы (IntraSight, Core Mobile) — стационарное оборудование для рентген-операционной. Запросите индивидуальное коммерческое предложение — мы подберём оптимальную конфигурацию для вашей клиники.',
    ],
    [
        'q' => 'ВСУЗИ или ОКТ — что лучше?',
        'a' => 'Оба метода дополняют друг друга. ВСУЗИ обеспечивает глубокое проникновение ультразвука (до 10 мм) и не требует очистки сосуда от крови. ОКТ даёт более высокое разрешение (10 мкм vs 100 мкм). Для оценки размера сосуда и оптимизации стентирования ВСУЗИ имеет наибольшую доказательную базу.',
    ],
    [
        'q' => 'Как организовать поставку ВСУЗИ оборудования?',
        'a' => 'Оставьте заявку на сайте или свяжитесь с нами по телефону. Мы подготовим коммерческое предложение, организуем демонстрацию оборудования и обеспечим техническую поддержку и обучение персонала.',
    ],
];

?>

<style>
/* ================================================================
   VSUZI HUB PAGE — tdpuls.com
   Все стили с префиксом .tdp-vsuzi для изоляции от темы
   ================================================================ */

:root {
    --tdp-blue: #00a4e4;
    --tdp-blue-hover: #0090cc;
    --tdp-blue-light: #e0f2fe;
    --tdp-blue-bg: #f0f7ff;
    --tdp-dark: #1a1a2e;
    --tdp-gray: #64748b;
    --tdp-gray-light: #94a3b8;
    --tdp-green: #059669;
    --tdp-green-light: #ecfdf5;
    --tdp-bg: #f8fafc;
    --tdp-white: #ffffff;
    --tdp-radius: 12px;
    --tdp-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
    --tdp-shadow-lg: 0 10px 25px rgba(0,0,0,0.08), 0 4px 10px rgba(0,0,0,0.04);
}

.tdp-vsuzi {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    color: var(--tdp-dark);
    line-height: 1.6;
}

.tdp-vsuzi * {
    box-sizing: border-box;
}

.tdp-vsuzi a {
    text-decoration: none;
    color: inherit;
}

.tdp-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ===== HERO ===== */
.tdp-vsuzi-hero {
    background: linear-gradient(135deg, #0a1628 0%, #1a2744 50%, #0d2137 100%);
    padding: 70px 0 80px;
    position: relative;
    overflow: hidden;
}

.tdp-vsuzi-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(0,164,228,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.tdp-vsuzi-hero::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(5,150,105,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.tdp-hero-content {
    position: relative;
    z-index: 1;
    max-width: 800px;
}

.tdp-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,164,228,0.15);
    border: 1px solid rgba(0,164,228,0.3);
    color: #7dd3fc;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 24px;
    letter-spacing: 0.3px;
}

.tdp-hero-badge svg {
    width: 16px;
    height: 16px;
}

.tdp-hero-h1 {
    font-size: 44px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.15;
    margin: 0 0 20px;
    letter-spacing: -0.5px;
}

.tdp-hero-h1 span {
    color: var(--tdp-blue);
}

.tdp-hero-desc {
    font-size: 18px;
    color: #94a3b8;
    line-height: 1.7;
    margin-bottom: 36px;
    max-width: 640px;
}

.tdp-hero-actions {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.tdp-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s;
    border: none;
    letter-spacing: 0.2px;
}

.tdp-btn-primary {
    background: var(--tdp-blue);
    color: #fff;
}

.tdp-btn-primary:hover {
    background: var(--tdp-blue-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,164,228,0.35);
    color: #fff;
}

.tdp-btn-outline {
    background: transparent;
    color: #cbd5e1;
    border: 1.5px solid rgba(148,163,184,0.3);
}

.tdp-btn-outline:hover {
    border-color: var(--tdp-blue);
    color: var(--tdp-blue);
    background: rgba(0,164,228,0.08);
}

.tdp-btn svg {
    width: 18px;
    height: 18px;
}

/* Hero stats */
.tdp-hero-stats {
    display: flex;
    gap: 40px;
    margin-top: 50px;
    padding-top: 36px;
    border-top: 1px solid rgba(148,163,184,0.15);
}

.tdp-hero-stat-value {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}

.tdp-hero-stat-value span {
    color: var(--tdp-blue);
}

.tdp-hero-stat-label {
    font-size: 13px;
    color: #64748b;
    margin-top: 6px;
    line-height: 1.4;
}

/* ===== SECTION COMMON ===== */
.tdp-section {
    padding: 70px 0;
}

.tdp-section-alt {
    background: var(--tdp-bg);
}

.tdp-section-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--tdp-dark);
    margin: 0 0 12px;
    line-height: 1.2;
}

.tdp-section-subtitle {
    font-size: 16px;
    color: var(--tdp-gray);
    margin-bottom: 40px;
    max-width: 600px;
}

.tdp-section-center {
    text-align: center;
}

.tdp-section-center .tdp-section-subtitle {
    margin-left: auto;
    margin-right: auto;
}

/* ===== WHY IVUS ===== */
.tdp-benefits-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.tdp-benefit-card {
    background: var(--tdp-white);
    border-radius: var(--tdp-radius);
    padding: 32px 28px;
    box-shadow: var(--tdp-shadow);
    transition: all 0.3s;
    border: 1px solid #e2e8f0;
}

.tdp-benefit-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--tdp-shadow-lg);
    border-color: var(--tdp-blue);
}

.tdp-benefit-icon {
    width: 52px;
    height: 52px;
    background: var(--tdp-blue-light);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--tdp-blue);
    margin-bottom: 20px;
}

.tdp-benefit-icon svg {
    width: 26px;
    height: 26px;
}

.tdp-benefit-icon--green {
    background: var(--tdp-green-light);
    color: var(--tdp-green);
}

.tdp-benefit-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--tdp-dark);
}

.tdp-benefit-text {
    font-size: 14px;
    color: var(--tdp-gray);
    line-height: 1.65;
}

.tdp-benefit-stat {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 14px;
    padding: 6px 12px;
    background: var(--tdp-green-light);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    color: var(--tdp-green);
}

/* ===== PRODUCTS CATALOG ===== */
.tdp-products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.tdp-product-card {
    background: var(--tdp-white);
    border-radius: var(--tdp-radius);
    padding: 32px;
    box-shadow: var(--tdp-shadow);
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    transition: all 0.3s;
}

.tdp-product-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--tdp-shadow-lg);
    border-color: var(--tdp-blue);
}

.tdp-product-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 16px;
}

.tdp-product-icon {
    width: 48px;
    height: 48px;
    background: var(--tdp-blue-bg);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--tdp-blue);
}

.tdp-product-icon svg {
    width: 24px;
    height: 24px;
}

.tdp-product-tag {
    padding: 4px 12px;
    background: var(--tdp-blue-light);
    color: var(--tdp-blue);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.tdp-product-name {
    font-size: 22px;
    font-weight: 700;
    color: var(--tdp-dark);
    margin-bottom: 4px;
}

.tdp-product-subtitle {
    font-size: 14px;
    color: var(--tdp-blue);
    font-weight: 500;
    margin-bottom: 12px;
}

.tdp-product-desc {
    font-size: 14px;
    color: var(--tdp-gray);
    line-height: 1.65;
    margin-bottom: 20px;
    flex: 1;
}

.tdp-product-specs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.tdp-product-spec {
    padding: 5px 12px;
    background: var(--tdp-bg);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--tdp-gray);
}

.tdp-product-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--tdp-blue);
    font-weight: 600;
    font-size: 14px;
    transition: gap 0.2s;
}

.tdp-product-link:hover {
    gap: 12px;
    color: var(--tdp-blue-hover);
}

.tdp-product-link svg {
    width: 18px;
    height: 18px;
}

/* ===== HOW IT WORKS ===== */
.tdp-how-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    counter-reset: step;
}

.tdp-how-step {
    text-align: center;
    position: relative;
}

.tdp-how-step::before {
    counter-increment: step;
    content: counter(step);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: var(--tdp-blue);
    color: #fff;
    border-radius: 50%;
    font-size: 20px;
    font-weight: 700;
    margin: 0 auto 16px;
}

.tdp-how-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--tdp-dark);
}

.tdp-how-text {
    font-size: 13px;
    color: var(--tdp-gray);
    line-height: 1.6;
}

/* ===== COMPARISON TABLE ===== */
.tdp-compare-wrap {
    overflow-x: auto;
}

.tdp-compare-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: var(--tdp-radius);
    overflow: hidden;
    box-shadow: var(--tdp-shadow);
    font-size: 14px;
}

.tdp-compare-table thead {
    background: linear-gradient(135deg, #0a1628, #1a2744);
}

.tdp-compare-table th {
    padding: 16px 20px;
    color: #fff;
    font-weight: 600;
    text-align: left;
    font-size: 14px;
}

.tdp-compare-table th:first-child {
    width: 30%;
}

.tdp-compare-table th.tdp-highlight {
    background: rgba(0,164,228,0.2);
    color: #7dd3fc;
}

.tdp-compare-table td {
    padding: 14px 20px;
    border-bottom: 1px solid #e2e8f0;
    color: var(--tdp-gray);
}

.tdp-compare-table tr:last-child td {
    border-bottom: none;
}

.tdp-compare-table tbody tr {
    background: var(--tdp-white);
    transition: background 0.15s;
}

.tdp-compare-table tbody tr:hover {
    background: var(--tdp-blue-bg);
}

.tdp-compare-table td:first-child {
    font-weight: 600;
    color: var(--tdp-dark);
}

.tdp-compare-table td.tdp-highlight {
    background: rgba(0,164,228,0.04);
    color: var(--tdp-dark);
    font-weight: 500;
}

.tdp-compare-check {
    color: var(--tdp-green);
    font-weight: 700;
}

.tdp-compare-cross {
    color: #cbd5e1;
}

/* ===== EVIDENCE ===== */
.tdp-evidence-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.tdp-evidence-card {
    background: var(--tdp-white);
    border-radius: var(--tdp-radius);
    padding: 32px;
    text-align: center;
    box-shadow: var(--tdp-shadow);
    border: 1px solid #e2e8f0;
}

.tdp-evidence-number {
    font-size: 48px;
    font-weight: 800;
    color: var(--tdp-blue);
    line-height: 1;
    margin-bottom: 8px;
}

.tdp-evidence-label {
    font-size: 15px;
    font-weight: 600;
    color: var(--tdp-dark);
    margin-bottom: 8px;
}

.tdp-evidence-source {
    font-size: 12px;
    color: var(--tdp-gray-light);
    line-height: 1.5;
}

/* ===== FAQ ===== */
.tdp-faq-list {
    max-width: 800px;
    margin: 0 auto;
}

.tdp-faq-item {
    border: 1px solid #e2e8f0;
    border-radius: var(--tdp-radius);
    margin-bottom: 12px;
    background: var(--tdp-white);
    overflow: hidden;
    transition: border-color 0.2s;
}

.tdp-faq-item:hover {
    border-color: var(--tdp-blue);
}

.tdp-faq-q {
    padding: 20px 24px;
    font-size: 16px;
    font-weight: 600;
    color: var(--tdp-dark);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    user-select: none;
}

.tdp-faq-q::after {
    content: '+';
    font-size: 22px;
    color: var(--tdp-blue);
    font-weight: 300;
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--tdp-blue-light);
    transition: transform 0.3s;
}

.tdp-faq-item.open .tdp-faq-q::after {
    content: '\2212';
    transform: rotate(180deg);
}

.tdp-faq-a {
    padding: 0 24px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease, padding 0.35s ease;
}

.tdp-faq-item.open .tdp-faq-a {
    padding: 0 24px 20px;
    max-height: 300px;
}

.tdp-faq-a p {
    font-size: 14px;
    color: var(--tdp-gray);
    line-height: 1.7;
    margin: 0;
}

/* ===== CTA BOTTOM ===== */
.tdp-cta-section {
    background: linear-gradient(135deg, #0a1628 0%, #1a2744 50%, #0d2137 100%);
    padding: 70px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.tdp-cta-section::before {
    content: '';
    position: absolute;
    top: -30%;
    right: 10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(0,164,228,0.12) 0%, transparent 70%);
    border-radius: 50%;
}

.tdp-cta-content {
    position: relative;
    z-index: 1;
}

.tdp-cta-title {
    font-size: 32px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 16px;
}

.tdp-cta-desc {
    font-size: 17px;
    color: #94a3b8;
    margin-bottom: 32px;
    max-width: 550px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.tdp-cta-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.tdp-phone-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #94a3b8;
    font-size: 15px;
    margin-top: 20px;
    transition: color 0.2s;
}

.tdp-phone-link:hover {
    color: #fff;
}

.tdp-phone-link svg {
    width: 18px;
    height: 18px;
}

/* ===== BREADCRUMBS ===== */
.tdp-vsuzi-breadcrumbs {
    padding: 16px 0;
    font-size: 13px;
    color: #94a3b8;
    background: var(--tdp-bg);
    border-bottom: 1px solid #e2e8f0;
}

.tdp-vsuzi-breadcrumbs a {
    color: var(--tdp-gray-light);
    transition: color 0.2s;
}

.tdp-vsuzi-breadcrumbs a:hover {
    color: var(--tdp-blue);
}

.tdp-vsuzi-breadcrumbs span.sep {
    margin: 0 8px;
    color: #cbd5e1;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .tdp-hero-h1 { font-size: 36px; }
    .tdp-benefits-grid { grid-template-columns: repeat(2, 1fr); }
    .tdp-evidence-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .tdp-vsuzi-hero { padding: 50px 0 60px; }
    .tdp-hero-h1 { font-size: 30px; }
    .tdp-hero-desc { font-size: 16px; }
    .tdp-hero-stats { flex-direction: column; gap: 20px; }
    .tdp-hero-stat-value { font-size: 26px; }
    .tdp-section { padding: 50px 0; }
    .tdp-section-title { font-size: 26px; }
    .tdp-benefits-grid { grid-template-columns: 1fr; }
    .tdp-products-grid { grid-template-columns: 1fr; }
    .tdp-how-grid { grid-template-columns: repeat(2, 1fr); }
    .tdp-evidence-grid { grid-template-columns: 1fr; }
    .tdp-cta-title { font-size: 26px; }
    .tdp-hero-actions { flex-direction: column; }
    .tdp-btn { justify-content: center; }
}

@media (max-width: 480px) {
    .tdp-how-grid { grid-template-columns: 1fr; }
    .tdp-hero-h1 { font-size: 26px; }
    .tdp-product-card { padding: 24px; }
}
</style>

<div class="tdp-vsuzi">

    <!-- Breadcrumbs -->
    <div class="tdp-vsuzi-breadcrumbs">
        <div class="tdp-container">
            <a href="/">Главная</a>
            <span class="sep">/</span>
            <a href="/product-category/interventsionnaya-rentgenologiya/">Интервенционная рентгенология</a>
            <span class="sep">/</span>
            ВСУЗИ оборудование
        </div>
    </div>

    <!-- HERO -->
    <section class="tdp-vsuzi-hero">
        <div class="tdp-container">
            <div class="tdp-hero-content">
                <div class="tdp-hero-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    Philips Volcano — официальный партнёр
                </div>

                <h1 class="tdp-hero-h1">
                    ВСУЗИ оборудование<br>
                    <span>Philips Volcano</span>
                </h1>

                <p class="tdp-hero-desc">
                    Внутрисосудистый ультразвук для интервенционной кардиологии. Катетеры Eagle&nbsp;Eye&nbsp;Platinum и платформы IntraSight — точная визуализация коронарных артерий для оптимизации стентирования и снижения осложнений.
                </p>

                <div class="tdp-hero-actions">
                    <a href="#b24-modal" class="tdp-btn tdp-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Получить коммерческое предложение
                    </a>
                    <a href="#catalog" class="tdp-btn tdp-btn-outline">
                        Смотреть каталог
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                </div>

                <div class="tdp-hero-stats">
                    <div>
                        <div class="tdp-hero-stat-value">-33<span>%</span></div>
                        <div class="tdp-hero-stat-label">снижение смертности<br>после ЧКВ с ВСУЗИ</div>
                    </div>
                    <div>
                        <div class="tdp-hero-stat-value">-34<span>%</span></div>
                        <div class="tdp-hero-stat-label">меньше осложнений<br>в первый год</div>
                    </div>
                    <div>
                        <div class="tdp-hero-stat-value">№1</div>
                        <div class="tdp-hero-stat-label">IVUS-катетер<br>в мире по выбору врачей</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY IVUS -->
    <section class="tdp-section tdp-section-alt">
        <div class="tdp-container">
            <div class="tdp-section-center">
                <h2 class="tdp-section-title">Почему ВСУЗИ меняет результаты ЧКВ</h2>
                <p class="tdp-section-subtitle">Внутрисосудистый ультразвук видит то, что скрыто от ангиографии — структуру стенки сосуда, состав бляшки и точный размер артерии</p>
            </div>

            <div class="tdp-benefits-grid">
                <div class="tdp-benefit-card">
                    <div class="tdp-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>
                    </div>
                    <div class="tdp-benefit-title">Точная визуализация</div>
                    <div class="tdp-benefit-text">
                        ВСУЗИ показывает поперечное сечение артерии в реальном времени — размер сосуда, состав атеросклеротической бляшки, степень стеноза. Ангиография даёт лишь силуэт.
                    </div>
                </div>

                <div class="tdp-benefit-card">
                    <div class="tdp-benefit-icon tdp-benefit-icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="tdp-benefit-title">Оптимальный подбор стента</div>
                    <div class="tdp-benefit-text">
                        Точное измерение диаметра и длины поражения позволяет выбрать стент идеального размера. Исключает недораскрытие и малапозицию.
                    </div>
                    <div class="tdp-benefit-stat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        -38% инфаркт миокарда
                    </div>
                </div>

                <div class="tdp-benefit-card">
                    <div class="tdp-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                    </div>
                    <div class="tdp-benefit-title">Доказанное снижение смертности</div>
                    <div class="tdp-benefit-text">
                        Мета-анализы показывают: применение ВСУЗИ при стентировании снижает сердечно-сосудистую смертность на 33% и осложнения на 34% в первый год.
                    </div>
                    <div class="tdp-benefit-stat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        -33% смертность
                    </div>
                </div>

                <div class="tdp-benefit-card">
                    <div class="tdp-benefit-icon tdp-benefit-icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="tdp-benefit-title">Без очистки от крови</div>
                    <div class="tdp-benefit-text">
                        В отличие от ОКТ, ВСУЗИ не требует введения контраста для вытеснения крови. Исследование проводится без прерывания кровотока.
                    </div>
                </div>

                <div class="tdp-benefit-card">
                    <div class="tdp-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </div>
                    <div class="tdp-benefit-title">Интеграция SyncVision</div>
                    <div class="tdp-benefit-text">
                        Корегистрация ВСУЗИ-изображений с ангиограммой в реальном времени. Точная навигация и принятие решений во время процедуры.
                    </div>
                </div>

                <div class="tdp-benefit-card">
                    <div class="tdp-benefit-icon tdp-benefit-icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div class="tdp-benefit-title">Глубокое проникновение</div>
                    <div class="tdp-benefit-text">
                        Ультразвук проникает на глубину до 10 мм, визуализируя все слои стенки сосуда — интиму, медию, адвентицию. ОКТ — только 1-2 мм.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCTS CATALOG -->
    <section class="tdp-section" id="catalog">
        <div class="tdp-container">
            <div class="tdp-section-center">
                <h2 class="tdp-section-title">Каталог ВСУЗИ оборудования</h2>
                <p class="tdp-section-subtitle">Полная линейка Philips Volcano для внутрисосудистой визуализации — катетеры и платформы</p>
            </div>

            <div class="tdp-products-grid">
                <?php foreach ($products as $p): ?>
                <div class="tdp-product-card">
                    <div class="tdp-product-header">
                        <div class="tdp-product-icon">
                            <?php echo $p['icon']; ?>
                        </div>
                        <div class="tdp-product-tag"><?php echo esc_html($p['tag']); ?></div>
                    </div>
                    <div class="tdp-product-name"><?php echo esc_html($p['title']); ?></div>
                    <div class="tdp-product-subtitle"><?php echo esc_html($p['subtitle']); ?></div>
                    <div class="tdp-product-desc"><?php echo esc_html($p['desc']); ?></div>
                    <div class="tdp-product-specs">
                        <?php foreach ($p['specs'] as $spec): ?>
                        <span class="tdp-product-spec"><?php echo esc_html($spec); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?php echo esc_url($p['url']); ?>" class="tdp-product-link">
                        Подробнее
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="tdp-section tdp-section-alt">
        <div class="tdp-container">
            <div class="tdp-section-center">
                <h2 class="tdp-section-title">Как работает ВСУЗИ</h2>
                <p class="tdp-section-subtitle">Внутрисосудистый ультразвук — 4 шага к точной диагностике</p>
            </div>

            <div class="tdp-how-grid">
                <div class="tdp-how-step">
                    <div class="tdp-how-title">Доступ</div>
                    <div class="tdp-how-text">Катетер вводится через направляющий катетер в коронарную артерию по стандартному проводнику 0.014"</div>
                </div>
                <div class="tdp-how-step">
                    <div class="tdp-how-title">Визуализация</div>
                    <div class="tdp-how-text">Миниатюрный датчик на кончике катетера создаёт 360° ультразвуковое изображение стенки сосуда изнутри</div>
                </div>
                <div class="tdp-how-step">
                    <div class="tdp-how-title">Анализ</div>
                    <div class="tdp-how-text">Автоматическое измерение диаметра, площади просвета, объёма бляшки. Корегистрация с ангиограммой (SyncVision)</div>
                </div>
                <div class="tdp-how-step">
                    <div class="tdp-how-title">Оптимизация</div>
                    <div class="tdp-how-text">Точный подбор размера стента, контроль раскрытия и прилегания. Оценка результата вмешательства в реальном времени</div>
                </div>
            </div>
        </div>
    </section>

    <!-- IVUS vs OCT COMPARISON -->
    <section class="tdp-section">
        <div class="tdp-container">
            <div class="tdp-section-center">
                <h2 class="tdp-section-title">ВСУЗИ vs ОКТ: сравнение технологий</h2>
                <p class="tdp-section-subtitle">Оба метода дополняют друг друга, но ВСУЗИ имеет наибольшую доказательную базу для оптимизации стентирования</p>
            </div>

            <div class="tdp-compare-wrap">
                <table class="tdp-compare-table">
                    <thead>
                        <tr>
                            <th>Параметр</th>
                            <th class="tdp-highlight">ВСУЗИ</th>
                            <th>ОКТ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Глубина проникновения</td>
                            <td class="tdp-highlight"><strong>до 10 мм</strong></td>
                            <td>1-2 мм</td>
                        </tr>
                        <tr>
                            <td>Разрешение</td>
                            <td class="tdp-highlight">100-150 мкм</td>
                            <td><strong>10-20 мкм</strong></td>
                        </tr>
                        <tr>
                            <td>Очистка от крови</td>
                            <td class="tdp-highlight"><span class="tdp-compare-check">Не требуется</span></td>
                            <td><span class="tdp-compare-cross">Требуется (контраст)</span></td>
                        </tr>
                        <tr>
                            <td>Визуализация всех слоёв стенки</td>
                            <td class="tdp-highlight"><span class="tdp-compare-check">Да</span></td>
                            <td><span class="tdp-compare-cross">Частично</span></td>
                        </tr>
                        <tr>
                            <td>Оценка размера сосуда</td>
                            <td class="tdp-highlight"><span class="tdp-compare-check">Точная (EEM)</span></td>
                            <td>Ограничена проникновением</td>
                        </tr>
                        <tr>
                            <td>Детализация поверхности стента</td>
                            <td class="tdp-highlight">Базовая</td>
                            <td><span class="tdp-compare-check"><strong>Высокая</strong></span></td>
                        </tr>
                        <tr>
                            <td>Снижение смертности (мета-анализ)</td>
                            <td class="tdp-highlight"><span class="tdp-compare-check"><strong>-33%</strong></span></td>
                            <td>Данные накапливаются</td>
                        </tr>
                        <tr>
                            <td>Класс рекомендаций ESC</td>
                            <td class="tdp-highlight"><strong>IIa</strong></td>
                            <td>IIa</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- CLINICAL EVIDENCE -->
    <section class="tdp-section tdp-section-alt">
        <div class="tdp-container">
            <div class="tdp-section-center">
                <h2 class="tdp-section-title">Клинические доказательства</h2>
                <p class="tdp-section-subtitle">ВСУЗИ — одна из наиболее изученных технологий в интервенционной кардиологии</p>
            </div>

            <div class="tdp-evidence-grid">
                <div class="tdp-evidence-card">
                    <div class="tdp-evidence-number">-33%</div>
                    <div class="tdp-evidence-label">Сердечно-сосудистая смертность</div>
                    <div class="tdp-evidence-source">Мета-анализ рандомизированных исследований, 2023. ВСУЗИ-контроль vs ангиографический контроль ЧКВ.</div>
                </div>
                <div class="tdp-evidence-card">
                    <div class="tdp-evidence-number">-38%</div>
                    <div class="tdp-evidence-label">Инфаркт миокарда</div>
                    <div class="tdp-evidence-source">Снижение частоты ИМ при стентировании под контролем ВСУЗИ в сравнении с ангиографическим контролем.</div>
                </div>
                <div class="tdp-evidence-card">
                    <div class="tdp-evidence-number">-34%</div>
                    <div class="tdp-evidence-label">Повторная реваскуляризация</div>
                    <div class="tdp-evidence-source">Снижение необходимости в повторных вмешательствах на целевом сосуде (TLR) в первый год после ЧКВ.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="tdp-section">
        <div class="tdp-container">
            <div class="tdp-section-center">
                <h2 class="tdp-section-title">Частые вопросы</h2>
                <p class="tdp-section-subtitle">Ответы на основные вопросы о ВСУЗИ оборудовании</p>
            </div>

            <div class="tdp-faq-list">
                <?php foreach ($faqs as $faq): ?>
                <div class="tdp-faq-item">
                    <div class="tdp-faq-q"><?php echo esc_html($faq['q']); ?></div>
                    <div class="tdp-faq-a"><p><?php echo esc_html($faq['a']); ?></p></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA BOTTOM -->
    <section class="tdp-cta-section">
        <div class="tdp-container">
            <div class="tdp-cta-content">
                <h2 class="tdp-cta-title">Готовы внедрить ВСУЗИ в вашей клинике?</h2>
                <p class="tdp-cta-desc">
                    Запросите индивидуальное коммерческое предложение — подберём оптимальную конфигурацию оборудования для вашей рентген-операционной.
                </p>
                <div class="tdp-cta-actions">
                    <a href="#b24-modal" class="tdp-btn tdp-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Получить коммерческое предложение
                    </a>
                    <a href="tel:+78633100807" class="tdp-btn tdp-btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                        Позвонить нам
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

<script>
// FAQ accordion
document.querySelectorAll('.tdp-faq-q').forEach(function(q) {
    q.addEventListener('click', function() {
        var item = this.parentElement;
        var wasOpen = item.classList.contains('open');
        // Close all
        document.querySelectorAll('.tdp-faq-item').forEach(function(i) {
            i.classList.remove('open');
        });
        // Toggle current
        if (!wasOpen) {
            item.classList.add('open');
        }
    });
});

// Smooth scroll for #catalog
document.querySelectorAll('a[href="#catalog"]').forEach(function(a) {
    a.addEventListener('click', function(e) {
        e.preventDefault();
        var target = document.getElementById('catalog');
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

<?php
// Schema.org structured data
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'ВСУЗИ оборудование Philips Volcano — катетеры и платформы для внутрисосудистого ультразвука',
    'description' => 'Каталог ВСУЗИ оборудования Philips Volcano: катетеры Eagle Eye Platinum, платформы IntraSight. Внутрисосудистый ультразвук для интервенционной кардиологии.',
    'url' => home_url('/vsuzi/'),
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'ТД «Пульс»',
        'url' => home_url('/'),
    ],
    'mainEntity' => [
        '@type' => 'ItemList',
        'numberOfItems' => count($products),
        'itemListElement' => array_map(function($p, $i) {
            return [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $p['title'],
                'url' => home_url($p['url']),
            ];
        }, $products, array_keys($products)),
    ],
];

// FAQ Schema
$faq_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function($faq) {
        return [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['a'],
            ],
        ];
    }, $faqs),
];
?>
<script type="application/ld+json"><?php echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo json_encode($faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

<?php get_footer(); ?>
