# Voorbeeldweergave van een pagina

Dit is de pagina-tegenhanger van [[post_preview]] en werkt op dezelfde manier. Een opgeslagen pagina krijgt een
voorbeeldknop in de editor (niet in het overzicht); die opent de weergave in een nieuw tabblad, zodat de editor open blijft.

De preview staat op een eigen URL onder `/admin` en vraagt dus om inloggen. De openbare URL van een conceptpagina blijft
een 404. Dat scheiding is belangrijk omdat pagina's op een root-level slug leven: een conceptpagina mag nooit per
ongeluk via `/{slug}` bereikbaar worden. Ook gepubliceerde pagina's hebben een preview, zodat de knop zich overal gelijk
gedraagt.

De preview toont wat is opgeslagen, niet ongeopslagen invoer, en ontbreekt op het formulier voor een nieuwe pagina omdat
er dan nog niets te tonen is. Een melding maakt duidelijk dat het om een voorbeeld gaat. De slug volgt hetzelfde strikte
formaat als de openbare route, en ruwe HTML wordt ook hier uitgeschreven in plaats van uitgevoerd.
