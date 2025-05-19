<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'ambitie_game';
 
try 
{
    $dbh = new PDO('mysql:host='. $hostname .';dbname='. $database, 
         $username, $password);
} 
catch(PDOException $e) 
{
    echo '<h1>An error has occurred.</h1><pre>', $e->getMessage()
            ,'</pre>';
}
 
$sth = $dbh->query('SELECT * FROM highscores ORDER BY time DESC LIMIT 5');
$sth->setFetchMode(PDO::FETCH_ASSOC);
 
$result = $sth->fetchAll();
 
if (count($result) > 0) 
{
    foreach($result as $r) 
    {
        echo $r['player_name'], "\n _";
        echo $r['time'], "\n _";
    }
}