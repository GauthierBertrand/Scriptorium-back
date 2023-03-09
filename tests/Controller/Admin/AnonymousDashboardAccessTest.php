<?php

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnonymousDashboardAccessTest extends WebTestCase
{
    /**
     * Anonymous access to admin dashbord test
     *
     */
    public function testAnonymousDashbordAccess(): void
    {
        $client = self::createClient();

        $client->request('GET','/admin'); 

        //We have a login page redirection in this case
        $this->assertResponseStatusCodeSame(302);
        
    }
}