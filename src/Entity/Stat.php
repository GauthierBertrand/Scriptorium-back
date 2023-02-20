<?php

namespace App\Entity;

use App\Repository\StatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=StatRepository::class)
 */
class Stat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     * @Groups({"classes_get_collection"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=ClasseStat::class, mappedBy="stat")
     */
    private $classeStats;

    public function __construct()
    {
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
            $classeStat->setStat($this);
        }

        return $this;
    }

    public function removeClasseStat(ClasseStat $classeStat): self
    {
        if ($this->classeStats->removeElement($classeStat)) {
            // set the owning side to null (unless already changed)
            if ($classeStat->getStat() === $this) {
                $classeStat->setStat(null);
            }
        }

        return $this;
    }
}
