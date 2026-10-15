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

Tot slot moet het icoon in zowel het lichte als het donkere thema goed leesbaar blijven, zodat het nooit onzichtbaar
wordt tegen de achtergrond van een van beide thema's.

## Puur zwart en wit, net als het LinkedIn-logo

Aanvankelijk kleurde het icoon mee met de tekstkleur van het thema, zoals de overige header-elementen: bijna-zwart
(#1f2328) in het lichte thema, lichtgrijs (#c9d1d9) in het donkere. Op zichzelf was dat goed leesbaar. Maar sinds de
LinkedIn-link ernaast staat (zie [[header_linkedin_link]]) valt het verschil op: het officiële LinkedIn-logo is puur
zwart of puur wit, en daarnaast oogt het GitHub-icoon in het donkere thema grijs en flets. Twee profiellogo's die
visueel bij elkaar horen, krijgen ook dezelfde kleur.

Het GitHub-icoon krijgt daarom een vaste kleur per thema: puur zwart (#000000) in licht, puur wit (#ffffff) in donker
— exact de kleuren van de twee LinkedIn-varianten. Omdat de kleur nog steeds per thema wisselt, blijft het contrast
met de achtergrond gewaarborgd; dat was de reden om het icoon oorspronkelijk te laten meekleuren. De tekstkleur van de
site zelf verandert niet: lichtgrijze tekst leest op een donkere achtergrond rustiger dan fel wit, en alleen de
logo's wijken daarvan af. Ook de theme-toggle houdt zijn gedempte kleur, want dat is een bediening van de site en
geen profiellogo.
