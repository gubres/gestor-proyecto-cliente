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
#[UniqueEntity(fields: ['email'], message: 'Ya existe una cuenta con este email')]
class Clientes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "El nombre del cliente no puede estar vacío.")]
    #[Assert\Length(
        min: 1,
        max: 30,
        maxMessage: "El nombre no puede sobrepasar los 30 caracteres."
    )]
    private ?string $nombre = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "El teléfono no puede estar vacío.")]
    #[Assert\Length(
        min: 9,
        minMessage: "El teléfono debe tener un mínimo de 9 caracteres.",
        max: 20,
        maxMessage: "El teléfono no puede sobrepasar los 20 caracteres."
    )]
    private ?string $telefono = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "El campo email no puede estar vacío.")]
    #[Assert\Email(
        message: "El formato del email '{{ value }}' no es válido.",
        mode: "strict"
    )]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: Proyectos::class, mappedBy: 'cliente', fetch: 'EAGER')]
    private Collection $proyectos;

    #[ORM\Column(type: 'boolean')]
    private bool $eliminado = false;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuarios $creado_por = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $creado_en = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $actualizado_en = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuarios $actualizado_por = null;

    public function __construct()
    {
        $this->proyectos = new ArrayCollection();
        $this->creado_en = new \DateTime();
        $this->actualizado_en = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): self
    {
        $this->telefono = $telefono;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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

    public function isEliminado(): bool
    {
        return $this->eliminado;
    }

    public function setEliminado(bool $eliminado): self
    {
        $this->eliminado = $eliminado;
        return $this;
    }

    public function getCreadoPor(): ?Usuarios
    {
        return $this->creado_por;
    }

    public function setCreadoPor(?Usuarios $creado_por): self
    {
        $this->creado_por = $creado_por;
        return $this;
    }

    public function getCreadoEn(): ?\DateTimeInterface
    {
        return $this->creado_en;
    }

    public function setCreadoEn(\DateTimeInterface $creado_en): self
    {
        $this->creado_en = $creado_en;
        return $this;
    }

    public function getActualizadoEn(): ?\DateTimeInterface
    {
        return $this->actualizado_en;
    }

    public function setActualizadoEn(?\DateTimeInterface $actualizado_en): self
    {
        $this->actualizado_en = $actualizado_en;
        return $this;
    }

    public function getActualizadoPor(): ?Usuarios
    {
        return $this->actualizado_por;
    }

    public function setActualizadoPor(?Usuarios $actualizado_por): self
    {
        $this->actualizado_por = $actualizado_por;
        return $this;
    }
}
