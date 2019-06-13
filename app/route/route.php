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

if (!isset($argv)) {
    // Set root / index page response view

    Flight::route('GET|POST /', function () {
        Flight::json(array(
            'data' => array(
                'title' => 'Welcome',
                'message' => 'This REST-API template is built on PHP langauage, with flight microfamework as backend engine',
            ),
            'result_code' => ResultCode::SUCCESS,
        ));
    });

    // Set route for all GET query request from client
    Flight::route('GET /@api_version/@resource_name', array('Controller', 'initGet'));

    // Set route for all POST query request from client
    Flight::route('POST /@api_version/@resource_name', array('Controller', 'initPost'));

    // Set route for all PUT query request from client
    Flight::route('PUT /@api_version/@resource_name', array('Controller', 'initPut'));

    // Set route for all PATCH query request from client
    Flight::route('PATCH /@api_version/@resource_name', array('Controller', 'initPatch'));

    // Set route for all DELETE query request from client
    Flight::route('DELETE /@api_version/@resource_name', array('Controller', 'initDelete'));

    /*
     * Image path is masked in API response
     * Show image from file get content by table rowID and type
     */
    Flight::route('GET /image/@type/@id', array('ShowImage', 'index'));

    // Set error page page response
    Flight::map('notFound', function () {
        Flight::json(array(
            'error' => array(
                'title' => 'Data Not Found',
                'message' => 'Requested data not found',
            ),
            'result_code' => ResultCode::NOT_FOUND,
        ));
    });
}
