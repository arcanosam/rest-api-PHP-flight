<?php

namespace v00;

(defined('APP_NAME')) or exit('Forbidden 403');

use \BaseClass;
use \Common_DateUtil;
use \Model_User;
use \Model_UserLoginSession;
use \Model_CacheKey;
use \System_ApiException;
use \ResultCode;

/**
 * Description of UserLogin.
 *
 * @author sabbir-hossain
 */
class UserLogin extends BaseClass {
    // Login Required.
    const LOGIN_REQUIRED = false;

    private $_user_email;
    private $_user_password;
    private $_user_id;
    private $_login_type;

    /**
     * Validating Login Request.
     */
    public function validate() {
        parent::validate();

        $this->_login_type = $this->getValueFromJSON('login_type', 'int');
        if (empty($this->_login_type)) {
            $this->_login_type = 1;
        }
        switch ($this->_login_type) {
            case 1:
                $this->_user_email = $this->getValueFromJSON('email', 'string', true);
                $this->_user_password = $this->getValueFromJSON('password', 'string', true);

                if (empty($this->_user_password)) {
                    throw new System_ApiException(ResultCode::INVALID_REQUEST_PARAMETER, 'Password is empty');
                }

                if (false === filter_var($this->_user_email, FILTER_VALIDATE_EMAIL)) {
                    throw new System_ApiException(ResultCode::INVALID_REQUEST_PARAMETER, 'Email is invalid.');
                }

                break;
            default:
                throw new System_ApiException(ResultCode::INVALID_REQUEST_PARAMETER, 'Login type is not defined');

                break;
        }
    }

    /**
     * Processing API script execution.
     */
    public function action() {
        $this->pdo->beginTransaction();

        try {
            $user = null;
            switch ($this->_login_type) {
                case 1:
                    $user = Model_User::findBy(array('email' => $this->_user_email), $this->pdo, true);
                    if (null === $user || empty($user)) {
                        throw new System_ApiException(ResultCode::USER_NOT_FOUND);
                    }
                    if (false === password_verify($this->_user_password, $user->password)) {
                        throw new System_ApiException(ResultCode::PASSWORD_MISMATCHED);
                    }

                    break;
                default:
                    throw new System_ApiException(ResultCode::INVALID_REQUEST_PARAMETER, 'Login type is not defined');

                    break;
            }

            // Delete old session data from cache
            $user->removeSessionFromUserId($user->id);
            // Save DB session data in cache

            $sessionId = $user->setSession();

            // Device token and model update : update only if the value set in the parameters and user information is different

            if (property_exists($this->json, 'device_token')) {
                $deviceToken = $this->getValueFromJSON('device_token', 'string');
                if (!empty($deviceToken) && $deviceToken != $user->device_token) {
                    $user->device_token = $deviceToken;
                }
            }
            if (property_exists($this->json, 'device_model')) {
                $deviceModel = $this->getValueFromJSON('device_model', 'string');
                if (!empty($deviceModel) && $deviceModel != $user->device_model) {
                    $user->device_model = $deviceModel;
                }
            }

            if (property_exists($this->json, 'longitude')) {
                $user->longitude = $this->getValueFromJSON('longitude', 'string', true);
            }
            if (property_exists($this->json, 'latitude')) {
                $user->latitude = $this->getValueFromJSON('latitude', 'string', true);
            }

            $user->update($this->pdo);

            Model_UserLoginSession::updateSession($user->id, $sessionId, $this->_login_type, $this->pdo);

            // Update User data in cache

            Model_User::setCache(Model_CacheKey::getUserKey($user->id), $user);

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollback();

            throw $e;
        }

        // Encode session data for client
        $encodeUserSession = base64_encode(serialize(array(
            'session_id' => $sessionId,
            'user_id' => $user->id,
        )));

        return array(
            'result_code' => ResultCode::SUCCESS,
            'time' => Common_DateUtil::getToday(),
            'data' => array(
                'session_id' => $encodeUserSession,
                'user_info' => $user->toJsonHash(),
            ),
            'error' => array(),
        );
    }
}
