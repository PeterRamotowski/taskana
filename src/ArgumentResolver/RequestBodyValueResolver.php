<?php

namespace App\ArgumentResolver;

use App\Service\RequestMapperService;
use App\Service\ValidatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestBodyValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly RequestMapperService $requestMapper,
        private readonly ValidatorService $validatorService,
    ) {
    }

    /**
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();
        $data = new $type();

        $this->requestMapper->toObject($request, $data);
        $this->validatorService->validate($data);

        yield $data;
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $attrs = $argument->getAttributes(RequestBody::class);
        
        return count($attrs) > 0;
    }
}