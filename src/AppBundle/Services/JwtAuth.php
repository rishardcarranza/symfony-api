<?php
/**
 * Created by PhpStorm.
 * User: rcarranza
 * Date: 4/8/2019
 * Time: 8:42 AM
 */

namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth {

    public $manager;
    public $key;

    /**
     * JwtAuth constructor.
     */
    public function __construct($manager) {
        $this->manager = $manager;
        $this->key = "secret-key";
    }

    public function signup($email, $password, $getHash = null) {

        $user = $this->manager->getRepository('BackendBundle:User')->findOneBy(
                array(
                    "email" => $email,
                    "password" => $password
                )
            );

        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }

        if ($signup) {
            $token = array(
                "sub" => $user->getId(),
                "email" => $user->getEmail(),
                "name" => $user->getName(),
                "surname" => $user->getSurname(),
                "password" => $user->getPassword(),
                "image" => $user->getImage(),
                "iat" => time(),
                "exp" => time() + (7*24*60*60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, array('HS256'));

            if ($getHash != null) {
                return $jwt;
            } else {
                return $decode;
            }
        } else {
            return array("status" => "error", "data" => "Login failed !");
        }
    }

    public function checkToken($jwt, $getIdentity = false) {
        $auth = false;

        try {
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));
        } catch (\UnexpectedValueException $exception) {
            $auth = false;
        } catch (\DomainException $exception) {
            $auth = false;
        }

        if (isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        } else {
            return $auth;
        }
    }
}