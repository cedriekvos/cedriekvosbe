# Authentication

De applicatie kent bewust maar één accounthandeling: inloggen. Er is geen self-service registratie en geen
wachtwoord-reset-flow. Dit is een gesloten systeem met een handvol redacteuren, geen openbaar platform waar onbekenden
zich aanmelden. Accounts worden buiten de UI aangemaakt (via een seeder of console-commando). Dat scheelt aanvalsoppervlak
en onderhoud: elke pagina die we niet hebben, kan ook niet misbruikt of stukgaan. De routes voor registratie en reset
bestaan daarom niet en leveren een 404 op.

Na een geslaagde login sturen we de redacteur door naar het admin-gedeelte. Het hele admin-gedeelte zit achter
authenticatie: wie niet is ingelogd en toch `/admin` (of een onderliggende pagina) probeert te openen, wordt
teruggestuurd naar het inlogscherm. Zo is er geen enkele beheerpagina die per ongeluk publiek bereikbaar is.

Twee veiligheidsmaatregelen verdienen toelichting. Ten eerste beschermen we tegen brute-force: na een reeks mislukte
pogingen wordt het inloggen tijdelijk geblokkeerd, met een melding die aangeeft hoeveel seconden de gebruiker moet
wachten. Ten tweede regenereren we bij het inloggen het sessie-id (bescherming tegen session fixation) en legen we de
sessie volledig bij het uitloggen, zodat er na afloop geen herbruikbare sessiegegevens achterblijven.
