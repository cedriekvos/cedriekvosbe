# Admin post editor

Aanmaken en bewerken delen één formulier: zonder slug een nieuwe post, met slug een bestaande. Zo zijn validatie en
opslag voor beide gegarandeerd gelijk.

- **Veilige standaardwaarden** — een nieuwe post is een concept met de datum van vandaag. Publiceren is altijd een
  bewuste keuze.
- **Slugregels** — alleen kleine letters, cijfers en koppeltekens, en nooit `draft` of `draft-…`. Concepten staan op
  schijf als `draft-{slug}`; een gepubliceerde post met zo'n slug zou niet van een concept te onderscheiden zijn.
- **Geen stille overschrijving** — een slug die al bestaat geeft een foutmelding.
- **Featured staat los van concept** — een schakelaar, standaard uit, voor de badge op de homepage
  ([[homepage_featured_post]]). Meerdere featured posts mogen; een limiet zou alleen extra klikken kosten.
- **Slug uit de titel** — wordt alleen ingevuld als de slug nog leeg is; een ingevulde slug wordt nooit overschreven.
