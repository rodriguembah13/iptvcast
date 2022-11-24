<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function add(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Customer[] Returns an array of Customer objects
     */
    public function searchCustomer($value): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.compte','compte')
            ->andWhere('compte.name like :val')
            ->setParameter('val', '%'.$value.'%')
            ->orderBy('compte.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return Customer[] Returns an array of Customer objects
     */
    public function findAllOrder(): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.compte','compte')
            ->orderBy('compte.name', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
//    public function findOneBySomeField($value): ?Customer
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
