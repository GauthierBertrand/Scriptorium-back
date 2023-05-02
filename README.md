# Scriptorium

Scriptorium is an application that will easily guide a player through the process of creating a character for the "Chroniques oubliées" french role-playing game. This application will allow the player to generate his character sheet in PDF format.

- An unregistered USER can only generate a pdf character sheet.

- A registered USER can save, modify, and regenerate in pdf the character sheets created.
  User account connexion route : `/connection`

- A user with a ROLE_GAME_MASTER role can manage groups with many players.
  Backoffice connexion route : `/api/game-master`

- A user with ROLE_ADMIN role can CRUD all parts of the application (users,sheets).
  Backoffice connexion route : `/admin`

- The frontend part is managed by the repository at the address `https://github.com/O-clock-Lucy/projet-03-fiches-jeux-de-role-front/tree/develop`

## Development environment

### Technical specifications

PHP 7.4
Symfony 5.4 framework
Composer

### Commands to execute when importing the project on a local machine in -dev mode

Create .env.local file

```bash
composer install
bin/console doctrine:database:create scriptorium
bin/console doctrine:migrations:migrate
bin/console doctrine:fixtures:load
bin/console lexik:jwt:generate-keypair
```

## Services

### rateLimiter


#### Cron

To activate the cron job for removing unused tokens(used for account activation and reset-password), use the `app:token:remove` command.

#### Mail Service

The user account activation and password recovery is done by email. Configure the properties of your mail service in the .env file on the MAILER_DSN line.

In the `reset_passord_success.html.twig` template, fill in the redirection address to the front office (ex: user login page).

#### Security

Rate Limiter is already configured. If you need secure more Routes, see comments in the `RateLimiterService.php`
