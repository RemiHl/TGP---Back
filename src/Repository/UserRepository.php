<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    private $passwordHasher;

    public function __construct(ManagerRegistry $registry, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($registry, User::class);
        $this->passwordHasher = $passwordHasher;
    }

    public function createUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        // Utiliser l'EntityManager correctement
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Utiliser l'EntityManager correctement
        $this->getEntityManager()->flush();
    }

    public function deleteUser(User $user): void
    {
        // Utiliser l'EntityManager correctement
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function findByEmail($email)
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}