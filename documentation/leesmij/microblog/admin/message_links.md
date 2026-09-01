# Message links in de admin

De regels voor wat een link wordt staan in `leesmij/microblog/frontend/message_links.md`. Dit document gaat alleen over
de vraag waar die links wél en waar ze níet worden weergegeven zodra je aan de beheerkant zit.

## De lijst rendert links, het invoerveld niet

De berichtenlijst in de admin toont links, net als de homepage. De reden is de controle achteraf: na het plaatsen van
een bericht wil je weten of de link daadwerkelijk werkt, en dat is de meest voorkomende fout in een bericht dat verder
uit één regel bestaat. Als de link alleen op de homepage klikbaar is, moet je voor die controle heen en weer schakelen
tussen twee schermen. De lijst is toch al een weergave, geen invoerveld, dus hij mag dezelfde weergaveregels volgen als
de publieke kant. Bijkomend voordeel: wat je in de lijst ziet is wat de bezoeker ziet, dus een verschil tussen de twee
is meteen een signaal dat er iets mis is.

Het tekstveld van de composer doet hier níet aan mee. Daar bewerk je de bron, en wie de ruwe tekst bewerkt moet de ruwe
tekst zien — inclusief het volledige, onopgemaakte adres. Een `<textarea>` kan geen links bevatten en dat is hier geen
beperking maar precies het gewenste gedrag.

## De tekenlimiet blijft ongemoeid

De grens van 280 tekens gaat over hoeveel er wordt opgeslagen, niet over hoe het wordt weergegeven. Een lange URL kost
dus gewoon een flink deel van het budget.

We hebben overwogen om URL's buiten de telling te laten of ze als vaste lengte te rekenen, zoals Twitter deed met
t.co-links. Dat is afgewezen: het maakt de limiet onvoorspelbaar voor de auteur, en zodra de opgeslagen lengte en de
getelde lengte uit elkaar lopen kan een teller in het formulier niet meer eerlijk zijn over hoeveel ruimte er over is.
De huidige situatie heeft ook een gunstig neveneffect: dat een lange URL veel van je 280 tekens opeet, is een prikkel om
kort te linken in plaats van een probleem dat opgelost moet worden.

Dat gedrag ongewijzigd is, is met een scenario vastgelegd in plaats van stilzwijgend gelaten. Deze feature is precies
het moment waarop iemand zou kunnen denken dat de limiet moest meebewegen; een scenario zorgt ervoor dat zo'n wijziging
een bewuste keuze wordt en niet een stille.
