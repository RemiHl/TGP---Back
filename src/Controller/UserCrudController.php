<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UserCrudController extends AbstractController
{
    #[Route('/api/admin/users', name: 'admin_users_list', methods: ['GET'])]
    public function listUsers(EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $entityManager->getRepository(User::class)->findAll();
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
    public function deleteUser(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User deleted successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/admin/users/{id}', name: 'admin_users_update', methods: ['PUT'])]
    public function updateUserEmail(EntityManagerInterface $entityManager, Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $newEmail = $data['email'];

        if (!$newEmail) {
            return new JsonResponse(['message' => 'Invalid email'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->setEmail($newEmail);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Email updated successfully'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/admin/users', name: 'admin_users_create', methods: ['POST'])]
    public function createUser(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $password = $data['password'];

        if (!$email || !$password) {
            return new JsonResponse(['message' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($email);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User created successfully'], JsonResponse::HTTP_CREATED);
    }
}