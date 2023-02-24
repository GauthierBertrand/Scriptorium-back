<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass=EquipmentRepository::class)
 */
class Equipment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * 
     * @Groups({"classes_get_collection"})
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"classes_get_collection"})
     */
    private $description;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $damage = [];

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $attack_type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $hand;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $distance;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $bonus = [];

    /**
     * @ORM\OneToMany(targetEntity=ClasseEquipment::class, mappedBy="equipment")
     */
    private $classeEquipment;

    public function __construct()
    {
        $this->classeEquipment = new ArrayCollection();
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

    public function getDamage(): ?array
    {
        return $this->damage;
    }

    public function setDamage(array $damage): self
    {
        $this->damage = $damage;

        return $this;
    }

    public function getAttackType(): ?string
    {
        return $this->attack_type;
    }

    public function setAttackType(string $attack_type): self
    {
        $this->attack_type = $attack_type;

        return $this;
    }

    public function getHand(): ?int
    {
        return $this->hand;
    }

    public function setHand(?int $hand): self
    {
        $this->hand = $hand;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

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
            $classeEquipment->setEquipment($this);
        }

        return $this;
    }

    public function removeClasseEquipment(ClasseEquipment $classeEquipment): self
    {
        if ($this->classeEquipment->removeElement($classeEquipment)) {
            // set the owning side to null (unless already changed)
            if ($classeEquipment->getEquipment() === $this) {
                $classeEquipment->setEquipment(null);
            }
        }

        return $this;
    }
}
