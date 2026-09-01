# Header GitHub link

De header linkte tot nu toe alleen terug naar de homepage via het vosje-logo. Er was geen manier voor een bezoeker
om vanaf de site direct naar mijn GitHub-profiel (github.com/cedriekvos) te springen, terwijl dat juist een van de
plekken is waar mensen mijn code en open-source werk kunnen bekijken. Vandaar deze link, permanent zichtbaar in de
header naast de theme-toggle, op elke publieke pagina.

We beperken dit bewust tot de publieke site-header (`components/layouts/app.blade.php`). De admin-omgeving is een
werkplek voor mijzelf, geen bezoekerspagina — een GitHub-link heeft daar geen functie en zou alleen ruis toevoegen.

De link opent in een nieuw tabblad. Een externe link die het huidige tabblad zou overschrijven, duwt de bezoeker
ongevraagd weg van de site; een nieuw tabblad laat hem eenvoudig terugkeren. Omdat `target="_blank"` de nieuwe pagina
toegang geeft tot `window.opener`, voegen we `rel="noopener noreferrer"` toe — dezelfde bescherming die al gold voor
externe links in microblog-berichten (zie [[message_links]]).

De link toont alleen het GitHub-icoon, zonder zichtbare tekst, om de header compact te houden naast de bestaande
logo- en toggle-elementen. Icoon-only betekent wel dat er een expliciet toegankelijk label ("GitHub") nodig is, zodat
schermlezers de link kunnen aankondigen — zonder dat label zou de link voor die bezoekers onherkenbaar zijn.

Tot slot moet het icoon in zowel het lichte als het donkere thema goed leesbaar blijven. De site kent al een
thema-systeem (licht/donker/auto, zie de bestaande `theme-toggle`-knop) waarin overige header-elementen zoals het
logo automatisch meekleuren via de bestaande kleurtokens. Het GitHub-icoon volgt datzelfde patroon in plaats van een
eigen vaste kleur te krijgen, zodat het nooit onzichtbaar wordt tegen de achtergrond van een van beide thema's.
