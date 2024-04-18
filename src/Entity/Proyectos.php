<?php

namespace App\Entity;

use App\Repository\ProyectosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\UsuariosProyectos;
use Doctrine\ORM\Mapping as ORM;
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
