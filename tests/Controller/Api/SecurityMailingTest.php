<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityMailingTest extends WebTestCase
{
    /**
     * Route with e-mailing testing
     *
     * @return void
     */
    public function testMailSend()
    {
        
        $content = [
            "email" => "test1@gmail.com",
           
        ];

        $client = static::createClient();
        $client->request('POST', '/reset-password', [], [], [], json_encode($content));
        
        $this->assertResponseIsSuccessful();

        $this->assertEmailCount(1); 
    }
}