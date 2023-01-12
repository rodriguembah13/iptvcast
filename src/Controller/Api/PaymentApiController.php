<?php


namespace App\Controller\Api;


use App\Entity\Activation;
use App\Entity\Card;
use App\Entity\CardCustomer;
use App\Entity\CardPending;
use App\Entity\Souscription;
use App\Repository\ActivationRepository;
use App\Repository\BouquetRepository;
use App\Repository\CardCustomerRepository;
use App\Repository\CardPendingRepository;
use App\Repository\CardRepository;
use App\Repository\CustomerRepository;
use App\Repository\PersonnelRepository;
use App\Repository\UserRepository;
use App\Service\paiement\ClientPaymoo;
use App\Service\paiement\EkolopayService;
use App\Service\paiement\FlutterwaveService;
use App\Service\paiement\OmService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentApiController extends AbstractFOSRestController
{
    private $customerRepository;
    private $userRepository;
    private $logger;
    private $params;
    private $doctrine;
    private $bouquetRepository;
    private $flutterService;
    private $cardRepository;
    private $cardcustomerRepository;
    private $activationRepository;
    private $cardpendingRepository;
    private $personnelRepository;
    private $omService;

    /**
     * PaymentApiController constructor.
     * @param CardRepository $cardRepository
     * @param FlutterwaveService $flutterwaveService
     * @param UserRepository $userRepository
     * @param BouquetRepository $bouquetRepository
     * @param LoggerInterface $logger
     * @param CardCustomerRepository $cardCustomerRepository
     * @param ActivationRepository $activationRepository
     * @param CustomerRepository $customerRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(CardRepository $cardRepository, OmService $omService,
                                FlutterwaveService $flutterwaveService,
                                UserRepository $userRepository, ParameterBagInterface $params,
                                BouquetRepository $bouquetRepository, PersonnelRepository $personnelRepository,
                                CardPendingRepository $cardPendingRepository,
                                LoggerInterface $logger, CardCustomerRepository $cardCustomerRepository, ActivationRepository $activationRepository,
                                CustomerRepository $customerRepository,
                                EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
        $this->doctrine = $entityManager;
        $this->bouquetRepository = $bouquetRepository;
        $this->customerRepository = $customerRepository;
        $this->flutterService = $flutterwaveService;
        $this->cardRepository = $cardRepository;
        $this->cardcustomerRepository = $cardCustomerRepository;
        $this->activationRepository = $activationRepository;
        $this->cardpendingRepository = $cardPendingRepository;
        $this->personnelRepository = $personnelRepository;
        $this->omService = $omService;
        $this->params = $params;
    }

    /**
     * @Rest\Post("/v1/callbackajax", name="notifyurlajax")
     * @param Request $request
     * @return Response
     */
    public function notifyurl(Request $request): Response
    {
        $this->logger->error("notify call");
        $res = json_decode($request->getContent(), true);
        $reference = $res['item_ref'];
        $this->logger->error($reference);
        $statusbool = $res['status'];
        $activation = $this->activationRepository->findOneBy(['reference' => $reference, 'status' => Activation::PENDING]);
        $cardpending = $this->cardpendingRepository->findOneBy(['activation' => $activation->getId()]);
        if ($statusbool == "Success") {
            $activation->setStatus(Activation::SUCCESS);
            $cardpending->setStatus(CardPending::SUCCESS);
        } else {
            $activation->setStatus(Activation::ECHEC);
            $cardpending->setStatus(CardPending::ECHEC);
            $this->doctrine->remove($cardpending);
        }
        $this->doctrine->flush();
        return new JsonResponse([], 200);
    }
    /**
     * @Rest\Post("/v1/omcallbackajax", name="omnotifyurlajax")
     * @param Request $request
     * @return Response
     */
    public function notifyurlOM(Request $request): Response
    {
        $this->logger->error("notify call");
        $res = json_decode($request->getContent(), true);
        $reference = $res['payToken'];
        $this->logger->error($reference);
        $statusbool = $res['status'];
        $activation = $this->activationRepository->findOneBy(['reference' => $reference, 'status' => Activation::PENDING]);
        $cardpending = $this->cardpendingRepository->findOneBy(['activation' => $activation->getId()]);
        if ($statusbool == "SUCCESSFULL") {
            $activation->setStatus(Activation::SUCCESS);
            $cardpending->setStatus(CardPending::SUCCESS);
        }
        $this->doctrine->flush();
        return new JsonResponse([], 200);
    }
    /**
     * @Rest\Post("/v1/activations_om", name="api_activations_om")
     * @param Request $request
     * @return Response
     */
    public function activatecardOM(Request $request): Response
    {
        $res = json_decode($request->getContent(), true);
        $data = $res['data'];
        $produts = $data['bouquets'];
        $amount = $data['amount'];
        $month = $data['month'];
        $this->logger->info($data['amount']);
        $personnel = $this->personnelRepository->find($data['agent']);
        $cardcustomer = $this->cardcustomerRepository->find($data['cardcustomer']);
        $id = "OM_0".rand(100000, 900000)."_00".rand(10000, 90000);
        $notify_url = $this->generateUrl('omnotifyurlajax', ['souscription' => $id]);
        $notify_url = $this->params->get('DOMAINSITE') . $notify_url;
        $dataOM = [
            'subscriberMsisdn' => strval($data['phone']),
            'amount' => strval($data['amount']),
            'description' => "Payement bouquet",
            'notifUrl' => $notify_url,
            'orderId'=>$id
        ];
        $response =$this->omService->pay($dataOM);
        if ($response['data']['inittxnstatus']=="200"){
            //$this->createActivate($cardcustomer, $response['data']['payToken'], $amount, $produts, $month, $personnel, Activation::PENDING);
            $resp = [
                'message' => $response,
                'code' => 200
            ];
        }else{
            $resp= [
                'message' => $response,
                'code' => 500
            ];
        }
        $view = $this->view($resp, Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/v1/activations", name="api_activations_get_ajax")
     * @param Request $request
     * @return Response
     */
    public function activatecard(Request $request): Response
    {
        $res = json_decode($request->getContent(), true);
        $data = $res['data'];
        $produts = $data['bouquets'];
        $amount = $data['amount'];
        $month = $data['month'];
        $personnel = $this->personnelRepository->find($data['agent']);
        $cardcustomer = $this->cardcustomerRepository->find($data['cardcustomer']);
        $reference = "";
        $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
        for ($i = 1; $i <= 12; ++$i) {
            $reference .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
        }
        if (strtolower($data['method']) == 'mobile money') {
            $currency = "XAF";
        } else {
            $currency = "USD";
        }
        $data = [
            'amount' => $amount,
            'currency_code' => $currency,
            'ccode' => 'CM',
            'lang' => 'en',
            'item_ref' => $reference,
            'item_name' => $cardcustomer->getCard()->getName() . " :" . $cardcustomer->getCard()->getNumerocard(),
            'description' => 'Activation card:' . $cardcustomer->getCard()->getNumerocard(),
            'email' => 'exemple@email.com',
            'phone' => '+237' . $cardcustomer->getCustomer()->getCompte()->getPhone(),
            'first_name' => $cardcustomer->getCustomer()->getCompte()->getName(),
            'last_name' => 'Surname',
            'public_key' => $this->params->get('PAYMONNEY_KEY'),
            'logo' => 'https://paymooney.com/images/logo_paymooney2.png',
            'environement' => 'test'
        ];
        $client = new ClientPaymoo();
        $response = $client->postfinal("payment_url", $data);
        if ($response['response'] == "success") {
            $this->createActivate($cardcustomer, $reference, $amount, $produts, $month, $personnel, Activation::PENDING);
            $url = $response["payment_url"];
            $this->logger->info($url);
            $link_array = explode('/', $url);
            $response = [
                'url' => $url,
                'code' => 200
            ];
            $view = $this->view($response, Response::HTTP_OK, []);
            return $this->handleView($view);
        } else {
            $response = [
                'url' => "",
                'code' => 500
            ];
            $view = $this->view($response, Response::HTTP_OK, []);
            return $this->handleView($view);
        }
    }

    /**
     * @Rest\Post("/v1/activations/cash", name="api_activations_cash_get_ajax")
     * @param Request $request
     * @return Response
     */
    public function cashactivatecard(Request $request): Response
    {
        $res = json_decode($request->getContent(), true);
        $data = $res['data'];
        $produts = $data['bouquets'];
        $amount = $data['amount'];
        $month = $data['month'];
        $this->logger->error("M0");
        $cardcustomer = $this->cardcustomerRepository->find($data['cardcustomer']);
        $this->logger->error("M1");
        $personnel = $this->personnelRepository->find($data['agent']);
        $this->logger->error("M2");
        $reference = "";
        $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
        for ($i = 1; $i <= 12; ++$i) {
            $reference .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
        }
        if ($personnel->getSolde() >= $amount) {
            $this->createActivateCash($cardcustomer, $reference, $amount, $produts, $month, $personnel, Activation::SUCCESS);
            $personnel->setSolde($personnel->getSolde() - $amount);
            $this->doctrine->flush();
            $response = [
                'url' => "",
                'message' => 'Successful',
                'code' => 200
            ];
            $view = $this->view($response, Response::HTTP_OK, []);
        } else {
            $this->logger->error("M4");
            $response = [
                'url' => "",
                'message' => 'Solde insuficant',
                'code' => 500
            ];
            $view = $this->view($response, 404, []);
        }
        return $this->handleView($view);
    }

    function createActivate(CardCustomer $card, $reference, $amount, $produts, $month, $personnel, $status)
    {
        $actiavtion = new Activation();
        $actiavtion->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Africa/Douala')));
        $actiavtion->setCard($card->getCard());
        $actiavtion->setAmount($amount);
        $actiavtion->setMonthto($month);
        $actiavtion->setReference($reference);
        $actiavtion->setStatus($status);
        $actiavtion->setBouquets($produts);
        $actiavtion->setCreatedBy($personnel);
        $this->doctrine->persist($actiavtion);
        $this->doctrine->flush();
        for ($i = 0; $i < sizeof($produts); $i++) {
            $cardpending = new CardPending();
            $cardpending->setCardid($card->getCard()->getNumerocard());
            $cardpending->setIsdelete(true);
            $cardpending->setSendornot(1);
            $cardpending->setCardstatus(1);
            $cardpending->setStatus(CardPending::PENDING);
            $cardpending->setActivation($actiavtion->getId());
            $cardpending->setBouquet($produts[$i]);
            $date_line = is_null($card->getPeriodto()) ? new \DateTime('now', new \DateTimeZone('Africa/Douala')) :
                new \DateTime($card->getPeriodto()->format('Y-m-d h:m'), new \DateTimeZone('Africa/Douala'));
            $mod = "+" . $month . " month";
            $date_line->modify($mod);
            $cardpending->setExpiredtime($date_line);
            $this->logger->info($date_line->format("Y-m-d"));
            $this->doctrine->persist($cardpending);
        }
        $this->doctrine->flush();
    }

    function createActivateCash(CardCustomer $card, $reference, $amount, $produts, $month, $personnel, $status)
    {
        $actiavtion = new Activation();
        $actiavtion->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Africa/Douala')));
        $actiavtion->setCard($card->getCard());
        $actiavtion->setAmount($amount);
        // $month = intdiv($amount, $card->getCard()->getAmount());
        //$month = 1;
        $actiavtion->setMonthto($month);
        $actiavtion->setReference($reference);
        $actiavtion->setStatus($status);
        $actiavtion->setBouquets($produts);
        $actiavtion->setCreatedBy($personnel);
        $this->doctrine->persist($actiavtion);

        for ($i = 0; $i < sizeof($produts); $i++) {
            $cardpending = new CardPending();
            $cardpending->setCardid($card->getCard()->getNumerocard());
            $cardpending->setIsdelete(true);
            $cardpending->setSendornot(1);
            $cardpending->setCardstatus(1);
            $cardpending->setStatus(CardPending::SUCCESS);
            $cardpending->setActivation($actiavtion->getId());
            $cardpending->setBouquet($produts[$i]);
            $date_line = is_null($card->getPeriodto()) ? new \DateTime('now', new \DateTimeZone('Africa/Douala')) :
                new \DateTime($card->getPeriodto()->format('Y-m-d h:m'), new \DateTimeZone('Africa/Douala'));
            $mod = "+" . $month . " month";
            $date_line->modify($mod);
            $cardpending->setExpiredtime($date_line);
            $this->logger->info($date_line->format("Y-m-d"));
            $this->doctrine->persist($cardpending);
        }

        $this->doctrine->flush();
    }
}
