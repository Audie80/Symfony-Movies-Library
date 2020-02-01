<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SerieRepository")
 */
class Serie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $tmdbID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $firstDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $overview;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $posterPath;

    /**
     * @ORM\Column(type="array")
     */
    private $genreIDs;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTmdbID(): ?int
    {
        return $this->tmdbID;
    }

    public function setTmdbID(int $tmdbID): self
    {
        $this->tmdbID = $tmdbID;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstDate(): ?\DateTimeInterface
    {
        return $this->firstDate;
    }

    public function setFirstDate(?\DateTimeInterface $firstDate): self
    {
        $this->firstDate = $firstDate;

        return $this;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function setOverview(?string $overview): self
    {
        $this->overview = $overview;

        return $this;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function setPosterPath(?string $posterPath): self
    {
        $this->posterPath = $posterPath;

        return $this;
    }

    public function getGenreIDs(): ?array
    {
        return $this->genreIDs;
    }

    public function setGenreIDs(array $genreIDs): self
    {
        $this->genreIDs = $genreIDs;

        return $this;
    }
}
