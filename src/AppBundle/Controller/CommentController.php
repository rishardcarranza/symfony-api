<?php
/**
 * Created by PhpStorm.
 * User: rcarranza
 * Date: 5/17/2019
 * Time: 11:11 AM
 */

namespace AppBundle\Controller;

use BackendBundle\Entity\Comment;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class CommentController extends  Controller {

    public function newAction(Request $request) {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authcheck = $helpers->authCheck($hash);

        if ($authcheck) {
            $em = $this->getDoctrine()->getManager();
            $identity = $helpers->authCheck($hash, true);
            $json = $request->get("json", null);

            if ($json != null) {
                $params = json_decode($json);
                $createdAt = new \DateTime("now");

                $user_id = (isset($identity->sub) && $identity->sub != null) ? $identity->sub : null;
                $video_id = (isset($params->video_id)) ? $params->video_id : null;
                $body = (isset($params->body)) ? $params->body : null;

                if ($user_id != null && $video_id != null) {
                    $user = $em->getRepository("BackendBundle:User")->findOneBy(array(
                        "id" => $user_id
                    ));

                    $video = $em->getRepository("BackendBundle:Video")->findOneBy(array(
                        "id" => $video_id
                    ));

                    // Save video instance
                    $comment = new Comment();
                    $comment->setUser($user);
                    $comment->setVideo($video);
                    $comment->setBody($body);
                    $comment->setCreatedAt($createdAt);

                    $em->persist($comment);
                    $em->flush();

                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "msg" => "Comment created success"
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Comment not created."
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Video not created, params failed."
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

    public function deleteAction(Request $request, $id = null) {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authcheck = $helpers->authCheck($hash);

        if ($authcheck) {
            $em = $this->getDoctrine()->getManager();
            $identity = $helpers->authCheck($hash, true);

            $user_id = (isset($identity->sub) && $identity->sub != null) ? $identity->sub : null;
            $comment = $em->getRepository("BackendBundle:Comment")->findOneBy(array(
                "id" => $id
            ));

            if (is_object($comment) && $user_id != null) {
                if ($user_id == $comment->getUser()->getId() || $user_id == $comment->getVideo()->getUser()->getId()) {
                    $em->remove($comment);
                    $em->flush();

                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "msg" => "Comment deleted success."
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Comment not deleted. User not comment owner or video owner."
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Comment not deleted."
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

    public function listAction(Request $request, $id = null) {
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $video = $em->getRepository("BackendBundle:Video")->findOneBy(array(
            "id" => $id
        ));

        $comments = $em->getRepository("BackendBundle:Comment")->findBy(array(
            "video" => $video
        ), array("id"=>"desc"));

        if (count($comments) >= 1) {
            $data = array(
                "status" => "success",
                "code" => 200,
                "data" => $comments
            );
        } else {
            $data = array(
                "status" => "error",
                "code" => 400,
                "msg" => "Don't exists comments in this video."
            );
        }

        return $helpers->getJson($data);
    }
}