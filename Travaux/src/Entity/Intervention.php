<?php

namespace AcMarche\Travaux\Entity;

use AcMarche\Travaux\Repository\InterventionRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
#[ORM\Table(name: 'intervention')]
class Intervention implements TimestampableInterface, Stringable
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    #[ORM\OrderBy(['intitule' => 'ASC'])]
    #[Assert\NotBlank]
    protected string $intitule;
    #[ORM\ManyToOne(targetEntity: Etat::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    protected Etat $etat;
    #[ORM\ManyToOne(targetEntity: Priorite::class, inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    protected Priorite $priorite;
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    protected bool $transmis = false;
    #[ORM\Column(type: 'date', nullable: false)]
    protected DateTimeInterface $date_introduction;
    #[ORM\Column(type: 'date', nullable: true)]
    protected ?DateTimeInterface $date_rappel;
    #[ORM\Column(type: 'date', nullable: true)]
    protected ?DateTimeInterface $date_execution;
    #[ORM\Column(type: 'text', nullable: false)]
    protected string $descriptif;
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $affectation = null;
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    protected bool $affecte_prive = false;
    #[ORM\Column(type: 'date', nullable: true)]
    protected ?DateTimeInterface $soumis_le;
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $solution;
    #[ORM\Column(type: 'date', nullable: true)]
    protected ?DateTimeInterface $date_solution;
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    protected bool $archive = false;
    #[ORM\Column(type: 'decimal', precision: 9, scale: 2, nullable: true)]
    protected ?float $cout_main = 0;
    #[ORM\Column(type: 'decimal', precision: 9, scale: 2, nullable: true)]
    protected ?float $cout_materiel = 0;
    #[ORM\Column(type: 'date', nullable: true)]
    protected ?DateTimeInterface $date_validation;
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    protected bool $smartphone = false;
    #[ORM\Column(type: 'string', nullable: false)]
    protected string $user_add;
    #[ORM\ManyToOne(targetEntity: Domaine::class, inversedBy: 'intervention')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Domaine $domaine;
    #[ORM\ManyToOne(targetEntity: Batiment::class, inversedBy: 'intervention')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Batiment $batiment;
    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'intervention')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Service $service;
    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'intervention')]
    #[ORM\JoinColumn(nullable: false)]
    protected Categorie $categorie;
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'intervention', cascade: ['remove'])]
    protected Collection $documents;
    #[ORM\OneToMany(targetEntity: Suivi::class, mappedBy: 'intervention', cascade: ['remove'])]
    #[ORM\OrderBy(['id' => 'DESC'])]
    protected Collection $suivis;
    /**
     * This property is used by the marking store
     */
    #[ORM\Column(type: 'string', nullable: true)]
    public ?string $currentPlace;

    #[ORM\ManyToOne(targetEntity: Horaire::class, inversedBy: 'intervention')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?Horaire $horaire;

    public function __toString(): string
    {
        return $this->intitule;
    }

    /**
     * @var Suivi|null
     */
    protected $lastSuivi;

    /**
     * @return Suivi
     */
    public function getLastSuivi(): ?Suivi
    {
        return $this->lastSuivi;
    }

    /**
     * @param Suivi $lastSuivis
     */
    public function setLastSuivi(?Suivi $lastSuivi): void
    {
        $this->lastSuivi = $lastSuivi;
    }

    public function __construct()
    {
        $this->date_introduction = new DateTime();
        $this->documents = new ArrayCollection();
        $this->suivis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): self
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getTransmis(): bool
    {
        return $this->transmis;
    }

    public function setTransmis(bool $transmis): self
    {
        $this->transmis = $transmis;

        return $this;
    }

    public function getDateIntroduction(): ?DateTimeInterface
    {
        return $this->date_introduction;
    }

    public function setDateIntroduction(DateTimeInterface $date_introduction): self
    {
        $this->date_introduction = $date_introduction;

        return $this;
    }

    public function getDateRappel(): ?DateTimeInterface
    {
        return $this->date_rappel;
    }

    public function setDateRappel(?DateTimeInterface $date_rappel): self
    {
        $this->date_rappel = $date_rappel;

        return $this;
    }

    public function getDateExecution(): ?DateTimeInterface
    {
        return $this->date_execution;
    }

    public function setDateExecution(?DateTimeInterface $date_execution): self
    {
        $this->date_execution = $date_execution;

        return $this;
    }

    public function getDescriptif(): ?string
    {
        return $this->descriptif;
    }

    public function setDescriptif(string $descriptif): self
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    public function getAffectation(): ?string
    {
        return $this->affectation;
    }

    public function setAffectation(?string $affectation): self
    {
        $this->affectation = $affectation;

        return $this;
    }

    public function getAffectePrive(): bool
    {
        return $this->affecte_prive;
    }

    public function setAffectePrive(bool $affecte_prive): self
    {
        $this->affecte_prive = $affecte_prive;

        return $this;
    }

    public function getSoumisLe(): ?DateTimeInterface
    {
        return $this->soumis_le;
    }

    public function setSoumisLe(?DateTimeInterface $soumis_le): self
    {
        $this->soumis_le = $soumis_le;

        return $this;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function setSolution(?string $solution): self
    {
        $this->solution = $solution;

        return $this;
    }

    public function getDateSolution(): ?DateTimeInterface
    {
        return $this->date_solution;
    }

    public function setDateSolution(?DateTimeInterface $date_solution): self
    {
        $this->date_solution = $date_solution;

        return $this;
    }

    public function getArchive(): bool
    {
        return $this->archive;
    }

    public function setArchive(bool $archive): self
    {
        $this->archive = $archive;

        return $this;
    }

    public function getCoutMain(): ?string
    {
        return $this->cout_main;
    }

    public function setCoutMain(?string $cout_main): self
    {
        $this->cout_main = $cout_main;

        return $this;
    }

    public function getCoutMateriel(): ?string
    {
        return $this->cout_materiel;
    }

    public function setCoutMateriel(?string $cout_materiel): self
    {
        $this->cout_materiel = $cout_materiel;

        return $this;
    }

    public function getDateValidation(): ?DateTimeInterface
    {
        return $this->date_validation;
    }

    public function setDateValidation(?DateTimeInterface $date_validation): self
    {
        $this->date_validation = $date_validation;

        return $this;
    }

    public function getSmartphone(): bool
    {
        return $this->smartphone;
    }

    public function setSmartphone(bool $smartphone): self
    {
        $this->smartphone = $smartphone;

        return $this;
    }

    public function getUserAdd(): ?string
    {
        return $this->user_add;
    }

    public function setUserAdd(string $user_add): self
    {
        $this->user_add = $user_add;

        return $this;
    }

    public function getCurrentPlace(): ?string
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace(?string $currentPlace): self
    {
        $this->currentPlace = $currentPlace;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getPriorite(): ?Priorite
    {
        return $this->priorite;
    }

    public function setPriorite(?Priorite $priorite): self
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getDomaine(): ?Domaine
    {
        return $this->domaine;
    }

    public function setDomaine(?Domaine $domaine): self
    {
        $this->domaine = $domaine;

        return $this;
    }

    public function getBatiment(): ?Batiment
    {
        return $this->batiment;
    }

    public function setBatiment(?Batiment $batiment): self
    {
        $this->batiment = $batiment;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): array|Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setIntervention($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            // set the owning side to null (unless already changed)
            if ($document->getIntervention() === $this) {
                $document->setIntervention(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Suivi[]
     */
    public function getSuivis(): array|Collection
    {
        return $this->suivis;
    }

    public function addSuivi(Suivi $suivi): self
    {
        if (!$this->suivis->contains($suivi)) {
            $this->suivis[] = $suivi;
            $suivi->setIntervention($this);
        }

        return $this;
    }

    public function removeSuivi(Suivi $suivi): self
    {
        if ($this->suivis->contains($suivi)) {
            $this->suivis->removeElement($suivi);
            // set the owning side to null (unless already changed)
            if ($suivi->getIntervention() === $this) {
                $suivi->setIntervention(null);
            }
        }

        return $this;
    }
}
