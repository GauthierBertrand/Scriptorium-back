<?php

namespace App\Entity;

use App\Repository\RacialAbilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=RacialAbilityRepository::class)
 */
class RacialAbility
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
     * @Groups({"sheets_get_collection"})
     * @Groups({"sheet_get_item"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"races_get_collection"})
     * @Groups({"sheets_get_collection"})
     * @Groups({"sheet_get_item"})
     */
    private $description;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"races_get_collection"})
     */
    private $bonus = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"races_get_collection"})
     */
    private $traits = [];

    /**
     * @ORM\ManyToOne(targetEntity=Race::class, inversedBy="racialAbilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $race;

    /**
     * @ORM\OneToMany(targetEntity=Sheet::class, mappedBy="racialAbility")
     */
    private $sheets;

    public function __construct()
    {
        $this->sheets = new ArrayCollection();
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

    public function getBonus(): ?array
    {
        return $this->bonus;
    }

    public function setBonus(array $bonus): self
    {
        $this->bonus = $bonus;

        return $this;
    }

    public function getTraits(): ?array
    {
        return $this->traits;
    }

    public function setTraits(?array $traits): self
    {
        $this->traits = $traits;

        return $this;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): self
    {
        $this->race = $race;

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
            $sheet->setRacialAbility($this);
        }

        return $this;
    }

    public function removeSheet(Sheet $sheet): self
    {
        if ($this->sheets->removeElement($sheet)) {
            // set the owning side to null (unless already changed)
            if ($sheet->getRacialAbility() === $this) {
                $sheet->setRacialAbility(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
