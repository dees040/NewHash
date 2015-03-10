<?php

/*
|--------------------------------------------------------------------------
| NewHash by dees040
|--------------------------------------------------------------------------
|
| This class can be used to check an user his/her credentials and create plus
| hash there password.
|
| By dees040 - http://github.com/dees040/NewHash
|
| Version:     0.1
| Last update: march 7 2015
|
*/

define('MD5', '0');
define('SHA1', '1');
define('SHA256', '2');
define('SHA384', '3');
define('SHA512', '4');
define('RIPEMD128', '5');

class Hash
{

    /**
     * @var
     */
    static private $oldHash;

    /**
     * @var
     */
    static private $newHash;

    /**
     * @var
     */
    static private $connection;

    /**
     * @var
     */
    static private $userTable;

    /**
     * set - This function can be called to set settings for the Hash class.
     *
     * @param array $options
     * @throws Exception
     */
    static public function set(array $options)
    {
        if (array_key_exists('oldHash', $options)) {
            self::setHashType($options['oldHash'], null);
        } else {
            self::setHashType(MD5, null);
        }

        if (array_key_exists('newHash', $options)) {
            self::setHashType(null, $options['newHash']);
        } else {
            self::setHashType(null, SHA256);
        }

        if (array_key_exists('connection', $options) AND $options['connection'] instanceof PDO) {
            self::$connection = $options['connection'];
        } else if (array_key_exists('connection', $options) AND is_array($options['connection'])) {
            $connectionVars = ['host', 'database', 'user', 'password'];
            if(0 === count(array_diff($connectionVars, array_keys($options['connection'])))){
                self::setConnection($options['connection']['host'], $options['connection']['database'], $options['connection']['user'], $options['connection']['password']);
            } else {
                throw new Exception("Hash::set(\$options []) needs a PDO connection, use 'connection' in the options.");
            }
        } else {
            throw new Exception("Hash::set(\$options []) needs a PDO connection, use 'connection' in the options.");
        }

        if (array_key_exists('userTable', $options)) {
            self::$userTable = $options['userTable'];
        } else {
            self::$userTable = "users";
        }
    }

    /**
     * setHashType - This function will set the old and new hash type
     *
     * @param null $oldHash
     * @param bool $newHash
     */
    static public function setHashType($oldHash = null, $newHash = false)
    {
        if (!is_null($oldHash))
            self::$oldHash = $oldHash;
        if (!is_null($newHash))
            self::$newHash = $newHash;
    }


    /**
     * login - This function will login an user, first it will check if the user needs to have a new hashed password.
     *
     * @param $userInfo
     * @param $password
     * @return bool|mixed
     */
    static public function check($userInfo, $password)
    {
        $userStatement = self::query(
            "SELECT * FROM ".self::$userTable." WHERE id = :userInfo OR username = :userInfo OR email = :userInfo",
            [
                ':userInfo'  => $userInfo,
            ]
        );
        $user = $userStatement->fetchObject();

        if ($user == false OR count($user) == 0) {
            return false;
        }

        // If user doesn't have a salt and the password the entered is the same as our old hash, rehash there password.
        if ($user->password === self::doHash(self::$oldHash, $password)) {
            return self::rehash($user, $password);
        }
        // If user has a salt but an old hash, rehash and resalt password
        else if ($user->password === self::doHash(self::$oldHash, $password.$user->salt)) {
            return self::rehash($user, $password);
        }
        // If user has a new hash but no salt, rehash password and create salt
        else if ($user->password === self::doHash(self::$newHash, $password)) {
            return self::rehash($user, $password);
        }
        // If user has a new hash plus salt and the password equals the given password, return the user object.
        else if ($user->password === self::doHash(self::$newHash, $password.$user->salt)) {
            return $user;
        }

        return false;
    }

    /**
     * hash - This function will hash a input by hash type.
     *
     * @param $hashType
     * @param $input
     * @return bool|string
     */
    static private function doHash($hashType, $input)
    {
        switch($hashType) {
            case '0':
                return hash('md5', $input);
            case '1':
                return hash('sha1', $input);
            case '2':
                return hash('sha256', $input);
            case '3':
                return hash('sha384', $input);
            case '4':
                return hash('sha512', $input);
            case '5':
                return hash('ripemd128', $input);
            default:
                return false;
        }
    }

    /**
     * rehash - This function will rehash a password, it will automatically create an unique salt.
     *
     * @param $user
     * @param $password
     */
    static public function rehash($user, $password)
    {
        $user->salt = self::randomSalt();
        $passwordWithSalt = $password.$user->salt;
        $user->password = self::doHash(self::$newHash, $passwordWithSalt);

        self::query(
            "UPDATE ".self::$userTable." SET password = :password, salt = :salt WHERE id = :userId",
            [
                ':password'  => $user->password,
                ':salt'      => $user->salt,
                ':userId'    => $user->id,
            ]
        );

        return $user;
    }

    /**
     * randomSalt - This function will create a random salt.
     *
     * @return string
     */
    static private function randomSalt()
    {
        return uniqid().mt_rand(10, 99);
    }

    /**
     * setConnection - This function will create and set a PDO connection
     *
     * @param string $host
     * @param string $database
     * @param string $user
     * @param string $password
     */
    static private function setConnection($host = 'localhost', $database = 'database', $user = 'root', $password = '')
    {
        try {
            // MySQL with PDO_MYSQL
            self::$connection = new PDO('mysql:host='.$host.';dbname='.$database, $user, $password);
            self::$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        } catch(PDOException $e) {
            die("Error connecting to database.");
        }
    }

    /**
     * query - Query to the database
     *
     * @param string $query - The query that has to be executed
     * @param array $items - The placeholders (Default = empty array)
     * @return bool|\PDOStatement :  Query result
     */
    static private function query($query, $items = array()) {
        try {
            $stmt = self::$connection->prepare($query);
            $stmt->execute($items);
        } catch (PDOException $e) {
            die($e);
        }
        if ($stmt) {
            return $stmt;
        } else {
            return false;
        }
    }

}