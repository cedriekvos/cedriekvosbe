# Message links

URL's in een kort bericht worden klikbaar, zodat de bezoeker ze niet hoeft te kopiëren.

- **Alleen kale URL's, geen Markdown** — met Markdown wordt elke `*` of `[` opmaak en moet de auteur gaan escapen; dat
  weegt niet op tegen één klikbare link. De regel is simpel: wat op een web-URL lijkt wordt een link, de rest blijft
  zoals getypt. Bij `[de docs](https://php.net)` blijven de haakjes dus zichtbaar — rommelig, maar eerlijk.
- **De volledige URL als linktekst** — de bezoeker ziet waar de link heen gaat, en het past bij de terminalstijl. Lange
  URL's breken af binnen de sectie, zodat er niet horizontaal gescrold hoeft te worden.
- **`www.` krijgt `https://`** als bestemming; de tekst blijft zoals getypt.
- **Alleen `http://`, `https://` en `www.`** — de kleinste set die volstaat. `javascript:` zou stored XSS opleveren
  (ruwe HTML wordt om dezelfde reden geëscapet), `ftp://` gebruikt niemand en `mailto:` opent ongevraagd een
  mailprogramma.
- **Afsluitende leestekens horen bij de zin** — een punt of komma direct na een URL valt buiten de link.
- **Externe links openen in een nieuw tabblad** met `noopener noreferrer`, zoals bij posts en de bio; zo verlies je je
  plek in de feed niet. Interne links openen in hetzelfde tabblad.
