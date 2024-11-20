<?php

namespace App\Entity;


use App\Traits\TimeStampsTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: '`user_tokens`')]
#[ORM\HasLifecycleCallbacks]
class UserToken
{
    use TimeStampsTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $jti = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $token = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValid = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $expiredAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $issueAt = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $now = new \DateTime();
        $this->setCreateAt($now);
        $this->setUpdatedAt($now);
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $now = new \DateTime();
        $this->setUpdatedAt($now);
    }


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

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getJti(): ?string
    {
        return $this->jti;
    }

    /**
     * @param string|null $jti
     */
    public function setJti(?string $jti): void
    {
        $this->jti = $jti;
    }
}
