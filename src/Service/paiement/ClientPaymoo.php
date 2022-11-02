<?php


namespace App\Service\paiement;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use http\Exception;

class ClientPaymoo
{

    /**
     * Orange Money  API Base url
     */
    const BASE_URL = "https://www.paymooney.com/api/v1.0/";
    /**
     * @var string or null
     */
    private $return_url;
    /**
     * @var string or null
     */
    private $cancel_url;
    /**
     * @var string or null
     */
    private $notif_url;
    /**
     * @var string or null
     */
    private $auth_header ;
    /**
     * @var Client
     */
    private $client;

    protected  $params;
    /**
     * ClientApi constructor.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json charset=UTF-8 ',
            ],
            // 'verify' => false,
            // 'http_errors' => false
        ]);
    }
    /**
     * Create API query and execute a GET/POST request
     * @param string $httpMethod GET/POST
     * @param string $endpoint
     * @param array $options
     */
    private function apiCall($httpMethod, $endpoint, $options)
    {
        try {
            if (strtolower($httpMethod) === "post") {
                $data_string = json_encode($options);
                /** @var Response $response */
                $response = json_decode($this->client->request('post', $endpoint, $options)->getBody(),true);

            } else {
                $response = json_decode($this->client->request('get', $endpoint, $options)->getBody(),true);

            }

            return $response;
        } catch (Exception $exception) {
            return $exception->getMessage();
        } catch (GuzzleException $e) {
            return $e->getMessage();
        }

    }

    /**
     * Call GET request
     * @param string $endpoint
     * @param array $options
     */
    public function get($endpoint, $options = null)
    {
        return $this->apiCall("get", $endpoint, $options);
    }

    /**
     * Call POST request
     * @param string $endpoint
     * @param array $options
     */
    public function post($endpoint, $options = null)
    {

        return $this->apiCall("post", $endpoint, $options);
    }
    /**
     * Call POST request
     * @param string $endpoint
     * @param array $options
     */
    public function postfinal($endpoint, $data = null)
    {

        $jsdata = json_encode($data);

        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
            'body' => $jsdata
        ];
        $response = $this->client->post($endpoint,$options);
        $body = $response->getBody();
        return json_decode($body,true);
    }
}
