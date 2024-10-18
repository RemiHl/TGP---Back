<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ContactController extends AbstractController
{
    #[Route('/api/contact', name: 'contact_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createContact(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Sanitize l'email et le message
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL); // Nettoye l'email
        $message = htmlspecialchars($data['message']); // Échappe les caractères spéciaux 

        // Vérifier que l'email est valide
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['message' => 'Email invalide'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Récupérer l'utilisateur connecté
        $connectedUser = $this->getUser();

        // Créer un nouvel objet Contact
        $contact = new Contact();
        $contact->setEmail($email);
        $contact->setMessage($message);
        $contact->setConnectedUser($connectedUser);

        // Sauvegarder dans la base de données
        $entityManager->persist($contact);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Message envoyé avec succès.'], 201);
    }
}