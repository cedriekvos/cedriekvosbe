# Homepage about-me section

Een persoonlijke blog draait niet alleen om de losse posts, maar ook om de persoon erachter. Een eerste bezoeker die
binnenkomt op de homepage moet meteen kunnen plaatsen wiens blog dit is, zonder eerst naar een aparte "over mij"-pagina
te hoeven klikken. Daarom plaatsen we een korte introductie bovenaan de homepage, vóór de lijst met posts: eerst de
context, dan de content.

## Waarom Markdown voor de biotekst

De biotekst schrijven we in Markdown en renderen we naar HTML, precies zoals de inhoud van een post. Zo kan de auteur met
minimale moeite een link, wat nadruk of een opsomming toevoegen zonder ruwe HTML te hoeven typen. Het hergebruikt
bovendien dezelfde rendering die elders op de blog al gebruikt wordt, wat de verwachtingen consistent houdt: wat in een
post werkt, werkt ook hier.

## Wanneer het blok verschijnt en wanneer niet

Het blok bestaat uit twee velden: een kop (naam of titel) en een biotekst. We kiezen bewust voor een ruimhartige regel:
zodra één van beide velden gevuld is, tonen we het blok. Een kop zonder bio, of een bio zonder kop, is nog steeds een
zinvolle introductie en hoort dus zichtbaar te zijn. Pas wanneer beide velden leeg zijn, verbergen we het blok volledig
en begint de homepage gewoon met de posts.

Die keuze voorkomt een leeg, ongemakkelijk kader bovenaan de pagina in een verse omgeving waar nog niets is ingevuld,
zonder dat we de auteur dwingen álle velden in te vullen voordat er iets verschijnt. Het is vergevingsgezind in beide
richtingen.

## Onafhankelijk van de posts

De introductie gaat over de auteur, niet over de content. Daarom staat de zichtbaarheid van het blok volledig los van de
vraag of er al posts gepubliceerd zijn. Ook op een lege blog — vlak na de eerste deploy, wanneer de homepage nog "no
posts yet" toont — verschijnt de about-me sectie. Juist op dat moment is identiteit waardevol: de bezoeker ziet wie hier
gaat schrijven, ook al staat er nog geen enkele post.

## Bewuste beperkingen

We houden de sectie voorlopig tekstueel: alleen een kop en een biotekst, geen profielfoto en geen social links. Dat dekt
de kern — weten wie er schrijft — zonder dat we nu al keuzes hoeven te maken over beeldformaten, alt-teksten of welke
platforms we wel en niet linken. Zodra daar behoefte aan ontstaat, breiden we het blok uit.

Het beheer van de inhoud gebeurt in het admin-gedeelte via een eigen, enkel scherm; dat redacteursscherm valt buiten deze
specificatie en wordt apart vastgelegd. Deze specificatie beschrijft uitsluitend wat de bezoeker op de homepage ziet.
