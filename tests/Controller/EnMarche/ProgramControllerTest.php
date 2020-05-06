<?php

namespace Tests\App\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ProgramControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideActions
     */
    public function testSuccessfulActions(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->isSuccessful($this->client->getResponse());
    }

    public function provideActions()
    {
        yield ['/emmanuel-macron/le-programme'];
        yield ['/emmanuel-macron/le-programme/produire-en-france-et-sauver-la-planete'];
        yield ['/emmanuel-macron/le-programme/eduquer-tous-nos-enfants'];
    }

    /**
     * @dataProvider provideRedirectActions
     */
    public function testRedirectActions(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertClientIsRedirectedTo('/emmanuel-macron/le-programme', $this->client, false, true);
    }

    public function provideRedirectActions()
    {
        yield ['/programme'];
        yield ['/le-programme'];
    }

    public function testProposalDraft()
    {
        $this->client->request(Request::METHOD_GET, '/emmanuel-macron/le-programme/mieux-vivre-de-son-travail');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
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
