<?php

namespace App\DTO;

class AdminFiltre
{
    private ?string $nomVille = null;

    private ?string $nomCampus = null;

    public function getNomVille(): ?string
    {
        return $this->nomVille;
    }

    public function setNomVille(?string $nomVille): void
    {
        $this->nomVille = $nomVille;
    }

    public function getNomCampus(): ?string
    {
        return $this->nomCampus;
    }

    public function setNomCampus(?string $nomCampus): void
    {
        $this->nomCampus = $nomCampus;
    }

    public function getNomUtilisateur(): ?string
    {
        return $this->nomUtilisateur;
    }

    public function setNomUtilisateur(?string $nomUtilisateur): void
    {
        $this->nomUtilisateur = $nomUtilisateur;
    }

    private ?string $nomUtilisateur = null;
}