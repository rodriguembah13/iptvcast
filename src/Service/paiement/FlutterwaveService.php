<?php


namespace App\Service\paiement;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FlutterwaveService
{

    const BASE_URL = "https://api.flutterwave.com/v3/";
    private $params;
    private $looger;
    public $flutterwave;
    /**
     * @var Client
     */
    private $client;

    /**
     * FlutterwaveService constructor.
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->looger = $logger;
        $this->client = new Client([
            'base_uri' => $params->get('FLU_PUBLIC'),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json charset=UTF-8 ',
            ],
            // 'verify' => false,
            // 'http_errors' => false
        ]);

    }

    function postPayement($data)
    {
        $postdata = [
            'tx_ref' => $data['ref'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'redirect_url' => $data['redirect_url'],
            'meta' => [
                'consumer_id' => 23,
                'consumer_mac' => "92a3-912ba-1192a"
            ],
            'customer' => [
                'email' => $data['email'],
                'phonenumber' => $data['phonenumber'],
                'name' => $data['name']
            ],
            'customizations' => [
                'title' => $data['title'],
                'logo' => $data['logo']

            ],
        ];
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->params->get('FLU_SECRET'),
            ],
            'body' => json_encode($postdata)
        ];
        $endpoint ="payments";
        $response = $this->client->post($endpoint,$options);
        $body = $response->getBody();
        return json_decode($body,true);
    }
}
