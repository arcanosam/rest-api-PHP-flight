<?php

(defined('APP_NAME')) or exit('Forbidden 403');

/**
 * Mission Status API.
 */
class ShowImage extends BaseClass {
    private $_image_type;

    public function __construct() {
    }

    public static function index($type, $id) {
        $imageObj = new ShowImage();

        $imgURL = '';
        switch ($type) {
            case 'user-profile':
                $userInfo = Model_User::find($id);
                if (empty($userInfo)) {
                    header('HTTP/1.1 400 OK');
                    exit;
                }

                $mobileDevice = false;
                if (stristr($_SERVER['HTTP_USER_AGENT'], 'ipad')) {
                    $mobileDevice = true;
                } elseif (stristr($_SERVER['HTTP_USER_AGENT'], 'iphone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iphone')) {
                    $mobileDevice = true;
                } elseif (stristr($_SERVER['HTTP_USER_AGENT'], 'blackberry')) {
                    $mobileDevice = true;
                } elseif (stristr($_SERVER['HTTP_USER_AGENT'], 'android')) {
                    $mobileDevice = true;
                }

                $imgPath = ($mobileDevice ? Const_Application::UPLOAD_PROFILE_IMAGE_PATH_MOBILE : Const_Application::UPLOAD_PROFILE_IMAGE_PATH_MOBILE).$userInfo->profile_image;

                break;
            default:
                break;
        }
        $imageObj->show_image($imgPath);
    }

    /**
     * Process execution.
     *
     * @param mixed $filePath
     */
    protected function show_image($filePath) {
        if ('' != $filePath) {
            header('Content-Type: image/jpeg');
            $res = file_get_contents($filePath);
            echo $res;
        } else {
            header('HTTP/1.1 400 OK');
        }
        exit();
    }
}
