<?php
    require_once 'server.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['id_spec'])) {
            $id = $_POST['id_spec'];

            $sql = "SELECT
                        s.c_spec,
                        s.titr,
                        s.titr_skup,
                        s.id_zam,
                        s.id_typ_stroje as sk_stroj,
                        CONCAT(z.jmeno, ' ', z.prijmeni) as 'vytvoril',
                        s.vytvoreno, 
                        s.upraveno,
                        v.vyrobek,
                        s.poznamka
                    FROM (Specifikace as s LEFT JOIN Vyrobky as v ON s.id_vyr = v.id_vyr) LEFT JOIN Zamestnanci as z ON s.id_zam = z.id_zam 
                    WHERE s.id_spec = ?;";
            $params = [$id];
            $result = sqlsrv_query($conn, $sql, $params);

            if ($result === false) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu!",
                    "error" => sqlsrv_errors()
                ]);     
                exit;
            }            
            $zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            
            if ($zaznam) {
                $zaznam["vytvoreno"] = $zaznam["vytvoreno"]->format("d.m.Y");
                $zaznam["upraveno"] = isset($zaznam["upraveno"]) ? $zaznam["upraveno"]->format("d.m.Y H:i") : "Ne";

                switch ($zaznam['id_zam']) {
                        case '3':
                            $zaznam['vytvoril'] = "Martin Vocásek";
                            break;
                        case '5':
                            $zaznam['vytvoril'] = "Uršula Löwyová"; 
                            break;
                        default:
                            $zaznam['vytvoril'] = $zaznam['vytvoril'];
                            break;
                    }
                
                echo json_encode([
                    "success" => true,
                    "data" => $zaznam
                ]);
                exit;
            }
            else {
                echo json_encode(["success" => false, "message" => "Záznam nenalezen"]);
                exit;
            }
            sqlsrv_free_stmt($result);
        }
        elseif(isset($_POST['get_vyrobky'])){
            $sql = "SELECT id_vyr, vyrobek FROM Vyrobky;";
            $result = sqlsrv_query($conn, $sql);
            if ($result === FALSE) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro získání výrobků!",
                    "error" => sqlsrv_errors()
                ]);
                exit;
            }
            $vyrobky = [];
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $vyrobky[] = $row;
            }
            sqlsrv_free_stmt($result);
            echo json_encode([
                "success" => true,
                "data" => $vyrobky
            ]);
            exit;
        }
        elseif(isset($_POST['search'])){
            $search = $_POST['search'];
            $typ = isset($_POST['typ']) ? intval($_POST['typ']) : 0;

            $sql = "SELECT 
                        id_spec, s.id_vyr, c_spec, titr, titr_skup, s.id_typ_stroje, 
                        v.vyrobek, s.id_zam, CONCAT(z.jmeno, ' ', z.prijmeni) as vytvoril, vytvoreno
                    FROM Specifikace AS s
                    LEFT JOIN Vyrobky AS v ON s.id_vyr = v.id_vyr
                    LEFT JOIN Zamestnanci AS z ON s.id_zam = z.id_zam
                    WHERE " . ($typ != 0 ? "s.id_typ_stroje = ? AND " : "") . "
                        (c_spec LIKE ? OR titr LIKE ? OR titr_skup LIKE ? OR v.vyrobek LIKE ?)
                    ORDER BY c_spec;";

            $params = $typ != 0 ? [$typ, "%$search%", "%$search%", "%$search%", "%$search%"] : ["%$search%", "%$search%", "%$search%", "%$search%"];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro hledání specifikací!",
                    "error" => sqlsrv_errors()
                ]);
                exit;
            }
            while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $zaznam["vytvoreno"] = $zaznam["vytvoreno"]->format("d.m.Y");
                $zaznam["vyrobek"] = $zaznam["vyrobek"] ?? "Nevybrán";
                switch ($zaznam['id_zam']) {
                    case '3':
                        $zaznam['vytvoril'] = "Martin Vocásek";
                        break;
                    case '5':
                        $zaznam['vytvoril'] = "Uršula Löwyová"; 
                        break;
                    default:
                        $zaznam['vytvoril'] = $zaznam['vytvoril'];
                        break;
                }
                $results[] = $zaznam;
            }
            sqlsrv_free_stmt($result);
            echo json_encode([
                "success" => true,
                "data" => $results
            ]);
            exit;
        }
        elseif(isset($_POST['getTyden'])){
            $sql = "SELECT id_stav, CONCAT(zkratka, ' - ', nazev) as stav from Stav_stroje;";
            $result = sqlsrv_query($conn, $sql);
            if ($result === FALSE) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro SELECT stavů strojů!",
                    "error" => sqlsrv_errors()
                ]);
                exit;
            }
            $stavy = [];
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $stavy[] = $row;
            }
            sqlsrv_free_stmt($result);

            $sql = "SELECT MAX(konec) as posledni from Naviny;";
            $result = sqlsrv_query($conn, $sql);
            if ($result === FALSE) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro SELECT posledního týdne!",
                    "error" => sqlsrv_errors()
                ]);
                exit;
            }
            $posledni = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['posledni']->format("Y-m-d");
            sqlsrv_free_stmt($result);

            echo json_encode([
                "success" => true,
                "data" => [
                    "stavy" => $stavy,
                    "posledni" => $posledni
                ]
            ]);
            exit;
        }
        elseif(isset($_POST['id_nav'])){
            $id_nav = $_POST['id_nav'];

            $sql = "SELECT FORMAT(n.zacatek, 'dd.MM.yyyy HH:mm') as od, FORMAT(n.zacatek, 'yyyy-MM-dd HH:mm') as zacatek, FORMAT(n.konec, 'dd.MM.yyyy HH:mm') as do, FORMAT(n.konec, 'yyyy-MM-dd HH:mm') as konec, CONVERT(varchar(5), n.doba, 108) as doba, n.serie, n.id_spec, n.stav_stroje as id_stav, n.id_stroj, s.nazev, s.id_typ
                    FROM Naviny as n JOIN Stroje as s on n.id_stroj = s.id_stroj 
                    WHERE n.id_nav = ?;";
            $params = [$id_nav];
            $result = sqlsrv_query($conn, $sql, $params);

            if ($result === false) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro získání detailu návinu!",
                    "error" => sqlsrv_errors()
                ]);     
                exit;
            }            
            $navin = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($result);

            $sql = "SELECT CONCAT(s.c_spec, ' - titr: ', s.titr_skup) as spec, s.id_spec FROM Specifikace as s WHERE s.id_typ_stroje = ?;";
            $params = [$navin['id_typ']];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === false) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro získání specifikací podle typu stroje!",
                    "error" => sqlsrv_errors()
                ]);     
                exit;
            }
            $spec = [];
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $spec[] = $row;
            }
            sqlsrv_free_stmt($result);

            $sql = "SELECT CONCAT(zkratka, ' - ', nazev) as stav, id_stav FROM Stav_stroje;";
            $result = sqlsrv_query($conn, $sql);
            if ($result === FALSE) {
                echo json_encode([
                    "success" => false,
                    "message" => "Chyba SQL dotazu pro SELECT stavů strojů!",
                    "error" => sqlsrv_errors()
                ]);
                exit;
            }
            $stavy = [];
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $stavy[] = $row;
            }
            sqlsrv_free_stmt($result);

            if ($navin && $spec && $stavy) {
                echo json_encode([
                    "success" => true,
                    "navin" => $navin,
                    "spec" => $spec,
                    "stavy" => $stavy
                ]);
                exit;                
            }
            else {
                echo json_encode(["success" => false, "message" => "Záznam nenalezen"]);
                exit;
            }
            sqlsrv_free_stmt($result);
        }
        else {
            echo json_encode(["success" => false, "message" => "Chybí ID specifikace"]);
            exit;
        }
    }
?>