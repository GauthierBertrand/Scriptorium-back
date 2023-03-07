<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class PseudoTokenListener {
    /**
     * Récupère l'event lié à la création d'un token JWT pour ajouter le pseudo de l'utilisateur dans sa création
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