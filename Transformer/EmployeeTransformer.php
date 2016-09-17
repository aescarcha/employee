<?php
namespace Aescarcha\EmployeeBundle\Transformer;

use Aescarcha\EmployeeBundle\Entity\Employee;

use Aescarcha\BusinessBundle\Transformer\BusinessTransformer;
use Aescarcha\UserBundle\Transformer\UserTransformer;

use League\Fractal;

class EmployeeTransformer extends Fractal\TransformerAbstract
{

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'business'
    ];

    public function transform( Employee $entity )
    {
        return [
            'id'      => $entity->getId(),
            'role'   => $entity->getRole(),
            'userId'   => $entity->getUser()->getId(),
            'businessId'   => $entity->getBusiness()->getId(),
            'links'   => [
                'self' => [
                    'rel' => 'self',
                    'uri' => '/employees/'.$entity->getId(),
                ],
            ],
        ];
    }

    /**
     * Include Business
     *
     * @return League\Fractal\ItemResource
     */
    public function includeBusiness(Employee $entity)
    {
        $business = $entity->getBusiness();

        return $this->item($business, new BusinessTransformer);
    }

    /**
     * Include User
     *
     * @return League\Fractal\ItemResource
     */
    public function includeUser(User $entity)
    {
        $user = $entity->getUser();

        return $this->item($user, new UserTransformer);
    }

}

