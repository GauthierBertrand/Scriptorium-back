<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChroniqueControllerAccessTest extends WebTestCase
{   
    /**
     * Test to assert response datas types. Json in this case
     *
     * @dataProvider getRoutes
     */
    public function testJsonResponseOk($route): void
    {
        $client = static::createClient();
        $client->request('GET', $route);
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertResponseIsSuccessful();
    }

    public function getRoutes()
    {
        yield ['/api/classes'];
        yield ['/api/races'];
        yield ['/api/ways/2'];
        yield ['/api/stats'];
        yield ['/api/religions'];
    }
}