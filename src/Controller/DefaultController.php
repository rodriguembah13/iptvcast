<?php

namespace App\Controller;

use App\Entity\Activation;
use App\Entity\Agence;
use App\Entity\Bouquet;
use App\Entity\Card;
use App\Entity\CardCustomer;
use App\Entity\CardPending;
use App\Entity\Customer;
use App\Entity\Personnel;
use App\Entity\RechargeWallet;
use App\Entity\Reclamation;
use App\Entity\Souscription;
use App\Entity\User;
use App\Repository\ActivationRepository;
use App\Repository\AgenceRepository;
use App\Repository\BouquetRepository;
use App\Repository\CardCustomerRepository;
use App\Repository\CardRepository;
use App\Repository\CustomerRepository;
use App\Repository\PersonnelRepository;
use App\Repository\RechargeWalletRepository;
use App\Repository\SouscriptionRepository;
use App\Service\EndpointService;
use App\Service\paiement\ClientPaymoo;
use App\Service\paiement\OmService;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTableFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $params;
    private $dataTableFactory;
    private $endpointService;
    private $bouquetRepository;
    private $customerRepository;
    private $souscriptionRepository;
    private $cardcustomerRepository;
    private $agenceRepository;
    private $activationRepository;
    private $personnelRepository;
    private $walletrechargeRepository;
    private $cardRepository;
    private $passwordEncoder;
    private $omService;
    private $logger;

    /**
     * @param CardRepository $cardRepository
     * @param RechargeWalletRepository $rechargeWalletRepository
     * @param CardCustomerRepository $cardCustomerRepository
     * @param SouscriptionRepository $souscriptionRepository
     * @param CustomerRepository $customerRepository
     * @param PersonnelRepository $personnelRepository
     * @param LoggerInterface $logger
     * @param UserPasswordHasherInterface $passwordEncoder
     * @param AgenceRepository $agenceRepository
     * @param BouquetRepository $bouquetRepository
     * @param EndpointService $endpointService
     * @param DataTableFactory $dataTableFactory
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ActivationRepository $activationRepository,CardRepository $cardRepository,RechargeWalletRepository $rechargeWalletRepository,
                                CardCustomerRepository $cardCustomerRepository,OmService $omService,
                                SouscriptionRepository $souscriptionRepository,
                                CustomerRepository $customerRepository,PersonnelRepository $personnelRepository,
                                LoggerInterface $logger, UserPasswordHasherInterface $passwordEncoder,
                                AgenceRepository $agenceRepository,
                                BouquetRepository $bouquetRepository, EndpointService $endpointService, DataTableFactory $dataTableFactory, ParameterBagInterface $parameterBag)
    {
        $this->params = $parameterBag;
        $this->dataTableFactory = $dataTableFactory;
        $this->endpointService = $endpointService;
        $this->bouquetRepository = $bouquetRepository;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->souscriptionRepository = $souscriptionRepository;
        $this->cardcustomerRepository = $cardCustomerRepository;
        $this->cardRepository = $cardRepository;
        $this->agenceRepository = $agenceRepository;
        $this->personnelRepository=$personnelRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->walletrechargeRepository=$rechargeWalletRepository;
        $this->omService=$omService;
        $this->activationRepository=$activationRepository;
    }

    /**
     * @Route("/", name="home")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('default/index.html.twig', [
            'league' => '61',
            'date' => date('Y-m-d'),
            'title' => "Dashboard"
        ]);
    }

    /**
     * @Route("/v1/error", name="erropage")
     * @param Request $request
     * @return Response
     */
    public function echecpage(Request $request): Response
    {
        return $this->render('default/error/500.html.twig', [
            'title' => "Eroor page"
        ]);
    }

    /**
     * @Route("/v1/cancelurl", name="cancelpage")
     * @param Request $request
     * @return Response
     */
    public function cancelpage(Request $request): Response
    {
        return $this->render('default/error/500.html.twig', [
            'title' => "Eroor page"
        ]);
    }

    /**
     * @Route("/v1/successurl", name="successpage")
     * @param Request $request
     * @return Response
     */
    public function successpage(Request $request): Response
    {
        return $this->render('default/error/200.html.twig', [
            'title' => "Success page"
        ]);
    }
    /**
     * @Route("/reclamations", name="reclamations")
     * @param Request $request
     * @return Response
     */
    public function reclamations(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('card', TextColumn::class, [
                'label' => 'Card',
            ])
            ->add('amount', TextColumn::class, [
                'label' => 'Amount',
            ])
            ->add('createdAt', DateTimeColumn::class, [
                'label' => 'Date creation',
                'format' => "Y-m-d h:i"
            ])
            ->add('reclamationdate', DateTimeColumn::class, [
                'label' => 'Date reclamation',
                'format' => "Y-m-d h:i"
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Reclamation::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('reclamation')
                        ->from(Reclamation::class, 'reclamation')
                        ->orderBy('reclamation.id','ASC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/reclamations.html.twig', [
            'datatable' => $table,
            'title' => "Reclamations"
        ]);
    }
    /**
     * @Route("/bouquetchanel", name="bouquetchanel")
     * @param Request $request
     * @return Response
     */
    public function bouquetchanel(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('description', TextColumn::class, [
                'label' => 'Name',
            ])
            ->add('price', TextColumn::class, [
                'label' => 'Amount',
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('id', TwigColumn::class, [
                'className' => 'buttons',
                'label' => 'action',
                'template' => 'default/buttonbar.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Bouquet::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('bouquet')
                        ->from(Bouquet::class, 'bouquet')
                        ->orderBy('bouquet.description','ASC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/bouquetchanel.html.twig', [
            'datatable' => $table,
            'title' => "Bouquets"
        ]);
    }

    /**
     * @Route("/cards", name="cards")
     * @param Request $request
     * @return Response
     */
    public function cards(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('customer', TextColumn::class, [
                'label' => 'Customer',
                'field' => 'customer.compte.name',
                'orderField'=>'customer.compte.name',
                'searchable'=>false
            ])
            ->add('numero', TextColumn::class, [
                'label' => 'CardID',
                'field' => 'card.numerocard',
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('id', TwigColumn::class, [
                'className' => 'buttons',
                'label' => 'action',
                'template' => 'default/buttons/card.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Bouquet::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('card', 'customer', 'card_customer')
                        ->from(CardCustomer::class, 'card_customer')
                        ->leftJoin('card_customer.card', 'card')
                        ->leftJoin('card_customer.customer', 'customer')
                        ->orderBy('card.numerocard','ASC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/cards.html.twig', [
            'datatable' => $table,
            'title' => "Cards"
        ]);
    }

    /**
     * @Route("/bouquetchanel/create", name="bouquetchanel_new")
     * @param Request $request
     * @return Response
     */
    public function bouquetchanel_new(Request $request): Response
    {
        $data = [];
        return $this->render('default/bouquetchanel_new.html.twig', [
            'data' => $data,
            'title' => "Add ouquets"
        ]);
    }

    /**
     * @Route("/bouquetchanel/edit/{id}", name="bouquetchanel_edit")
     * @param Bouquet $bouquet
     * @return Response
     */
    public function bouquetchanel_edit(Bouquet $bouquet, Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $entityManager = $this->getDoctrine()->getManager();
            $bouquet->setPrice($request->get('price'));
            $entityManager->flush();
            return $this->redirectToRoute('bouquetchanel');
        }

        return $this->render('default/bouquetchanel_edit.html.twig', [
            'data' => $data,
            'bouquet' => $bouquet,
            'title' => "Edit Bouquets",
            'chanels' => []
        ]);
    }

    /**
     * @Route("/souscriptions", name="souscriptions")
     */
    public function souscriptions(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('createdAt', DateTimeColumn::class, [
                'label' => 'Date ',
                'format' => "Y-m-d h:i:s"
            ])
            ->add('card', TextColumn::class, [
                'label' => 'N° card',
                'field' => 'card.numerocard'
            ])
            ->add('amount', TextColumn::class, [
                'label' => 'Montant',
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('bouquets', TwigColumn::class, [
                'label' => 'Bouquets',
                'template' => 'default/buttons/activation.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->add('status', TextColumn::class, [
                'className' => 'buttons',
                'label' => 'status',
                // 'template' => 'user/status.html.twig',
                'render' => function ($value, $context) {
                    if ($value == Activation::SUCCESS) {
                        return '<a class="btn btn-sm btn-success">' . $value . '</a>';
                    } elseif ($value == Souscription::PENDING) {
                        return '<a class="btn btn-sm btn-warning">' . $value . '</a>';
                    } else {
                        return '<a class="btn btn-sm btn-danger">' . $value . '</a>';
                    }
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Activation::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('souscription', 'card')
                        ->from(Activation::class, 'souscription')
                        ->andWhere('souscription.status = :satus')
                        ->setParameter('satus', Activation::SUCCESS)
                        ->join('souscription.card', 'card')
                        ->orderBy('souscription.id', 'DESC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/souscriptions.html.twig', [
            'datatable' => $table,
            'title' => "Activations"
        ]);
    }
    /**
     * @Route("/souscriptionsagent", name="souscriptionsagent")
     */
    public function souscriptionsagent(Request $request): Response
    {
        $user=$this->getUser();
        $personnel=$this->personnelRepository->findOneBy(['compte'=>$user]);
        $table = $this->dataTableFactory->create()
            ->add('createdAt', DateTimeColumn::class, [
                'label' => 'Date ',
                'format' => "Y-m-d h:i"
            ])
            ->add('card', TextColumn::class, [
                'label' => 'N° card',
                'field' => 'card.numerocard'
            ])
            ->add('amount', TextColumn::class, [
                'label' => 'Montant',
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('bouquets', TwigColumn::class, [
                'label' => 'Bouquets',
                'template' => 'default/buttons/activation.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->add('status', TextColumn::class, [
                'className' => 'buttons',
                'label' => 'status',
                // 'template' => 'user/status.html.twig',
                'render' => function ($value, $context) {
                    if ($value == Activation::SUCCESS) {
                        return '<a class="btn btn-sm btn-success">' . $value . '</a>';
                    } elseif ($value == Souscription::PENDING) {
                        return '<a class="btn btn-sm btn-warning">' . $value . '</a>';
                    } else {
                        return '<a class="btn btn-sm btn-danger">' . $value . '</a>';
                    }
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Activation::class,
                'query' => function (QueryBuilder $builder) use($personnel) {
                    $builder
                        ->select('souscription', 'card')
                        ->from(Activation::class, 'souscription')
                        ->andWhere('souscription.createdBy = :personnel')
                        ->setParameter('personnel', $personnel)
                        ->andWhere('souscription.status = :satus')
                        ->setParameter('satus', Activation::SUCCESS)
                        ->join('souscription.card', 'card')
                        ->orderBy('souscription.id', 'DESC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/souscriptions.html.twig', [
            'datatable' => $table,
            'title' => "Activations"
        ]);
    }
    /**
     * @Route("/souscriptionspending", name="souscriptionspending")
     */
    public function souscriptionspending(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('createdAt', DateTimeColumn::class, [
                'label' => 'Date ',
                'format' => "Y-m-d h:m"
            ])
            ->add('card', TextColumn::class, [
                'label' => 'N° card',
                'field' => 'card.numerocard'
            ])
            ->add('amount', TextColumn::class, [
                'label' => 'Montant',
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('bouquets', TwigColumn::class, [
                'label' => 'Bouquets',
                'template' => 'default/buttons/activation.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->add('status', TextColumn::class, [
                'className' => 'buttons',
                'label' => 'status',
                // 'template' => 'user/status.html.twig',
                'render' => function ($value, $context) {
                    if ($value == Activation::SUCCESS) {
                        return '<a class="btn btn-sm btn-success">' . $value . '</a>';
                    } elseif ($value == Souscription::PENDING) {
                        return '<a class="btn btn-sm btn-warning">' . $value . '</a>';
                    } else {
                        return '<a class="btn btn-sm btn-danger">' . $value . '</a>';
                    }
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Activation::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('souscription', 'card')
                        ->from(Activation::class, 'souscription')
                        ->andWhere('souscription.status <> :satus')
                        ->setParameter('satus', Activation::SUCCESS)
                        ->join('souscription.card', 'card')
                        ->orderBy('souscription.id', 'DESC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/souscriptions.html.twig', [
            'datatable' => $table,
            'title' => "Activations"
        ]);
    }
    /**
     * @Route("/etatsouscriptionagent", name="etatsouscriptionagent",options={"expose"=true})
     */
    public function etatsouscripagent(Request $request): Response
    {
        $user=$this->getUser();
        if (is_null($request->get('at'))){
            $at=date("Y-m-d");
            $to=date("Y-m-d");
        }else{
            $at=$request->get('at');
            $to=$request->get('to');
        }

        $personnel=$this->personnelRepository->findOneBy(['compte'=>$user]);
        $table = $this->dataTableFactory->create()
            ->add('createdAt', DateTimeColumn::class, [
                'label' => 'Date ',
                'format' => "Y-m-d h:m"
            ])
            ->add('card', TextColumn::class, [
                'label' => 'N° card',
                'field' => 'card.numerocard'
            ])
            ->add('amount', TextColumn::class, [
                'label' => 'Montant',
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('bouquets', TwigColumn::class, [
                'label' => 'Bouquets',
                'template' => 'default/buttons/activation.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->add('status', TextColumn::class, [
                'className' => 'buttons',
                'label' => 'status',
                // 'template' => 'user/status.html.twig',
                'render' => function ($value, $context) {
                    if ($value == Activation::SUCCESS) {
                        return '<a class="btn btn-sm btn-success">' . $value . '</a>';
                    } elseif ($value == Souscription::PENDING) {
                        return '<a class="btn btn-sm btn-warning">' . $value . '</a>';
                    } else {
                        return '<a class="btn btn-sm btn-danger">' . $value . '</a>';
                    }
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Activation::class,
                'query' => function (QueryBuilder $builder) use($personnel,$at,$to) {
                    $builder
                        ->select('souscription', 'card')
                        ->from(Activation::class, 'souscription')
                        ->andWhere('souscription.createdBy = :personnel')
                        ->setParameter('personnel', $personnel)
                        ->andWhere('souscription.status = :satus')
                        ->setParameter('satus', Activation::SUCCESS)
                        ->andWhere('souscription.createdAt >= :begin')
                        ->andWhere('souscription.createdAt < :end')
                        ->setParameter('begin',$at )
                        ->setParameter('end', $to)
                        ->join('souscription.card', 'card')
                        ->orderBy('souscription.id', 'DESC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/etatsouscriptionagent.html.twig', [
            'datatable' => $table,
            'title' => "Etats souscriptions",
            'begin'=>$at,
            'end'=>$to
        ]);
    }
    /**
     * @Route("/recapsoucripteur", name="recapsoucripteur",options={"expose"=true})
     */
    public function recapaSoucription(Request $request): Response
    {
        $user=$this->getUser();
        if (is_null($request->get('at'))){
            $at=date("Y-m-d");
            $to=date("Y-m-d");
            // $activations=$this->activationRepository->findByAllbydate($at,$to);
        }else{
            $at=date($request->get('at'));
            $to=date($request->get('to'));

        }
        $activations=$this->activationRepository->findByAllbydate($at,$to);
        $sum=0;
        foreach ($activations as $activation){
            $sum+=$activation->getAmount();
        }
        return $this->render('default/recapitilatif.html.twig', [
            //'datatable' => $table,
            'activactions' => $activations,
            'sum'=>$sum,
            'title' => "Recapitulatif"
        ]);
    }
    /**
     * @Route("/etatsouscription", name="etatsouscription",options={"expose"=true})
     */
    public function etatsouscripall(Request $request): Response
    {
        $user=$this->getUser();
       if (is_null($request->get('at'))){
            $at=date("Y-m-d");
            $to=date("Y-m-d");
          // $activations=$this->activationRepository->findByAllbydate($at,$to);
        }else{
            $at=date($request->get('at'));
            $to=date($request->get('to'));

        }
        $activations=$this->activationRepository->findByAllbydate($at,$to);
       $sum=0;
       foreach ($activations as $activation){
           $sum+=$activation->getAmount();
       }
               return $this->render('default/etatsouscription.html.twig', [
            //'datatable' => $table,
            'activactions' => $activations,
            'sum'=>$sum,
            'title' => "Etats souscriptions",
                   'begin'=>$at,
                   'end'=>$to
        ]);
    }
    /**
     * @Route("/etatsouscriptionbyagent", name="etatsouscriptionbyagent",options={"expose"=true})
     */
    public function etatsouscripbyagent(Request $request): Response
    {
        if (is_null($request->get('at'))){
            $at=date("Y-m-d");
            $to=date("Y-m-d");
            // $activations=$this->activationRepository->findByAllbydate($at,$to);
        }else{
            $at=date($request->get('at'));
            $to=date($request->get('to'));
        }
        if (is_null($request->get('agent'))){
            $activations=[];
            $wallets=[];
            $agent=null;
        }else{

            $agent=$this->personnelRepository->find($request->get('agent'));
            $wallets=$this->walletrechargeRepository->findByAllbydateAndAgent($at,$to,$agent);
            $activations=$this->activationRepository->findByAllbydateAndAgent($at,$to,$agent);
        }

        $sum=0;
        $sumwallet=0;
        foreach ($activations as $activation){
            $sum+=$activation->getAmount();
        }
        foreach ($wallets as $wallet){
            $sumwallet+=$wallet->getAmount();
        }
        return $this->render('default/etatsouscriptionbyagent.html.twig', [
            'activactions' => $activations,
            'sum'=>$sum,
            'recharge'=>$sumwallet,
            'agents'=>$this->personnelRepository->findAll(),
            'title' => "Etats souscriptions par agent",
            'begin'=>$at,
            'end'=>$to,
            "agent"=>$agent
        ]);
    }
    /**
     * @Route("/customers", name="customers")
     */
    public function customers(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('customer', TextColumn::class, [
                'label' => 'Name',
                'field' => 'compte.name'
            ])
            ->add('phone', TextColumn::class, [
                'label' => 'Phone',
                'field' => 'compte.phone'
            ])
            ->add('id', TwigColumn::class, [
                'className' => 'buttons',
                'label' => 'action',
                'template' => 'default/buttons/customer.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Souscription::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('compte', 'customer')
                        ->from(Customer::class, 'customer')
                        ->join('customer.compte', 'compte')
                        ->orderBy('compte.name','ASC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/customers.html.twig', [
            'datatable' => $table,
            'agences'=>$this->agenceRepository->findAll(),
            'title' => "Customers"
        ]);
    }

    /**
     * @Route("/agents", name="agents")
     */
    public function agents(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('agent', TextColumn::class, [
                'label' => 'Name',
                'field' => 'compte.name'
            ])
            ->add('email', TextColumn::class, [
                'label' => 'Email',
                'field' => 'compte.email'
            ])
            ->add('phone', TextColumn::class, [
                'label' => 'Phone',
                'field' => 'compte.phone'
            ])
            ->add('id', TwigColumn::class, [
                'className' => 'buttons',
                'label' => 'action',
                'template' => 'default/buttons/agent.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Souscription::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('compte', 'customer')
                        ->from(Personnel::class, 'customer')
                        ->join('customer.compte', 'compte')
                        ->orderBy('compte.name','ASC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/agents.html.twig', [
            'datatable' => $table,
            'agences' => $this->agenceRepository->findAll(),
            'title' => "Agents"
        ]);
    }

    /**
     * @Route("/agences", name="agences")
     */
    public function agences(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('agence', TextColumn::class, [
                'label' => 'Name',
                'field' => 'agence.name'
            ])
            ->add('phone', TextColumn::class, [
                'label' => 'Phone',
            ])
            ->add('address', TextColumn::class, [
                'label' => 'Address',
            ])
            ->add('city', TextColumn::class, [
                'label' => 'City',
            ])
            ->add('id', TwigColumn::class, [
                'className' => 'buttons',
                'label' => 'action',
                'template' => 'default/buttons/agence.html.twig',
                'render' => function ($value, $context) {
                    return $value;
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Agence::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('agence')
                        ->from(Agence::class, 'agence')
                    ->orderBy('agence.name','ASC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/agences.html.twig', [
            'datatable' => $table,
            'title' => "Agences"
        ]);
    }

    /**
     * @Route("/customer/edit/{id}", name="customer_edit")
     * @param Customer $bouquet
     * @return Response
     */
    public function customer_edit(Customer $customer, Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $entityManager = $this->getDoctrine()->getManager();
            $customer->setCity($request->get('city'));
            $customer->setAddress($request->get('city'));
            $customer->setCity($request->get('city'));
            $customer->setCity($request->get('city'));
            $entityManager->flush();
            return $this->redirectToRoute('customers');
        }

        return $this->render('default/edit/editcustomer.html.twig', [
            'data' => $data,
            'customer' => $customer,
            'title' => "Edit Customers",
            'chanels' => []
        ]);
    }

    /**
     * @Route("/agences/edit/{id}", name="agence_edit")
     * @param Customer $bouquet
     * @return Response
     */
    public function agence_edit(Agence $agence, Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $entityManager = $this->getDoctrine()->getManager();
            $agence->setCity($request->get('city'));
            $agence->setAddress($request->get('address'));
            $agence->setCity($request->get('city'));
            $entityManager->flush();
            return $this->redirectToRoute('agences');
        }

        return $this->render('default/edit/editagence.html.twig', [
            'agence' => $agence,
            'title' => "Edit Agences",

        ]);
    }

    /**
     * @Route("/agents/edit/{id}", name="agent_edit")
     * @param Customer $bouquet
     * @return Response
     */
    public function agent_edit(Personnel $personnel, Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $entityManager = $this->getDoctrine()->getManager();
            $compte = $personnel->getCompte();
            $compte->setName($request->get('name'));
            $compte->setPhone($request->get('phone'));

            $compte->setEmail($request->get('email'));
            $entityManager->flush();
            return $this->redirectToRoute('agents');
        }

        return $this->render('default/edit/editagent.html.twig', [
            'agent' => $personnel,
            'title' => "Edit Agents",

        ]);
    }

    /**
     * @Route("/agents/add", name="agent_add")
     * @param Customer $bouquet
     * @return Response
     */
    public function agent_add(Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $entityManager = $this->getDoctrine()->getManager();
            $agence = $this->agenceRepository->find($request->get('agence'));
            $personnel = new Personnel();
            $compte = new User();
            $compte->setName($request->get('name'));
            $compte->setPhone($request->get('phone'));
            $compte->setEmail($request->get('email'));
            $compte->setUsername($request->get('email'));
            $encodedPassword = $this->passwordEncoder->hashPassword($compte, "cast12345");
            $compte->setRoles(['ROLE_AGENT']);
            $compte->setPassword($encodedPassword);
            $entityManager->persist($compte);
            $personnel->setCompte($compte);
            $personnel->setSolde(0.0);
            $personnel->setAgence($agence);
            $personnel->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Africa/Douala')));
            $entityManager->persist($personnel);
            $entityManager->flush();
            return $this->redirectToRoute('agents');
        }
        return $this->redirectToRoute('agents');
    }

    /**
     * @Route("/agences/add", name="agence_add")
     * @param Request $request
     * @return Response
     */
    public function agence_add(Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $entityManager = $this->getDoctrine()->getManager();
            $agence = new Agence();
            $agence->setName($request->get('name'));
            $agence->setPhone($request->get('phone'));
            $agence->setAddress($request->get('address'));
            $agence->setCity($request->get('city'));
            $entityManager->persist($agence);
            $entityManager->flush();
            return $this->redirectToRoute('agences');
        }
        return $this->redirectToRoute('agences');
    }

    /**
     * @Route("/customer/add", name="customer_add")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function customer_add(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        if ($request->getMethod() == "POST") {
            $agence=$this->agenceRepository->find($request->get('agence'));
            $compte=new User();
            $compte->setEmail($request->get('email'));
            $compte->setName($request->get('name'));
            $compte->setPhone($request->get('phone'));
            $compte->setUsername($request->get('email'));
            $encodedPassword = $this->passwordEncoder->hashPassword($compte, "teraqs12_}//reagart-(365");
            $compte->setRoles(['ROLE_CUSTOMER']);
            $compte->setPassword($encodedPassword);
            $entityManager->persist($compte);
            $customer=new Customer();
            $customer->setAgence($agence);
            $customer->setCompte($compte);
            $customer->setCity($request->get('city'));
            $customer->setAddress($request->get('address'));
            $customer->setDatecreation(new \DateTime('now',New \DateTimeZone('Africa/Douala')));
            $entityManager->persist($customer);
            $entityManager->flush();
        }
        return $this->redirectToRoute('customers');
    }

    /**
     * @Route("/customers/addcard/{id}", name="customer_add_card")
     * @param Customer $customer
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function customer_add_card(Customer $customer, Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $entityManager = $this->getDoctrine()->getManager();
            $card = new Card();
            $card->setName($request->get('cardname'));
            $card->setNumerocard($request->get('cardnumber'));
            //$card->setAmount($data['amount']);
            $entityManager->persist($card);
            $cardcustomer = new CardCustomer();
            $cardcustomer->setCustomer($customer);
            $cardcustomer->setCard($card);
            $cardcustomer->setIsActive(false);
            $cardcustomer->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Africa/Douala')));
            $cardcustomer->setPeriodto(new \DateTime('now', new \DateTimeZone('Africa/Douala')));
            $entityManager->persist($cardcustomer);
            $entityManager->flush();
            if ($request->get('saveandactivate')) {
                $url = $this->generateUrl('customer_activate_card', ['id' => $cardcustomer->getCustomer()->getId()]);
                return $this->redirect($url);
            }
            return $this->redirectToRoute('customers');
        }

        return $this->render('default/edit/addcardcustomer.html.twig', [
            'customer' => $customer,
            'cards' => $this->cardcustomerRepository->findBy(['customer' => $customer]),
            'title' => "Add card",

        ]);
    }

    /**
     * @Route("/cards/activate", name="activate_card")
     * @return Response
     */
    public function activatecard(Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $produts = $request->get('bouquets');
            $this->logger->info(json_encode($produts));
            $cardcustomer = $this->cardcustomerRepository->find($request->get('cardcustomer'));
            $reference = "";
            $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
            for ($i = 1; $i <= 12; ++$i) {
                $reference .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
            }
            if ($request->get('method')=="point_recharge"){
                $personnel=$this->personnelRepository->findOneBy(['compte'=>$this->getUser()]);
                if (is_null($personnel)){
                    $this->addFlash("error","Vous avez pas suffissament des droits pour effectuer ce paiement");
                }else{
                    $entityManager = $this->getDoctrine()->getManager();
                    if($personnel->getSolde()<$request->get('amount')){
                        $this->addFlash("error","Votre compte ne peut pas effectuer cette transaction: montant insuffissant");
                        return $this->redirectToRoute('erropage');
                    }
                    $personnel->setSolde($personnel->getSolde()-$request->get('amount'));
                    $month=$request->get('periode');
                    $this->createActivateRecharge($cardcustomer, $reference, $request->get('amount'), $produts,$month);
                    $entityManager->flush();
                    return $this->redirectToRoute('successpage');
                }
            }elseif ($request->get('method')=="om"){
                $month=$request->get('periode');
                $id = "OM_0".rand(100000, 900000)."_00".rand(10000, 90000);
                $notify_url = $this->generateUrl('omnotifyurlajax', ['souscription' => $id]);
                $notify_url = $this->params->get('DOMAINSITE') . $notify_url;
                $dataOM = [
                    'subscriberMsisdn' => $request->get('phone'),
                    'amount' => "1",
                    'description' => "Payement bouquet",
                    'notifUrl' => $notify_url,
                    'orderId'=>$id
                ];
                $response =$this->omService->pay($dataOM);
                $this->logger->info(json_encode($response));
                if ($response['data']['inittxnstatus']=="200"){
                    //$this->createActivate($cardcustomer, $reference, $request->get('amount'), $produts,$month);
                    return $this->redirectToRoute('successpage');
                }else{
                    return $this->redirectToRoute('erropage');
                }
            }else{
                if (strtolower($request->get('method')) == 'mobil_money') {
                    $currency = "XAF";
                } else {
                    $currency = "USD";
                }
                $data = [
                    'amount' => $request->get('amount'),
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
                    'logo' => 'http://195.24.222.202:9090/assets/img/logo_white.png',
                    'environement' => 'test'
                ];
                $client = new ClientPaymoo();
                $response = $client->postfinal("payment_url", $data);
                $this->logger->info($response['response']);
                $this->logger->info($request->get('amount'));
                if ($response['response'] == "success") {
                    $month=$request->get('periode');
                    $this->createActivate($cardcustomer, $reference, $request->get('amount'), $produts,$month);
                    $url = $response["payment_url"];
                    $this->logger->info($url);
                    $link_array = explode('/', $url);
                    return $this->redirect($url);
                } else {
                    return $this->redirectToRoute('erropage');
                }
            }

        }

        return $this->render('default/edit/activatecard.html.twig', [
            'title' => "Activate card",
            'customers' => $this->customerRepository->findAllOrder(),
            'bouquets' => $this->bouquetRepository->findAll()
        ]);
    }

    /**
     * @Route("/cards/addactivatefromcard/{id}", name="addactivatefromcard")
     * @return Response
     */
    public function addactivatefromcard(CardCustomer $cardcustomer, Request $request): Response
    {
        if ($request->getMethod() == "POST") {
            $produts = $request->get('bouquets');
            $this->logger->info(json_encode($produts));
            $reference = "";
            $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
            for ($i = 1; $i <= 12; ++$i) {
                $reference .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
            }
            if ($request->get('method')=="point_recharge"){
                $personnel=$this->personnelRepository->findOneBy(['compte'=>$this->getUser()]);
                if (is_null($personnel)){
                    $this->addFlash("error","Vous avez pas suffissament des droits pour effectuer ce paiement");
                }else{
                    $entityManager = $this->getDoctrine()->getManager();
                    if($personnel->getSolde()<$request->get('amount')){
                        $this->addFlash("error","Votre compte ne peut pas effectuer cette transaction: montant insuffissant");
                        return $this->redirectToRoute('erropage');
                    }
                    $personnel->setSolde($personnel->getSolde()-$request->get('amount'));
                    $month=$request->get('periode');
                    $this->createActivateRecharge($cardcustomer, $reference, $request->get('amount'), $produts,$month);
                    $entityManager->flush();
                    return $this->redirectToRoute('successpage');
                }
            }elseif ($request->get('method')=="om"){
                $month=$request->get('periode');
                $id = "OM_0".rand(100000, 900000)."_00".rand(10000, 90000);
                $notify_url = $this->generateUrl('omnotifyurlajax', ['souscription' => $id]);
                $notify_url = $this->params->get('DOMAINSITE') . $notify_url;
                $dataOM = [
                    'subscriberMsisdn' => $request->get('phone'),
                   // 'amount' => $request->get('amount'),
                    'amount' => "1",
                    'description' => "Payement bouquet",
                    'notifUrl' => $notify_url,
                    'orderId'=>$id
                ];
                $response =$this->omService->pay($dataOM);
                $this->logger->info(json_encode($response));
                if ($response['data']['inittxnstatus']=="200"){
                    //$this->createActivate($cardcustomer, $reference, $request->get('amount'), $produts,$month);
                    return $this->redirectToRoute('successpage');
                }else{
                    return $this->redirectToRoute('erropage');
                }
            }else{

                if (strtolower($request->get('method')) == 'mobil_money') {
                    $currency = "XAF";
                } else {
                    $currency = "USD";
                }
                $data = [
                    'amount' => $request->get('amount'),
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
                    'logo' => 'http://195.24.222.202:9090/assets/img/logo_white.png',
                    'environement' => 'test'
                ];
                $client = new ClientPaymoo();
                $response = $client->postfinal("payment_url", $data);
                $this->logger->info(json_encode($response));
                if ($response['response'] == "success") {
                    $month=$request->get('periode');
                    $this->createActivate($cardcustomer, $reference, $request->get('amount'), $produts,$month);
                    $url = $response["payment_url"];
                    $this->logger->info($url);
                    $link_array = explode('/', $url);
                    return $this->redirect($url);
                } else {
                    return $this->redirectToRoute('erropage');
                }
            }


        }
        return $this->render('default/edit/addactivatefromcard.html.twig', [
            'title' => "Activate card",
            'cardcustomer' => $cardcustomer,
            'bouquets' => $this->bouquetRepository->findAll()
        ]);
    }



    /**
     * @Route("/customers/activatecard/{id}", name="customer_activate_card")
     * @param Customer $customer
     * @param Request $request
     * @return Response
     */
    public function customer_activate_card(Customer $customer, Request $request): Response
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $produts = $request->get('bouquets');

            $this->logger->info(json_encode($produts));
            $cardcustomer = $this->cardcustomerRepository->find($request->get('cardcustomer'));
            $reference = "";
            $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
            for ($i = 1; $i <= 12; ++$i) {
                $reference .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
            }
            if ($request->get('method')=="point_recharge"){
                $personnel=$this->personnelRepository->findOneBy(['compte'=>$this->getUser()]);
                if (is_null($personnel)){
                    $this->addFlash("error","Vous avez pas suffissament des droits pour effectuer ce paiement");
                }else{
                    $entityManager = $this->getDoctrine()->getManager();
                    if($personnel->getSolde()<$request->get('amount')){
                        $this->addFlash("error","Votre compte ne peut pas effectuer cette transaction: montant insuffissant");
                        return $this->redirectToRoute('erropage');
                    }
                    $personnel->setSolde($personnel->getSolde()-$request->get('amount'));
                    $month=$request->get('periode');
                    $this->createActivateRecharge($cardcustomer, $reference, $request->get('amount'), $produts,$month);
                    $entityManager->flush();
                    return $this->redirectToRoute('successpage');
                }
            }elseif ($request->get('method')=="om"){
                $month=$request->get('periode');
                $id = "OM_0".rand(100000, 900000)."_00".rand(10000, 90000);
                $notify_url = $this->generateUrl('omnotifyurlajax', ['souscription' => $id]);
                $notify_url = $this->params->get('DOMAINSITE') . $notify_url;
                $dataOM = [
                    'subscriberMsisdn' => $request->get('phone'),
                    'amount' => "1",
                    'description' => "Payement bouquet",
                    'notifUrl' => $notify_url,
                    'orderId'=>$id
                ];
                $response =$this->omService->pay($dataOM);
                if ($response['data']['inittxnstatus']=="200"){
                    //$this->createActivate($cardcustomer, $reference, $request->get('amount'), $produts,$month);
                    return $this->redirectToRoute('successpage');
                }else{
                    return $this->redirectToRoute('erropage');
                }
            } else{

                if (strtolower($request->get('method')) == 'mobil_money') {
                    $currency = "XAF";
                } else {
                    $currency = "USD";
                }
                $data = [
                    'amount' => $request->get('amount'),
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
                    'logo' => 'http://195.24.222.202:9090/assets/img/logo_white.png',
                    'environement' => 'test'
                ];
                $client = new ClientPaymoo();
                $response = $client->postfinal("payment_url", $data);
                $this->logger->info($response['response']);
                if ($response['response'] == "success") {
                    $this->createActivate($cardcustomer, $reference, $request->get('amount'), $produts);
                    $url = $response["payment_url"];
                    $this->logger->info($url);
                    $link_array = explode('/', $url);
                    return $this->redirect($url);
                } else {
                    return $this->redirectToRoute('erropage');
                }
            }

        }

        return $this->render('default/edit/activatecardcustomer.html.twig', [
            'customer' => $customer,
            'cards' => $this->cardcustomerRepository->findBy(['customer'=>$customer]),
            'bouquets' => $this->bouquetRepository->findAll(),
            'title' => "Activate card",

        ]);
    }

    function createActivate(CardCustomer $card, $reference, $amount, $produts,$month)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $personnel=$this->personnelRepository->findOneBy(['compte'=>$this->getUser()]);
        if ($amount>0 && $month>0){
            $actiavtion = new Activation();
            $actiavtion->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Africa/Douala')));
            $actiavtion->setCard($card->getCard());
            $actiavtion->setAmount($amount);
            $actiavtion->setMonthto($month);
            $actiavtion->setReference($reference);
            $actiavtion->setStatus(Activation::PENDING);
            $actiavtion->setCreatedBy($personnel);

            $actiavtion->setBouquets($produts);
            $entityManager->persist($actiavtion);
            for ($i = 0; $i < sizeof($produts); $i++) {
                $cardpending = new CardPending();
                $cardpending->setCardid($card->getCard()->getNumerocard());
                $cardpending->setIsdelete(true);
                $cardpending->setSendornot(1);
                $cardpending->setCardstatus(1);
                $cardpending->setBouquet($produts[$i]);
                $cardpending->setStatus(CardPending::PENDING);
                $cardpending->setActivation($actiavtion->getId());
                $date_line = is_null($card->getPeriodto())? new \DateTime('now', new \DateTimeZone('Africa/Douala')):
                    new \DateTime($card->getPeriodto()->format('Y-m-d h:m'), new \DateTimeZone('Africa/Douala'));
                $mod = "+".intval($month)." month";
                $date_line->modify($mod);
                $cardpending->setExpiredtime($date_line);
                $entityManager->persist($cardpending);
            }

            $entityManager->flush();
        }

    }
    function createActivateRecharge(CardCustomer $card, $reference, $amount, $produts,$month)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $personnel=$this->personnelRepository->findOneBy(['compte'=>$this->getUser()]);
        if ($amount>0 && $month>0){
            $actiavtion = new Activation();
            $actiavtion->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Africa/Douala')));
            $actiavtion->setCard($card->getCard());
            $actiavtion->setAmount($amount);
            $actiavtion->setMonthto($month);
            $actiavtion->setReference($reference);
            $actiavtion->setStatus(Activation::SUCCESS);
            $actiavtion->setBouquets($produts);
            $actiavtion->setCreatedBy($personnel);
            $entityManager->persist($actiavtion);
            for ($i = 0; $i < sizeof($produts); $i++) {
                $cardpending = new CardPending();
                $cardpending->setCardid($card->getCard()->getNumerocard());
                $cardpending->setIsdelete(true);
                $cardpending->setSendornot(1);
                $cardpending->setCardstatus(1);
                $cardpending->setBouquet($produts[$i]);
                $cardpending->setStatus(CardPending::SUCCESS);
                $cardpending->setActivation($actiavtion->getId());
                $date_line = is_null($card->getPeriodto())? new \DateTime('now', new \DateTimeZone('Africa/Douala')):
                    new \DateTime($card->getPeriodto()->format('Y-m-d h:m'), new \DateTimeZone('Africa/Douala'));
                $mod = "+".intval($month)." month";
                $date_line->modify($mod);
                $cardpending->setExpiredtime($date_line);
                $entityManager->persist($cardpending);
            }

            $entityManager->flush();
        }

    }

    /**
     * @Route("/getpricebouquet/ajax", name="getpricebouquet_ajax", methods={"GET"})
     */
    public function getpricebouquetAjax(Request $request): JsonResponse
    {
        $bqts = $request->get('bouquets');
        $amount = 0.0;
        for ($i = 0; $i < count($bqts); $i++) {
            $bq = $this->bouquetRepository->findOneBy(['numero' => $bqts[$i]]);
            $amount += $bq->getPrice();
        }
        $data = [
            'amount' => $amount,
        ];

        return new JsonResponse($data, 200);
    }
    /**
     * @Route("/getpricebouquetqte/ajax", name="getpricebouquetqte_ajax", methods={"GET"})
     */
    public function getpricebouquetByQteAjax(Request $request): JsonResponse
    {
        $bqts = $request->get('bouquets');
        $qte=$request->get('periode');
        $amount = 0.0;
        for ($i = 0; $i < count($bqts); $i++) {
            $bq = $this->bouquetRepository->findOneBy(['numero' => $bqts[$i]]);
            $amount += $bq->getPrice();
        }
        $data = [
            'amount' => $amount*$qte,
        ];

        return new JsonResponse($data, 200);
    }
    /**
     * @Route("/getcardcustomer/ajax", name="getcardcustomer_ajax", methods={"GET"})
     */
    public function getcardcustomerAjax(Request $request): JsonResponse
    {
        $customer = $this->customerRepository->find($request->get('customer'));
        $cardcustomers = $this->cardcustomerRepository->findBy(['customer' => $customer]);
        $data = [];
        foreach ($cardcustomers as $cardCustomer) {
            $data[] = [
                'id' => $cardCustomer->getId(),
                'numero' => $cardCustomer->getCard()->getNumerocard(),
                'cardid' => $cardCustomer->getCard()->getId()
            ];
        }
        return new JsonResponse($data, 200);
    }
    /**
     * @Route("/deleteagence/ajax", name="deleteagence_ajax", methods={"GET"})
     */
    public function deleteAgenceAjax(Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $agence = $this->agenceRepository->find($request->get('id'));
        $this->agenceRepository->remove($agence,false);
        $entityManager->remove($agence);
        $entityManager->flush();

        return new JsonResponse([], 200);
    }
    /**
     * @Route("/deletecustomer/ajax", name="deletecustomer_ajax", methods={"GET"})
     */
    public function deleteCustomerAjax(Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $customer = $this->customerRepository->find($request->get('id'));
        $carcustomer=$this->cardcustomerRepository->findOneBy(['customer'=>$customer]);
        if (!is_null($carcustomer)){
            $entityManager->remove($carcustomer->getCard());
            $entityManager->remove($carcustomer);
        }

        $entityManager->remove($customer->getCompte());
        $entityManager->remove($customer);
        $entityManager->flush();

        return new JsonResponse([], 200);
    }
    /**
     * @Route("/removecard/ajax", name="removecard_ajax", methods={"GET"})
     */
    public function removecardAjax(Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $carcustomer=$this->cardcustomerRepository->find($request->get('id'));
        if (!is_null($carcustomer)){
            $entityManager->remove($carcustomer->getCard());
            $entityManager->remove($carcustomer);
        }
        $entityManager->flush();

        return new JsonResponse([], 200);
    }
    /**
     * @Route("/import/customer", name="customer_import_xls", methods={"GET","POST"})
     *
     * @throws Exception
     */
    public function importCustomer(Request $request): Response
    {
        $customers=[];
        $var=[];
        if ($request->getMethod() == 'POST') {

            $entityManager = $this->getDoctrine()->getManager();
            $uploadFilename = $request->files->get('file');
            if (empty($uploadFilename)) {
                return new Response('nafile', Response::HTTP_INTERNAL_SERVER_ERROR, ['']);
            }
            if ($uploadFilename) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/xls/';
                if ('xls' == $uploadFilename->guessExtension()) {
                    $inputFileType = 'Xls';
                } elseif ('csv' == $uploadFilename->guessExtension()) {
                    $inputFileType = 'Csv';
                } else {
                    return new Response('Bad response', Response::HTTP_BAD_REQUEST, ['']);
                }

                $reader = IOFactory::createReader($inputFileType);
                $spreadsheet = $reader->load($uploadFilename);
                $loadedSheetNames = $spreadsheet->getActiveSheet()->toArray();
                foreach ($loadedSheetNames as $sheetIndex => $loadedSheetNamess) {
                    $reference = "";
                    $allowed_characters = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0];
                    for ($i = 1; $i <= 12; ++$i) {
                        $reference .= $allowed_characters[rand(0, count($allowed_characters) - 1)];
                    }
                    if ($sheetIndex >= 1) {
                        $ga_name="tous les agences";
                       $agence=$this->agenceRepository->findOneBy(['name'=>$ga_name]);
                       /* if (is_null($agence)){
                            $agence=new Agence();
                            $agence->setName($loadedSheetNamess[2]);
                            $entityManager->persist($agence);
                        }*/
                        if (!empty($loadedSheetNamess[0])){
                            $customer = new Customer();
                            $compte = new User();
                            $compte->setName($loadedSheetNamess[0]);
                            $compte->setEmail($loadedSheetNamess[1].$reference. '@cast.cm');
                            $compte->setUsername($loadedSheetNamess[1].$reference . '@cast.cm');
                            $compte->setPhone($loadedSheetNamess[2]);
                            $compte->setPassword('1234455');
                            $entityManager->persist($compte);
                            $customer->setCompte($compte);
                             $customer->setAgence($agence);
                            $card = new Card();
                            $card->setName('gospel');
                            if (strlen($loadedSheetNamess[1])>8){
                               $num= substr("".$loadedSheetNamess[1],-8);
                                $card->setNumerocard($num);
                            }else{
                                $card->setNumerocard($loadedSheetNamess[1]);
                            }
                            $card->setCreated(new DateTime('now'));
                            $entityManager->persist($card);
                            $entityManager->persist($customer);
                            $cardcustomer = new CardCustomer();
                            $cardcustomer->setCustomer($customer);
                            $cardcustomer->setCard($card);
                            $cardcustomer->setIsActive(true);
                            $entityManager->persist($cardcustomer);
                            $customers[]=$cardcustomer;
                            $var[]=$loadedSheetNamess[1];
                        }

                    }
                }
                $entityManager->flush();
               // return $this->redirectToRoute('customer_import_xls');
            }
        }
       // dump($var);

        return $this->render('default/edit/importcustomer.html.twig', [
            'title' => "Import customer",
            'customers'=>$customers
        ]);
    }
    /**
     * @Route("/agent/addwallet/{id}", name="addwallet_card")
     * @param Personnel $personnel
     * @param Request $request
     * @return Response
     */
    public function wallet_card(Personnel $personnel, Request $request): Response
    {
        if ($request->getMethod()=="POST"){
            $entityManager = $this->getDoctrine()->getManager();
            $wallet=new RechargeWallet();
            $wallet->setAmount($request->get('amount'));
            $wallet->setPersonnel($personnel);
            $personnel->setSolde($personnel->getSolde()+$request->get('amount'));
            $wallet->setCreatedAt(new \DateTimeImmutable('now',new DateTimeZone('Africa/Douala')));
            $entityManager->persist($wallet);
            $entityManager->flush();
        }
        $sum=0.0;
        $wallets=$this->walletrechargeRepository->findBy(['personnel'=>$personnel]);
        foreach ($wallets as $wallet){
            $sum+=$wallet->getAmount();
        }
        return $this->render('default/edit/walletcard.html.twig', [
            'title' => "Wallet agent",
            'personnel' => $personnel,
            'recharges' => $wallets,
            'total' => $sum
        ]);
    }
    /**
     * @Route("/reclamation/card/", name="reclamation_card")
     * @param Personnel $personnel
     * @param Request $request
     * @return Response
     */
    public function reclamation_card(Request $request): Response
    {
        $activactions=[];
        if ($request->getMethod()=="POST"){
            $card = $this->cardRepository->findOneBy(['numerocard'=>$request->get('card')]);
            $bouquet = $request->get('bouquet');
            $date = $request->get('datecreation');
            $activactions=$this->activationRepository->findCardAndDate($card,$date);
       // $activactions[]=$activaction;
        }
        return $this->render('default/edit/reclamation_card.html.twig', [
            'title' => "Wallet agent",
            'activations'=>$activactions
        ]);
    }
    /**
     * @Route("/canalfacturation/", name="canalfacturation")
     * @param Request $request
     * @return Response
     */
    public function canal_facture(Request $request): Response
    {
        $activactions=[];
        $sum=0.0;
        $canals=explode(",",$this->getParameter("CANALNUMBERS"));
        $activs=[];
        $end="";
        $begin="";
        if ($request->getMethod()=="POST"){
            $end = $request->get('end');
            $begin = $request->get('begin');
            $activactions=$this->activationRepository->findByAllbydate($begin,$end);
            for ($i=0;$i<sizeof($canals);$i++){
                $p=$canals[$i];
                $arrs=array_filter($activactions,function ($item)use ($p){
                    if (in_array($p,$item->getBouquets())){
                        return true;
                    }
                    return false;
                });
                foreach ($arrs as $activation){
                    $sum+=$this->getParameter("CANALPRICE");
                    array_push($activs,$activation);
                }
                //$activs[]=$arrs;
               // array_merge($activs,$arrs);

            }
        }
        return $this->render('default/canalfacturation.html.twig', [
            'title' => "Facturation canal+",
            'activactions'=>$activs,
            'sum'=>$sum,
            'begin'=>$begin,
            'end'=>$end,
            'pricecanal'=>$this->getParameter("CANALPRICE")
        ]);
    }
    /**
     * @Route("/export/souscription", name="souscription_export_xls", methods={"GET","POST"},options={"expose"=true})
     */
    public function export(Request $request): Response
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        if (is_null($request->get('at'))){
            $at=date("Y-m-d");
            $to=date("Y-m-d");
        }else{
            $at=date($request->get('at'));
            $to=date($request->get('to'));

        }
        $activations=$this->activationRepository->findByAllbydate($at,$to);
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Creativsoft-e')
            ->setLastModifiedBy('Rodrigue')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', strtoupper('Date'))
            ->setCellValue('B1', strtoupper('N° card'))
            ->setCellValue('C1', strtoupper('Montant'))
            ->setCellValue('D1', strtoupper('bouquets'))
            ->setCellValue('E1', strtoupper('agent'));
        $i = 2;
        foreach ($activations as $student) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $student->getCreatedAt()->format("Y-m-d h:i"))
                ->setCellValue('B' . $i, $student->getCard()->getNumerocard())
                ->setCellValue('C' . $i, $student->getAmount())
                ->setCellValue('D' . $i,implode(",",$student->getBouquets()))
                ->setCellValue('E' . $i, is_null($student->getCreatedBy())?"": $student->getCreatedBy()->getCompte()->getName());
            ++$i;
        }
        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Activation'.$at.''.$to);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="activation'.$at.' au'.$to.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    }
    /**
     * @Route("/export/souscriptionagent", name="souscription_agent_export_xls", methods={"GET","POST"},options={"expose"=true})
     */
    public function exportByAgent(Request $request): Response
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        if (is_null($request->get('at'))){
            $at=date("Y-m-d");
            $to=date("Y-m-d");
        }else{
            $at=date($request->get('at'));
            $to=date($request->get('to'));
        }
        if (is_null($request->get('agent'))){
            $activations=[];
        }else{
            $agent=$this->personnelRepository->find($request->get('agent'));
            $activations=$this->activationRepository->findByAllbydateAndAgent($at,$to,$agent);
        }
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Creativsoft-e')
            ->setLastModifiedBy('Rodrigue')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');

        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', strtoupper('Date'))
            ->setCellValue('B1', strtoupper('N° card'))
            ->setCellValue('C1', strtoupper('Montant'))
            ->setCellValue('D1', strtoupper('bouquets'))
            ->setCellValue('E1', strtoupper('agent'));
        $i = 2;
        foreach ($activations as $student) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $student->getCreatedAt()->format("Y-m-d h:i"))
                ->setCellValue('B' . $i, $student->getCard()->getNumerocard())
                ->setCellValue('C' . $i, $student->getAmount())
                ->setCellValue('D' . $i, implode(",",$student->getBouquets()))
                ->setCellValue('E' . $i, is_null($student->getCreatedBy())?"": $student->getCreatedBy()->getCompte()->getName());
            ++$i;
        }
        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Activation'.$at.''.$to);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="activation du '.$at.' au'.$to.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');
    }

    /**
     * @Route("/findreclamation/ajax", name="findreclamation_ajax", methods={"GET"})
     * @throws Exception
     */
    public function findreclamationAjax(Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $resp=[];
        $activaction=$this->activationRepository->find($request->get('id'));
        for ($i=0;$i<sizeof($activaction->getBouquets());$i++){
            $reclamation=new Reclamation();
            $this->logger->info("---".$activaction->getBouquets()[$i]);
            $bou=$this->bouquetRepository->findOneBy(['numero'=>$activaction->getBouquets()[$i]]);
            $reclamation->setAmount($bou->getPrice()*$activaction->getMonthto());
            $reclamation->setStatus(false);
            $reclamation->setCreatedAt(new DateTime('now'));
            $reclamation->setBouquets($activaction->getBouquets());
            $reclamation->setBouquetid($activaction->getBouquets()[$i]);
            $reclamation->setAgent($activaction->getCreatedBy());
            $reclamation->setCard($activaction->getCard()->getNumerocard());
            $reclamation->setIssend(false);
            $reclamation->setReclamationdate(new DateTime($activaction->getCreatedAt()->format('Y-m-d h:i:s')));
            $entityManager->persist($reclamation);
            // card pending
            $cardpending = new CardPending();
            $cardpending->setCardid($activaction->getCard()->getNumerocard());
            $cardpending->setIsdelete(true);
            $cardpending->setSendornot(1);
            $cardpending->setCardstatus(1);
            $cardpending->setStatus(CardPending::SUCCESS);
            $cardpending->setActivation($activaction->getId());
            $cardpending->setBouquet($activaction->getBouquets()[$i]);
            $les=new \DateTime('now',new \DateTimeZone('Africa/Douala'));
            $date=$les->modify('-12 month')->format('Y-m-d').'16:59:59';
            $date_line=new \DateTime($date,new \DateTimeZone('Africa/Douala'));
            $cardpending->setExpiredtime($date_line);
            $entityManager->persist($cardpending);
        }
        $entityManager->remove($activaction);
        $entityManager->flush();
        return new JsonResponse([], 200);
    }
}
