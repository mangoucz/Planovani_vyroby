<?php
    session_start();

    if (isset($_SESSION['uziv']))
        $uziv = $_SESSION['uziv'];
    else{
        header("Location: login.php");
        exit();    
    }
    require_once 'server.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['subTisk'])) {
            $den = $_POST['den'];
            $date = DateTime::createFromFormat("Y-m-d", $den);

            $od = $date->format("Y-m-d") . " 05:45";
            $do = (clone $date)->modify("+1 day")->format("Y-m-d") . " 05:35";

            $sql = "SELECT * FROM Stav_stroje;";
            $result = sqlsrv_query($conn, $sql);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            $stavy = [];
            while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $stavy[] = $zaznam;
            }
            sqlsrv_free_stmt($result);

            //pocet sloupců
            $sql = "SELECT DISTINCT doba FROM Naviny as n WHERE ? <= n.konec AND n.konec <= ? AND n.stav_stroje = ? ORDER BY doba;";
            $params = [$od, $do, 1];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            $doby = [];
            while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $doby[] = $zaznam['doba'];
            }
            $pocetSloupcu = count($doby);

            $sql = "SELECT sp.titr_skup, sp.c_spec, s.nazev, n.konec, n.doba, n.stav_stroje
                    FROM (Specifikace as sp JOIN Naviny as n ON sp.id_spec = n.id_spec) JOIN Stroje as s ON n.id_stroj = s.id_stroj JOIN Stav_stroje as ss ON ss.id_stav = n.stav_stroje
                    WHERE ? <= n.konec AND n.konec <= ? AND stav_stroje = ? 
                    ORDER BY n.konec, n.doba;";
            $params = [$od, $do, 1];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            $naviny = [];
            while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $naviny[] = $zaznam;
            }
            $cas = new DateTime('05:45');
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="docHeader">
        <div style="display: flex; flex-direction: column;">
            <h4>ČASOVÉ SCHÉMA UKONČENÍ NÁVINU VLÁKNA VISCORD</h4>
            <p>datum: <?= $date->format("d.m.Y") ?></p>
        </div>
        <img src="Indorama.png" width="130px">
    </div>
    <div class="naviny">
        <?php for($i=0; $i < 3; $i++): //počet tabulek?>
            <table>
                <thead>
                    <tr><th rowspan="3"></th><th colspan="<?= $pocetSloupcu ?>"><?= $date->format("d.m.Y") ?></th></tr>
                    <tr><th colspan="<?= $pocetSloupcu ?>"><?= $i == 0 ? 'ranní' : ($i == 1 ? 'odpolední' : 'noční') ?></th></tr>
                    <tr>
                        <?php for($j=0; $j<$pocetSloupcu; $j++) : ?>
                            <th><?= $doby[$j]->format("H:i") ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for($j=0; $j<48; $j++) : //počet řádků ?>
                        <tr>
                            <td><?= $cas->format("H:i") ?></td>
                            <?php for($k=0; $k < $pocetSloupcu; $k++) : //počet sloupců ?>
                                <?php // hledáme navin, který odpovídá tomuto řádku a sloupci
                                    $obsah = "";
                                    $barva = "#ffffff";
                                    for($l=0; $l < count($naviny); $l++) {
                                        if ($naviny[$l]['doba']->format("H:i") == $doby[$k]->format("H:i") && $naviny[$l]['konec']->format("H:i") == $cas->format("H:i")) {
                                            $obsah = $naviny[$l]['nazev'];
                                            break;
                                        }
                                    }
                                ?>
                                <td><?= $obsah ?></td>
                            <?php endfor; ?>
                            <?php $cas->modify("+10 minutes"); ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        <?php endfor; ?>
    </div>
    <div class="docFooter">
        <?php
            $sql = "SELECT uziv_jmeno as uzivatel FROM Zamestnanci WHERE id_zam = ?;";
            $params = [$uziv];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));    
            $uzivatel = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['uzivatel'];
        ?>
        <p><?= __FILE__ ?></p>
        <p>Vytištěno: <?= date('d.m.Y H:i:s') ?> uživatelem: <?= $uzivatel ?></p>
    </div>
    <style>
        body{
            font-family: Arial, Verdana, Sans-Serif;
        }
        td, th, h4{
            font-weight: bold;
        }
        .naviny {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            margin: 0 auto;
        }
        .naviny table {
            border-collapse: collapse;
            width: 100%;
            background: #ffffff;
            border: 1px solid #ccc;
            font-size: 9pt;
            table-layout: fixed; 
        }
        .naviny td, .naviny th {
            border: 1px solid #e0e0e0;
            padding: 1px 2px;
            text-align: center;
            vertical-align: middle;
        }
        .docHeader{
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 10px;
        }
        .docFooter{
            margin-top: 10px;
        }
        .docFooter p, .docHeader p{
            font-size: 9pt;
            margin: 1px 0 0 0;
        }
        .docHeader h4 {
            margin: 0 0 0 0;
        }

        @page {
            size: A4;
            margin: 1cm 0.5cm;
        }   
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>