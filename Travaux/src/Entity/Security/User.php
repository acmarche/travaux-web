<?php

namespace AcMarche\Travaux\Entity\Security;

use AcMarche\Travaux\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, Stringable, PasswordAuthenticatedUserInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'string', unique: true, length: 180)]
    protected string $username;

    #[ORM\Column(type: 'string')]
    protected string $password;

    protected ?string $plainPassword;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected string $email;
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'fos_user_group')]
    protected Collection $groups;

    #[ORM\Column(type: 'array')]
    protected array $roles;

    #[ORM\Column(type: 'string', nullable: false)]
    #[ORM\OrderBy(['intitule' => 'ASC'])]
    #[Assert\Length(min: 3)]
    protected string $nom;
    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\Length(min: 3)]
    protected ?string $prenom;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $accord = null;
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $accord_date = null;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
    protected ?string $token;

    #[ORM\Column(type: 'boolean', nullable: false)]
    public ?bool $notification = false;

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function __construct()
    {
        $this->roles = [];
        $this->groups = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role): static
    {
        $role = strtoupper($role);
        if ($role === 'ROLE_USER') {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    public function getSalt(): void
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAccord(): ?bool
    {
        return $this->accord;
    }

    public function setAccord(?bool $accord): self
    {
        $this->accord = $accord;

        return $this;
    }

    public function getAccordDate(): ?DateTimeInterface
    {
        return $this->accord_date;
    }

    public function setAccordDate(?DateTimeInterface $accord_date): self
    {
        $this->accord_date = $accord_date;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection|Group[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }
}
