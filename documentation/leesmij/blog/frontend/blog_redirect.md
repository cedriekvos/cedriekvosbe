# Blog redirect

De `/blog`-route is historisch een aparte ingang naar de blog geweest. Nu de homepage zelf de bloglijst toont, heeft een
afzonderlijke `/blog`-pagina geen meerwaarde meer en zou hij hooguit verwarrend zijn (twee URL's met dezelfde inhoud).
Daarom sturen we elk bezoek aan `/blog` door naar de homepage `/`.

We doen dat met een 302-statuscode (tijdelijke redirect), niet met een 301 (permanente redirect). De reden daarvoor is
dat we de optie willen openhouden om `/blog` in de toekomst weer een eigen invulling te geven — bijvoorbeeld een
archiefpagina of een categorie-overzicht. Met een 301 zouden zoekmachines de URL definitief afschrijven, en dat willen
we voorkomen.
