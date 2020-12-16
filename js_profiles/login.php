<?php // Do not put any HTML above this line

session_start();

if ( isset($_POST['cancel'] ) ) 
{
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
$failure = false;  // If we have no POST data

if ( isset($_SESSION['failure']) ) {
    $failure = htmlentities($_SESSION['failure']);

    unset($_SESSION['failure']);
}

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['email']) && isset($_POST['pass']) ) 
{
    if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) 
    {
        $_SESSION['failure'] = "User name and password are required";
        header("Location: login.php");
        return;
    } 

    $pass = htmlentities($_POST['pass']);
    $email = htmlentities($_POST['email']);

    require_once "pdo.php";

    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE email = :email AND password = :password
    ");

    $stmt->execute([
        ':email' => $email, 
        ':password' => hash('md5', $salt.$pass), 
    ]);

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if ($row !== false) 
    {
        error_log("Login success ".$email);
        $_SESSION['name'] = $row->name;
        $_SESSION['user_id'] = $row->user_id;

        header("Location: index.php");
        return;
    }

    error_log("Login fail ".$pass." $check");
    $_SESSION['failure'] = "Incorrect password";

    header("Location: login.php");
    return;

}



// Fall through into the View
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once "bootstrap.php"; ?>
    <title>Lu Jingjun's Resume Registry</title>
</head>
<body>
<div class="container">
    <h1>Please Log In</h1>
    <?php
    if ( $failure !== false ) {
        echo('<p style="color: red;">'.htmlentities($failure)."</p>\n");
    }
    ?>
    <form method="POST" action="login.php">
    <label for="email">Email</label>
    <input type="text" name="email" id="email"><br/>
    <label for="id_1723">Password</label>
    <input type="password" name="pass" id="id_1723"><br/>
    <input type="submit" onclick="return doValidate();" value="Log In">
    <input type="submit" name="cancel" value="Cancel">
    </form>
    <p>For a password hint, view source and find an account and password hint in the HTML comments.
    <!-- Hint: 
    The account is umsi@umich.edu
    The password is the three character name of the 
    programming language used in this class (all lower case) 
    followed by 123. -->
    </p>
    <script>
    function doValidate() {
        console.log('Validating...');
        try {
            addr = document.getElementById('email').value;
            pw = document.getElementById('id_1723').value;
            console.log("Validating addr="+addr+" pw="+pw);
            if (addr == null || addr == "" || pw == null || pw == "") {
                alert("Both fields must be filled out");
                return false;
            }
            if ( addr.indexOf('@') == -1 ) {
                alert("Invalid email address");
                return false;
            }
            return true;
        } catch(e) {
            return false;
        }
        return false;
    }
    </script>

</div>
</body>
</html>