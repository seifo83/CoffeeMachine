<?php

namespace App\CoffeeMachine\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class MercureController extends AbstractController
{
    #[Route('/api/mercure-token', methods: ['GET'])]
    public function getMercureToken(): JsonResponse
    {
        try {
            $secret = $this->getParameter('app.mercure_jwt_secret');

            $config = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText($secret)
            );

            $now = new \DateTimeImmutable();
            $token = $config->builder()
                ->issuedAt($now)
                ->expiresAt($now->modify('+1 hour'))
                ->withClaim('mercure', ['subscribe' => ['orders/*']])
                ->getToken($config->signer(), $config->signingKey());

            return new JsonResponse(['token' => $token->toString()]);
        } catch (\Exception $e) {
            error_log('Erreur JWT: ' . $e->getMessage());

            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}