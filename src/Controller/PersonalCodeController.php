<?php

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use App\Service\PersonalCoder;

/**
 * Class PersonalCodeController
 * @package App\Controller
 */
class PersonalCodeController extends FOSRestController
{
    
    private $coder;
    
    function __construct(PersonalCoder $coder)
    {
        $this->coder = $coder;
    }

    /**
     * @Rest\Get("/validate/{code}")
     * @param string $code 
     * @return View
     */
    public function validateGet(string $code) 
    {
        $result = $this->coder->validateCode($code);
        $data = ['valid' => $result];
        $view = $this->view($data, Response::HTTP_OK);
        return $view;
    }

    /**
     * @Rest\Get("/generate/{dob}/{gender}")
     * @param string $dob
     * @param int $gender
     * @return Response
     */
    public function generateGet(string $dob, $gender)
    {
        try {
            $codes = $this->coder->generateCode($dob, $gender);
        } catch (\Error $e) {
            $codes = false;
        }
        
        if (!$codes) {
            $data = ['error' => 'Invalid request'];
            $response_code = Response::HTTP_BAD_REQUEST;
        } else {
            $data = ['codes' => $codes];
            $response_code = Response::HTTP_OK;
        }
        
        $view = $this->view($data, $response_code);
        return $view;
    }
    
}
