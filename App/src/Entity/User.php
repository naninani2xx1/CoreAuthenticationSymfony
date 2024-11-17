<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
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
        new Get(normalizationContext: ['groups' => UserGroup::USER_ITEM], stateless: true),
        new GetCollection(
            normalizationContext: ['groups' => UserGroup::USER_LIST],
            stateless: true,
            openapiContext: []
        ),
        new Patch(
            stateless: true,
            normalizationContext: ['groups' => UserGroup::USER_PATCH_ITEM],
            denormalizationContext: ['groups' => UserGroup::USER_PATCH_ITEM]
        ),
        new Post(
            stateless: true,
            processor: UserStateProcessor::class,
            denormalizationContext: ['groups' => UserGroup::USER_POST_WRITE_ITEM],
            normalizationContext: ['groups' => UserGroup::USER_POST_READ_ITEM],
        )
    ],
    paginationEnabled: false,
    order: ['id' => 'DESC'],
)]
class User implements UserInterface, LegacyPasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        UserGroup::USER_LIST, UserGroup::USER_ITEM, UserGroup::USER_PATCH_ITEM,
        UserGroup::USER_AUTH_ITEM, UserGroup::USER_POST_READ_ITEM
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups([UserGroup::USER_LIST, UserGroup::USER_ITEM, UserGroup::USER_POST_READ_ITEM, UserGroup::USER_POST_WRITE_ITEM])]
    private ?string $username = null;

    #[ORM\Column(length: 180)]
    #[Groups([
        UserGroup::USER_LIST, UserGroup::USER_ITEM, 
        UserGroup::USER_PATCH_ITEM, UserGroup::USER_AUTH_ITEM,
        UserGroup::USER_POST_WRITE_ITEM,
        UserGroup::USER_POST_READ_ITEM,
    ])]
    private ?string $fullName = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: "simple_array")]
    #[Groups([UserGroup::USER_LIST, UserGroup::USER_ITEM])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups([UserGroup::USER_POST_WRITE_ITEM])]
    private ?string $password = null;

     /**
     * @var string The hashed salt
     */
    #[ORM\Column]
    private ?string $salt = null;


    #[PrePersist]
    public function prePersits(): void
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


    public function generateSalt()
    {
        $this->salt = md5(time());
    }
    
}
