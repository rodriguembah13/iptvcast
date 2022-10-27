<?php

namespace App\Controller;

use App\Entity\Bouquet;
use App\Entity\Customer;
use App\Entity\Souscription;
use App\Repository\BouquetRepository;
use App\Repository\CustomerRepository;
use App\Repository\SouscriptionRepository;
use App\Service\EndpointService;
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
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $params;
    private $dataTableFactory;
    private $endpointService;
    private $bouquetRepository;
    private $customerRepository;
    private $souscriptionRepository;
    private $logger;

    /**
     * @param CustomerRepository $customerRepository
     * @param LoggerInterface $logger
     * @param BouquetRepository $bouquetRepository
     * @param EndpointService $endpointService
     * @param DataTableFactory $dataTableFactory
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(SouscriptionRepository $souscriptionRepository,CustomerRepository $customerRepository,LoggerInterface $logger,BouquetRepository $bouquetRepository,EndpointService $endpointService,DataTableFactory $dataTableFactory,ParameterBagInterface $parameterBag)
    {
        $this->params = $parameterBag;
        $this->dataTableFactory = $dataTableFactory;
        $this->endpointService=$endpointService;
        $this->bouquetRepository=$bouquetRepository;
        $this->logger=$logger;
        $this->customerRepository=$customerRepository;
        $this->souscriptionRepository=$souscriptionRepository;
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
            'date'=>date('Y-m-d'),
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
            ->add('name', TextColumn::class,[
                'label' => 'Name',
            ])
            ->add('price', TextColumn::class, [
                'label' => 'Amount',
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('created', DateTimeColumn::class,[
                'label' => 'Date ',
                'format'=>"Y-m-d"
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
                    ;
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/bouquetchanel.html.twig', [
            'datatable' => $table
        ]);
    }
    /**
     * @Route("/bouquetchanel/create", name="bouquetchanel_new")
     * @param Request $request
     * @return Response
     */
    public function bouquetchanel_new(Request $request): Response
    {
        $data=$this->endpointService->getLiveStreams();
        return $this->render('default/bouquetchanel_new.html.twig', [
            'data'=>$data
        ]);
    }

    /**
     * @Route("/bouquetchanel/edit/{id}", name="bouquetchanel_edit")
     * @param Bouquet $bouquet
     * @return Response
     */
    public function bouquetchanel_edit(Bouquet $bouquet): Response
    {
         $data=$this->endpointService->getLiveStreams();
       $arrays_= array_filter($data,function ($item) use($bouquet){
             if (in_array($item['num'],$bouquet->getChanelids())){
                 return true;
             }else{
                 return false;
             }
         });
        return $this->render('default/bouquetchanel_edit.html.twig', [
            'data'=>$data,
            'bouquet'=>$bouquet,
            'chanels'=>$arrays_
        ]);
    }
    /**
     * @Route("/souscriptions", name="souscriptions")
     */
    public function souscriptions(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('datecreation', DateTimeColumn::class,[
                'label' => 'Date ',
                'format'=>"Y-m-d"
            ])
            ->add('amount', TextColumn::class, [
                'label' => 'Montant',
                'render' => function ($value, $context) {
                    return '<span>' . $value . '</span>';
                }
            ])
            ->add('customer', TextColumn::class,[
                'label' => 'Nom du client',
                'field'=>'compte.name'
            ])
            ->add('email', TextColumn::class,[
                'label' => 'Email du client',
                'field'=>'compte.email'
            ])
            ->add('datevalidite', DateTimeColumn::class,[
                'label' => 'Date Expiration',
                'field'=>'customer.expiredAt',
                'format'=>"Y-m-d"
            ])
            ->add('statut', TextColumn::class, [
                'className' => 'buttons',
                'label' => 'status',
                // 'template' => 'user/status.html.twig',
                'render' => function ($value, $context) {
                    if($value== Souscription::ACCEPTED){
                        return '<a class="btn btn-sm btn-success">'.$value.'</a>';
                    }elseif ($value== Souscription::PENDING){
                        return '<a class="btn btn-sm btn-warning">'.$value.'</a>';
                    }
                    else{
                        return '<a class="btn btn-sm btn-danger">'.$value.'</a>';
                    }
                }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Souscription::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('souscription','customer')
                        ->from(Souscription::class, 'souscription')
                        ->join('souscription.customer', 'customer')
                        ->join('customer.compte', 'compte')
                    ;
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/souscriptions.html.twig', [
            'datatable' => $table
        ]);
    }
    /**
     * @Route("/customers", name="customers")
     */
    public function customers(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('customer', TextColumn::class,[
                'label' => 'Name',
                'field'=>'compte.name'
            ])
            ->add('email', TextColumn::class,[
                'label' => 'Email',
                'field'=>'compte.email'
            ])
            ->add('phone', TextColumn::class,[
                'label' => 'Phone',
            ])

            ->add('datevalidite', DateTimeColumn::class,[
                'label' => 'Date Expiration',
                'field'=>'customer.expiredAt',
                'format'=>"Y-m-d"
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Souscription::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('compte','customer')
                        ->from(Customer::class, 'customer')
                        ->join('customer.compte', 'compte')
                    ;
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('default/customers.html.twig', [
            'datatable' => $table
        ]);
    }
    /**
     * @Route("/post/savechanels", name="savechanels", methods={"GET","POST"})
     *
     */
    public function postChanels(Request $request): JsonResponse
    {
        $chs=$request->get('chanels');
        $chanels=[];
        for ($i = 0; $i < sizeof($chs); ++$i) {
            array_push($chanels,$chs[$i]['id']);
        }
        $entityManager = $this->getDoctrine()->getManager();
        if (is_null($request->get('id'))){
            $bouquet=new Bouquet();
            $bouquet->setIsactive(true);
            $entityManager->persist($bouquet);
        }else{
            $bouquet=$this->bouquetRepository->find($request->get('id'));

        }
       // $bouquet->setName($request->get('name'));
        $bouquet->setPrice($request->get('price'));
       // $bouquet->setChanelids($chanels);
        $entityManager->flush();
        return new JsonResponse(["id" => $chanels], 200);
    }
    /**
     * @Route("/notifyurlflutter/ajax", name="notifyurlflutterajax", methods={"POST","GET"})
     */
    public function notifyurlflutter(Request $request)
    {
        $this->logger->error("----------------------- notify call");
        $data=json_decode($request->getContent(), true);
        $this->logger->error("----------------------- notify call". $request->get('customer'));
        if (!empty($request->get('status'))) {
            $status = $request->get('status');
            $customer_ = $this->customerRepository->find($request->get('customer'));
            $souscription_ = $this->souscriptionRepository->findOneBy(['reference'=>$request->get('ref')]);
            if ($souscription_->getStatus() == "PENDING") {
                if ($status == "successful") {
                 //   $this->updateVote($vote_, 'ACCEPTED');
                } elseif ($status == "cancelled") {
                  //  $this->updateVote($vote_, 'REFUSED');
                }
            }
        }else{
            $status = $request->get('status');
        }
        return $this->redirectToRoute('home');
    }
}
