<?php

namespace App\Repository;

use App\Entity\Student;
use App\Entity\SupportedQuiz;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SupportedQuiz>
 *
 * @method SupportedQuiz|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportedQuiz|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportedQuiz[]    findAll()
 * @method SupportedQuiz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportedQuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportedQuiz::class);
    }

    public function save(SupportedQuiz $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SupportedQuiz $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getSupportedQuizzesById($quiz) {
        return $this->createQueryBuilder('s')
//            ->join(User::class, 'u',  Query\Expr\Join::WITH, 's.supportedBy = u.id')
//            ->select('u')
            ->leftJoin('s.supportedBy', 'u')
            ->addSelect('u')
            ->andwhere('s.quiz = :quiz')
            ->setParameter('quiz',  $quiz)
            ->getQuery()
            ->getResult();
        ;
    }

    public function getSupportedQuizzesListById(array $quizzes) {
        return $this->createQueryBuilder('s')
//            ->join(User::class, 'u',  Query\Expr\Join::WITH, 's.supportedBy = u.id')
//            ->select('u')
            ->leftJoin('s.supportedBy', 'u')
            ->addSelect('u')
            ->andwhere('s.quiz IN (:quiz)')
            ->setParameter('quiz', implode(',', $quizzes))
            ->getQuery()
            ->getResult();
        ;
    }

//    /**
//     * @return SupportedQuiz[] Returns an array of SupportedQuiz objects
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

//    public function findOneBySomeField($value): ?SupportedQuiz
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
