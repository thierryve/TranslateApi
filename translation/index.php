<?php
$loader = require __DIR__.'/vendor/autoload.php'; 
$loader->add('TranslationApi', __DIR__);

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$redis = new Predis\Client();

$validLanguages = ['nl_NL', 'en_GB'];
$app['debug'] = false;
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

/**
 * Enable cache
 */
$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
'http_cache.cache_dir' => __DIR__.'/cache/',
));

/**
 * CORS fix
 */
$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
});

/**
 * Translation Controller
 **/
$app['translation.controller'] = $app->share(function() use ($app, $redis) {
    return new TranslationApi\Translation($redis);
});

/**
 * List translations by language
 */
$app->get('/list/{lang}', 'translation.controller:listRoute');

/**
 * Count number of translations by language
 */
$app->get('/count/{lang}', 'translation.controller:countRoute');

/**
 * Get translation by language and key
 */
$app->get('/{lang}/{key}', 'translation.controller:getRoute');

/**
 * Store translation by language and key
 */
$app->match('/{lang}/{key}/{trans}', 'translation.controller:setRoute')->method('GET|PUT|POST');

/**
 * Remove translation in all languages by key
 */ 
$app->delete('/{key}', 'translation.controller:deleteRoute');
if ($app['debug']) {
    $app->run();
} else {
    $app['http_cache']->run();
}

