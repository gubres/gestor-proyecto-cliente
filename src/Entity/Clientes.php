<?php

namespace App\Entity;

use App\Repository\ClientesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Proyectos;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ClientesRepository::class)]
//campo email único en bbdd
#[UniqueEntity(fields: ['email'], message: 'Ya existe una cuenta con este email')]
class Clientes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    //restricciones nombre cliente
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "El nombre del cliente no puede estar vacío.")]
    #[Assert\Length(
        min: 1,
        max: 30,
        maxMessage: "El nombre no puede sobrepasar los 30 caracteres."
    )]
    private ?string $nombre = null;
    
    //validación teléfono cliente
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "El teléfono no puede estar vacío.")]
    #[Assert\Length(
        min: 9,
        minMessage: "El teléfono debe tener un mínimo de 9 caracteres.",
        max: 20,
        maxMessage: "El teléfono no puede sobrepasar los 20 caracteres."
    )]
    private ?string $telefono = null;

    //validaciones email cliente
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "El campo email no puede estar vacío.")]
    #[Assert\Email(
        message: "El formato del email '{{ value }}' no es válido.",
        mode: "strict"
    )]
    private ?string $email = null;

    /**
     * @var Collection<int, Proyectos>
     */
    #[ORM\OneToMany(targetEntity: Proyectos::class, mappedBy: 'cliente', fetch: 'EAGER')]
    private Collection $proyectos;


    



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

 /**
     * @return Collection<int, Proyectos>
     */
    public function getProyectos(): Collection
    {
        return $this->proyectos;
    }

    public function addProyecto(Proyectos $proyecto): self
    {
        if (!$this->proyectos->contains($proyecto)) {
            $this->proyectos[] = $proyecto;
            $proyecto->setCliente($this);
        }

        return $this;
    }

    public function removeProyecto(Proyectos $proyecto): self
    {
        $this->proyectos->removeElement($proyecto);
        return $this;
    }
}