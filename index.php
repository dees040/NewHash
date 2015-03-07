<?php

ini_set('display_errors', 1);
error_reporting(~0);

require_once 'NewHash.php';

Hash::set(['oldHash' => MD5, 'newHash' => SHA256]);

var_dump(Hash::check('dees', 'test'));