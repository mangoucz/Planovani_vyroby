<?php
    require_once 'server.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['id_spec'])) {
            $id = $_POST['id_spec'];

            $sql = "SELECT
                        s.c_spec,
                        s.titr,
                        s.titr_skup,
                        CONCAT(z.jmeno, ' ', z.prijmeni) as 'zadal',
                        s.vytvoreno, 
                        s.upraveno,
                        s.poznamka,
                    FROM (Specifikace AS s JOIN Zamestnanci as z on s.id_zam = z.id_zam)
                    WHERE p.id_spec = ?;";
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
                $zaznam["vytvoreno"] = $zaznam["vytvoreno"]->format("d.m.Y H:i");
                $zaznam["upraveno"] = isset($zaznam["upraveno"]) ? $zaznam["upraveno"]->format("d.m.Y H:i") : "Ne";
                
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
    }
?>