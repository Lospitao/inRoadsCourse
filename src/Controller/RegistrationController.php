<?php

namespace App\Controller;

use App\Domain\Entity\RegisterUserService;
use App\Entity\User;

use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    var $user;
    var $form;
    var $registerService;
    var $entityManager;
    var $flashBag;
    var $encodedPassword;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        try {
            $this->createNewUser();
            $this->flashBag = $this->getFlashbag();
            $this->generateForm($request);
            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $this->encodedPassword = $this->encodePassword($passwordEncoder);
                $this->registerService = new RegisterUserService($this->entityManager, $request, $this->user, $this->form, $this->flashBag, $this->encodedPassword);
                $this->registerService->execute();
                return $this->redirectToRoute('app_login');
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

    private function getFlashbag()
    {
       return $this->get('session')->getFlashBag();
    }

    private function encodePassword($passwordEncoder)
    {
            return $passwordEncoder->encodePassword(
                $this->user,
                $this->form->get('plainPassword')->getData());
    }
}
