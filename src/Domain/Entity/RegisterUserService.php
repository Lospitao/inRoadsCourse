<?php
namespace App\Domain\Entity;
use App\Entity\User;
use App\Entity\UserRoles;
use Symfony\Component\Config\Definition\Exception\Exception;


class RegisterUserService
{



    private $flashBag;
    private $entityManager;
    private $registrationParameters;


    public function __construct($entityManager, $registrationParameters, $flashBag)
    {
        $this->entityManager = $entityManager;
        $this->registrationParameters = $registrationParameters;
        $this->flashBag = $flashBag;
    }
    public function execute() {
        try {
                $this->lookForEmailInDataBase();
                $this->checkIfEmailExistsInDataBase();
                $this->setSignupDate();
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
            ->findOneBy(['email'=>$this->registrationParameters['form']->get('email')->getData()]);
    }
    private function checkIfEmailExistsInDataBase()
    {
        if ($this->lookForEmailInDataBase()) {
            throw new Exception("Ya existe un usuario registrado con este correo electrónico. Utilice otro correo electrónico.");
        }
    }

    private function setSignupDate()
    {
        $this->registrationParameters['user']->setSignupDate(new \DateTime());
    }

    private function setRole()
    {
        $this->registrationParameters['user']->setRoles([$this->registrationParameters['userRole']]);
    }
    private function setEncodedPassword()
    {
        $this->registrationParameters['user']->setPassword($this->registrationParameters['encodedPassword']);
    }
    private function throwSuccessMessage()
    {
        $this->flashBag->add('notice', 'Se ha registrado con éxito');
    }

    private function persistNewUserToDataBase()
    {
        $this->entityManager->persist($this->registrationParameters['user']);
        $this->entityManager->flush();
    }

    private function createErrorMessage(\Exception $exception)
    {
        $errorMessage=$exception->getMessage();
        return $this->flashBag->add('error', $errorMessage);
    }
}