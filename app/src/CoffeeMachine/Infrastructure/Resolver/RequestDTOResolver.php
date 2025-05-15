<?php

namespace App\CoffeeMachine\Infrastructure\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDTOResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();

        if (null === $type) {
            return false;
        }

        return str_ends_with($type, 'DTO');
    }

    /**
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (null === $type) {
            throw new \InvalidArgumentException('Type cannot be null');
        }

        /** @var object $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            $type,
            'json'
        );

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errorMessage = '';
            foreach ($violations as $violation) {
                $errorMessage .= $violation->getPropertyPath().': '.$violation->getMessage().PHP_EOL;
            }

            throw new BadRequestHttpException(trim($errorMessage));
        }

        yield $dto;
    }
}
