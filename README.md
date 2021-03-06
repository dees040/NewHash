# NewHash

This class can be used to check if a password needs to be rehashed.

Usage
=====

**Require the class**

```
require_once 'NewHash.php';
```

**Set settings**

```
Hash::set(['oldHash' => MD5, 'newHash' => SHA256]);
```

**Check password**

```
Hash::check('user_input', 'password');
```

Documentation
=============

**Settings options**

 - oldHash: the old hash that your script currently is using. This could be MD5, SHA1, SHA256, SHA384, SHA512, SHA256. Default: MD5.
 - newHash: the new hash you want to use. This could be MD5, SHA1, SHA256, SHA384, SHA512, RIPEMD128. Default: SHA256
 - connection (REQUIRED): A PDO instance of the database connection. Default: creates a custom connection.
 - userTable: The user table in the database. Default: 'users'.
 
**Check passwords**

 - ```$userInput``` ('user_input'): The user input info, this can be an: id, email or username.
 - ```$password``` ('password): The user password which has been filled in on the login form.
 - ```return```: Returns user credentials on success and false on failure/user not found.
 
Database
========

The user table needs to have five columns.

 1. id, INT AI
 2. username, VARCHAR()  
 3. password, VARCHAR(128)
 4. salt, VARCHAR(15)
 5. email, VARCHAR()

TODO
====

 1. Specify database connection settings. With this you don't need to set a PDO connection, you can just specify the database credentials en the code will create a new PDO object for you.
 2. Just hash a password. This function just will hash a password, so you don't need to check it. This can be used on user registration.
 3. Add beter documenation.