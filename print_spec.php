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
            $id_spec = $_POST['id'] ?? null;
            $typ_stroje = $_POST['typ_stroje'] ?? null;

            switch ($typ_stroje) {
                case '1':
                    $table_name = "Spec_stare";
                    $title = " - staré stroje (A3-A5, B3-B5, C1-C6, D1-D6)";
                    break;
                case '2':
                    $table_name = "Spec_barmag";
                    $title = " - staré stroje s Barmagy (A6-A12, B6-B12, C7-C11, D7-D12)";
                    break;
                case '3':
                    $table_name = "Spec_nove";
                    $title = " - nový stroj (A13, A14, B13, B14, C12, C13, C14, D13, D14)";
                    break;
                default:
                    $table_name = "";
                    $title = "";
                    break;
            }
            $sql = "SELECT * FROM Specifikace AS s RIGHT JOIN $table_name AS st ON s.id_spec = st.id_spec WHERE s.id_spec = ?;";
            $params = [$id_spec];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($result);


            //region konstanty pro výpočty
            $z3 = 19;
            $z4 = 28;
            $z25 = 10;
            $z26 = 30;
            $z33 = 10;
            $z34 = 30;
            $z38 = 10;
            $z39 = 30;
            $z40 = 10;
            $z41 = 30;
            $z42 = 22;
            $z43 = 44;
            $z44 = 22;
            $z45 = 44;
            $z46 = 29;
            $z47 = 58;
            $z48 = 10;
            $z49 = 30;
            $z52 = 18;
            $z53 = 18;
            $z54 = 21;
            $z55 = 14;
            $z58 = 21;
            $z59 = 14;
            //endregion

            if ($typ_stroje == '1'){
                $npohon = $zaznam['hnaci_motor'] * $zaznam['kotouc1'] / $zaznam['kotouc2'];
                $na = $npohon * $z3 / $z4;
                $ng2 = $na * $zaznam['z13'] / $zaznam['z14'] * $zaznam['z15'] / $zaznam['z16'] * $z33 / $z34 * $z53 / $z52;
                $ng1 = $ng2 * $zaznam['z30'] / $zaznam['z32'];
                $vg2 = $ng2 * ($zaznam['galety'] / 1000 * M_PI);
                $vg1 = $ng1 * ($zaznam['galety'] / 1000 * M_PI);
                $nw = $na * $zaznam['z9'] / $zaznam['z10'] * $zaznam['z11'] / $zaznam['z12'] * $z25 / $z26 * $z59 / $z58;
                $vw = $nw * ($zaznam['praci_valce'] / 1000 * M_PI);
                $nwt = $na * $zaznam['z17'] / $zaznam['z18'] * $zaznam['z19'] / $zaznam['z20'] * $z38 / $z39 * $z55 / $z54;
                $vwt = $nwt * ($zaznam['susici_valec'] / 1000 * M_PI);
                $nsp = $na * $zaznam['z21'] / $zaznam['z22'] * $zaznam['z23'] / $zaznam['z24'] * $z40 / $z41 * $z42 / $z43 * $z44 / $z45 * $z46 / $z47 * (1 + $zaznam['korekce']/100);
                $spotr_misto = $nsp * $zaznam['cerpadlo'] * 60 / 1000;
                $spotr_stroj = $spotr_misto * $zaznam['pocet_mist'];
                $vnavijeni = $vwt * (1 + $zaznam['dlouzeni']/100);
                $nnavijeni = $vnavijeni / ($zaznam['navijeci_valec'] / 1000 * M_PI);
                $n1pohon = $nnavijeni * $z49 / $z48;
                $zdvihy = $zaznam['ukladani_motor'] * $zaznam['remenice_m'] / $zaznam['remenice_g'] * 1/30;
                $sg1_g2 = ($ng2 - $ng1) / $ng1 * 100;
                $sg2_w = ($vw - $vg2) / $vg2 * 100;
                $sw_t = ($vwt - $vw) / $vw * 100;
                $sges =  ($vnavijeni - $vg1) / $vg1 * 100;
            }
            elseif($typ_stroje == '2'){
                $npohon = $zaznam['hnaci_motor'] * $zaznam['kotouc1'] / $zaznam['kotouc2'];
                $na = $npohon * $z3 / $z4;
                $ng2 = $na * $zaznam['z13'] / $zaznam['z14'] * $zaznam['z15'] / $zaznam['z16'] * $z33 / $z34 * $z53 / $z52;
                $ng1 = $ng2 * $zaznam['z30'] / $zaznam['z32'];
                $vg2 = $ng2 * ($zaznam['galety'] / 1000 * M_PI);
                $vg1 = $ng1 * ($zaznam['galety'] / 1000 * M_PI);
                $nw = $na * $zaznam['z9'] / $zaznam['z10'] * $zaznam['z11'] / $zaznam['z12'] * $z25 / $z26 * $z59 / $z58;
                $vw = $nw * ($zaznam['praci_valce'] / 1000 * M_PI);
                $nwt = $na * $zaznam['z17'] / $zaznam['z18'] * $zaznam['z19'] / $zaznam['z20'] * $z38 / $z39 * $z55 / $z54;
                $vwt = $nwt * ($zaznam['susici_valec'] / 1000 * M_PI);
                $nsp = $na * $zaznam['z21'] / $zaznam['z22'] * $zaznam['z23'] / $zaznam['z24'] * $z40 / $z41 * $z42 / $z43 * $z44 / $z45 * $z46 / $z47 * (1 + $zaznam['korekce']/100);
                $spotr_misto = $nsp * $zaznam['cerpadlo'] * 60 / 1000;
                $spotr_stroj = $spotr_misto * $zaznam['pocet_mist'];
                $sg1_g2 = ($ng2 - $ng1) / $ng1 * 100;
                $sg2_w = ($vw - $vg2) / $vg2 * 100;
                $sw_t = ($vwt - $vw) / $vw * 100;
                $sges =  ($vwt - $vg1) / $vg1 * 100;

            }
            else{
                $ng2 = $zaznam['vg2'] / ($zaznam['galety'] / 1000 * M_PI);
                $ng2_n = $ng2 * $zaznam['z2g2'] / $zaznam['z1g2'];
                $ng1 = $ng2 / (1 + $zaznam['sg1_g2'] / 100);
                $ng1_n = $ng1 * $zaznam['z2g1'] / $zaznam['z1g1'];
                $vg1 = $ng1 * ($zaznam['galety'] / 1000 * M_PI);
                $vw = $zaznam['vg2'] * (1 + $zaznam['sg2_w'] / 100);
                $nw = $vw / ($zaznam['praci_valce'] / 1000 * M_PI);
                $nw_n = $nw * $zaznam['z2w'] / $zaznam['z1w'];
                $vwt = $vw * (1 + $zaznam['sw_t'] / 100);
                $nwt = $vwt / ($zaznam['susici_valec'] / 1000 * M_PI);
                $nwt_n = $nwt * $zaznam['z2t'] / $zaznam['z1t'];
                $produkce_1 = $zaznam['vg2'] * 60 / 10000 * $zaznam['titr'] / 1000;
                $produkce_stroj = $produkce_1 * $zaznam['pocet_mist'];
                $spotr_misto = $produkce_1 / $zaznam['faktor'] * (1 + $zaznam['korekce'] / 100);
                $spotr_stroj = $spotr_misto * $zaznam['pocet_mist'];
                $nsp = $spotr_misto * 1000 / 60 / $zaznam['cerpadlo'];
                $nsp_n = $nsp * $zaznam['z2sp'] / $zaznam['z1sp'];
                $sges =  ($vwt - $vg1) / $vg1 * 100;
            }
            $sql = "SELECT
                        CONCAT(z.jmeno, ' ', z.prijmeni) AS jmeno,
                        z.funkce
                    FROM Zamestnanci AS z
                    WHERE z.id_zam = ?;";
            $params = [$uziv];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));

            $zam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($result);

            $jmeno = $zam['jmeno'];
            $funkce = $zam['funkce'];
            $admin = $_SESSION['admin'];
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Systém plánování výroby</title>
    <script src="jquery-3.7.1.min.js"></script>
    <script src="jquery-ui-1.14.1/jquery-ui.js"></script>
    <script src="script.js"></script>
    <?php if($_POST['subTisk'] != "Tisk"): ?>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="jquery-ui-1.14.1/jquery-ui.css">
    <?php endif; ?>
