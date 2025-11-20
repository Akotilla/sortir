<?php

namespace App\DTO;

use App\Entity\Campus;
use Symfony\Component\Validator\Constraints as Assert;

class SortieFiltre
{
    #[Assert\NotBlank()]
    private ?Campus $siteOrganisateur  = null;

    private ?string $nom = null;

    private ?\DateTimeInterface $dateHeureDebut = null;

    private ?\DateTimeInterface $dateLimiteInscription = null;

    private ?bool $organisePar = false;
    private ?bool $participe = false;
    private ?bool $participePas = false;
    private ?bool $termine = false;

    public function getSiteOrganisateur (): ?Campus
    {
        return $this->siteOrganisateur ;
    }

    public function setSiteOrganisateur (?Campus $siteOrganisateur ): void
    {
        $this->siteOrganisateur  = $siteOrganisateur ;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(?\DateTimeInterface $dateHeureDebut): void
    {
        $this->dateHeureDebut = $dateHeureDebut;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(?\DateTimeInterface $dateLimiteInscription): void
    {
        $this->dateLimiteInscription = $dateLimiteInscription;
    }



    public function getOrganisePar(): ?bool
    {
        return $this->organisePar;
    }

    public function setOrganisePar(?bool $organisePar): void
    {
        $this->organisePar = $organisePar;
    }

    public function getParticipe(): ?bool
    {
        return $this->participe;
    }

    public function setParticipe(?bool $participe): void
    {
        $this->participe = $participe;
    }

    public function getParticipePas(): ?bool
    {
        return $this->participePas;
    }

    public function setParticipePas(?bool $participePas): void
    {
        $this->participePas = $participePas;
    }

    public function getTermine(): ?bool
    {
        return $this->termine;
    }

    public function setTermine(?bool $termine): void
    {
        $this->termine = $termine;
    }




}