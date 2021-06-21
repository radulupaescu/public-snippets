<?php

require_once('./src/ActiveRecord.php');
require_once('./src/User.php');

use Examples\ActiveRecord\ActiveRecord;
use Examples\ActiveRecord\User;

// These could be read from a config.
$host = '127.0.0.1';
$user = 'root';
$pass = 'somepass';
$db = 'active_record_example';

// instantiate a database connection and set it on the ActiveRecord
ActiveRecord::$database = new PDO(
    "mysql:host=$host;dbname=$db;charset=utf8",
    $user,
    $pass,
    [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
    ]
);

//--------------------------------------------
// Example add user;
$u = (new User())
    ->setName('Radu Lupaescu')
    ->setEmail('radu.lupaescu@gmail.com')
    ->setPassword(password_hash('randomStringMaybe?', PASSWORD_BCRYPT));

$u->save();

//--------------------------------------------
// Example findById and rename
$user = User::findById(2);
echo $user . "\n";

$user->setName('Other Radu');
if ($user->save()) {
    echo "\nSuccessfully saved!\n";
} else {
    echo "\nError when saving!\n";
}

echo $user . "\n";

//--------------------------------------------
// Example Delete() when deleting we return true/false depending on the success
// we can also check that the user object has id -1 meaning it's not in the database anymore.
$user->delete();
echo $user . "\n";

//--------------------------------------------
// Example findAll();
$users = User::findAll();
foreach ($users as $user) {
    echo $user . "\n";
}
