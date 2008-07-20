<?php

/**
 * Authentication object.
 * Should be reviewed and refactor to allow simple plugin overwrite
 * See OpenId authentication plugin, using Zend_Auth_OpenId on http://dev.piwik.org/trac/ticket/160
 * See Review the Login plugin to make it really modular  http://dev.piwik.org/trac/ticket/144
 * 
 * @package Piwik
 */
class Piwik_Login_Auth extends Zend_Auth_Adapter_DbTable implements Piwik_Auth
{
	public function __construct()
	{
		$db = Zend_Registry::get('db');
		parent::__construct($db);
	}

	public function authenticate()
	{
		$rootLogin = Zend_Registry::get('config')->superuser->login;
		$rootPassword = Zend_Registry::get('config')->superuser->password;
		$rootToken = Piwik_UsersManager_API::getTokenAuth($rootLogin,$rootPassword);

		if($this->_identity == $rootLogin
		&& $this->_credential == $rootToken)
		{
			return new Piwik_Auth_Result(Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE,
										$this->_identity,
										array() // message empty
				);
		}

		// we then look if the user is API authenticated
		// API authentication works without login name, but only with the token
		// TODO the logic (sql select) should be in the Login plugin, not here
		// this class should stay simple. Another Login plugin should only have to create an auth entry
		// of this class in the zend_registry and it should work
		if(is_null($this->_identity))
		{
			$authenticated = false;
			if($this->_credential === $rootToken)
			{
				return new Piwik_Auth_Result(Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE,
											$rootLogin,
											array() // message empty
				);
			}

			$login = Zend_Registry::get('db')->fetchOne(
						'SELECT login FROM '.Piwik::prefixTable('user').' WHERE token_auth = ?',
						array($this->_credential)
			);
			if($login !== false)
			{
				return new Piwik_Auth_Result(Zend_Auth_Result::SUCCESS,
											$login,
											array() // message empty
											);
			}
			else
			{
				return new Piwik_Auth_Result( Zend_Auth_Result::FAILURE,
											$this->_identity,
											array()
											);
			}
		}

		// if not then we return the result of the database authentification provided by zend
		return parent::authenticate();
	}

	public function getTokenAuth()
	{
		return $this->_credential;
	}
}