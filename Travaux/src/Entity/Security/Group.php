<?php

namespace AcMarche\Travaux\Entity\Security;

use AcMarche\Travaux\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: 'fos_group')]
class Group implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;
    #[ORM\Column(type: 'string', nullable: true)]
    protected string $title;
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description;
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    protected Collection $users;

    #[ORM\Column(type: 'text',length: 180, unique: true)]
    private string $name;

    #[ORM\Column(type: 'array')]
    private array $roles;

    /**
     * Group constructor.
     * @param string $name
     * @param array $roles
     */
    public function __construct(string $name, $roles = array())
    {
        $this->users = new ArrayCollection();
        $this->name = $name;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role): static
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = strtoupper($role);
        }

        return $this;
    }

    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->getName().' ('.$this->getDescription().')';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): array|ArrayCollection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addGroup($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeGroup($this);
        }

        return $this;
    }
}
