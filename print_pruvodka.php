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
        if(isset($_POST['duvod'])){
            $nadpis = "Blokovaná výroba";
            $od = $_POST['od'] ?? '';
            $do = $_POST['do'] ?? '';
            $duvod = $_POST['duvod'] ?? '';
            $id_nav = $_POST['id_nav'] ?? '';

            if(isset($_POST['od']) && isset($_POST['do'])){
                $sql = "SELECT sp.titr_skup, sp.c_spec, s.nazev, FORMAT(n.konec, 'HH:mm') as konec, n.serie
                        FROM (Specifikace as sp JOIN Naviny as n ON sp.id_spec = n.id_spec) JOIN Stroje as s ON n.id_stroj = s.id_stroj JOIN Stav_stroje as ss ON ss.id_stav = n.stav_stroje
                        WHERE ? <= n.konec AND n.konec <= ? AND stav_stroje = ? 
                        ORDER BY n.konec, n.doba;";
                $params = [$od, $do, 1];
            }
            else{
                $sql = "SELECT sp.titr_skup, sp.c_spec, s.nazev, FORMAT(n.konec, 'HH:mm') as konec, n.serie
                        FROM (Specifikace as sp JOIN Naviny as n ON sp.id_spec = n.id_spec) JOIN Stroje as s ON n.id_stroj = s.id_stroj JOIN Stav_stroje as ss ON ss.id_stav = n.stav_stroje
                        WHERE n.id_nav = ? AND n.stav_stroje = ? 
                        ORDER BY n.konec, n.doba;";
                $params = [$id_nav, 1];
            }
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            $naviny = [];
            while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $naviny[] = $zaznam;
            }
            sqlsrv_free_stmt($result);
            $pocet = count($naviny);
            $prvni = "Důvod:";
        }else{
            if(isset($_POST['pruvodni']))
                $nadpis = $_POST['pruvodni'];
            else if(isset($_POST['blokovana']))
                $nadpis = $_POST['blokovana'];
            else if (isset($_POST['testovaci']))
                $nadpis = $_POST['testovaci'];
            else
                return;
            $id_nav = $_POST['id_nav'];
    
            $sql = "SELECT v.vyrobek, s.c_spec, FORMAT(n.konec, 'HH:mm') as konec, n.serie, s.titr_skup, st.nazev
                    FROM ((Specifikace as s LEFT JOIN Vyrobky as v on s.id_vyr = v.id_vyr) JOIN Naviny as n on n.id_spec = s.id_spec) JOIN Stroje as st on n.id_stroj = st.id_stroj
                    WHERE n.id_nav = ?;";
            $params = [$id_nav];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($result);
            $pocet = 1;
            $prvni = "Výrobek:";
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
    <?php for($i=0; $i<$pocet; $i++) : ?>
        <?php if(isset($naviny)) $zaznam = $naviny[$i]; ?>
        <div class="page">
            <h1><?= $nadpis ?></h1>
            <table>
                <tr>
                    <td width="50%"><?= $prvni ?></td>
                    <td><?= $prvni == "Výrobek:" ? $zaznam['vyrobek'] : $duvod ?></td>
                </tr>
                <tr>
                    <td>Číslo specifikace:</td>
                    <td><?= $zaznam['c_spec'] ?></td>
                </tr>
                <tr>
                    <td>Stroj:</td>
                    <td><?= $zaznam['nazev'] ?></td>
                </tr>   
                <tr>
                    <td>Odtah:</td>
                    <td><?= $zaznam['konec'] ?></td>
                </tr>    
                <tr>
                    <td>Série:</td>
                    <td><?= $zaznam['serie'] ?></td>
                </tr>
            </table>
            <h1>Titr-<?= $zaznam['titr_skup'] ?></h1>
        </div>
    <?php endfor; ?>
    <style>
        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: white;
            font: 12pt "Tahoma";
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
            margin: 10mm auto;
            border: 1px solid black;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 3.4em;
            text-decoration: underline;   
            text-align: center;
        }
        h1:first-of-type {
            padding-top: 75%;
        }
        table{
            font-size: xx-large;
            margin: 0 auto;
        }
        tr td:last-child {
            text-align: right;
        }
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;        
            }
            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>