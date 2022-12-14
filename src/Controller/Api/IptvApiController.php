<?php


namespace App\Controller\Api;


use App\Entity\Activation;
use App\Entity\Customer;
use App\Repository\BouquetRepository;
use App\Repository\CardPendingRepository;
use App\Repository\CardRepository;
use App\Repository\CardSettingRepository;
use App\Repository\CustomerRepository;
use App\Repository\ReclamationRepository;
use App\Service\EndpointService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IptvApiController extends AbstractFOSRestController
{

    private $logger;
    private $endpointsService;
    private $bouquetRepository;
    private $cardRepository;
    private $cardpendingRepository;
    private $reclamationRepository;
    private $settingRepository;
    private $doctrine;

    /**
     * IptvApiController constructor.
     * @param CardPendingRepository $cardPendingRepository
     * @param EntityManagerInterface $entityManager
     * @param CardRepository $cardRepository
     * @param BouquetRepository $bouquetRepository
     * @param LoggerInterface $logger
     * @param EndpointService $endpointService
     */
    public function __construct(ReclamationRepository $reclamationRepository,CardSettingRepository $cardSettingRepository,
                                CardPendingRepository $cardPendingRepository, EntityManagerInterface $entityManager,
                                CardRepository $cardRepository, BouquetRepository $bouquetRepository, LoggerInterface $logger, EndpointService $endpointService)
    {
        $this->logger = $logger;
        $this->endpointsService = $endpointService;
        $this->bouquetRepository = $bouquetRepository;
        $this->cardRepository = $cardRepository;
        $this->cardpendingRepository = $cardPendingRepository;
        $this->reclamationRepository=$reclamationRepository;
        $this->settingRepository=$cardSettingRepository;
        $this->doctrine = $entityManager;
    }


    /**
     * @Rest\Get("/v1/cardstatus/check/{card}", name="api_checkcardstatus")
     * @return Response
     */
    public function getcardstatus($card)
    {
        $values = $this->endpointsService->getCardStatus($card);
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Rest\Get("/v1/cardstatus/all", name="api_allcardstatus")
     * @return Response
     */
    public function getAllcardstatus()
    {
        $values = $this->endpointsService->getAllProduits();

        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Rest\Get("/v1/cardstatus", name="api_allcard")
     * @return Response
     */
    public function getAllcard()
    {
        $data = $this->cardRepository->findAll();
        /*  $values = [
              'card_id'=>$data['card_id'],
              'card_status'=>$data['card_status'],
              'send_or_not'=>$data['send_or_not'],
              'expired_time'=>$data['expired_time'],
          ];*/
        $view = $this->view($data, Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Rest\Get("/v1/activatecard", name="api_activatecard")
     * @return Response
     * @throws Exception
     */
    public function activateCard()
    {
        $data = $this->cardpendingRepository->findOneByFirst();
        if (is_null($data)){
            $values=[
                'res'=>400
            ];
        }else{
            $les=new \DateTime('now',new \DateTimeZone('Africa/Douala'));
            $date=$les->modify('-1 day')->format('Y-m-d').'16:59:59';
            $now=new \DateTime($date,new \DateTimeZone('Africa/Douala'));

            $values = [
                'res'=>200,
                'id' => $data->getId(),
                'card_id' => $data->getCardid(),
                'card_status' => $data->getCardstatus(),
                'product_id'=>$data->getBouquet(),
                'send_or_not' => $data->getSendornot(),
                'expired_time' => $data->getExpiredtime()->format('Y-m-d h:m:s'),
                'begin_time' => $now->format('Y-m-d h:m:s'),
                'expired_timestamp'=>date_timestamp_get($data->getExpiredtime()),
                'begin_timestamp'=>date_timestamp_get($now)
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/v1/reponseactivatecard", name="api_activatecardresponse")
     * @param Request $request
     * @return Response
     */
    public function activateCardResponse(Request $request)
    {
        $res = json_decode($request->getContent(), true);
        $this->logger->info("----------activation ok---------------");
        $pending = $this->cardpendingRepository->find($res['id']);
        if ($res['code'] == 0) {
            $pending->setCardstatus(0);
            $pending->setIsdelete(true);
            $this->doctrine->remove($pending);
        } else {
            $pending->setCardstatus(1);
            $pending->setIsdelete(false);
        }
        $this->logger->info($res['code']);
        $this->logger->info($res['cardid']);
        $this->logger->info($res['id']);
        $this->doctrine->flush();
        $view = $this->view([], Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Rest\Get("/v1/reclamationsend", name="api_reclamationsend")
     * @return Response
     * @throws Exception
     */
    public function reclamationRequet()
    {
        $data = $this->reclamationRepository->findOneByFirst();
        if (is_null($data)){
            $values=[
                'res'=>400
            ];
        }else{
            $les=new \DateTime('now',new \DateTimeZone('Africa/Douala'));
            $date=$les->modify('-12 month')->format('Y-m-d').'16:59:59';
            $now=new \DateTime($date,new \DateTimeZone('Africa/Douala'));

            $values = [
                'res'=>200,
                'id' => $data->getId(),
                'card_id' => intval($data->getCard()),
                'card_status' => 1,
                'product_id'=>$data->getBouquetid(),
                'send_or_not' => 1,
                'expired_time' => $now->format('Y-m-d h:m:s'),
                'begin_time' => $now->format('Y-m-d h:m:s'),
                'expired_timestamp'=>date_timestamp_get($now),
                'begin_timestamp'=>date_timestamp_get($now)
            ];
        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/v1/reponsereclamation", name="api_reponsereclamation")
     * @param Request $request
     * @return Response
     */
    public function reclamationResponse(Request $request)
    {
        $res = json_decode($request->getContent(), true);
        $this->logger->info("----------activation ok---------------");
        $pending = $this->reclamationRepository->find($res['id']);
        if ($res['code'] == 0) {
            $pending->setIssend(true);
            $pending->setStatus(true);
        } else {
            $pending->setStatus(false);
        }
        $this->doctrine->flush();
        $view = $this->view([], Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Get("/v1/settingrequest", name="api_settingrequest")
     * @return Response
     * @throws Exception
     */
    public function settingsRequet()
    {
        $data = $this->settingRepository->findOneByFirst();
        if (is_null($data)){
            $values=[
                'res'=>400
            ];
        }else{
            $les=new \DateTime('now',new \DateTimeZone('Africa/Douala'));
            $date=$les->modify('12 year')->format('Y-m-d').'16:59:59';
            $now=new \DateTime($date,new \DateTimeZone('Africa/Douala'));
            switch ($data->getCommandvalue()){
                case 8: //Activate Area Lock
                    $values=[
                        'res'=>200,
                        'id' => $data->getId(),
                        'card_id' => intval($data->getNumero()),
                        'expired_timestamp'=>date_timestamp_get($now),
                        'command'=>8
                    ];
                    break;
                case 9: //Cancel Area Lock
                    $values=[
                        'res'=>200,
                        'id' => $data->getId(),
                        'card_id' => intval($data->getNumero()),
                        'expired_timestamp'=>date_timestamp_get($now),
                        'command'=>9
                    ];
                    break;
                case 3: //
                    $values=[
                        'res'=>200,
                        'id' => $data->getId(),
                        'card_id' => intval($data->getNumero()),
                        'expired_timestamp'=>date_timestamp_get($now),
                        'command'=>3
                    ];
                    break;
                case 11: //activate card
                    $values=[
                        'res'=>200,
                        'id' => $data->getId(),
                        'card_id' => intval($data->getNumero()),
                        'expired_timestamp'=>date_timestamp_get($now),
                        'card_status' => 1,
                        'send_or_not' => 1,
                        'command'=>11
                    ];
                    break;
                default:
                    $values=[
                        'res'=>200,
                    ];
            }

        }
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
    /**
     * @Rest\Post("/v1/reponsesetting", name="api_reponsesetting")
     * @param Request $request
     * @return Response
     */
    public function settingResponse(Request $request)
    {
        $res = json_decode($request->getContent(), true);
        $this->logger->info("----------setting ok---------------");
        $pending = $this->settingRepository->find($res['id']);
        if ($res['code'] == 0) {
            $pending->setIssend(true);
            $pending->setStatus(Activation::SUCCESS);
        } else {
            $pending->setStatus(Activation::ECHEC);
        }
        $this->doctrine->flush();
        $view = $this->view([], Response::HTTP_OK, []);
        return $this->handleView($view);
    }
}
