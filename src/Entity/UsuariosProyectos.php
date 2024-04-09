<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity()]
#[ORM\Table(name: "clientes_proyectos")]
class UsuariosProyectos
{
    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Usuarios::class, inversedBy: "usuariosProyectos")]
    private ?Usuarios $usuario;

    #[ORM\ManyToOne(targetEntity: Proyectos::class, inversedBy: "usuariosProyectos")]
    private ?Proyectos $proyecto;

    #[ORM\Column(type: "boolean")]
    private bool $estado;

    #[ORM\Column(type: "datetime")]
    private DateTime $fechaAlta;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTime $fechaBaja;

    public function __construct($usuario, $proyecto)
    {
        $this->usuario = $usuario;
        $this->proyecto = $proyecto;
        $this->estado = true;
        $this->fechaAlta = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuarios $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getProyecto(): ?Proyectos
    {
        return $this->proyecto;
    }

    public function setProyecto(?Proyectos $proyecto): self
    {
        $this->proyecto = $proyecto;

        return $this;
    }

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(bool $estado): self
    {
        $this->estado = $estado;

        if (!$estado) {
            $this->fechaBaja = new DateTime();
        }

        return $this;
    }

    public function getFechaAlta(): ?DateTime
    {
        return $this->fechaAlta;
    }

    public function setFechaAlta(DateTime $fechaAlta): self
    {
        $this->fechaAlta = $fechaAlta;

        return $this;
    }

    public function getFechaBaja(): ?DateTime
    {
        return $this->fechaBaja;
    }

    public function setFechaBaja(?DateTime $fechaBaja): self
    {
        $this->fechaBaja = $fechaBaja;

        return $this;
    }
}
