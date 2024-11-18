<?php

namespace App\ApiBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiBundle\DTO\ArticleCreateInputDTO;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ArticleCreateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface     $persistProcessor,
        private readonly EntityManagerInterface $manager,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof ArticleCreateInputDTO) {
            $article = new Article();
            $article->setAuthor($this->manager->getRepository(User::class)->find($data->author));
            return $this->persistProcessor->process($article, $operation, $uriVariables , $context);
        }

        throw new \Exception('Invalid input data');
    }
}