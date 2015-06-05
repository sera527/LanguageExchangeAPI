<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.06.2015
 * Time: 15:12
 */

class db_connect {
    function connect(){
        $host = "localhost";
        $db = "languageexchange";
        $user = "root";
        $pass = "KPIshnik527";
        $charset = "unicode";
        $dsn = "mysql:host=$host;dbname=$db";
        $opt = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->query("SET character_set_connection='utf8'");
        $pdo->query("SET character_set_database='utf8'");
        $pdo->query("SET character_set_results='utf8'");
        $pdo->query("SET character_set_server='utf8'");
        $pdo->query("SET character_set_system='utf8'");
        $pdo->query("SET character_set_client='utf8'");
        return $pdo;
    }
}