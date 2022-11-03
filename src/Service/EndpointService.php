<?php


namespace App\Service;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EndpointService
{
    //52107553 used
    //52112779
    private $params;
    /**
     * @var Client
     */
    private $client;
    private $tokencinet;
    private $logger;
    private $connection;
    private $dbPrefix = '';
    /**
     * EkolopayService constructor.
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->logger = $logger;
        $this->client = new Client([
            'base_uri' => $params->get('API_URL'),
        ]);
        $config = new Configuration();
       //$connectionParams = ['url' => "mysql://symfony:Symfony123*@localhost:3306/GOS_CAS?charset=utf8&autoReconnect=true"];
        $connectionParams = ['url' => "mysql://iptvcast:iptvcast@10.1.1.195:3306/GOS_CAS?charset=utf8&autoReconnect=true"];
        $this->connection = DriverManager::getConnection($connectionParams, $config);
    }
    function getCardStatus($card){
        $cardstatus=$this->fetchAllFromImport('AllCardStatus',['CardID'=>$card]);
        return $cardstatus;
    }
    function getAllCardStatus(){
        $cardstatus=$this->fetchAllFromImport('OldAccredit');
        return $cardstatus;
    }
    function createCard($data){
        $values=[];
        $this->connection->createQueryBuilder()
            ->insert()->values($values)->execute();
    }
    function getAllProduits(){
        $cardstatus=$this->fetchAllFromImport('ProductInfo');
        return $cardstatus;
    }
    /**
     * @param string $table
     * @param array $where
     * @return array
     */
    protected function fetchAllFromImport($table, array $where = [])
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->connection->quoteIdentifier($this->dbPrefix . $table));

        foreach ($where as $column => $value) {
            $query->andWhere($query->expr()->eq($column, $value));
        }

        return $query->execute()->fetchAll();
    }
}
