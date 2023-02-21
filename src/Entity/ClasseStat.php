<?php

namespace App\Entity;

use App\Repository\ClasseStatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ClasseStatRepository::class)
 */
class ClasseStat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="classeStats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classe;

    /**
     * @ORM\ManyToOne(targetEntity=Stat::class, inversedBy="classeStats")
     * @ORM\JoinColumn(nullable=false)
     * 
     */
    private $stat;

    /**
     * @ORM\Column(type="boolean")
     * 
     */
    private $priority;

    public function __construct()
    {
        $this->priority = true;
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

    public function getStat(): ?Stat
    {
        return $this->stat;
    }

    public function setStat(?Stat $stat): self
    {
        $this->stat = $stat;

        return $this;
    }

    public function isPriority(): ?bool
    {
        return $this->priority;
    }

    public function setPriority(bool $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
