<?php

namespace App\Entity;

use App\Repository\ProgrammeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProgrammeRepository::class)]
class Programme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $duree_Programme = null;

    #[ORM\ManyToMany(targetEntity: Module::class, inversedBy: 'programmes')]
    private Collection $modules;

    #[ORM\OneToMany(mappedBy: 'programme', targetEntity: Session::class)]
    private Collection $sessions;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDureeProgramme(): ?int
    {
        return $this->duree_Programme;
    }

    public function setDureeProgramme(int $duree_Programme): self
    {
        $this->duree_Programme = $duree_Programme;

        return $this;
    }

    /**
     * @return Collection<int, Module>
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Module $module): self
    {
        if (!$this->modules->contains($module)) {
            $this->modules->add($module);
            $module->addProgramme($this);
        }

        return $this;
    }

    public function removeModule(Module $module): self
    {
        if ($this->modules->removeElement($module)) {
            $module->removeProgramme($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setProgramme($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getProgramme() === $this) {
                $session->setProgramme(null);
            }
        }

        return $this;
    }
}
