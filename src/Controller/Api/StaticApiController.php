<?php


namespace App\Controller\Api;


use App\Entity\Activation;
use App\Entity\Agence;
use App\Entity\Card;
use App\Entity\CardCustomer;
use App\Entity\CardPending;
use App\Entity\Customer;
use App\Entity\Personnel;
use App\Entity\User;
use App\Repository\ActivationRepository;
use App\Repository\AgenceRepository;
use App\Repository\BouquetRepository;
use App\Repository\CardCustomerRepository;
use App\Repository\CardRepository;
use App\Repository\CustomerRepository;
use App\Repository\PersonnelRepository;
use App\Service\EndpointService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class StaticApiController extends AbstractFOSRestController
{

    private $logger;
    private $endpointsService;
    private $bouquetRepository;
    private $customerRepository;
    private $cardRepository;
    private $cardcustomerRepository;
    private $agenceRepository;
    private $personnelRepository;
    private $doctrine;
    private $passwordEncoder;
    private $activationRepository;

    /**
     * IptvApiController constructor.
     * @param BouquetRepository $bouquetRepository
     * @param LoggerInterface $logger
     * @param AgenceRepository $agenceRepository
     * @param CardCustomerRepository $cardCustomerRepository
     * @param CardRepository $cardRepository
     * @param EndpointService $endpointService
     * @param CustomerRepository $customerRepository
     */
    public function __construct(BouquetRepository $bouquetRepository, LoggerInterface $logger,
                                EntityManagerInterface $entityManager,ActivationRepository $activationRepository,
                                AgenceRepository $agenceRepository,PersonnelRepository $personnelRepository,
                                CardCustomerRepository $cardCustomerRepository, CardRepository $cardRepository,
                                EndpointService $endpointService,UserPasswordHasherInterface $passwordEncoder,
                                CustomerRepository $customerRepository)
    {
        $this->logger = $logger;
        $this->endpointsService = $endpointService;
        $this->bouquetRepository = $bouquetRepository;
        $this->customerRepository = $customerRepository;
        $this->cardRepository = $cardRepository;
        $this->cardcustomerRepository = $cardCustomerRepository;
        $this->agenceRepository=$agenceRepository;
        $this->personnelRepository=$personnelRepository;
        $this->doctrine=$entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->activationRepository=$activationRepository;
    }

    /**
     * @Rest\Get("/v1/customers", name="api_customers")
     * @return Response
     */
    public function getAllCustomers()
    {
        $customers = $this->customerRepository->findAll();
        $values = [];
        foreach ($customers as $customer) {
            $values[] = [
                'id' => $customer->getId(),
                'name' => $customer->getCompte()->getName(),
                'phone' => $customer->getCompte()->getPhone(),
                'email' => $customer->getCompte()->getEmail(),
                'agence' => $customer->getAgence()->getName(),
                'address' => $customer->getAddress(),
                'city' => $customer->getCity()
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/customers/{id}/one", name="api_one_customers")
     * @return Response
     */
    public function getOneCustomer(Customer $customer)
    {
            $values = [
                'id' => $customer->getId(),
                'name' => $customer->getCompte()->getName(),
                'phone' => $customer->getCompte()->getPhone(),
                'email' => $customer->getCompte()->getEmail(),
                'agence' => $customer->getAgence()->getName(),
                'address' => $customer->getAddress(),
                'city' => $customer->getCity()
            ];
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/customers/search", name="api_search_customers")
     * @return Response
     */
    public function searchCustomers(Request $request)
    {
        $customers = $this->customerRepository->searchCustomer($request->get('q'));
        $values = [];
        foreach ($customers as $customer) {
            $values[] = [
                'id' => $customer->getId(),
                'name' => $customer->getCompte()->getName(),
                'phone' => $customer->getCompte()->getPhone(),
                'email' => $customer->getCompte()->getEmail(),
                'agence' => $customer->getAgence()->getName(),
                'address' => $customer->getAddress(),
                'city' => $customer->getCity()
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/agences", name="api_agences")
     * @return Response
     */
    public function getAllAgences()
    {
        $agences = $this->agenceRepository->findAll();
        $values = [];
        foreach ($agences as $agence) {
            $values[] = [
                'name' => $agence->getName(),
                'phone' => $agence->getPhone(),
                'id' => $agence->getId(),
                'address' => $agence->getAddress(),
                'city' => $agence->getCity()
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/personnels", name="api_personnels")
     * @return Response
     */
    public function getAllPersonnel()
    {
        $personnels = $this->personnelRepository->findAll();
        $values = [];
        foreach ($personnels as $personnel) {
            $values[] = [
                'name' => $personnel->getCompte()->getName(),
                'phone' => $personnel->getCompte()->getPhone(),
                'email' => $personnel->getCompte()->getEmail(),
                'id' => $personnel->getId(),
                'agence' => $personnel->getAgence()->getName(),
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/bouquets", name="api_bouquets")
     * @return Response
     */
    public function getAllBouquet()
    {
        $bouquets = $this->bouquetRepository->findAll();
        $values = [];
        foreach ($bouquets as $bouquet) {
            $values[] = [
                'name' => $bouquet->getDescription(),
                'numero' => $bouquet->getNumero(),
                'price' => $bouquet->getPrice(),
                'id' => $bouquet->getId(),
                'bouquetid' => $bouquet->getBouquetid(),
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/cards", name="api_cards")
     * @return Response
     */
    public function getAllCards()
    {
        $cards = $this->cardRepository->findAll();
        $values = [];
        foreach ($cards as $card) {
            $values[] = [
                'name' => $card->getName(),
                'amount' => $card->getAmount(),
                'numero' => $card->getNumerocard(),
                'id' => $card->getId(),
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/activations", name="api_activations_ajax")
     * @param Request $request
     * @return Response
     */
    public function activations(Request $request): Response
    {
        $cards = $this->activationRepository->findAll();
        $values = [];
        foreach ($cards as $card) {
            $values[] = [
                'name' => $card->getCard()->getName(),
                'numero' => $card->getCard()->getNumerocard(),
                'amount' => $card->getAmount(),
                'created'=>$card->getCreatedAt()->format('Y-m-d h:m:s'),
                'monthto' => $card->getMonthto(),
                'id' => $card->getId(),
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/cards/customer/{id}", name="api_customer_cards")
     * @return Response
     */
    public function getCardBycustomer(Customer $customer)
    {
        $cards = $this->cardcustomerRepository->findBy(['customer'=>$customer]);
        $values = [];
        foreach ($cards as $card) {
            $values[] = [
                'name' => $card->getCard()->getName(),
                'numero' => $card->getCard()->getNumerocard(),
                'expireddate' => $card->getPeriodto(),
                'id' => $card->getId(),
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Post("/v1/personnels", name="api_personnels_ajax")
     * @param Request $request
     * @return Response
     */
    public function postpersonnels(Request $request): Response
    {
        $res = json_decode($request->getContent(), true);
        $data=$res['data'];
        $agence=$this->agenceRepository->find($data['agence']);
        if (!is_null($data['id'])){
            $personnel=$this->personnelRepository->find($data['id']);
            $compte=$personnel->getCompte();
        }else{
            $compte=new User();
            $compte->setEmail($data['email']);
            $compte->setUsername($data['email']);
            $encodedPassword = $this->passwordEncoder->hashPassword($compte, "cast12345");
            $compte->setRoles(['ROLE_AGENT']);
            $compte->setPassword($encodedPassword);
            $this->doctrine->persist($compte);
            $personnel=new Personnel();
            $personnel->setCompte($compte);
            $personnel->setCreatedAt(new \DateTimeImmutable('now',New \DateTimeZone('Africa/Douala')));
            $this->doctrine->persist($personnel);
        }
        $personnel->setAgence($agence);
        $compte->setName($data['name']);
        $compte->setPhone($data['phone']);
        $this->doctrine->flush();
        $response=[
            'status'=>200,
            'message'=>"Successful request"
        ];
        $view = $this->view($response, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Post("/v1/agences", name="api_agences_post_ajax")
     * @param Request $request
     */
    public function postagences(Request $request)
    {
        $res = json_decode($request->getContent(), true);
        $data=$res['data'];
        if (!is_null($data['id'])){
            $agence=$this->agenceRepository->find($data['id']);
        }else{
            $agence=new Agence();
            $this->doctrine->persist($agence);
        }
        $agence->setName($data['name']);
        $agence->setPhone($data['phone']);
        $agence->setAddress($data['address']);
        $agence->setCity($data['city']);
        $this->doctrine->flush();
        $response=[
            'status'=>200,
            'message'=>"Successful request"
        ];
        $view = $this->view($response, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Post("/v1/customers", name="api_customers_ajax")
     * @param Request $request
     * @return Response
     */
    public function postcustomers(Request $request): Response
    {
        $res = json_decode($request->getContent(), true);
        $data=$res['data'];
        $iscreated=false;
        $agence=$this->agenceRepository->find($data['agence']);
        if (!is_null($data['id'])){
            $customer=$this->customerRepository->find($data['id']);
            $compte=$customer->getCompte();
        }else{
            $compte=new User();
            $compte->setEmail($data['email']);
            $compte->setUsername($data['email']);
            $encodedPassword = $this->passwordEncoder->hashPassword($compte, "teraqs12_}//reagart-(365");
            $compte->setRoles(['ROLE_CUSTOMER']);
            $compte->setPassword($encodedPassword);
            $this->doctrine->persist($compte);
            $customer=new Customer();
            $customer->setCompte($compte);
            $customer->setDatecreation(new \DateTime('now',New \DateTimeZone('Africa/Douala')));
            $this->doctrine->persist($customer);
            $iscreated=true;
        }
        $customer->setAgence($agence);
        $customer->setCity($data['city']);
        $customer->setAddress($data['address']);
        $compte->setName($data['name']);
        $compte->setPhone($data['phone']);
        $this->doctrine->flush();
        $response=[
            'status'=>200,
            'message'=>"Successful request",
            'customer'=>$customer,
        ];
        $view = $this->view([
            'id' => $customer->getId(),
            'name' => $customer->getCompte()->getName(),
            'phone' => $customer->getCompte()->getPhone(),
            'email' => $customer->getCompte()->getEmail(),
            'agence' => $customer->getAgence()->getName(),
            'address' => $customer->getAddress(),
            'city' => $customer->getCity()
        ], Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Post("/v1/cards/add", name="api_cards_ajax")
     * @param Request $request
     * @return Response
     */
    public function addCard(Request $request): Response
    {
        $res = json_decode($request->getContent(), true);
        $data=$res['data'];
        $customer=$this->customerRepository->find($data['customer']);
        $card=new Card();
        $card->setName($data['cardname']);
        $card->setNumerocard($data['cardid']);
        //$card->setAmount($data['amount']);
        $this->doctrine->persist($card);
        $cardcustomer=new CardCustomer();
        $cardcustomer->setCustomer($customer);
        $cardcustomer->setCard($card);
        $cardcustomer->setIsActive(false);
        $cardcustomer->setCreatedAt(new \DateTimeImmutable('now',New \DateTimeZone('Africa/Douala')));
        $cardcustomer->setPeriodto(new \DateTime('now',New \DateTimeZone('Africa/Douala')));
        $this->doctrine->persist($cardcustomer);
        $this->doctrine->flush();
        $response=[
            'status'=>200,
            'message'=>"Successful request",
        ];
        $view = $this->view($response, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
}
