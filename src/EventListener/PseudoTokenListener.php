<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class PseudoTokenListener {
    /**
     * Take the event linked to the creation of a JWT token to add user's nickname in his creation
     *
     * @param JWTCreatedEvent $event
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event) 
    {
        $payload = $event->getData();
        $payload["pseudo"] = $event->getUser()->getPseudo();
        $event->setData($payload);
    }
}