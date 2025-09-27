<?php
    $connOptions = array("Database" => "Viscord", "UID" => "", "PWD" => "", "CharacterSet"=>"UTF-8");
    $conn = sqlsrv_connect("MRAZEKNTB-GB\SQLEXPRESS", $connOptions);
    if ($conn == false)
        die(print_r(sqlsrv_errors(), true));

    register_shutdown_function(function () use ($conn) {
        if ($conn) {
            sqlsrv_close($conn);
        }
    });
?>