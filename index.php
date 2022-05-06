<?php
     $HOST="localhost";
     $DB_NAME="test";
     $USER="root";
     $PASS="azerty123";
 
     // Connect to DB
     $db = new PDO("mysql:host=" . $HOST . ";dbname=" . $DB_NAME, $USER, $PASS);
     // Display errors when occurs
     $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
?>