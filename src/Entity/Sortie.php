<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Nom obligatoire')]
    #[Assert\Length(max: 180, maxMessage: 'Maximum {{ limit }} characters please!')]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Date de début obligatoire')]
    #[Assert\GreaterThanOrEqual(
        value: 'now',
        message: 'La date de début ne peut pas être dans le passé.'
    )]
    private ?\DateTimeImmutable $dateHeureDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank(message: 'Durée obligatoire')]
    private ?\DateTime $duree = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Date limite d\'inscription obligatoire')]
   private ?\DateTimeImmutable $dateLimiteInscription = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Nombre max d\'inscription obligatoire')]
    #[Assert\Positive(message: 'Le nombre maximum d\'inscriptions doit être supérieur à 0')]
    private ?int $nbInscriptionMax = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Information obligatoire')]
    private ?string $infosSortie = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    private ?Lieu $lieu = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $siteOrganisateur = null;

    #[ORM\ManyToOne(inversedBy: 'organisateur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $organisePar = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'participant')]
    private Collection $participants;

    #[Assert\Callback]
    public function validateDateLimiteInscription(ExecutionContextInterface $context): void
    {
        if ($this->dateLimiteInscription !== null && $this->dateHeureDebut !== null) {
            if ($this->dateLimiteInscription < $this->dateHeureDebut) {
                $context->buildViolation('La date limite doit être supérieure ou égale à la date de début.')
                    ->atPath('dateLimiteInscription')
                    ->addViolation();
            }
        }
    }

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?\DateTime
    {
        return $this->duree;
    }

    public function setDuree(\DateTime $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeImmutable
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeImmutable $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(int $nbInscriptionMax): static
    {
        $this->nbInscriptionMax = $nbInscriptionMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getSiteOrganisateur(): ?Campus
    {
        return $this->siteOrganisateur;
    }

    public function setSiteOrganisateur(?Campus $siteOrganisateur): static
    {
        $this->siteOrganisateur = $siteOrganisateur;

        return $this;
    }

    public function getOrganisePar(): ?Utilisateur
    {
        return $this->organisePar;
    }

    public function setOrganisePar(?Utilisateur $organisePar): static
    {
        $this->organisePar = $organisePar;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Utilisateur $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipant(Utilisateur $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeParticipant($this);
        }

        return $this;
    }

}
