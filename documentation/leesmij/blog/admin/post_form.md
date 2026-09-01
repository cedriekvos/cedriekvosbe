# Admin post editor

Aanmaken en bewerken gebeuren bewust met één en hetzelfde formulier. Open je het zonder slug, dan staat er een leeg
nieuw bericht klaar; open je het mét slug, dan laadt het de bestaande post in. Dat scheelt een hoop dubbele code en
zorgt ervoor dat de validatie- en opslaglogica voor beide gevallen gegarandeerd identiek is.

Een nieuw bericht krijgt twee veilige standaardwaarden: de datum staat alvast op vandaag, en het bericht is standaard een
*concept*. Zo publiceer je nooit per ongeluk iets met één verkeerde klik — publiceren is altijd een expliciete keuze. De
slug mag alleen kleine letters, cijfers en koppeltekens bevatten en mag niet letterlijk `draft` zijn of met `draft-`
beginnen. Die beperkingen houden URL's netjes en voorspelbaar, en alles rond `draft` is verboden omdat het botst met de
manier waarop we concepten opslaan: een gepubliceerde post met slug `draft-release-notes` zou op schijf niet van een
concept te onderscheiden zijn en dus stilzwijgend van de homepage verdwijnen.

Concepten krijgen op schijf namelijk de prefix `draft-` voor hun slug; publiceren haalt die prefix er weer af (en het
bestand wordt hernoemd). Daardoor kunnen een concept en de uiteindelijke publieke versie dezelfde "publieke" slug delen
zonder elkaar te overschrijven. Probeer je op te slaan onder een slug die al bestaat, dan krijg je een duidelijke
foutmelding in plaats van een stille overschrijving — verlies van bestaande content moet je nooit per ongeluk kunnen
veroorzaken.

Naast concept/gepubliceerd heeft een post ook een onafhankelijke "featured"-markering, aan te zetten met een simpele
schakelaar in hetzelfde formulier — net als de concept-schakelaar standaard uit. Deze bepaalt of de post op de
homepage de "Uitgelicht"-badge krijgt (zie [[homepage_featured_post]]). Er is bewust geen beperking tot één featured
post tegelijk: dat zou de redacteur dwingen om bij elke nieuwe uitgelichte post eerst een andere te ontmarkeren,
terwijl daar geen inhoudelijke reden voor is.

Om het overtikken van de titel naar een slug te besparen, wordt de slug automatisch gevuld zodra de redacteur het
titelveld verlaat — maar alleen als de slug op dat moment nog leeg is. Is er al een slug ingevuld (handmatig, of
omdat een bestaande post wordt bewerkt), dan blijft die ongemoeid: we overschrijven nooit een bewust ingevoerde
waarde.
