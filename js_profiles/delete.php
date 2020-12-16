<?php

session_start();

if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

// If the user requested logout go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

require_once "pdo.php";

if (isset($_REQUEST['profile_id']))
{
    $profile_id = htmlentities($_REQUEST['profile_id']);

    if (isset($_POST['delete'])) 
    {
        $stmt = $pdo->prepare("
            DELETE FROM profile
            WHERE profile_id = :profile_id
        ");

        $stmt->execute([
            ':profile_id' => $profile_id, 
        ]);

        $_SESSION['status'] = 'Record deleted';
        $_SESSION['color'] = 'green';

        header('Location: index.php');
        return;
    }

    $stmt = $pdo->prepare("
        SELECT * FROM profile 
        WHERE profile_id = :profile_id
    ");

    $stmt->execute([
        ':profile_id' => $profile_id, 
    ]);

    $row = $stmt->fetch(PDO::FETCH_OBJ);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Autos Database</title>
    <?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
    <h1>Deleting profile</h1>
    <p>First Name: <?php echo $row->first_name; ?></p>
    <p>Last Name: <?php echo $row->last_name; ?></p>
    <form method="post"><input type="hidden" name="profile_id" value="<?php echo $row->profile_id; ?>">
        <input type="submit" name="delete" value="Delete">
        <input type="submit" name="cancel" value="cancel">
    </form>
</div>
</body>