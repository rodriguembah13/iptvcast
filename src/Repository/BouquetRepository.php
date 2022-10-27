<?php

namespace App\Repository;

use App\Entity\Bouquet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bouquet>
 *
 * @method Bouquet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bouquet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bouquet[]    findAll()
 * @method Bouquet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BouquetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bouquet::class);
    }

    public function add(Bouquet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Bouquet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param $values
     * @return Bouquet[] Returns an array of Bouquet objects
     */
    public function findByBouquetIds($values): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('b')
            ->from(Bouquet::class,'b');
        $qb->andWhere('b.bouquetid IN (:ids)')
            ->setParameter('ids', $values);
        return $qb->getQuery()->getResult();
    }

//    public function findOneBySomeField($value): ?Bouquet
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
