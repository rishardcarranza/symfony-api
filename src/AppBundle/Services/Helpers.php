<?php
/**
 * Created by PhpStorm.
 * User: rcarranza
 * Date: 4/4/2019
 * Time: 3:53 PM
 */

namespace AppBundle\Services;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class Helpers {

    public function getJson($data) {
        $normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
        $encoders = array("json" => new \Symfony\Component\Serializer\Encoder\JsonEncoder());

        $serializer = new Serializer($normalizers, $encoders);

        $json = $serializer->serialize($data, "json");

        $response = new Response();
        $response->setContent($json);
        $response->headers->set("Content-Type", "application/json");

        return $response;
    }
}