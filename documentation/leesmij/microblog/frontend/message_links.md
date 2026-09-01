# Message links

Een kort bericht verwijst vaak naar iets anders: een tweet, een release notes-pagina, een stuk documentatie. Tot nu toe
stond die URL er als platte tekst, en moest de bezoeker hem selecteren en kopiëren om er te komen. Dat is precies de
wrijving die een microblog niet hoort te hebben — het hele punt van de sectie is dat je er in één oogopslag doorheen
scrolt.

## Alleen kale URL's, geen Markdown

De berichttekst blijft platte tekst. We hebben overwogen om het berichtveld door dezelfde Markdown-pijplijn te halen als
blogposts en de bio — dan zou `[de docs](https://php.net)` werken en had je er meteen nadruk en inline code bij. Dat is
afgewezen. Een blogpost is een document dat je bewust schrijft; een bericht van maximaal 280 tekens is een gedachte die
je opschrijft. Op het moment dat de body Markdown wordt, verandert de betekenis van elk teken dat je typt: een asterisk
of een blokhaakje is dan opeens opmaak in plaats van een teken, en de auteur moet gaan ontsnappen om iets letterlijk te
bedoelen. Die prijs staat niet in verhouding tot wat we ervoor terugkrijgen — één klikbare link.

Dus: wat op een web-URL lijkt wordt een link, en al het andere blijft staan zoals het getypt is. Die regel is in één
zin uit te leggen, en dat is bij een invoerveld zonder voorbeeldweergave belangrijker dan expressiviteit. De keerzijde
zie je terug in het scenario over `[de docs](https://php.net)`: de haakjes blijven zichtbaar en alleen de URL erbinnen
wordt klikbaar. Dat ziet er rommelig uit, maar het is eerlijk — het toont precies wat er is opgeslagen, en het is
consistent met de regel in plaats van er een uitzondering op te maken.

## De volledige URL als linktekst

De link toont de URL letterlijk, hoe lang hij ook is. Verkorten tot alleen het domein (`x.com`) of afbreken met een
ellips was compacter, maar verbergt waar de bezoeker heen gaat, en dat is bij uitgaande links precies de informatie die
hij nodig heeft om te beslissen of hij klikt. Het past ook bij de vormgeving van de sectie: die bootst terminal-output
na, en een terminal kort geen URL's af. Dat lange URL's veel ruimte innemen accepteren we; het bestaande afbreekgedrag
(zie `homepage_microblog_section`, scenario 06) zorgt dat ze binnen de sectie blijven, en dat gedrag is hier opnieuw
vastgelegd omdat een `<a>`-element met een lange, onbreekbare URL erin een reëel risico op horizontaal scrollen is.

Een URL zonder schema, geschreven als `www.php.net`, krijgt `https://` als bestemming. De weergegeven tekst blijft
`www.php.net` — er is geen enkele reden om een bezoeker op platte HTTP te laten beginnen, maar ook geen reden om de
tekst anders te tonen dan hij getypt is.

## Wat níet klikbaar wordt, en waarom

Alleen `http://`, `https://` en `www.` leveren een link op. Alles daarbuiten blijft tekst. Dat is bewust de kleinste
verzameling die het doel dekt:

- **`javascript:`** — als dit een link zou worden, is het een stored-XSS-vector. Berichten schrijven kan alleen wie is
  ingelogd, maar dat is geen argument om het toe te laten: een gecompromitteerd redacteursaccount mag niet kunnen
  uitgroeien tot een aanval op iedere bezoeker. Dat is dezelfde afweging die `MarkdownToHtmlConverter` al maakt met
  `allow_unsafe_links` en `html_input: escape`, en om die reden blijft ook ruwe HTML in een berichttekst ontsnapt.
- **`ftp://`** — niemand linkt hier ooit naar, dus het toelaten voegt alleen oppervlak toe.
- **E-mailadressen en `mailto:`** — een `mailto:`-link opent onvoorspelbaar de mailclient van de bezoeker, wat op een
  telefoon vervelender is dan behulpzaam. Belangrijker: een adres in een bericht is meestal een vermelding, geen
  uitnodiging om te mailen. Wie gemaild wil worden zet dat op de about-pagina.

## Afbreekpunten en nieuwe tabbladen

Een punt of komma direct achter een URL hoort bij de zin, niet bij de link. Dat is de reden dat de afsluitende
interpunctie buiten de link valt: anders levert iedere URL aan het eind van een zin een kapotte bestemming op, en
schrijven mensen hun berichten zonder punt om dat te omzeilen.

Externe links openen in een nieuw tabblad, met `noopener` en `noreferrer`. Dat is niet nieuw beleid maar hetzelfde
gedrag dat blogposts en de bio al hebben; afwijken zou de site inconsistent maken. De praktische reden erachter geldt
hier extra sterk: de microblog-sectie staat onderaan de homepage, en wie daar een link volgt in hetzelfde tabblad
verliest zijn plek in de feed. Links naar de site zelf openen wél in hetzelfde tabblad — daar navigeer je binnen de
site, en een nieuw tabblad per interne klik is alleen maar hinderlijk.
