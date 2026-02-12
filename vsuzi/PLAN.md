# Контент-стратегия ВСУЗИ — tdpuls.com

## Цель
Стать главным русскоязычным ресурсом по ВСУЗИ оборудованию. Трафик + экспертный авторитет + лиды (заявки на КП).

## Продукция
- **Катетеры ВСУЗИ** — Eagle Eye Platinum (20 МГц, цифровой), Eagle Eye Platinum ST (Short Tip 2.5 мм), Refinity (45 МГц, ротационный), Reconnaissance PV .018 OTW (периферический)
- **Проводники давления** — OmniWire (FFR/iFR, 0.014", 185 см)
- **Платформы** — IntraSight (IVUS + FFR/iFR + тройная ко-регистрация), Core Mobile
- **Программное обеспечение** — SyncVision (ко-регистрация, Angio+, iFR Scout)
- Целевая аудитория: кардиологи-интервенционисты, заведующие отделениями, закупщики клиник

## Поисковый спрос (Яндекс.Вордстат, февраль 2026)

| Запрос | Показов/мес |
|--------|-------------|
| всузи | 777 |
| всузи коронарных артерий | 124 |
| внутрисосудистое узи | 90 |
| eagle eye platinum | 65 |
| eagle eye platinum st | 41 |
| аппарат всузи | 35 |
| датчик всузи | 29 |
| всузи филипс | 27 |
| volcano philips | 19 |
| philips intrasight | 15 |
| **Смежные (трафик-драйверы):** | |
| коронарография | 47 434 |
| стентирование коронарных артерий | 2 870 |
| оптическая когерентная томография | 3 742 |
| интервенционная кардиология | 626 |

## Контент-пиллары

```
СТОЛП 1: ВСУЗИ технология (Hub)
├── Что такое ВСУЗИ
├── ВСУЗИ vs ОКТ: сравнение
├── Показания к ВСУЗИ
├── ВСУЗИ при стентировании
└── Как ВСУЗИ снижает смертность

СТОЛП 2: Оборудование Philips Volcano
├── Eagle Eye Platinum: обзор
├── Eagle Eye Platinum ST: обзор
├── IntraSight: платформа
├── Revolution 45 vs Eagle Eye
└── Как выбрать систему ВСУЗИ

СТОЛП 3: Практика для клиник
├── Как внедрить ВСУЗИ в клинике
├── Окупаемость ВСУЗИ оборудования
├── Обучение персонала ВСУЗИ
└── Кейсы российских клиник
```

## Приоритетный контент-план (10 публикаций)

### Приоритет 1 — Hub-лендинг
- [x] **Hub: ВСУЗИ оборудование Philips Volcano** — `/vsuzi/`
  - Файл: `page-vsuzi-hub.php`
  - Статус: ОБНОВЛЁН — 8 продуктов (4 катетера + OmniWire + 2 платформы + SyncVision), 9 FAQ, поддержка изображений, обогащённые описания

### Приоритет 2 — Информационные статьи
- [ ] **«Что такое ВСУЗИ: полное руководство»** — `/blog/chto-takoe-vsuzi/`
  - Запросы: всузи (777), внутрисосудистый ультразвук (34), внутрисосудистое узи (90)
- [ ] **«ВСУЗИ vs ОКТ: что выбрать»** — `/blog/vsuzi-vs-okt/`
  - Запросы: всузи, окт коронарных артерий (12)
- [ ] **«ВСУЗИ при стентировании коронарных артерий»** — `/blog/vsuzi-pri-stentirovanii/`
  - Подключение к запросу стентирование коронарных артерий (2 870)

### Приоритет 3 — Продуктовые обзоры
- [ ] **«Eagle Eye Platinum: обзор катетера №1»** — `/blog/eagle-eye-platinum-obzor/`
  - Запросы: eagle eye platinum (65), катетер eagle eye platinum (30)
- [ ] **«Philips IntraSight: обзор платформы»** — `/blog/philips-intrasight-obzor/`
  - Запросы: philips intrasight (15), аппарат всузи (35)
- [ ] **«Сравнение катетеров ВСУЗИ: Eagle Eye vs Revolution vs Refinity»** — `/blog/sravnenie-kateterov-vsuzi/`

### Приоритет 4 — Практика и кейсы
- [ ] **«Как внедрить ВСУЗИ в клинике»** — `/blog/vnedrenie-vsuzi-v-klinike/`
- [ ] **«ROI оборудования ВСУЗИ: окупаемость»** — `/blog/okupaemost-vsuzi/`
- [ ] **«ВСУЗИ снижает смертность на 33%: обзор исследований»** — `/blog/vsuzi-snizhenie-smertnosti/`

## Конкуренты
- **НДА (nda.ru)** — официальный партнёр Philips, каталог Volcano
- **ПМК (permedcom.ru)** — Volcano Core от 1.9 млн руб.
- **Tierbach.ru** — дистрибьютор Philips
- **medeq.ru, ultramedpro.ru** — ВСУЗИ системы
- **TheExp.ru** — каталог ВСУЗИ

## Технические детали

### Файлы
| Файл | Описание |
|------|----------|
| `page-vsuzi-hub.php` | Hub-лендинг, шаблон WP-страницы |
| `functions-new.php` | Хук template_include + SEO мета |

### Деплой
1. Загрузить `page-vsuzi-hub.php` на сервер
2. Загрузить обновлённый `functions-new.php`
3. Создать WP-страницу со slug `vsuzi` через REST API
4. Проверить https://tdpuls.com/vsuzi/

## Ссылки
- [Philips IVUS (rus)](https://www.philips.ru/healthcare/education-resources/technologies/igt/intravascular-ultrasound-ivus)
- [Eagle Eye Platinum](https://www.usa.philips.com/healthcare/product/HC85900P/eagle-eye-platinum-digital-ivus-catheter)
- [Cardioweb — ВСУЗИ снижает смертность](https://cardioweb.ru/news/item/1199)
- [CyberLeninka — ВСУЗИ при ЧКВ](https://cyberleninka.ru/article/n/vnutrisosudistoe-ultrazvukovoe-skanirovanie-pri-interventsionnyh-vmeshatelstvah-na-koronarnyh-arteriyah-optimalnoe-primenenie-i)
