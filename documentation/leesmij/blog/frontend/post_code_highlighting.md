# Post code highlighting

De blog is bedoeld voor technische artikelen, en die bevatten regelmatig codevoorbeelden. Tot nu toe rendert de
detailpagina (zie [[post_detail]]) die codeblokken als kale, monochrome `<pre><code>`-tekst: geen kleur, geen
onderscheid tussen sleutelwoorden, strings en commentaar. Dat maakt langere voorbeelden lastig te scannen. Deze feature voegt
syntax highlighting toe aan codeblokken in de volledige post-inhoud, geïnspireerd op het pakket dat in de
taak werd aangedragen (tempestphp.com/3.x/packages/highlight). Welke library en welk kleurenschema precies
gebruikt worden is een technische keuze die aan feature-architect wordt overgelaten — deze spec beschrijft
alleen het zichtbare gedrag.

## Scope: alleen blogposts

De taal is expliciet "voor blogposts". Wat de scope lastig maakt: de homepage rendert ook de About-bio via
dezelfde `PostMarkdownToHtmlConverter` die posts gebruiken. Zonder een expliciete grens zou een naïeve
implementatie de About-bio automatisch meenemen, puur omdat die toevallig dezelfde converter deelt. Scenario
06 legt vast dat dat niet de bedoeling is: de About-bio blijft ongewijzigd, ook als de bio zelf een codeblok
bevat. Dat dwingt de latere implementatie om de highlighting expliciet aan de post-rendering te koppelen, niet
aan de gedeelde converter als geheel.

## Onbekende of ontbrekende taal: gewoon een codeblok, geen highlighting

Een codeblok zonder taalaanduiding (drie backticks zonder woord erachter) of met een taal die niet ondersteund
wordt, mag niet stuklopen en ook niet onopgemaakt terugvallen op de oude, volledig ongestylede weergave. Het
blijft gewoon een codeblok — achtergrond, padding, monospace lettertype — alleen zonder kleur. Dat is
voorspelbaar gedrag voor redacteuren: een typefout in de taalnaam (`pyhton` in plaats van `python`) verpest de
layout niet, het valt alleen de kleuring weg.

## Inline code blijft altijd platte tekst

Een enkel codewoord middenin een zin (`` `$variable` ``) hoort niet met kleur opgemaakt te worden — dat trekt
te veel aandacht binnen lopende tekst en de meeste inline code is te kort om baat te hebben bij highlighting.
Tempest Highlight ondersteunt wel een opt-in syntax (`` `{php}$variable` ``) om een los woord alsnog te
highlighten, maar we kiezen er bewust voor dat inline code altijd plat blijft, ongeacht of die syntax gebruikt
wordt. Redacteuren hoeven zo geen speciale schrijfwijze te leren voor iets dat maar zelden meerwaarde biedt;
scenario 04 legt vast dat de `{taal}`-prefix genegeerd wordt.

## Highlighting volgt het thema van de site — met een eigen, herkenbaar kleurenschema

De site heeft al een licht/donker/auto-schakelaar (zie de theme-toggle in de layout). Vaste, hardgecodeerde
highlight-kleuren zouden in donkere modus onleesbaar of storend kunnen ogen (bijvoorbeeld een fel wit
codeblok-canvas terwijl de rest van de pagina donker is). Daarom moet de highlighting-kleurstelling meebewegen
met dezelfde schakelaar als de rest van de site.

Belangrijk: dit gaat verder dan alleen "de achtergrond wordt donker in donkere modus". Codeblokken krijgen elk
een eigen, samenhangend syntax highlighting-thema — het soort naembare kleurenschema dat je kent van editors
("Tokyo Night", "Catppuccin", "GitHub Dark/Light", "One Dark/Light"), waarbij sleutelwoorden, strings, getallen
en commentaar allemaal een eigen, onderscheidende kleur krijgen. Dat is iets anders dan de sobere
`--t-*`-CSS-variabelen die de rest van de site al gebruikt voor tekst- en achtergrondkleuren — die zijn niet
rijk genoeg om meerdere tokentypes in code van elkaar te onderscheiden.

Welk specifiek thema voor lichte en welk voor donkere modus gekozen wordt, is een technische/visuele keuze die
aan feature-architect wordt overgelaten (deels omdat die keuze vaak vastzit aan de gekozen highlighting-library
en de kant-en-klare thema's die daarbij meekomen). Scenario 06 legt wel het gedragscriterium vast dat de twee
standen zichtbaar verschillende kleuren opleveren — een enkel thema dat toevallig ook in donkere modus gebruikt
wordt, voldoet dus niet.
