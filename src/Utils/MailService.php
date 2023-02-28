<?php

namespace App\Utils;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailService
{
private $mailer;

public function __construct(MailerInterface $mailer)
{
    $this->mailer = $mailer;
}

/**
 * Fonction permettant de renvoyer un mail
 *
 * @param string $from expediteur
 * @param string $to destinataire
 * @param string $subject sujet du mail
 * @param string $template nom du template twig
 * @param array $context
 * @return void
 */
public function send(
    string $from,
    string $to,
    string $subject,
    string $template,
    array $context
): void
{
//on créé le mail avec le composant permettant la creation de mail
$email = (new TemplatedEmail())
    ->from($from)
    ->to($to)
    ->subject($subject)
    ->htmlTemplate("api/mail/$template.html.twig")
    ->context($context);

    // on venvoie le mail avec la fonction send de Mailer
    $this->mailer->send($email);
}

}