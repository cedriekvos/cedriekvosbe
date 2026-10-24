# Admin page editor

Pagina's zijn voor inhoud die geen artikel is, zoals een "Over mij" of een colofon. Het formulier heeft daarom alleen
een titel, een slug en een body.

- **Dezelfde spelregels als [[post_form]]** — één formulier voor aanmaken en bewerken, dezelfde slugregels, een nieuwe
  pagina is een concept met `draft-`-opslag, en de slug wordt alleen uit de titel gevuld als hij nog leeg is. Eén
  manier van werken voor beide contenttypes.
- **Geen datum, excerpt of featured** — die velden passen niet bij een pagina: geen publicatiemoment, geen
  overzichtslijst, niets om uit te lichten. Dat is de juiste modellering, geen tijdelijke vereenvoudiging.
