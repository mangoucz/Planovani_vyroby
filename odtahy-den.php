<?php
    session_start();
    if (isset($_SESSION['uziv']))
        $uziv = $_SESSION['uziv'];
    else{
        header("Location: login.php");
        exit();    
    }

    try {
        $date = new DateTime($_GET['date'] ?? null);
    } catch (Exception $e) {
        $date = new DateTime();
    }
    if (!isset($_GET['date']) || $_GET['date'] !== $date->format("Y-m-d")) {
        header("Location: odtahy-den.php?date=" . $date->format("Y-m-d"));
        exit;
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
    $admin = $_SESSION['admin'];
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

    $sql = "SELECT sp.titr_skup, sp.c_spec, s.nazev, n.konec, n.doba, n.stav_stroje, n.id_nav
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
            <?php 
                $d = new DateTime();
                $rozdil = $d->format('N')-1;              
                $d->modify("-$rozdil days");
            ?>
            <li><a href="odtahy-tyden.php?date=<?= date_format($d, "Y-m-d") ?>">Odtahy - týden</a></li>
            <li><a href="odtahy-den.php" class="active">Odtahy - den</a></li>
            <li><a href="specifikace.php">Specifikace</a></li>
            <li><a href="stroje.php">Stroje</a></li>
            <?php if($admin): ?><li><a href="administrace.php">Administrace</a></li><?php endif; ?>
        </ul>
    </div>
    <div class="setting">
        <button type="button" id="setDnes" class="defButt">Dnes</button>
        <form action="" method="get">
            <input type="text" id="denOdtahu" name="date" class="date">
        </form>
        <form action="print_den.php" method="post" target="printFrame">
            <input type="submit" name="subTisk" class="defButt print" id="subTisk" value="Tisk" title="Tisk denního plánu odtahů" <?= $pocetSloupcu == 0 ? "disabled" : "" ?>>
            <input type="hidden" name="den" value="<?= $date->format("Y-m-d") ?>">
        </form>
        <iframe id="frame" name="printFrame" style="display: none;"></iframe>
    </div>
    <div class="naviny">
        <?php if ($pocetSloupcu != 0): ?>
            <?php for($i=0; $i < 3; $i++): //počet tabulek?>
                <table>
                    <thead>
                        <tr><th rowspan="4"></th><th colspan="<?= $pocetSloupcu ?>" class="tabDat"></th></tr>
                        <tr><th colspan="<?= $pocetSloupcu ?>"><?= $i == 0 ? 'ranní' : ($i == 1 ? 'odpolední' : 'noční') ?></th></tr>
                        <tr><th colspan="<?= $pocetSloupcu ?>">
                            <form action="print_pruvodka.php" method="post" class="printBlokForm" target="printFrame">
                                <a href="" class="print-blok">Tisk (blokovaná výroba)</a>
                                <input type="hidden" name="od" value="<?= $i == 0 ? $od : ($i == 1 ? (((clone $date)->modify("+13 hours 45 minutes"))->format("Y-m-d H:i")) : (((clone $date)->modify("+21 hours 45 minutes"))->format("Y-m-d H:i"))) ?>">
                                <input type="hidden" name="do" value="<?= $i == 0 ? (((clone $date)->modify("+13 hours 35 minutes"))->format("Y-m-d H:i")) : ($i == 1 ? (((clone $date)->modify("+21 hours 35 minutes"))->format("Y-m-d H:i")) : (((clone $date)->modify("+1 day +5 hours 35 minutes"))->format("Y-m-d H:i"))) ?>">
                                <input type="hidden" name="duvod" value="<?= $i ?>" class="duvodInputBlok">
                            </form>
                        </th></tr>
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
                                                $title = "skupina titrů: " . $naviny[$l]['titr_skup'] . ",\n specifikace: " . $naviny[$l]['c_spec'];
                                                $barva = $naviny[$l]['stav_stroje'] == 1 ? "#d9f3ff" : "#ffd9d9";
                                                $dataID = $naviny[$l]['id_nav'];
                                                break;
                                            }
                                        }
                                    ?>
                                    <td style="background-color: <?= $barva ?>"><a href="" class="den-link" data-id="<?= $dataID ?>" title="<?= $title ?>"><?= $obsah ?></a></td>
                                <?php endfor; ?>
                                <?php $cas->modify("+10 minutes"); ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            <?php endfor; ?>
        <?php else: ?>
            <div class='no-data'>Na zadaný den nejsou naplánovány žádné odtahy.</div>        
        <?php endif; ?>
    </div>
    <div class="footer">
        <img src="Indorama.png" width="200px">
    </div>
    <div class="modal stroj">
        <div class="modal-content">
            <div class="modal-header">
                <span id="closeBtn" class="close">&times;</span>
                <h2>Vybraný stroj:</h2>
            </div>
            <div class="modal-body">
                <form action="print_pruvodka.php" method="post" target="printFrame">
                    <label class="karta">
                        <input type="submit" value="PRŮVODNÍ LIST" name="pruvodni">
                        <div class="karta-nadpis">PRŮVODNÍ LIST</div>
                        <div class="karta-podnadpis">
                            Tisk průvodního listu pro vybraný stroj
                        </div>
                    </label>
                    <label class="karta">
                        <input type="submit" value="BLOKOVANÁ VÝROBA" name="blokovana">
                        <div class="karta-nadpis">BLOKOVANÁ VÝROBA</div>
                        <div class="karta-podnadpis">
                            Tisk průvodky blokované výroby pro vybraný stroj
                        </div>
                    </label>
                    <label class="karta">
                        <input type="submit" value="TESTOVACÍ VÝROBA" name="testovaci">
                        <div class="karta-nadpis">TESTOVACÍ VÝROBA</div>
                        <div class="karta-podnadpis">
                            Tisk průvodky testovací výroby pro vybraný stroj
                        </div>
                    </label>
                    <input type="hidden" name="id_nav" value="" id="navIDInput">
                </form>
                <form action="print_etiketa.php" method="post">
                    <label class="karta">
                        <input type="submit" value="ETIKETY" name="etikety">
                        <div class="karta-nadpis">ETIKETY</div>
                        <div class="karta-podnadpis">
                            Tisk etiket pro vybraný stroj
                        </div>
                    </label>
                </form>
            </div>
        </div>
    </div>
    <div class="modal duvod">
        <div class="modal-content">
            <div class="modal-header">
                <span id="closeDuvodBtn" class="close">&times;</span>
                <h2>Důvod blokování výroby:</h2>
            </div>
            <div class="modal-body">
                <input type="text" name="duvod" id="duvodInput" placeholder="Zadejte důvod blokování výroby" style="width: 100%;">
                <button type="button" id="subDuvod" class="defButt print" style="margin-top: 10px;">Tisk</button>
            </div>
        </div>
    </div>
    <style>
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
        .naviny {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 10px;
            align-items: flex-start;
            width: 80vw;
            margin: 0 auto;
        }
        .naviny table {
            border-collapse: collapse;
            width: 100%;
            max-width: 90vw;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #ccc;
            font-size: 14px;
            table-layout: fixed; 
        }
        .naviny thead{
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .naviny thead th {
            background: #f5f5f5;
            padding: 3px;
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }
        .naviny tbody{
            display: block;
            max-height: 70vh;   
            overflow-y: auto;    
            overflow-x: hidden; 
            width: 100%;
        }
        .naviny tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;    
        }
        tbody td:first-child {
            font-weight: bold;
            background: #fafafa;
            border-right: 1px solid #ccc;
        }
        .naviny td, .naviny th {
            border: 1px solid #e0e0e0;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
        }
        .naviny td:has(a:not(:empty)) {
            background: #d9f3ff;
            font-weight: 600;
        }
        .naviny td:has(a:not(:empty)):hover {
            background: #a6e1ff;
        }
        .naviny td a {
            color: #0055aa;
            text-decoration: none;
            display: block;
            width: 100%;
            height: 100%;
        }
        .naviny tr:last-child th{
            border-bottom: 1px solid #4b7c85;
        }
        .naviny thead a {
            color: #0055aa;
            text-decoration: none;
            font-weight: 600;
        }
        .naviny thead a:hover {
            text-decoration: underline;
        }
        .naviny tbody::-webkit-scrollbar {
            width: 1px;
        }

        .karta {
            display: block;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            background: #fff;
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
        }
        .karta input[type="submit"] {
            display: none;
        }
        .karta:hover {
            background: #f5f7fa;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        .karta-nadpis {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .karta-podnadpis {
            font-size: 13px;
            color: #555;
        }
      
        .footer{
            display: none;
        }
        
        @media (max-width: 660px) {
            .naviny {
                flex-direction: column;
                gap: 30px;
                margin-bottom: 5vh;
            }
        }
    </style>
</body>
</html>