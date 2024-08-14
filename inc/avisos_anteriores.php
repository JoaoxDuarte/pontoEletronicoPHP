<?php
$qtd               = explode('/', $_SERVER['PHP_SELF']);
$path_dots_slashes = str_repeat("../", count($qtd) - 3);

// Inicia a sessão e carrega as funções de uso geral
//include_once( $path_dots_slashes . "config.php" );

include_once( "../config.php" );

include_once( "class_avisos.php" );
?>
<html>
    <head>
        <link rel="shortcut icon" href="<?= _DIR_; ?>favicon.ico">
        <link rel="stylesheet" type="text/css" href="<?= _DIR_CSS_; ?>app.css">
    </head>
    <body style='width:300px;margin:0px;padding:0px;'>
        <br>
        <table class='align_center border_0' style='width:300px;'>
            <tr>
                <td style='vertical-align:top;'>
                    <fieldset style='text-align:justify;width:300px;font-size:11;font-family:arial;'>
                        <legend style='color:blue;font-size:9px;'>INFORMAÇÕES</legend>
                        <br>
                        <?php

                        // S: para Servidores
                        $avisos_anteriores = new avisos('S');
                        $avisos_anteriores->setLimite(0);
                        $avisos_anteriores->setDiasAnteriores(0);
                        $avisos_anteriores->sayAvisos();

                        ?>
                        <br>
                    </fieldset>
                </td>
            </tr>
        </table>
    </body>
</html>
