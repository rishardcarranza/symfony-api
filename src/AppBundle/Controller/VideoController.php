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
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;

                if ($user_id != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();

                    $user = $em->getRepository("BackendBundle:User")->findOneBy(array(
                        "id" => $user_id
                    ));

                    // Save video instance
                    $video = new Video();
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setStatus($status);
                    $video->setCreatedAt($createdAt);
                    $video->setUpdatedAt($updatedAt);

                    $em->persist($video);
                    $em->flush();

                    $video = $em->getRepository("BackendBundle:Video")->findOneBy(
                        array(
                            "user" => $user,
                            "title" => $title,
                            "status" => $status,
                            "createdAt" => $createdAt
                    ));

                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "data" => $video
                    );
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Video not created."
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

    public function editAction(Request $request, $id = null) {
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
                $video_id = $id;
//                $createdAt = new \DateTime("now");
                $updatedAt = new \DateTime("now");
                $image = null;
                $video_path = null;

                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;

                if ($user_id != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();

                    $video = $em->getRepository("BackendBundle:Video")->findOneBy(
                        array(
                            "id" => $video_id
                        ));

                    if (isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
                        // Update video instance
                        $video->setTitle($title);
                        $video->setDescription($description);
                        $video->setStatus($status);
                        $video->setUpdatedAt($updatedAt);

                        $em->persist($video);
                        $em->flush();

                        $data = array(
                            "status" => "success",
                            "code" => 200,
                            "data" => "Video updated with success."
                        );
                    } else {
                        $data = array(
                            "status" => "error",
                            "code" => 400,
                            "msg" => "Video updated error, user is not owner."
                        );
                    }
                } else {
                    $data = array(
                        "status" => "error",
                        "code" => 400,
                        "msg" => "Video not updated."
                    );
                }
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Video not updated, params failed."
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

    public function uploadAction(Request $request, $id = null) {
        $helpers = $this->get("app.helpers");

        $hash = $request->get("authorization", null);
        $authcheck = $helpers->authCheck($hash);

        if ($authcheck) {
            $identity = $helpers->authCheck($hash, true);

            $video_id = $id;

            $em = $this->getDoctrine()->getManager();
            $video = $em->getRepository("BackendBundle:Video")->findOneBy(array(
                "id" => $video_id
            ));

            if ($video_id != null  && isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
                $file_image = $request->files->get("image", null);
                $file_video = $request->files->get("video", null);

                if ($file_image != null && !empty($file_image)) {
                    $ext = $file_image->guessExtension();

                    if ($ext == "jpg" || $ext == "jpeg" || $ext == "png") {
                        $image_name = time().".".$ext;
                        $path_of_file = "uploads/video_images/video_".$video_id;
                        $file_image->move($path_of_file, $image_name);

                        $video->setImage($image_name);
                        $data = array(
                            "status" => "success",
                            "code" => 200,
                            "msg" => "Image file for video uploaded."
                        );
                    } else {
                        $data = array(
                            "status" => "error",
                            "code" => 400,
                            "msg" => "Image format nor valid."
                        );
                    }
                } elseif ($file_video != null && !empty($file_video)) {
                    $ext = $file_video->guessExtension();

                    if ($ext == "mp4" || $ext == "avi" || $ext == "mov") {
                        $video_name = time().".".$ext;
                        $path_of_file = "uploads/video_files/video_".$video_id;
                        $file_video->move($path_of_file, $video_name);

                        $video->setVideoPath($video_name);
                        $data = array(
                            "status" => "success",
                            "code" => 200,
                            "msg" => "Video file uploaded."
                        );
                    } else {
                        $data = array(
                            "status" => "error",
                            "code" => 400,
                            "msg" => "Video format nor valid."
                        );
                    }

                }

                $em->persist($video);
                $em->flush();
            } else {
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "msg" => "Video uploaded error, user is not owner."
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

    public function videosAction(Request $request) {
        $helpers = $this->get("app.helpers");

        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT v FROM BackendBundle:Video v ORDER BY v.id DESC";
        $query = $em->createQuery($dql);

        $page = $request->query->getInt("page", 1);
        $paginator = $this->get("knp_paginator");
        $items_per_page = 6;

        $pagination = $paginator->paginate($query, $page, $items_per_page);
        $total_items_count = $pagination->getTotalItemCount();

        $data = array(
            "status" => "success",
            "code" => 200,
            "total_items" => $total_items_count,
            "actual_page" => $page,
            "items_per_page" => $items_per_page,
            "total_pages" => ceil($total_items_count / $items_per_page),
            "data" => $pagination
        );

        return $helpers->getJson($data);
    }

    
}