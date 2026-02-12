<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php wp_head();?>

	<?php if (is_front_page()): ?>
		<script type="application/ld+json">
		{
			"@context": "https://schema.org",
			"@type": "Organization",
			"name": "Группа компаний «Пульс»",
			"alternateName": ["ООО «Торговый дом Пульс»", "НПФ «Пульс»", "ТД «Пульс»"],
			"url": "https://tdpuls.com",
			"logo": "https://tdpuls.com/wp-content/uploads/2025/07/logo.png",
			"description": "Комплексные поставки медицинского оборудования от ведущих мировых производителей с 1989 года",
			"foundingDate": "1989",
			"address": {
				"@type": "PostalAddress",
				"streetAddress": "ул. Максима Горького, д. 245/26",
				"addressLocality": "Ростов-на-Дону",
				"postalCode": "344022",
				"addressCountry": "RU"
			},
			"contactPoint": {
				"@type": "ContactPoint",
				"telephone": "+7-863-310-08-07",
				"contactType": "sales",
				"areaServed": "RU",
				"availableLanguage": ["Russian", "English"]
			}
		}
		</script>
	<?php
	// Страница контакты
	elseif (get_the_ID() == 34):
		?>

		<script type='application/ld+json'>
			{
				"@context": "http://www.schema.org",
				"@type": "LocalBusiness",
				"name": "ООО «ТД «Пульс»",
				"url": "https://tdpuls.com/kontakty/",
				"logo": "https://tdpuls.com/wp-content/uploads/2025/07/logo.png",
				"image": "https://tdpuls.com/wp-content/uploads/2025/07/logo.png",
				"description": "Медицинское оборудование, медтехника ООО «ТД «Пульс»",
				"address": {
				"@type": "PostalAddress",
				"streetAddress": "улица Максима Горького",
				"addressLocality": "Ростов-на-Дону",
				"postalCode": "344022",
				"addressCountry": "Россия"
				},
				"geo": {
				"@type": "GeoCoordinates",
				"longitude": "47.230652",
				"latitude": "39.731720"
				},
				"hasMap": "https://yandex.ru/maps/-/CLQ3rI3e",
				"openingHours": "Mo 09:00-18:00 Tu 09:00-18:00 We 09:00-18:00 Th 09:00-18:00 Fr 09:00-18:00",
				"contactPoint": {
				"@type": "PostalAddress",
				"telephone": "+7 (863) 250-66-80"
				}
			}
		</script>

	<?php elseif(get_post_type() == 'post'):?>

		<script type="application/ld+json">
			{
				"@context": "http://schema.org/",
				"@type": "NewsArticle",
				"headline": "<?php echo get_the_title();?>",
				"datePublished": "<?php echo date('Y-m-d', strtotime(get_the_date()));?>",
				"description": "<?php echo get_the_excerpt();?>",
				"image": {
					"@type": "ImageObject",
					"height": "772",
					"width": "505",
					"url": "<?php echo get_the_post_thumbnail_url();?>"
				},
				"author": "ООО «ТД «Пульс»",
				"publisher": {
					"@type": "Organization",
					"logo": {
						"@type": "ImageObject",
						"url": "https://tdpuls.com/wp-content/uploads/2025/07/logo.png"
					},
					"name": "ООО «ТД «Пульс»"
				},
				"articleBody": ""
			}
		</script>

	<?php endif;
	?>

</head>
<body <?php body_class(); ?>>

<?php do_action( 'flatsome_after_body_open' ); ?>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'flatsome' ); ?></a>

<div id="wrapper">

	<?php do_action( 'flatsome_before_header' ); ?>

	<header id="header" class="header <?php flatsome_header_classes(); ?>">
		<div class="header-wrapper">
			<?php get_template_part( 'template-parts/header/header', 'wrapper' ); ?>
		</div>
	</header>

	<?php do_action( 'flatsome_after_header' ); ?>

	<main id="main" class="<?php flatsome_main_classes(); ?>">
