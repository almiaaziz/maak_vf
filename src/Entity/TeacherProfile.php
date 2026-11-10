<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'teacher_profiles')]
class TeacherProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 180)]
    private ?string $university = null;

    #[ORM\Column(length: 180)]
    private ?string $fieldOfStudy = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $academicDegree = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $professionalBio = null;

    #[ORM\Column]
    private bool $verified = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getUniversity(): ?string
    {
        return $this->university;
    }

    public function setUniversity(string $university): static
    {
        $this->university = trim($university);
        return $this;
    }

    public function getFieldOfStudy(): ?string
    {
        return $this->fieldOfStudy;
    }

    public function setFieldOfStudy(string $fieldOfStudy): static
    {
        $this->fieldOfStudy = trim($fieldOfStudy);
        return $this;
    }

    public function getAcademicDegree(): ?string
    {
        return $this->academicDegree;
    }

    public function setAcademicDegree(?string $academicDegree): static
    {
        $this->academicDegree = $academicDegree;
        return $this;
    }

    public function getProfessionalBio(): ?string
    {
        return $this->professionalBio;
    }

    public function setProfessionalBio(?string $professionalBio): static
    {
        $this->professionalBio = $professionalBio;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;
        return $this;
    }
}
