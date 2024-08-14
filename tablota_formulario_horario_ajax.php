<?php
include_once( "config.php" );

$inicio_atend = $_POST['data']['inicio_atend'];
$fim_atend = $_POST['data']['fim_atend'];
$uorgs = implode(',', $_POST['data']['uorgs']);

$oDBase = new DataBase();
$query = $oDBase->query("UPDATE tabsetor SET inicio_atend = '".$inicio_atend."', fim_atend = '".$fim_atend."' WHERE codigo in (".$uorgs.")");


die('Horário alterados com sucesso!');