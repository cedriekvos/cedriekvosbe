# Blog post detail

De homepage is bedoeld om posts te *ontdekken*; de detailpagina is bedoeld om een post te *lezen*. Daarom krijgt elke
gepubliceerde post een eigen, deelbare URL onder `/blog/{slug}`. De slug komt uit de frontmatter (of valt terug op de
bestandsnaam) en is daarmee voorspelbaar en stabiel — precies wat je wilt voor een link die je deelt of die door een
zoekmachine wordt opgeslagen.

De inhoud wordt op de detailpagina uit Markdown naar HTML gerenderd, zodat redacteuren in een prettig, leesbaar formaat
kunnen schrijven terwijl de bezoeker nette opmaak ziet. De pagina toont bovenaan een korte breadcrumb terug naar de
homepage, zodat de lezer altijd met één klik terug is bij het overzicht.

Een onbekende slug levert bewust een 404 op, en geen redirect naar de homepage of een lege pagina. Een verkeerde of
verouderde link moet eerlijk falen: dan weet de bezoeker dat de post niet (meer) bestaat, en weten zoekmachines dat ze
de URL niet hoeven te indexeren. Een stille redirect zou die fouten juist verbergen.

De publicatiedatum bovenaan de post gebruikt dezelfde dag/maand/jaar-notatie als de homepage, zodat de weergave
overal op de site consistent is.

## Excerpt als inleiding

Naast titel, datum en leestijd toont de detailpagina nu ook de excerpt: een korte inleidende alinea direct onder de
header en vóór de volledige, uit Markdown gerenderde inhoud. Op de homepage helpt de excerpt een bezoeker te bepalen
of een post de moeite van het doorklikken waard is; op de detailpagina zelf dient diezelfde tekst als opstapje naar
het artikel — een korte samenvatting waarmee de lezer weet wat te verwachten voordat hij aan de volledige tekst
begint.

We tonen dezelfde excerpt-tekst die ook op de homepage staat, in plaats van een aparte, langere samenvatting voor de
detailpagina te introduceren. Eén bron van waarheid voorkomt dat de twee teksten uit elkaar gaan lopen en scheelt
redacteuren een extra veld om te onderhouden.

Heeft een post geen excerpt, dan wordt er op de detailpagina simpelweg geen inleidende alinea getoond — dezelfde
regel die op de homepage al geldt. Een lege alinea reserveren zou alleen witruimte opleveren zonder functie.

## Alleen echte slugs bereiken de opslag

De slug in de URL wijst rechtstreeks naar een bestand op schijf. Daarom accepteert de route alleen de vorm die het
adminformulier ook kan opleveren: kleine letters, cijfers en koppeltekens. Alles daarbuiten — een punt, een
padscheidingsteken, een hoofdletter — levert een 404 op nog voordat er iets van schijf wordt gelezen. Dat is geen
extra beveiligingslaag bovenop een gat, maar een eerlijke grens: wat geen postslug kán zijn, hoeft de opslaglaag ook
nooit te zien.

## Markdown wordt gerenderd, HTML niet

De gerenderde HTML van een post wordt ongeëscapet in de pagina gezet — anders zou Markdown niets opleveren. Precies
daarom wordt ruwe HTML *in* de Markdown zelf geëscapet in plaats van vertrouwd, en worden links met een onveilig
schema (zoals `javascript:`) niet als link gerenderd.

Alleen de beheerder schrijft posts, dus dit beschermt niet tegen een vreemde die zomaar HTML plaatst. Het beperkt de
schade als dat ene account ooit wordt overgenomen: zonder deze grens zou één bewerkte post blijvend script uitvoeren
bij iedere bezoeker. De prijs is dat een post geen eigen HTML-blokken meer kan bevatten; voor deze site is dat geen
verlies, want de opmaak komt volledig uit Markdown.