</head>
<body>
    <?php if($_POST['subTisk'] != "Tisk"): ?>
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
                <li><a href="specifikace.php" class="active">Specifikace</a></li>
                <li><a href="stroje.php">Stroje</a></li>
                <?php if($admin): ?><li><a href="administrace.php">Administrace</a></li><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th colspan="11" style="font-size: 14px;" id="nadpis" data-typ="<?= $typ_stroje ?>">Specifikace spřádacího stroje VISCORD <?= $title ?></th>
            </tr>
        </thead>
        <tbody id="spec_nove_body">
            <tr>
                <td style="font-weight: bold; font-size: 12px;">Číslo specifikace</td>
                <td><input type="number" readonly name="c_spec" id="c_spec" value="<?= $zaznam['c_spec'] ?? '' ?>" style="font-weight: bold; font-size: 12px;"></td>
                <td></td>
                <td></td>
                <td>Poznámka</td>
                <td colspan="6"><textarea name="poznamka" id="poznamka"><?= $zaznam['poznamka'] ?? '' ?></textarea></td>
            </tr>
            <tr>
                <td>Titr</td>
                <td><input type="number" readonly name="titr" id="titr" value="<?= $zaznam['titr'] ?? '' ?>"></td>
                <td>g/10 000m</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Skupina titrů</td>
                <td><input type="number" readonly name="titr_skup" id="titr_skup" value="<?= $zaznam['titr_skup'] ?? '' ?>"></td>
                <td>g/10 000m</td>
                <td></td>
                <td>Prací válce</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Z1</td>
                <td>Z2</td>
            </tr>
            <tr>
                <td>Galety Ø</td>
                <td><input type="number" readonly name="galety" id="galety" value="<?= $zaznam['galety'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>nW</td>
                <td><input type="number" readonly name="nW" id="nW" value="<?= $nw ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td>Horní galeta<br>(G1)</td>
                <td><input type="number" readonly name="z1_horni" id="z1_horni" value="<?= $zaznam['z1g1'] ?? '' ?>"></td>
                <td><input type="number" readonly name="z2_horni" id="z2_horni" value="<?= $zaznam['z2g1'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Prací válce Ø</td>
                <td><input type="number" readonly name="praci_valce" id="praci_valce" value="<?= $zaznam['praci_valce'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>vW</td>
                <td><input type="number" readonly name="vW" id="vW" value="<?= $vw ?? '' ?>"></td>
                <td>m/min</td>
                <td></td>
                <td>Dolní galeta <br>(G2)</td>
                <td><input type="number" readonly name="z1_dolni" id="z1_dolni" value="<?= $zaznam['z1g2'] ?? '' ?>"></td>
                <td><input type="number" readonly name="z2_dolni" id="z2_dolni" value="<?= $zaznam['z2g2'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Sušící válec Ø</td>
                <td><input type="number" readonly name="susici_valec" id="susici_valec" value="<?= $zaznam['susici_valec'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>Sušič</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Prací válce(W)</td>
                <td><input type="number" readonly name="z1_praci" id="z1_praci" value="<?= $zaznam['z1w'] ?? '' ?>"></td>
                <td><input type="number" readonly name="z2_praci" id="z2_praci" value="<?= $zaznam['z2w'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Spřádací čerpadlo</td>
                <td><input type="number" readonly name="cerpadlo" id="cerpadlo" value="<?= (float)$zaznam['cerpadlo'] ?? '' ?>"></td>
                <td>cm3/ot</td>
                <td></td>
                <td>nWT</td>
                <td><input type="number" readonly name="nWT" id="nWT" value="<?= $nwt ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td>Sušící válec(T)</td>
                <td><input type="number" readonly name="z1_susici" id="z1_susici" value="<?= $zaznam['z1t'] ?? '' ?>"></td>
                <td><input type="number" readonly name="z2_susici" id="z2_susici" value="<?= $zaznam['z2t'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Počet spřádacích míst</td>
                <td><input type="number" readonly name="pocet_sprad_mist" id="pocet_sprad_mist" value="<?= $zaznam['pocet_mist'] ?? '' ?>"></td>
                <td></td>
                <td></td>
                <td>vWT</td>
                <td><input type="number" readonly name="vWT" id="vWT" value="<?= $vwt ?? '' ?>"></td>
                <td>m/min</td>
                <td></td>
                <td>Čerpadla(Sp)</td>
                <td><input type="number" readonly name="z1_cerpadlo" id="z1_cerpadlo" value="<?= $zaznam['z1sp'] ?? '' ?>"></td>
                <td><input type="number" readonly name="z2_cerpadlo" id="z2_cerpadlo" value="<?= $zaznam['z2sp'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Faktor <br> Viskóza/Produkt</td>
                <td><input type="number" readonly name="faktor" id="faktor" value="<?= (float)$zaznam['faktor'] ?? '' ?>"></td>
                <td></td>
                <td></td>
                <td>Spřádací čerpadla</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Dloužení</td>
                <td></td>
                <td></td>
                <td></td>
                <td>nSp</td>
                <td><input type="number" readonly name="nSp" id="nSp" value="<?= $nsp ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>SG1_G2</td>
                <td><input type="number" readonly name="SG1_G2" id="SG1_G2" value="<?= $zaznam['sg1_g2'] ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td>Spotř. viskózy - <br> místo</td>
                <td><input type="number" readonly name="spotr_misto" id="spotr_misto" value="<?= $spotr_misto ?? '' ?>"></td>
                <td>l/hod</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>SG2-W</td>
                <td><input type="number" readonly name="SG2_W" id="SG2_W" value="<?= $zaznam['sg2_w'] ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td>Spotř. viskózy - <br> stroj</td>
                <td><input type="number" readonly name="spotr_stroj" id="spotr_stroj" value="<?= $spotr_stroj ?? '' ?>"></td>
                <td>l/hod</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>SW-T</td>
                <td><input type="number" readonly name="SW_T" id="SW_T" value="<?= $zaznam['sw_t'] ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td>Produkce</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Sges</td>
                <td><input type="number" readonly name="Sges" id="Sges" value="<?= $sges ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td>Produkce 1 místo</td>
                <td><input type="number" readonly name="produkce_1" id="produkce_1" value="<?= $produkce_1 ?? '' ?>"></td>
                <td>kg/hod</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Korekce</td>
                <td><input type="number" readonly name="korekce" id="korekce" value="<?= (float)$zaznam['korekce'] ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td>Produkce stroj</td>
                <td><input type="number" readonly name="produkce_stroj" id="produkce_stroj" value="<?= $produkce_stroj ?? '' ?>"></td>
                <td>kg/hod</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Galety</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>vG2</td>
                <td><input type="number" readonly name="vG2" id="vG2" value="<?= $zaznam['vg2'] ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>nG2</td>
                <td><input type="number" readonly name="nG2" id="nG2" value="<?= $ng2 ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>vG1</td>
                <td><input type="number" readonly name="vG1" id="vG1" value="<?= $vg1 ?? '' ?>"></td>
                <td>m/min</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>nG1</td>
                <td><input type="number" readonly name="nG1" id="nG1" value="<?= $ng1 ?? '' ?>"></td>
                <td>m/min</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
        <tbody id="spec_stare_body">
            <tr>
                <td style="font-weight: bold; font-size: 12px;">Číslo specifikace</td>
                <td><input type="number" readonly name="c_spec" id="c_spec" value="<?= $zaznam['c_spec'] ?? '' ?>" style="font-weight: bold; font-size: 12px;"></td>
                <td></td>
                <td></td>
                <td>Poznámka</td>
                <td colspan="6"><textarea name="poznamka" id="poznamka"><?= $zaznam['poznamka'] ?? '' ?></textarea></td>
            </tr>
            <tr>
                <td>Titr</td>
                <td><input type="number" readonly name="titr" id="titr" value="<?= $zaznam['titr'] ?? '' ?>"></td>
                <td>g/10 000m</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Skupina titrů</td>
                <td><input type="number" readonly name="titr_skup" id="titr_skup" value="<?= $zaznam['titr_skup'] ?? '' ?>"></td>
                <td>g/10 000m</td>
                <td></td>
                <td>n pohon</td>
                <td><input type="number" readonly name="n_pohon" id="n_pohon" value="<?= $npohon ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td>Praní</td>
                <td>Z9</td>
                <td><input type="number" readonly name="z9" id="z9" value="<?= $zaznam['z9'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Hnací motor</td>
                <td><input type="number" readonly name="hnaci_motor" id="hnaci_motor" value="<?= $zaznam['hnaci_motor'] ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td>nA</td>
                <td><input type="number" readonly name="nA" id="nA" value="<?= $na ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td></td>
                <td>Z10</td>
                <td><input type="number" readonly name="z10" id="z10" value="<?= $zaznam['z10'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Ozubený kotouč (ZS1)</td>
                <td><input type="number" readonly name="kotouc1" id="kotouc1" value="<?= $zaznam['kotouc1'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>Galety</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Z11</td>
                <td><input type="number" readonly name="z11" id="z11" value="<?= $zaznam['z11'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Ozubený kotouč (ZS2)</td>
                <td><input type="number" readonly name="kotouc2" id="kotouc2" value="<?= $zaznam['kotouc2'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>nG2</td>
                <td><input type="number" readonly name="nG2" id="nG2" value="<?= $ng2 ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td></td>
                <td>Z12</td>
                <td><input type="number" readonly name="z12" id="z12" value="<?= $zaznam['z12'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Kotouč 3</td>
                <td><input type="number" readonly name="kotouc3" id="kotouc3" value="<?= $zaznam['kotouc3'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>nG1</td>
                <td><input type="number" readonly name="nG1" id="nG1" value="<?= $ng1 ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td>Galeta</td>
                <td>Z13</td>
                <td><input type="number" readonly name="z13" id="z13" value="<?= $zaznam['z13'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Kotouč 4</td>
                <td><input type="number" readonly name="kotouc4" id="kotouc4" value="<?= $zaznam['kotouc4'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>vG2</td>
                <td><input type="number" readonly name="vG2" id="vG2" value="<?= $vg2 ?? '' ?>"></td>
                <td>m/min</td>
                <td></td>
                <td></td>
                <td>Z14</td>
                <td><input type="number" readonly name="z14" id="z14" value="<?= $zaznam['z14'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Galety Ø</td>
                <td><input type="number" readonly name="galety" id="galety" value="<?= $zaznam['galety'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>vG1</td>
                <td><input type="number" readonly name="vG1" id="vG1" value="<?= $vg1 ?? '' ?>"></td>
                <td>m/min</td>
                <td></td>
                <td></td>
                <td>Z15</td>
                <td><input type="number" readonly name="z15" id="z15" value="<?= $zaznam['z15'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Prací válce Ø</td>
                <td><input type="number" readonly name="praci_valce" id="praci_valce" value="<?= $zaznam['praci_valce'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>Prací válce</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Z16</td>
                <td><input type="number" readonly name="z16" id="z16" value="<?= $zaznam['z16'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Sušící válec Ø</td>
                <td><input type="number" readonly name="susici_valec" id="susici_valec" value="<?= $zaznam['susici_valec'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>nW</td>
                <td><input type="number" readonly name="nW" id="nW" value="<?= $nw ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td>Sušící válec</td>
                <td>Z17</td>
                <td><input type="number" readonly name="z17" id="z17" value="<?= $zaznam['z17'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Navíjecí válec Ø</td>
                <td><input type="number" readonly name="navijeci_valec" id="navijeci_valec" value="<?= $zaznam['navijeci_valec'] ?? '' ?>"></td>
                <td>mm</td>
                <td></td>
                <td>vW</td>
                <td><input type="number" readonly name="vW" id="vW" value="<?= $vw ?? '' ?>"></td>
                <td>m/min</td>
                <td></td>
                <td></td>
                <td>Z18</td>
                <td><input type="number" readonly name="z18" id="z18" value="<?= $zaznam['z18'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Spřádací čerpadlo</td>
                <td><input type="number" readonly name="cerpadlo" id="cerpadlo" value="<?= (float)$zaznam['cerpadlo'] ?? '' ?>"></td>
                <td>cm3/U</td>
                <td></td>
                <td>Sušící válec</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Z19</td>
                <td><input type="number" readonly name="z19" id="z19" value="<?= $zaznam['z19'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td>Počet spřádacích míst</td>
                <td><input type="number" readonly name="pocet_sprad_mist" id="pocet_sprad_mist" value="<?= $zaznam['pocet_mist'] ?? '' ?>"></td>
                <td></td>
                <td></td>
                <td>nWT</td>
                <td><input type="number" readonly name="nWT" id="nWT" value="<?= $nwt ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td></td>
                <td>Z20</td>
                <td><input type="number" readonly name="z20" id="z20" value="<?= $zaznam['z20'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td class="stare">Navíjení</td>
                <td></td>
                <td></td>
                <td></td>
                <td>vWT</td>
                <td><input type="number" readonly name="vWT" id="vWT" value="<?= $vwt ?? '' ?>"></td>
                <td>m/min</td>
                <td></td>
                <td>Čerpadla</td>
                <td>Z21</td>
                <td><input type="number" readonly name="z21" id="z21" value="<?= $zaznam['z21'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td class="stare">Dloužení</td>
                <td class="stare"><input type="number" readonly name="dlouzeni" id="dlouzeni" value="<?= (float)$zaznam['dlouzeni'] ?? '' ?>"></td>
                <td class="stare">%</td>
                <td></td>
                <td>Spřádací čerpadla</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Z22</td>
                <td><input type="number" readonly name="z22" id="z22" value="<?= $zaznam['z22'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td class="stare">v navíjení</td>
                <td class="stare"><input type="number" readonly name="v_navijeni" id="v_navijeni" value="<?= $vnavijeni ?? '' ?>"></td>
                <td class="stare">m/min</td>
                <td></td>
                <td>Korekce</td>
                <td><input type="number" readonly name="korekce" id="korekce" value="<?= (float)$zaznam['korekce'] ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td></td>
                <td>Z23</td>
                <td><input type="number" readonly name="z23" id="z23" value="<?= $zaznam['z23'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td class="stare">n navíjení</td>
                <td class="stare"><input type="number" readonly name="n_navijeni" id="n_navijeni" value="<?= $nnavijeni ?? '' ?>"></td>
                <td class="stare">ot/min</td>
                <td></td>
                <td>nSp</td>
                <td><input type="number" readonly name="nSp" id="nSp" value="<?= $nsp ?? '' ?>"></td>
                <td>ot/min</td>
                <td></td>
                <td></td>
                <td>Z24</td>
                <td><input type="number" readonly name="z24" id="z24" value="<?= $zaznam['z24'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td class="stare">n 1 pohon</td>
                <td class="stare"><input type="number" readonly name="n_1pohon" id="n_1pohon" value="<?= $n1pohon ?? '' ?>"></td>
                <td class="stare">ot/min</td>
                <td></td>
                <td>Spotř. viskózy - místo</td>
                <td><input type="number" readonly name="spotr_misto" id="spotr_misto" value="<?= $spotr_misto ?? '' ?>"></td>
                <td>l/hod</td>
                <td></td>
                <td>Galeta</td>
                <td>Z30</td>
                <td><input type="number" readonly name="z30" id="z30" value="<?= $zaznam['z30'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td class="stare">Ukládání</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Spotř. viskózy - stroj</td>
                <td><input type="number" readonly name="spotr_stroj" id="spotr_stroj" value="<?= $spotr_stroj ?? '' ?>"></td>
                <td>l/hod</td>
                <td></td>
                <td>Galeta</td>
                <td>Z32</td>
                <td><input type="number" readonly name="z32" id="z32" value="<?= $zaznam['z32'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td class="stare">Motor</td>
                <td class="stare"><input type="number" readonly name="ukladani_motor" id="ukladani_motor" value="<?= $zaznam['ukladani_motor'] ?? '' ?>"></td>
                <td></td>
                <td></td>
                <td>Dloužení</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="stare">Řemenice M. (RS3)</td>
                <td class="stare"><input type="number" readonly name="remenice_m" id="remenice_m" value="<?= $zaznam['remenice_m'] ?? '' ?>"></td>
                <td class="stare">ot/min</td>
                <td></td>
                <td>SG1-G2</td>
                <td><input type="number" readonly name="SG1_G2" id="SG1_G2" value="<?= $sg1_g2 ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="stare">Řemenice G. (RS4)</td>
                <td class="stare"><input type="number" readonly name="remenice_g" id="remenice_g" value="<?= $zaznam['remenice_g'] ?? '' ?>"></td>
                <td class="stare">mm</td>
                <td></td>
                <td>SG2-W</td>
                <td><input type="number" readonly name="SG2_W" id="SG2_W" value="<?= $sg2_w ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="stare">i pohon 1:30</td>
                <td class="stare"><input type="number" readonly name="i_pohon" id="i_pohon" value="<?= 1/30 ?>"></td>
                <td></td>
                <td></td>
                <td>SW-T</td>
                <td><input type="number" readonly name="SW_T" id="SW_T" value="<?= $sw_t ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="stare">Dvojité zdvihy</td>
                <td class="stare"><input type="number" readonly name="zdvihy" id="zdvihy" value="<?= $zdvihy ?? '' ?>"></td>
                <td class="stare">DH/min</td>
                <td></td>
                <td>Sges</td>
                <td><input type="number" readonly name="Sges" id="Sges" value="<?= $sges ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th colspan="16">Nastavení spřádacího stroje</th>
            </tr>
        </thead>
        <tbody id="nast_nove_body">
            <tr>
                <td colspan="2">Číslo</td>
                <td colspan="2">Titr</td>
                <td colspan="2">Prací válce</td>
                <td colspan="2">Odtah</td>
                <td>Čerpadlo</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2">specifikace</td>
                <td colspan="2">dtex</td>
                <td colspan="2">Ø v mm</td>
                <td colspan="2">m/min</td>
                <td>cm3/U</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2"><input type="number" readonly name="c_spec_nastaveni" id="c_spec_nastaveni" value="<?= $zaznam['c_spec'] ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="titr_nastaveni" id="titr_nastaveni" value="<?= $zaznam['titr'] ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="praci_valce_nastaveni" id="praci_valce_nastaveni" value="<?= $zaznam['praci_valce'] ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="odtah_nastaveni" id="odtah_nastaveni" value="<?= $vwt ?? '' ?>"></td>
                <td><input type="number" readonly name="cerpadlo_nastaveni" id="cerpadlo_nastaveni" value="<?= (float)$zaznam['cerpadlo'] ?? '' ?>"></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2">Spodní galeta</td>
                <td colspan="2">Horní galeta</td>
                <td colspan="2">Prací válce</td>
                <td colspan="2">Sušící válec</td>
                <td>Navíjení</td>
                <td colspan="2">Spř. čepradlo</td>
            </tr>
            <tr>
                <td colspan="2">ot/min</td>
                <td colspan="2">ot/min</td>
                <td colspan="2">ot/min</td>
                <td colspan="2">ot/min</td>
                <td>napínání [g]</td>
                <td colspan="2">ot/min</td>
            </tr>
            <tr>
                <td><input type="number" readonly name="" id="" value="<?= $ng1 ?? '' ?>"></td>
                <td><input type="number" readonly name="" id="" value="<?= $ng1_n ?? '' ?>"></td>
                <td><input type="number" readonly name="" id="" value="<?= $ng2 ?? '' ?>"></td>
                <td><input type="number" readonly name="" id="" value="<?= $ng2_n ?? '' ?>"></td>
                <td><input type="number" readonly name="" id="" value="<?= $nw ?? '' ?>"></td>
                <td><input type="number" readonly name="" id="" value="<?= $nw_n ?? '' ?>"></td>
                <td><input type="number" readonly name="" id="" value="<?= $nwt ?? '' ?>"></td>
                <td><input type="number" readonly name="" id="" value="<?= $nwt_n ?? '' ?>"></td>
                <td>ca 250</td>
                <td><input type="number" readonly name="" id="" value="<?= $nsp ?? '' ?>"></td>
                <td><input type="number" readonly name="" id="" value="<?= $nsp_n ?? '' ?>"></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2">Dloužení</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td>Spotřeba <br> viskózy</td>
                <td><input type="number" readonly name="" id="" value="<?= $spotr_stroj ?? '' ?>"/></td>
                <td>l/h</td>
            </tr>
            <tr>
                <td colspan="2">SG1_G2</td>
                <td colspan="2">SG2_W</td>
                <td colspan="2">SW_T</td>
                <td colspan="2">Sges</td>
                <td></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['sg1_g2'] ?? '' ?>"></td>
                <td>%</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['sg2_w'] ?? '' ?>"></td>
                <td>%</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['sw_t'] ?? '' ?>"></td>
                <td>%</td>
                <td><input type="number" readonly name="" id="" value="<?= $sges ?? '' ?>"></td>
                <td>%</td>
                <td></td>
                <td colspan="2"></td>
            </tr>   
        </tbody>
        <tbody id="nast_stare_body">
            <tr>
                <td colspan="2">Číslo</td>
                <td colspan="2">Titr</td>
                <td colspan="2">Prací válce</td>
                <td colspan="2">Odtah</td>
                <td colspan="2">Čerpadlo</td>
                <td colspan="2"></td>
                <?= $typ_stroje == 1 ? '<td colspan="2"></td>' : '' ?>
                <td colspan="2"></td>

            </tr>
            <tr>
                <td colspan="2">specifikace</td>
                <td colspan="2">dtex</td>
                <td colspan="2">Ø v mm</td>
                <td colspan="2">m/min</td>
                <td colspan="2">cm3/U</td>
                <td colspan="2"></td>
                <?= $typ_stroje == 1 ? '<td colspan="2"></td>' : '' ?>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2"><input type="number" readonly name="c_spec_nastaveni" id="c_spec_nastaveni" value="<?= $zaznam['c_spec'] ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="titr_nastaveni" id="titr_nastaveni" value="<?= $zaznam['titr'] ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="praci_valce_nastaveni" id="praci_valce_nastaveni" value="<?= $zaznam['praci_valce'] ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="odtah_nastaveni" id="odtah_nastaveni" value="<?= $vnavijeni ?? $vwt ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="cerpadlo_nastaveni" id="cerpadlo_nastaveni" value="<?= (float)$zaznam['cerpadlo'] ?? '' ?>"></td>
                <td colspan="2"></td>
                <?= $typ_stroje == 1 ? '<td colspan="2"></td>' : '' ?>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <?= $typ_stroje == 1 ? '<td colspan="2"></td>' : '' ?>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2">Motor</td>
                <td colspan="2">Spodní galeta</td>
                <td colspan="2">Horní galeta</td>
                <td colspan="2">Prací válce</td>
                <td colspan="2">Sušící válec</td>
                <td colspan="2">Navíjení</td>
                <?= $typ_stroje == 1 ? '<td colspan="2">Ukládání</td>' : '' ?>
                <td colspan="2">Spř. čepradlo</td>
            </tr>
            <tr>
                <td colspan="2">ot/min</td>
                <td colspan="2">ot/min</td>
                <td colspan="2">ot/min</td>
                <td colspan="2">ot/min</td>
                <td colspan="2">ot/min</td>
                <td colspan="2"><?= $typ_stroje == 2 ? 'napínání [g]' : 'ot/min' ?></td>
                <?= $typ_stroje == 1 ? '<td colspan="2">DH/min</td>' : '' ?>
                <td colspan="2">ot/min</td>
            </tr>
            <tr>
                <td colspan="2"><input type="number" readonly name="" id="" value="<?= $zaznam['hnaci_motor'] ?? '' ?>"></td>
                <td>ZS1</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['kotouc1'] ?? '' ?>"></td>
                <td>Z30</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z30'] ?? '' ?>"></td>
                <td>Z9</td> 
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z9'] ?? '' ?>"></td>
                <td>Z17</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z17'] ?? '' ?>"></td>
                <?= $typ_stroje == 2 ? '<td colspan="2">ca 250</td>' : '<td></td><td></td>' ?>
                <?= $typ_stroje == 1 ? '<td>KRS3</td>' : '' ?>
                <?= $typ_stroje == 1 ? '<td><input type="number" readonly name="" id="" value="' . ($zaznam['remenice_m'] ?? "") . '"></td>' : '' ?>
                <td>Z21</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z21'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td>ZS2</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['kotouc2'] ?? '' ?>"></td>
                <td></td>
                <td></td>
                <td>Z12</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z12'] ?? '' ?>"></td>
                <td>Z20</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z20'] ?? '' ?>"></td>
                <td><?= $typ_stroje == 2 ? '' : 'Sa[%]' ?></td>  
                <td><?= $typ_stroje == 2 ? '' : '<input type="number" readonly name="" id="" value="' . ((float)$zaznam['dlouzeni'] ?? "") . '">'?></td>
                <?= $typ_stroje == 1 ? '<td>KRS4</td>' : '' ?>
                <?= $typ_stroje == 1 ? '<td><input type="number" readonly name="" id="" value="' . ($zaznam['remenice_g'] ?? "") . '"></td>' : '' ?>
                <td>Z24</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z24'] ?? '' ?>"></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td>Z13</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z13'] ?? '' ?>"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <?= $typ_stroje == 1 ? '<td></td>' : '' ?>
                <?= $typ_stroje == 1 ? '<td></td>' : '' ?>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td>Z16</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z16'] ?? '' ?>"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <?= $typ_stroje == 1 ? '<td></td>' : '' ?>
                <?= $typ_stroje == 1 ? '<td></td>' : '' ?>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td>Z32</td>
                <td><input type="number" readonly name="" id="" value="<?= $zaznam['z32'] ?? '' ?>"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <?= $typ_stroje == 1 ? '<td></td>' : '' ?>
                <?= $typ_stroje == 1 ? '<td></td>' : '' ?>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="2"><input type="number" name="ng1" id="" value="<?= $ng1 ?? '' ?>"></td>
                <td colspan="2"><input type="number" name="ng2" id="" value="<?= $ng2 ?? '' ?>"></td>
                <td colspan="2"><input type="number" name="nw" id="" value="<?= $nw ?? '' ?>"></td>
                <td colspan="2"><input type="number" name="nwt" id="" value="<?= $nwt ?? '' ?>"></td>
                <?= $typ_stroje == 1 ? '<td colspan="2"><input type="number" name="nnavijeni" value="' . ($nnavijeni ?? "") . '"></td>' : '<td></td><td></td>' ?>
                <?= $typ_stroje == 1 ? '<td colspan="2"><input type="number" readonly name="" id="" value="' . ($zdvihy ?? "") . '"></td>' : '' ?>
                <td colspan="2"><input type="number" name="nsp" id="" value="<?= $nsp ?? '' ?>"></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <?= $typ_stroje == 1 ? '<td colspan="2"></td>' : '' ?>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2">Dloužení</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2">Spotřeba viskózy</td>
                <td <?= $typ_stroje == 1 ? 'colspan="2"' : '' ?>><input type="number" name="spotr_stroj" id="" value="<?= $spotr_stroj ?? '' ?>"></td>
                <td <?= $typ_stroje == 1 ? 'colspan="2"' : '' ?>>l/h</td>
            </tr>
            <tr>
                <td colspan="2">SG1-G2</td>
                <td colspan="2">SG2-W</td>
                <td colspan="2">SW-T</td>
                <?= $typ_stroje == 1 ? '<td colspan="2">ST-Aw</td>' : ''?>
                <td colspan="2">Sges</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2"><input type="number" readonly name="" id="" value="<?= $sg1_g2 ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="" id="" value="<?= $sg2_w ?? '' ?>"></td>
                <td colspan="2"><input type="number" readonly name="" id="" value="<?= $sw_t ?? '' ?>"></td>
                <?= $typ_stroje == 1 ? '<td colspan="2"><input type="number" readonly name="" id="" value="' . ((float)$zaznam['dlouzeni'] ?? "") . '"></td>' : ''?>
                <td colspan="2"><input type="number" readonly name="" id="" value="<?= $sges ?? '' ?>"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    <?php
        $sql = "SELECT s.id_zam, CONCAT(z.prijmeni, ' ', z.jmeno) AS zodpovedny FROM Zamestnanci as z RIGHT JOIN Specifikace as s ON z.id_zam = s.id_zam WHERE s.id_spec = ?;";
        $params = [$id_spec];
        $result = sqlsrv_query($conn, $sql, $params);
        if ($result === FALSE)
            die(print_r(sqlsrv_errors(), true));
        $zodpovedny = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($result);
        
        switch ($zodpovedny['id_zam']) {
            case '3':
                $zodpovedny['zodpovedny'] = "Vocásek Martin";
                break;
            case '5':
                $zodpovedny['zodpovedny'] = "Löwyová Uršula"; 
                break;
            default:
                $zodpovedny['zodpovedny'] = $zodpovedny['zodpovedny'];
                break;
        }
                
        $sql = "SELECT uziv_jmeno as uzivatel FROM Zamestnanci WHERE id_zam = ?;";
        $params = [$uziv];
        $result = sqlsrv_query($conn, $sql, $params);
        if ($result === FALSE)
            die(print_r(sqlsrv_errors(), true));    
        $uzivatel = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['uzivatel'];
    ?>
    <div class="docFooter">
        <p>Zodpovědná osoba: <?= $zodpovedny['zodpovedny'] ?></p>
        <p><?= __FILE__ ?></p>
        <p>Vytištěno: <?= date('d.m.Y H:i:s') ?> uživatelem: <?= $uzivatel ?></p>
    </div>
