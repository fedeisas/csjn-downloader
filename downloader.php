<?php
require 'vendor/autoload.php';
require 'utilities.php';

use Guzzle\Http\Client;
use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\DefaultCacheStorage;

const BASE_URL = 'http://www.csjn.gov.ar/investigaciones/2012';
$XMLtypes = [
    'V' => 'indexV.xml',
    'I' => 'indexI.xml',
];
$urls = [];
$rows = [];
$results = [];
$output = [];
$locations = [
    'caba',
    'lamatanza',
    'lomasdezamora',
    'moreno',
    'moron',
    'quilmes',
    'sanisidro',
    'sanmartin',
];

/**
 * Create HTTP client
 */
$httpClient = new Client();

/**
 * HTTP client cache
 */
$cachePlugin = new CachePlugin(array(
    'storage' => new DefaultCacheStorage(
        new DoctrineCacheAdapter(
            new FilesystemCache('cache')
        )
    )
));
$httpClient->addSubscriber($cachePlugin);

/**
 * Fetch location URLs
 */
foreach ($locations as $location) {
    foreach ($XMLtypes as $key => $type) {
        $url = join('/', [BASE_URL, $location, 'data', $type]);
        $body = $httpClient->get($url)->send()->getBody();
        $xml = simplexml_load_string($body);
        foreach ($xml->mc as $entry) {
            $urls[$key][$location][] = join('/', [BASE_URL, $location, 'data', (string) $entry]) . '.txt';
        }
    }
}

/**
 * Fetch rows content
 */
foreach ($locations as $location) {
    foreach (array_keys($XMLtypes) as $type) {
        foreach ($urls[$type][$location] as $url) {
            $body = $httpClient->get($url)->send()->getBody();
            $results[$type][$location][$url] = (string) $body;
        }
    }
}

/**
 * Sanitize results
 */
foreach ($locations as $location) {
    foreach (array_keys($XMLtypes) as $type) {
        foreach ($results[$type][$location] as $url => $content) {
            parse_str($content, $content);
            $content['tipo'] = getRowType($type);
            $content['ubicacion'] = $location;
            $content['id'] = join('-', [$location, str_replace('.txt', '', end((explode('/', $url))))]);
            $output[] = sanitize($content);
        }
    }
}

/**
 * Write CSV
 */
$csvFile = fopen("output.csv", 'w');
fputcsv(
    $csvFile,
    ['id', 'tipo', 'ubicacion', 'sexo', 'edad', 'nacionalidad', 'fecha', 'hora', 'lugar', 'comuna', 'barrio', 'villa', 'movil', 'arma']
);
foreach ($output as $row) {
    fputcsv($csvFile, $row);
}
fclose($csvFile);
