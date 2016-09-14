<?php

namespace Aescarcha\EmployeeBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

use Aescarcha\BusinessBundle\Entity\Business;
use Aescarcha\BusinessBundle\Transformer\BusinessTransformer;
use Aescarcha\BusinessBundle\Transformer\ErrorTransformer;

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
     *  description="Create a new Business Object",
     *  input="Aescarcha\EmployeeBundle\Entity\Business",
     *  output="Aescarcha\EmployeeBundle\Entity\Business",
     *  statusCodes={
     *         201="Returned when create is successful",
     *         400="Returned when data is invalid",
     *     }
     * )
     */
    public function postEmployeeAction( Request $request, Business $business )
    {
        dump($business);
        die("XAAA");
        return $this->newAction( $request );
    }


    protected function newAction( Request $request )
    {
        $entity = new Business();
        $validator = $this->get('validator');
        $fractal = new Manager();

        $entity->setName($request->request->get('name'));
        $entity->setDescription($request->request->get('description'));
        $entity->setLatitude($request->request->get('latitude'));
        $entity->setLongitude($request->request->get('longitude'));

        $errors = $validator->validate($entity);
        if ( count($errors) === 0 ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $resource = new Item($entity, new BusinessTransformer);
            $view = $this->view($fractal->createData($resource)->toArray(), 201);
            return $this->handleView($view);
        }

        //This serializer won't set the "data" namespace for errors
        $fractal->setSerializer(new ArraySerializer());
        $resource = new Item($errors->get(0), new ErrorTransformer);
        $view = $this->view($fractal->createData($resource)->toArray(), 400);

        return $this->handleView($view);
    }

    protected function checkRights( Business $entity )
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        if($entity->getUser()->getId() !== $user->getId()){
            throw $this->createAccessDeniedException( "You can't delete this entity." );
        }
    }

}
