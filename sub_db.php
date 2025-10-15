<?php
    function GenCisSpec($conn) : int {
        $c_spec = (int)date("y") * 100000;

        $sql = "SELECT MAX(c_spec) AS c_spec FROM Specifikace WHERE c_spec >= ? AND c_spec < ?";
        $params = [$c_spec, $c_spec + 100000];
        $result = sqlsrv_query($conn, $sql, $params);
        if ($result === FALSE)
            die(print_r(sqlsrv_errors(), true));
        $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);         
        sqlsrv_free_stmt($result);
            
        if ($zaznam['c_spec'] !== null) 
            return $c_spec = $zaznam['c_spec'] + 1;   
        return $c_spec + 1;
    }
    function inputCheck($input){
        return $input === "" ? $input = null : $input;
    }
    session_start();
 
    if (isset($_SESSION['uziv']))
        $uziv = $_SESSION['uziv'];
    else{
        header("Location: login.php");
        exit();    
    }
    require_once 'server.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sql = "";
        $params = [];

        //Specifikace
        $id_spec = inputCheck($_POST['id_spec']);
        $c_spec = !isset($id_pov) ? GenCisSpec($conn) : $_POST['c_spec'];
        $stroj_skup = $_POST['stroj_skup'];
        $titr = $_POST['titr'];
        $titr_skup = $_POST['titr_skup'];
        $poznamka = inputCheck($_POST['poznamka']);
        $galety = $_POST['galety'];
        $praci_valce = $_POST['praci_valce'];
        $susici_valce = $_POST['susici_valce'];
        $cerpadlo = $_POST['cerpadlo'];
        $pocet_mist = $_POST['pocet_mist'];
        $korekce = $_POST['korekce'];
        //Stare + Barmag
        $hnaci_motor = $_POST['hnaci_motor'];
        $kotouc1 = $_POST['kotouc1'];
        $kotouc2 = $_POST['kotouc2'];
        $kotouc3 = $_POST['kotouc3'];
        $kotouc4 = $_POST['kotouc4'];
        $navijeci_valec = $_POST['navijeci_valec'];
        $dlouzeni = $_POST['dlouzeni'];
        $ukladani_motor = $_POST['ukladani_motor'];
        $remenice_m = $_POST['remenice_m'];
        $remenice_g = $_POST['remenice_g'];
        $z9 = $_POST['z9'];
        $z10 = $_POST['z10'];
        $z11 = $_POST['z11'];
        $z12 = $_POST['z12'];
        $z13 = $_POST['z13'];
        $z14 = $_POST['z14'];
        $z15 = $_POST['z15'];
        $z16 = $_POST['z16'];
        $z17 = $_POST['z17'];
        $z18 = $_POST['z18'];
        $z19 = $_POST['z19'];
        $z20 = $_POST['z20'];
        $z21 = $_POST['z21'];
        $z22 = $_POST['z22'];
        $z23 = $_POST['z23'];
        $z24 = $_POST['z24'];
        $z30 = $_POST['z30'];
        $z32 = $_POST['z30'];
        //Nove
        $faktor = $_POST['faktor'];
        $sg1_g2 = $_POST['sg1_g2'];
        $sg2_w = $_POST['sg2_w'];
        $sw_t = $_POST['sw_t'];
        $vg2 = $_POST['vg2'];
        $z1g1 = $_POST['z1g1'];
        $z2g1 = $_POST['z2g1'];
        $z1g2 = $_POST['z1g2'];
        $z2g2 = $_POST['z2g2'];
        $z1w = $_POST['z1w'];
        $z2w = $_POST['z2w'];
        $z1t = $_POST['z1t'];
        $z2t = $_POST['z2t'];
        $z1sp = $_POST['z1sp'];
        $z2sp = $_POST['z2sp'];

        //EDIT
        if ($id_pov != null) {

        }//INSERT
        else{
            $sql = "INSERT INTO Specifikace(c_spec, stroj_skup, titr, titr_skup, poznamka, vytvoreno, id_zam, galety, praci_valce, susici_valce, cerpadlo, pocet_mist, korekce) 
                    VALUES (?, ?, ?, ?, ?, GETDATE(), ?, ?, ?, ?, ?, ?, ?);";   
            $params = [$c_spec, $stroj_skup, $titr, $titr_skup, $poznamka, $uziv, $galety, $praci_valce, $susici_valce, $cerpadlo, $pocet_mist, $korekce];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === false) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro INSERT Specifikace!",
                    "error" => sqlsrv_errors()
                ]);     
                exit;
            }

            $sql = "SELECT @@identity AS id_spec";
            $result = sqlsrv_query($conn, $sql);
            if ($result === FALSE) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro získání ID!",
                    "error" => sqlsrv_errors()
                ]);
                exit;
            }
            $id_spec = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['id_spec'];
            sqlsrv_free_stmt($result);

            if($stroj_skup == 1){
                $sql = "INSERT INTO Specifikace(id_spec, hnaci_motor, kotouc1, kotouc2, kotouc3, kotouc4, navijeci_valec, dlouzeni, ukladani_motor, remenice_m, remenice_g,
                            z9, z10, z11, z12, z13, z14, z15, z16, z17, z18, z19, z20, z21, z22, z23, z24, z30, z32)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
                $params = [$id_spec, $hnaci_motor, $kotouc1, $kotouc2, $kotouc3, $kotouc4, $navijeci_valec, $dlouzeni, $ukladani_motor, $remenice_m, $remenice_g,
                           $z9, $z10, $z11, $z12, $z13, $z14, $z15, $z16, $z17, $z18, $z19, $z20, $z21, $z22, $z23, $z24, $z30, $z32];
                $result = sqlsrv_query($conn, $sql, $params);
                if ($result === false) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Chyba SQL dotazu pro INSERT Spec_stare!",
                        "error" => sqlsrv_errors()
                    ]);     
                    exit;
                }
            }
            else if ($stroj_skup == 2){
                $sql = "INSERT INTO Specifikace (id_spec, hnaci_motor, kotouc1, kotouc2, kotouc3, kotouc4, navijeci_valec,
                            z9, z10, z11, z12, z13, z14, z15, z16, z17, z18, z19, z20, z21, z22, z23, z24, z30, z32)
                        VALUES (?, ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
                $params = [$id_spec, $hnaci_motor, $kotouc1, $kotouc2, $kotouc3, $kotouc4, $navijeci_valec,
                           $z9, $z10, $z11, $z12, $z13, $z14, $z15, $z16, $z17, $z18, $z19, $z20, $z21, $z22, $z23, $z24, $z30, $z32];
                $result = sqlsrv_query($conn, $sql, $params);
                if ($result === false) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Chyba SQL dotazu pro INSERT Spec_barmag!",
                        "error" => sqlsrv_errors()
                    ]);     
                    exit;
                }
            }
            else{
                $sql = "INSERT INTO Specifikace (id_spec, faktor, sg1_g2, sg2_w, sw_t, vg2, z1g1, z2g1, z1g2, z2g2, z1w, z2w, z1t, z2t, z1sp, z2sp)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
                $params = [$id_spec, $faktor, $sg1_g2, $sg2_w, $sw_t, $vg2, $z1g1, $z2g1, $z1g2, $z2g2, $z1w, $z2w, $z1t, $z2t, $z1sp, $z2sp];
                $result = sqlsrv_query($conn, $sql, $params);
                if ($result === false) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Chyba SQL dotazu pro INSERT Spec_nove!",
                        "error" => sqlsrv_errors()
                    ]);     
                    exit;
                }
            }
            sqlsrv_free_stmt($result);
        }
        echo json_encode(["success" => true, "message" => "Záznam byl úspěšně vložen."]);
        exit;
    }
?>