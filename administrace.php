<?php
    session_start();
    if (isset($_SESSION['uziv']) && isset($_SESSION['admin']) && $_SESSION['admin'] == true)
        $uziv = $_SESSION['uziv'];
    else{
        header("Location: login.php");
        exit();    
    }
    require_once 'server.php';

    $sql = "SELECT
                CONCAT(z.jmeno, ' ', z.prijmeni) AS jmeno,
                z.funkce
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

    $sql = [];
    $sql[0] = "SELECT TABLE_NAME as tab FROM Viscord.INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME LIKE 'Spec%' ORDER BY TABLE_NAME DESC;";
    $sql[1] = "SELECT * FROM Typ_stroje;";
    $sql[2] = "SELECT * FROM Stav_stroje;";
    $sql[3] = "SELECT * FROM Vyrobky;";

    for($i=0; $i<count($sql); $i++){
        $result = sqlsrv_query($conn, $sql[$i]);
        if ($result === FALSE)
            die(print_r(sqlsrv_errors(), true));

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            switch ($i) {
                case 0:
                    $tab_spec[] = $row;
                    break;
                case 1:
                    $typy_stroju[] = $row;
                    break;
                case 2:
                    $stav_stroje[] = $row;
                    break;
                case 3:
                    $vyrobky[] = $row;
                    break;
                default:
                    break;
            }
        }
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(!empty($_POST['save'])){ //Uložení do SESSION
            $_SESSION['save'] = $_POST['id'];
            echo json_encode(["success" => true]);
            exit;
        }
    }
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
        <?php 
            $d = new DateTime();
            $rozdil = $d->format('N')-1;              
            $d->modify("-$rozdil days");
        ?>
        <ul>
            <li><a href="odtahy-tyden.php?date=<?= date_format($d, "Y-m-d") ?>">Odtahy - týden</a></li>
            <li><a href="odtahy-den.php?date=<?= date_format(new DateTime(), "Y-m-d") ?>">Odtahy - den</a></li>
            <li><a href="specifikace.php">Specifikace</a></li>
            <li><a href="stroje.php">Stroje</a></li>
            <?php if($_SESSION['admin']): ?><li><a href="administrace.php" class="active">Administrace</a></li><?php endif; ?>
        </ul>
    </div>
    <div class="content-block clearfix">
        <div class="nadpis" id="specifikace">
            <h2>Specifikace</h2>
        </div>
        <div class="vyber specifikace" style="<?= (isset($_SESSION['save']) && $_SESSION['save'] == "specifikace") ? 'display: block;' : 'display: none'; ?>">
            <?php for ($i = 0; $i < count($tab_spec); $i++) : ?>
            <button class="vyberButt TabSpecButt" id="<?= $tab_spec[$i]['tab'] ?>"><?= $tab_spec[$i]['tab'] ?></button>
            <?php endfor; ?>
            <button class="vyberButt TabSpecButt" id="addTabSpec">+</button>
        </div>
        <?php for ($i = 0; $i < count($tab_spec); $i++) : ?>
        <div id="<?= $tab_spec[$i]['tab'] ?>" class="content specContent" style="width: 70%; <?= (isset($_SESSION['save']) && $_SESSION['save'] == $tab_spec[$i]['tab']) ? 'display: block;' : ''; ?>">
            <h3>
                <?= $tab_spec[$i]['tab'] ?>
                <span class="action-buttons">
                    <button class="edit" id="<?= $tab_spec[$i]['tab'] ?>">Upravit</button>
                    <button class="del" id="<?= $tab_spec[$i]['tab'] ?>">Smazat</button>
                </span>
            </h3>
            <?php
                $table = $tab_spec[$i]['tab'];
                $sql = "SELECT COLUMN_NAME as sloupec, DATA_TYPE as typ, CHARACTER_MAXIMUM_LENGTH as max, CONCAT(NUMERIC_PRECISION, ', ', NUMERIC_SCALE) as max_num, IS_NULLABLE as povinne
                        FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table';";
                $result = sqlsrv_query($conn, $sql);
                if($result === FALSE)
                    die(print_r(sqlsrv_errors(), true));

                $spec = [];
                while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                    $spec[] = $row;
                }
            ?>

            <form action="admin.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Sloupec</th>
                            <th>Datový typ</th>
                            <th>Max. vel.</th>
                            <th>NULL</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($j = 0; $j < count($spec); $j++) : ?>
                        <tr>
                            <td><?= $spec[$j]['sloupec'] ?></td>
                            <td><?= $spec[$j]['typ'] ?></td>
                            <td><?= ($spec[$j]['max'] ??  $spec[$j]['max_num']) === ', ' ? '' : ($spec[$j]['max'] ?? $spec[$j]['max_num']) ?></td>
                            <td><?= $spec[$j]['povinne'] == 'NO' ? 'NE' : 'ANO' ?></td>
                            <td style="text-align: center;"><img src="Update.png" class="editSpec" id="<?= $spec[$j]['sloupec'] ?>"></td>
                            <td style="text-align: center;"><img src="Delete.png" class="delSpec" id="<?= $spec[$j]['sloupec'] ?>"></td>
                            <input type="hidden" name="tab_spec" value="<?= $tab_spec[$i]['tab'] ?>">
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <button type="button" class="add" id="addSloupec">+</button>
            </form>
        </div>
        <?php endfor; ?>
    </div>
    <div class="content-block clearfix">
        <div class="nadpis" id="stroje">
            <h2>Stroje</h2>
        </div>
        <div class="vyber stroje" style="<?= (isset($_SESSION['save']) && $_SESSION['save'] == "stroje") ? 'display: block;' : 'display: none'; ?>">
            <?php for ($i = 0; $i < count($typy_stroju); $i++) : ?>
            <button class="vyberButt TypStrojButt" id="<?= $typy_stroju[$i]['id_typ'] ?>"><?= $typy_stroju[$i]['nazev'] ?></button>
            <?php endfor; ?>
            <button class="vyberButt TypStrojButt" id="addTypStroj">+</button>
        </div>
        <?php for ($i = 0; $i < count($typy_stroju); $i++) : ?>
        <div id="<?= $typy_stroju[$i]['id_typ'] ?>" class="content strojContent" style="width: 70%; <?= (isset($_SESSION['save']) && $_SESSION['save'] == $typy_stroju[$i]['id_typ']) ? 'display: block;' : ''; ?>">
            <h3>
                <?= $typy_stroju[$i]['nazev'] ?>
                <span class="action-buttons">
                    <button class="edit" id="<?= $typy_stroju[$i]['id_typ'] ?>">Upravit</button>
                    <button class="del" id="<?= $typy_stroju[$i]['id_typ'] ?>">Smazat</button>
                </span>
            </h3>
            <?php
                $sql = "SELECT * FROM Stroje WHERE id_typ = ?;";
                $params = [$typy_stroju[$i]['id_typ']];
                $result = sqlsrv_query($conn, $sql, $params);
                if($result === FALSE)
                    die(print_r(sqlsrv_errors(), true));

                $stroje = [];
                while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                    $stroje[] = $row;
                }
            ?>

            <form action="admin.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Název</th>
                            <th>Počet pozic</th>
                            <th>Váha cívky</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($j = 0; $j < count($stroje); $j++) : ?>
                        <tr>
                            <td><?= $stroje[$j]['nazev'] ?></td>
                            <td><?= $stroje[$j]['pocet_pozic'] ?></td>
                            <td><?= $stroje[$j]['vaha_civky'] ?></td>
                            <td style="text-align: center;"><img src="Update.png" class="editStroj" id="<?= $stroje[$j]['id_stroj'] ?>"></td>
                            <td style="text-align: center;"><img src="Delete.png" class="delStroj" id="<?= $stroje[$j]['id_stroj'] ?>"></td>
                            <input type="hidden" name="id_typ" value="<?= $stroje[$i]['id_typ'] ?>">
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <button type="button" class="add" id="addDruh">+</button>
            </form>
        </div>
        <?php endfor; ?>
    </div>
    <div class="content-block clearfix">
        <div class="nadpis" id="stavyStroju">
            <h2>Stavy strojů</h2>
        </div>
        <div class="content stavyStroju" style="width: 95%;<?= (isset($_SESSION['save']) && $_SESSION['save'] == "stavyStroju") ? 'display: block;' : 'display: none'; ?>">
            <form action="admin.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Zkratka</th>
                            <th>Název</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < count($stav_stroje); $i++) : ?>
                        <tr>
                            <td><?= $stav_stroje[$i]['zkratka'] ?></td>
                            <td><?= $stav_stroje[$i]['nazev'] ?></td>
                            <td style="text-align: center;"><img src="Update.png" class="editStav" id="<?= $stav_stroje[$i]['id_stav'] ?>"></td>
                            <td style="text-align: center;"><img src="Delete.png" class="delStav" id="<?= $stav_stroje[$i]['id_stav'] ?>"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <button type="button" class="add" id="addStav">+</button>
            </form>
        </div>
    </div>
    <div class="content-block clearfix">
        <div class="nadpis" id="vyrobky">
            <h2>Výrobky</h2>
        </div>
        <div class="content vyrobky" style="width: 95%;<?= (isset($_SESSION['save']) && $_SESSION['save'] == "vyrobky") ? 'display: block;' : 'display: none'; ?>">
            <form action="admin.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Výrobek</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < count($vyrobky); $i++) : ?>
                        <tr>
                            <td><?= $vyrobky[$i]['vyrobek'] ?></td>
                            <td style="text-align: center;"><img src="Update.png" class="editVyrobek" id="<?= $vyrobky[$i]['id_vyr'] ?>"></td>
                            <td style="text-align: center;"><img src="Delete.png" class="delVyrobek" id="<?= $vyrobky[$i]['id_vyr'] ?>"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <button type="button" class="add" id="addVyr">+</button>
            </form>
        </div>
    </div>
    <div class="footer">
        <img src="Indorama.png" width="200px">
    </div>
    <style>
        .clearfix::after {
            content: "";
            display: block;
            clear: both;
        }

        .nadpis{
            background: #FFFFFF;
            border: 1px solid #ccc;
            width: 100%;
            margin: 20px 0 0;
            padding-left: 10px;
            border-left: 5px solid #007bff;
            cursor: pointer;
        }
        .vyber {
            float: left;
            border: 1px solid #ccc;
            border-left: 5px solid #ff0000;
            background-color: #ffffff;
            width: 20vw;
        }

        .vyberButt {
            display: flex;
            background-color: transparent;
            color: #333;
            padding: 14px 20px;
            width: 100%;
            border: none;
            border-bottom: 1px solid #eee;
            outline: none;
            text-align: left;
            font-size: 1em;
            font-family: sans-serif;
            cursor: pointer;
            transition: background-color 0.3s ease;
            justify-content: space-between;
            align-items: center;
        }

        .vyberButt:hover {
            background-color: #f0f8ff;
        }

        .vyberButt.active {
            background-color: #e7f1ff;
            font-weight: bold;
        }
        .content {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            float: left;
            border-radius: 8px;
            border-left: none;
            display: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin: 20px;
            padding: 20px;
        }
        .content h3 {
            margin-top: 0;
            font-size: 1.5em;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            color: white;
            transition: background-color 0.2s ease;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f1f1f1;
        }
        tr:nth-child(even) {
            background-color: #fbfbfb;
        }
        td img, h3 img{
            width: 30px;
            cursor: pointer;
        }

        .edit {
            background-color: #007bff;
        }
        .edit:hover {
            background-color: #0056b3;
        }
        .del {
            background-color: #dc3545;
        }
        .del:hover {
            background-color: #b02a37;
        }

        .add {
            float: right;
            margin: 10px 0;
            padding: 8px 16px;
            font-size: 1em;
            font-weight: bold;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .add:hover {
            background-color: #218838;
        }
        .add:active {
            transform: scale(0.97);
        }
        .delNew{
            background: #ff0000;
        }
        .delNew:hover {
            background-color: #dc0000;
        }
        .save {
            background-color: #3b82f6;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .save:hover {
            background-color: #2563eb;
        }
        .save:active {
            transform: scale(0.97);
        }
        .save:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.4);
        }

        .footer{
            display: none;
        }
        
        @media (max-width: 660px) {
            
        }
    </style>
</body>
</html>