<?php

namespace App\Repository;

use App\Entity\Activation;
use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activation>
 *
 * @method Activation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activation[]    findAll()
 * @method Activation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activation::class);
    }

    public function add(Activation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Activation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Activation[] Returns an array of Activation objects
     */
    public function findByAllorder(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Card $card
     * @return Activation Returns an array of Activation objects
     * @throws NonUniqueResultException
     */
    public function findByCustomer(Card $card)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.card = :card')
            ->andWhere('a.status = :status')
            ->setParameter('status',Activation::SUCCESS)
            ->setParameter('card',$card)
            ->setMaxResults(1)
                ->orderBy('a.id', 'DESC')
                ->getQuery()
                ->getOneOrNullResult()
            ;
    }

//    public function findOneBySomeField($value): ?Activation
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
