<?php

/**
 * A RESTful API template in PHP based on flight micro-framework.
 *
 * ANYONE IN THE DEVELOPER COMMUNITY MAY USE THIS PROJECT FREELY
 * FOR THEIR OWN DEVELOPMENT SELF-LEARNING OR DEVELOPMENT or LIVE PROJECT
 *
 * @author      Sabbir Hossain Rupom <sabbir.hossain.rupom@hotmail.com>
 * @license	http://www.opensource.org/licenses/mit-license.php ( MIT License )
 *
 * @since       Version 1.0.0
 */
(defined('APP_NAME')) or exit('Forbidden 403');

/**
 * Controller for application.
 *
 * @property BaseClass $action BaseClass
 *
 * @author sabbir-hossain
 */
class Controller {
    protected static $apiName;
    protected static $getParams;
    protected static $headers;
    protected static $json;

    /**
     * Initialize application.
     *
     * @param string $name   REST API name
     * @param string $method Application request Method
     */
    public static function init($api_version, $resource_name, $method) {
        $data = null;

        try {
            self::$apiName = $api_version."\\".(string)Common_Utils::camelize($resource_name); // prepare api controller from request url call
            self::$getParams = $_GET;
            self::$headers = getallheaders();

            if (in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE'))) {
                // Fetch all requested parameters
                $data = file_get_contents('php://input');

                self::$json = json_decode($data);

                // Check if requested parameters are in json format or not
                if (!empty($data) && JSON_ERROR_NONE != json_last_error() && empty($_FILES)) {
                    throw new System_ApiException(ResultCode::INVALID_JSON, "Invalid JSON: ${data}");
                }
            } else {
                self::$json = array();
            }

            // Check if requested API controller exist in server
            if (!class_exists(self::$apiName)) {
                throw new System_ApiException(ResultCode::UNKNOWN_ERROR, 'No such api: '.$resource_name);
            }

            /**
             * Call Base Controller to Retrieve Instance of API Controller.
             */
            $action = new self::$apiName(self::$headers, self::$getParams, self::$json, self::$apiName);
            $result = $action->process();
        } catch (Exception $e) {
            // Handle all exception messages

            if ($e instanceof System_ApiException) {
                // Handle all application error messages
                header('HTTP/1.1 '.ResultCode::getHTTPstatusCode($e->getCode()).' '.ResultCode::getTitle($e->getCode()));
                $errMsg = empty($e->getMessage()) ? ResultCode::getMessage($e->getCode()) : $e->getMessage();
                $result = array(
                    'result_code' => $e->getCode(),
                    'time' => Common_DateUtil::getToday(),
                    'error' => array(
                        'title' => ResultCode::getTitle($e->getCode()),
                        'msg' => $errMsg,
                    ),
                );

                Common_Log::log(self::$apiName.' ('.ResultCode::DATABASE_ERROR.'): '.$errMsg);
            } elseif ($e instanceof PDOException) {
                // Handle all database related error messages
                header('HTTP/1.1 '.ResultCode::getHTTPstatusCode(ResultCode::DATABASE_ERROR).' '.ResultCode::getTitle(ResultCode::DATABASE_ERROR));
                $errMsg = empty($e->getMessage()) ? ResultCode::getMessage(ResultCode::DATABASE_ERROR).': check connection' : $e->getMessage();
                $result = array(
                    'result_code' => ResultCode::DATABASE_ERROR,
                    'time' => Common_DateUtil::getToday(),
                    'error' => array(
                        'title' => ResultCode::getTitle(ResultCode::DATABASE_ERROR),
                        'msg' => $errMsg,
                    ),
                );

                Common_Log::log(self::$apiName.' ('.ResultCode::DATABASE_ERROR.'): '.$errMsg);
            } else {
                // Handle all system error messages
                header('HTTP/1.1 '.ResultCode::getHTTPstatusCode(ResultCode::UNKNOWN_ERROR).' '.ResultCode::getTitle(ResultCode::UNKNOWN_ERROR));
                $errMsg = empty($e->getMessage()) ? ResultCode::getMessage(ResultCode::UNKNOWN_ERROR) : $e->getMessage();
                $result = array(
                    'result_code' => ResultCode::UNKNOWN_ERROR,
                    'time' => Common_DateUtil::getToday(),
                    'error' => array(
                        'title' => ResultCode::getTitle(ResultCode::UNKNOWN_ERROR),
                        'msg' => $errMsg,
                    ),
                );

                Common_Log::log(array(
                    'message' => self::$apiName.' ('.ResultCode::UNKNOWN_ERROR.'): '.$errMsg,
                    'file_name' => $e->getFile(),
                    'line_number' => $e->getLine(),
                ));
            }

            if (Config_Config::getInstance()->isErrorDump()) {
                /*
                 * Additional error messages
                 * For developers debug purpose
                 */
                $result['error_dump'] = array(
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                );
            }
        }
        $json_array = $result;

        if ('PRODUCTION' != strtoupper(Flight::get('env'))) {
            /*
             * Calculate server execution time for running API script [ For developers only ]
             * And add to output result
             */
            $json_array['execution_time'] = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

//            $sql = "INSERT INTO `api_exec_time` (`api_name`, `exec_time`) VALUES ('" . self::$apiName . "', {$json_array['execution_time']});";
//            $pdo = Flight::pdo();
//            $stmt = $pdo->prepare($sql);
//            $stmt->execute();
        }

        // JSON Output
        View_Output::responseJson($json_array);
    }

    /**
     * Initialize application for GET method.
     *
     * @param type $resource_name Api name
     */
    public static function initGet($api_version, $resource_name) {
        self::init($api_version, $resource_name, 'GET');
    }

    /**
     * Initialize application for POST method.
     *
     * @param type $resource_name Api name
     */
    public static function initPost($api_version, $resource_name) {
        self::init($api_version, $resource_name, 'POST');
    }

    /**
     * Initialize application for PUT method.
     *
     * @param type $resource_name Api name
     */
    public static function initPut($api_version, $resource_name) {
        self::init($api_version, $resource_name, 'PUT');
    }

    /**
     * Initialize application for PATCH method.
     *
     * @param type $resource_name Api name
     */
    public static function initPatch($api_version, $resource_name) {
        self::init($api_version, $resource_name, 'PATCH');
    }

    /**
     * Initialize application for DELETE method.
     *
     * @param type $resource_name Api name
     */
    public static function initDelete($api_version, $resource_name) {
        self::init($api_version, $resource_name, 'DELETE');
    }
}
