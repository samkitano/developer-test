<?php
require_once '../vendor/autoload.php';

//Load Twig templating environment
$loader = new Twig_Loader_Filesystem( '../templates/' );

// TODO: disable debug in production
$twig   = new Twig_Environment( $loader, [ 'debug' => true ] );

// instantiate api caller
$api = new episodeFetcher();

// fetch episodes
$episodes = $api->getEpisodes();

// render template
echo $twig->render('page.html', compact('episodes'));


class episodeFetcher
{
    /** @var GuzzleHttp\Client */
    private $client;

    /** @var mixed */
    private $response;

    /** @var string */
    private $api;

    
    public function __construct()
    {
        $this->client = new GuzzleHttp\Client();
        $this->api    = 'http://3ev.org/dev-test-api/';
    }

    /**
     * return the sorted episodes
     *
     * @return bool|array
     */
    public function getEpisodes()
    {
        try {
            $this->response = $this->client->request('GET', $this->api);
        } catch (Exception $e) {
            // we will not inform users about server errors, custom generic message will be shown instead
            // TODO: log the exception
            return false;
        }

        return $this->sortEpisodes($this->decodeResponse());
    }

    /**
     * decode JSON response into assoc array
     *
     * @return array
     */
    private function decodeResponse()
    {
        return json_decode($this->response->getBody(), true);
    }

    /**
     * Sort the epiodes
     *
     * @param $decoded
     *
     * @return array
     */
    private function sortEpisodes($decoded)
    {
        array_multisort(array_keys($decoded), SORT_ASC, SORT_NATURAL, $decoded);

        return (array) $decoded;
    }
}
