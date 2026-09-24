# NS Vertrekbord Amersfoort Schothorst

Een vertrekbord voor digital signage (Full HD, 1920×1080). Het toont de actuele vertrektijden van station Amersfoort Schothorst via de [NS Reisinformatie API](https://apiportal.ns.nl/).

Gemaakt voor ICT College Amersfoort (ROC Midden Nederland).

## Wat het bord laat zien

- Vertrektijd, met vertraging in rood (bijv. `16:03 +8`)
- Bestemming, herkenbare tussenbestemming (bijv. "via Schiphol Airport ✈") en de route
- Meldingen van NS, zoals "Let op: overstappen op Amersfoort C."
- Spoor; een gewijzigd spoor is geel gemarkeerd
- Treinsoort en hoeveel minuten het nog duurt tot vertrek
- Treinen die niet rijden worden doorgestreept

De tijden worden elke 30 seconden ververst. Vertrokken treinen verdwijnen vanzelf, en de pagina toont alleen zoveel rijen als er op het scherm passen.

## Hoe het werkt

```
browser (index.html)  →  api.php  →  NS Reisinformatie API
```

De browser roept de NS API niet zelf aan, maar via `api.php`. Daar zijn twee redenen voor:

- **De API-sleutel blijft op de server.** Hij staat nooit in de HTML of JavaScript.
- **Geen CORS-problemen.** De NS API staat verzoeken rechtstreeks vanuit een browser niet altijd toe.

`api.php` bewaart het antwoord 20 seconden, zodat meerdere schermen niet elk apart de API aanroepen. Is NS tijdelijk niet bereikbaar, dan blijven de laatst opgehaalde tijden staan en wordt de status onderin geel of rood.

## API-sleutel aanvragen

De NS API is gratis, maar je hebt een persoonlijke sleutel (*subscription key*) nodig. Die vraag je zo aan:

1. **Account aanmaken:** ga naar het [NS API-portaal](https://apiportal.ns.nl/) en klik op **Sign in**. Kies **Externe bezoeker**, vul het formulier in en verstuur het.
2. **E-mail bevestigen:** je krijgt automatisch een bevestigingsmail. Het kan tot 5 minuten duren voordat je de API's in het portaal ziet.
3. **Product kiezen:** open **Products** en kies **Ns-App**. Daarin zit de *Reisinformatie API* die dit bord gebruikt.
4. **Abonneren:** vul onder *jouw subscriptions* een naam in (bijv. `Ns-App`), ga akkoord met de voorwaarden en klik op **Subscribe**. Je krijgt opnieuw een bevestigingsmail.
5. **Goedkeuring afwachten:** soms moet NS je aanvraag eerst goedkeuren. Dat gebeurt meestal snel.
6. **Sleutel ophalen:** na goedkeuring staat de sleutel op je **profielpagina**. Je ziet een *primary* en een *secondary* key; beide werken. Klik op **Show** om de sleutel te zien.

Deze sleutel stuur je bij elk verzoek mee in de header `Ocp-Apim-Subscription-Key`. Dat doet `api.php` voor je; je hoeft hem alleen in `config.php` te zetten.

> **Tip:** test je sleutel eerst in het portaal. Open *APIs → Reisinformatie API → GET /api/v2/departures*, klik op **Try it** en vul `station` = `AMFS` in. Krijg je daar vertrektijden terug, dan werkt je sleutel.

Een sleutel is persoonlijk. Deel hem niet en zet hem niet in git. Is hij toch uitgelekt, dan kun je op je profielpagina een nieuwe sleutel aanmaken (**Regenerate**).

## Installatie

**Nodig:** PHP 8 met de extensie `curl`, en een API-sleutel (zie [API-sleutel aanvragen](#api-sleutel-aanvragen)).

1. Clone de repository.
2. Kopieer het voorbeeldbestand met de instellingen:
   ```
   cp config.example.php config.php
   ```
3. Zet je sleutel bij `api_key` in `config.php`.
4. Start een webserver, bijvoorbeeld:
   ```
   php -S localhost:8889
   ```
5. Open `http://localhost:8889/`.

> `config.php` staat in `.gitignore`. Commit je sleutel nooit. Staat hij toch per ongeluk op GitHub, maak dan een nieuwe sleutel aan in het NS API-portaal.

### Demo zonder API-sleutel

Open `http://localhost:8889/?demo`. De pagina gebruikt dan de voorbeelddata uit `demo.json`.

## Instellingen

Alle instellingen staan in `config.php`:

| Instelling      | Standaard | Betekenis                                    |
|-----------------|-----------|----------------------------------------------|
| `api_key`       | —         | Je `Ocp-Apim-Subscription-Key`               |
| `station`       | `AMFS`    | Stationscode (bijv. `AMF` voor Amersfoort C.) |
| `max_journeys`  | `10`      | Aantal vertrekken dat wordt opgehaald        |
| `lang`          | `nl`      | Taal van meldingen (`nl` of `en`)            |
| `cache_seconds` | `20`      | Hoe lang een API-antwoord hergebruikt wordt  |

Kies je een ander station, pas dan ook de stationsnaam in de kop van `index.html` aan.

Opmaak (kleuren, rijhoogte) staat bovenin `index.html` onder `:root`. Met een grotere `--row-h` krijg je minder rijen met grotere tekst.

## Bestanden

| Bestand              | Doel                                              |
|----------------------|---------------------------------------------------|
| `index.html`         | Het vertrekbord (HTML, CSS en JavaScript)         |
| `api.php`            | Proxy naar de NS API, met cache                   |
| `config.example.php` | Voorbeeldinstellingen; kopieer naar `config.php`  |
| `demo.json`          | Voorbeelddata voor `?demo`                        |
| `images/`            | Logo ICT College                                  |

## Problemen oplossen

**`502 Bad Gateway` of `SSL certificate problem`**
PHP op Windows kan standaard geen HTTPS-certificaten controleren. `api.php` gebruikt daarom de certificatenopslag van Windows (`CURLSSLOPT_NATIVE_CA`). Werkt dat niet, stel dan in `php.ini` bij `curl.cainfo` een CA-bundel in, bijvoorbeeld [cacert.pem](https://curl.se/docs/caextract.html).

**`config.php ontbreekt`**
Voer stap 2 van de installatie uit.

**`HTTP 401`**
De API-sleutel ontbreekt of klopt niet.

**Het scherm toont "Geen verbinding met NS"**
Open `api.php` rechtstreeks in de browser. Daar zie je de foutmelding van de API.
