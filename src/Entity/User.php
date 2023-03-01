<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * 
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(
     *  message = "Le champ email ne peut pas être vide."
     * )
     * @Assert\Email(
     *  message = "L'email {{ value }} n'est pas un email valide."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *  message = "Le champ password ne peut pas être vide."
     * )
     * @Assert\Length(
     *  min = 8,
     *  max = 64,
     *  minMessage = "Le password doit être de '{{ limit }}' caractères minimum.",
     *  maxMessage = "Le password doit être de '{{ limit }}' caractères maximum."
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotNull(
     *  message = "Le champ pseudo ne peut pas être null."
     * )
     * @Assert\Length(
     *  min = 1,
     *  max = 64,
     *  minMessage = "Le champ pseudo doit être de '{{ limit }}' caractères minimum.",
     *  maxMessage = "Le champ pseudo doit être de '{{ limit }}' caractères maximum."
     * )
     */
    private $pseudo;

    /**
     * @ORM\OneToMany(targetEntity=Sheet::class, mappedBy="user")
     */
    private $sheets;

    /**
     * @ORM\OneToMany(targetEntity=Token::class, mappedBy="user", orphanRemoval=true)
     */
    private $tokens;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_verified = false;

    /**
     * @ORM\OneToMany(targetEntity=Group::class, mappedBy="game_master")
     */
    private $groups;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class, mappedBy="players")
     */
    private $playerGroups;


    public function __construct()
    {
        $this->sheets = new ArrayCollection();
        $this->tokens = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->playerGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

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
            $sheet->setUser($this);
        }

        return $this;
    }

    public function removeSheet(Sheet $sheet): self
    {
        if ($this->sheets->removeElement($sheet)) {
            // set the owning side to null (unless already changed)
            if ($sheet->getUser() === $this) {
                $sheet->setUser(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getPseudo();
    }

    /**
     * @return Collection<int, Token>
     */
    public function getTokens(): Collection
    {
        return $this->tokens;
    }

    public function addToken(Token $token): self
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens[] = $token;
            $token->setUser($this);
        }

        return $this;
    }

    public function removeToken(Token $token): self
    {
        if ($this->tokens->removeElement($token)) {
            // set the owning side to null (unless already changed)
            if ($token->getUser() === $this) {
                $token->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of is_verified
     */ 
    public function getIsVerified(): ?bool
    {
        return $this->is_verified;
    }

    /**
     * Set the value of is_verified
     *
     * @return  self
     */ 
    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
            $group->setGameMaster($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->removeElement($group)) {
            // set the owning side to null (unless already changed)
            if ($group->getGameMaster() === $this) {
                $group->setGameMaster(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getPlayerGroups(): Collection
    {
        return $this->playerGroups;
    }

    public function addPlayerGroup(Group $playerGroup): self
    {
        if (!$this->playerGroups->contains($playerGroup)) {
            $this->playerGroups[] = $playerGroup;
            $playerGroup->addPlayer($this);
        }

        return $this;
    }

    public function removePlayerGroup(Group $playerGroup): self
    {
        if ($this->playerGroups->removeElement($playerGroup)) {
            $playerGroup->removePlayer($this);
        }

        return $this;
    }
}
