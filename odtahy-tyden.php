<?php
    function formatDate(DateTime $pondeli, int $den) {
        $datum = clone $pondeli;
        $datum->modify('+' . ($den) . ' day');

        return $datum->format("d. m. Y");
    }
    function dobaToColor(int $minuty, array $doba): string{
        $min = $doba['min_doba']; 
        $max = $doba['max_doba']; 

        $ratio = ($minuty - $min) / ($max - $min);
        $sat = 80;
        $light = 65;

        if ($ratio <= (2/3)) {
            // zelená (120) → žlutá (55)
            $local = $ratio / (2/3);
            $hue = 120 - (120 - 55) * $local;
        } else {
            // žlutá (55) → oranžová (10)
            $local = ($ratio - (2/3)) / (1/3);
            $hue = 55 - (55 - 10) * $local;
        }

        return "hsl(" . round($hue) . ", {$sat}%, {$light}%)";
    }

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
    $rozdil = $date->format("N") - 1;

    if ($rozdil != 0) {
        $date->modify("-$rozdil days");
    }

    if (!isset($_GET['date']) || $_GET['date'] !== $date->format("Y-m-d")) {
        header("Location: odtahy-tyden.php?date=" . $date->format("Y-m-d"));
        exit;
    }    
    $cis_tydne = $date->format("W");
    $od = (clone $date)->modify("+5 hours +45 minutes");
    $do = (clone $date)->modify("+7 days +5 hours +35 minutes");

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

    $sql = "SELECT DISTINCT titr_skup from Specifikace as s JOIN Naviny as n ON s.id_spec = n.id_spec where ? <= n.konec AND n.konec < ?;";
    $params = [$od, $do];
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
            <li><a href="odtahy-den.php?date=<?= date_format(new DateTime(), "Y-m-d") ?>">Odtahy - den</a></li>
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
            <input type="submit" name="subTisk" class="defButt print" id="subTisk" value="Tisk" title="Tisk týdenního plánu odtahů">
            <input type="hidden" name="den" value="">
        </form>
        <iframe id="frame" name="printFrame" style="display: none;"></iframe>
    </div>
    <div class="naviny">
        <?php if (isset($skup)) : ?>
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
                    <?php
                        $sql = "SELECT
                                    MIN(DATEDIFF(MINUTE, '00:00:00', doba)) AS min_doba,
                                    MAX(DATEDIFF(MINUTE, '00:00:00', doba)) AS max_doba
                                FROM Naviny;";
                        $result = sqlsrv_query($conn, $sql);
                        if ($result === FALSE)
                            die(print_r(sqlsrv_errors(), true));
                        $doba = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                        sqlsrv_free_stmt($result);
                    ?>
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
                            $params = [$od, $do, $skup[$i]];
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
        
                                $sql = "SELECT n.id_nav, s.nazev, sp.c_spec, n.zacatek, n.konec, n.doba, n.stav_stroje, CONCAT(ss.zkratka, ' (', ss.nazev, ')') AS stav
                                        FROM ((Specifikace as sp JOIN Naviny as n ON sp.id_spec = n.id_spec) JOIN Stroje as s ON n.id_stroj = s.id_stroj) JOIN Stav_stroje as ss ON n.stav_stroje = ss.id_stav
                                        WHERE ? <= n.konec AND n.konec < ? AND sp.titr_skup = ? AND s.id_stroj = ?
                                        ORDER BY n.konec;";
                                $params = [$od, $do, $skup[$i], $stroj];
                                $result = sqlsrv_query($conn, $sql, $params);
                                if ($result === FALSE)
                                    die(print_r(sqlsrv_errors(), true));
        
                                $naviny = [];
                                while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                                    $naviny[] = $zaznam;
                                }
                            ?>
                            <tr>
                                <td><?= $naviny[0]['nazev'] ?></td>
                                <?php for($k=0; $k<7; $k++) : ?>
                                    <?php $zacatek = (clone $date)->modify('+' . ($k) . ' day +5 hours +45 minutes'); ?>
                                    <?php for ($l=0; $l<3; $l++) : ?>
                                        <?php 
                                            if ($l != 0) 
                                                $zacatek->modify('+8 hours');
                                            $konec = (clone $zacatek)->modify('+7 hours +50 minutes');
                                            $obsah = '';
                                            $barva = 'white';
                                            for($m=0; $m < count($naviny); $m++) {
                                                if ($naviny[$m]['konec'] >= $zacatek && $naviny[$m]['konec'] <= $konec) {
                                                    $obsah = $naviny[$m]['konec']->format("H:i");
                                                    $title = "Specifikace: " . $naviny[$m]['c_spec'] . ",\nZačátek: " . $naviny[$m]['zacatek']->format("H:i") . ",\nDoba: " . $naviny[$m]['doba']->format("H:i") . ",\nStav: " . $naviny[$m]['stav'];
                                                    $minuty = ((int)$naviny[$m]['doba']->format('H') * 60) + (int)$naviny[$m]['doba']->format('i');
                                                    $barva = dobaToColor($minuty, $doba);
                                                    $class = $naviny[$m]['stav_stroje'] == 4 ? 'mimo' : '';
                                                    $id_nav = $naviny[$m]['id_nav'];
                                                    break;
                                                }   
                                            }
                                        ?>
                                        <td style="background-color: <?= $barva ?>" class="<?= $class ?>"><a href="" title="<?= $title ?>" class="tyden-link <?= $class ?>" data-id_nav="<?= $id_nav ?>"><?= $obsah ?></a></td>
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
        <?php else : ?>
            <div class='no-data'>Na zadaný týden nejsou naplánovány žádné odtahy.</div>        
        <?php endif; ?>
    </div>
    <div class="footer">
        <img src="Indorama.png" width="200px">
    </div>
    <div class="modal novy">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close closeBtn">&times;</span>
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
        </div>
    </div>
    <div class="modal alert">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close closeBtn">&times;</span>
                <h2>Upozornění</h2>
            </div>
            <div class="modal-body">
                <h3>Plán návinů pro následující tři týdny byl již vytvořen!</h3>
            </div>
        </div>
    </div>
    <div class="modal zmena">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close closeBtn">&times;</span>
                <h2>Změna návinu</h2>
            </div>
            <div class="modal-body">
                <div class="zmena-menu">
                    <button type="button" class="defButt active" data-target="doba-content">Doba návinu</button>
                    <button type="button" class="defButt" data-target="spec-content">Specifikace</button>
                    <button type="button" class="defButt" data-target="stav-content">Stav stroje</button>
                    <button type="button" class="defButt" data-target="posun-content">Posun začátku</button>
                </div>
                <div class="zmena-content doba-content" style="display: block;">
                    <h3>Změna stavu pro stroj:</h3>
                    <h4>série: 260126</h4>
                    <h5>25.01.2026 15:45:00 > 26.01.2026 5:45:00</h5>
                    <form action="sub_db.php" method="post" id="formDoba">
                        <table>
                            <tbody>
                                <tr>
                                    <td>Doba návinu</td>
                                    <td><input type="text" name="doba_navinu" class="timeDoba" id="doba_navinu" placeholder="00:00" data-origo=""></td>
                                </tr>
                                <tr>
                                    <td>Specifikace</td>
                                    <td><select name="id_spec" class="specifikace"></select></td>
                                </tr>
                                <tr>
                                    <td colspan="2" id="novaDoba" class="nove" data-od="" data-do=""></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><p>Změna se provede pro tento a všechny následující náviny</p></td>
                                </tr>
                            </tbody>
                        </table>
                        <input type="hidden" name="id_stroj" class="inputIdStroje">
                        <input type="hidden" name="zacatek" class="inputZacatek">
                        <input type="hidden" name="stav" id="inputStav">
                    </form>
                </div>
                <div class="zmena-content spec-content">
                    <h3>Změna stavu pro stroj:</h3>
                    <h4>série: 260126</h4>
                    <h5>25.01.2026 15:45:00 > 26.01.2026 5:45:00</h5>
                    <form action="sub_db.php" method="post" id="formSpec">
                        <table>
                            <tbody>
                                <tr>
                                    <td>Specifikace</td>
                                    <td><select name="id_spec" class="specifikace"></select></td>
                                </tr>
                            </tbody>
                        </table>
                        <fieldset>
                            <input type="radio" name="navin_volba_spec" id="tento_nav_spec" value="Tento návin" checked>
                            <label for="tento_nav_spec">Tento návin</label>
    
                            <input type="radio" name="navin_volba_spec" id="nasl_nav_spec" value="Všechny následující náviny">
                            <label for="nasl_nav_spec">Všechny následující náviny</label>
    
                            <input type="radio" name="navin_volba_spec" id="tento_nasl_nav_spec" value="Tento a všechny následující náviny">
                            <label for="tento_nasl_nav_spec">Tento a všechny následující náviny</label>
    
                            <input type="radio" name="navin_volba_spec" id="pocet_nav_spec" value="Počet návinů">
                            <label for="pocet_nav_spec">Počet návinů</label>
    
                            <input type="radio" name="navin_volba_spec" id="do_data_spec" value="Následující náviny do data">
                            <label for="do_data_spec">Následující náviny do data</label>
                        </fieldset>
                        <input type="hidden" name="id_stroj" class="inputIdStroje">
                        <input type="hidden" name="zacatek" class="inputZacatek">
                    </form>
                </div>
                <div class="zmena-content stav-content">
                    <h3>Změna stavu pro stroj:</h3>
                    <h4>série: 260126</h4>
                    <h5>25.01.2026 15:45:00 > 26.01.2026 5:45:00</h5>
                    <table>
                        <tbody>
                            <tr>
                                <td>Stav stroje</td>
                                <td><select name="stav" id="stavSelect"></select></td>
                            </tr>
                        </tbody>
                    </table>
                    <fieldset>
                        <input type="radio" name="navin_volba_stav" id="tento_nav_stav" value="Tento návin" checked>
                        <label for="tento_nav_stav">Tento návin</label>

                        <input type="radio" name="navin_volba_stav" id="nasl_nav_stav" value="Všechny následující náviny">
                        <label for="nasl_nav_stav">Všechny následující náviny</label>

                        <input type="radio" name="navin_volba_stav" id="tento_nasl_nav_stav" value="Tento a všechny následující náviny">
                        <label for="tento_nasl_nav_stav">Tento a všechny následující náviny</label>

                        <input type="radio" name="navin_volba_stav" id="pocet_nav_stav" value="Počet návinů">
                        <label for="pocet_nav_stav">Počet návinů</label>

                        <input type="radio" name="navin_volba_stav" id="do_data_stav" value="Následující náviny do data">
                        <label for="do_data_stav">Následující náviny do data</label>
                    </fieldset>
                </div>
                <div class="zmena-content posun-content">
                    <h3>Změna stavu pro stroj:</h3>
                    <h4>série: 260126</h4>
                    <h5>25.01.2026 15:45:00 > 26.01.2026 5:45:00</h5>
                    <table>
                        <tbody>
                            <tr>
                                <td>Posun začátku o</td>
                                <td><input type="text" name="posun" class="time" id="posun_zacatku" placeholder="00:00"></td>
                            </tr>
                            <tr>
                                <td>Specifikace</td>
                                <td><select name="specifikace" class="specifikace"></select></td>
                            </tr>
                            <tr>
                                <td colspan="2" id="novyZacatek" class="nove" data-zacatek=""></td>
                            </tr> 
                            <tr>
                                <td colspan="2"><p>Změna se provede pro tento a všechny následující náviny</p></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="defButt" id="ulozitZmenu">Uložit změny</button>
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
        .naviny th {
            padding: 12px;
            text-align: center;
            text-transform: uppercase;
            background-color: #d9eaed;
            border: 1px solid #c3d7db;
        }
        .naviny td {
            padding: 2px;
            border: 1px solid #c3d7db;
            color: #333;
            text-align: center;
        }
        .naviny td a{
            color: inherit;
            text-decoration: none;
        }
        .naviny td:hover{
            text-decoration: underline;
        }
        .mimo{
            background-color: #fff !important;
            color: #868686 !important;
            text-decoration: line-through;
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

        .zmena .modal-body {
            display: flex;
            gap: 20px;
        }

        .zmena-menu {
            display: flex;
            flex-direction: column;
            width: 200px;
            gap: 10px;
        }

        .zmena-menu .defButt {
            flex: auto;
            text-align: left;
            border: 1px solid #ccc;
            background: #f5f5f5;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.2s ease;
            max-height: 8vh;
        }
        .zmena-menu .defButt:hover {
            background: #e9e9e9;
        }
        .zmena-menu .defButt.active {
            background: #2f80ed;
            color: #fff;
            position: relative;
        }
        .zmena-menu .defButt.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: #000000;
            border-radius: 4px 0 0 4px;
        }

        .zmena-content {
            display: none;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
            width: 25vw;
            height: 60vh;
        }

        /* === NADPISY === */
        .zmena-content h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }

        .zmena-content h4 {
            margin: 0 0 5px 0;
            font-size: 15px;
            color: #555;
        }

        .zmena-content h5 {
            margin: 0 0 15px 0;
            font-size: 13px;
            color: #777;
        }

        /* === TABULKY === */
        .zmena-content table {
            width: 100%;
            border-collapse: collapse;
        }

        .zmena-content td {
            padding: 8px;
            vertical-align: middle;
        }

        .zmena-content td:first-child {
            width: 40%;
            font-weight: 600;
        }

        /* === INPUTY A SELECTY === */
        .zmena-content input,
        .zmena-content select {
            width: 100%;
            padding: 6px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        /* === INFO TEXT === */
        .zmena-content p {
            margin: 10px 0 0 0;
            font-size: 13px;
            color: #666;
            font-style: italic;
        }

        /* === SPECIÁLNÍ ŘÁDEK === */
        .nove {
            font-weight: 600;
            color: #2f80ed;
        }


        .footer{
            display: none;
        }
        
        @media (max-width: 660px) {
            
        }
    </style>
</body>
</html>