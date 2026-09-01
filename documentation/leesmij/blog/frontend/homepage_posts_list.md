# Homepage post list

De homepage is de plek waar bezoekers de meest recente blogposts ontdekken. Alle gepubliceerde posts verschijnen op één
pagina, met de nieuwste bovenaan gesorteerd op publicatiedatum, niet op het moment waarop een post in het systeem is
aangemaakt. Per post tonen we drie dingen: de titel (klikbaar, leidt naar de detailpagina van de post), de
publicatiedatum, en een korte excerpt zodat de lezer een idee krijgt van de inhoud zonder eerst door te hoeven klikken.

Wanneer twee posts dezelfde publicatiedatum delen, sorteren we ze alfabetisch op titel (A→Z). We kiezen bewust niet
voor de interne aanmaakvolgorde of het laatste-wijzigingsmoment als tiebreaker: die momenten zijn voor de lezer
onzichtbaar en daardoor onvoorspelbaar. Een alfabetische volgorde is deterministisch en volledig af te leiden uit wat de
bezoeker zelf ziet — de titel — zodat dezelfde verzameling posts altijd in dezelfde volgorde verschijnt.

Conceptposts (drafts) worden volledig verborgen voor bezoekers. Die zijn uitsluitend zichtbaar binnen het
admin-gedeelte, zodat redacteuren in alle rust aan content kunnen werken zonder dat het publiek de halfafgewerkte
versies te zien krijgt.

Voorlopig kiezen we bewust voor één enkele pagina zonder paginering. Dat houdt de implementatie eenvoudig en past bij
het huidige aantal posts. Zodra de bloglijst zo lang wordt dat scrollen hinderlijk voelt, herzien we deze keuze en
introduceren we ofwel paginering ofwel een "top 10 + archief"-aanpak.

## Datumnotatie

De publicatiedatum wordt aan bezoekers getoond als dag/maand/jaar (bijvoorbeeld `01/05/2026`), niet als het
ISO-achtige jaar-maand-dag dat we intern in de frontmatter opslaan. Dag/maand/jaar is de notatie die Nederlandse
lezers gewend zijn en dus sneller en foutlozer lezen dan een jaar-eerst formaat. De onderliggende opslag (frontmatter,
sortering, admin-formulier) verandert niet mee — alleen de weergave op de publieke pagina's wordt aangepast, zodat
sorteerlogica en datumvalidatie ongemoeid blijven.
