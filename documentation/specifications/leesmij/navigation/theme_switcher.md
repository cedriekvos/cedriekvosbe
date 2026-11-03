# Theme switcher dropdown

Het thema (licht, donker of auto) kies je in een dropdown in de header in plaats van door een vaste volgorde te klikken.
Zo zie je alle drie de opties tegelijk en kies je er direct één.

- **Eigen menu, geen native `<select>`** — een `<select>` laat zich niet consistent over browsers opmaken. Toetsenbord-
  en focusgedrag bouwen we daarom zelf.
- **De knop toont altijd het auto-icoon (◑), zonder label of rand** — de knop opent het menu en hoeft de actieve modus
  niet te tonen; een wisselend icoon verwarde. Hij is even groot als het GitHub-icoon en vergroot bij hover op dezelfde
  manier, zonder kleurverandering. Voor schermlezers heet de knop "Theme".
- **Het menu toont "Light", "Dark" en "Auto" zonder iconen** — de actieve optie krijgt een vinkje en `aria-checked`.
  Dat vinkje is de enige plek waar je de actieve modus ziet.
- **Volledig met het toetsenbord te bedienen** volgens het listbox-patroon: openen met Enter, Spatie of pijl-omlaag,
  kiezen met de pijltjes en Enter, sluiten met Escape (de focus gaat terug naar de knop).
- **Sluit na een keuze, bij een klik buiten het menu en bij Escape** — wie het menu per ongeluk opent, hoeft niets te
  kiezen.
- **Opslag ongewijzigd** — de keuze blijft in `localStorage` (`theme`) en `auto` volgt `prefers-color-scheme`.
- **Alleen de publieke header** — de admin past het thema wel toe, maar heeft geen schakelaar.
