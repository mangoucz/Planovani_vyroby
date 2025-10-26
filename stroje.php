<?php
    session_start();
    if (isset($_SESSION['uziv']))
        $uziv = $_SESSION['uziv'];
    else{
        header("Location: login.php");
        exit();    
    }
    require_once 'server.php';

    $sql = "SELECT
                CONCAT(z.jmeno, ' ', z.prijmeni) AS jmeno,
                z.funkce,
                z.uziv_jmeno
            FROM Zamestnanci AS z
            WHERE z.id_zam = ?;";
    $params = [$uziv];
    $result = sqlsrv_query($conn, $sql, $params);
    if ($result === FALSE)
        die(print_r(sqlsrv_errors(), true));

    $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($result);

    $jmeno = $zaznam['jmeno'];
    $funkce = $zaznam['funkce'];
    $uziv_jmeno = $zaznam['uziv_jmeno'];

    switch ($uziv_jmeno) {
        case 'admin':
            $admin = true;
            break;
        case 'kucera':
            $admin = true;
            break;
        default:
            $admin = false;
            break;
    }
    if ($admin) 
        $_SESSION['admin'] = true;

    $sql = "SELECT s.id_schval FROM Schvalovani as s WHERE s.id_schval = ?;";
    $result = sqlsrv_query($conn, $sql, $params);
    if ($result === FALSE)
        die(print_r(sqlsrv_errors(), true));

    $schval = sqlsrv_has_rows($result);
    sqlsrv_free_stmt($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Systém plánování výroby</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="jquery-ui-1.14.1/jquery-ui.css">
    <script src="jquery-3.7.1.min.js"></script>
    <script src="jquery-ui-1.14.1/jquery-ui.js"></script>
    <script src="script.js"></script>
</head>
<body>
    <div class="header">
        <img src="Indorama.png" class="logo">
        <h1>SYSTÉM PLÁNOVÁNÍ VÝROBY</h1>
        <div class="headerB">
            <div class="uziv">
                <img src="user_icon.png" width="28%" style="margin-right: 2%;">
                <div class="uziv_inf">
                    <p><?php echo $jmeno; ?></p>
                    <p style="font-size: 12px; margin-left: 1px;"><?php echo $funkce; ?></p>
                </div>
            </div>
            <a id="logout">
                <img src="logout_icon.png" width="78%" style="cursor: pointer;">
            </a>
        </div>
    </div>
    <div class="menu">
        <ul>
            <li><a href="odtahy-tyden.php">Odtahy - týden</a></li>
            <li><a href="odtahy-den.php">Odtahy - den</a></li>
            <li><a href="specifikace.php">Specifikace</a></li>
            <li><a href="stroje.php" class="active">Stroje</a></li>
            <?php if($admin): ?><li><a href="administrace.php">Administrace</a></li><?php endif; ?>
        </ul>
    </div>
    <table>
        <thead>
            <th>Název stroje</th>
            <th>Skup. strojů</th>
            <th>Skup. titrů</th>
            <th>Specifikace</th>
            <th>Spotřeba viskózy <br>[l/hod]</th>
            <th>Rychlost výr. <br>[kg/hod]</th>
            <th>Rychlost výr. <br>[kg/den]</th>
            <th>Doba návinu</th>
            <th>Příští odtah</th>
        </thead>
        <tbody>

        </tbody>
    </table>
    <div class="footer">
        <img src="Indorama.png" width="200px">
    </div>
    <style>
        table {
            width: 100%;
            max-width: 80vw;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        thead {
            background-color: #5d99a3;
            color: #fff;
        }
        th {
            padding: 15px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #333;
            text-align: center;
        }
        tbody tr:hover {
            background-color: #f8f9fa;
        }

        .footer{
            display: none;
        }


        @media (max-width: 660px) {
            table {
                margin: 10px;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                background: white;
                border-radius: 8px;
            }
            td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
            }
            td:before {
                position: absolute;
                left: 12px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                content: attr(data-label);
            }
        }
    </style>
</body>
</html>