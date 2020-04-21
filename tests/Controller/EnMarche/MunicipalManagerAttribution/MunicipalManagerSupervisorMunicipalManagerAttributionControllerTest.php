<?php

namespace Tests\AppBundle\Controller\EnMarche\MunicipalManagerAttribution;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class MunicipalManagerSupervisorMunicipalManagerAttributionControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testReferentCanReachMunicipalManagerAttributionForm()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Espace Responsable attribution')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $cities = $crawler->filter('.datagrid__table-manager tbody tr');
        $this->assertCount(3, $cities);

        $this->assertContains('Lille (59350)', $cities->eq(0)->text());
        $this->assertContains('Roubaix (59512)', $cities->eq(1)->text());
        $this->assertContains('Seclin (59560)', $cities->eq(2)->text());
    }

    public function testAdherentCanNotSeeMunicipalManagerAttributionForm()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');

        $this->client->request(Request::METHOD_GET, '/espace-responsable-attribution/responsables-communaux');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
