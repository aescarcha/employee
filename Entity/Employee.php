<?php

namespace Aescarcha\EmployeeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Aescarcha\BusinessBundle\Entity\Business;

use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=64)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(min="3", max="255")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="\Aescarcha\UserBundle\Entity\User")
     * @Assert\NotNull()
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Aescarcha\BusinessBundle\Entity\Business")
     * @Assert\NotNull()
     */
    protected $business;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    protected $updated;

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


    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Employee
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Employee
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

}

