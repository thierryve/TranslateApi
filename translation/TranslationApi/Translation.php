<?php
namespace TranslationApi;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class Translation {
    private $redis;
    private $validLanguages;

    /**
     * @param \Predis\Client $redis
     */
    public function __construct(\Predis\Client $redis) {
        $this->redis = $redis;
        $this->validLanguages = ['nl_NL', 'en_GB'];;
    }

    /**
     * List translations by language
     *
     * @param $lang
     * @return JsonResponse
     */
    public function listRoute($lang){
        if(!$this->isValidLanguage($lang))
            return $this->returnInvalidLanguageError();
        return new JsonResponse($this->redis->hgetall($lang));
    }

    /**
     * Count number of translations by language
     *
     * @param $lang
     * @return Response
     */
    public function countRoute($lang){
        if(!$this->isValidLanguage($lang))
            return $this->returnInvalidLanguageError();

        return new Response($this->redis->hlen($lang));
    }

    /**
     * Get translation by language and key
     *
     * @param $lang
     * @param $key
     * @return JsonResponse|Response
     */
    public function getRoute($lang, $key){
        if(!$this->isValidLanguage($lang))
           return $this->returnInvalidLanguageError();

        if(!$this->redis->hexists($lang, $key))
            return new JsonResponse("No translation found for: " . $key, Response::HTTP_NOT_FOUND);

        return new Response($this->redis->hget($lang, $key), Response::HTTP_OK, array('Cache-Control' => 's-maxage=3600, public'));
    }

    /**
     * Store translation by language and key
     *
     * @param $lang
     * @param $key
     * @param $trans
     * @return Response
     */
    public function setRoute($lang, $key, $trans) {
        if(!$this->isValidLanguage($lang))
            return $this->returnInvalidLanguageError();

        $this->redis->hset($lang, $key, $trans);
        return new Response($key. ' is added in ' . $lang . ' with translation ' . $trans, Response::HTTP_CREATED);
    }

    /**
     * Remove translation in all languages by key
     *
     * @param $key
     * @return Response
     */
    public function deleteRoute($key) {
        foreach($this->validLanguages as $lang) {
            $this->redis->hdel($lang, $key);
        }
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Check if provided language is valid
     * @param $lang
     * @return bool
     */
    private function isValidLanguage($lang){
        return !in_array($lang, $this->validLanguages) ? false : true;
    }

    /**
     * Return invalid Language error
     * @return Response
     */
    private function returnInvalidLanguageError() {
        return new Response("Language not in accepted languages: " . implode(', ', $this->validLanguages), Response::HTTP_BAD_REQUEST);
    }
}