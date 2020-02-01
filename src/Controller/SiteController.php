<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GenreRepository;

class SiteController extends AbstractController
{
    /**
     * @Route("/", name="site")
     */
    public function index(GenreRepository $genreRepository)
    {
        return $this->render('site/index.html.twig', [
            'controller_name' => 'SiteController',
            'genres' => $genreRepository->findAll(),
        ]);
    }
}
