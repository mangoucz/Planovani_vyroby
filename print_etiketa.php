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
        $id_nav = $_POST['id_nav'];

        $sql = "SELECT n.serie, FORMAT(n.konec, 'HH:mm') as konec, st.nazev
                FROM Naviny as n JOIN Stroje as st on n.id_stroj = st.id_stroj
                WHERE n.id_nav = ?;";
        $params = [$id_nav];
        $result = sqlsrv_query($conn, $sql, $params);
        if ($result === FALSE)
            die(print_r(sqlsrv_errors(), true));
        $navin = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt($result);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php for ($i = 0; $i < 36; $i++): ?>
        <div class="page">
            <table style='font-size: 13px;'>
                <tbody>
                    <tr>
                        <td rowspan="4"><img src="" alt=""></td>
                        <td>Série: <?= $navin['serie'] ?></td>
                    </tr>
                    <tr>
                        <td>Odtah: <?= $navin['konec'] ?></td>
                    </tr>
                    <tr>
                        <td>Stroj: <?= $navin['nazev'] ?></td>
                    </tr>
                    <tr>
                        <td>Pozice: <?= $i + 1 ?></td>
                    </tr>
                </tbody>
            </table>
        </div>    
    <?php endfor; ?>
    <style>
        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: white;
            font: 6pt "Tahoma";
        }
        .page {
            width: 50mm;
            min-height: 30mm;
            padding: 0mm;
            margin: 0mm auto;
            border: 0px solid black;
            border-radius: 2px;
            background: white;
            box-shadow: 0 0 0px rgba(0, 0, 0, 0.1);
        }

        @page {
            size: label;
            margin: 0;
        }
        @media print {
            html, body {
                width: 50mm;
                height: 30mm;        
            }
            .page {
                margin-left: 10px;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>