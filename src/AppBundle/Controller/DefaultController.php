<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Tests\A;
use Symfony\Component\Validator\Constraints as Assert;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    public function loginAction(Request $request) {
        $helpers = $this->get("app.helpers");
        $jwt_auth = $this->get("app.jwt_auth");

        // Received a JSON by POST
        $json = $request->get("json", null);

        if ($json != null) {
            $params = json_decode($json);

            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->gethash)) ? $params->gethash : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid !";

            $validate_email = $this->get("validator")->validate($email, $emailConstraint);

            // Encrypt the password
            $pwd = hash("sha256", $password);

            if (count($validate_email) == 0 && $password != null) {
                if ($getHash == null) {
                    $signup = $jwt_auth->signup($email, $pwd);
                } else {
                    $signup = $jwt_auth->signup($email, $pwd, true);
                }

                return new JsonResponse($signup);
            } else {
                return $helpers->getJson(array(
                        "status" => "error",
                        "data" => "Login not valid !"
                    )
                );
            }

        } else {
            return $helpers->getJson(array(
                    "status" => "error",
                    "data" => "Json parameters error !"
                )
            );
        }


    }

    public function pruebasAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $hash = $request->get('authorization', null);
        $check = $helpers->authCheck($hash);

        var_dump($check);
        die();
//        $em = $this->getDoctrine()->getManager();
//        $users = $em->getRepository('BackendBundle:User')->findAll();
//
//        return $helpers->getJson($users);
    }
}
