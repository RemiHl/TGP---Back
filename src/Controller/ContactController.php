<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ContactController extends AbstractController
{
    #[Route('/api/contact', name: 'contact_create', methods: ['POST'])]
    public function createContact(
        Request $request,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Récupérer le CSRF
        $submittedCsrfToken = $data['_csrf_token'] ?? '';

        // Valider le CSRF
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('contact_form', $submittedCsrfToken))) {
            return new JsonResponse(['message' => 'Token CSRF invalide'], JsonResponse::HTTP_FORBIDDEN);
        }

        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $message = htmlspecialchars($data['message']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['message' => 'Email invalide'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $connectedUser = $this->getUser();

        $contact = new Contact();
        $contact->setEmail($email);
        $contact->setMessage($message);
        $contact->setConnectedUser($connectedUser);

        $entityManager->persist($contact);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Message envoyé avec succès.'], 201);
    }

    #[Route('/api/contact/csrf-token', name: 'get_csrf_token', methods: ['GET'])]
    public function getCsrfToken(CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $csrfToken = $csrfTokenManager->getToken('contact_form')->getValue();

        return new JsonResponse(['csrfToken' => $csrfToken]);
    }

    #[Route('/api/admin/contact', name: 'contact_list', methods: ['GET'])]
    public function listContacts(EntityManagerInterface $entityManager): JsonResponse
    {
        $contacts = $entityManager->getRepository(Contact::class)->findAll();

        $contactData = [];
        foreach ($contacts as $contact) {
            $contactData[] = [
                'id' => $contact->getId(),
                'email' => $contact->getEmail(),
                'message' => $contact->getMessage(),
                'connectedUser' => $contact->getConnectedUser()->getId(),
            ];
        }

        return new JsonResponse($contactData);
    }
}