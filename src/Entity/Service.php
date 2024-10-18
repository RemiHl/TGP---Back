<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_du_service = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Devis::class, mappedBy: 'services')]
    private Collection $devis;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomDuService(): ?string
    {
        return $this->nom_du_service;
    }

    public function setNomDuService(string $nom_du_service): static
    {
        $this->nom_du_service = $nom_du_service;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @retur Collection<int, Devis>
     */
    public function getDevis(): Collection
    {
        return $this->devis;
    }

    public function addDevi(Devis $devi): self
    {
        if (!$this->devis->contains($devi)) {
            $this->devis[] = $devi;
            $devi->addService($this);
        }
        return $this;
    }

    public function removeDevi(Devis $devi): self
    {
        if ($this->devis->removeElement($devi)) {
            $devi->removeService($this);
        }
        return $this;
    }
}
