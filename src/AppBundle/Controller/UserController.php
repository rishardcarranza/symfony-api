<?php
/**
 * Created by PhpStorm.
 * User: rcarranza
 * Date: 4/8/2019
 * Time: 10:33 AM
 */

namespace AppBundle\Controller;

use BackendBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Tests\A;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends Controller {

    public function newAction(Request $request) {
        $helpers = $this->get("app.helpers");

        $json = $request->get("json", null);
        $params = json_decode($json);
        $data = array(
            "status" => "error",
            "code" => 400,
            "msg" => "User not created"
        );

        if ($json != null) {
            $createdAt = new \DateTime("now");
            $image = null;
            $role = "user";
            $email =  (isset($params->email)) ? $params->email : null;
            $name =  (isset($params->name) && ctype_alpha($params->name)) ? $params->name : null;
            $surname =  (isset($params->surname) && ctype_alpha($params->surname)) ? $params->surname : null;
            $password =  (isset($params->password)) ? $params->password : null;

            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid !";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);

            if ($email != null && count($validate_email) == 0 && $password != null && $name != null && $surname != null) {
                $user = new User();
                $user->setCreatedAt($createdAt);
                $user->setImage($image);
                $user->setRole($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);

                // Encrypt the password
                $pwd = hash("sha256", $password);
                $user->setPassword($pwd);

                $em = $this->getDoctrine()->getManager();
                $isset_user = $em->getRepository("BackendBundle:User")->findBy(
                    array(
                        "email" => $email
                    ));

                if (count($isset_user) == 0) {
                    $em->persist($user);
                    $em->flush();

                    $data["status"] = "success";
                    $data["msg"] = "New user created.";
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "User exists, not created."
                    );
                }
            }
        }

        return $helpers->getJson($data);
    }

    public function editAction(Request $request) {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authcheck = $helpers->authCheck($hash);

        if ($authcheck) {

            $identity = $helpers->authCheck($hash, true);

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository("BackendBundle:User")->findOneBy(array(
                "id" => $identity->sub
            ));

            $json = $request->get("json", null);
            $params = json_decode($json);
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "User not updated"
            );

            if ($json != null) {
                $createdAt = new \DateTime("now");
                $image = null;
                $role = "user";
                $email = (isset($params->email)) ? $params->email : null;
                $name = (isset($params->name) && ctype_alpha($params->name)) ? $params->name : null;
                $surname = (isset($params->surname) && ctype_alpha($params->surname)) ? $params->surname : null;
                $password = (isset($params->password)) ? $params->password : null;

                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "This email is not valid !";
                $validate_email = $this->get("validator")->validate($email, $emailConstraint);

                if ($email != null && count($validate_email) == 0 && $name != null && $surname != null) {
//                    $user = new User();
                    $user->setCreatedAt($createdAt);
                    $user->setImage($image);
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    if ($password != null) {
                        // Encrypt the password
                        $pwd = hash("sha256", $password);
                        $user->setPassword($pwd);
                    }

                    $isset_user = $em->getRepository("BackendBundle:User")->findBy(
                        array(
                            "email" => $email
                        ));

                    if (count($isset_user) == 0 || $identity->email == $email) {
                        $em->persist($user);
                        $em->flush();

                        $data["status"] = "success";
                        $data["msg"] = "User updated.";
                    } else {
                        $data = array(
                            "status" => "error",
                            "code" => 400,
                            "msg" => "User exists, not updated."
                        );
                    }
                }
            }

        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Not authenticated, authorization not valid."
            );
        }

        return $helpers->getJson($data);
    }

    public function uploadImageAction(Request $request) {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authcheck = $helpers->authCheck($hash);

        if ($authcheck) {
            $identity = $helpers->authCheck($hash, true);

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository("BackendBundle:User")->findOneBy(array(
                "id" => $identity->sub
            ));

            // Upload file
            $file = $request->files->get("image");
            if (!empty($file) && $file != null) {
                $ext = $file->guessExtension();
                if ($ext == "png" || $ext == "jpg" || $ext == "jpeg" || $ext == "gif") {
                    $file_name = time() . "." . $ext;
                    $file->move("uploads/users", $file_name);

                    $user->setImage($file_name);
                    $em->persist($user);
                    $em->flush();

                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "msg" => "Image for user uploaded."
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Image extension not valid."
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Image not uploaded."
                );
            }

        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Not authenticated, authorization not valid."
            );
        }

        return $helpers->getJson($data);
    }
}