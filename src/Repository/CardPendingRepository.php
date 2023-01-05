<?php

namespace App\Repository;

use App\Entity\CardPending;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CardPending>
 *
 * @method CardPending|null find($id, $lockMode = null, $lockVersion = null)
 * @method CardPending|null findOneBy(array $criteria, array $orderBy = null)
 * @method CardPending[]    findAll()
 * @method CardPending[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardPendingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardPending::class);
    }

    public function add(CardPending $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CardPending $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findOneByFirst()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isdelete = 1')
            ->andWhere('s.status = :status')
            ->setMaxResults(1)
            ->setParameter('status',CardPending::SUCCESS)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    /**
     * @return CardPending[] Returns an array of Activation objects
     */
    public function findByNumeroAndDate($card,$begin,$end): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb->select('s')
            ->leftJoin("s","card");
        $qb->andWhere($qb->expr()->in('s.bouquets',$card));
        $qb->andWhere('s.expiredtime >= :begin')
            ->andWhere('s.createdAt <= :end')
            ->andWhere('s.status = :status')
            ->setParameter('status',Activation::SUCCESS)
            ->setParameter('begin',$begin )
            ->setParameter('end', $end.' 23:59')
            ->orderBy('s.id', 'DESC');
        return $qb->getQuery()->getResult();
    }
}
