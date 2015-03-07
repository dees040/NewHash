<?php

define('MD5', '0');
define('SHA1', '1');
define('SHA256', '2');
define('SHA384', '3');
define('SHA512', '4');
define('RIPEMD128', '5');

class Hash
{

    static private $oldHash;

    static private $newHash;

    static private $connection;

    static public function set(array $options)
    {
        if (in_array('oldHash', $options)) {
            self::setHashType($options['oldHash']);
        }

        if (in_array('newHash', $options)) {
            self::setHashType(null, $options['newHash']);
        }

        if (in_array('connection', $options)) {
            self::$connection = $options['connection'];
        }
    }

    static public function setHashType($oldHash = null, $newHash = null)
    {
        self::$oldHash = $oldHash;
        self::$newHash = $newHash;
    }

    private function hashTypeSwitch($hashType, $value)
    {
        switch($hashType) {
            case '0':

                break;
            case '1':

                break;
            case '2':

                break;
            case '3':

                break;
            case '4':

                break;
            case '5':

                break;
            default:

                break;
        }
    }

}