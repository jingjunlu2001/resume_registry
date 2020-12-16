<?php

session_start();

if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

require_once "pdo.php";

if (isset($_REQUEST['profile_id']))
{
    $profile_id = htmlentities($_REQUEST['profile_id']);

    $stmt = $pdo->prepare("
        SELECT * FROM profile 
        WHERE profile_id = :profile_id
    ");

    $stmt->execute([
        ':profile_id' => $profile_id, 
    ]);

    $profile = $stmt->fetch(PDO::FETCH_OBJ);

    $position = [];
    $education = [];

    $stmt = $pdo->prepare("
        SELECT * FROM position 
        WHERE profile_id = :profile_id
    ");

    $stmt->execute([
        ':profile_id' => $profile_id, 
    ]);

    while ( $row = $stmt->fetch(PDO::FETCH_OBJ) ) 
    {
        $position[] = $row;
    }

    $stmt = $pdo->prepare("
        SELECT * FROM education 
        LEFT JOIN institution ON education.institution_id=institution.institution_id
        WHERE profile_id = :profile_id
    ");

    $stmt->execute([
        ':profile_id' => $profile_id, 
    ]);

    while ( $row = $stmt->fetch(PDO::FETCH_OBJ) ) 
    {
        $education[] = $row;
    }

    $positionLen = count($position);
    $educationLen = count($education);
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once "bootstrap.php"; ?>
    <title>Lu Jingjun's Resume Registry</title>
</head>
<body>
<div class="container">
    <h1>Profile information</h1>
    <p>First Name: <?php echo($profile->first_name); ?></p>
    <p>Last Name: <?php echo($profile->last_name); ?></p>
    <p>Email: <?php echo($profile->email); ?></p>
    <p>Headline:<br/> <?php echo($profile->headline); ?></p>
    <p>Summary: <br/><?php echo($profile->summary); ?></p>
    <?php if($educationLen > 0) : ?>
        <div>
            <p>Educations:</p>
            <ul>
                <?php for($i=1; $i<=$educationLen; $i++) : ?>
                    <li><?php echo $education[$i-1]->year; ?>: <?php echo $education[$i-1]->name; ?></li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if($positionLen > 0) : ?>
        <div>
            <p>Positions:</p>
            <ul>
                <?php for($i=1; $i<=$positionLen; $i++) : ?>
                    <li><?php echo $position[$i-1]->year; ?>: <?php echo $position[$i-1]->description; ?></li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
    <a href="index.php">Done</a>
</div>
</body>
</html>