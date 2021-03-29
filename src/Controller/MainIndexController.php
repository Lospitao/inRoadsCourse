<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainIndexController extends AbstractController
{
    /**
     * @Route("/main/index", name="main_index")
     */
    public function index(): Response
    {
        return $this->render('main_index/index.html.twig', [
            'controller_name' => 'MainIndexController',
        ]);
    }
}
