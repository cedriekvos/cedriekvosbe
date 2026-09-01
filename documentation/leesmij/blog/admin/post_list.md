# Admin post list

Waar de homepage uitsluitend gepubliceerde posts toont, laat het admin-overzicht juist *alle* posts zien — gepubliceerd
én concept. Een redacteur moet immers ook het werk-in-uitvoering kunnen terugvinden, openen en verder bewerken. Drafts
worden duidelijk gemarkeerd met een `draft`-label en herkenbaar gemaakt aan hun `draft-`-slug, zodat in één oogopslag
zichtbaar is wat wél en niet live staat.

Verwijderen is een onomkeerbare actie en krijgt daarom een extra drempel: de gebruiker moet de verwijdering eerst
bevestigen voordat het bestand daadwerkelijk van schijf wordt gehaald. Na afloop tonen we een korte bevestigingsboodschap
("Post [slug] deleted.") zodat duidelijk is dat de actie is geslaagd.

Net als op de homepage kiezen we hier voorlopig bewust voor één enkele lijst zonder paginering of zoekfunctie. Bij het
huidige aantal posts is dat ruim voldoende en houdt het de interface eenvoudig. Zodra het overzicht onhandig lang wordt,
herzien we die keuze en voegen we filtering of paginering toe.

## Datumnotatie

De datum naast elke slug tonen we, net als op de publieke pagina's, als dag/maand/jaar in plaats van het intern
opgeslagen jaar-maand-dag. Eén notatie voor de hele site is voorspelbaarder voor de redacteur, die vaak schakelt
tussen het admin-overzicht en de publieke pagina's. Het datumveld in het formulier waarmee een post wordt aangemaakt
of bewerkt blijft ongewijzigd: dat is een los, technisch invoerformaat en geen weergave.
