<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Movie;
use App\Entity\User;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user")
     * //@IsGranted("ROLE_USER")
     */
    public function index()
    {
        $user = $this->getUser();
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
        ]);
    }

    /**
     * @Route("/addfavoritemovie/{id}", name="add_favorite_movie", options={"expose"=true})
     * //@IsGranted("ROLE_USER")
     */
    public function addFavoriteMovie(Movie $movie): Response
    {
        // $message = 'déconnecté';
        // if ($this->getUser()) {
        //     $message = 'connecté';
        // }

        // Fonction dd = dump & die
        //dd($movie);
        
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $message = 'Le film ' . $movie->getTitle();

        if (!$user->getFavoriteMovies()->contains($movie)) {
            $user->addFavoriteMovie($movie);
            $message .= ' a été ajouté aux favoris';
            $add = true;
        } else {
            $user->removeFavoriteMovie($movie);
            $message .= ' a été retiré des favoris';
            $add = false;
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->persist($movie);
        $em->flush();

        return new JsonResponse(['data' => $message, 'add' => $add]);
    }
}
