<?php

namespace App\Controller;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    #[Route('/api/services', name: 'service_list', methods: ['GET'])]
    public function listServices(EntityManagerInterface $entityManager): JsonResponse
    {
        $services = $entityManager->getRepository(Service::class)->findAll();
        $data = [];

        foreach ($services as $service) {
            $data[] = [
                'id' => $service->getId(),
                'nom' => $service->getNomDuService(), // Assurez-vous que 'getName' correspond à la méthode correcte dans votre entité 'Service'
            ];
        }

        return new JsonResponse($data, 200);
    }
}