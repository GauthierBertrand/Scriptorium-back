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
    private $inscriptionApiLimiter;
    private $loginApiLimiter;
    private $generatePdfApiLimiter;
    private $modifyPasswordApiLimiter;

    private $limiter;   

    public function __construct(RateLimiterFactory $inscriptionApiLimiter, RateLimiterFactory $loginApiLimiter, RateLimiterFactory $generatePdfApiLimiter, RateLimiterFactory $modifyPasswordApiLimiter)
    {
        $this->inscriptionApiLimiter=$inscriptionApiLimiter;
        $this->loginApiLimiter=$loginApiLimiter;
        $this->generatePdfApiLimiter=$generatePdfApiLimiter;
        $this->modifyPasswordApiLimiter=$modifyPasswordApiLimiter;
       
    }
    
    /**
     * Rate limiter
     *
     * @param [type] $request
     * @return void
     */
    public function limit($request)
    {
        //Retrive in the request the name of the fuction called for the route and add Apilimiter suffixe and set it in $routeToLimit
        $partToexplode = $request->get('_controller');
        $routeToLimit = explode('::',$partToexplode)[1].'ApiLimiter';
        
        //Set the limiter with it and base it on the client ip 
        $this->limiter = $this->$routeToLimit->create($request->getClientIp());
        //Consume one request and check if it's still accepted
        if (false === $this->limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }
    }

}