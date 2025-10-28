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
        else {
            echo json_encode(["success" => false, "message" => "Chybí ID specifikace"]);
            exit;
        }
    }
?>