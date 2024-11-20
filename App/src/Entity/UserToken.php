<?php

namespace App\Entity;

use App\Repository\UserTokenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTokenRepository::class)]
#[ORM\Table(name: '`user_tokens`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_TOKEN', fields: ['token'])]
class UserToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tokens')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?string $token = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValid = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $expiredAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $issueAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->isValid;
    }

    public function setValid(?bool $isValid): static
    {
        $this->isValid = $isValid;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?\DateTimeInterface $expiredAt): static
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function getIssueAt(): ?\DateTimeInterface
    {
        return $this->issueAt;
    }

    public function setIssueAt(?\DateTimeInterface $issueAt): static
    {
        $this->issueAt = $issueAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
