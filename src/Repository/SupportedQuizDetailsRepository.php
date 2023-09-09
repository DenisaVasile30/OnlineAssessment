<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Student;
use App\Entity\SupportedQuizDetails;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SupportedQuizDetails>
 *
 * @method SupportedQuizDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportedQuizDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportedQuizDetails[]    findAll()
 * @method SupportedQuizDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportedQuizDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportedQuizDetails::class);
    }

    public function save(SupportedQuizDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SupportedQuizDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SupportedQuizDetails[] Returns an array of SupportedQuizDetails objects
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

//    public function findOneBySomeField($value): ?SupportedQuizDetails
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function getTotalObtainedScore(int $quizId, User $user)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.quizId = :quizId')
            ->setParameter('quizId', $quizId)
            ->andWhere('s.supportedByStudent = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getDetailedQuestionsByQuizId(int $quizId)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.quizId = :quizId')
            ->setParameter('quizId', $quizId)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getDetailedSupportedQuizByQuiz($quizId) {
//        return $this->createQueryBuilder('q')
//            ->select('q.quizId, q.questionId, AVG(q.timeSpent) as averageTime')
//            ->where('q.quizId = :quizId')
//            ->setParameter('quizId', $quizId)
//            ->groupBy('q.quizId, q.questionId')
//            ->getQuery()
//            ->getResult();
//        ;

        return $this->createQueryBuilder('q')
            ->select('q.quizId, q.questionId, AVG(q.timeSpent) as averageTime')
            ->join(Student::class, 's', 'WITH', 's.user = q.supportedByStudent')
            ->join(Group::class, 'g', 'WITH', 'g.id = s.groupId')
            ->where('g.groupNo NOT IN (100,500)')
            ->andWhere('q.quizId = :quizId')
            ->setParameter('quizId', $quizId)
            ->groupBy('q.quizId, q.questionId')
            ->getQuery()
            ->getResult();
    }
}
