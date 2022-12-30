<?php

namespace App\Repository;

use App\Entity\RechargeWallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RechargeWallet>
 *
 * @method RechargeWallet|null find($id, $lockMode = null, $lockVersion = null)
 * @method RechargeWallet|null findOneBy(array $criteria, array $orderBy = null)
 * @method RechargeWallet[]    findAll()
 * @method RechargeWallet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RechargeWalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RechargeWallet::class);
    }

    public function add(RechargeWallet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RechargeWallet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * @return RechargeWallet[] Returns an array of Activation objects
     */
    public function findByAllbydateAndAgent($at,$to,$agent): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb->select('s');
        $qb->leftJoin('s.personnel','f');
        $qb->andWhere('s.personnel = :agent')
            ->setParameter('agent',$agent);
        $qb->andWhere('s.createdAt >= :begin')
            ->andWhere('s.createdAt <= :end')
            ->setParameter('begin',$at )
            ->setParameter('end', $to.' 23:59')
            ->orderBy('s.id', 'DESC');
        //$qb->orderBy('s.id');
        return $qb->getQuery()->getResult();
    }
//    /**
//     * @return RechargeWallet[] Returns an array of RechargeWallet objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RechargeWallet
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
