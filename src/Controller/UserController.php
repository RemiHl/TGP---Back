<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    private $userRepository;
    private $passwordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route(path: "/api/users", name:"api_users", methods: ["GET"])]
    public function getUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
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
    public function createUser(Request $request, ValidatorInterface $validator, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['status' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->createUser($data['email'], $data['password']);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['status' => 'Validation failed', 'errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $token = $jwtManager->create($user);
        error_log('Token: ' . $token);

        return new JsonResponse(['status' => 'User created', 'token' => $token], Response::HTTP_CREATED, ['Access-Control-Allow-Origin' => '*']);
    }

    #[Route(path: "/api/users/change_password", name: "change_password", methods: ["PUT"])]
    public function changePassword(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['oldPassword']) || !isset($data['newPassword'])) {
            return $this->json(['message' => 'Invalid input'], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['oldPassword'])) {
            return $this->json(['message' => 'Old password is incorrect'], Response::HTTP_BAD_REQUEST);
        }

        $this->userRepository->updatePassword($user, $data['newPassword']);

        return $this->json(['message' => 'Password changed successfully'], Response::HTTP_OK);
    }

    #[Route(path: "/api/users/delete_account", name: "delete_account", methods: ["DELETE"])]
    public function deleteAccount(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $this->userRepository->deleteUser($user);

        return $this->json(['message' => 'Account deleted successfully'], Response::HTTP_OK);
    }
}