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
            'user' => 1,
            'business' => 0,
        ],
        [
            'role' => 'Employee',
            'user' => 2,
            'business' => 0,
        ],
        [
            'role' => 'Employee',
            'user' => 3,
            'business' => 1,
        ]
    ];

    public function load(ObjectManager $manager)
    {
        $businesses = $manager->getRepository('AescarchaBusinessBundle:Business')->findAll();
        foreach ($this->data as $key => $data) {
            $user = $manager->getRepository('AescarchaUserBundle:User')->find($data['user']);
            $business = $businesses[$data['business']];
            $entity = new Employee();
            $entity->setRole($data['role']);
            $entity->setUser($user);
            $entity->setBusiness($business);
            $manager->persist($entity);
        }

        $manager->flush();
    }
}