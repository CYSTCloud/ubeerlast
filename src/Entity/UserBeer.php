<?php

namespace App\Entity;

use App\Repository\UserBeerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserBeerRepository::class)]
class UserBeer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $beerId = null;

    private ?string $beerName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getBeerId(): ?int
    {
        return $this->beerId;
    }

    public function setBeerId(int $beerId): static
    {
        $this->beerId = $beerId;
        return $this;
    }

    public function getBeerName(): ?string
    {
        return $this->beerName;
    }

    public function setBeerName(?string $beerName): static
    {
        $this->beerName = $beerName;
        return $this;
    }
}
