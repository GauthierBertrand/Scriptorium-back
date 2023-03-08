<?php
namespace App\DataFixtures;

use App\Entity\Classe;
use App\Entity\ClasseEquipment;
use App\Entity\Equipment;
use App\Entity\Race;
use App\Entity\RacialAbility;
use App\Entity\Religion;
use App\Entity\Stat;
use App\Entity\Way;
use App\Entity\User;
use App\Entity\WayAbility;
use App\Utils\DataChronique;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $datas;
    private $hasher;

    public function __construct(DataChronique $dataChronique, UserPasswordHasherInterface $passwordHasher) {
        $this->datas = $dataChronique;
        $this->hasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        foreach($this->datas->races as $race) {
            $raceEntity = new Race();

            $raceEntity->setName($race[0])
            ->setDescription($race[1])
            ->setPartiality($race[2])
            ->setStats($race[3])
            ->setPicturePrincipal($race[4])
            ->setPictureMale($race[5])
            ->setPictureFemale($race[6]);

            $manager->persist($raceEntity);

            foreach($this->datas->racialAbilities[$raceEntity->getName()] as $racialAbility) {
                $racialAbilityEntity = new RacialAbility();

                $racialAbilityEntity->setName($racialAbility[0])
                ->setDescription($racialAbility[1]);

                if ($racialAbility[2] !== null) {
                    $racialAbilityEntity->setBonus($racialAbility[2]);
                }
                
                if ($racialAbility[3] !== null) {
                    $racialAbilityEntity->setTraits($racialAbility[3]);
                }

                $racialAbilityEntity->setRace($raceEntity);

                $manager->persist($racialAbilityEntity);
            }
        }

        foreach($this->datas->stats as $stat) {
            $statEntity = new Stat();

            $statEntity->setName($stat[0])
            ->setDescription($stat[1]);

            $manager->persist($statEntity);
        }

        $classes = [];

        foreach($this->datas->classes as $classe) {
            $classeEntity = new Classe();

            $classeEntity->setName($classe[0])
            ->setDescription($classe[1])
            ->setHitDie($classe[2])
            ->setPicture($classe[3])
            ->setStats($classe[4]);

            $manager->persist($classeEntity);

            $classes[] = $classeEntity;

            

            foreach($this->datas->ways[$classeEntity->getName()] as $wayname => $abilities) {
                $wayEntity = new Way();

                $wayEntity->setName($wayname);
                $wayEntity->setClasse($classeEntity);

                $manager->persist($wayEntity);

                foreach($abilities as $ability) {
                    $wayAbilityEntity = new WayAbility();

                    $wayAbilityEntity->setName($ability[0])
                    ->setDescription($ability[1])
                    ->setLimited($ability[2]);

                    if ($abilities[3] !== null) {
                        $wayAbilityEntity->setBonus($ability[3]);
                    }
                    if ($abilities[4] !== null) {
                        $wayAbilityEntity->setTraits($ability[4]);
                    }
                    
                    $wayAbilityEntity->setLevel($ability[5])
                    ->setCost($ability[6])
                    ->setWay($wayEntity);
                    
                    $manager->persist($wayAbilityEntity);
                }
            }
        }

        foreach($this->datas->equipments as $equipment) {
            $equipmentEntity = new Equipment();

            $equipmentEntity->setName($equipment[0]);

            if ($equipment[2] !== null) {
                $equipmentEntity->setDescription($equipment[1]);
            }
            
            if ($equipment[2] !== null) {
                $equipmentEntity->setDamage($equipment[2]);
            }
            
            if ($equipment[3] !== null) {
                $equipmentEntity->setAttackType($equipment[3]);
            }
            
            if ($equipment[4] !== null) {
                $equipmentEntity->setHand($equipment[4]);
            }

            if ($equipment[5] !== null) {
                $equipmentEntity->setDistance($equipment[5]);
            }

            if ($equipment[6] !== null) {
                $equipmentEntity->setBonus($equipment[6]);
            }

            $manager->persist($equipmentEntity);

            foreach ($classes as $classe) {
                $classsEquipmentEntity = new ClasseEquipment();
                
                if (in_array($classe->getName(), $equipment[7])) {
                    
                    $classsEquipmentEntity->setClasse($classe)
                    ->setEquipment($equipmentEntity);
                    
                    if ($equipment[8] !== null) {
                        $classsEquipmentEntity->setNumber($equipment[8]);
                    }
                    $manager->persist($classsEquipmentEntity);
                }
            }
        }

        foreach($this->datas->religions as $religion) {
            $religionEntity = new Religion();

            $religionEntity->setName($religion[0])
            ->setDescription($religion[1])
            ->setAlignment($religion[2]);

            $manager->persist($religionEntity);
        }
        $manager->flush();
    }
}