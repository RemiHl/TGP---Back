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

class AuthController extends AbstractController
{
    #[Route('/api/token', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $password = $data['password'];

        // Récupérer l'utilisateur par l'email
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        // Vérifier que l'utilisateur existe et que le mot de passe est valide
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Identifiants incorrects'], 401);
        }

        // Générer le token JWT pour l'utilisateur
        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}