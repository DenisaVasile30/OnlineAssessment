<?php

namespace App\Repository;

use App\Entity\QuizQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizQuestion>
 *
 * @method QuizQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuizQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuizQuestion[]    findAll()
 * @method QuizQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuizQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizQuestion::class);
    }

    public function save(QuizQuestion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(QuizQuestion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return QuizQuestion[] Returns an array of QuizQuestion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?QuizQuestion
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function filterQuestions(array $filters)
    {
        $mainQuery = $this->createQueryBuilder('q')
            ->andWhere('q.id LIKE :id')
            ->setParameter('id', '%' . $filters['id'] . '%')
            ->andWhere('q.category LIKE :category')
            ->setParameter('category', '%' . $filters['category'] . '%')
            ->andWhere('q.optionalDescription LIKE :optionalDescription')
            ->setParameter('optionalDescription', '%' . $filters['optional_description'] . '%')
            ->andWhere('q.questionContent LIKE :questionContent')
            ->setParameter('questionContent', '%' . $filters['question_content'] . '%')
            ->andWhere('q.choiceA LIKE :choiceA')
            ->setParameter('choiceA', '%' . $filters['choice_a'] . '%')
            ->andWhere('q.choiceB LIKE :choiceB')
            ->setParameter('choiceB', '%' . $filters['choice_b'] . '%')
            ->andWhere('q.choiceC LIKE :choiceC')
            ->setParameter('choiceC', '%' . $filters['choice_c'] . '%')
            ->andWhere('q.choiceD LIKE :choiceD')
            ->setParameter('choiceD', '%' . $filters['choice_d'] . '%')
            ->andWhere('q.correctAnswer LIKE :correctAnswer')
            ->setParameter('correctAnswer', '%' . $filters['correct_answer'] . '%');

        if (isset($filters['issued_by']) && $filters['issued_by'] != '') {
            $mainQuery
                ->andWhere('q.issuedBy = :issuedBy')
                ->setParameter('issuedBy', $filters['issued_by']);
        }

        return $mainQuery
            ->getQuery()
            ->getResult()
        ;
    }
}
