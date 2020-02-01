<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Genre;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use App\Repository\GenreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/movie", options={"expose"=true})
 */
class MovieController extends AbstractController
{
    /**
     * @Route("/{page}", requirements={"page" = "\d+"}, defaults={"page": 1}, options={"expose"=true}, name="movie_index", methods={"GET"})
     */
    public function index(MovieRepository $movieRepository, PaginatorInterface $paginator, GenreRepository $genreRepository, $page): Response
    {
        // prépare la requête
        $query = $movieRepository->createQueryBuilder('m')->getQuery();
        // utilise le bundle paginator pour préciser la requête avec offset=1 (page 1) et limit=30 (30 films par page)
        $pagination = $paginator->paginate($query, $page, 30);

        return $this->render('movie/index.html.twig', [
            'movies' => $pagination,
            //'genres' => $genreRepository->findAll(),
        ]);
    }

    /**
     * @Route("/sorted/{name}/{page}", requirements={"page" = "\d+"}, defaults={"page": 1}, name="movie_index_genre", methods={"GET"})
     */
    public function indexByGenre(MovieRepository $movieRepository, PaginatorInterface $paginator, Genre $genre, int $page): Response
    {
        // prépare la requête
        $queryBuilder = $movieRepository->createQueryBuilder('m');
        $queryBuilder->join('m.genres', 'g')
            ->where($queryBuilder->expr()->eq('g.id', $genre->getId()));
        $query = $queryBuilder->getQuery();

        // utilise le bundle paginator pour préciser la requête avec offset=1 (page 1) et limit=30 (30 films par page)
        $pagination = $paginator->paginate($query, $page, 30);

        return $this->render('movie/index.html.twig', [
            //'movies' => $genre->getMovies(),
            'movies' => $pagination,
            'genre' => $genre,
            //'genres' => $genreRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="movie_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($movie);
            $entityManager->flush();

            return $this->redirectToRoute('movie_index');
        }

        return $this->render('movie/new.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="movie_show", methods={"GET"})
     */
    public function show(Movie $movie): Response
    {
        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="movie_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Movie $movie): Response
    {
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('movie_index');
        }

        return $this->render('movie/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="movie_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Movie $movie): Response
    {
        if ($this->isCsrfTokenValid('delete'.$movie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($movie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('movie_index');
    }
}
