<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Usuarios;
use App\Repository\ProyectosRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProyectosRepository::class)]
class Proyectos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    //restricción nombre proyecto 1-50 caracteres
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "El nombre del proyecto no puede estar vacío.")]
    #[Assert\Length(
        max: 50,
        maxMessage: "El nombre del proyecto no puede tener más de 50 caracteres."
    )]
    private ?string $nombre = null;

    //se requiere hacer click a un estado del proyecto
    #[ORM\Column(length: 15)]
    #[Assert\NotBlank(message: "El estado del proyecto es requerido.")]
    #[Assert\Choice(
        choices: ['Activo', 'Inactivo'],
        message: "El estado del proyecto debe ser activo o inactivo."
    )]
    private ?string $estado = null;

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
    private ?\DateTimeInterface $creado_en = null;

    /**
     * @var Collection<int, Tareas>
     */
    #[ORM\OneToMany(targetEntity: Tareas::class, mappedBy: 'proyecto', orphanRemoval: true)]
    private Collection $tareas;

    #[ORM\ManyToOne(targetEntity: Clientes::class, inversedBy: 'proyectos')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Clientes $cliente = null;

    #[ORM\OneToMany(mappedBy: 'proyecto', targetEntity: UsuariosProyectos::class, cascade: ['persist', 'remove'])]
    private Collection $usuariosProyectos;

    public function __construct()
    {
        $this->tareas = new ArrayCollection();
        $this->usuariosProyectos = new ArrayCollection();
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

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

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
    public function getCreadoEn(): ?\DateTimeInterface
    {
        return $this->creado_en;
    }

    public function setCreadoEn(?\DateTimeInterface $creado_en): self
    {
        $this->creado_en = $creado_en;
        return $this;
    }
    /**
     * @return Collection<int, Tareas>
     */
    public function getTareas(): Collection
    {
        return $this->tareas;
    }

    public function addTarea(Tareas $tarea): static
    {
        if (!$this->tareas->contains($tarea)) {
            $this->tareas->add($tarea);
            $tarea->setProyecto($this);
        }

        return $this;
    }

    public function removeTarea(Tareas $tarea): static
    {
        if ($this->tareas->removeElement($tarea)) {
            // set the owning side to null (unless already changed)
            if ($tarea->getProyecto() === $this) {
                $tarea->setProyecto(null);
            }
        }

        return $this;
    }

    public function getCliente(): ?Clientes
    {
        return $this->cliente;
    }

    public function setCliente(Clientes $cliente): static
    {
        $this->cliente = $cliente;

        return $this;
    }
    /**
     * @return Collection<int, Usuarios>
     */
    public function getUsuariosProyectos(): Collection
    {
        return $this->usuariosProyectos;
    }

    public function addUsuariosProyectos(UsuariosProyectos $usuariosProyecto): static
    {
        if (!$this->usuariosProyectos->contains($usuariosProyecto)) {
            $this->usuariosProyectos->add($usuariosProyecto);
            $usuariosProyecto->setProyecto($this);
        }

        return $this;
    }

    public function removeUsuariosProyectos(UsuariosProyectos $usuariosProyecto): static
    {
        if ($this->usuariosProyectos->removeElement($usuariosProyecto)) {
            // set the owning side to null (unless already changed)
            if ($usuariosProyecto->getProyecto() === $this) {
                $usuariosProyecto->setProyecto(null);
            }
        }

        return $this;
    }
}
