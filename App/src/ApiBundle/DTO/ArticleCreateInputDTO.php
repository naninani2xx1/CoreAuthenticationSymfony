<?php

namespace App\ApiBundle\DTO;

use ApiPlatform\Metadata\ApiProperty;
use App\ApiBundle\Groups\ArticleGroup;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class ArticleCreateInputDTO
{
    #[Assert\NotBlank(message: 'Author ID is required')]
    #[Groups(groups: [ ArticleGroup::POST_WRITE_ITEM])]
    public ?int $author = null;

    #[Assert\NotBlank(message: 'The name is required')]
    #[Groups(groups: [ ArticleGroup::POST_WRITE_ITEM])]
    public ?string $name = null;
}