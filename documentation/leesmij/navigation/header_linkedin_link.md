# Header LinkedIn link

Naast mijn GitHub-profiel is LinkedIn de plek waar mensen mijn professionele achtergrond kunnen bekijken en contact
kunnen opnemen. De header verwees tot nu toe alleen naar GitHub; bezoekers die geen code willen lezen maar iemand zoeken
om mee te praten, hadden geen directe route. Daarom komt er een LinkedIn-link (linkedin.com/in/cedriekvos) bij,
permanent zichtbaar op elke publieke pagina.

De link staat direct naast de GitHub-link, vóór de theme-toggle. Beide zijn externe profielen en horen visueel bij
elkaar; de theme-toggle is een instelling van de site zelf en blijft daarom als laatste staan. Dit is een uitbreiding
van de bestaande header-regel uit [[header_github_link]], geen vervanging: de GitHub-link verandert niet.

Alle keuzes uit de GitHub-link gelden hier net zo. Alleen het icoon, zonder zichtbare tekst, om de header compact te
houden — met als gevolg dat een expliciet toegankelijk label ("LinkedIn") nodig is voor schermlezers. De link opent in
een nieuw tabblad met `rel="noopener noreferrer"`, zodat de bezoeker niet ongevraagd wegnavigeert en de externe pagina
geen toegang krijgt tot `window.opener`.

## Officiële PNG-logo's in plaats van een zelfgetekend icoon

In een eerdere ronde was het icoon een inline SVG die de themakleur volgde. Dat was een tussenstap: LinkedIn biedt op
zijn merksite geen SVG aan, alleen PNG's, en het logo moet uit het officiële merkmateriaal komen — niet uit een
derde-partij-icoonset of een eigen nateken. Daarom is gekozen voor de twee officiële PNG-varianten: een zwart vierkantje
met witte "in" voor het lichte thema en een wit exemplaar voor het donkere thema.

Een afbeelding heeft geen `fill` die met het thema meekleurt, dus het thema bepaalt nu welke variant zichtbaar is: in
licht alleen de zwarte, in donker alleen de witte. Dat is wat de spec vastlegt, en daarbij moet elk exemplaar ook echt
laden — een kapotte afbeelding in de header zou anders onopgemerkt een leeg gat worden. De naam van de link blijft
"LinkedIn" via het toegankelijke label, niet via de afbeeldingen zelf.

Het ®-teken is onderdeel van het officiële artwork. We knippen het er bewust niet af: de merkrichtlijnen van LinkedIn
staan aanpassing van het logo niet toe, en bij deze grootte is het toch nauwelijks leesbaar. Dat het vierkantje visueel
zwaarder oogt dan het kale GitHub-glyph is een bewust geaccepteerde afweging. Hoe de afbeeldingen worden gebundeld en
geserveerd (Vite) is een implementatiekeuze en geen onderdeel van de spec.

Net als bij GitHub blijft het buiten de admin-omgeving: dat is een werkplek, geen bezoekerspagina.
