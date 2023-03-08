<?php

namespace App\Tests\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserDashboardAcessTest extends WebTestCase
{
    /**
     * Access testing with a ROLE-USER connection
     *
     * @return void
     */
    public function testUserDashboardAccess(): void
    {
        $client = static::createClient();

        //UserRepository access
        $userRepository = static::getContainer()->get(UserRepository::class);

        //Retrieve a user with a USER_ROLE
        $testUser = $userRepository->findOneByEmail('odin@gmail.com');

        //Connect the user 
        $client->loginUser($testUser);

        $client->request('GET', '/admin');

        //We must have a HTTP 403 Forbidden reponse in this case
        $this->assertResponseStatusCodeSame(403);
    }
}