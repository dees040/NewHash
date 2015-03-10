<?php

require_once '../NewHash.php';

Hash::set(['oldHash' => MD5, 'newHash' => SHA256]);

$user = Hash::check('user', 'password');

if ($user !== false) { // You also could use is_object($user)
    // login the $user
}

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>NewHash Example</title>
    </head>
    <body>
        Example
    </body>
</html>