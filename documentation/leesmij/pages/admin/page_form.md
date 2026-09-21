# Admin page editor

Pagina's zijn de tegenhanger van blogposts voor inhoud die geen artikel is: een "Over mij", een colofon, een
contactpagina. Zulke inhoud heeft geen publicatiedatum die ertoe doet, geen excerpt om op een overzichtspagina te
tonen (er ís geen overzicht) en geen "featured"-status (er is niets om tussen te kiezen). Vandaar dat het pagina-
formulier bewust een kaler formulier is dan het postformulier: alleen titel, slug en body.

Net als bij posts gebeuren aanmaken en bewerken met één en hetzelfde formulier, en gelden dezelfde spelregels rond de
slug: alleen kleine letters, cijfers en koppeltekens, nooit letterlijk `draft` of beginnend met `draft-`, en nooit een
slug die al in gebruik is. Deze regels een-op-een overnemen van [[post_form]] houdt de twee content-types consistent
voor de redacteur, die toch al met beide gaat werken.

Concept/gepubliceerd blijft ook voor pagina's bestaan, met dezelfde `draft-`-prefix-opslag als bij posts: een nieuwe
pagina begint als concept, en publiceren is een expliciete keuze. Dat een pagina geen datum heeft, verandert niets aan
de reden om concepten te ondersteunen — een redacteur moet een "Over mij"-pagina net zo goed rustig kunnen opbouwen
voordat hij hem live zet.

Het automatisch vullen van de slug vanuit de titel werkt identiek aan het postformulier: alleen zolang de slug nog
leeg is, en nooit overschrijvend zodra er al iets is ingevuld.

## Wat bewust ontbreekt

Datum, excerpt en featured-markering zijn niet een kleiner setje van dezelfde velden, maar velden die inhoudelijk niet
van toepassing zijn op een pagina: een pagina heeft geen publicatiemoment dat de bezoeker moet kennen, staat niet in
een chronologische lijst die een excerpt nodig heeft, en concurreert niet met andere pagina's om een "uitgelicht"-
plek op de homepage. Ze weglaten is dus geen tijdelijke vereenvoudiging maar de juiste modellering van wat een pagina
is.
