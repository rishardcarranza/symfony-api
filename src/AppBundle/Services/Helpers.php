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

    public $jwt_auth;

    /**
     * Helpers constructor.
     */
    public function __construct($jwt_auth) {
        $this->jwt_auth = $jwt_auth;
    }

    public function authCheck($hash, $getIdentity = false) {
        $jwt_auth = $this->jwt_auth;

        $auth = false;

        if ($hash != null) {
            if ($getIdentity == false) {
                $check_token = $jwt_auth->checkToken($hash);
                if ($check_token) {
                    $auth = true;
                }
            } else {
                $check_token = $jwt_auth->checkToken($hash, true);
                if (is_object($check_token)) {
                    $auth = $check_token;
                }
            }
        }

        return $auth;
    }

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