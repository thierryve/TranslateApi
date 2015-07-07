<?php
require_once __DIR__.'/vendor/autoload.php'; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$redis = new Predis\Client();

$validLanguages = ['nl_NL', 'en_GB'];
$app['debug'] = false;

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
 * List translations by language
 */
$app->get('/list/{lang}', function($lang) use($app, $redis, $validLanguages) {
    if(!in_array($lang, $validLanguages))
        return new JsonResponse("Language not in accepted languages: " . implode(', ', $validLanguages), Response::HTTP_BAD_REQUEST);
    
    return new JsonResponse($redis->hgetall($lang));
});

/**
 * Count number of translations by language
 */
$app->get('/count/{lang}', function($lang) use($app, $redis, $validLanguages) {
    if(!in_array($lang, $validLanguages))
        return new JsonResponse("Language not in accepted languages: " . implode(', ', $validLanguages), Response::HTTP_BAD_REQUEST);
    
    return new Response($redis->hlen($lang));
});

/**
 * Get translation by language and key
 */
$app->get('/{lang}/{key}', function($lang, $key) use($app, $redis, $validLanguages) {
    if(!$redis->hexists($lang, $key))
        return new JsonResponse("No translation found for: " . $key, Response::HTTP_NOT_FOUND);       
    
    return new Response($redis->hget($lang, $key), Response::HTTP_OK, array('Cache-Control' => 's-maxage=3600, public'));
});

/**
 * Store translation by language and key
 */
$app->match('/{lang}/{key}/{trans}', function($lang, $key, $trans) use($app, $redis, $validLanguages) {
    if(!in_array($lang, $validLanguages))
        return new Response("Language not in accepted languages: " . implode(', ', $validLanguages), Response::HTTP_BAD_REQUEST);       
  
    $redis->hset($lang, $key, $trans);
    return new Response($key. ' is added in ' . $lang . ' with translation ' . $trans, Response::HTTP_CREATED);
})->method('GET|PUT|POST');

/**
 * Remove translation in all languages by key
 */ 
$app->delete('/{key}', function($key) use($app, $redis, $validLanguages) {
    foreach($validLanguages as $lang) {
        $redis->hdel($lang, $key);
    }
    return new Response('', Response::HTTP_NO_CONTENT);
});
if ($app['debug']) {
    $app->run();
} else {
    $app['http_cache']->run();
}

