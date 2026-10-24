# Post code highlighting

Technische posts bevatten veel code, en met syntax highlighting is die beter te scannen. De library en de kleurthema's
zijn een technische keuze (ADR 0003); deze spec gaat alleen over wat de lezer ziet.

- **Alleen blogposts** — de About-bio gebruikt dezelfde Markdown-rendering, maar krijgt bewust geen highlighting, ook
  niet als de bio een codeblok bevat.
- **Geen of onbekende taal: gewoon een codeblok** — met achtergrond en monospace, alleen zonder kleur. Een typefout als
  `pyhton` breekt de layout niet.
- **Inline code blijft altijd plat** — kleur in lopende tekst leidt af en een los woord heeft er weinig aan. Ook een
  `{taal}`-prefix wordt genegeerd, zodat redacteuren geen extra syntax hoeven te leren.
- **Volgt het thema van de site** — vaste kleuren ogen verkeerd in donkere modus. Licht en donker krijgen elk een eigen
  editorkleurenschema dat sleutelwoorden, strings, getallen en commentaar onderscheidt, en de twee moeten zichtbaar
  verschillen.
