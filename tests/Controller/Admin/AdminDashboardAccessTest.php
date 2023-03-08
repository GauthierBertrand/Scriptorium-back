<?php

namespace App\Tests\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminDashboardAccessTest extends WebTestCase
{
    /**
     * Access testing with a ROLE-USER connection
     *
     * @return void
     */
    public function testAdminDashboardAccess(): void
    {
        $client = static::createClient();

        //UserRepository access
        $userRepository = static::getContainer()->get(UserRepository::class);

        //Retrieve a user with a ROLE_ADMIN
        $testUser = $userRepository->findOneByEmail('freir@gmail.com');

        //Connect the user
        $client->loginUser($testUser);

        $client->request('GET', '/admin');
        
        //We must have a HTTP 200 OK response in this case
        $this->assertResponseStatusCodeSame(200);
    }
}