</body>
<style>
    body {
        background: #fff;
    }
    <?php if($_POST['subTisk'] != "Tisk"): ?>
        table{
            margin: auto;
            width: 21cm;
        }
        .docFooter{
            margin: auto;
            width: 21cm;
        }
    <?php endif; ?>
    table {
        font-size: 9pt;
        border-collapse: collapse;
        border: 2px solid black;
        text-align: center;
    }
    td {
        padding: 2px;
        border: 1px solid #999;
        line-height: 1;
    }
    th {
        padding: 1px;
        border: 1px solid #666666;
        text-align: center;
        background: #C0C0C0;
        color: #000000;
    }
    .docFooter p{
        font-weight: bold;
        font-size: 9pt;
        margin: 1px 0 0 0;
    }
    input {
        width: 100%;
        border: none;
        text-align: center;
        font-size: inherit;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }
    textarea {
        width: 100%;
        border: none;
        font-size: inherit;
        padding: 0;
        margin: 0;
        resize: none;
        text-align: center;
        box-sizing: border-box;
    }
    <?php
        if ($typ_stroje == '3'){
            echo "#spec_nove_body { display: table-row-group; }";
            echo "#nast_nove_body { display: table-row-group; }";
            echo "#spec_stare_body { display: none; }";
            echo "#nast_stare_body { display: none; }";
        }
        else{
            echo "#spec_nove_body { display: none; }";
            echo "#nast_nove_body { display: none; }";
            echo "#spec_stare_body { display: table-row-group; }";
            echo "#nast_stare_body { display: table-row-group; }";
        }
    ?>
    
    @page {
        size: A4;
        margin: 1cm 0.5cm;
    }
    @media print{
        body{
            margin: 1cm 0.5cm;
        }
    }
</style>
<script>
    window.onload = function() {
        <?php if($_POST['subTisk'] == "Tisk"): ?>
            window.print();
        <?php endif; ?>
    }
</script>
</html>