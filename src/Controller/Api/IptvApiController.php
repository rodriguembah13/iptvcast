<?php


namespace App\Controller\Api;


use App\Entity\Customer;
use App\Repository\BouquetRepository;
use App\Repository\CardPendingRepository;
use App\Repository\CardRepository;
use App\Repository\CustomerRepository;
use App\Service\EndpointService;
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

    /**
     * IptvApiController constructor.
     * @param CardPendingRepository $cardPendingRepository
     * @param CardRepository $cardRepository
     * @param BouquetRepository $bouquetRepository
     * @param LoggerInterface $logger
     * @param EndpointService $endpointService
     */
    public function __construct(CardPendingRepository $cardPendingRepository,CardRepository $cardRepository,BouquetRepository $bouquetRepository,LoggerInterface $logger, EndpointService $endpointService)
    {
        $this->logger = $logger;
        $this->endpointsService = $endpointService;
        $this->bouquetRepository=$bouquetRepository;
        $this->cardRepository=$cardRepository;
        $this->cardpendingRepository=$cardPendingRepository;
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
        $values = $this->endpointsService->getAllCardStatus();

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
     */
    public function activateCard()
    {
        $data = $this->cardpendingRepository->findOneByFirst();
        $values = [
            'id'=>$data->getId(),
            'card_id'=>$data->getCardid(),
            'card_status'=>$data->getCardstatus(),
            'send_or_not'=>$data->getSendornot(),
            'expired_time'=>$data->getExpiredtime()->format('Y-m-d h:m:s'),
        ];
        $view = $this->view($values, Response::HTTP_OK, []);
        return $this->handleView($view);
    }
     /**
     * @Rest\Post("/v1/reponseactivatecard", name="api_activatecardresponse")
     * @return Response
     */
    public function activateCardResponse(Request $request)
    {
       $res = json_decode($request->getContent(), true);
       $this->logger->info("----------activation ok---------------");
        $data = $res['data'];
        $view = $this->view([], Response::HTTP_OK, []);
        return $this->handleView($view);
    }
}
