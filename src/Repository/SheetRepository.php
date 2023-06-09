<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Sheet;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Sheet>
 *
 * @method Sheet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sheet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sheet[]    findAll()
 * @method Sheet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SheetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sheet::class);
    }

    public function add(Sheet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sheet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $user 
     */
    public function getSheetsByUser($user) 
    {
        $manager = $this->getEntityManager();

        $query = $manager->createQuery(
            "SELECT sheet FROM App\Entity\Sheet sheet WHERE sheet.user = :user"
        );

        $query->setParameter("user", $user);

        return $query->getResult();
    }

    /**
     * @param int $id
     */
    public function getSavedSheet($id)
    {
        $manager = $this->getEntityManager();

        $dqlQuery = $manager->createQuery("SELECT sheet, classe, ways, way_abilities, racialAbility  FROM App\Entity\Sheet sheet JOIN sheet.classe classe JOIN sheet.way_abilities way_abilities JOIN classe.ways ways JOIN sheet.racialAbility racialAbility WHERE sheet.id = :id");

        $dqlQuery->setParameter("id", $id);

        return $dqlQuery->getSingleResult();
    }


//    /**
//     * @return Sheet[] Returns an array of Sheet objects
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

//    public function findOneBySomeField($value): ?Sheet
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
