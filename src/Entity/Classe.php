<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
//!Attention en V>6 il faut ignorer les propriétés. Ici on ne prend que ce dont on a besoin avec les group annotations
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ClasseRepository::class)
 */
class Classe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"classes_get_collection"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups({"classes_get_collection"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"classes_get_collection"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"classes_get_collection"})
     */
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity=Sheet::class, mappedBy="classe")
     * 
     */
    private $sheets;


    /**
     * @ORM\OneToMany(targetEntity=Way::class, mappedBy="classe")
     * 
     */
    private $ways;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"classes_get_collection"})
     */
    private $hit_die;

    /**
     * @ORM\OneToMany(targetEntity=ClasseEquipment::class, mappedBy="classe")
     * @Groups({"classes_get_collection"})
     */
    private $classeEquipment;

    /**
     * @ORM\OneToMany(targetEntity=ClasseStat::class, mappedBy="classe")
     * 
     */
    private $classeStats;

    public function __construct()
    {
        $this->sheets = new ArrayCollection();
        $this->ways = new ArrayCollection();
        $this->classeEquipment = new ArrayCollection();
        $this->classeStats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return Collection<int, Sheet>
     */
    public function getSheets(): Collection
    {
        return $this->sheets;
    }

    public function addSheet(Sheet $sheet): self
    {
        if (!$this->sheets->contains($sheet)) {
            $this->sheets[] = $sheet;
            $sheet->setClasse($this);
        }

        return $this;
    }

    public function removeSheet(Sheet $sheet): self
    {
        if ($this->sheets->removeElement($sheet)) {
            // set the owning side to null (unless already changed)
            if ($sheet->getClasse() === $this) {
                $sheet->setClasse(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Way>
     */
    public function getWays(): Collection
    {
        return $this->ways;
    }

    public function addWay(Way $way): self
    {
        if (!$this->ways->contains($way)) {
            $this->ways[] = $way;
            $way->setClasse($this);
        }

        return $this;
    }

    public function removeWay(Way $way): self
    {
        if ($this->ways->removeElement($way)) {
            // set the owning side to null (unless already changed)
            if ($way->getClasse() === $this) {
                $way->setClasse(null);
            }
        }

        return $this;
    }

    public function getHitDie(): ?int
    {
        return $this->hit_die;
    }

    public function setHitDie(int $hit_die): self
    {
        $this->hit_die = $hit_die;

        return $this;
    }

    /**
     * @return Collection<int, ClasseEquipment>
     */
    public function getClasseEquipment(): Collection
    {
        return $this->classeEquipment;
    }

    public function addClasseEquipment(ClasseEquipment $classeEquipment): self
    {
        if (!$this->classeEquipment->contains($classeEquipment)) {
            $this->classeEquipment[] = $classeEquipment;
            $classeEquipment->setClasse($this);
        }

        return $this;
    }

    public function removeClasseEquipment(ClasseEquipment $classeEquipment): self
    {
        if ($this->classeEquipment->removeElement($classeEquipment)) {
            // set the owning side to null (unless already changed)
            if ($classeEquipment->getClasse() === $this) {
                $classeEquipment->setClasse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClasseStat>
     */
    public function getClasseStats(): Collection
    {
        return $this->classeStats;
    }

    public function addClasseStat(ClasseStat $classeStat): self
    {
        if (!$this->classeStats->contains($classeStat)) {
            $this->classeStats[] = $classeStat;
            $classeStat->setClasse($this);
        }

        return $this;
    }

    public function removeClasseStat(ClasseStat $classeStat): self
    {
        if ($this->classeStats->removeElement($classeStat)) {
            // set the owning side to null (unless already changed)
            if ($classeStat->getClasse() === $this) {
                $classeStat->setClasse(null);
            }
        }

        return $this;
    }
}
