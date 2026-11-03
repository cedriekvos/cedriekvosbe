# Authentication

Inloggen is de enige accounthandeling. Dit is een gesloten systeem met een handvol redacteuren: accounts worden buiten
de UI aangemaakt, en registratie en wachtwoordreset bestaan niet (404). Minder pagina's betekent minder
aanvalsoppervlak.

- Het hele admin-gedeelte zit achter de login; na het inloggen kom je daar terecht.
- Na een reeks mislukte pogingen wordt inloggen tijdelijk geblokkeerd, met de wachttijd in seconden in de melding.
- Bij inloggen krijgt de sessie een nieuw id (tegen session fixation); bij uitloggen wordt ze volledig geleegd.
