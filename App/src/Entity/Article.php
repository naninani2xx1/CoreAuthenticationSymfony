<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\ApiBundle\Groups\ArticleGroup;
use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: '`post`')]
#[ApiResource(
    operations: [
        new Post(
            stateless: true,
            normalizationContext: ['groups' => ArticleGroup::POST_READ_ITEM],
            denormalizationContext: ['groups' => ArticleGroup::POST_WRITE_ITEM],
            security: "is_granted('ROLE_USER')",
        ),
    ],
)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: [ArticleGroup::POST_LIST, ArticleGroup::POST_READ_ITEM])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: [ArticleGroup::POST_LIST, ArticleGroup::POST_READ_ITEM, ArticleGroup::POST_WRITE_ITEM])]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'articles')]
    #[Groups(groups: [ArticleGroup::POST_LIST, ArticleGroup::POST_READ_ITEM, ArticleGroup::POST_WRITE_ITEM])]
    private ?User $author = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
