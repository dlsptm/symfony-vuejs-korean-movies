<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    private $manager;
    private $user;

    public function __construct(EntityManagerInterface $manager, UserRepository $user) {
        $this->manager = $manager;
        $this->user = $user;
    }


    /**
     * Création d'un utilisateur
     *
     * @return Response
     */
    #[Route('/user', name: 'app_user')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);
    
    
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['roles'])) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Missing email, roles or password in request data.'
                ]
            );
        }
    
        $email = $data['email'];
        $password = $data['password'];
        $roles = $data['roles'];
    
        $email_exists = $this->user->findOneByEmail($email);
    
        if ($email_exists) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Un compte avec cet email existe déjà.'
                ]
            );
        }
    
        $user = new User;
        $user->setEmail($email);
        $user->setRoles([$roles]);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
    
        $this->manager->persist($user);
        $this->manager->flush();
    
        return new JsonResponse(
            [
                'status' => true,
                'message' => 'Votre compte a bien été crée.'
            ]
        );
    }

    /**
     * Liste de tous les utilisateurs
     *
     * @return Response
     */
    #[Route('/alluser', name: 'app_user_all', methods: ['POST'])]
     public function getAllUsers ():Response
    {
        $users = $this->user->findAll();

        return new JsonResponse(
            [
                'status' => true,
                'user' => $users
            ]
        );
    }
}
