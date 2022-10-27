<?php


namespace App\Service\paiement;


use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EkolopayService
{
    const BASE_URL = "https://ekolopay.com";
    private $params;
    /**
     * @var Client
     */
    private $client;
    private $tokencinet;
    private $logger;

    /**
     * EkolopayService constructor.
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->logger = $logger;
        $this->client = new Client([
            'base_uri' => $params->get('EKOLO_URL'),
        ]);
    }

    function postRequest($data)
    {
        $endpoint = "/api/v1/gateway/purchase-token?api_client=" . $this->params->get('EKOLOAPI');
        $myuuid = $this->guidv4();
        $product = [
            "label" => "Paiement session",
            "amount" => $data['amount'],
            "details" => "",
        ];
        $customer = [
            "uuid" => $myuuid,
            "name" => $data['name'],
            "phone" => $data['phone']
        ];
        $arrayJson = [
            "customer" => json_encode($customer),
            "product" => json_encode($product),
            "amount" => $data['amount'],
            "secret_key" => $this->params->get('EKOLO_SECRETKEY')
        ];

       // $this->logger->info(json_encode($arrayJson));
        $options = [
            'headers' => [
                'Accept' => 'application/x-www-form-urlencoded',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                "customer" => json_encode($customer),
                "product" => json_encode($product),
                "amount" => $data['amount'],
                "secret_key" => $this->params->get('EKOLO_SECRETKEY')
            ],
        ];

        $res = $this->client->post($endpoint, $options);
       // $this->logger->info((string)$options);
        $valresp = json_decode($res->getBody(), true);

        $response = $valresp['response'];
        $this->logger->info("-------------------------------------------");
        $this->logger->info($response['API_RESPONSE_CODE']);
        if ($response['API_RESPONSE_CODE'] == 200) {
            return [
                'code' => 200,
                'message' => $response['API_RESPONSE_DATA']['API_DATA']['purchaseToken']
            ];
        } else {
            return [
                'code' => 0,
                'message' => $response['API_RESPONSE_DATA']['clearMessage']
            ];
        }
    }

    function sentUserAgent($purchasetoken)
    {
        $url = "https://ekolopay.com/api/v1/gateway/purchase-product=" . $purchasetoken;
       // $res = $this->client->get($endpoint);
        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                    "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);
        $file = file_get_contents($url, false, $context);

    }

    function verifierPayment($data)
    {
        $endpoint = "/api/v1/gateway/check-payment?api_client=" . $this->params->get('EKOLOAPI');
        $arrayJson = [
            "purchaseToken" => $data,
            "secret_key" => $this->params->get('EKOLO_SECRETKEY')
        ];

        $this->logger->info(json_encode($arrayJson));
        $options = [
            'headers' => [
                'Accept' => 'application/x-www-form-urlencoded',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => $arrayJson,
        ];
        $res = $this->client->post($endpoint, $options);

        $valresp = json_decode($res->getBody(), true);
        $response = $valresp['response'];
        if ($response['API_RESPONSE_CODE'] == 200) {
            return [
                'code' => 200,
                'message' => $response['API_RESPONSE_DATA']['API_DATA']['payment_successful']
            ];
        } else {
            return [
                'code' => 0,
                'message' => $response['API_RESPONSE_DATA']['clearMessage']
            ];
        }
    }

    function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}
