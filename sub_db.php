<?php
    function GenCisSpec($conn) : int {
        $c_spec = (int)date("y") * 100000;

        $sql = "SELECT MAX(c_spec) AS c_spec FROM Specifikace WHERE c_spec >= ? AND c_spec < ?";
        $params = [$c_spec, $c_spec + 100000];
        $result = sqlsrv_query($conn, $sql, $params);
        if ($result === FALSE)
            die(print_r(sqlsrv_errors(), true));
        $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['c_spec'];         
        sqlsrv_free_stmt($result);
            
        if ($zaznam !== null) 
            return $c_spec = $zaznam + 1;   
        return $c_spec + 1;
    }
    function GenSerie($konec) : int {
        return (int)$konec->format('ymd');
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
        if (isset($_POST['id_typ_stroje'])) { //specifikace (nová i editace)
            $sql = "";
            $params = [];
    
            //Specifikace
            $id_spec = $_POST['id_spec'] ?? null;
            $c_spec = !isset($_POST['c_spec']) ? GenCisSpec($conn) : $_POST['c_spec'];
            $id_typ_stroje = $_POST['id_typ_stroje'] ?? null;
            $titr = $_POST['titr'] ?? null;
            $titr_skup = $_POST['titr_skup'] ?? null;
            $poznamka = $_POST['poznamka'] ?? null;
            $galety = $_POST['galety'] ?? null;
            $praci_valce = $_POST['praci_valce'] ?? null;
            $susici_valec = $_POST['susici_valec'] ?? null;
            $cerpadlo = inputCheck($_POST['cerpadlo']);
            $pocet_mist = $_POST['pocet_mist'] ?? null;
            $korekce_barmag = inputCheck($_POST['korekce_barmag']);
            $korekce_nove = inputCheck($_POST['korekce_nove']);
            $korekce = $korekce_barmag ?? $korekce_nove;
            //Stare + Barmag
            $hnaci_motor = $_POST['hnaci_motor'] ?? null;
            $kotouc1 = $_POST['kotouc1'] ?? null;
            $kotouc2 = $_POST['kotouc2'] ?? null;
            $kotouc3 = $_POST['kotouc3'] ?? null;
            $kotouc4 = $_POST['kotouc4'] ?? null;   
            $navijeci_valec = $_POST['navijeci_valec'] ?? null;
            $dlouzeni = inputCheck($_POST['dlouzeni']);
            $ukladani_motor = $_POST['ukladani_motor'] ?? null;
            $remenice_m = $_POST['remenice_m'] ?? null;
            $remenice_g = $_POST['remenice_g'] ?? null;
            $z9 = $_POST['z9'] ?? null;
            $z10 = $_POST['z10'] ?? null;
            $z11 = $_POST['z11'] ?? null;
            $z12 = $_POST['z12'] ?? null;
            $z13 = $_POST['z13'] ?? null;
            $z14 = $_POST['z14'] ?? null;
            $z15 = $_POST['z15'] ?? null;
            $z16 = $_POST['z16'] ?? null;
            $z17 = $_POST['z17'] ?? null;
            $z18 = $_POST['z18'] ?? null;
            $z19 = $_POST['z19'] ?? null;
            $z20 = $_POST['z20'] ?? null;
            $z21 = $_POST['z21'] ?? null;
            $z22 = $_POST['z22'] ?? null;
            $z23 = $_POST['z23'] ?? null;
            $z24 = $_POST['z24'] ?? null;
            $z30 = $_POST['z30'] ?? null;
            $z32 = $_POST['z32'] ?? null;
            //Nove
            $faktor = inputCheck($_POST['faktor']);
            $sg1_g2 = inputCheck($_POST['sg1_g2']);
            $sg2_w = inputCheck($_POST['sg2_w']);
            $sw_t = inputCheck($_POST['sw_t']);
            $vg2 = inputCheck($_POST['vg2']);
            $z1g1 = $_POST['z1g1'] ?? null; 
            $z2g1 = $_POST['z2g1'] ?? null;
            $z1g2 = $_POST['z1g2'] ?? null;
            $z2g2 = $_POST['z2g2'] ?? null;
            $z1w = $_POST['z1w'] ?? null;
            $z2w = $_POST['z2w'] ?? null;
            $z1t = $_POST['z1t'] ?? null;
            $z2t = $_POST['z2t'] ?? null;
            $z1sp = $_POST['z1sp'] ?? null;
            $z2sp = $_POST['z2sp'] ?? null;

            $spotreba = $id_typ_stroje == 3 ? (($vg2 * 60.0 / 10000.0 * $titr / 1000.0) / $faktor * (1.0 + $korekce / 100.0) * $pocet_mist) : 
                                                ($hnaci_motor * 1.0 * $kotouc1 * 1.0 / $kotouc2 * 19.0 / 28.0 * $z21 * 1.0 / $z22 * $z23 * 1.0 / $z24 * 10.0 / 30.0 * 22.0 / 44.0 * 22.0 / 44.0 * 29.0 / 58.0 * ($korekce + 1) * $cerpadlo * 1.0 * 60.0 / 1000.0 * $pocet_mist * 1.0);

            //EDIT
            if ($id_spec != null) { 
                $sql = "UPDATE Specifikace 
                        SET c_spec = ?, id_typ_stroje = ?, titr = ?, titr_skup = ?, poznamka = ?, upraveno = GETDATE(), upravil = ?, galety = ?, praci_valce = ?, susici_valec = ?, cerpadlo = ?, pocet_mist = ?, korekce = ?, spotreba = ? 
                        WHERE id_spec = ?;";
                $params = [$c_spec, $id_typ_stroje, $titr, $titr_skup, $poznamka, $uziv, $galety, $praci_valce, $susici_valec, $cerpadlo, $pocet_mist, $korekce, $spotreba, $id_spec];
                $result = sqlsrv_query($conn, $sql, $params);
                if ($result === false) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Chyba SQL dotazu pro UPDATE Specifikace!",
                        "error" => sqlsrv_errors()
                    ]);     
                    exit;
                }

                if($id_typ_stroje == 1){
                    $sql = "UPDATE Spec_stare 
                            SET hnaci_motor = ?, kotouc1 = ?, kotouc2 = ?, kotouc3 = ?, kotouc4 = ?, navijeci_valec = ?, dlouzeni = ?, ukladani_motor = ?, remenice_m = ?, remenice_g = ?,
                                z9 = ?, z10 = ?, z11 = ?, z12 = ?, z13 = ?, z14 = ?, z15 = ?, z16 = ?, z17 = ?, z18 = ?, z19 = ?, z20 = ?, z21 = ?, z22 = ?, z23 = ?, z24 = ?, z30 = ?, z32 = ?
                            WHERE id_spec = ?;";
                    $params = [$hnaci_motor, $kotouc1, $kotouc2, $kotouc3, $kotouc4, $navijeci_valec, $dlouzeni, $ukladani_motor, $remenice_m, $remenice_g,
                               $z9, $z10, $z11, $z12, $z13, $z14, $z15, $z16, $z17, $z18, $z19, $z20, $z21, $z22, $z23, $z24, $z30, $z32,
                               $id_spec];
                    $result = sqlsrv_query($conn, $sql, $params);
                    if ($result === false) {
                        echo json_encode([
                            "success" => false,
                            "message" => "Chyba SQL dotazu pro UPDATE Spec_stare!",
                            "error" => sqlsrv_errors()
                        ]);     
                        exit;
                    }
                }
                else if ($id_typ_stroje == 2){
                    $sql = "UPDATE Spec_barmag 
                            SET hnaci_motor = ?, kotouc1 = ?, kotouc2 = ?, kotouc3 = ?, kotouc4 = ?, navijeci_valec = ?,
                                z9 = ?, z10 = ?, z11 = ?, z12 = ?, z13 = ?, z14 = ?, z15 = ?, z16 = ?, z17 = ?, z18 = ?, z19 = ?, z20 = ?, z21 = ?, z22 = ?, z23 = ?, z24 = ?, z30 = ?, z32 = ?
                            WHERE id_spec = ?;";
                    $params = [$hnaci_motor, $kotouc1, $kotouc2, $kotouc3, $kotouc4, $navijeci_valec,
                               $z9, $z10, $z11, $z12, $z13, $z14, $z15, $z16, $z17, $z18, $z19, $z20, $z21, $z22, $z23, $z24, $z30, $z32,
                               $id_spec];
                    $result = sqlsrv_query($conn, $sql, $params);
                    if ($result === false) {
                        echo json_encode([
                            "success" => false,
                            "message" => "Chyba SQL dotazu pro UPDATE Spec_barmag!",
                            "error" => sqlsrv_errors()
                        ]);     
                        exit;
                    }
                }
                else if ($id_typ_stroje == 3){
                    $sql = "UPDATE Spec_nove 
                            SET faktor = ?, sg1_g2 = ?, sg2_w = ?, sw_t = ?, vg2 = ?, z1g1 = ?, z2g1 = ?, z1g2 = ?, z2g2 = ?, z1w = ?, z2w = ?, z1t = ?, z2t = ?, z1sp = ?, z2sp = ?
                            WHERE id_spec = ?;";
                    $params = [$faktor, $sg1_g2, $sg2_w, $sw_t, $vg2, $z1g1, $z2g1, $z1g2, $z2g2, $z1w, $z2w, $z1t, $z2t, $z1sp, $z2sp,
                               $id_spec];
                    $result = sqlsrv_query($conn, $sql, $params);
                    if ($result === false) {
                        echo json_encode([
                            "success" => false,
                            "message" => "Chyba SQL dotazu pro UPDATE Spec_nove!",
                            "error" => sqlsrv_errors()
                        ]);     
                        exit;
                    }
                }
                sqlsrv_free_stmt($result);
                echo json_encode(["success" => true, 
                                    "data" => [
                                        "id_spec" => $id_spec,
                                        "c_spec" => $c_spec,
                                        "typ_stroje" => $id_typ_stroje
                                    ]
                                ]);
                exit;
    
            }//INSERT
            else{
                $sql = "INSERT INTO Specifikace(c_spec, id_typ_stroje, titr, titr_skup, poznamka, vytvoreno, id_zam, galety, praci_valce, susici_valec, cerpadlo, pocet_mist, korekce) 
                        VALUES (?, ?, ?, ?, ?, GETDATE(), ?, ?, ?, ?, ?, ?, ?);";   
                $params = [$c_spec, $id_typ_stroje, $titr, $titr_skup, $poznamka, $uziv, $galety, $praci_valce, $susici_valec, $cerpadlo, $pocet_mist, $korekce];
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
    
                if($id_typ_stroje == 1){
                    $sql = "INSERT INTO Spec_stare(id_spec, hnaci_motor, kotouc1, kotouc2, kotouc3, kotouc4, navijeci_valec, dlouzeni, ukladani_motor, remenice_m, remenice_g,
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
                else if ($id_typ_stroje == 2){
                    $sql = "INSERT INTO Spec_barmag (id_spec, hnaci_motor, kotouc1, kotouc2, kotouc3, kotouc4, navijeci_valec,
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
                else if ($id_typ_stroje == 3){
                    $sql = "INSERT INTO Spec_nove (id_spec, faktor, sg1_g2, sg2_w, sw_t, vg2, z1g1, z2g1, z1g2, z2g2, z1w, z2w, z1t, z2t, z1sp, z2sp)
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
            echo json_encode(["success" => true, 
                                "data" => [
                                    "id_spec" => $id_spec,
                                    "c_spec" => $c_spec,
                                    "typ_stroje" => $id_typ_stroje
                                ]
                            ]);
            exit;
        }
        elseif (isset($_POST['id_vyr'])) { //změna výrobku u specifikace
            $id_spec = $_POST['id_spec'];
            $id_vyr = $_POST['id_vyr'] == 0 ? null : $_POST['id_vyr']; 

            $sql = "UPDATE Specifikace SET id_vyr = ? WHERE id_spec = ?;";
            $params = [$id_vyr, $id_spec];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === false) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro UPDATE id_vyr!",
                    "error" => sqlsrv_errors()
                ]);     
                exit;
            }
            echo json_encode(["success" => true, "message" => "Výrobek byl úspěšně přiřazen."]);
            exit;
        }
        elseif (isset($_POST['novyTyden'])){ //vytvoření nového týdne
            $stav = $_POST['stav_stroju'];
            $zac_nov_tydne = new DateTime($_POST['pondeli']); //první den nového týdne
            $kon_nov_tydne = (clone $zac_nov_tydne)->modify('monday next week'); //poslední den nového týdne
            $zac_nov_tydne->modify('+5 hours 45 minutes');
            $kon_nov_tydne->modify('+5 hours 35 minutes');

            $sql = "SELECT MAX(n.zacatek) as posledni FROM Naviny as n;"; //zjištění posledního naplánovaného dne
            $result = sqlsrv_query($conn, $sql);
            if ($result === FALSE) 
                die(print_r(sqlsrv_errors(), true));
            $posledni = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['posledni'];
            sqlsrv_free_stmt($result);

            $posl_zac_tydne = new DateTime($posledni->format('Y-m-d'));
            if ($posl_zac_tydne->format('N') == 1) 
                $posl_zac_tydne->modify('monday previous week'); //první den posledně nalánovaného týdne
            else
                $posl_zac_tydne = $posl_zac_tydne->modify('monday this week'); 

            $posl_zac_tydne->modify('+5 hours 45 minutes');
            $posl_konec_tydne = (clone $posl_zac_tydne)->modify('monday next week'); //poslední den posledně naplánovaného týdne 
            $posl_konec_tydne->modify('+5 hours 35 minutes');

            $sql = "SELECT n.id_stroj, n.serie, n.id_spec, n.zacatek, n.konec, n.doba, n.stav_stroje 
                    FROM Naviny AS n
                    JOIN (SELECT id_stroj, max(konec) as posledni 
                        FROM Naviny as n
                        WHERE n.zacatek >= ? AND n.konec <= ? 
                        GROUP BY id_stroj) AS stroje
                    ON n.id_stroj = stroje.id_stroj AND n.konec = stroje.posledni
                    ORDER BY n.id_stroj;"; //výběr posledních navinů pro každý stroj z posledně naplánovaného týdne
            $params = [$posl_zac_tydne->format('Y-m-d H:i'), $posl_konec_tydne->format('Y-m-d H:i')];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE) 
                die(print_r(sqlsrv_errors(), true));
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $novy_zacatek = $row['konec'];
                $minuty = ((int)$row['doba']->format('H') * 60) + (int)$row['doba']->format('i');
                while($novy_zacatek->format('o-W') != $zac_nov_tydne->format('o-W')){
                    $novy_zacatek->modify('+' . $minuty . ' minutes');
                }
                $novy_konec = (clone $novy_zacatek)->modify('+' . $minuty . ' minutes');
                if($stav != 0)
                    $row['stav_stroje'] = $stav;

                while($novy_konec < $kon_nov_tydne){
                    $sql_insert = "INSERT INTO Naviny (id_stroj, serie, id_spec, zacatek, konec, doba, stav_stroje) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?);";
                    $params_insert = [$row['id_stroj'], GenSerie($novy_konec), $row['id_spec'], $novy_zacatek, $novy_konec, $row['doba'], $row['stav_stroje']];
                    $result_insert = sqlsrv_query($conn, $sql_insert, $params_insert);
                    if ($result_insert === FALSE) 
                        die(print_r(sqlsrv_errors(), true));
                    sqlsrv_free_stmt($result_insert);

                    $novy_zacatek = (clone $novy_konec);
                    $novy_konec = (clone $novy_zacatek)->modify('+' . $minuty . ' minutes');
                }
            }
            sqlsrv_free_stmt($result);
            header("Location: odtahy-tyden.php?date=" . $zac_nov_tydne->format('Y-m-d'));
            exit;
        }
        elseif(isset($_POST['doba_navinu'])){ //změna doby návinů stroje
            //Změna se provede pro tento a všechny následující náviny
            $doba = $_POST['doba_navinu'];
            $id_spec = $_POST['id_spec'];
            $id_stroj = $_POST['id_stroj'];
            $zacatek = $_POST['zacatek'];
            $stav = $_POST['stav'];

            $sql = "SELECT MAX(n.zacatek) as posledni FROM Naviny as n;"; //zjištění posledního naplánovaného dne
            $result = sqlsrv_query($conn, $sql);
            if ($result === FALSE) 
                die(print_r(sqlsrv_errors(), true));
            $konec_tydne = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['posledni'];
            sqlsrv_free_stmt($result);

            $konec_tydne = new DateTime($konec_tydne->format("Y-m-d"));
            if ($konec_tydne->format('N') !== 1) 
                $konec_tydne->modify('monday next week'); //poslední den posledně nalánovaného týdne
            $konec_tydne->modify('+5 hours 35 minutes');

            sqlsrv_begin_transaction($conn);

            // Smazání dotčených návinů
            $sql = "DELETE FROM Naviny
                    WHERE id_stroj = ? AND zacatek >= ?";
            $params = [$id_stroj, $zacatek];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === false) {
                sqlsrv_free_stmt($result);
                sqlsrv_rollback($conn);
                die(print_r(sqlsrv_errors(), true));
            }
            list($h, $m) = explode(':', $doba);
            $predchoziKonec = new DateTime($zacatek); //předchozí konec = nový začátek
            $novyKonec = (clone $predchoziKonec)->modify('+ ' . $h . ' hours ' . $m . ' minutes');

            while($novyKonec < $konec_tydne){
                $sql = "INSERT INTO Naviny (id_stroj, serie, id_spec, zacatek, konec, doba, stav_stroje)
                        VALUES (?, ?, ?, ?, ?, ?, ?);"; //Znovu naplánování dotčených návinů
                $params = [$id_stroj, GenSerie($novyKonec), $id_spec, $predchoziKonec, $novyKonec, $doba, $stav];
                $resultUpd = sqlsrv_query($conn, $sql, $params);
                if ($resultUpd === false) {
                    sqlsrv_rollback($conn);
                    die(print_r(sqlsrv_errors(), true));
                }
                $predchoziKonec = $novyKonec;
                $novyKonec = (clone $predchoziKonec)->modify('+ ' . $h . ' hours ' . $m . ' minutes');
            }
            sqlsrv_free_stmt($result);
            sqlsrv_free_stmt($resultUpd);
            sqlsrv_commit($conn);

            header("Location: odtahy-tyden.php?date=" . $zacatek);
            exit;
        }
        elseif(isset($_POST['id_spec'])){ //změna specifikace stroje
            $id_spec = $_POST['id_spec'];
            $cil = $_POST['navin_volba_spec'];
            $id_stroj = $_POST['id_stroj'];
            $zacatek = $_POST['zacatek']; 

            if($cil < 4){
                $ar = [
                    '1' => '=', //tento návin
                    '2' => '>', //všechny následující
                    '3' => '>=' //tento a všechny násl.
                ];
                $zn = $ar[$cil];

                $sql = "UPDATE Naviny SET id_spec = ? WHERE zacatek $zn ? AND id_stroj = ?;";
                $params = [$id_spec, $zacatek, $id_stroj];
                $result = sqlsrv_query($conn, $sql, $params);
                if ($result === FALSE) 
                    die(print_r(sqlsrv_errors(), true));
                sqlsrv_free_stmt($result);
            }
            elseif($cil == 4){ //počet návinů
                $pocet = $_POST['pocet'];
            }
            else{ //náviny do data
                $do = $_POST['dat'];
            }
            
            header("Location: odtahy-tyden.php?date=" . $zacatek);
            exit;
        }
        elseif(isset($_POST['stav'])){ //změna stavu stroje

        }
        elseif(isset($_POST['posun'])){ //posun začátku navinů stroje

        }
        else{
            echo json_encode([
                "success" => false,
                "message" => "Neplatné parametry POST!"
            ]);
            exit;
        }
    }
?>