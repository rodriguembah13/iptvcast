<?php

namespace App\Controller;

use App\Entity\Activation;
use App\Entity\CardSetting;
use App\Entity\Reclamation;
use App\Repository\CardSettingRepository;
use Doctrine\ORM\QueryBuilder;
use Monolog\Logger;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingController extends AbstractController
{
    private $logger;
    private $dataTableFactory;
    private $settingRepository;

    /**
     * SettingController constructor.
     * @param $logger
     * @param $dataTableFactory
     * @param $settingRepository
     */
    public function __construct(LoggerInterface $logger, DataTableFactory $dataTableFactory, CardSettingRepository $settingRepository)
    {
        $this->logger = $logger;
        $this->dataTableFactory = $dataTableFactory;
        $this->settingRepository = $settingRepository;
    }

    /**
     * @Route("/setting", name="app_setting")
     * @return Response
     */
    public function index(Request $request): Response
    {
        $table = $this->dataTableFactory->create()
            ->add('numero', TextColumn::class, [
                'label' => 'Card',
            ])
            ->add('commande', TextColumn::class, [
                'label' => 'Command',
            ])
            ->add('createdAt', DateTimeColumn::class, [
                'label' => 'Date creation',
                'format' => "Y-m-d h:i"
            ])
       /*     ->add('createdAt', DateTimeColumn::class, [
                'label' => 'Date reclamation',
                'format' => "Y-m-d h:i"
            ])*/
            ->createAdapter(ORMAdapter::class, [
                'entity' => CardSetting::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('reclamation')
                        ->from(CardSetting::class, 'reclamation')
                        ->orderBy('reclamation.id','ASC');
                },
            ])->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }
        return $this->render('setting/index.html.twig', [
            'title' => "Setiing card",
            'datatable' => $table,
        ]);
    }
    /**
     * @Route("/addsetting", name="app_add_setting")
     * @return Response
     */
    public function addSetting(Request $request): Response
    {
        if ($request->getMethod()=="POST"){
            $commad=$request->get('command');
            $card=$request->get('card');
            $setting=new CardSetting();
            $setting->setStatus(Activation::PENDING);
            $setting->setNumero($card);
            $setting->setCommandvalue($commad);
            $setting->setCommande($this->getCommandID($commad));
            $setting->setIsSend(false);
            $setting->setCreatedAt(new \DateTimeImmutable('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($setting);
            $entityManager->flush();
        }
        return $this->render('setting/addsetting.html.twig', [
            'title' => "Add setting"
        ]);
    }
   function getCommandID($commang){
       $return="";
        switch ($commang){
            case 3:
                $return= "Reset PIN Code";
                break;
            case 8:
                $return= "Activate Area Lock";
                break;
            case 9:
                $return= "Cancel Area Lock";
                break;
            case 11:
                $return= "Activate/Deactivate Card";
                break;
        }
        return $return;
}
}
