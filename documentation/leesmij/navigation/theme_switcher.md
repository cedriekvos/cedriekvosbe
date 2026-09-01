# Theme switcher dropdown

De site had al een licht/donker/auto-thema, bediend via een enkele knop in de header (`#theme-toggle`) die bij elke
klik doorschakelde naar het volgende thema in de vaste volgorde light → dark → auto. Dat werkte, maar had twee
nadelen: de bezoeker kon de drie opties niet in één oogopslag zien, en om van bijvoorbeeld "light" naar "auto" te
gaan moest je twee keer klikken zonder te weten waar je uitkwam zonder het label steeds te lezen. Vandaar de wens om
dit om te bouwen naar een dropdown: één knop die het actieve thema toont, en bij openen een menu met alle drie de
opties direct zichtbaar en aanklikbaar.

## Custom menu in plaats van een native `<select>`

Een native `<select>`-element was de eenvoudigste weg geweest — gratis toetsenbordbediening en toegankelijkheid,
zonder eigen JavaScript voor focusbeheer. Toch is gekozen voor een eigen knop-plus-menu, omdat de huidige knop al
icoon + label combineert (☀ Light, ◐ Dark, ◑ Auto) en dat visuele patroon bij een native `<select>` niet consistent
over browsers te behouden is: de meeste browsers tekenen de gesloten `<select>` met hun eigen chrome en laten geen
iconen naast de tekst toe. Omdat de iconen een bewuste keuze blijven (zie hieronder), is voor het custom menu
gekozen, met het bijbehorende gevolg dat toetsenbord- en focusgedrag (pijltjestoetsen, Escape, klik-buiten) expliciet
moet worden gebouwd en getest in plaats van gratis van de browser te krijgen.

## Gesloten knop: alleen het icoon, groter dan voorheen

Bij nader inzien (na de eerste ronde, zie task 013) bleek de gesloten knop met icoon + label naast de GitHub-link te
veel ruimte in te nemen in de header: één icoon-plus-tekst-knop naast een icoon-alleen GitHub-link oogt onbalans, en
het label voegt weinig toe zolang de icoonvorm zelf al het onderscheid tussen ☀ Light, ◐ Dark en ◑ Auto draagt. De
gesloten knop toont daarom voortaan alléén het icoon (◑ voor de actieve modus), zonder zichtbaar tekstlabel. Om het
icoon-alleen zonder label toch goed leesbaar te houden, is het groter gemaakt — even groot als het GitHub-icoon
ernaast, zodat de twee knoppen in de header visueel in balans zijn (scenario 01b). De accessible name van de knop
blijft de statische tekst "Theme" (scenario 11 blijft ongewijzigd) — schermlezers krijgen dus niet automatisch te
horen welke modus actief is via de knop zelf; dat is een bewuste afweging omdat het geopende menu dat al met
`aria-checked` communiceert zodra de gebruiker het opent.

## Iconen blijven, óók in het menu

De combinatie van icoon en label (☀ Light, ◐ Dark, ◑ Auto) blijft behouden per optie in het geopende menu — alleen de
gesloten knop verliest het label, niet het menu. Dit is de stijl die al bekend is van de huidige toggle, en een
dropdown die binnenin alleen platte tekst toont zou de visuele identiteit van de header onnodig veranderen voor een
wijziging die alleen over de gesloten knop gaat.

## Actieve modus zichtbaar gemarkeerd met een vinkje in het geopende menu

Naast dat de gesloten knop al het icoon van de actieve modus toont, markeert het geopende menu ook de actieve optie
apart. Aanvankelijk (task 013) gebeurde dat alleen via `aria-checked`, zonder zichtbaar visueel signaal — prima voor
schermlezers, maar een ziende gebruiker die het menu opent kon de actieve modus alleen afleiden uit het feit dat hij
'm al van de knop kende, niet uit het menu zelf. Daarom krijgt de actieve optie nu ook een zichtbaar vinkje
(checkmark) naast icoon en label. `aria-checked` blijft daarnaast gewoon staan (scenario 03) — het vinkje is een
aanvulling voor ziende gebruikers, geen vervanging van de bestaande a11y-markering.

### Scenario 02 en het vinkje: volgorde, niet exacte tekst

Scenario 02 (vaste volgorde Light/Dark/Auto bij het openen van het menu) bestond al vóór het vinkje en test alleen de
*volgorde* van de opties, niet de afwezigheid van decoratie. Omdat er altijd precies één actieve modus is — en dus
altijd precies één optie met een vinkje, ook in scenario 02's uitgangssituatie (geen eerder gekozen thema betekent
`auto` als standaard) — kán geen enkele implementatie van het vinkje tegelijk voldoen aan "de actieve optie draagt
een zichtbaar vinkje" (scenario 03) én "alle drie de opties tonen exact hun kale label, zonder toevoeging" als
scenario 02 dat laatste zou eisen. Dat is geen bewuste keuze maar een omissie: scenario 02 is verduidelijkt om
expliciet te negeren welke optie het vinkje draagt, zodat de twee scenario's elkaar niet tegenspreken. De vinkjestest
zelf blijft de taak van scenario 03.

## Volledige toetsenbordbediening

Omdat een custom menu geen gratis toetsenbordondersteuning krijgt zoals een native `<select>`, is gekozen voor het
volledige listbox-patroon: Enter/Spatie/pijl-omlaag op de knop opent het menu met focus op de huidige optie,
pijltjestoetsen bewegen tussen de drie opties, Enter selecteert de gefocuste optie, en Escape sluit het menu en
brengt de focus terug naar de knop. Dit is bewust gekozen boven een minimale variant (alleen Tab + Enter, zonder
pijltjes) omdat de header al langer via het toetsenbord bruikbaar moet zijn (zie de bestaande GitHub-link,
[[header_github_link]]) en een menu met slechts drie opties zich uitstekend leent voor het standaard listbox-patroon
zonder extra complexiteit.

## Sluiten bij selectie, klik-buiten én Escape

Het menu sluit in alle drie de gebruikelijke situaties: na het kiezen van een optie, bij een klik ergens anders op de
pagina, en bij het indrukken van Escape. Een menu dat alleen sluit na een keuze zou een bezoeker die per ongeluk het
menu opent, of van gedachten verandert zonder een andere modus te willen kiezen, dwingen alsnog een optie te
selecteren om ervan af te komen — dat is onnodig wrijving voor een simpel voorkeursmenu.

## Onthouden van de keuze blijft ongewijzigd

De opslag van de gekozen modus in `localStorage` onder de sleutel `theme`, en het toepassen van `auto` via
`prefers-color-scheme`, verandert niet — alleen de manier waarop de bezoeker die keuze maakt (menu in plaats van
doorklikken) wijzigt. Scenario 05 in de feature-specificatie legt vast dat de eerder gekozen modus na een nieuw
bezoek behouden blijft, als regressietest tegen deze bestaande opslag.

## Scope: alleen de publieke header

Net als bij de GitHub-link (zie [[header_github_link]]) blijft deze wijziging beperkt tot de publieke header
(`components/layouts/app.blade.php`). De admin-layout past het thema wel toe, maar heeft geen zichtbare
schakelaar voor de beheerder — dat blijft ongewijzigd, omdat er geen wens is geuit om daar alsnog een switcher toe
te voegen.
