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
        else {
            echo json_encode(["success" => false, "message" => "Chybí ID specifikace"]);
            exit;
        }
    }
?>