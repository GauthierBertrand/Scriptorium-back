<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class LoginLimiterSubscriber implements EventSubscriberInterface
{
    private $limiter;
    private $currentRequest;

    public function __construct(RateLimiterFactory $loginApiLimiter, RequestStack $request)
    {
        $this->limiter = $loginApiLimiter;
        $this->currentRequest = $request->getCurrentRequest();
    }

    public function limitLoginSuccess(AuthenticationSuccessEvent $event): void
    {
        $limiter = $this->limiter->create($this->currentRequest->getClientIp());

        if ($limiter->consume(1)->isAccepted() === false) {
            throw new TooManyRequestsHttpException();
        }
    }

    public function limitLoginFailure(AuthenticationFailureEvent $event): void
    {
        $limiter = $this->limiter->create($this->currentRequest->getClientIp());

        if ($limiter->consume(1)->isAccepted() === false) {
            throw new TooManyRequestsHttpException();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'limitLoginSuccess',
            'lexik_jwt_authentication.on_authentication_failure' => 'limitLoginFailure',
        ];
    }
}
