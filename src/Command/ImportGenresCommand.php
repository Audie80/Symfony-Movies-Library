<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\GenreRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Genre;
use Symfony\Component\Validator\Constraints\DateTime;

class ImportGenresCommand extends Command
{
    protected static $defaultName = 'app:import-genres';

    /**
     * @var GenreRepository
     */
    private $genreRepo;

    /**
     * @var ContainerInterface
     */
    private $container;

    CONST GENRES_URL = "https://api.themoviedb.org/3/genre/movie/list?api_key=cb0607bea2008403e9dec2fd0e70d3d0&language=fr-FR";

    public function __construct(GenreRepository $genreRepository, ContainerInterface $container)
    {
        $this->genreRepo = $genreRepository;
        $this->container = $container;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Importe les genres de l\'API dans la bdd')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Equivalent de AJAX new XMLRequest
        $httpClient = HttpClient::create();

        $responseContent = json_decode($httpClient->request('GET', self::GENRES_URL)->getContent());

        // EntityManager : méthodes d'accès à la BDD
		/**
		 * @var EntityManager $em
		 */
        $em = $this->container->get('doctrine')->getManager();

        $nbGenresCreated = 0;

        foreach ($responseContent->genres as $result){

            // Si on ne trouve pas le genre par son identifiant IMDB
            if (!$this->genreRepo->findOneBy(['tmdbID' => $result->id])){

                // Affiche une information dans la console en joli
                dump($result->name);

                // Création d'un genre
                $genre = new Genre();
                $genre->setTmdbID($result->id);
                $genre->setName($result->name);

                // Enregistrement
                $em->persist($genre);

                // Incrémentation du compteur
                $nbGenresCreated++;
            }
        }
        
        // Envoi dans la bdd
        $em->flush();

        $io->success($nbGenresCreated . ' genres ont été créés :)');

        return 0;
    }
}
