<?php

namespace Piwik\Plugins\SandstormLogin;

use Exception;
use Piwik\Access;
use Piwik\AuthResult;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Session;

class Auth implements \Piwik\Auth
{
    public function getName()
    {
        return "SandstormLogin";
    }

    public function getLogin()
    {
        return $_SERVER['HTTP_X_SANDSTORM_USER_ID'];
    }

    public function getTokenAuthSecret()
    {
        // Not actually used, merely for conformance to \Piwik\Auth interface.
        return "";
    }

    public function setTokenAuth($token_auth)
    {
        // Not actually used, merely for conformance to \Piwik\Auth interface.
    }

    public function setLogin($login)
    {
        // Not actually used, merely for conformance to \Piwik\Auth interface.
    }

    public function setPassword($password)
    {
        // Not actually used, merely for conformance to \Piwik\Auth interface.
    }

    public function setPasswordHash($passwordHash)
    {
        // Not actually used, merely for conformance to \Piwik\Auth interface.
    }

    public function authenticate()
    {
        // Autoprovision a user in the DB if they don't exist yet
        Access::doAsSuperUser(function () {
            $login = $_SERVER['HTTP_X_SANDSTORM_USER_ID'];
            if (!empty($login)) {
                $displayName = rawurldecode($_SERVER['HTTP_X_SANDSTORM_USERNAME']);
                $api = UsersManagerAPI::getInstance();
                $user = null;
                try {
                    $user = $api->getUser($login);
                    $api->updateUser($login, null, null, $displayName);
                } catch (Exception $e) {
                    // No user in the user table yet, create it.
                    $api->addUser($login, md5($login), $login . "@example.com", $displayName);
                }
            }
        });

        // Patch access levels in the DB to match those of the incoming request
        Access::doAsSuperUser(function () {
            $login = $_SERVER['HTTP_X_SANDSTORM_USER_ID'];
            if (!empty($login)) {
                // Compute desired effective access from X-Sandstorm-Permissions
                $perms = explode(",", $_SERVER['HTTP_X_SANDSTORM_PERMISSIONS']);
                $maxPerm = in_array("admin", $perms, true) ? "admin" : 
                                in_array("view", $perms, true) ? "view" : "noaccess";
                $grantSuperUser = in_array("superuser", $perms, true) ? true : false;
                $api = UsersManagerAPI::getInstance();

                $currentlySuperUser = Piwik::hasTheUserSuperUserAccess($login);
                if ($currentlySuperUser !== $grantSuperUser) {
                    $api->setSuperUserAccess($login, $grantSuperUser);
                }
                if (! $grantSuperUser) {
                    // Piwik clears the access table if your login has superuser privileges,
                    // so we only need to set access in the case that you don't have superuser.
                    // If we always call setUserAccess, then we often get transaction aborts when
                    // multiple requests are in-flight, which bubble up to the user as 500s for JS
                    // or CSS, which is terrible.  So check if we need to setUserAccess first, and
                    // most of the time it'll be set on the first request and only read thereafter.
                    $sitesApi = SitesManagerAPI::getInstance();
                    $allSitesIds = $sitesApi->getAllSitesId();
                    $currentAccess = $api->getSitesAccessFromUser($login);
                    // We want to grant $maxPerm to all sites, not just ones that this user already
                    // has bits for.  However, we want to avoid unnecessary writes to the DB, to
                    // minimize risk of transaction aborts.
                    foreach ($allSitesIds as $siteId) {
                        $found = false;
                        foreach ($currentAccess as $accessible) {
                            if ($accessible['site'] === $siteId) {
                                $found = true;
                                if ($accessible['access'] !== $maxPerm) {
                                    $api->setUserAccess($login, $maxPerm, $siteId);
                                }
                            }
                        }
                        if (! $found) {
                            $api->setUserAccess($login, $maxPerm, $siteId);
                        }
                    }
                }
            }
        });
        // We make tokenAuth be md5(sandstorm_userid).  This should perhaps be improved to be not guessable.
        // The default implementation appears to be md5(username . md5(password)).
        $perms = explode(",", $_SERVER['HTTP_X_SANDSTORM_PERMISSIONS']);
        $isSuperUser = in_array("superuser", $perms, true);
        $hasViewPerm = in_array("view", $perms, true);
        $authResult = $isSuperUser ? AuthResult::SUCCESS_SUPERUSER_AUTH_CODE :
                                     ($hasViewPerm ? AuthResult::SUCCESS : AuthResult::FAILURE);
        if ($authResult === AuthResult::FAILURE) {
            return new AuthResult($authResult, "", "");
        }
        return new AuthResult($authResult, $_SERVER['HTTP_X_SANDSTORM_USER_ID'], md5($_SERVER['HTTP_X_SANDSTORM_USER_ID']));
    }
}
