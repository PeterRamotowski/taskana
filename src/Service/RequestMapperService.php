<?php

namespace App\Service;

use App\Service\Enum\RequestSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RequestMapperService
{
  public function __construct(
    private readonly SerializerInterface $serializer
  ) {
  }

  /**
   * @param array<mixed> $context
   */
  public function toObject(
    Request $request,
    object $data,
    array $context = [],
    RequestSource $source = RequestSource::BODY
  ): void
  {
    switch ($source) {
      case RequestSource::POST:
        $rawData = $request->request->all();
        $format = 'array';
        break;
      case RequestSource::BODY:
      default:
        $rawData = $request->getContent();
        $format = 'json';
        break;
    }

    $context = array_merge_recursive(
      [
        AbstractObjectNormalizer::ALLOW_EXTRA_ATTRIBUTES => true,
        AbstractObjectNormalizer::OBJECT_TO_POPULATE => $data,
      ],
      $context
    );

    try {
      $this->serializer->deserialize(
        $rawData,
        get_class($data),
        $format,
        $context
      );
    } catch (ExceptionInterface $e) {
      throw new BadRequestHttpException('Deserialization error', $e);
    }
  }
}
