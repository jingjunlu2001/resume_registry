<?php

session_start();
include "validatePos.php";

if ( ! isset($_SESSION['name']) ) {
	die("ACCESS DENIED");
}

// If the user requested logout go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

$status = false;

if ( isset($_SESSION['status']) ) {
	$status = htmlentities($_SESSION['status']);
	$status_color = htmlentities($_SESSION['color']);

	unset($_SESSION['status']);
	unset($_SESSION['color']);
}

require_once "pdo.php";

$name = htmlentities($_SESSION['name']);

$_SESSION['color'] = 'red';

// Check to see if we have some POST data, if we do process it
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) 
{
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1)
    {
        $_SESSION['status'] = "All fields are required";
        header("Location: add.php");
        return;
    }

    if (strpos($_POST['email'], '@') === false)
    {
        $_SESSION['status'] = "Email address must contain @";
        header("Location: add.php");
        return;
    }

    if(!validatePos())
    {
        header("Location: add.php");
        return;
    }

    $first_name = htmlentities($_POST['first_name']);
    $last_name = htmlentities($_POST['last_name']);
    $email = htmlentities($_POST['email']);
    $headline = htmlentities($_POST['headline']);
    $summary = htmlentities($_POST['summary']);

    $stmt = $pdo->prepare("
        INSERT INTO profile (user_id, first_name, last_name, email, headline, summary)
        VALUES (:user_id, :first_name, :last_name, :email, :headline, :summary)
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':first_name' => $first_name, 
        ':last_name' => $last_name, 
        ':email' => $email,
        ':headline' => $headline,
        ':summary' => $summary,
    ]);

    $profile_id = $pdo->lastInsertId();

    $rank = 1;

    for ($i=1; $i<=9; $i++) 
    {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;

        $year = htmlentities($_POST['year'.$i]);
        $desc = htmlentities($_POST['desc'.$i]);

        $stmt = $pdo->prepare("
            INSERT INTO position (profile_id, rank, year, description)
            VALUES (:profile_id, :rank, :year, :description)
        ");

        $stmt->execute([
            ':profile_id' => $profile_id,
            ':rank' => $rank, 
            ':year' => $year, 
            ':description' => $desc,
        ]);

        $rank++;
    }

    $rank = 1;

    for ($i=1; $i<=9; $i++) 
    {
        if ( ! isset($_POST['edu_year'.$i]) ) continue;
        if ( ! isset($_POST['edu_school'.$i]) ) continue;

        $edu_year = htmlentities($_POST['edu_year'.$i]);
        $edu_school = htmlentities($_POST['edu_school'.$i]);

        $stmt = $pdo->prepare("
            SELECT * FROM institution
            WHERE name = :edu_school LIMIT 1
        ");

        $stmt->execute([
            ':edu_school' => $edu_school, 
        ]);

        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if ($result)
        {
            $institution_id = $result->institution_id;
        }
        else
        {
            $stmt = $pdo->prepare("
                INSERT INTO institution (name)
                VALUES (:name)
            ");

            $stmt->execute([
                ':name' => $edu_school,
            ]);

            $institution_id = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("
            INSERT INTO education (profile_id, institution_id, rank, year)
            VALUES (:profile_id, :institution_id, :rank, :year)
        ");

        $stmt->execute([
            ':profile_id' => $profile_id,
            ':institution_id' => $institution_id,
            ':rank' => $rank, 
            ':year' => $edu_year, 
        ]);

        $rank++;
    }

    $_SESSION['status'] = 'Record added';
    $_SESSION['color'] = 'green';

    header('Location: index.php');
	return;
    
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once "bootstrap.php"; ?>
    <title>Lu Jingjun's Profile Add</title>
</head>
<body>
<div class="container">
    <h1>Adding Profile for <?php echo $name; ?></h1>
    <?php
    if ( $status !== false ) 
    {
        echo('<p style="color: ' .$status_color. ';">'.htmlentities($status)."</p>\n");
    }
    ?>
    <form method="post">
        <p>First Name:
            <input type="text" name="first_name" size="60"/></p>
        <p>Last Name:
            <input type="text" name="last_name" size="60"/></p>
        <p>Email:
            <input type="text" name="email" size="30"/></p>
        <p>Headline:<br/>
            <input type="text" name="headline" size="80"/></p>
        <p>Summary:<br/>
            <textarea name="summary" rows="8" cols="80"></textarea></p>
        <p>Education:
            <button id="addEdu">+</button></p>
        <div id="edu_fields"></div>
        <p>Position:
            <button id="addPos">+</button></p>
        <div id="position_fields"></div>
        <p>
            <input type="submit" value="Add">
            <input type="submit" name="cancel" value="Cancel">
        </p>
    </form>
</div>
<script>
    countPos = 0;
    countEdu = 0;
    $(document).ready(function(){
        window.console && console.log('Document ready called');
        $('#addPos').click(function(event){
            event.preventDefault();
            if ( countPos >= 9 ) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            window.console && console.log("Adding position "+countPos);

            $('#position_fields').append(
                '<div id="position'+countPos+'"> \
                    <div> \
                        <label>Year:</label> \
                        <p> \
                            <input type="text" name="year'+countPos+'"> \
                            <button \
                                onclick="$(\'#position'+countPos+'\').remove();return false;" \
                            >-</button> \
                        </p> \
                    </div> \
                    <div> \
                        <label></label> \
                        <p> \
                            <textarea name="desc'+countPos+'" rows="8"></textarea> \
                        </p> \
                    </div> \
                </div>'
            );
        });

        $('#addEdu').click(function(event){
            event.preventDefault();
            if ( countEdu >= 9 ) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            window.console && console.log("Adding position "+countEdu);

            $('#edu_fields').append(
                '<div id="edu'+countEdu+'"> \
                    <div> \
                        <label>Year:</label> \
                        <p> \
                            <input type="text" name="edu_year'+countEdu+'"> \
                            <button \
                                onclick="$(\'#edu'+countEdu+'\').remove();return false;" \
                            >-</button> \
                        </p> \
                    </div> \
                    <div> \
                        <label>School:</label> \
                        <p> \
                            <input type="text" name="edu_school'+countEdu+'" /> \
                        </p> \
                    </div> \
                </div>'
            );

            $('.school').autocomplete({
                source: "school.php"
            });

        });
    });
</script>
</body>
</html>