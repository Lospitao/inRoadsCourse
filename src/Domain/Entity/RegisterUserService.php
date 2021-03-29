<?php
namespace App\Domain\Entity;
use App\Entity\User;
use App\Entity\UserRoles;
use Symfony\Component\Config\Definition\Exception\Exception;


class RegisterUserService
{

    private $request;
    private $user;
    private $form;
    private $userRole;
    private $flashBag;
    private $encodedPassword;
    private $entityManager;


    public function __construct($entityManager, $request,$user, $form, $flashBag, $encodedPassword)
    {
        $this->entityManager = $entityManager;
        $this->request = $request;
        $this->user = $user;
        $this->form = $form;
        $this->flashBag = $flashBag;
        $this->encodedPassword = $encodedPassword;
    }
    public function execute() {
        try {
                $this->lookForEmailInDataBase();
                $this->checkIfEmailExistsInDataBase();
                $this->setSignupDate();
                $this->userRole = $this->getUserRole();
                $this->setRole();
                $this->setEncodedPassword();
                $this->persistNewUserToDataBase();
                $this->throwSuccessMessage();

        } catch (\Exception $exception) {
            return $this->createErrorMessage($exception);
        }
    }

    private function lookForEmailInDataBase()
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email'=>$this->form->get('email')->getData()]);
    }
    private function checkIfEmailExistsInDataBase()
    {
        if ($this->lookForEmailInDataBase()) {
            throw new Exception("Ya existe un usuario registrado con este correo electrónico. Utilice otro correo electrónico.");
        }
    }

    private function setSignupDate()
    {
        $this->user->setSignupDate(new \DateTime());
    }
    private function getUserRole() {
        return $this->entityManager
            ->getRepository(UserRoles::class)
            ->findOneBy(['id' => UserRoles::ROLE_STUDENT]);
    }
    private function setRole()
    {
        $this->user->setRoles([$this->userRole->getRole()]);
    }
    private function setEncodedPassword()
    {
        $this->user->setPassword($this->encodedPassword);
    }
    private function throwSuccessMessage()
    {
        $this->flashBag->add('notice', 'Se ha registrado con éxito');
    }

    private function persistNewUserToDataBase()
    {
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }

    private function createErrorMessage(\Exception $exception)
    {
        $errorMessage=$exception->getMessage();
        return $this->flashBag->add('error', $errorMessage);
    }
}