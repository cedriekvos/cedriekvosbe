# Homepage microblog section

Korte berichten (microblog) krijgen op de homepage een eigen, duidelijk afgebakende sectie, ingeleid met een
terminal-prompt ("tail -n 100 messages.log") passend bij de stijl van de site, los van de lijst met blogposts. We kiezen bewust niet voor het door elkaar weven van berichten en posts in één tijdlijn: dat
zijn twee verschillende soorten content met een ander doel — een blogpost is doordacht en compleet, een bericht is een
vluchtige, korte update — en die twee door elkaar tonen zou het overzicht juist verwarrender maken in plaats van
completer. Een eigen sectie laat een bezoeker in één oogopslag kiezen waar de aandacht naartoe gaat.

Net als bij blogposts geldt: nieuwste eerst, geen paginering. Bij het huidige (lage) aantal berichten is een volledige
lijst overzichtelijk genoeg; mocht dat aantal flink groeien, dan herzien we deze keuze net zoals we dat bij de
bloglijst al hebben afgesproken.

De sectie toont de volledige berichttekst (nooit ingekort, want de 280-tekens grens houdt berichten toch al kort
genoeg om in hun geheel te tonen) samen met het exacte plaatsingsmoment inclusief tijd (`dag/maand/jaar uur:minuut`).
Die tijdsaanduiding is hier belangrijker dan bij blogposts: meerdere berichten op één dag moeten voor de bezoeker
duidelijk chronologisch te onderscheiden zijn.

De sectie bestaat onafhankelijk van de bloglijst: zijn er geen blogposts maar wel berichten (of andersom), dan blijft
de sectie die wél inhoud heeft gewoon zichtbaar. Zijn er nog geen berichten geplaatst, dan toont de sectie zelf een
duidelijke melding ("Nog geen berichten.") in plaats van leeg of onzichtbaar te zijn — consistent met hoe de bestaande
lege-homepage-status voor blogposts al werkt.
