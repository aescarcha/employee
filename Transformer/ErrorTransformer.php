<?php
namespace Aescarcha\EmployeeBundle\Transformer;

use Aescarcha\EmployeeBundle\Entity\Employee;
use League\Fractal;

/**
 * Transforms the errors from symfony to our API format
 */
class ErrorTransformer extends Fractal\TransformerAbstract
{
    public function transform( \Symfony\Component\Validator\ConstraintViolation $error )
    {
        return [
            'error' => [
                'type' => get_class($error),
                'code' => $error->getCode(),
                'property' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
                'doc_url' => '',
            ]
        ];
    }

}

