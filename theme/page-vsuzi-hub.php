<?php
/**
 * Hub-лендинг: ВСУЗИ оборудование Philips Volcano
 * URL: /vsuzi/
 *
 * @package flatsome
 */

if (!defined('ABSPATH')) exit;

get_header();

// Продукты ВСУЗИ — полная линейка Philips Volcano
$products = [
    // === КАТЕТЕРЫ КОРОНАРНЫЕ ===
    [
        'title' => 'Eagle Eye Platinum',
        'cat' => 'Катетеры ВСУЗИ',
        'desc' => 'Цифровой IVUS-катетер №1 в мире. Plug-and-play подключение без калибровки, гидрофильное покрытие GlyDx, три рентгеноконтрастных маркера (шаг 10 мм). Функция ChromaFlo выделяет кровоток красным для оценки прилегания стента.',
        'specs' => ['20 МГц', '3.5F', 'ChromaFlo', 'SyncVision'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/eagle-eye-platinum-tsifrovoj-kateter-dlya-vsuzi/',
        'tag' => 'Бестселлер',
        'img' => '/wp-content/uploads/vsuzi/eagle-eye-platinum.webp',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
    ],
    [
        'title' => 'Eagle Eye Platinum ST',
        'cat' => 'Катетеры ВСУЗИ',
        'desc' => 'Версия с коротким кончиком 2,5 мм от датчика до кончика — для дистальных поражений и извитых сосудов. GlyDx-покрытие, три рентгеноконтрастных маркера. Совместим с направляющими катетерами от 5F.',
        'specs' => ['20 МГц', 'Short Tip 2.5 мм', '3.5F', 'ChromaFlo'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/eagle-eye-platinum-st-tsifrovoj-kateter-dlya-vsuzi-s-korotkim-konchikom/',
        'tag' => 'Short Tip',
        'img' => '/wp-content/uploads/vsuzi/eagle-eye-platinum-st.webp',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>',
    ],
    [
        'title' => 'Refinity',
        'cat' => 'Катетеры ВСУЗИ',
        'desc' => 'Ротационный IVUS-катетер нового поколения. Частота 45 МГц для максимально чёткого изображения. GlyDx-покрытие, мягкий гибкий кончик. Совместим с радиальным доступом и направляющими от 5F.',
        'specs' => ['45 МГц', 'Ротационный', '3.0F', 'Радиальный'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/refinity-rotatsionnyj-kateter-dlya-vsuzi/',
        'tag' => 'Новинка',
        'img' => '/wp-content/uploads/vsuzi/refinity.webp',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 019 9"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="1"/></svg>',
    ],
    [
        'title' => 'Reconnaissance PV .018 OTW',
        'cat' => 'Катетеры ВСУЗИ',
        'desc' => 'Периферический цифровой IVUS-катетер с доставкой по проводнику 0.018" (OTW). Конический кончик, твёрдый сердечник, гидрофильное покрытие. Функция ChromaFlo для оценки периферических сосудов.',
        'specs' => ['20 МГц', 'OTW 0.018"', '150 см', 'Периферия'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/reconnaissance-pv-018-otw-tsifrovoj-kateter-dlya-vsuzi/',
        'tag' => 'Периферия',
        'img' => '/wp-content/uploads/vsuzi/reconnaissance-pv.webp',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 12h4l2-6 4 12 4-12 2 6h4"/><circle cx="20" cy="12" r="2"/></svg>',
    ],
    // === ПРОВОДНИКИ ДАВЛЕНИЯ ===
    [
        'title' => 'OmniWire',
        'cat' => 'Проводники давления',
        'desc' => 'Проводник с датчиком давления для измерения FFR и iFR. Жёсткий сердечник проксимальной части, нитиноловый дистальный сегмент для восстановления формы. Ко-регистрация iFR непосредственно на ангиограмме.',
        'specs' => ['FFR + iFR', '0.014"', '185 см', 'Ко-регистрация'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/omniwire-provodnik-s-datchikom-davleniya/',
        'tag' => 'Физиология',
        'img' => '/wp-content/uploads/vsuzi/omniwire.webp',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 12c2-3 4-3 6 0s4 3 6 0 4-3 6 0"/><circle cx="21" cy="12" r="1.5" fill="currentColor"/><path d="M8 6v2M12 4v4M16 6v2"/></svg>',
    ],
    // === ПЛАТФОРМЫ ===
    [
        'title' => 'Philips IntraSight',
        'cat' => 'Платформы',
        'desc' => 'Мобильная платформа для ВСУЗИ, FFR и iFR на единой масштабируемой архитектуре. Тройная ко-регистрация iFR + IVUS + ангиография. Сенсорный модуль для стерильной зоны. Демо-режим для обучения.',
        'specs' => ['IVUS + FFR/iFR', 'Тройная ко-рег.', 'Touchscreen', 'Мобильная'],
        'url' => '/shop/informatsionnye-sistemy/philips-intrasight-mobile-mobilnaya-platforma-dlya-intervenionnyh-vmeshatelstv/',
        'tag' => 'Платформа',
        'img' => '/wp-content/uploads/vsuzi/intrasight.webp',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>',
    ],
    [
        'title' => 'Volcano Core Mobile',
        'cat' => 'Платформы',
        'desc' => 'Система высокоточной визуализации и анализа физиологических параметров для ЧКВ. Интеграция IVUS-изображений с данными FFR/iFR. Компактная мобильная платформа для рентген-операционной.',
        'specs' => ['IVUS + Физ.', 'Мобильная', 'Мультимод.'],
        'url' => '/shop/interventsionnaya-rentgenologiya/katetery/core-mobile-sistema-dlya-vypolneniya-vysokotochnoj-terapii/',
        'tag' => 'Система',
        'img' => '/wp-content/uploads/vsuzi/core-mobile.webp',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
    ],
    // === ПРОГРАММНОЕ ОБЕСПЕЧЕНИЕ ===
    [
        'title' => 'SyncVision',
        'cat' => 'Программное обеспечение',
        'desc' => 'ПО для ко-регистрации IVUS и iFR с ангиограммой в реальном времени. Функция Angio+ улучшает визуализацию извитых сосудов. Автоматический расчёт размеров просвета и степени стеноза. iFR Scout для диффузных поражений.',
        'specs' => ['Ко-регистрация', 'Angio+', 'iFR Scout', 'Realtime'],
        'url' => '/shop/informatsionnye-sistemy/syncvision-sistema-tochnoj-navigatsii/',
        'tag' => 'ПО',
        'img' => '/wp-content/uploads/vsuzi/syncvision.webp',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 10l3 3 5-5"/><path d="M8 21h8M12 17v4"/></svg>',
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
        'q' => 'Какое ВСУЗИ оборудование Philips Volcano вы предлагаете?',
        'a' => 'Мы поставляем полную линейку Philips Volcano: коронарные цифровые катетеры Eagle Eye Platinum и Eagle Eye Platinum ST (20 МГц), ротационный катетер Refinity (45 МГц), периферический катетер Reconnaissance PV .018 OTW, проводник давления OmniWire для FFR/iFR, платформы IntraSight и Core Mobile, а также ПО SyncVision для ко-регистрации.',
    ],
    [
        'q' => 'Сколько стоит оборудование ВСУЗИ?',
        'a' => 'Стоимость зависит от конфигурации: катетеры (Eagle Eye, Refinity, Reconnaissance) и проводники (OmniWire) — расходный материал для каждой процедуры; платформы (IntraSight, Core Mobile) — стационарное оборудование; SyncVision — программное обеспечение. Запросите индивидуальное коммерческое предложение — мы подберём оптимальную конфигурацию для вашей клиники.',
    ],
    [
        'q' => 'ВСУЗИ или ОКТ — что лучше?',
        'a' => 'Оба метода дополняют друг друга. ВСУЗИ обеспечивает глубокое проникновение ультразвука (до 10 мм) и не требует очистки сосуда от крови. ОКТ даёт более высокое разрешение (10 мкм vs 100 мкм). Для оценки размера сосуда и оптимизации стентирования ВСУЗИ имеет наибольшую доказательную базу.',
    ],
    [
        'q' => 'Чем отличаются катетеры Eagle Eye Platinum и Refinity?',
        'a' => 'Eagle Eye Platinum — цифровой катетер с частотой 20 МГц, plug-and-play подключение, идеален для рутинных коронарных процедур. Refinity — ротационный катетер с частотой 45 МГц для максимально чёткого изображения, особенно эффективен при радиальном доступе и сложных анатомиях. Оба совместимы с направляющими катетерами от 5F.',
    ],
    [
        'q' => 'Для чего нужен проводник OmniWire?',
        'a' => 'OmniWire — это проводник с интегрированным датчиком давления для измерения FFR (фракционного резерва кровотока) и iFR (мгновенного соотношения давлений). Позволяет оценить гемодинамическую значимость стеноза без дополнительных устройств. Поддерживает ко-регистрацию данных iFR непосредственно на ангиограмме.',
    ],
    [
        'q' => 'Что такое SyncVision и зачем он нужен?',
        'a' => 'SyncVision — программное обеспечение для ко-регистрации данных IVUS и iFR с ангиограммой в реальном времени. Функция Angio+ улучшает визуализацию извитых и перекрывающихся сосудов. iFR Scout выявляет значимые градиенты при диффузных поражениях. Помогает точнее планировать и контролировать вмешательство.',
    ],
    [
        'q' => 'Как организовать поставку ВСУЗИ оборудования?',
        'a' => 'Оставьте заявку на сайте или свяжитесь с нами по телефону. Мы подготовим коммерческое предложение, организуем демонстрацию оборудования и обеспечим техническую поддержку и обучение персонала.',
    ],
];

?>


<div class="vsuzi-hub">

    <!-- Breadcrumbs -->
    <div class="vsuzi-breadcrumbs">
        <div class="vsuzi-container">
            <a href="/">Главная</a>
            <span class="sep">/</span>
            <a href="/katalog/">Каталог</a>
            <span class="sep">/</span>
            <a href="/product-category/interventsionnaya-rentgenologiya/">Интервенционная рентгенология</a>
            <span class="sep">/</span>
            ВСУЗИ оборудование
        </div>
    </div>

    <!-- HERO -->
    <section class="vsuzi-hero">
        <div class="vsuzi-container">
            <div class="vsuzi-hero-inner">
                <div class="vsuzi-hero-text">
                    <div class="vsuzi-partner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Официальный партнёр Philips
                    </div>

                    <h1>ВСУЗИ оборудование<br><span>Philips Volcano</span></h1>

                    <p class="vsuzi-hero-desc">
                        Полная линейка для внутрисосудистой визуализации и физиологии: катетеры Eagle&nbsp;Eye&nbsp;Platinum, ротационный Refinity 45&nbsp;МГц, проводник давления OmniWire, платформа IntraSight и&nbsp;ПО&nbsp;SyncVision.
                    </p>

                    <div class="vsuzi-hero-actions">
                        <a href="#b24-modal" class="vsuzi-btn vsuzi-btn--primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            Получить КП
                        </a>
                        <a href="#catalog" class="vsuzi-btn vsuzi-btn--outline">
                            Смотреть каталог
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </a>
                    </div>

                    <div class="vsuzi-hero-stats">
                        <div>
                            <div class="vsuzi-stat-value">-33<span>%</span></div>
                            <div class="vsuzi-stat-label">снижение смертности<br>после ЧКВ с ВСУЗИ</div>
                        </div>
                        <div>
                            <div class="vsuzi-stat-value">-34<span>%</span></div>
                            <div class="vsuzi-stat-label">меньше осложнений<br>в первый год</div>
                        </div>
                        <div>
                            <div class="vsuzi-stat-value">&#8470;1</div>
                            <div class="vsuzi-stat-label">IVUS-катетер в мире<br>по выбору врачей</div>
                        </div>
                    </div>
                </div>

                <div class="vsuzi-hero-visual">
                    <div class="vsuzi-ivus-visual">
                        <div class="vsuzi-ivus-ring"></div>
                        <div class="vsuzi-ivus-ring"></div>
                        <div class="vsuzi-ivus-ring"></div>
                        <div class="vsuzi-ivus-ring"></div>
                        <div class="vsuzi-ivus-center">IVUS<br>360°</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY IVUS -->
    <section class="vsuzi-section vsuzi-section--gray">
        <div class="vsuzi-container">
            <div class="vsuzi-center">
                <h2 class="vsuzi-section-title">Почему ВСУЗИ меняет результаты ЧКВ</h2>
                <p class="vsuzi-section-sub">Внутрисосудистый ультразвук видит то, что скрыто от ангиографии — структуру стенки сосуда, состав бляшки и точный размер артерии</p>
            </div>

            <div class="vsuzi-benefits">
                <div class="vsuzi-benefit">
                    <div class="vsuzi-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>
                    </div>
                    <h3>Точная визуализация</h3>
                    <p>ВСУЗИ показывает поперечное сечение артерии в реальном времени — размер сосуда, состав бляшки, степень стеноза. Ангиография даёт лишь силуэт.</p>
                </div>

                <div class="vsuzi-benefit">
                    <div class="vsuzi-benefit-icon vsuzi-benefit-icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <h3>Оптимальный подбор стента</h3>
                    <p>Точное измерение диаметра и длины поражения позволяет выбрать стент идеального размера. Исключает недораскрытие и малапозицию.</p>
                    <div class="vsuzi-benefit-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        -38% инфаркт миокарда
                    </div>
                </div>

                <div class="vsuzi-benefit">
                    <div class="vsuzi-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                    </div>
                    <h3>Снижение смертности на 33%</h3>
                    <p>Мета-анализы показывают: применение ВСУЗИ при стентировании снижает сердечно-сосудистую смертность и осложнения в первый год.</p>
                    <div class="vsuzi-benefit-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        -33% смертность
                    </div>
                </div>

                <div class="vsuzi-benefit">
                    <div class="vsuzi-benefit-icon vsuzi-benefit-icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <h3>Без очистки от крови</h3>
                    <p>В отличие от ОКТ, ВСУЗИ не требует введения контраста для вытеснения крови. Исследование проводится без прерывания кровотока.</p>
                </div>

                <div class="vsuzi-benefit">
                    <div class="vsuzi-benefit-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </div>
                    <h3>Интеграция SyncVision</h3>
                    <p>Корегистрация ВСУЗИ-изображений с ангиограммой в реальном времени. Точная навигация и принятие решений во время процедуры.</p>
                </div>

                <div class="vsuzi-benefit">
                    <div class="vsuzi-benefit-icon vsuzi-benefit-icon--green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3>Глубокое проникновение</h3>
                    <p>Ультразвук проникает на глубину до 10 мм, визуализируя все слои стенки сосуда — интиму, медию, адвентицию. ОКТ — только 1-2 мм.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCTS CATALOG -->
    <section class="vsuzi-section" id="catalog">
        <div class="vsuzi-container">
            <div class="vsuzi-center">
                <h2 class="vsuzi-section-title">Каталог ВСУЗИ оборудования</h2>
                <p class="vsuzi-section-sub">Катетеры ВСУЗИ, проводники давления, платформы и ПО — от визуализации до оценки физиологии</p>
            </div>

            <div class="vsuzi-products">
                <?php foreach ($products as $p): ?>
                <div class="vsuzi-product">
                    <div class="vsuzi-product-img">
                        <?php if (!empty($p['img'])): ?>
                            <img src="<?php echo esc_url($p['img']); ?>" alt="<?php echo esc_attr($p['title']); ?> — <?php echo esc_attr($p['cat']); ?>" loading="lazy" width="280" height="280" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                            <span style="display:none"><?php echo $p['icon']; ?></span>
                        <?php else: ?>
                            <?php echo $p['icon']; ?>
                        <?php endif; ?>
                        <div class="vsuzi-product-tag"><?php echo esc_html($p['tag']); ?></div>
                    </div>
                    <div class="vsuzi-product-body">
                        <div class="vsuzi-product-cat"><?php echo esc_html($p['cat']); ?></div>
                        <div class="vsuzi-product-name"><?php echo esc_html($p['title']); ?></div>
                        <div class="vsuzi-product-desc"><?php echo esc_html($p['desc']); ?></div>
                        <div class="vsuzi-product-specs">
                            <?php foreach ($p['specs'] as $spec): ?>
                            <span class="vsuzi-product-spec"><?php echo esc_html($spec); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?php echo esc_url($p['url']); ?>" class="vsuzi-product-btn">
                            <?php echo ($p['url'] === '#b24-modal') ? 'Запросить КП' : 'Подробнее'; ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="vsuzi-section vsuzi-section--gray">
        <div class="vsuzi-container">
            <div class="vsuzi-center">
                <h2 class="vsuzi-section-title">Как работает ВСУЗИ</h2>
                <p class="vsuzi-section-sub">Внутрисосудистый ультразвук — 4 шага к точной диагностике</p>
            </div>

            <div class="vsuzi-how">
                <div class="vsuzi-how-step">
                    <h4>Доступ</h4>
                    <p>Катетер вводится через направляющий катетер в коронарную артерию по стандартному проводнику 0.014"</p>
                </div>
                <div class="vsuzi-how-step">
                    <h4>Визуализация</h4>
                    <p>Миниатюрный датчик создаёт 360° ультразвуковое изображение стенки сосуда изнутри</p>
                </div>
                <div class="vsuzi-how-step">
                    <h4>Анализ</h4>
                    <p>Автоматическое измерение диаметра, площади просвета, объёма бляшки. Корегистрация с ангиограммой</p>
                </div>
                <div class="vsuzi-how-step">
                    <h4>Оптимизация</h4>
                    <p>Точный подбор размера стента, контроль раскрытия и прилегания. Оценка результата в реальном времени</p>
                </div>
            </div>
        </div>
    </section>

    <!-- COMPARISON TABLE -->
    <section class="vsuzi-section">
        <div class="vsuzi-container">
            <div class="vsuzi-center">
                <h2 class="vsuzi-section-title">ВСУЗИ vs ОКТ: сравнение технологий</h2>
                <p class="vsuzi-section-sub">Оба метода дополняют друг друга, но ВСУЗИ имеет наибольшую доказательную базу для оптимизации стентирования</p>
            </div>

            <div class="vsuzi-compare-wrap">
                <table class="vsuzi-compare">
                    <thead>
                        <tr>
                            <th>Параметр</th>
                            <th class="vsuzi-hl">ВСУЗИ</th>
                            <th>ОКТ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Глубина проникновения</td>
                            <td class="vsuzi-hl"><strong>до 10 мм</strong></td>
                            <td>1-2 мм</td>
                        </tr>
                        <tr>
                            <td>Разрешение</td>
                            <td class="vsuzi-hl">100-150 мкм</td>
                            <td><strong>10-20 мкм</strong></td>
                        </tr>
                        <tr>
                            <td>Очистка от крови</td>
                            <td class="vsuzi-hl"><span class="vsuzi-check">Не требуется</span></td>
                            <td><span class="vsuzi-cross">Требуется (контраст)</span></td>
                        </tr>
                        <tr>
                            <td>Визуализация слоёв стенки</td>
                            <td class="vsuzi-hl"><span class="vsuzi-check">Все слои</span></td>
                            <td><span class="vsuzi-cross">Частично</span></td>
                        </tr>
                        <tr>
                            <td>Оценка размера сосуда</td>
                            <td class="vsuzi-hl"><span class="vsuzi-check">Точная (EEM)</span></td>
                            <td>Ограничена</td>
                        </tr>
                        <tr>
                            <td>Детализация поверхности стента</td>
                            <td class="vsuzi-hl">Базовая</td>
                            <td><span class="vsuzi-check"><strong>Высокая</strong></span></td>
                        </tr>
                        <tr>
                            <td>Снижение смертности</td>
                            <td class="vsuzi-hl"><span class="vsuzi-check"><strong>-33%</strong></span></td>
                            <td>Данные накапливаются</td>
                        </tr>
                        <tr>
                            <td>Рекомендации ESC</td>
                            <td class="vsuzi-hl"><strong>IIa</strong></td>
                            <td>IIa</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- CLINICAL EVIDENCE -->
    <section class="vsuzi-section vsuzi-section--gray">
        <div class="vsuzi-container">
            <div class="vsuzi-center">
                <h2 class="vsuzi-section-title">Клинические доказательства</h2>
                <p class="vsuzi-section-sub">ВСУЗИ — одна из наиболее изученных технологий в интервенционной кардиологии</p>
            </div>

            <div class="vsuzi-evidence">
                <div class="vsuzi-evidence-card">
                    <div class="vsuzi-evidence-num">-33%</div>
                    <div class="vsuzi-evidence-label">Сердечно-сосудистая смертность</div>
                    <div class="vsuzi-evidence-src">Мета-анализ рандомизированных исследований, 2023. ВСУЗИ-контроль vs ангиографический контроль ЧКВ.</div>
                </div>
                <div class="vsuzi-evidence-card">
                    <div class="vsuzi-evidence-num">-38%</div>
                    <div class="vsuzi-evidence-label">Инфаркт миокарда</div>
                    <div class="vsuzi-evidence-src">Снижение частоты ИМ при стентировании под контролем ВСУЗИ в сравнении с ангиографическим контролем.</div>
                </div>
                <div class="vsuzi-evidence-card">
                    <div class="vsuzi-evidence-num">-34%</div>
                    <div class="vsuzi-evidence-label">Повторная реваскуляризация</div>
                    <div class="vsuzi-evidence-src">Снижение повторных вмешательств на целевом сосуде (TLR) в первый год после ЧКВ.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="vsuzi-section">
        <div class="vsuzi-container">
            <div class="vsuzi-center">
                <h2 class="vsuzi-section-title">Частые вопросы</h2>
                <p class="vsuzi-section-sub">Ответы на основные вопросы о ВСУЗИ оборудовании</p>
            </div>

            <div class="vsuzi-faq">
                <?php foreach ($faqs as $faq): ?>
                <div class="vsuzi-faq-item">
                    <div class="vsuzi-faq-q"><?php echo esc_html($faq['q']); ?></div>
                    <div class="vsuzi-faq-a"><p><?php echo esc_html($faq['a']); ?></p></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="vsuzi-cta">
        <div class="vsuzi-container">
            <h2>Готовы внедрить ВСУЗИ в вашей клинике?</h2>
            <p class="vsuzi-cta-desc">
                Запросите индивидуальное коммерческое предложение — подберём оптимальную конфигурацию оборудования для вашей рентген-операционной.
            </p>
            <div class="vsuzi-cta-actions">
                <a href="#b24-modal" class="vsuzi-btn vsuzi-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    Получить коммерческое предложение
                </a>
                <a href="tel:+78633100807" class="vsuzi-btn vsuzi-btn--dark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                    Позвонить нам
                </a>
            </div>
            <a href="tel:+78633100807" class="vsuzi-cta-phone">+7 (863) 310-08-07</a>
        </div>
    </section>

</div>

<script>
// FAQ accordion
document.querySelectorAll('.vsuzi-faq-q').forEach(function(q) {
    q.addEventListener('click', function() {
        var item = this.parentElement;
        var wasOpen = item.classList.contains('open');
        document.querySelectorAll('.vsuzi-faq-item').forEach(function(i) {
            i.classList.remove('open');
        });
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
    'name' => 'ВСУЗИ оборудование Philips Volcano — катетеры, проводники, платформы для внутрисосудистого ультразвука',
    'description' => 'Полная линейка ВСУЗИ оборудования Philips Volcano: катетеры Eagle Eye Platinum, Refinity, Reconnaissance PV, проводник OmniWire, платформы IntraSight и Core Mobile, ПО SyncVision.',
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
