<?php

namespace App\Entity;

use App\Repository\WayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=WayRepository::class)
 */
class Way
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"ways_get_collection"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups({"ways_get_collection"})
     * @Groups({"sheets_get_collection"})
     * @Groups({"sheet_get_item"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="ways")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classe;

    /**
     * @ORM\OneToMany(targetEntity=WayAbility::class, mappedBy="way")
     * @Groups({"ways_get_collection"})
     * @Groups({"races_get_collection"})
     */
    private $wayAbilities;

    public function __construct()
    {
        $this->wayAbilities = new ArrayCollection();
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

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * @return Collection<int, WayAbility>
     */
    public function getWayAbilities(): Collection
    {
        return $this->wayAbilities;
    }

    public function addWayAbility(WayAbility $wayAbility): self
    {
        if (!$this->wayAbilities->contains($wayAbility)) {
            $this->wayAbilities[] = $wayAbility;
            $wayAbility->setWay($this);
        }

        return $this;
    }

    public function removeWayAbility(WayAbility $wayAbility): self
    {
        if ($this->wayAbilities->removeElement($wayAbility)) {
            // set the owning side to null (unless already changed)
            if ($wayAbility->getWay() === $this) {
                $wayAbility->setWay(null);
            }
        }

        return $this;
    }
}
