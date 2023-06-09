<?php

namespace App\Repository;

use App\Entity\Classe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Classe>
 *
 * @method Classe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Classe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Classe[]    findAll()
 * @method Classe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classe::class);
    }

    public function add(Classe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Classe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Return a more readable custom array 
     * 
     * @return array
     */
    public function getClassesAndEquipments(): array
    {
        $manager = $this->getEntityManager();

        $query = $manager->createQuery(
            "SELECT classe.id, classe.name, classe.description, classe.picture, classe.hit_die, classe.stats FROM App\Entity\Classe classe"
        );

        $result = $query->getResult();

        $classes = [];

        foreach($result as $classe) {
            $query = $manager->createQuery(
                "SELECT equipment.name, equipment.description, cE.number FROM App\Entity\Equipment equipment JOIN equipment.classeEquipment cE JOIN cE.classe classe WHERE classe.id = :classeId"
            );
            $query->setParameter("classeId", $classe["id"]);
            $classe["equipments"] = $query->getResult();

            $classes[] = $classe; 
        }

        return $classes;
    }

//    /**
//     * @return Classe[] Returns an array of Classe objects
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

//    public function findOneBySomeField($value): ?Classe
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
