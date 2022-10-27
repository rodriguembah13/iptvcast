<?php


namespace App\Controller\Api;



use App\Entity\Souscription;
use App\Repository\BouquetRepository;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Service\paiement\EkolopayService;
use App\Service\paiement\FlutterwaveService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentApiController extends AbstractFOSRestController
{
    private $customerRepository;
    private $userRepository;
    private $logger;
    private $params;
    private $ekoloService;
    private $doctrine;
    private $souscriptionRepository;
    private $bouquetRepository;
    private $flutterService;

    /**
     * PaymentApiController constructor.
     * @param UserRepository $userRepository
     * @param BouquetRepository $bouquetRepository
     * @param LoggerInterface $logger
     * @param EkolopayService $ekolopayService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(FlutterwaveService $flutterwaveService,UserRepository $userRepository,BouquetRepository $bouquetRepository,
                                LoggerInterface $logger,EkolopayService $ekolopayService,CustomerRepository $customerRepository,

                                EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->ekoloService=$ekolopayService;
        $this->logger = $logger;
        $this->doctrine=$entityManager;
        $this->bouquetRepository=$bouquetRepository;
        $this->customerRepository=$customerRepository;
        $this->flutterService=$flutterwaveService;
    }

    /**
     * @Rest\Post("/callbackajax", name="notifyurlajax")
     * @param Request $request
     * @return Response
     */
    public function notifyurl(Request $request): Response
    {
        $this->logger->error("notify call");
        $token = $_POST['purchaseToken'];
        $this->logger->error($token);
        $statusbool = $_POST['paymentSuccessful'];
        $status = $_POST['status'];
        $souscription = $this->souscriptionRepository->findOneBy(['tokentransaction'=>$token]);
        if ($statusbool==true){
            $response= $this->ekoloService->verifierPayment($token);
            if ($response['code']==200){
                $souscription->setStatut(Souscription::ACCEPTED);
               $customer= $souscription->getCustomer();
               $date=$customer->getExpiredAt();
               $month=$this->getPeriodeFromAmouint($souscription->getAmount());
                date_add($date, date_interval_create_from_date_string($month." months"));
               $customer->setExpiredAt($date);
            }else{
                $souscription->setStatut(Souscription::ECHEC);
            }
        }
        $this->doctrine->flush();
        return new JsonResponse([], 200);
    }
    /**
     * @Rest\Post("/v1/sendpaiementekolo/ajax", name="sendpaiementekolopay", methods={"POST"})
     */
    public function sendpaiementcinetpay(Request $request): Response
    {
        $this->logger->error($this->getParameter("EKOLO_URL"));
        $res = json_decode($request->getContent(), true);
        $data=$res['data'];
        $reference = "";
        $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
        for ($i = 1; $i <= 12; ++$i) {
            $reference .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
        }
        $customer=$this->customerRepository->find($data['customer_id']);
        $bouquet=$this->bouquetRepository->find($data['bouquet_id']);
        $notify_url = $this->generateUrl('notifyurlflutterajax', ['ref' => $reference, 'customer' => $customer->getId()]);
        $notify_url = $this->params->get('domain') . $notify_url;
        $data = [
            'amount' => $bouquet->getPrice(),
            'currency' => 'USD',
            'payment_method' => 'card',
            'country' => 'CMR',
            'ref' => $reference,
            'title' => 'Bouquet: '.$bouquet->getName(),
            'description' => 'Voting session',
            'email' => $data['email'],
            'phonenumber' => $data['phone'],
            'name' => 'Bouquet: '.$bouquet->getName(),
            'last_name' => $customer->getCompte()->getName(),
            'logo' => 'http://www.piedpiper.com/app/themes/joystick-v27/images/logo.png',
            'pay_button_text' => "Valider le vote",
            'successurl' => $notify_url,
            'redirect_url' => $notify_url,
        ];
        $response= $this->flutterService->postPayement($data);
        $this->logger->info($notify_url);
        $arrays=[];
        if ($response['status'] == "success"){
            $souscription=new Souscription();
            $souscription->setCustomer($customer);
            $souscription->setBouquet($bouquet);
            $souscription->setCreated(new \DateTime('now',new \DateTimeZone('Africa/Douala')));
            $souscription->setStatus(Souscription::PENDING);
            $this->doctrine->persist($souscription);
            $this->doctrine->flush();
            $returnurl= $response["data"]['link'];;
            $arrays=[
              'code'=>200,
                'url'=>$returnurl,
              'message'=>'transaction send'
            ];
        }else{
            $arrays=[
                'code'=>0,
                'token'=>"",
                'message'=>'echec de transaction'
            ];
        }
         $view = $this->view($arrays, Response::HTTP_OK, []);
        return $this->handleView($view);
     }
     function getAmount($periode){
        $val=650;
        switch ($periode){
            case "1":
                $val=650;
                break;
            case "2":
                $val=2*650;
                break;
            case "3":
                $val=3*650;
                break;
            case "4":
                $val=4*650;
                break;
            case "5":
                $val=5*650;
                break;
        }
        return $val;
     }
    function getPeriodeFromAmouint($amount){
        $val=1;
        switch ($amount){
            case 650:
                $val=1;
                break;
            case 1300:
                $val=2;
                break;
            case 1950:
                $val=3;
                break;
            case 2600:
                $val=4;
                break;
            case 3250:
                $val=5;
                break;
        }
        return $val;
    }
}
