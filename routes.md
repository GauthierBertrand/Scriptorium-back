# Routes API

## Chronique Controller
|endpoints|methods|ressources|access|
|--|--|--|--|
|/api/classes|GET|Récupère les classes et leurs équipements de départ|PUBLIC|
|/api/races|GET|Récupère les races et leurs capacités associées|PUBLIC|
|/api/ways/{id}|GET|Récupère les voies et leurs compétences en fonction de la classe choisi|PUBLIC|
|/api/stats|GET|Récupère le nom des stats et leur description|PUBLIC|
|/api/religions|GET|Récupère le nom des religions et leur description|PUBLIC|

## Sheet Controller
|endpoints|methods|ressources|access|
|--|--|--|--|
|/api/characters/{id}|GET|Récupère les informations d'une fiche en fonction de son id|ROLE_USER|
|/api/characters/users|GET|Récupère toutes les fiches d'un utilisateur en fonction de son token|ROLE_USER|
|/api/characters|POST|Créer une fiche en fonction des données en cache (impossible à utiliser sans passer par la génération d'un pdf)|ROLE_USER|
|/api/characters/{id}|PATCH|Permet l'édition d'une fiche en fonction de son id|ROLE_USER|
|/api/characters/{id}|DELETE|Permet la suppresion d'une fiche en fonction de son id|ROLE_USER|

## Pdf Controller
|endpoints|methods|ressources|access|
|--|--|--|--|
|/api/generator|POST|Génère un pdf en fonction des données envoyées et place ces informations dans le cache|PUBLIC|

## User Controller
|endpoints|methods|ressources|access|
|--|--|--|--|
|/api/users/password|PATCH|Permet la modification du password de l'utilisateur|ROLE_USER|
|/api/users/delete|DELETE|Permet de supprimer le compte d'un utilisateur en fonction de son token|ROLE_USER|

## GameMaster Controller
|endpoints|methods|ressources|access|
|--|--|--|--|
|/api/game-master/users|PATCH|Permet d'obtenir le role de GameMaster |ROLE_USER|
|/api/game-master/groups|GET|Permet d'obtenir tous les groupes avec leur joueur d'un GM |ROLE_GAME_MASTER|
|/api/game-master/groups|POST|Permet de créer un groupe et d'obtenir son code en retour pour un GM |ROLE_GAME_MASTER|
|/api/game-master/groups/add|POST|Permet pour un joueur de rejoindre un groupe via son code|ROLE_USER|
|/api/game-master/groups/{id}/users|DELETE|Permet à un GM de supprimer un joueur de son groupe via son id|ROLE_GAME_MASTER|
|/api/game-master/groups/{io}/delete|DELETE|Permet de supprimer un groupe pour un GM |ROLE_GAME_MASTER|

## Security Controller
|endpoints|methods|ressources|access|
|--|--|--|--|
|/inscription|POST|Permet à n'importe qui de s'inscrire (envoi un mail de validation de compte)|PUBLIC|
|/activation/{token}|GET|Permet d'activer le compte d'un utilisateur via son token associé |PUBLIC|
|/api/resend-activation|GET|Permet à un utilisateur de demander le renvoi du mail de validation |ROLE_USER|
|/admin|GET-POST|Permet d'obtenir le formulaire de connexion pour acceder au Back-Office|PUBLIC|
|/reset-password|GET|Permet à un utilisateur de generer un mail pour réinitialiser son password|ROLE_USER|

## Dashboard Controller
|endpoints|methods|ressources|access|
|--|--|--|--|
|/admin|GET|Affiche l'interface principal du Back-Office|ROLE_ADMIN|

