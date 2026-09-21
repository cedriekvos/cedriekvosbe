# Page detail

Een pagina krijgt, anders dan een post, geen URL onder een gedeeld voorvoegsel zoals `/blog/`. Een pagina met slug
`about` staat gewoon op `/about`. Dat past bij wat een pagina ís: geen artikel uit een reeks, maar een losstaand stuk
inhoud dat net zo goed een vaste plek op de site verdient als de homepage zelf. Dit is bewust een andere keuze dan bij
posts, waar het gedeelde `/blog/`-voorvoegsel juist post-content bij elkaar houdt.

Omdat elke pagina de wortel van de site deelt met de bestaande routes (`/`, `/blog`, `/admin`, `/login`, ...), wint een
al bestaande route altijd van een pagina met dezelfde slug — een pagina met slug `login` zou dus nooit bereikbaar zijn
zolang de inlogpagina op die plek staat. Dat is een bekende beperking van de root-level aanpak, geen bug: we voorkomen
dit nu niet met een expliciete lijst van verboden slugs, omdat er op dit moment geen scenario is waarin een redacteur
dat per ongeluk zou doen. Mocht dat in de praktijk wel gebeuren, dan is een duidelijke validatiefout op het
pagina-formulier de logische volgende stap.

Voor het overige is deze pagina de kale versie van [[post_detail]]: dezelfde Markdown-naar-HTML-rendering, dezelfde
harde 404 op een onbekende of verkeerd gevormde slug (geen stille redirect), en dezelfde beveiliging tegen ruwe HTML
en onveilige linkschema's in de body. Een publicatiedatum, een excerpt en een leestijd-indicatie ontbreken bewust:
zonder datumveld is er niets om de leestijd "naast" te tonen, en zonder een overzichtspagina die pagina's toont is er
geen plek waar een excerpt zou verschijnen.

Een concept-pagina is, net als een conceptpost, vanaf de publieke kant simpelweg onvindbaar: doordat een concept op
schijf onder de `draft-`-geprefixte slug staat, bestaat er onder de publieke slug nog geen bestand om te tonen. Er is
dus geen aparte controle nodig om concepten te verbergen — dat volgt vanzelf uit de opslag.
