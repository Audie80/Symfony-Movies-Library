<?php

namespace App\Service;

use Psr\Container\ContainerInterface;
use App\Repository\GenreRepository;

class navbarGenres {

  /**
     * @var ContainerInterface
     */
    private $container;

  public function __construct(GenreRepository $genreRepository, ContainerInterface $container) {

    $this->container = $container;
    $this->genreRepo = $genreRepository;

  }

  public function getGenres() {

    return $this->genreRepo->findAll();

  }

}

?>