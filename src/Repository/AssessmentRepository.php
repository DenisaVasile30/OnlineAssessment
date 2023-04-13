<?php

namespace App\Repository;

use App\Entity\Assessment;
use App\Entity\Group;
use App\Entity\Student;
use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assessment>
 *
 * @method Assessment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Assessment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Assessment[]    findAll()
 * @method Assessment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssessmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assessment::class);
    }

    public function save(Assessment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Assessment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAssessmentsByGroupNo(int $groupNo): array
    {
        $groupNo = json_encode($groupNo);
        return $this->createQueryBuilder('a')
            ->andWhere("a.status = 'Active'")
            ->andWhere("JSON_CONTAINS(a.assigneeGroup, :groupNo) = 1")
            ->setParameter('groupNo', $groupNo)
            ->orderBy('a.startAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAssessmentsByIssuerId(int $issuerId, string $status = 'Active'): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.createdBy = :issuerId')
            ->setParameter('issuerId', $issuerId)
            ->andWhere('a.status = :status')
            ->setParameter('status', $status)
            ->orderBy('a.startAt', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return Assessment[] Returns an array of Assessment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Assessment
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function getDetailedAssessment(int $assessmentId)
    {
        return $this->createQueryBuilder('a')
//            ->join(User::class, 'u',  Query\Expr\Join::WITH, 's.supportedBy = u.id')
//            ->select('u')
            ->leftJoin('a.subject', 's')
            ->addSelect('s')
            ->andwhere('a.id = :id')
            ->setParameter('id',  $assessmentId)
            ->getQuery()
            ->getResult();
        ;
    }
}
