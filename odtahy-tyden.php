<?php
    function formatDate(DateTime $pondeli, int $den) {
        $datum = clone $pondeli;
        $datum->modify('+' . ($den) . ' day');

        return $datum->format("d. m. Y");
    }

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

    $date = new DateTime ($_GET['date']);
    $cis_tydne = $date->format("W");

    $sql = "SELECT DISTINCT titr_skup from Specifikace as s JOIN Naviny as n ON s.id_spec = n.id_spec where ? <= n.konec AND n.konec < ?;";
    $params = ['2025-10-27', '2025-11-03'];
    $result = sqlsrv_query($conn, $sql, $params);
    if ($result === FALSE)
        die(print_r(sqlsrv_errors(), true));

    while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $skup[] = $zaznam['titr_skup'];
    }
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
            <li><a href="odtahy-tyden.php" class="active">Odtahy - týden</a></li>
            <li><a href="odtahy-den.php">Odtahy - den</a></li>
            <li><a href="specifikace.php">Specifikace</a></li>
            <li><a href="stroje.php">Stroje</a></li>
            <?php if($admin): ?><li><a href="administrace.php">Administrace</a></li><?php endif; ?>
        </ul>
    </div>
    <div class="setting">
        <button type="button" id="novyTydenButt" class="defButt">Vytvořit nový týden</button>
        <button type="button" id="setTyden" class="defButt">Tento týden</button>
        <form action="" method="get">
            <input type="text" id="tydenOdtahu" name="date" class="date">
        </form>
        <form action="print_den.php" method="post" target="printFrame">
            <input type="submit" name="subTisk" class="defButt print" id="subTisk" value="Tisk" title="Tisk denního plánu odtahů">
            <input type="hidden" name="den" value="">
        </form>
        <iframe id="frame" name="printFrame" style="display: none;"></iframe>
    </div>
    <table>
        <thead>
            <tr><th colspan="28">Týden č. <?= $cis_tydne ?></th></tr>
            <tr><td colspan="28"></td></tr>
            <tr>
                <td></td>
                <th colspan="3">Pondělí</th>
                <td></td>
                <th colspan="3">Úterý</th>
                <td></td>
                <th colspan="3">Středa</th>
                <td></td>
                <th colspan="3">Čtvrtek</th>
                <td></td>
                <th colspan="3">Pátek</th>
                <td></td>
                <th colspan="3">Sobota</th>
                <td></td>
                <th colspan="3">Neděle</th>
            </tr>
            <tr>
                <?php for ($i=0; $i<7; $i++) : ?>
                    <td></td>
                    <th colspan="3"><?= formatDate($date, $i)?></th>
                <?php endfor; ?>
            </tr>
            <tr>
                <?php for ($i=0; $i<7; $i++) : ?>
                    <td></td>
                    <th>R</th>
                    <th>O</th>
                    <th>N</th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php for ($i=0; $i<count($skup); $i++) : ?>
                <tr><td colspan="28"></td></tr>
                <tr>
                    <?php for($j=0; $j<7; $j++) : ?>
                        <td></td>
                        <th colspan="3"><?= $skup[$i] ?></th>
                    <?php endfor; ?>
                </tr>
                <?php 
                    $sql = "SELECT n.id_stroj, MIN(n.konec) as minNavin 
                            from Specifikace as s JOIN Naviny as n on s.id_spec = n.id_spec 
                            WHERE ? <= n.konec AND n.konec < ? AND titr_skup = ?
                            GROUP BY id_stroj 
                            ORDER BY 2;";
                    $params = ['2025-10-27', '2025-11-03', $skup[$i]];
                    $result = sqlsrv_query($conn, $sql, $params);
                    if ($result === FALSE)
                        die(print_r(sqlsrv_errors(), true));

                    $poradiStroju = [];
                    while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                        $poradiStroju[] = $zaznam;
                    }
                    sqlsrv_free_stmt($result);
                ?>
                <?php  for($j=0; $j<count($poradiStroju); $j++) : ?>
                    <?php
                        $stroj = $poradiStroju[$j]['id_stroj'];

                        $sql = "SELECT n.id_nav, s.nazev, sp.c_spec, n.zacatek, n.konec, n.doba, n.stav_stroje
                                FROM (Specifikace as sp JOIN Naviny as n ON sp.id_spec = n.id_spec) JOIN Stroje as s ON n.id_stroj = s.id_stroj
                                WHERE ? <= n.konec AND n.konec < ? AND sp.titr_skup = ? AND s.id_stroj = ?
                                ORDER BY n.konec;";
                        $params = ['2025-10-27', '2025-11-03', $skup[$i], $stroj];
                        $result = sqlsrv_query($conn, $sql, $params);
                        if ($result === FALSE)
                            die(print_r(sqlsrv_errors(), true));

                        $naviny = [];
                        while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                            $naviny[] = $zaznam;
                        }
                        //print_r($naviny);
                    ?>
                    <tr>
                        <td><?= $naviny[0]['nazev'] ?></td>
                        <?php for($k=0; $k<7; $k++) : ?>
                            <?php for ($l=0; $l<3; $l++) : ?>
                                <td></td>
                            <?php endfor; ?>
                            <?php if($k<6) : ?>
                                <td></td>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            <?php endfor; ?>
        </tbody>
    </table>
    <div class="footer">
        <img src="Indorama.png" width="200px">
    </div>
    <div class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span id="closeBtn" class="close">&times;</span>
                <h2>Vytvoření nového týdne</h2>
            </div>
            <div class="modal-body">
                <h3>24.11.2025 - 30.11.2025</h3>
                <h4>Vyber stav strojů v novém týdnu</h4>
                <form action="sub_db.php" method="post" id="novyTydenForm">
                    <select name="stav_stroju" id="selectStav" required>
                    </select>
                    <input type="hidden" name="pondeli" id="hiPondeli" value="">
                    <input type="submit" name="novyTyden" value="Vytvořit" class="defButt">
                </form>
            </div>
            <div class="footer">

            </div>
        </div>
    </div>
    <style>
        table {
            font-size: 12px;
            font-family: Arial, Tahoma, Verdana;
            color: #202020;
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
            color: #000000;
            font-weight: bold;
        }
        th {
            padding: 12px;
            text-align: center;
            text-transform: uppercase;
            background-color: #d9eaed;
            border: 1px solid #c3d7db;
        }
        td {
            padding: 2px;
            border: 1px solid #c3d7db;
            color: #333;
            text-align: center;
        }
        h2::after {
            content: "";
            display: block;
            width: 25%;
            height: 3px; 
            background: #d40000; 
            margin-top: 5px;
            border-radius: 2px;
        }
        .setting{
            justify-content: space-around;
        }
        #novyTydenForm{
            display: flex;
        }
        .footer{
            display: none;
        }
        
        @media (max-width: 660px) {
            
        }
    </style>
</body>
</html>