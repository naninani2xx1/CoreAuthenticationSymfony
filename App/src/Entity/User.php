<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use App\ApiBundle\Groups\ArticleGroup;
use App\Repository\UserRepository;
use App\Traits\TimeStampsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiBundle\Groups\UserGroup;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Symfony\Component\Serializer\Attribute\Groups;
use App\ApiBundle\State\UserStateProcessor;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(stateless: true, normalizationContext: ['groups' => UserGroup::USER_ITEM]),
        new Patch(
            stateless: true,
            normalizationContext: ['groups' => UserGroup::USER_PATCH_ITEM],
            denormalizationContext: ['groups' => UserGroup::USER_PATCH_ITEM]
        ),
        new Post(
            stateless: true,
            normalizationContext: ['groups' => UserGroup::USER_POST_READ_ITEM],
            denormalizationContext: ['groups' => UserGroup::USER_POST_WRITE_ITEM],
            security: "is_granted('ROLE_ADMIN')",
            processor: UserStateProcessor::class,
        ),
    ],
    order: ['id' => 'DESC'],
    paginationEnabled: false,
)]
class User implements UserInterface, LegacyPasswordAuthenticatedUserInterface
{
    use TimeStampsTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        UserGroup::USER_ITEM, UserGroup::USER_PATCH_ITEM,
        UserGroup::USER_AUTH_ITEM, UserGroup::USER_POST_READ_ITEM, ArticleGroup::POST_READ_ITEM,
    ])]
//    #[ApiProperty(writableLink: false)]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups([UserGroup::USER_ITEM, UserGroup::USER_POST_READ_ITEM, UserGroup::USER_POST_WRITE_ITEM, ArticleGroup::POST_READ_ITEM])]
    private ?string $username = null;

    #[ORM\Column(length: 180)]
    #[Groups([
        UserGroup::USER_ITEM, 
        UserGroup::USER_PATCH_ITEM, UserGroup::USER_AUTH_ITEM,
        UserGroup::USER_POST_WRITE_ITEM,
        UserGroup::USER_POST_READ_ITEM,
    ])]
    private ?string $fullName = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: "simple_array")]
    #[Groups([UserGroup::USER_ITEM])]
    private array $roles = [];

    /**
     * @var null| string The hashed password
     */
    #[ORM\Column]
    #[Groups([UserGroup::USER_POST_WRITE_ITEM])]
    private ?string $password = null;

     /**
     * @var string | null The hashed salt
     */
    #[ORM\Column]
    private ?string $salt = null;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'author')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    #[PrePersist]
    public function prePersist(): void
    {
        $this->roles[] = 'ROLE_USER';
        $this->generateSalt();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getSalt(): ?string
    {
        return $this->salt;
    }


    public function generateSalt(): void
    {
        $this->salt = md5(time());
    }

    public function setSalt(string $salt): static
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }
}
