<?php

namespace App\Entity;

use App\Repository\RaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=RaceRepository::class)
 */
class Race
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups({"races_get_collection"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"races_get_collection"})
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     * @Groups({"races_get_collection"})
     */
    private $partiality;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"races_get_collection"})
     */
    private $stats = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"races_get_collection"})
     */
    private $picture_principal;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"races_get_collection"})
     */
    private $picture_male;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"races_get_collection"})
     */
    private $picture_female;

    /**
     * @ORM\OneToMany(targetEntity=RacialAbility::class, mappedBy="race")
     * @Groups({"races_get_collection"})
     */
    private $racialAbilities;

    public function __construct()
    {
        $this->racialAbilities = new ArrayCollection();
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

    public function getPartiality(): ?string
    {
        return $this->partiality;
    }

    public function setPartiality(string $partiality): self
    {
        $this->partiality = $partiality;

        return $this;
    }

    public function getStats(): ?array
    {
        return $this->stats;
    }

    public function setStats(?array $stats): self
    {
        $this->stats = $stats;

        return $this;
    }

    public function getPicturePrincipal(): ?string
    {
        return $this->picture_principal;
    }

    public function setPicturePrincipal(string $picture_principal): self
    {
        $this->picture_principal = $picture_principal;

        return $this;
    }

    public function getPictureMale(): ?string
    {
        return $this->picture_male;
    }

    public function setPictureMale(string $picture_male): self
    {
        $this->picture_male = $picture_male;

        return $this;
    }

    public function getPictureFemale(): ?string
    {
        return $this->picture_female;
    }

    public function setPictureFemale(string $picture_female): self
    {
        $this->picture_female = $picture_female;

        return $this;
    }

    /**
     * @return Collection<int, RacialAbility>
     */
    public function getRacialAbilities(): Collection
    {
        return $this->racialAbilities;
    }

    public function addRacialAbility(RacialAbility $racialAbility): self
    {
        if (!$this->racialAbilities->contains($racialAbility)) {
            $this->racialAbilities[] = $racialAbility;
            $racialAbility->setRace($this);
        }

        return $this;
    }

    public function removeRacialAbility(RacialAbility $racialAbility): self
    {
        if ($this->racialAbilities->removeElement($racialAbility)) {
            // set the owning side to null (unless already changed)
            if ($racialAbility->getRace() === $this) {
                $racialAbility->setRace(null);
            }
        }

        return $this;
    }
}
