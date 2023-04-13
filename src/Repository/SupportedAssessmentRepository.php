<?php

namespace App\Repository;

use App\Entity\SupportedAssessment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SupportedAssessment>
 *
 * @method SupportedAssessment|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportedAssessment|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportedAssessment[]    findAll()
 * @method SupportedAssessment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportedAssessmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportedAssessment::class);
    }

    public function save(SupportedAssessment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SupportedAssessment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SupportedAssessment[] Returns an array of SupportedAssessment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SupportedAssessment
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findSubmittedAssessmentDetailed(int $assessmentId, User $user)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.assessmentId = :assessmentId')
            ->setParameter('assessmentId', $assessmentId)
            ->andWhere('s.submittedBy = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getSupportedAssessmentsById($assessmentId) {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.submittedBy', 'u')
            ->addSelect('u')
            ->andwhere('s.assessmentId = :assessmentId')
            ->setParameter('assessmentId',  $assessmentId)
            ->getQuery()
            ->getResult();
        ;
    }
}
