<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserCrudRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function listUsers(): array
    {
        return $this->findAll();
    }

    public function findUserById($id): ?User
    {
        return $this->find($id);
    }

    public function createUser(string $email, string $hashedPassword, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($hashedPassword);
        $user->setRoles($roles);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    public function deleteUser(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function updateUser(User $user): void
    {
        $this->getEntityManager()->flush();
    }
}