<?php

namespace Aescarcha\EmployeeBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

use Aescarcha\BusinessBundle\Entity\Business;

use Aescarcha\EmployeeBundle\Entity\Employee;
use Aescarcha\EmployeeBundle\Transformer\EmployeeTransformer;
use Aescarcha\EmployeeBundle\Transformer\ErrorTransformer;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Serializer\ArraySerializer;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class EmployeeController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Create a new Employee Object",
     *  input="Aescarcha\EmployeeBundle\Entity\Employee",
     *  output="Aescarcha\EmployeeBundle\Entity\Employee",
     *  statusCodes={
     *         201="Returned when create is successful",
     *         400="Returned when data is invalid",
     *     }
     * )
     */
    public function postEmployeesAction( Request $request, Business $business )
    {
        return $this->newAction( $request, $business );
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Finds an displays Business Employee entities",
     *  output="Collection of Aescarcha\EmployeeBundle\Entity\Employee",
     *  requirements={
     *      {"name"="entity", "dataType"="uuid", "description"="Unique id of the business entity"}
     *  },
     *  statusCodes={
     *         200="Returned when entity exists",
     *         404="Returned when entity is not found",
     *     }
     * )
     */
    public function getEmployeesAction(Business $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $fractal = new Manager();

        $entities = $em->getRepository('AescarchaEmployeeBundle:Employee')->findBy(['business' => $entity]);

        $resource = new Collection($entities, new EmployeeTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 200);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Finds an displays a Employee entity",
     *  output="Aescarcha\EmployeeBundle\Entity\Employee",
     *  requirements={
     *      {"name"="business", "dataType"="uuid", "description"="Unique id of the business entity"},
     *      {"name"="entity", "dataType"="uuid", "description"="Unique id of the employee entity"}
     *  },
     *  statusCodes={
     *         200="Returned when entity exists",
     *         404="Returned when entity is not found",
     *     }
     * )
     */
    public function getEmployeeAction( Business $business, Employee $entity)
    {
        $fractal = new Manager();

        $resource = new Item($entity, new EmployeeTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 200);
        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Update an Employee Object",
     *  input="Aescarcha\EmployeeBundle\Entity\Employee",
     *  output="Aescarcha\EmployeeBundle\Entity\Employee",
     *  statusCodes={
     *         200="Returned when update is successful",
     *         400="Returned when data is invalid",
     *     }
     * )
     */
    public function patchEmployeeAction( Request $request, Business $business, Employee $entity )
    {
        return $this->updateAction( $request, $business, $entity );
    }


    /**
     * Handles Post and may hanlde PUT in the future
     */
    protected function newAction( Request $request, Business $business )
    {
        $entity = new Employee();
        $validator = $this->get('validator');
        $fractal = new Manager();
        $user = $this->get('fos_user.user_manager')->findUserBy(['id' => $request->request->get('user')]);

        $entity->setBusiness($business);
        $entity->setRole($request->request->get('role'));
        if($user){
            $entity->setUser($user);
        }

        $errors = $validator->validate($entity);
        if ( count($errors) === 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $resource = new Item($entity, new EmployeeTransformer);
            $view = $this->view($fractal->createData($resource)->toArray(), 201);
            return $this->handleView($view);
        }

        //This serializer won't set the "data" namespace for errors
        $fractal->setSerializer(new ArraySerializer());
        $resource = new Item($errors->get(0), new ErrorTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 400);

        return $this->handleView($view);
    }

    /**
     * Handle PATCH and POST
     */
    protected function updateAction( Request $request, Business $business, Employee $entity )
    {
        $this->checkRights( $entity );
        
        $validator = $this->get('validator');
        $fractal = new Manager();

        $entity->setRole($request->request->get('role'));
        $errors = $validator->validate($entity);
        if ( count($errors) === 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $resource = new Item($entity, new EmployeeTransformer);
            $view = $this->view($fractal->createData($resource)->toArray(), 200);
            return $this->handleView($view);
        }

        //This serializer won't set the "data" namespace for errors
        $fractal->setSerializer(new ArraySerializer());
        $resource = new Item($errors->get(0), new ErrorTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 400);

        return $this->handleView($view);
    }

    protected function checkRights( Employee $entity )
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        if($entity->getBusiness()->getUser()->getId() !== $user->getId()){
            throw $this->createAccessDeniedException( "You don't own entity." );
        }
    }

}
