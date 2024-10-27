<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserCrudRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserCrudController extends AbstractController
{
    private $userCrudRepository;

    public function __construct(UserCrudRepository $userCrudRepository)
    {
        $this->userCrudRepository = $userCrudRepository;
    }

    #[Route('/api/admin/users', name: 'admin_users_list', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->userCrudRepository->listUsers();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/admin/users/{id}', name: 'admin_users_delete', methods: ['DELETE'])]
    public function deleteUser($id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->userCrudRepository->findUserById($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->userCrudRepository->deleteUser($user);

        return new JsonResponse(['message' => 'User deleted successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/admin/users/{id}', name: 'admin_users_update', methods: ['PUT'])]
    public function updateUserEmail(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $this->userCrudRepository->findUserById($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $newEmail = $data['email'] ?? null;

        if (!$newEmail) {
            return new JsonResponse(['message' => 'Invalid email'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->setEmail($newEmail);
        $this->userCrudRepository->updateUser($user);

        return new JsonResponse(['message' => 'Email updated successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/admin/users', name: 'admin_users_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['message' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Hachage du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Créer l'utilisateur via le UserCrudRepository
        $this->userCrudRepository->createUser($email, $hashedPassword, ['ROLE_USER']);

        return new JsonResponse(['message' => 'User created successfully'], JsonResponse::HTTP_CREATED);
    }
}