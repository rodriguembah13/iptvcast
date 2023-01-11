<?php


namespace App\Service\paiement;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use http\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OmService
{
    /**
     * @var string or null
     */
    private $auth_header;
    /**
     * @var Client
     */
    private $client;
    protected $params;
    private $channelMsisdn = "691301143";

    /**
     * ClientApi constructor.
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        // Credentials: <Base64 value of UTF-8 encoded “username:password”>
        $this->client = new Client([
            'base_uri' => $params->get("OM_URL"),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json charset=UTF-8 ',
            ],
            'verify' => false,
            'http_errors' => false
        ]);
        $this->auth_header = base64_encode($this->params->get('OM_USERNAME') . ":" . $this->params->get('OM_PASSWORD'));
    }

    /**
     * Create API query and execute a GET/POST request
     * @param string $httpMethod GET/POST
     * @param string $endpoint
     * @param array $options
     * @return Response|mixed|string
     */
    private function apiCall($httpMethod, $endpoint, $options)
    {
        try {
            if (strtolower($httpMethod) === "post") {

                /** @var Response $response */
                $response = json_decode($this->client->request('post', $endpoint, $options)->getBody(), true);

            } else {
                $response = json_decode($this->client->request('get', $endpoint, $options)->getBody(), true);

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
     * @return Response|mixed|string
     */
    public function get($endpoint, $options = null)
    {
        return $this->apiCall("get", $endpoint, $options);
    }

    /**
     * Call POST request
     * @param string $endpoint
     * @param array $options
     * @return Response|mixed|string
     */
    public function post($endpoint, $options = null)
    {
        return $this->apiCall("post", $endpoint, $options);
    }

    /**
     * Get Token
     */
    public function getToken()
    {
        $aut=base64_encode($this->params->get('OM_CONSUMER') . ":" . $this->params->get('OM_SECRET'));
        $options = [
            'headers' => [
                'Authorization' => 'Basic ' . $aut,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ]
        ];

        return $this->post('/token', $options);
    }

    public function init()
    {
        $access_token = $this->getAccesToken();
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'X-AUTH-TOKEN' => $this->auth_header,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'verify' => false,
            ],
        ];
        return $this->post('mp/init', $options);
    }

    public function pay($data)
    {
        $id = "OM_0" . rand(100000, 900000) . "_00" . rand(10000, 90000);
        $access_token = $this->getAccesToken();

        $b = [
            "notifUrl"=>$data['notifUrl'],
            "channelUserMsisdn" => $this->channelMsisdn,
            "amount"=>$data['amount'],
            "subscriberMsisdn"=>$data['subscriberMsisdn'],
            "pin" => "2222",
            "orderId" => $data['orderId'],
            "description"=>"test",
            'payToken' => $this->getPayToken(),
        ];
       // $b = array_merge($data, $b);
        $b = json_encode($b);
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'X-AUTH-TOKEN' => $this->auth_header,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
            'body' => $b
        ];
        return $this->post('mp/pay', $options);
    }

    /**
     * @return string
     */
    public function getPayToken(): string
    {
        $dt = $this->init();
        $data = $dt['data'];
        return $data['payToken'];
    }

    public function push($payToken)
    {
        $access_token = $this->getAccesToken();
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'X-AUTH-TOKEN' => $this->auth_header,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
        ];
        return $this->get('mp/push/' . $payToken, $options);
    }

    public function getStatusPayment($payToken)
    {
        $access_token = $this->getAccesToken();
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'X-AUTH-TOKEN' => $this->auth_header,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
        ];
        return $this->get('mp/paymentstatus/' . $payToken, $options);
    }

    public function getAccesToken()
    {
        $rep = $this->getToken();
        return $rep["access_token"];
    }
}
