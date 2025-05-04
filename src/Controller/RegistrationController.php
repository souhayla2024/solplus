<?php
namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupération des données
        $nom = $request->get('nom');
        $prenom = $request->get('prenom');
        $email = $request->get('email');
        $plainPassword = $request->get('plainPassword');

        // Préparer les données dans un tableau
        $data = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'plainPassword' => $plainPassword,
        ];

        // Définir les contraintes de validation
        $constraints = new Assert\Collection([
            'nom' => [new Assert\NotBlank(), new Assert\Length(['min' => 3])],
            'prenom' => [new Assert\NotBlank(), new Assert\Length(['min' => 3])],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'plainPassword' => [new Assert\NotBlank(), new Assert\Length(['min' => 8])],
        ]);

        // Valider les données
        $validator = Validation::createValidator();
        $violations = $validator->validate($data, $constraints);

        // Gérer les erreurs
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        if (!empty($errors)) {
            return $this->render('administration/register.html.twig', [
                'errors' => $errors,
                'data' => $data,
            ]);
        }

        // Créer l'utilisateur
        $user = new Utilisateur();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_login');
    }
}
