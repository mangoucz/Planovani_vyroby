<?php
    function GenCisSpec($conn) : int {
        $c_spec = (int)date("y") * 1000;

        $sql = "SELECT MAX(c_spec) AS c_spec FROM Specifikace WHERE c_spec >= ? AND c_spec < ?";
        $params = [$c_spec, $c_spec + 1000];
        $result = sqlsrv_query($conn, $sql, $params);
        if ($result === FALSE)
            die(print_r(sqlsrv_errors(), true));
        $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['c_spec'];         
        sqlsrv_free_stmt($result);
            
        if ($zaznam !== null) 
            return $c_spec = $zaznam + 1;   
        return $c_spec + 1;
    }
    session_start();
    if (isset($_SESSION['uziv']))
        $uziv = $_SESSION['uziv'];
    else{
        header("Location: login.php");
        exit();    
    }
    require_once 'server.php';

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['subEdit'])){
            $id_spec = $_POST['id'];
            $typ_stroje = $_POST['typ_stroje'];
            switch ($typ_stroje) {
                case '1':
                    $table_name = "Spec_stare";
                    break;
                case '2':
                    $table_name = "Spec_barmag";
                    break;
                case '3':
                    $table_name = "Spec_nove";
                    break;
                default:
                    break;
            }
            
            $sql = "SELECT * FROM Specifikace AS s RIGHT JOIN $table_name AS st ON s.id_spec = st.id_spec WHERE s.id_spec = ?;";
            $params = [$id_spec];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($result);
        }
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

    $sql = "SELECT * FROM Typ_stroje;";
    $result = sqlsrv_query($conn, $sql);
    if ($result === FALSE)
        die(print_r(sqlsrv_errors(), true));
    $typy_stroju = [];
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $typy_stroju[$row['id_typ']] = $row['nazev'];
    }
    $id_stroje = isset($_GET['stroj']) ? $_GET['stroj'] : (isset($typ_stroje) ? $typ_stroje : 0);
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
                    <p><?= $jmeno; ?></p>
                    <p style="font-size: 12px; margin-left: 1px;"><?= $funkce; ?></p>
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
    <form action="" method="post" id="form">
        <div class="setting">
            <select name="id_typ_stroje" id="selectStroj">
                <?php
                    foreach ($typy_stroju as $id => $typ): ?>
                        <option value="<?= $id ?>" <?= ($id == $id_stroje) ? 'selected' : '' ?>><?= $typ ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <h2>Výpočet specifikace spřádacího stroje</h2>
        <div class="table1" id="specifikace">
            <h3>Specifikace</h3>
            <div class="radek">
                <label for="c_spec">Číslo specifikace</label>
                <input type="number" id="c_spec" name="c_spec" required value="<?= isset($zaznam['c_spec']) ? $zaznam['c_spec'] : GenCisSpec($conn); ?>">
            </div>
            <div class="radek">
                <label for="titr">Titr</label>
                <input type="number" id="titr" name="titr" required value="<?= $zaznam['titr'] ?? '' ?>">
                <span class="jednotka">g/10 000 m</span>
            </div>
            <div class="radek">
                <label for="titr_skup">Skupina titrů</label>
                <input type="number" id="titr_skup" name="titr_skup" required value="<?= $zaznam['titr_skup'] ?? '' ?>">
                <span class="jednotka">g/10 000 m</span>
            </div>
        </div>
        <div class="double barmag" id="kotouce_div">
            <div class="table1">
                <h3>Kotouče</h3>
                <div class="radek">
                    <label for="hnaci_motor">Hnací motor</label>
                    <input type="number" id="hnaci_motor" name="hnaci_motor" value="<?= $zaznam['hnaci_motor'] ?? '' ?>">
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="zs1">Kotouč ZS1</label>
                    <input type="number" id="kotouc1" name="kotouc1" value="<?= $zaznam['kotouc1'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="zs2">Kotouč ZS2</label>
                    <input type="number" id="kotouc2" name="kotouc2" value="<?= $zaznam['kotouc2'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="kotouc3">Kotouč 3</label>
                    <input type="number" id="kotouc3" name="kotouc3" value="<?= $zaznam['kotouc3'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="kotouc4">Kotouč 4</label>
                    <input type="number" id="kotouc4" name="kotouc4" value="<?= $zaznam['kotouc4'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
            </div>
            <div class="table2">
                <div class="radek">
                    <label for="npohon">n pohon</label>
                    <input type="number" id="npohon" name="npohon" step="0.01" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="nA">nA</label>
                    <input type="number" id="nA" name="nA" step="0.01" disabled><span class="jednotka">ot/min</span>
                </div>
            </div>
        </div>
        <div class="double nove" id="dlouzeni_div_nove">
            <div class="table1">
                <h3>Dloužení</h3>
                <div class="radek">
                    <label for="SG1-G2">SG1-G2</label>
                    <input type="number" id="SG1-G2" name="sg1_g2" step="0.01" value="<?= (float)$zaznam['sg1_g2'] ?? '' ?>">
                    <span class="jednotka">%</span>
                </div>
                <div class="radek">
                    <label for="SG2-W">SG2-W</label>
                    <input type="number" id="SG2-W" name="sg2_w" step="0.01" value="<?= (float)$zaznam['sg2_w'] ?? '' ?>">
                    <span class="jednotka">%</span>
                </div>
                <div class="radek">
                    <label for="SW-T">SW-T</label>
                    <input type="number" id="SW-T" name="sw_t" step="0.01" value="<?= (float)$zaznam['sw_t'] ?? '' ?>">
                    <span class="jednotka">%</span>
                </div>
                <div class="radek">
                    <label for="korekce_nove">Korekce</label>
                    <input type="number" id="korekce_nove" name="korekce_nove" step="0.01" value="<?= (float)$zaznam['korekce'] ?? '' ?>">
                    <span class="jednotka">%</span>
                </div>
            </div>
            <div class="table2">
                <div class="radek">
                    <label for="Sges">Sges</label>
                    <input type="number" id="Sges" name="Sges" step="0.01" disabled>
                    <span class="jednotka">%</span>
                </div>
            </div>
        </div>
        <div class="double" id="galety_div">
            <div class="table1">
                <h3>Galety</h3>
                <div class="radek">
                    <label for="galety">Galety Ø</label>
                    <input type="number" id="galety" name="galety" value="<?= $zaznam['galety'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
                <div class="radek nove">
                    <label for="vg2">vG2</label>
                    <input type="number" id="vg2" name="vg2" step="0.01" <?= $id_spec ?? 0 == 3 ? 'required' : '' ?> value="<?= (float)$zaznam['vg2'] ?? '' ?>">
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek nove">
                    <label for="z1g1">Horní (G1) Z1</label>
                    <input type="number" id="z1g1" name="z1g1" value="<?= $zaznam['z1g1'] ?? '' ?>">
                </div>
                <div class="radek nove">
                    <label for="z2g1">Horní (G1) Z2</label>
                    <input type="number" id="z2g1" name="z2g1" value="<?= $zaznam['z2g1'] ?? '' ?>">
                </div>
                <div class="radek nove">
                    <label for="z1g2">Dolní (G2) Z1</label>
                    <input type="number" id="z1g2" name="z1g2" value="<?= $zaznam['z1g2'] ?? '' ?>">
                </div>
                <div class="radek nove">
                    <label for="z2g2">Dolní (G2) Z2</label>
                    <input type="number" id="z2g2" name="z2g2" value="<?= $zaznam['z2g2'] ?? '' ?>">
                </div>
                <div class="barmag">
                    <div class="radek">
                        <label for="Z13">Z13</label>
                        <input type="number" id="Z13" name="z13" value="<?= $zaznam['z13'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z14">Z14</label>
                        <input type="number" id="Z14" name="z14" value="<?= $zaznam['z14'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z15">Z15</label>
                        <input type="number" id="Z15" name="z15" value="<?= $zaznam['z15'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z16">Z16</label>
                        <input type="number" id="Z16" name="z16" value="<?= $zaznam['z16'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z30">Z30</label>
                        <input type="number" id="Z30" name="z30" value="<?= $zaznam['z30'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z32">Z32</label>
                        <input type="number" id="Z32" name="z32" value="<?= $zaznam['z32'] ?? '' ?>">
                    </div>
                </div>
            </div>
            <div class="table2">
                <div class="radek">
                    <label for="nG2">nG2</label>
                    <input type="number" id="nG2" name="nG2" step="0.01" disabled>
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="nG1">nG1</label>
                    <input type="number" id="nG1" name="nG1" step="0.01" disabled>
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek barmag">
                    <label for="vG2">vG2</label>
                    <input type="number" id="vG2" name="vG2" step="0.01" disabled>
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="vG1">vG1</label>
                    <input type="number" id="vG1" name="vG1" step="0.01" disabled>
                    <span class="jednotka">ot/min</span>
                </div>
            </div>
        </div>
        <div class="double" id="praci_valce_div">
            <div class="table1">
                <h3>Prací válce</h3>
                <div class="radek">
                    <label for="praci_valce">Prací válce Ø</label>
                    <input type="number" id="praci_valce" name="praci_valce" value="<?= $zaznam['praci_valce'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
                <div class="radek nove">
                    <label for="z1w">Prací válce (W) Z1</label>
                    <input type="number" id="z1w" name="z1w" value="<?= $zaznam['z1w'] ?? '' ?>">
                </div>
                <div class="radek nove">
                    <label for="z2w">Prací válce (W) Z2</label>
                    <input type="number" id="z2w" name="z2w" value="<?= $zaznam['z2w'] ?? '' ?>">
                </div>
                <div class="barmag">
                    <div class="radek">
                        <label for="Z9">Z9</label>
                        <input type="number" id="Z9" name="z9" value="<?= $zaznam['z9'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z10">Z10</label>
                        <input type="number" id="Z10" name="z10" value="<?= $zaznam['z10'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z11">Z11</label>
                        <input type="number" id="Z11" name="z11" value="<?= $zaznam['z11'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z12">Z12</label>
                        <input type="number" id="Z12" name="z12" value="<?= $zaznam['z12'] ?? '' ?>">
                    </div>
                </div>
            </div>
            <div class="table2">
                <div class="radek">
                    <label for="nW">nW</label>
                    <input type="number" id="nW" name="nW" step="0.01" disabled>
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="vW">vW</label>
                    <input type="number" id="vW" name="vW" step="0.01" disabled>
                    <span class="jednotka">m/min</span>
                </div>
            </div>
        </div>
        <div class="double" id="susici_valce_div">
            <div class="table1">
                <h3>Sušící válec</h3>
                <div class="radek">
                    <label for="susici_valec">Sušicí válec Ø</label>
                    <input type="number" id="susici_valec" name="susici_valec" value="<?= $zaznam['susici_valec'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
                <div class="radek nove">
                    <label for="z1t">Sušicí válec (T) Z1</label>
                    <input type="number" id="z1t" name="z1t" value="<?= $zaznam['z1t'] ?? '' ?>">
                </div>
                <div class="radek nove">
                    <label for="z2t">Sušicí válec (T) Z2</label>
                    <input type="number" id="z2t" name="z2t" value="<?= $zaznam['z2t'] ?? '' ?>">
                </div>  
                <div class="barmag">
                    <div class="radek">
                        <label for="Z17">Z17</label>
                        <input type="number" id="Z17" name="z17" value="<?= $zaznam['z17'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z18">Z18</label>
                        <input type="number" id="Z18" name="z18" value="<?= $zaznam['z18'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z19">Z19</label>
                        <input type="number" id="Z19" name="z19" value="<?= $zaznam['z19'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z20">Z20</label>
                        <input type="number" id="Z20" name="z20" value="<?= $zaznam['z20'] ?? '' ?>">
                    </div>
                </div>
            </div>
            <div class="table2">
                <div class="radek">
                    <label for="nWT">nWT</label>
                    <input type="number" id="nWT" name="nWT" step="0.01" disabled>
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="vWT">vWT</label>
                    <input type="number" id="vWT" name="vWT" step="0.01" disabled>
                    <span class="jednotka">m/min</span>
                </div>
            </div>
        </div>
        <div class="double barmag" id="navijeni_div">
            <div class="table1">
                <h3>Navíjení</h3>
                <div class="radek">
                    <label for="navijeci_valec">Navíjecí válec Ø</label>
                    <input type="number" id="navijeci_valec" name="navijeci_valec" value="<?= $zaznam['navijeci_valec'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
                <div class="radek stare">
                    <label for="dlouzeni">Dloužení</label>
                    <input type="number" id="dlouzeni" step="0.01" name="dlouzeni" value="<?= (float)$zaznam['dlouzeni'] ?? '' ?>">
                    <span class="jednotka">%</span>
                </div>
            </div>
            <div class="table2 stare">
                <div class="radek">
                    <label for="v_navijeni">v navíjení</label>
                    <input type="number" id="v_navijeni" name="v_navijeni" step="0.01" disabled>
                    <span class="jednotka">m/min</span>
                </div>
                <div class="radek">
                    <label for="n_navijeni">n navíjení</label>
                    <input type="number" id="n_navijeni" name="n_navijeni" step="0.01" disabled>
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="1_pohon">na 1 pohon</label>
                    <input type="number" id="1_pohon" name="1_pohon" step="0.01" disabled>
                    <span class="jednotka">ot/min</span>
                </div>
            </div>
        </div>
        <div class="double" id="cerpadlo_div">
            <div class="table1">
                <h3>Spřádací čerpadlo</h3>
                <div class="radek">
                    <label for="cerpadlo">Spřádací čerpadlo</label>
                    <input type="number" id="cerpadlo" name="cerpadlo" value="<?= (float)$zaznam['cerpadlo'] ?? '' ?>">
                    <span class="jednotka">cm³/U</span>
                </div>
                <div class="radek">
                    <label for="pocet_sprad_mist">Počet spřádacích míst</label>
                    <input type="number" id="pocet_sprad_mist" name="pocet_mist" value="<?= $zaznam['pocet_mist'] ?? '' ?>">
                </div>
                <div class="radek barmag">
                    <label for="korekce_barmag">Korekce</label>
                    <input type="number" id="korekce_barmag" name="korekce_barmag" step="0.01" value="<?= (float)$zaznam['korekce'] ?? '' ?>">
                    <span class="jednotka">%</span>
                </div>
                <div class="radek nove">
                    <label for="faktor">Faktor Viskóza/Produkt</label>
                    <input type="number" id="faktor" name="faktor" step="0.0001" value="<?= (float)$zaznam['faktor'] ?? '' ?>">
                </div>
                <div class="radek nove">
                    <label for="z1sp">Čerpadla (Sp) Z1</label>
                    <input type="number" id="z1sp" name="z1sp" value="<?= $zaznam['z1sp'] ?? '' ?>">
                </div>
                <div class="radek nove">
                    <label for="z2sp">Čerpadla (Sp) Z2</label>
                    <input type="number" id="z2sp" name="z2sp" value="<?= $zaznam['z2sp'] ?? '' ?>">
                </div>  
                <div class="barmag">
                    <div class="radek">
                        <label for="Z21">Z21</label>
                        <input type="number" id="Z21" name="z21" value="<?= $zaznam['z21'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z22">Z22</label>
                        <input type="number" id="Z22" name="z22" value="<?= $zaznam['z22'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z23">Z23</label>
                        <input type="number" id="Z23" name="z23" value="<?= $zaznam['z23'] ?? '' ?>">
                    </div>
                    <div class="radek">
                        <label for="Z24">Z24</label>
                        <input type="number" id="Z24" name="z24" value="<?= $zaznam['z24'] ?? '' ?>">
                    </div>
                </div>
            </div>
            <div class="table2">
                <div class="radek">
                    <label for="nSp">nSp</label>
                    <input type="number" id="nSp" name="nSp" step="0.01" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="spotr_misto">Spotř. viskózy - místo</label>
                    <input type="number" id="spotr_misto" name="spotr_misto" step="0.01" disabled><span class="jednotka">l/hod</span>
                </div>
                <div class="radek">
                    <label for="spotr_stroj">Spotř. viskózy - stroj</label>
                    <input type="number" id="spotr_stroj" name="spotr_stroj" step="0.01" disabled><span class="jednotka">l/hod</span>
                </div>
            </div>
        </div>
        <div class="table1 nove" id="Produkce">
            <h3>Produkce</h3>
            <div class="radek">
                <label for="produkce_1_misto">Produkce 1 místo</label>
                <input type="number" id="produkce_1_misto" name="produkce_1_misto" disabled>
                <span class="jednotka">kg/h</span>
            </div>
            <div class="radek">
                <label for="produkce_stroj">Produkce stroj</label>
                <input type="number" id="produkce_stroj" name="produkce_stroj" disabled>
                <span class="jednotka">kg/h</span>
            </div>
        </div>
        <div class="double stare" id="ukladani_div">     
            <div class="table1">
                <h3>Ukládání</h3>
                <div class="radek">
                    <label for="motor">Motor</label>
                    <input type="number" id="motor" name="ukladani_motor" value="<?= $zaznam['ukladani_motor'] ?? '' ?>">
                </div>
                <div class="radek stare">
                    <label for="rs3">Řemenice M. (RS3)</label>
                    <input type="number" id="rs3" name="remenice_m" value="<?= $zaznam['remenice_m'] ?? '' ?>">
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek stare">
                    <label for="rs4">Řemenice G. (RS4)</label>
                    <input type="number" id="rs4" name="remenice_g" value="<?= $zaznam['remenice_g'] ?? '' ?>">
                    <span class="jednotka">mm</span>
                </div>
            </div>
            <div class="table2 stare">
                <div class="radek">
                    <label for="ipohon">i pohon 1:30</label>
                    <input type="number" id="ipohon" name="ipohon" step="0.01" disabled>
                </div>
                <div class="radek">
                    <label for="dvojite_zdvihy">Dvojité zdvihy</label>
                    <input type="number" id="dvojite_zdvihy" name="dvojite_zdvihy" step="0.01" disabled>
                    <span class="jednotka">DH/min</span>
                </div>
            </div>
        </div>
        <div class="table1 barmag" id="dlouzeni_div_stare">
            <h3>Dloužení</h3>
            <div class="radek">
                <label for="SG1-G2">SG1-G2</label>
                <input type="number" id="SG1-G2" name="SG1-G2" step="0.01" disabled><span class="jednotka">%</span>
            </div>
            <div class="radek">
                <label for="SG2-W">SG2-W</label>
                <input type="number" id="SG2-W" name="SG2-W" step="0.01" disabled><span class="jednotka">%</span>
            </div>
            <div class="radek">
                <label for="SW-T">SW-T</label>
                <input type="number" id="SW-T" name="SW-T" step="0.01" disabled><span class="jednotka">%</span>
            </div>
            <div class="radek">
                <label for="Sges">Sges</label>
                <input type="number" id="Sges" name="Sges" step="0.01" disabled><span class="jednotka">%</span>
            </div>
        </div>
        <div class="table1" id="poznamky_div">
            <h3>Poznámky</h3>
            <textarea name="poznamka" rows="10"><?= $zaznam['poznamka'] ?? '' ?></textarea>
        </div>
        <div class="submit-container">
            <input type="button" class="add" id="odeslat" value="Uložit specifikaci" name="subUloz" style="font-size: 16px;">
            <input type="hidden" name="id_spec" value="<?= $zaznam['id_spec'] ?? '' ?>">
        </div>
        <div class="modal" id="modalOdeslano">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="closeBtn" class="close">&times;</span>
                    <h2>Specifikace č. </h2>
                </div>
                <div class="modal-body">
                    <h3>Specifikace byla úspěšně odeslána!</h3>
                    <p>Chcete ji vytisknout?</p>
                </div>
                <div class="modal-footer">
                    <form action="print_form.php" method="post" target="printFrame">
                        <input type="hidden" name="id" value="">
                        <input type="submit" name="subTisk" value="Tisk" id="printBtn" class="defButt print"></button>                    
                    </form>
                    <iframe id="frame" name="printFrame" style="display: none;"></iframe>
                    <button id="closeBtn" class="defButt">Zavřít</button>
                </div>
            </div>
        </div>
    </form>
    <style>
        h2{
            text-align: center;
            margin-top: 20px;
            color: #2c3e50;
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
        textarea {
            width: 100%;
            border: 1px solid #aaa;
            border-radius: 4px;
            resize: none;
        }
        .setting {
            max-width: 20vw;
        }
        .table1, .table2 {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 1vw;
            margin: 20px auto;
            background: #fff;
            width: 80vw;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
            display: flex;
            flex-direction: column;
        }
        .table2 {
            justify-content: center;
        }
        .table1 h3 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 1.2rem;
            color: #2c3e50;
            border-bottom: 1px solid #ddd;
            padding-bottom: 6px;
        }
        .radek {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .radek label {
            flex: 0 0 220px;
            font-weight: 500;
        }
        .radek input {
            padding: 6px;
            border-radius: 4px;
            width: 70%;
        }
        .jednotka {
            margin-left: 8px;
            color: #555;
        }
        .double{
            display: flex; 
            gap: 1vw; 
            flex-wrap: nowrap; 
            width: 82vw; 
            margin: auto;
        }
        .double input{
            width: 15vw;
        }
        button, .add, #ulozit{
            color: #FFFFFF;
            border: none;
            border-radius: 50px;
            padding: 10px 20px; 
            font-size: 20px; 
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
            transition: all 0.3s ease;
        }
        .add {
            background: #39B54A;
        }
        .add:hover {
            background: #34A343;
        }
        .add:active {
            background: #2E8E3B;
        }
        .submit-container {
            display: flex;
            justify-content: center; 
            margin: 20px 0 100px 0; 
        }
    </style>
</body>
</html>