<?php

namespace App\Entity;

use App\Repository\TareasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TareasRepository::class)]
class Tareas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "El nombre de la tarea no puede estar vacío.")]
    #[Assert\Length(
        min: 1,
        max: 30,
        maxMessage: "El nombre de la tarea debe ser entre 1 y 30 caracteres."
    )]
    private ?string $nombre = null;


    #[ORM\Column]
    private ?bool $finalizada = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La fecha de creación no puede estar vacía.")]
    private ?\DateTimeInterface $creado_en = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "La prioridad de la tarea es requerida.")]
    #[Assert\Choice(
        choices: ['ALTA', 'MEDIA', 'BAJA'],
        message: "La prioridad debe ser alta, media o baja."
    )]
    private ?string $prioridad = null;

    #[ORM\ManyToOne(inversedBy: 'tareas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "La tarea debe estar asociada a un proyecto.")]
    private ?Proyectos $proyecto = null;

    /**
     * @var Collection<int, Usuarios>
     */
    #[ORM\ManyToMany(targetEntity: Usuarios::class, inversedBy: 'tareas')]
    private Collection $usuarios;

    #[ORM\Column(type: 'boolean')]
    private bool $eliminado = false;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuarios $creado_por = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuarios $actualizado_por = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $actualizado_en = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $finalizado_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true, message: "La descripción no puede estar vacía.")]
    private ?string $descripcion = null;


    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
        $this->creado_en = new \DateTime();
        $this->finalizado_en = new \DateTime();  // Establece la fecha final por defecto a la fecha actual
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

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function isFinalizada(): ?bool
    {
        return $this->finalizada;
    }

    public function setFinalizada(bool $finalizada): static
    {
        $this->finalizada = $finalizada;

        return $this;
    }

    public function getFinalizadoEn(): ?\DateTimeInterface
    {
        return $this->finalizado_en;
    }

    public function setFinalizadoEn(\DateTimeInterface $finalizado_en): self
    {
        $this->finalizado_en = $finalizado_en;
        return $this;
    }


    public function getCreadoEn(): ?\DateTimeInterface
    {
        return $this->creado_en;
    }

    public function setCreadoEn(\DateTimeInterface $creado_en): static
    {
        $this->creado_en = $creado_en;

        return $this;
    }

    public function getPrioridad(): ?string
    {
        return $this->prioridad;
    }

    public function setPrioridad(string $prioridad): static
    {
        $this->prioridad = $prioridad;

        return $this;
    }

    public function getProyecto(): ?Proyectos
    {
        return $this->proyecto;
    }

    public function setProyecto(?Proyectos $proyecto): static
    {
        $this->proyecto = $proyecto;

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

    public function getActualizadoPor(): ?Usuarios
    {
        return $this->actualizado_por;
    }

    public function setActualizadoPor(?Usuarios $actualizado_por): self
    {
        $this->actualizado_por = $actualizado_por;
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
    /**
     * @return Collection<int, Usuarios>
     */
    public function getUsuario(): Collection
    {
        return $this->usuarios;
    }

    public function addUsuario(Usuarios $usuario): static
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios->add($usuario);
        }

        return $this;
    }

    public function removeUsuario(Usuarios $usuario): static
    {
        $this->usuarios->removeElement($usuario);

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }
}
