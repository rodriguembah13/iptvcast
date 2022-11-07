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
use App\Entity\Souscription;
use App\Entity\User;
use App\Repository\AgenceRepository;
use App\Repository\BouquetRepository;
use App\Repository\CardCustomerRepository;
use App\Repository\CardRepository;
use App\Repository\CustomerRepository;
use App\Repository\SouscriptionRepository;
use App\Service\EndpointService;
use App\Service\paiement\ClientPaymoo;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTableFactory;
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
    private $cardRepository;
    private $passwordEncoder;
    private $logger;

    /**
     * @param CustomerRepository $customerRepository
     * @param LoggerInterface $logger
     * @param BouquetRepository $bouquetRepository
     * @param EndpointService $endpointService
     * @param DataTableFactory $dataTableFactory
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(CardRepository $cardRepository,
                                CardCustomerRepository $cardCustomerRepository,
                                SouscriptionRepository $souscriptionRepository,
                                CustomerRepository $customerRepository,
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
        $this->passwordEncoder = $passwordEncoder;
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
     * @Route("/error", name="erropage")
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
     * @Route("/cancelurl", name="cancelpage")
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
     * @Route("/successurl", name="successpage")
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
                        ->from(Bouquet::class, 'bouquet');
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
            ->add('description', TextColumn::class, [
                'label' => 'Name',
                'field' => 'card.name'
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
                        ->leftJoin('card_customer.customer', 'customer');
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
                'format' => "Y-m-d h:m"
            ])
            ->add('card', TextColumn::class, [
                'label' => 'N° card',
                'field' => 'card.name'
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
                        ->setParameter('satus',Activation::SUCCESS)
                        ->join('souscription.card', 'card');
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
                        ->setParameter('satus',Activation::SUCCESS)
                        ->join('souscription.card', 'card');
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
     * @Route("/customers", name="customers")
     */
    public function customers(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('customer', TextColumn::class, [
                'label' => 'Name',
                'field' => 'compte.name'
            ])
            ->add('email', TextColumn::class, [
                'label' => 'Email',
                'field' => 'compte.email'
            ])
            ->add('phone', TextColumn::class, [
                'label' => 'Phone',
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
                        ->join('customer.compte', 'compte');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/customers.html.twig', [
            'datatable' => $table,
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
                        ->join('customer.compte', 'compte');
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
                        ->from(Agence::class, 'agence');
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
            $agence->setAddress($request->get('city'));
            $agence->setCity($request->get('city'));
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
     * @Route("/customers/addcard/{id}", name="customer_add_card")
     * @param Customer $bouquet
     * @return Response
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
                $this->createActivate($cardcustomer, $reference, $request->get('amount'), $produts);
                $url = $response["payment_url"];
                $this->logger->info($url);
                $link_array = explode('/', $url);
                return $this->redirect($url);
            } else {
                return $this->redirectToRoute('erropage');
            }
        }

        return $this->render('default/edit/activatecard.html.twig', [
            'title' => "Activate card",
            'customers' => $this->customerRepository->findAll(),
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
                $this->createActivate($cardcustomer, $reference, $request->get('amount'), $produts);
                $url = $response["payment_url"];
                $this->logger->info($url);
                $link_array = explode('/', $url);
                return $this->redirect($url);
            } else {
                return $this->redirectToRoute('erropage');
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

        return $this->render('default/edit/activatecardcustomer.html.twig', [
            'customer' => $customer,
            'cards' => $this->cardcustomerRepository->findAll(),
            'bouquets' => $this->bouquetRepository->findAll(),
            'title' => "Activate card",

        ]);
    }

    function createActivate(CardCustomer $card, $reference, $amount, $produts)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $actiavtion = new Activation();
        $actiavtion->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Africa/Douala')));
        $actiavtion->setCard($card->getCard());
        $actiavtion->setAmount($amount);
        $month = 1;
        $actiavtion->setMonthto($month);
        $actiavtion->setReference($reference);
        $actiavtion->setStatus(Activation::PENDING);
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
            $date_line = new \DateTime($card->getPeriodto()->format('Y-m-d h:m'), new \DateTimeZone('Africa/Douala'));
            $mod = "+1 month";
            $date_line->modify($mod);
            $cardpending->setExpiredtime($date_line);
            $entityManager->persist($cardpending);
        }

        $entityManager->flush();
    }

    /**
     * @Route("/getpricebouquet/ajax", name="getpricebouquet_ajax", methods={"GET"})
     */
    public function getcandidatAjax(Request $request): JsonResponse
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
     * @Route("/getcardcustomer/ajax", name="getcardcustomer_ajax", methods={"GET"})
     */
    public function getcardcustomerAjax(Request $request): JsonResponse
    {
        $customer = $this->customerRepository->find($request->get('customer'));
        $cardcustomers = $this->cardcustomerRepository->findBy(['customer' => $customer]);
        $data=[];
        foreach ($cardcustomers as $cardCustomer) {
            $data[] = [
                'id' => $cardCustomer->getId(),
                'numero'=>$cardCustomer->getCard()->getNumerocard(),
                'cardid'=>$cardCustomer->getCard()->getId()
            ];
        }
        return new JsonResponse($data, 200);
    }
}
