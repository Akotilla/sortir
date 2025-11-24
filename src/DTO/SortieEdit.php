<?php

namespace App\DTO;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Utilisateur;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SortieEdit
{

    private ?int $id = null;

    #[Assert\NotBlank(message: 'Nom obligatoire')]
    #[Assert\Length(max: 180, maxMessage: 'Maximum {{ limit }} characters please!')]
    private ?string $nom = null;

    #[Assert\NotBlank(message: 'Date de début obligatoire')]
    #[Assert\GreaterThanOrEqual(
        value: 'now',
        message: 'La date de début ne peut pas être dans le passé.'
    )]
    private ?\DateTimeImmutable $dateHeureDebut = null;

    #[Assert\NotBlank(message: 'Durée obligatoire')]
    private ?\DateTime $duree = null;

    #[Assert\NotBlank(message: 'Date limite d\'inscription obligatoire')]
    private ?\DateTimeImmutable $dateLimiteInscription = null;

    #[Assert\NotBlank(message: 'Nombre max d\'inscription obligatoire')]
    #[Assert\Positive(message: 'Le nombre maximum d\'inscriptions doit être supérieur à 0')]
    private ?int $nbInscriptionMax = null;

    #[Assert\NotBlank(message: 'Information obligatoire')]
    private ?string $infosSortie = null;

    private ?Lieu $lieu = null;

    private ?Etat $etat = null;

    private ?Campus $siteOrganisateur = null;

    private ?Utilisateur $organisePar = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    public function getDateHeureDebut(): ?\DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(?\DateTimeImmutable $dateHeureDebut): void
    {
        $this->dateHeureDebut = $dateHeureDebut;
    }

    public function getDuree(): ?\DateTime
    {
        return $this->duree;
    }

    public function setDuree(?\DateTime $duree): void
    {
        $this->duree = $duree;
    }

    public function getDateLimiteInscription(): ?\DateTimeImmutable
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(?\DateTimeImmutable $dateLimiteInscription): void
    {
        $this->dateLimiteInscription = $dateLimiteInscription;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(?int $nbInscriptionMax): void
    {
        $this->nbInscriptionMax = $nbInscriptionMax;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): void
    {
        $this->infosSortie = $infosSortie;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): void
    {
        $this->lieu = $lieu;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): void
    {
        $this->etat = $etat;
    }

    public function getSiteOrganisateur(): ?Campus
    {
        return $this->siteOrganisateur;
    }

    public function setSiteOrganisateur(?Campus $siteOrganisateur): void
    {
        $this->siteOrganisateur = $siteOrganisateur;
    }

    public function getOrganisePar(): ?Utilisateur
    {
        return $this->organisePar;
    }

    public function setOrganisePar(?Utilisateur $organisePar): void
    {
        $this->organisePar = $organisePar;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function setParticipants(Collection $participants): void
    {
        $this->participants = $participants;
    }



}