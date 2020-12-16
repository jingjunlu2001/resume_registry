<?php

session_start();

if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

include_once "pdo.php";

if (isset($_REQUEST['term']))
{
	$stmt = $pdo->prepare('
		SELECT name FROM Institution
		WHERE name LIKE :prefix'
	);

	$stmt->execute([
		':prefix' => $_REQUEST['term']."%"
	]);

	$retval = [];

	while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) 
	{
		$retval[] = $row['name'];
	}

	echo(json_encode($retval, JSON_PRETTY_PRINT));

}