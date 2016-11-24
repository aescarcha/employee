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
            'Aescarcha\EmployeeBundle\DataFixtures\ORM\LoadEmployeeData',
        );
        $this->loadFixtures($classes, null, 'doctrine', \Doctrine\Common\DataFixtures\Purger\ORMPurger::PURGE_MODE_TRUNCATE);
        $this->client = static::createClient();
        $this->manager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->login();
    }

    
    protected function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = null)
    {
        $container = $this->getContainer();
        /** @var ManagerRegistry $registry */
        $registry = $container->get($registryName);
        /** @var ObjectManager $om */
        $om = $registry->getManager($omName);
        $connection = $om->getConnection();
        if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\AbstractMySQLDriver) {
            $connection->exec(sprintf('SET foreign_key_checks=%s', 0));
        }
        parent::loadFixtures($classNames, $omName , $registryName , $purgeMode);
        if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\AbstractMySQLDriver) {
            $connection->exec(sprintf('SET foreign_key_checks=%s', 1));
        }
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

    public function testGet()
    {
        $user = $this->getOneEntity('AescarchaUserBundle:User');
        $employee = $this->getOneEntity('AescarchaEmployeeBundle:Employee');
        $crawler = $this->client->request(
                         'GET',
                         '/businesses/' .$employee->getBusiness()->getId(). '/employees/' . $employee->getId(),
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( $employee->getUser()->getId(), $response['data']['userId'] );
        $this->assertEquals( $employee->getBusiness()->getId(), $response['data']['businessId'] );
        $this->assertEquals( '/businesses/' .$employee->getBusiness()->getId(). '/employees/' . $employee->getId(), $response['data']['links']['self']['uri'] );
    }

    public function testGetFromBusiness()
    {
        $business = $this->getOneEntity();
        $user = $this->getOneEntity('AescarchaUserBundle:User');
        $crawler = $this->client->request(
                         'GET',
                         '/businesses/' . $business->getId() . '/employees',
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'Fixtured', $response['data'][0]['role'] );
        $this->assertEquals( $business->getId(), $response['data'][0]['businessId'] );
        $this->assertEquals( $business->getUser()->getId(), $response['data'][0]['userId'] );
    }


    public function testUpdate()
    {
        $entity = $this->getOneEntity('AescarchaEmployeeBundle:Employee');
        $crawler = $this->client->request(
                         'PATCH',
                         '/businesses/' . $entity->getBusiness()->getId() . '/employees/' . $entity->getId(),
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'),
                         '{"role": "ROLEEDIT"}'
                         );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'ROLEEDIT', $response['data']['role'] );
        $this->assertContains( '/businesses/' . $entity->getBusiness()->getId() . '/employees/' . $entity->getId(), $response['data']['links']['self']['uri'] );
    }

    public function testUpdateBadData()
    {
        $entity = $this->getOneEntity('AescarchaEmployeeBundle:Employee');
        $crawler = $this->client->request(
                         'PATCH',
                         '/businesses/' . $entity->getBusiness()->getId() . '/employees/' . $entity->getId(),
                         array(),
                         array(),
                         array('CONTENT_TYPE' => 'application/json'),
                         '{"role": ""}'
                         );
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'Symfony\Component\Validator\ConstraintViolation', $response['error']['type'] );
        $this->assertEquals( 'c1051bb4-d103-4f74-8988-acbcafc7fdc3', $response['error']['code'] );
        $this->assertEquals( 'role', $response['error']['property'] );
        $this->assertEquals( 'This value should not be blank.', $response['error']['message'] );
        $this->assertEquals( '', $response['error']['doc_url'] );
    }

    public function testDelete()
    {
        $entity = $this->getOneEntity('AescarchaEmployeeBundle:Employee');
        $crawler = $this->client->request(
                                          'DELETE',
                                          '/businesses/' . $entity->getBusiness()->getId() . '/employees/' . $entity->getId(),
                                          array(),
                                          array(),
                                          array('CONTENT_TYPE' => 'application/json'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals( 'Fixtured', $response['data']['role'] );
        $this->assertEquals( $entity->getUser()->getId(), $response['data']['userId'] );
        $this->assertEquals( $entity->getBusiness()->getId(), $response['data']['businessId'] );

        $crawler = $this->client->request(
                                          'GET',
                                          '/businesses/' . $entity->getBusiness()->getId() . '/employees/' . $entity->getId(),
                                          array(),
                                          array(),
                                          array('CONTENT_TYPE' => 'application/json'));
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    private function getOneEntity($repository = 'AescarchaBusinessBundle:Business' )
    {
        return $this->manager->getRepository( $repository )->findOneBy([]);
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
