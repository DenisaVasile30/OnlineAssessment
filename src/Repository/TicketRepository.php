<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Ticket>
 *
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function save(Ticket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ticket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getOwnedTickets($user) {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.assignedTo', 'a')
            ->addSelect('a')
            ->andwhere('t.issuedBy = :user')
            ->orWhere('t.assignedTo = :user')
            ->setParameter('user', $user)
            ->orderBy('t.issuedAt', 'ASC')
            ->getQuery()
            ->getResult();
        ;
    }

    public function getTicketsWithMultipleAssignedTo(int $groupNo) {

        return $this->createQueryBuilder('t')
            ->andWhere("t.multipleAssignTo LIKE :groupNo")
            ->setParameter('groupNo', '%"Group":'.$groupNo.'%')
            ->getQuery()
            ->getResult();

    }

    public function findTicket(int $id) {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.issuedBy', 'i')
            ->addSelect('i')
            ->leftJoin('t.assignedTo', 'a')
            ->addSelect('a')
            ->andwhere('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
        ;
    }

//    /**
//     * @return Ticket[] Returns an array of Ticket objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Ticket
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
