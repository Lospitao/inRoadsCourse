<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserRoles;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    var $user;
    var $form;
    var $userRole;
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        try {
            $this->createNewUser();
            $this->generateForm($request);
            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $this->setSignupDate();
                $this->userRole = $this->getUserRole();
                $this->setRoles();
                $this->setEncodedPassword($passwordEncoder);
                $this->persistNewUserToDataBase();
                $this->addFlash('success', 'Se ha registrado con éxito');
                return $this->redirectToRoute('app_register');
            }
        } catch (\Doctrine\ORM\EntityNotFoundException $ex) {
            error_log($ex->getMessage());
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $this->form->createView(),
        ]);
    }

    private function createNewUser()
    {
        $this->user = new User();
    }

    private function generateForm($request)
    {
        $this->form = $this->createForm(RegistrationFormType::class, $this->user);
        $this->form->handleRequest($request);
    }

    private function setSignupDate()
    {
        $this->user->setSignupDate(new \DateTime());
    }
    private function getUserRole()
    {
        return $this->getDoctrine()
            ->getRepository(UserRoles::class)
            ->findOneBy(['id'=>UserRoles::ROLE_STUDENT]);
    }
    private function setRoles()
    {
        $this->user->setRoles([$this->userRole->getRole()]);
    }
    private function setEncodedPassword($passwordEncoder)
    {
        $this->user->setPassword(
            $passwordEncoder->encodePassword(
                $this->user,
                $this->form->get('plainPassword')->getData()
            )
        );
    }

    private function persistNewUserToDataBase()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($this->user);
        $entityManager->flush();
    }




}
