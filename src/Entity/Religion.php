<?php

namespace App\Entity;

use App\Repository\ReligionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ReligionRepository::class)
 */
class Religion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups({"religions_get_collection"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"religions_get_collection"})
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"religions_get_collection"})
     */
    private $alignment;

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

    public function isAlignment(): ?bool
    {
        return $this->alignment;
    }

    public function setAlignment(bool $alignment): self
    {
        $this->alignment = $alignment;

        return $this;
    }
}
