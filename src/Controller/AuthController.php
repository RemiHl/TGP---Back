<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class AuthController extends AbstractController
{
    #[Route('/api/token', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Récupérer le CSRF
        $submittedCsrfToken = $data['_csrf_token'] ?? '';

        // Valider le CSRF
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $submittedCsrfToken))) {
            return new JsonResponse(['message' => 'Token CSRF invalide'], JsonResponse::HTTP_FORBIDDEN);
        }

        $email = $data['email'];
        $password = $data['password'];

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Identifiants incorrects'], 401);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/login/csrf-token', name: 'login_csrf_token', methods: ['GET'])]
    public function getLoginCsrfToken(CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $csrfToken = $csrfTokenManager->getToken('authenticate')->getValue();

        return new JsonResponse(['csrfToken' => $csrfToken]);
    }
}