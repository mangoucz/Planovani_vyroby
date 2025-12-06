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
        die(printr(sqlsrv_errors(), true));

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

    // nastavení hodnot pro ozubená kola která mají neměnný počet zubů
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

    $sql = "SELECT 
                LEFT(st.nazev, 1) as nazev_L, TRY_CAST(SUBSTRING(st.nazev, 2, LEN(st.nazev)) AS INT) as nazev_p, st.id_typ, s.titr, s.titr_skup, s.c_spec, n.doba, n.konec, 
                ss.hnaci_motor, ss.kotouc1, ss.kotouc2, ss.z21, ss.z22, ss.z23, ss.z24, 
                s.korekce + 1 as korekce, s.cerpadlo, s.pocet_mist,
                id_typ AS typ_stroje
            FROM Stroje AS st
            JOIN Naviny AS n ON st.id_stroj = n.id_stroj
            JOIN Specifikace AS s ON n.id_spec = s.id_spec
            JOIN Spec_stare AS ss ON s.id_spec = ss.id_spec
            WHERE n.zacatek <= GETDATE()-50 AND n.konec > GETDATE()-50 AND n.stav_stroje = 1

            UNION ALL

            SELECT 
                LEFT(st.nazev, 1) as nazev_L, TRY_CAST(SUBSTRING(st.nazev, 2, LEN(st.nazev)) AS INT) as nazev_p, st.id_typ, s.titr, s.titr_skup, s.c_spec, n.doba, n.konec, 
                sb.hnaci_motor, sb.kotouc1, sb.kotouc2, sb.z21, sb.z22, sb.z23, sb.z24, 
                s.korekce + 1 as korekce, s.cerpadlo, s.pocet_mist,
                id_typ AS typ_stroje
            FROM Stroje AS st
            JOIN Naviny AS n ON st.id_stroj = n.id_stroj
            JOIN Specifikace AS s ON n.id_spec = s.id_spec
            JOIN Spec_barmag AS sb ON s.id_spec = sb.id_spec
            WHERE n.zacatek <= GETDATE()-50 AND n.konec > GETDATE()-50 AND n.stav_stroje = 1

            UNION ALL

            SELECT 
                LEFT(st.nazev, 1) as nazev_L, TRY_CAST(SUBSTRING(st.nazev, 2, LEN(st.nazev)) AS INT) as nazev_p, st.id_typ, s.titr, s.titr_skup, s.c_spec, n.doba, n.konec, 
                sn.vg2, sn.faktor, NULL AS kotouc2, NULL AS z21, NULL AS z22, NULL AS z23, NULL AS z24,
                s.korekce + 1 as korekce, s.cerpadlo, s.pocet_mist,
                id_typ AS typ_stroje
            FROM Stroje AS st
            JOIN Naviny AS n ON st.id_stroj = n.id_stroj
            JOIN Specifikace AS s ON n.id_spec = s.id_spec
            JOIN Spec_nove AS sn ON s.id_spec = sn.id_spec
            WHERE n.zacatek <= GETDATE()-50 AND n.konec > GETDATE()-50 AND n.stav_stroje = 1
            ORDER BY LEFT(st.nazev, 1), TRY_CAST(SUBSTRING(st.nazev, 2, LEN(st.nazev)) AS INT);";
    $result = sqlsrv_query($conn, $sql);
    if ($result === FALSE)
        die(print_r(sqlsrv_errors(), true));
    $stroje = [];
    while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        if($zaznam['typ_stroje'] == 1){
            //stare
            $hnacimotor = $zaznam['hnaci_motor'];
            $kotouc1 = $zaznam['kotouc1'];
            $kotouc2 = $zaznam['kotouc2'];
            $z21 = $zaznam['z21'];
            $z22 = $zaznam['z22'];
            $z23 = $zaznam['z23'];
            $z24 = $zaznam['z24'];
            $korekce = $zaznam['korekce'];
            $cerpadlo = $zaznam['cerpadlo'];
            $pocetmist = $zaznam['pocet_mist'];

            $spotreba = $hnacimotor * $kotouc1 / $kotouc2 * $z3 / $z4 * $z21 / $z22 * $z23 / $z24 * $z40 / $z41 * $z42 / $z43 * $z44 / $z45 * $z46 / $z47 * $korekce * $cerpadlo * 60 / 1000 * $pocetmist;
        }
        elseif($zaznam['typ_stroje'] == 2){
            //barmag
            $hnacimotor = $zaznam['hnaci_motor'];
            $kotouc1 = $zaznam['kotouc1'];
            $kotouc2 = $zaznam['kotouc2'];
            $z21 = $zaznam['z21'];
            $z22 = $zaznam['z22'];
            $z23 = $zaznam['z23'];
            $z24 = $zaznam['z24'];
            $korekce = $zaznam['korekce'];
            $cerpadlo = $zaznam['cerpadlo'];
            $pocetmist = $zaznam['pocet_mist'];

            $spotreba = $hnacimotor * $kotouc1 / $kotouc2 * $z3 / $z4 * $z21 / $z22 * $z23 / $z24 * $z40 / $z41 * $z42 / $z43 * $z44 / $z45 * $z46 / $z47 * $korekce * $cerpadlo * 60 / 1000 * $pocetmist;
        }
        else{
            //nove
            $vg2 = $zaznam['hnaci_motor'];
            $titr = $zaznam['titr'];
            $korekce = $zaznam['korekce'];
            $pocetmist = $zaznam['pocet_mist'];
            $faktor = $zaznam['kotouc1'];

            $spotreba = $vg2 * 60 / 10000 * $titr / 1000 / $faktor * $korekce * $pocetmist;
        }
        $rychlost_hod = $spotreba * 0.08674 * 0.97;
        $stroje[] = [
            'nazev' => $zaznam['nazev_L'] . $zaznam['nazev_p'],
            'skup_stroju' => $zaznam['typ_stroje'],
            'skup_titru' => $zaznam['titr_skup'],
            'specifikace' => $zaznam['c_spec'],
            'doba' => $zaznam['doba'],
            'odtah' => $zaznam['konec'],
            'spotreba' => round($spotreba, 2),
            'rychlost_hod' => round($rychlost_hod, 1),
            'rychlost_den' => round($rychlost_hod * 24, 1)
        ];
    }
    //stare = hnacimotor * kotouc1 / kotouc2 * z3 / z4 * z21 / z22 * z23 / z24 * z40 / z41 * z42 / z43 * z44 / z45 * z46 / z47*(1+dbl_korekce) * cerpadlo * 60 / 1000 * pocetmist;
    //barmag = hnacimotor * kotouc1 / kotouc2 * z3 / z4 * z21 / z22 * z23 / z24 * z40 / z41 * z42 / z43 * z44 / z45 * z46 / z47*(1+dbl_korekce) * cerpadlo * 60 / 1000 * pocetmist;
    //nove = vg2 * 60 / 10000 * titr / 1000 /faktor*(1+korekce) * pocetmist;
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
            <?php foreach ($stroje as $stroj): ?>
                <tr>
                    <td data-label="Název stroje"><?= $stroj['nazev']; ?></td>
                    <td data-label="Skup. strojů"><?= $stroj['skup_stroju']; ?></td>
                    <td data-label="Skup. titrů"><?= $stroj['skup_titru']; ?></td>
                    <td data-label="Specifikace"><?= $stroj['specifikace']; ?></td>
                    <td data-label="Spotřeba viskózy [l/hod]"><?= $stroj['spotreba']; ?></td>
                    <td data-label="Rychlost výr. [kg/hod]"><?= $stroj['rychlost_hod']; ?></td>
                    <td data-label="Rychlost výr. [kg/den]"><?= $stroj['rychlost_den']; ?></td>
                    <td data-label="Doba návinu"><?= is_object($stroj['doba']) ? $stroj['doba']->format('H:i') : $stroj['doba']; ?></td>
                    <td data-label="Příští odtah"><?= is_object($stroj['odtah']) ? $stroj['odtah']->format('d.m.Y H:i') : $stroj['odtah']; ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="text-align: center; font-weight: bold; background-color: #f1f1f1;">
                <td>Celkem:</td>
                <td><?= count($stroje); ?> strojů</td>
                <td>-</td>
                <td>-</td>
                <td>
                    <?php
                        $sum_spotreba = array_sum(array_column($stroje, 'spotreba'));
                        echo $sum_spotreba . ' l/hod <br> ' . round($sum_spotreba/1000, 2) . ' m3/hod';
                    ?>
                </td>
                <td>
                    <?php
                        $sum_rychlost_hod = array_sum(array_column($stroje, 'rychlost_hod'));
                        echo $sum_rychlost_hod . ' kg/hod';
                    ?>
                </td>
                <td>
                    <?php
                        $sum_rychlost_den = array_sum(array_column($stroje, 'rychlost_den'));
                        echo $sum_rychlost_den . ' kg/den';
                    ?>
                </td>
                <td>-</td>
                <td>-</td>
            </tr>
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