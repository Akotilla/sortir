<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\LieuRepository;
use Symfony\Component\HttpFoundation\JsonResponse;


class LieuApiController extends AbstractController
{
    #[Route('/api/lieu/{id}', name: 'api_lieu_get', methods: ['GET'])]
    public function getLieu(int $id, LieuRepository $lieuRepo): JsonResponse
    {
        $lieu = $lieuRepo->find($id);

        if (!$lieu) {
            return new JsonResponse(['error' => 'Lieu introuvable'], 404);
        }

        return new JsonResponse([
            'id' => $lieu->getId(),
            'nom' => $lieu->getNom(),
            'rue' => $lieu->getRue(),
            'codePostal' => $lieu->getVille()->getCodePostal(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude(),
        ]);
    }
}