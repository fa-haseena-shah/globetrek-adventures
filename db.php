<?php
    // arguements needed for db connection
    $hostName = "localhost";
    $dbUser = "root";
    $dbPassword = "";
    $dbName = "globetrek_db";
    // create new db connection object as conn variable using mysqli
    $conn = new mysqli($hostName, $dbUser, $dbPassword, $dbName);

    // check for errors. if connection fails, stop the php program + show error msg
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>