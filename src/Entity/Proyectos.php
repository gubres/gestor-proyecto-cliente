<?php

namespace App\Entity;

use App\Repository\ProyectosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProyectosRepository::class)]
class Proyectos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    #[ORM\Column(length: 15)]
    private ?string $estado = null;

    /**
     * @var Collection<int, Tareas>
     */
    #[ORM\OneToMany(targetEntity: Tareas::class, mappedBy: 'proyecto', orphanRemoval: true)]
    private Collection $tareas;

    #[ORM\OneToOne(inversedBy: 'proyectos', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clientes $cliente = null;

    #[ORM\OneToMany(targetEntity: UsuariosProyectos::class, mappedBy: 'proyectos')]
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

    public function addUsuariosProyectos(UsuariosProyectos $usuariosProyectos): static
    {
        if (!$this->usuariosProyectos->contains($usuariosProyectos)) {
            $this->usuariosProyectos[] = $usuariosProyectos;
            $usuariosProyectos->setProyecto($this);
        }

        return $this;
    }

    public function removeUsuariosProyectos(UsuariosProyectos $usuariosProyectos): static
    {
        $this->usuariosProyectos->removeElement($usuariosProyectos);

        return $this;
    }
}
