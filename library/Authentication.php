<?php
/**
 * Created by PhpStorm.
 * User: Sotiris
 * Date: 22/5/2015
 * Time: 12:05 μμ
 */

class Authentication {

    public static function authenticate(){
        if(OC_AUTHENTICATION == FALSE) {
          return TRUE;
        }

        if (is_file('../../config.php')) {
            require_once('../../config.php');
        } else {
            return FALSE;
        }

        // Startup
        require_once(DIR_SYSTEM . 'startup.php');

        // Registry
        $registry = new Registry();

        // Database
        $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        $registry->set('db', $db);

        // Request
        $request = new Request();
        $registry->set('request', $request);

        //Config
        $config = new Config();
        $registry->set('config', $config);

        // Session
        $session = new Session();
        $registry->set('session', $session);

        // User
        $user = new User($registry);
        $registry->set('user', $user);

        return $user->hasPermission("access", "tool/export_import");
    }


}