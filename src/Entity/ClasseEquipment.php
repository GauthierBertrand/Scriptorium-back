<?php

namespace App\Entity;

use App\Repository\ClasseEquipmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ClasseEquipmentRepository::class)
 */
class ClasseEquipment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="classeEquipment")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classe;

    /**
     * @ORM\ManyToOne(targetEntity=Equipment::class, inversedBy="classeEquipment")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"classes_get_collection"})
     */
    private $equipment;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"classes_get_collection"})
     */
    private $number;

    public function __construct()
    {
        $this->number = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }
}
