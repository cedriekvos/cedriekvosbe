# Voorbeeldweergave van een post

Een concept wil je kunnen zien zoals een bezoeker het later ziet, vóór je het publiceert. Daarom krijgt een opgeslagen
post een voorbeeldknop, in de editor (niet in het overzicht, dat bewust licht blijft). De knop opent de weergave in een nieuw tabblad, zodat
de redacteur de editor niet kwijtraakt en niet-opgeslagen werk niet verloren gaat.

De preview leeft op een eigen URL onder `/admin`, achter dezelfde inlog als de rest van het beheer. Dat is bewust: de
openbare URL van een concept blijft gewoon een 404, zodat er niets lekt en het gedrag voor bezoekers niet verandert.
Een link naar de preview delen met iemand zonder account helpt dus niet — dat is de bedoeling. Ook gepubliceerde posts
hebben een preview; het is dezelfde knop en dezelfde weergave, dus de redacteur hoeft geen uitzonderingen te onthouden.

De preview toont wat er op schijf staat. Wie in de editor wijzigingen heeft gemaakt, moet eerst opslaan om ze te zien; we
kiezen niet voor een live-preview van ongeopslagen invoer, omdat dat veel meer complexiteit kost dan het oplevert. Bij
een nieuwe, nog niet opgeslagen post is er niets om te tonen, dus daar ontbreekt de knop.

Om verwarring met de echte pagina te voorkomen toont de preview een duidelijke melding dat het een voorbeeld is. De slug
wordt, net als bij de openbare route, beperkt tot het vaste slug-formaat, omdat die naar een bestand op schijf verwijst.
Ook de veiligheidsregels van de openbare weergave gelden hier: ruwe HTML wordt uitgeschreven in plaats van uitgevoerd.
