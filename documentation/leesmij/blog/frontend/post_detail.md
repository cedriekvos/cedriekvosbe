# Blog post detail

De homepage is om posts te ontdekken, de detailpagina om ze te lezen. Elke gepubliceerde post heeft een vaste, deelbare
URL: `/blog/{slug}`.

- **Markdown naar HTML**, met een breadcrumb terug naar de homepage en de datum als dag/maand/jaar, net als op de
  homepage.
- **Excerpt als inleiding** — dezelfde tekst als op de homepage, onder de header en vóór de inhoud. Eén bron voorkomt
  dat twee samenvattingen uit elkaar lopen. Zonder excerpt is er geen inleiding.
- **Onbekende slug: 404, geen redirect** — een kapotte link moet eerlijk falen, voor de bezoeker en voor zoekmachines.
- **Alleen geldige slugs** — kleine letters, cijfers en koppeltekens. De slug wijst naar een bestand op schijf, dus al
  het andere geeft een 404 voordat er iets gelezen wordt.
- **Markdown wel, ruwe HTML niet** — ruwe HTML wordt geëscapet en `javascript:`-links worden geen link. Alleen de
  beheerder schrijft, maar zo kan een overgenomen account geen script bij elke bezoeker uitvoeren. De prijs: geen eigen
  HTML-blokken in een post, want de opmaak komt volledig uit Markdown.
