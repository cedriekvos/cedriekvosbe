# Page detail

Een pagina staat direct onder de root: slug `about` wordt `/about`. Een pagina is geen artikel uit een reeks maar een
vaste plek op de site; posts houden juist hun gedeelde `/blog/`-voorvoegsel.

- **Bestaande routes winnen** — een pagina met slug `login` is onbereikbaar zolang de inlogpagina daar staat. Dat is een
  bekende beperking; een lijst met verboden slugs komt er pas als het in de praktijk misgaat.
- **Verder zoals [[post_detail]]** — Markdown naar HTML, een 404 op een onbekende of ongeldige slug en escaping van ruwe
  HTML en onveilige links. Datum, excerpt en leestijd ontbreken bewust: daar is geen plek of aanleiding voor.
- **Concepten zijn vanzelf onvindbaar** — ze staan op schijf onder `draft-{slug}`, dus onder de publieke slug bestaat
  nog geen bestand.
