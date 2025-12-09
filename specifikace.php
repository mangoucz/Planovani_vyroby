<?php
    session_start();
    if (isset($_SESSION['uziv']))
        $uziv = $_SESSION['uziv'];
    else{
        header("Location: login.php");
        exit();    
    }
    require_once 'server.php';

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['subDel'])){
            $id_spec = $_POST['id'];
            $typ_stroje = $_POST['typ_stroje'];

            switch ($typ_stroje) {
                case '1':
                    $table = 'Spec_barmag';
                    break;
                case '2':
                    $table = 'Spec_stare';
                    break;
                case '3':
                    $table = 'Spec_nove';
                    break;
                default:
                    $table = '';
                    break;
            }
            $sql = "DELETE FROM $table WHERE id_spec = ?;
                    DELETE FROM Specifikace WHERE id_spec = ?;";
            $params = [$id_spec, $id_spec];
            $result = sqlsrv_query($conn, $sql, $params);
            if ($result === FALSE)
                die(print_r(sqlsrv_errors(), true));
            sqlsrv_free_stmt($result);
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

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
    $admin = $_SESSION['admin'];

    $sql = "SELECT * FROM Typ_stroje;";
    $result = sqlsrv_query($conn, $sql);
    if ($result === FALSE)
        die(print_r(sqlsrv_errors(), true));
    $typ_stroje = [];
    while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $typ_stroje[$zaznam['id_typ']] = $zaznam['nazev'];
    }
    $id_stroje = isset($_GET['stroj']) ? $_GET['stroj'] : 0;
    sqlsrv_free_stmt($result);

    $sql = "SELECT id_spec, s.id_vyr, c_spec, titr, titr_skup, s.id_typ_stroje, v.vyrobek, s.id_zam, CONCAT(z.jmeno, ' ', z.prijmeni) as vytvoril, vytvoreno 
            FROM (Specifikace as s LEFT JOIN Vyrobky as v ON s.id_vyr = v.id_vyr) LEFT JOIN Zamestnanci as z ON s.id_zam = z.id_zam 
            " . ($id_stroje != 0 ? "WHERE s.id_typ_stroje = ?" : "") . "
            ORDER BY c_spec;";
    $params = $id_stroje != 0 ? [$id_stroje] : [];
    if ($id_stroje != 0)
        $result = sqlsrv_query($conn, $sql, $params);
    else
        $result = sqlsrv_query($conn, $sql);
    if ($result === FALSE)
        die(print_r(sqlsrv_errors(), true));
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
            <li><a href="specifikace.php" class="active">Specifikace</a></li>
            <li><a href="stroje.php">Stroje</a></li>
            <?php if($admin): ?><li><a href="administrace.php">Administrace</a></li><?php endif; ?>
        </ul>
    </div>
    <div class="setting">
        <select name="stroj" id="selectStroj">
            <option value="0">Všechny specifikace</option>
            <?php
                foreach ($typ_stroje as $id => $typ): ?>
                    <option value="<?= $id ?>" <?= ($id == $id_stroje) ? 'selected' : '' ?>><?= $typ ?></option>
            <?php endforeach; ?>
        </select>
        <input type="search" name="searchSpec" id="searchSpec" placeholder="Hledat specifikaci...">    
        <button type="button" class="defButt" onclick="window.location.href = 'spec_form.php'">Nová specifikace</button>
    </div>
    <table>
        <thead>
            <tr>
                <th>Č. spec.</th>
                <th>Titr</th>
                <th>Skup. titrů</th>
                <th>Skup. strojů</th>
                <th>Vytvořil</th>
                <th>Vytvořeno</th>
                <th>Výrobek</th>
                <th>info</th>
            </tr>
        </thead>
        <tbody id="body_spec">
            <?php
                while ($zaznam = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                    $id_spec = $zaznam['id_spec'];
                    $c_spec = $zaznam['c_spec'];
                    $titr = $zaznam['titr'];
                    $titr_skup = $zaznam['titr_skup'];
                    $id_typ_stroje = $zaznam['id_typ_stroje'];
                    $vytvoreno = $zaznam['vytvoreno']->format('d.m.Y');
                    $id_vyr = $zaznam['id_vyr'];
                    $vyrobek = isset($zaznam['vyrobek']) ? $zaznam['vyrobek'] : 'Nevybrán';

                    switch ($zaznam['id_zam']) {
                        case '3':
                            $vytvoril = "Martin Vocásek";
                            break;
                        case '5':
                            $vytvoril = "Uršula Löwyová"; 
                            break;
                        default:
                            $vytvoril = $zaznam['vytvoril'];
                            break;
                    }

                    echo "<tr>
                            <td data-label='Č. spec.' id='c_spec'>$c_spec</td>
                            <td data-label='Titr' id='titr'>$titr</td>
                            <td data-label='Skup. titrů' id='skup_titr'>$titr_skup</td>
                            <td data-label='Skup. strojů' id='skup_stroj'>$id_typ_stroje</td>
                            <td data-label='Vytvořil' id='vytvoril'>$vytvoril</td>
                            <td data-label='Vytvořeno' id='vytvoreno'>$vytvoreno</td>
                            <td data-label='Výrobek' id='vyrobek'><span class='vyr' id='$id_vyr' data-id_spec='$id_spec'>$vyrobek</span></td>
                            <td data-label='info' id='info'><img src='info.png' alt='Podrobnosti' class='info-icon link' id='$id_spec'></td>
                        </tr>";
                }
                sqlsrv_free_stmt($result);
            ?>
        </tbody>
    </table>
    <div class="modal">
        <div class="modal-content" style="width: 500px;">
            <div class="modal-header">
                <span id="closeBtn" class="close">&times;</span>
                <h2>Specifikace č. </h2>
            </div>
            <div class="modal-body">
                <div class="info-row"><span class="label">Titr:</span><span class="titr obsah"></span></div>
                <div class="info-row"><span class="label">Skupina titrů:</span><span class="sk_titr obsah"></span></div>
                <div class="info-row"><span class="label">Skupina strojů:</span><span class="sk_stroj obsah"></span></div>
                <div class="info-row"><span class="label">Vytvořil:</span><span class="vytvoril obsah"></span></div>
                <div class="info-row"><span class="label">Vytvořeno:</span><span class="vytvoreno obsah"></span></div>
                <div class="info-row"><span class="label">Upraveno:</span><span class="upraveno obsah"></span></div>
                <div class="info-row"><span class="label">Výrobek:</span><span class="vyrobek obsah"></span></div>
                <div class="info-row"><span class="label">Poznámka:</span><span class="poznamka obsah"></span></div>
            </div>
            <div class="modal-footer">
                <form action="spec_form.php" method="post">
                    <input type="submit" name="subEdit" id="subEdit" class="defButt edit" value="Editovat" title="Editace specifikace">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="typ_stroje" value="">
                </form>
                <form action="print_form.php" method="post" target="printFrame">
                    <input type="submit" name="subTisk" class="defButt print" id="subTisk" value="Tisk" title="Tisk specifikace">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="typ_stroje" value="">
                </form>
                <iframe id="frame" name="printFrame" style="display: none;"></iframe>
                <form action="print_form.php" method="post">
                    <input type="submit" name="subTisk" class="defButt print" id="subNahl" value="Zobrazit" title="Zobrazení celé specifikace">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="typ_stroje" value="">
                </form>
                <form action="" method="post" id="delForm">
                    <input type="submit" value="Odstranit" name="subDel" id="subDel" class="defButt extend" title="Odstraní specifikaci z databáze">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="typ_stroje" value="">
                </form>
            </div>
        </div>
    </div>
    <div class="footer">
        <img src="Indorama.png" width="200px">
    </div>
    <style>
        h2::after {
            content: "";
            display: block;
            width: 25%;
            height: 3px; 
            background: #d40000; 
            margin-top: 5px;
            border-radius: 2px;
        }
        table {
            width: 100%;
            max-width: 80vw;
            margin: 20px auto;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        thead {
            background-color: #5d99a3;
            color: #fff;
        }
        th {
            padding: 15px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #333;
            text-align: center;
        }
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        .info-icon {
            width: 20px;
            height: 20px;
            cursor: pointer;
            transition: transform 0.2s ease;
            vertical-align: middle;
        }

        .info-icon:hover {
            transform: scale(1.1);
        }
    
        select {
            max-width: 300px;
        }
        #selectVyr{
            padding: 0.5vw 1vw;
            margin: 0;
            width: auto;
        }
        .vyr {
            cursor: pointer;
            text-decoration: underline;
        }

        .footer{
            display: none;
        }


        @media (max-width: 710px) {
            table {
                margin: 10px auto;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                background: white;
                border-radius: 8px;
            }
            td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
            }
            td:before {
                position: absolute;
                left: 12px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                content: attr(data-label);
            }
            .info-icon {
                width: 16px;
                height: 16px;
            }

            .setting {
                flex-direction: column;
                gap: 10px;
                margin: 10px auto;
                padding: 15px;
            }
            .setting select, .setting input[type="search"] {
                max-width: 100%;
            }
            .setting button {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
</body>
</html>