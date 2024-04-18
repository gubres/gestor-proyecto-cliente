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

    //restricciones nombre tarea
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

    //validación fecha, no puede estar vacía
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La fecha de creación no puede estar vacía.")]
    private ?\DateTimeInterface $creado_en = null;
    

    //añado restricciones a la prioridad
    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "La prioridad de la tarea es requerida.")]
    #[Assert\Choice(
        choices: ['ALTA', 'MEDIA', 'BAJA'],
        message: "La prioridad debe ser alta, media o baja."
    )]
    private ?string $prioridad = null;

    //restricción de asociación tarea/proyecto
    #[ORM\ManyToOne(inversedBy: 'tareas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "La tarea debe estar asociada a un proyecto.")]
    private ?Proyectos $proyecto = null;

    /**
     * @var Collection<int, Usuarios>
     */
    #[ORM\ManyToMany(targetEntity: Usuarios::class, inversedBy: 'tareas')]
    private Collection $usuarios;

    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
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
}
