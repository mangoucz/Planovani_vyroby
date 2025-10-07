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
            <li><a href="specifikace.php" class="active">Specifikace</a></li>
            <li><a href="stroje.php">Stroje</a></li>
            <?php if($admin): ?><li><a href="administrace.php">Administrace</a></li><?php endif; ?>
        </ul>
    </div>
    <form action="" method="post" id="spec_form">
        <div class="setting">
            <select name="stroj">
                <option value="1">Staré stroje</option>
                <option value="2">Staré stroje + Barmag</option>
                <option value="3">Nové stroje</option>
            </select>
        </div>
        <h2>Výpočet specifikace spřádacího stroje</h2>
        <div class="table" id="specifikace">
            <h3>Specifikace</h3>
            <div class="radek">
                <label for="c_spec">Číslo specifikace</label>
                <input type="text" id="c_spec" name="c_spec" required>
            </div>
            <div class="radek">
                <label for="titr">Titr</label>
                <input type="text" id="titr" name="titr" required>
                <span class="jednotka">g/10 000 m</span>
            </div>
            <div class="radek">
                    <label for="titr_skup">Skupina titrů</label>
                    <input type="text" id="titr_skup" name="titr_skup" required>
                    <span class="jednotka">g/10 000 m</span>
                </div>
            </div>
        </div>
        <div class="double" id="kotouče">
            <div class="table">
                <h3>Kotouče</h3>
                <div class="radek">
                    <label for="hnaci_motor">Hnací motor</label>
                    <input type="text" id="hnaci_motor" name="hnaci_motor" required>
                    <span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="zs1">Kotouč ZS1</label>
                    <input type="text" id="zs1" name="zs1" required><span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="zs2">Kotouč ZS2</label>
                    <input type="text" id="zs2" name="zs2" required><span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="kotouc3">Kotouč 3</label>
                    <input type="text" id="kotouc3" name="kotouc3" required><span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="kotouc4">Kotouč 4</label>
                    <input type="text" id="kotouc4" name="kotouc4" required><span class="jednotka">mm</span>
                </div>
            </div>
            <div class="table">
                <div class="radek">
                    <label for="npohon">n pohon</label>
                    <input type="text" id="npohon" name="npohon" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="nA">nA</label>
                    <input type="text" id="nA" name="nA" disabled><span class="jednotka">ot/min</span>
                </div>
            </div>
        </div>
        <div class="double" id="galety">
            <div class="table">
                <h3>Galety</h3>
                <div class="radek">
                    <label for="galety">Galety Ø</label>
                    <input type="text" id="galety" name="galety" required><span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="Z13">Z13</label>
                    <input type="text" id="Z13" name="Z13" required>
                </div>
                <div class="radek">
                    <label for="Z14">Z14</label>
                    <input type="text" id="Z14" name="Z14" required>
                </div>
                <div class="radek">
                    <label for="Z15">Z15</label>
                    <input type="text" id="Z15" name="Z15" required>
                </div>
                <div class="radek">
                    <label for="Z16">Z16</label>
                    <input type="text" id="Z16" name="Z16" required>
                </div>
                <div class="radek">
                    <label for="Z30">Z30</label>
                    <input type="text" id="Z30" name="Z30" required>
                </div>
                <div class="radek">
                    <label for="Z32">Z32</label>
                    <input type="text" id="Z32" name="Z32" required>
                </div>
            </div>
            <div class="table">
                <div class="radek">
                    <label for="nG2">nG2</label>
                    <input type="text" id="nG2" name="nG2" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="nG1">nG1</label>
                    <input type="text" id="nG1" name="nG1" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="vG2">vG2</label>
                    <input type="text" id="vG2" name="vG2" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="vG1">vG1</label>
                    <input type="text" id="vG1" name="vG1" disabled><span class="jednotka">ot/min</span>
                </div>
            </div>
        </div>
        <div class="double" id="praci_valce">
            <div class="table">
                <h3>Prací válce</h3>
                <div class="radek">
                    <label for="praci_valce">Prací válce Ø</label>
                    <input type="text" id="praci_valce" name="praci_valce" required><span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="Z9">Z9</label>
                    <input type="text" id="Z9" name="Z9" required>
                </div>
                <div class="radek">
                    <label for="Z10">Z10</label>
                    <input type="text" id="Z10" name="Z10" required>
                </div>
                <div class="radek">
                    <label for="Z11">Z11</label>
                    <input type="text" id="Z11" name="Z11" required>
                </div>
                <div class="radek">
                    <label for="Z12">Z12</label>
                    <input type="text" id="Z12" name="Z12" required>
                </div>
            </div>
            <div class="table">
                <div class="radek">
                    <label for="nW">nW</label>
                    <input type="text" id="nW" name="nW" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="vW">vW</label>
                    <input type="text" id="vW" name="vW" disabled><span class="jednotka">m/min</span>
                </div>
            </div>
        </div>
        <div class="double" id="susici_valce">
            <div class="table">
                <h3>Sušící válce</h3>
                <div class="radek">
                    <label for="susici_valec">Sušicí válec Ø</label>
                    <input type="text" id="susici_valec" name="susici_valec" required><span class="jednotka">mm</span>
                </div>
                <div class="radek">
                    <label for="Z17">Z17</label>
                    <input type="text" id="Z17" name="Z17" required>
                </div>
                <div class="radek">
                    <label for="Z18">Z18</label>
                    <input type="text" id="Z18" name="Z18" required>
                </div>
                <div class="radek">
                    <label for="Z19">Z19</label>
                    <input type="text" id="Z19" name="Z19" required>
                </div>
                <div class="radek">
                    <label for="Z20">Z20</label>
                    <input type="text" id="Z20" name="Z20" required>
                </div>
            </div>
            <div class="table">
                <div class="radek">
                    <label for="nWT">nWT</label>
                    <input type="text" id="nWT" name="nWT" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="vWT">vWT</label>
                    <input type="text" id="vWT" name="vWT" disabled><span class="jednotka">m/min</span>
                </div>
            </div>
        </div>
        <div class="double" id="navijeni">
            <div class="table">
                <h3>Navíjení</h3>
                <div class="radek">
                    <label for="navijeci_valec">Navíjecí válec Ø</label>
                    <input type="text" id="navijeci_valec" name="navijeci_valec" required><span class="jednotka">mm</span>
                </div>
                <div class="radek barmag">
                    <label for="dlouzeni">Dloužení</label>
                    <input type="text" id="dlouzeni" name="dlouzeni" required><span class="jednotka">%</span>
                </div>
            </div>
            <div class="table barmag">
                <div class="radek">
                    <label for="v_navijeni">v navíjení</label>
                    <input type="text" id="v_navijeni" name="v_navijeni" disabled><span class="jednotka">m/min</span>
                </div>
                <div class="radek">
                    <label for="n_navijeni">n navíjení</label>
                    <input type="text" id="n_navijeni" name="n_navijeni" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="1_pohon">na 1 pohon</label>
                    <input type="text" id="1_pohon" name="1_pohon" disabled><span class="jednotka">ot/min</span>
                </div>
            </div>
        </div>
        <div class="double" id="cerpadlo">
            <div class="table">
                <h3>Spřádací čerpadlo</h3>
                <div class="radek">
                    <label for="spradaci_cerpadlo">Spřádací čerpadlo</label>
                    <input type="text" id="spradaci_cerpadlo" name="spradaci_cerpadlo" required><span class="jednotka">cm³/U</span>
                </div>
                <div class="radek">
                    <label for="pocet_sprad_mist">Počet spřádacích míst</label>
                    <input type="text" id="pocet_sprad_mist" name="pocet_sprad_mist" required>
                </div>
                <div class="radek">
                    <label for="korekce">korekce</label>
                    <input type="text" id="korekce" name="korekce" required><span class="jednotka">%</span>
                </div>
                <div class="radek">
                    <label for="Z21">Z21</label>
                    <input type="text" id="Z21" name="Z21" required>
                </div>
                <div class="radek">
                    <label for="Z22">Z22</label>
                    <input type="text" id="Z22" name="Z22" required>
                </div>
                <div class="radek">
                    <label for="Z23">Z23</label>
                    <input type="text" id="Z23" name="Z23" required>
                </div>
                <div class="radek">
                    <label for="Z24">Z24</label>
                    <input type="text" id="Z24" name="Z24" required>
                </div>
            </div>
            <div class="table">
                <div class="radek">
                    <label for="nSp">nSp</label>
                    <input type="text" id="nSp" name="nSp" disabled><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="spotr_misto">Spotř. viskózy - místo</label>
                    <input type="text" id="spotr_misto" name="spotr_misto" disabled><span class="jednotka">l/hod</span>
                </div>
                <div class="radek">
                    <label for="spotr_stroj">Spotř. viskózy - stroj</label>
                    <input type="text" id="spotr_stroj" name="spotr_stroj" disabled><span class="jednotka">l/hod</span>
                </div>
            </div>
        </div>
        <div class="double barmag" id="ukladani">     
            <div class="table">
                <h3>Ukládání</h3>
                <div class="radek">
                    <label for="motor">Motor</label>
                    <input type="text" id="motor" name="motor" required>
                </div>
                <div class="radek">
                    <label for="rs3">Řemenice M. (RS3)</label>
                    <input type="text" id="rs3" name="rs3" required><span class="jednotka">ot/min</span>
                </div>
                <div class="radek">
                    <label for="rs4">Řemenice G. (RS4)</label>
                    <input type="text" id="rs4" name="rs4" required><span class="jednotka">mm</span>
                </div>
            </div>
            <div class="table">
                <div class="radek">
                    <label for="ipohon">i pohon 1:30</label>
                    <input type="text" id="ipohon" name="ipohon" disabled>
                </div>
                <div class="radek">
                    <label for="dvojite_zdvihy">Dvojité zdvihy</label>
                    <input type="text" id="dvojite_zdvihy" name="dvojite_zdvihy" disabled><span class="jednotka">DH/min</span>
                </div>
            </div>
        </div>
        <div class="table" id="dlouzeni">
            <h3>Dloužení</h3>
            <div class="radek">
                <label for="SG1-G2">SG1-G2</label>
                <input type="text" id="SG1-G2" name="SG1-G2" disabled><span class="jednotka">%</span>
            </div>
            <div class="radek">
                <label for="SG2-W">SG2-W</label>
                <input type="text" id="SG2-W" name="SG2-W" disabled><span class="jednotka">%</span>
            </div>
            <div class="radek">
                <label for="SW-T">SW-T</label>
                <input type="text" id="SW-T" name="SW-T" disabled><span class="jednotka">%</span>
            </div>
            <div class="radek">
                <label for="Sges">Sges</label>
                <input type="text" id="Sges" name="Sges" disabled><span class="jednotka">%</span>
            </div>
                
        </div>
        <div class="table">
            <h3>Poznámky</h3>
            <textarea name="poznamky" rows="10"></textarea>
        </div>
        <div class="submit-container">
            <input type="button" class="add" id="ulozit" value="Uložit specifikaci" name="subUloz" style="font-size: 16px;">
        </div>
    </form>
    <style>
        h2{
            text-align: center;
            margin-top: 20px;
            color: #2c3e50;
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
        .table {
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
            justify-content: center;
        }
        .table h3 {
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
            border: 1px solid #aaa;
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