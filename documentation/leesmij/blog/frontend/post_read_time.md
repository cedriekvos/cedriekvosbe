# Post read time

Naast de publicatiedatum toont elke post een geschatte leestijd ("X min read"), zowel in de bloglijst op de homepage
als op de detailpagina van de post zelf. Dit stond al als losse ontwerp-badge in de homepage-template, maar met een
hardcoded placeholder ("5 min read") en een `TODO`-comment — er was nog geen echte berekening achter. Deze spec vult
die berekening in en breidt de weergave uit naar de detailpagina, zodat een bezoeker ook daar vooraf weet hoeveel tijd
het lezen kost.

## Waarop de berekening is gebaseerd

De leestijd wordt berekend op basis van de zichtbare, gerenderde tekst van de post (`$post->content`, met HTML-tags
eruit gestript) — niet op de ruwe markdown-brontekst (`$post->body`). Markdown-syntax zoals `**`, `[tekst](url)` en
image-tags (`![alt](url)`) bevat tekens en woorden die een lezer nooit daadwerkelijk leest; die zouden de leestijd
kunstmatig opblazen. Een alt-tekst bij een afbeelding is hier het duidelijkste voorbeeld: die kan best lang zijn maar
draagt niets bij aan de tijd die iemand aan het lezen van de post besteedt, dus telt hij niet mee. Dit sluit aan bij
hoe `PostMarkdownToHtmlConverter::excerpt()` al werkt: eerst renderen naar HTML, dan tags strippen, dan pas de tekst
gebruiken.

## Rekenregels

We gaan uit van 200 woorden per minuut, een veelgebruikt gemiddelde voor doorsnee blogproza. Het aantal woorden delen
we door 200 en ronden we naar boven af naar de eerstvolgende minuut, met een ondergrens van 1 minuut. Naar boven
afronden voorkomt dat we "0 min read" tonen bij een heel korte post, en het is ook de gebruikelijke conventie bij
blogs (liever een fractie overschatten dan onderschatten) — een bezoeker die iets sneller klaar is dan aangegeven
voelt zich niet bekocht; andersom wel.

## Waarom ook op de detailpagina

De homepage-badge bestond al als ontwerp, maar de detailpagina toonde tot nu toe helemaal geen leestijd. Omdat de
leestijd bedoeld is om een bezoeker te helpen inschatten of hij/zij nu tijd wil vrijmaken om te lezen, hoort die
indicatie op beide plekken: zowel bij het overzicht (vóór het klikken) als op de post zelf (nog steeds zichtbaar
zodra je aan het lezen bent, bijvoorbeeld na terugkeren via de browser-back-knop).
