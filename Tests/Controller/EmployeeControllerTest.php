<?php

namespace Aescarcha\EmployeeBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class EmployeeControllerTest extends WebTestCase
{
    protected $manager;
    protected $client;

    public function setUp()
    {
        $classes = array(
            'Aescarcha\UserBundle\DataFixtures\ORM\LoadUserData',
            'Aescarcha\BusinessBundle\DataFixtures\ORM\LoadBusinessData',
        );
        $this->loadFixtures($classes);
        $this->client = static::createClient();
        $this->manager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->login();
    }

    public function testCreate()
    {
        $business = $this->getOneEntity();
        $user = $this->getOneEntity('AescarchaUserBundle:User');
        $crawler = $this->client->request(
                         'POST',
                         '/businesses/' . $business->getId() . '/employees',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'),
                         '{
                            "user":"'. $user->getId() . '",
                            "role": "WAITER"
                           }'
                         );
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'WAITER', $response['data']['role'] );
        $this->assertEquals( $user->getId(), $response['data']['userId'] );
        $this->assertEquals( $business->getId(), $response['data']['businessId'] );
        $this->assertContains( '/employees/', $response['data']['links']['self']['uri'] );
    }

    public function testCreateBadUser()
    {
        $business = $this->getOneEntity();
        $crawler = $this->client->request(
                         'POST',
                         '/businesses/' . $business->getId() . '/employees',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'),
                         '{
                            "user":"ASDF",
                            "role": "WAITER"
                           }'
                         );
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'Symfony\Component\Validator\ConstraintViolation', $response['error']['type'] );
        $this->assertEquals( 'ad32d13f-c3d4-423b-909a-857b961eb720', $response['error']['code'] );
        $this->assertEquals( 'user', $response['error']['property'] );
        $this->assertEquals( 'This value should not be null.', $response['error']['message'] ); //this message should be improved
        $this->assertEquals( '', $response['error']['doc_url'] );
    }


    private function getOneEntity($repository = 'AescarchaBusinessBundle:Business' )
    {
        return $this->manager->getRepository( $repository )->findAll()[0];
    }

    /**
     * Fake Login, @todo move this to use auth token
     * @param  string $userName
     */
    protected function login( $userName = 'Alvaro')
    {
        $session = $this->client->getContainer()->get('session');
        $container = $this->client->getContainer();
        $userManager = $container->get('fos_user.user_manager');
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');
        $user = $userManager->findUserBy(array('username' => $userName));
        $loginManager->loginUser($firewallName, $user);
        $container->get('session')->set('_security_' . $firewallName,
                                        serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->set('_locale', $user->getLocale());

        $container->get('session')->save();
        $this->client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId()));
        return $user;
    }

}
