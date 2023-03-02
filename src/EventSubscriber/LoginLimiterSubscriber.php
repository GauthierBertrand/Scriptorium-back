<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class LoginLimiterSubscriber implements EventSubscriberInterface
{
    private $limiter;

    public function __construct(RateLimiterFactory $loginApiLimiter)
    {
        $this->limiter = $loginApiLimiter;
    }

    public function limitLoginSuccess(AuthenticationSuccessEvent $event): void
    {
        $event->
    }

    public function limitLoginFailure(AuthenticationFailureEvent $event): void
    {
        $data = [
            'name' => 'John Doe',
            'foo'  => 'bar',
        ];
    
        $response = new JWTAuthenticationFailureResponse('Bad credentials, please verify that your username/password are correctly set', JsonResponse::HTTP_UNAUTHORIZED);
        $response->setData($data);
    
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'limitLoginSuccess',
            'lexik_jwt_authentication.on_authentication_failure' => 'limitLoginFailure',
        ];
    }
}
