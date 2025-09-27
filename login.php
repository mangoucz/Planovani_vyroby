<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        if (isset($_POST['subLogin'])) {
            $uziv = $_POST['uziv'];    
            
            require_once 'server.php';

            $sql = "SELECT z.id_zam FROM Zamestnanci AS z WHERE z.uziv_jmeno = ?;";
            $params = [$uziv];
            $result = sqlsrv_query($conn, $sql, $params);    
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            if ($zaznam) {
                session_start();      
                $_SESSION['uziv'] = $zaznam['id_zam'];
                header("Location: odtahy-tyden.php");
                exit();
            } 
            else 
                $error = "Uživatel nenalezen!";
            sqlsrv_free_stmt($result);
        }//Logout
        else{
            session_start();
            session_unset();
            session_destroy();
            echo json_encode(["success" => true]);
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Systém plánování výroby</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SYSTÉM PLÁNOVÁNÍ VÝROBY</h1>
        <span class="separator">|</span>
        <form action="" method="post">
            <h2>Přihlášení</h2>
            <?php echo isset($error) ? '<p class="error">' . $error . '</p>' : "";?>
            <input type="text" name="uziv" placeholder="Přihlašovací jméno" required>
            <input type="text" name="pasw" placeholder="Heslo">
            <input type="submit" value="Přihlásit se!" name="subLogin" class="defButt"><br>
        </form>
    </div>
    <div class="footer">
        <img src="Indorama.png" width="200px">
    </div>
</body>
<style>
    body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80vw;
        padding: 5% 0;
        background: rgba(255, 255, 255, 0.8); 
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        min-height: 300px;
    }
    .separator {
        font-size: 100px;
        margin-right: 20px;
    }
    h1 {
        font-size: 36px;
        margin: 0.9% 20px 0 0;
    }
    
    form {
        display: flex;
        flex-direction: column;
        background: #FFFFFF; 
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        max-width: 400px;
        width: 200%;
        text-align: center;
        align-items: center;
    }
    input[type="text"] {
        width: 80%;
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #CCCCCC; 
        border-radius: 5px;
        font-size: 16px;
        background: #F5F5F5;
        color: #333;
        transition: border-color 0.3s ease;
    }
    input[type="text"]:hover {
        border-color: #003366; 
        box-shadow: 0 2px 6px rgba(0, 51, 102, 0.2);
    }

    input[type="text"]:focus {
        border-color: #003366;
        outline: none;
        box-shadow: 0 4px 8px rgba(0, 51, 102, 0.3);
    }
    input[type="submit"] {
        width: 60%;
        margin-top: 10px;
    }

    .footer{
        display: none;
    }
    
    .error {
        width: 60%;
        color: #d40000;
        font-size: 16px;
        background: #ffe6e6;
        border: 1px solid #d40000;
        border-radius: 5px;
        padding: 10px;
        margin: 1px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 660px) {
        body{
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        .container {
            text-align: center;
            flex-direction: column;
            width: 90%;
            background: unset;
            box-shadow: unset;
        }
        .footer{
            display: flex;
        }
        h1{
            margin: 0 0 0 0;
        }
        .separator {
            display: none;
        }
        form {
            width: 90%;
            margin-top: 40%;
        }
    }
</style>
</html>