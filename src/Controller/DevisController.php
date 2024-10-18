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

        // Créer une nouvelle instance de Devis
        $devis = new Devis();
        $devis->setNom($data['nom'] ?? null);
        $devis->setPrenom($data['prenom'] ?? null);
        $devis->setEmail($data['email']);
        $devis->setEntreprise($data['entreprise']);
        $devis->setLocalisation($data['localisation']);

        // Récupérer les services sélectionnés et stocker les noms des services
        $services = $entityManager->getRepository(Service::class)->findBy(['id' => $data['services']]);
        $serviceNames = [];

        foreach ($services as $service) {
            $devis->addService($service); // On associe chaque service au devis
            $serviceNames[] = $service->getNomDuService(); // Stocker les noms des services
        }

        // Sauvegarder les noms des services sous forme de chaîne de caractères dans `servicesNames`
        $devis->setServicesNames(implode(', ', $serviceNames));

        // Sauvegarder le devis dans la base de données
        $entityManager->persist($devis);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Devis envoyé avec succès'], 201);
    }
}