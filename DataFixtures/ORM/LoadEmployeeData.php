<?php 
namespace Aescarcha\EmployeeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Aescarcha\EmployeeBundle\Entity\Employee;

class LoadEmployeeData implements FixtureInterface
{
    protected $data = [
        [
            'role' => 'Fixtured',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('AescarchaUserBundle:User')->find(1);
        $business = $manager->getRepository('AescarchaBusinessBundle:Business')->findOneBy([]);
        foreach ($this->data as $key => $data) {
            $entity = new Employee();
            $entity->setRole($data['role']);
            $entity->setUser($user);
            $entity->setBusiness($business);
            $manager->persist($entity);
        }

        $manager->flush();
    }
}