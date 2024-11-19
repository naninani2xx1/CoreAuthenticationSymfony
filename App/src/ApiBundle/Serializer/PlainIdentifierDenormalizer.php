<?php

namespace App\ApiBundle\Serializer;

use ApiPlatform\Symfony\Routing\IriConverter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use App\Entity\Article;
use App\Entity\User;

class PlainIdentifierDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        #[Autowire(service: 'api_platform.iri_converter')]
       private readonly IriConverter $iriConverter)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (isset($data['author']) && is_numeric($data['author'])) {
            $data['author'] = $this->iriConverter->getIriFromResource(
                resource: User::class, // Sử dụng class User
                context: ['uri_variables' => ['id' => $data['author']]]
            );
        }
        return $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return \in_array($format, ['json', 'jsonld'], true)
            && is_a($type, Article::class, true)
            && !empty($data['author'])
            && !isset($context[__CLASS__]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Article::class => true,
        ];
    }
}
