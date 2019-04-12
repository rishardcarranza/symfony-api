<?php
/**
 * Created by PhpStorm.
 * User: rcarranza
 * Date: 4/10/2019
 * Time: 4:06 PM
 */

namespace AppBundle\Controller;

use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class VideoController extends Controller {

    public function newAction(Request $request) {
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

            if ($json != null) {
                $params = json_decode($json);

                $createdAt = new \DateTime("now");
                $updatedAt = new \DateTime("now");
                $image = null;
                $video_path = null;

                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (isset($params->title)) ? isset($params->title) : null;
                $description = (isset($params->description)) ? isset($params->description) : null;
                $status = (isset($params->status)) ? isset($params->status) : null;

                if ($user_id != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();

                    $user = $em->getRepository("BackendBundle:User")->findOneBy(array(
                        "id" => $user_id
                    ));
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

}