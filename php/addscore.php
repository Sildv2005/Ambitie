<?php
$hostname = 'localhost';
$username = 'ambitie_game';
$password = 'ambitie_game';
$database = 'ambitie_game';
 
try 
{
    $dbh = new PDO('mysql:host='. $hostname .';dbname='. $database, 
           $username, $password);
} 
catch(PDOException $e) 
{
    echo '<h1>An error has ocurred.</h1><pre>', $e->getMessage() 
            ,'</pre>';
}
    

$sth = $dbh->prepare('INSERT INTO highscores (player_name, level, time, best_lap_time) VALUES (:player_name, :level, :total_time, :best_lap);');
try 
{
    $sth->bindParam(':player_name', $_GET['player_name'], PDO::PARAM_STR);
    $sth->bindParam(':level', $_GET['level'], PDO::PARAM_INT);
    $sth->bindParam(':time', $_GET['time'], PDO::PARAM_STR);
    $sth->bindParam(':best_lap_time', $_GET['best_lap_time'], PDO::PARAM_STR);
    $sth->execute();
} 
catch(Exception $e) 
{
    echo '<h1>An error has ocurred.</h1><pre>', 
             $e->getMessage() ,'</pre>';
}