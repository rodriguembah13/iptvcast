<?php

namespace App\Repository;

use App\Entity\Activation;
use App\Entity\CardSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CardSetting>
 *
 * @method CardSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method CardSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method CardSetting[]    findAll()
 * @method CardSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardSetting::class);
    }

    public function add(CardSetting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CardSetting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CardSetting[] Returns an array of CardSetting objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CardSetting
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findOneByFirst()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isSend = 0')
            ->andWhere('s.status = :status')
            ->setMaxResults(1)
            ->setParameter('status',Activation::PENDING)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
