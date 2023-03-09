<?php

namespace App\Utils;

use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Service for rate limiter.
 * Need a request injection in the route function
 */
class RateLimiterService
{
    //Differents variables for retrive the ratelimiterfactory with the _construct.
    //Each one take the name of the route function to ratelimit with ApiLimiter suffixe
    //One variable for one config in the config file rate_limiter.yaml 

    /**
     * @var RateLimiterFactory
     */
    private $inscriptionApiLimiter;
    private $generatePdfApiLimiter;
    private $modifyPasswordApiLimiter;
    private $resendActivationApiLimiter;
    private $resetMailSendApiLimiter;

    public function __construct(RateLimiterFactory $resetMailSendApiLimiter, RateLimiterFactory $inscriptionApiLimiter, RateLimiterFactory $generatePdfApiLimiter, RateLimiterFactory $modifyPasswordApiLimiter, RateLimiterFactory $resendActivationApiLimiter)
    {
        $this->inscriptionApiLimiter = $inscriptionApiLimiter;
        $this->generatePdfApiLimiter = $generatePdfApiLimiter;
        $this->modifyPasswordApiLimiter = $modifyPasswordApiLimiter;
        $this->resendActivationApiLimiter = $resendActivationApiLimiter;
        $this->resetMailSendApiLimiter = $resetMailSendApiLimiter;
    }
    
    /**
     * Rate limiter
     * @param [type] $request
     * @return void
     */
    public function limit($request)
    {
        //Retrive in the request the name of the fuction called for the route and add Apilimiter suffixe and set it in $routeToLimit
        //It will serve of identifier to know wich route to limit
        $partToexplode = $request->get('_controller');
        $routeToLimit = explode('::', $partToexplode)[1] . 'ApiLimiter';
        
        $limiter = $this->$routeToLimit->create($request->getClientIp());

        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }
    }
}