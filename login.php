<?php
// login.php

include('database_connection.php');

if(isset($_SESSION['type']))
{
    header("location:index.php");
    exit;
}

$message = '';

if(isset($_POST["login"]))
{
    $query = "SELECT * FROM user_details WHERE user_email = ?";
    $statement = $connect->prepare($query);
    if ($statement === false) {
        die('MySQL prepare error: ' . $connect->error);
    }
    
    // Bind parameters. 's' means the database expects a string.
    $statement->bind_param('s', $_POST["user_email"]);
    
    $statement->execute();
    $result = $statement->get_result();
    $count = $result->num_rows;
    
    if($count > 0)
    {
        while ($row = $result->fetch_assoc())
        {
            if($row['user_status'] == 'Active')
            {
                if(password_verify($_POST["user_password"], $row["user_password"]))
                {
                    $_SESSION['type'] = $row['user_type'];
                    $_SESSION['user_id'] = $row['user_id'];  // corrected the typo 'usar_id' to 'user_id'
                    $_SESSION['user_name'] = $row['user_name'];  // corrected 'usar_name' to 'user_name'
                    header("location:index.php");
                    exit;
                }
                else
                {
                    $message = "<label>Wrong Password</label>";
                }
            }
            else
            {
                $message = "<label>Your account is disabled, Contact Master</label>";
            }
        }
    }
    else
    {
        $message = "<label>Wrong Email Address</label>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management System using PHP with Ajax Jquery</title>     
    <script src="js/jquery-1.10.2.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <script src="js/bootstrap.min.js"></script>
</head>
<body>
    <br />
    <div class="container">
        <h2 align="center">Inventory Management System using PHP with Ajax Jquery</h2>
        <br />
        <div class="panel panel-default">
            <div class="panel-heading">Login</div>
            <div class="panel-body">
                <form method="post">
                    <?php echo $message; ?>
                    <div class="form-group">
                        <label>User Email</label>
                        <input type="text" name="user_email" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="user_password" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <input type="submit" name="login" value="Login" class="btn btn-info" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
