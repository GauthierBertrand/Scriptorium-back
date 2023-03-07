<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\Table(name="`group`")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"group_get_information"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups({"group_get_information"})
     * @Assert\NotBlank(
     *  message = "Le groupe doit avoir un nom."
     * )
     * @Assert\Length(
     *  max = 64,
     *  maxMessage = "Le nom du groupe ne doit pas dépasser {{ limit }} caractères."
     * )
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="groups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game_master;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="playerGroups")
     * @Groups({"group_get_information"})
     */
    private $players;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"group_get_information"})
     */
    private $code_register;

    public function __construct()
    {
        $this->players = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(User $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
        }

        return $this;
    }

    public function removePlayer(User $player): self
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getGameMaster(): ?User
    {
        return $this->game_master;
    }

    public function setGameMaster(?User $game_master): self
    {
        $this->game_master = $game_master;

        return $this;
    }

    public function getCodeRegister(): ?string
    {
        return $this->code_register;
    }

    public function setCodeRegister(string $code_register): self
    {
        $this->code_register = $code_register;

        return $this;
    }

    /**
     * @Assert\Callback()
     */
    public static function validateName($object, ExecutionContextInterface $context, $payload)
    {
        $game_master = $object->getGameMaster();

        foreach ($game_master->getGroups() as $group) {
            if (strtolower($group->getName()) === strtolower($object->getName())) {
                
                if ($group === $object) {
                    continue;
                }

                $context->buildViolation("Ce nom est déjà pris.")
                    ->atPath("name")
                    ->addViolation();
                break;
            }    
        }
    }

    /**
     * @Assert\Callback()
     */
    public static function validateAssociation($object, ExecutionContextInterface $context, $payload) 
    {
        $players = $object->getPlayers();

        if ($players->contains($object->getGameMaster())) {
            $context->buildViolation("Le GM ne peut pas faire partie de son propre groupe")
                ->atPath("players")
                ->addViolation();
        }
    }
}
