<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\MovieRepository;
use App\Repository\GenreRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Movie;
use Symfony\Component\Validator\Constraints\DateTime;

class ImportDataCommand extends Command
{
    protected static $defaultName = 'app:import-data';

    /**
     * @var MovieRepository
     */
    private $movieRepo;

    /**
     * @var GenreRepository
     */
    private $genreRepo;

    /**
     * @var ContainerInterface
     */
    private $container;

    CONST API_URL = "https://api.themoviedb.org/3/discover/movie?api_key=cb0607bea2008403e9dec2fd0e70d3d0&language=fr-FR&sort_by=popularity.desc&include_adult=true&include_video=true&page=1";

    public function __construct(MovieRepository $movieRepository,GenreRepository $genreRepository,ContainerInterface $container)
    {
        $this->movieRepo = $movieRepository;
        $this->genreRepo = $genreRepository;
        $this->container = $container;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Importe les films de l\'API')
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Equivalent de AJAX new XMLRequest
        $httpClient = HttpClient::create();

        $responseContent = json_decode($httpClient->request('GET', self::API_URL)->getContent());

        $nbPages = $responseContent->total_pages;

        // EntityManager : méthodes d'accès à la BDD
		/**
		 * @var EntityManager $em
		 */
        $em = $this->container->get('doctrine')->getManager();

        $nbMoviesCreated = 0;

        for ($i=1; $i < $nbPages; $i++) { 
            $url = "https://api.themoviedb.org/3/discover/movie?api_key=cb0607bea2008403e9dec2fd0e70d3d0&language=fr-FR&sort_by=popularity.desc&include_adult=true&include_video=true&page=" . $i;
            $responseContent = json_decode($httpClient->request('GET', $url)->getContent());

            foreach ($responseContent->results as $result){

                // Affiche une information dans la console en joli
                dump($result->title);

                $movie = $this->movieRepo->findOneBy(['tmdbID' => $result->id]);
                // Si on ne trouve pas le film par son identifiant IMDB
                if (!$movie){

                    // Création d'un film
                    $movie = new Movie();
                    $movie->setTmdbID($result->id);
                    $movie->setTitle($result->title);
                    if (property_exists($result, 'release_date')) {
                        $movie->setReleaseDate(new \DateTime($result->release_date));
                    };
                    $movie->setOverview($result->overview);
                    $movie->setPosterPath($result->poster_path);

                    // Incrémentation du compteur
                    $nbMoviesCreated++;
                }

                // Ajout des genres dans les films
                if (count($result->genre_ids) > 0 && count($movie->getGenres()) === 0) {
                    foreach ($result->genre_ids as $genreId) {
                        $genre = $this->genreRepo->findOneBy(['tmdbID' => $genreId]);
                        dump($genre->getName());
                        $movie->addGenre($genre);
                    }
                }

                // Enregistrement
                $em->persist($movie);
            }
        }
        
        // Envoi dans la bdd
        $em->flush();

        $io->success($nbMoviesCreated . ' films ont été créés :)');

        // $arg1 = $input->getArgument('arg1');
        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }
        // if ($input->getOption('option1')) {
        //     // ...
        // }
        // $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        
        return 0;
    }
}
