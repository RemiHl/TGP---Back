<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DevisController extends AbstractController
{
    #[Route('/api/devis', name: 'create_devis', methods: ['POST'])]
    public function createDevis(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $devis = new Devis();
        $devis->setNom($data['nom'] ?? null);
        $devis->setPrenom($data['prenom'] ?? null);
        $devis->setEmail($data['email']);
        $devis->setEntreprise($data['entreprise']);
        $devis->setLocalisation($data['localisation']);

        $services = $entityManager->getRepository(Service::class)->findBy(['id' => $data['services']]);
        $serviceNames = [];

        foreach ($services as $service) {
            $devis->addService($service);
            $serviceNames[] = $service->getNomDuService();
        }

        $devis->setServicesNames(implode(', ', $serviceNames));

        $entityManager->persist($devis);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Devis envoyé avec succès'], 201);
    }
}