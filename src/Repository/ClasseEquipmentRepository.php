<?php

namespace App\Repository;

use App\Entity\ClasseEquipment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClasseEquipment>
 *
 * @method ClasseEquipment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClasseEquipment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClasseEquipment[]    findAll()
 * @method ClasseEquipment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClasseEquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClasseEquipment::class);
    }

    public function add(ClasseEquipment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ClasseEquipment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ClasseEquipment[] Returns an array of ClasseEquipment objects
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

//    public function findOneBySomeField($value): ?ClasseEquipment
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
