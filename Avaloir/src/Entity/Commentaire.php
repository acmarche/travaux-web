<?php


namespace AcMarche\Avaloir\Entity;

use AcMarche\Avaloir\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ORM\Table(name: 'commentaire')]
class Commentaire implements TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;
    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\Length(min: 2)]
    protected string $content;

    #[ORM\ManyToOne(targetEntity: 'Avaloir', inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    protected Avaloir $avaloir;

    public function __construct(
        Avaloir $avaloir
    ) {
        $this->avaloir = $avaloir;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAvaloir(): ?Avaloir
    {
        return $this->avaloir;
    }

    public function setAvaloir(?Avaloir $avaloir): self
    {
        $this->avaloir = $avaloir;

        return $this;
    }
}
