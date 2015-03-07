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
     */
    static public function set(array $options)
    {
        if (array_key_exists('oldHash', $options)) {
            self::setHashType($options['oldHash']);
        }

        if (array_key_exists('newHash', $options)) {
            self::setHashType(null, $options['newHash']);
        }

        if (array_key_exists('connection', $options) AND $options['connection'] instanceof PDO) {
            self::$connection = $options['connection'];
        } else {
            self::setConnection();
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
     * @param null $newHash
     */
    static public function setHashType($oldHash = null, $newHash = null)
    {
        self::$oldHash = $oldHash;
        self::$newHash = $newHash;
    }


    /**
     * login - This function will login an user, first it will check if the user needs to have a new hashed password.
     *
     * @param $userInfo
     * @param $password
     * @return bool|mixed
     */
    static public function login($userInfo, $password)
    {
        $userQuery = self::query(
            "SELECT * FROM `:userTable` WHERE id = :userInfo OR username = :userInfo OR email = :userInfo",
            [
                ':userTable' => self::$userTable,
                ':userInfo'  => $userInfo,
            ]
        );
        $user = $userQuery->fetchObject();

        if ($user == false OR count($user) == 0) {
            return false;
        }

        // If user doesn't have a salt and the password the entered is the same as our old hash, rehash there password.
        if ($user->password == self::hash(self::$oldHash, $password)) {
            self::rehash($user->id, $password);
            return $user;
        }
        //
        else if ($user->password == self::hash(self::$oldHash, $password.$user->salt)) {
            self::rehash($user->id, $password);
            return $user;
        }
        //
        else if ($user->password == self::hash(self::$newHash, $password)) {
            self::rehash($user->id, $password);
            return $user;
        }
        //
        else if ($user->password == self::hash(self::$newHash, $password.$user->salt)) {
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
    private function hash($hashType, $input)
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
     * @param $userId
     * @param $password
     */
    static public function rehash($userId, $password)
    {
        $newSalt = self::randomSalt();
        $passwordWithSalt = $password.$newSalt;
        $newHashedPassword = self::hash(self::$newHash, $passwordWithSalt);

        self::query(
            "UPDATE `:userTable` SET password = :password, salt = :salt WHERE id = :userId",
            [
                ':userTable' => self::$userTable,
                ':password'  => $newHashedPassword,
                ':salt'      => $newSalt,
                ':userId'    => $userId,
            ]
        );
    }

    /**
     * randomSalt - This function will create a random salt.
     *
     * @return string
     */
    private function randomSalt()
    {
        return uniqid().mt_rand(10, 99);
    }

    /**
     * setConnection - This function will create and set a PDO connection
     */
    private function setConnection()
    {
        try {
            # MySQL with PDO_MYSQL
            self::$connection = new PDO('mysql:host=localhost;dbname=db_name,db_user,db_pass');
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
    private function query($query, $items = array()) {
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