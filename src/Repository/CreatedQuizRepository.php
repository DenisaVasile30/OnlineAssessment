<?php

namespace App\Repository;

use App\Entity\CreatedQuiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CreatedQuiz>
 *
 * @method CreatedQuiz|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreatedQuiz|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreatedQuiz[]    findAll()
 * @method CreatedQuiz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreatedQuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreatedQuiz::class);
    }

    public function save(CreatedQuiz $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CreatedQuiz $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CreatedQuiz[] Returns an array of CreatedQuiz objects
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

//    public function findOneBySomeField($value): ?CreatedQuiz
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getQuizzesByGroupNo(int $groupNo): array
    {
        $groupNo = json_encode($groupNo);
        return $this->createQueryBuilder('c')
//            ->andWhere("a.status = 'Active'")
            ->andWhere("JSON_CONTAINS(c.assigneeGroup, :groupNo) = 1")
            ->setParameter('groupNo', $groupNo)
            ->orderBy('c.startAt', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getCreatedQuizzesByIssuerId(int $issuerId, string $status = 'Active'): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.createdBy = :issuerId')
            ->setParameter('issuerId', $issuerId)
//            ->andWhere('a.status = :status')
//            ->setParameter('status', $status)
            ->orderBy('c.startAt', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
