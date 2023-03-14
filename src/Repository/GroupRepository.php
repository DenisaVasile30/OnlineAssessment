<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Group>
 *
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    public function save(Group $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Group $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getStudentsFromGroup(int $groupNo = 100): array
    {
//        return $this->createQueryBuilder('gr')
//            ->andWhere('gr.groupNo = :groupNo')
//            ->setParameter('groupNo', $groupNo)
//            ->leftJoin(Student::class, 'st', Query\Expr\Join::WITH, 'gr.id = st.groupId')
//            ->select('st')
//            ->orderBy('gr.groupNo', 'ASC')
//            ->getQuery()
//            ->getResult();

        return $this->createQueryBuilder('gr')
            ->setParameter('groupNo', $groupNo)
            ->leftJoin(Student::class, 'st', Query\Expr\Join::WITH, 'gr.id = st.groupId')
            ->select('st')
            ->join(User::class, 'us', Query\Expr\Join::WITH, 'us.id = st.user')
            ->select('us')
            ->andWhere('gr.groupNo = :groupNo')
            ->orderBy('gr.groupNo', 'ASC')
            ->getQuery()
            ->getResult();


    }


//    /**
//     * @return Group[] Returns an array of Group objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Group
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
