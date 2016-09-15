<?php

namespace Aescarcha\EmployeeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Aescarcha\BusinessBundle\Entity\Business;

/**
 * Employee
 *
 * @ORM\Table(name="employee")
 * @ORM\Entity(repositoryClass="Aescarcha\EmployeeBundle\Repository\EmployeeRepository")
 */
class Employee
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=64)
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="\Aescarcha\UserBundle\Entity\User")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Aescarcha\BusinessBundle\Entity\Business")
     */
    protected $business;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return Employee
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set User
     * 
     * @param UserInterface $user
     * @return Employee
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     * 
     * @return UserInterface $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set Business
     * 
     * @param Business $business
     * @return Employee
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * Get business
     * 
     * @return Business $business
     */
    public function getBusiness()
    {
        return $this->business;
    }
}

