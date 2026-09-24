<?php
// Configuratie voor het vertrekbord. Dit bestand wordt nooit naar de browser gestuurd.
return [
    'api_key'       => 'VUL-HIER-JE-API-SLEUTEL-IN', // Ocp-Apim-Subscription-Key
    'station'       => 'AMFS',                       // Amersfoort Schothorst
    'max_journeys'  => 10,
    'lang'          => 'nl',
    'cache_seconds' => 20,                           // voorkomt onnodige API-calls bij meerdere schermen
];
