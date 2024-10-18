<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route(path: "/api/users", name:"api_users", methods: ["GET"])]
    public function getUsers(EntityManagerInterface $entityManager): JsonResponse
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route(path: "/api/users", methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['status' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'User created'], Response::HTTP_CREATED);
    }

    #[Route(path: "/api/users/change_password", name: "change_password", methods: ["PUT"])]
    public function changePassword(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['oldPassword']) || !isset($data['newPassword'])) {
            return $this->json(['message' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser(); // Récupérer l'utilisateur actuellement connecté
        if (!$user) {
            return $this->json(['message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier si l'ancien mot de passe est correct
        if (!$this->passwordHasher->isPasswordValid($user, $data['oldPassword'])) {
            return $this->json(['message' => 'Old password is incorrect'], Response::HTTP_BAD_REQUEST);
        }

        // Encoder le nouveau mot de passe
        $encodedPassword = $this->passwordHasher->hashPassword($user, $data['newPassword']);
        $user->setPassword($encodedPassword);

        // Sauvegarder dans la base de données
        $this->entityManager->flush();

        return $this->json(['message' => 'Password changed successfully'], Response::HTTP_OK);
    }

    #[Route(path: "/api/users/delete_account", name: "delete_account", methods: ["DELETE"])]
    public function deleteAccount(): Response
    {
        /** @var User $user */
        $user = $this->getUser(); // Récupérer l'utilisateur actuellement connecté
        if (!$user) {
            return $this->json(['message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'Account deleted successfully'], Response::HTTP_OK);
    }
}