# Admin about-me editor

De homepage opent met een korte introductie — een kop en een biotekst — zodat een bezoeker meteen weet wiens blog dit
is. Die inhoud staat los van de posts en wordt apart beheerd. Tot nu toe kon de auteur ze alleen met de hand aanpassen.
Deze specificatie beschrijft het redacteursscherm dat in de frontend-specificatie al was beloofd: één plek in het
admin-gedeelte om de about-me inhoud te beheren, met een echte editor in plaats van handwerk.

## Waarom één enkel scherm, geen lijst

Een blog heeft veel posts maar precies één about-me. Een overzicht met "nieuw" en "verwijderen" — zoals de postenlijst —
slaat hier nergens op. Daarom is het een enkel bewerkscherm dat altijd hetzelfde, altijd bestaande document bewerkt. Er
is geen aanmaak- of niet-gevonden-situatie: het document bestaat per definitie en wordt gewoon geopend met de huidige
waarden ingevuld.

## Hetzelfde gereedschap als bij een post

De auteur bewerkt de biotekst met exact dezelfde Markdown-editor die ook voor de body van een post wordt gebruikt. Dat
is een bewuste keuze: wie al weet hoe je een post schrijft, hoeft niets nieuws te leren. Dezelfde syntaxis, dezelfde
knoppen, dezelfde manier van opslaan. De kop blijft een gewoon tekstveld — een kop is een korte regel, geen opmaak.

De biotekst blijft onbewerkte Markdown; de omzetting naar HTML gebeurt pas bij het tonen op de homepage en valt onder de
frontend-specificatie. Hier gaat het er alleen om dat wat de auteur typt letterlijk en ongeschonden bewaard blijft —
inclusief lege regels, opsommingen en nadruk.

## Allebei de velden zijn optioneel

De frontend toont het about-blok zodra één van beide velden gevuld is, en verbergt het pas wanneer kop én bio leeg zijn.
Het bewerkscherm volgt diezelfde, vergevingsgezinde regel: elke combinatie mag worden opgeslagen, ook twee lege velden.
We dwingen niets af. Een auteur die het blok tijdelijk wil laten verdwijnen, maakt simpelweg beide velden leeg en slaat
op; dat is een geldige toestand, geen fout. Validatie die hier een verplicht veld zou opleggen, zou botsen met de
zichtbaarheidsregel van de homepage en wordt daarom bewust weggelaten.

## Na het opslaan terug naar de postenlijst

Na een geslaagde opslag keert de redacteur terug naar de postenlijst met de bevestiging "About updated." — precies zoals
het bewerken van een post eindigt op diezelfde lijst. De postenlijst is het natuurlijke startpunt van het
admin-gedeelte,
dus daar weer belanden houdt de werkstroom consistent: je vertrekt vanaf de lijst, je komt erop terug. Het scherm zelf
is
bereikbaar via een vaste link in de admin-header, zodat de auteur er vanaf elke beheerpagina in één klik bij kan.

## Bewuste beperkingen

Net als de frontend houden we het redacteursscherm tekstueel: een kop en een biotekst, meer niet. Geen profielfoto, geen
social links, geen voorvertoning naast de editor. Dat dekt de kern — de introductie up-to-date houden — zonder nu al
keuzes te maken over beeld of extra velden. Zodra die behoefte ontstaat, breiden we zowel dit scherm als de frontend
uit.
