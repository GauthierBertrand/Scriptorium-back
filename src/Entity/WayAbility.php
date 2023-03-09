<?php

namespace App\Entity;

use App\Repository\WayAbilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=WayAbilityRepository::class)
 */
class WayAbility
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
     *@Groups({"sheets_get_collection"})
     * @Groups({"sheet_get_item"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"ways_get_collection"})
     * 
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"ways_get_collection"})
     * 
     */
    private $limited;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"ways_get_collection"})
     * 
     */
    private $bonus = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"ways_get_collection"})
     */
    private $traits = [];

    /**
     * @ORM\Column(type="integer")
     * @Groups({"ways_get_collection"})
     * 
     */
    private $cost;

    /**
     * @ORM\ManyToMany(targetEntity=Sheet::class, mappedBy="way_abilities")
     * 
     */
    private $sheets;

    /**
     * @ORM\ManyToOne(targetEntity=Way::class, inversedBy="wayAbilities")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"sheets_get_collection"})
     * @Groups({"sheet_get_item"})
     */
    private $way;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"ways_get_collection"})
     * 
     */
    private $level;

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

    public function isLimited(): ?bool
    {
        return $this->limited;
    }

    public function setLimited(bool $limited): self
    {
        $this->limited = $limited;

        return $this;
    }

    public function getBonus(): ?array
    {
        return $this->bonus;
    }

    public function setBonus(?array $bonus): self
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

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(int $cost): self
    {
        $this->cost = $cost;

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
            $sheet->addWayAbility($this);
        }

        return $this;
    }

    public function removeSheet(Sheet $sheet): self
    {
        if ($this->sheets->removeElement($sheet)) {
            $sheet->removeWayAbility($this);
        }

        return $this;
    }

    public function getWay(): ?Way
    {
        return $this->way;
    }

    public function setWay(?Way $way): self
    {
        $this->way = $way;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
