# Voorbeeldweergave van een pagina

Werkt zoals [[post_preview]]: een knop in de editor van een opgeslagen pagina opent in een nieuw tabblad een voorbeeld
onder `/admin`, achter de login, met een melding dat het om een voorbeeld gaat.

Voor pagina's weegt de scheiding extra zwaar: ze staan op een root-level slug, en een conceptpagina mag nooit via
`/{slug}` bereikbaar worden. De openbare URL van een concept blijft daarom een 404.
