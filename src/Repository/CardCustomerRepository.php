<?php

namespace App\Repository;

use App\Entity\CardCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CardCustomer>
 *
 * @method CardCustomer|null find($id, $lockMode = null, $lockVersion = null)
 * @method CardCustomer|null findOneBy(array $criteria, array $orderBy = null)
 * @method CardCustomer[]    findAll()
 * @method CardCustomer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardCustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardCustomer::class);
    }

    public function add(CardCustomer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CardCustomer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return CardCustomer[] Returns an array of CardCustomer objects
     */
    public function findByCustomer($value): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return CardCustomer[] Returns an array of Customer objects
     */
    public function searchCardCustomer($value): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.card','card')
            ->andWhere('card.numerocard like :val')
            ->setParameter('val', '%'.$value.'%')
            ->orderBy('card.numerocard', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

//    public function findOneBySomeField($value): ?CardCustomer
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
