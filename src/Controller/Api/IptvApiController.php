<?php


namespace App\Controller\Api;


use App\Entity\Customer;
use App\Repository\BouquetRepository;
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

    /**
     * IptvApiController constructor.
     */
    public function __construct(BouquetRepository $bouquetRepository,LoggerInterface $logger, EndpointService $endpointService)
    {
        $this->logger = $logger;
        $this->endpointsService = $endpointService;
        $this->bouquetRepository=$bouquetRepository;
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
}
