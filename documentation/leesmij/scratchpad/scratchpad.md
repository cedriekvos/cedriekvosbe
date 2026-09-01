# Admin scratchpad

Tijdens het schrijven en beheren van posts wil de redacteur af en toe iets losstaands noteren: een ideeën voor een
volgende post, een to-do, een linkje om later te checken. Daar bestond nog geen plek voor — de auteur moest hiervoor
buiten de applicatie om, bijvoorbeeld in een los bestand op de eigen computer. Deze specificatie voegt een scratchpad
toe: één scherm in het admin-gedeelte waar zulke aantekeningen kunnen worden bijgehouden, zonder dat ze ooit worden
gepubliceerd.

## Waarom één enkel scherm, geen lijst

Net als bij de about-me content gaat het hier om precies één document, niet om een verzameling. Er is geen "nieuw"- of
"verwijder"-actie nodig: de scratchpad bestaat per definitie en wordt gewoon geopend met de huidige inhoud. Dat maakt
het scherm net zo eenvoudig als de about-me editor, en om diezelfde reden gebruiken we hetzelfde patroon.

## Eén gedeelde scratchpad, niet per redacteur

De applicatie is een gesloten systeem met een handvol redacteuren, en nergens elders wordt content per gebruiker
gescheiden opgeslagen — posts en about-me zijn ook gedeeld. Een scratchpad die per account apart zou worden
bijgehouden, zou een nieuw soort opslag introduceren (koppeling aan een gebruiker) die nergens anders in de applicatie
bestaat. Omdat de redacteuren elkaar kennen en de scratchpad puur informeel is, kiezen we voor één gedeeld document:
wie er ook inlogt, ziet en bewerkt dezelfde aantekeningen. Mocht blijken dat redacteuren toch behoefte hebben aan
gescheiden notities, dan is dat een aparte, bewuste uitbreiding — geen impliciete aanname nu.

## Dezelfde Markdown-editor als bij een post

De inhoud wordt bewerkt met exact dezelfde Markdown-editor die ook voor de body van een post en de about-me bio wordt
gebruikt. Dat is bewust: het scherm voelt meteen vertrouwd aan, en er hoeft geen apart, lichter tekstveld gebouwd en
onderhouden te worden voor iets dat verder weinig van een post-body verschilt. De inhoud wordt onbewerkt bewaard,
inclusief lege regels, opsommingen en nadruk — precies zoals bij de about-me bio.

## Leeg is een geldige toestand

Net als de about-me content mag de scratchpad leeg zijn. Er is geen enkele reden om een verplicht veld af te dwingen op
iets dat puur voor eigen gebruik is: een redacteur die zijn aantekeningen heeft verwerkt en de scratchpad wil legen,
moet dat gewoon kunnen opslaan zonder tegen een validatiefout aan te lopen.

## Na het opslaan terug naar de postenlijst

Opslaan bevestigt met "Scratchpad updated." en keert terug naar de postenlijst, exact zoals de post- en about-me
editors doen. Dat houdt de navigatie door het hele admin-gedeelte consistent: elke bewerkactie eindigt op hetzelfde,
voorspelbare startpunt. Het scherm is bereikbaar via een link in de admin-header, net als de about-me editor.

## Nooit gepubliceerd

De scratchpad heeft bewust geen frontend-tegenhanger en wordt nergens omgezet naar HTML voor een bezoeker. Het is
puur een werkdocument binnen het admin-gedeelte. Mocht de scratchpad ooit toch een publieke vorm krijgen, dan is dat
een aparte specificatie — deze feature gaat uitsluitend over het interne, nooit-gepubliceerde gebruik.

## Bewuste beperkingen

Geen geschiedenis, geen versies, geen aparte scratchpads per onderwerp — één document, altijd overschreven bij het
opslaan. Dat dekt het gebruiksdoel (losse aantekeningen tijdens het werk) zonder nu al keuzes te maken over
versiebeheer of organisatie in meerdere notities. Zodra die behoefte blijkt, breiden we dit scherm uit.
