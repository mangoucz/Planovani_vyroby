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
    <div class="setting">
        <select name="stroj">
            <option value="0">Všechny specifikace</option>
            <option value="1">Staré Stroje</option>
            <option value="2">Staré stroje + Barmag</option>
            <option value="3">Nové stroje</option>
        </select>
            <button type="button" class="defButt" onclick="window.location.href = 'spec_form.php'">Nová specifikace</button>
    </div>
    <table>
        <thead>
            <tr>
                <th>Č. spec.</th>
                <th>Titr</th>
                <th>Skup. titrů</th>
                <th>Skup. strojů</th>
                <th>Vytvořeno</th>
                <th>Výrobek</th>
                <th>info</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td data-label="Č. spec.">25001</td>
                <td data-label="Titr">1220</td>
                <td data-label="Skup. titrů">1220</td>
                <td data-label="Skup. strojů">3</td>
                <td data-label="Vytvořeno">27.9.2025</td>
                <td data-label="Výrobek">Nevybrán</td>
                <td data-label="info"><img src="info.png" alt="Podrobnosti" class="info-icon link" id=""></td>
            </tr>
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
                <div class="info-row"><span class="label">Zadal:</span><span class="zadal obsah"></span></div>
                <div class="info-row"><span class="label">Vytvořeno:</span><span class="vytvoreno obsah"></span></div>
                <div class="info-row"><span class="label">Upraveno:</span><span class="upraveno obsah"></span></div>
                <div class="info-row"><span class="label">Poznámka:</span><span class="poznamka obsah"></span></div>
            </div>
            <div class="modal-footer">
                <form action="nove.php" method="post">
                    <input type="submit" name="subEdit" id="subEdit" class="defButt edit" value="Editovat" title="Editace specifikace">
                    <input type="hidden" name="id" value="">
                </form>
                <form action="print_form.php" method="post" target="printFrame">
                    <input type="submit" name="subTisk" class="defButt print" id="subTisk" value="Tisk" title="Tisk specifikace">
                    <input type="hidden" name="id" value="">
                </form>
                <form action="print_form.php" method="post">
                    <input type="submit" name="subTisk" class="defButt print" id="subNahl" value="Zobrazit" title="Zobrazení celé specifikace">
                    <input type="hidden" name="id" value="">
                </form>
                <iframe id="frame" name="printFrame" style="display: none;"></iframe>
                <form action="" method="post">
                    <input type="submit" value="Odstranit" name="subDel" id="subDel" class="defButt extend" title="Odstraní specifikaci z databáze">
                    <input type="hidden" name="id" value="">
                </form>
            </div>
        </div>
    </div>
    <div class="footer">
        <img src="Indorama.png" width="200px">
    </div>
    <style>
        table {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            border-collapse: collapse;
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
        
        .setting {
            max-width: 900px;
        }
        select {
            max-width: 300px;
        }

        .footer{
            display: none;
        }


        @media (max-width: 660px) {
            table {
                margin: 10px;
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
                margin: 10px;
                padding: 15px;
            }
            .setting select {
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