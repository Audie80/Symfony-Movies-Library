<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\SerieRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Serie;
use Symfony\Component\Validator\Constraints\DateTime;

class ImportSeriesCommand extends Command
{
    protected static $defaultName = 'app:import-series';

    /**
     * @var SerieRepository
     */
    private $serieRepo;

    /**
     * @var ContainerInterface
     */
    private $container;

    CONST SERIES_URL = "https://api.themoviedb.org/3/discover/tv?api_key=cb0607bea2008403e9dec2fd0e70d3d0&language=fr-FR&sort_by=popularity.desc&timezone=Europa%2FParis&include_null_first_air_dates=false&page=1";

    public function __construct(SerieRepository $serieRepository, ContainerInterface $container)
    {
        $this->serieRepo = $serieRepository;
        $this->container = $container;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Importe les séries de l\'API dans la bdd')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Equivalent de AJAX new XMLRequest
        $httpClient = HttpClient::create();

        $responseContent = json_decode($httpClient->request('GET', self::SERIES_URL)->getContent());

        $nbPages = $responseContent->total_pages;

        // EntityManager : méthodes d'accès à la BDD
		/**
		 * @var EntityManager $em
		 */
        $em = $this->container->get('doctrine')->getManager();

        $nbSeriesCreated = 0;

        for ($i=1; $i < $nbPages; $i++) { 
            $url = "https://api.themoviedb.org/3/discover/tv?api_key=cb0607bea2008403e9dec2fd0e70d3d0&language=fr-FR&sort_by=popularity.desc&timezone=Europa%2FParis&include_null_first_air_dates=false&page=" . $i;
            $responseContent = json_decode($httpClient->request('GET', $url)->getContent());

            foreach ($responseContent->results as $result){

                // Si on ne trouve pas le film par son identifiant IMDB
                if (!$this->serieRepo->findOneBy(['tmdbID' => $result->id])){

                    // Affiche une information dans la console en joli
                    dump($result->name);

                    // Création d'un film
                    $serie = new Serie();
                    $serie->setTmdbID($result->id);
                    $serie->setName($result->name);
                    if (property_exists($result, 'first_air_date')) {
                        $serie->setFirstDate(new \DateTime($result->first_air_date));
                    };
                    $serie->setOverview($result->overview);
                    $serie->setPosterPath($result->poster_path);
                    $serie->setGenreIDs($result->genre_ids);

                    // Enregistrement
                    $em->persist($serie);

                    // Incrémentation du compteur
                    $nbSeriesCreated++;
                }
            }
        }
        
        // Envoi dans la bdd
        $em->flush();

        $io->success($nbSeriesCreated . ' séries ont été créées :)');

        return 0;
    }
}
