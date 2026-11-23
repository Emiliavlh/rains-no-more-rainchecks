# RAINS – No More Rainchecks

Eksamensprojekt i **Creative Digital Design**, efterår 2026
Multimediedesigneruddannelsen, Erhvervsakademi Aarhus

**Live site:** [rains.emiliavigga.dk](https://rains.emiliavigga.dk/)

---

## Om projektet

*No More Rainchecks* er et kampagnekoncept for RAINS, der vender dårligt vejr
fra en undskyldning til en invitation. Konceptet er udfoldet som en eksklusiv
og legende oplevelse – både fysisk og digitalt – og dette repository indeholder
WordPress-temaerne bag kampagnesitet.

## Indhold

```
rains-no-more-rainchecks/
├── extendable/               Mother theme (Extendable af Automattic)
├── extendable-child-theme/   Child theme med vores tilpasninger
│   ├── style.css             Typografi, farver og layout-justeringer
│   ├── functions.php         Indlæser mother og child themets stylesheets
│   └── screenshot.png        Preview i WordPress (.png fordi det brugte mother-theme)
├── .gitignore
└── README.md
```

## Teknisk opsætning

| Del | Valg |
|---|---|
| CMS | WordPress |
| Mother theme | Extendable |
| Child theme | Klassisk child theme (style.css + functions.php) |
| Page builder | Elementor (inkl. HTML-widgets) |
| Formularer | SureForms |
| Sikkerhed | Wordfence med 2FA |
| Backup / flytning | All-in-One WP Migration |
| Hosting | Simply.com |

## Fonte

Child themet bruger selvhostede fonte: **Neue Haas Grotesk** og **LED Counter 7**.

Fontfilerne er **ikke** inkluderet i dette repository, da vi bruger
trial-versioner, som ikke må deles offentligt. Ved en rigtig lancering
skulle der købes en licens. Mappen `extendable-child-theme/fonts/` er
derfor udelukket via `.gitignore`.

## Installation

1. Upload `extendable` og `extendable-child-theme` til `wp-content/themes/`
2. Aktivér **Extendable Child Theme** under *Udseende → Temaer*
3. Læg fontfilerne i `extendable-child-theme/fonts/` (ikke inkluderet)

## Gruppe

- Emilia Hørning
- Mathilde Damsgaard
- Olivia Krogh
- Signe Justsen