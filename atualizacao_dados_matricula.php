<?php

include_once( "config.php" );

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


$oDBase = new DataBase('PDO');

// RECUPERA O PRÓXIMO SCRIPT A SER RODADO
$oDBase->query("SELECT * FROM controle_atualizacao WHERE executado = 'N' ORDER BY id ASC limit 1");
$script = $oDBase->fetch_row()['script'];

// BUSCA OS SERVIDORES PARA QUE ASSIM SEJAM ATUALIZADOS
$oDBase->query("SELECT cod_uorg , mat_siape FROM servativ");

if(!empty($_POST['atualizar'])) {

    switch ($script){
        case "SCRIPT_1":

                script1();
                $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_1';");

            break;

        case "SCRIPT_2":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)){
                    script2($servidor->mat_siape , $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_2';");

            break;

        case "SCRIPT_3":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script3($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_3';");
            break;

        case "SCRIPT_4":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script4($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_4';");
            break;

        case "SCRIPT_5":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script5($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_5';");

            break;

        case "SCRIPT_6":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script6($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_6';");

            break;

        case "SCRIPT_7":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script7($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_7';");

            break;

        case "SCRIPT_8":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script8($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_8';");

            break;

        case "SCRIPT_9":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script9($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_9';");

            break;

        case "SCRIPT_10":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script10($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_10';");

            break;

        case "SCRIPT_11":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script11($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_11';");

            break;

        case "SCRIPT_12":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script12($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_12';");

            break;

        case "SCRIPT_13":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script13($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_13';");

            break;

        case "SCRIPT_14":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script14($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_14';");

            break;

        case "SCRIPT_15":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script15($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_15';");

            break;

        case "SCRIPT_16":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script16($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_16';");

            break;

        case "SCRIPT_17":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script17($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_17';");

            break;

        case "SCRIPT_18":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script18($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_18';");

            break;

        case "SCRIPT_19":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script19($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_19';");

            break;

        case "SCRIPT_20":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script20($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_20';");

            break;

        case "SCRIPT_21":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script21($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_21';");

            break;

        case "SCRIPT_22":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script22($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_22';");

            break;

        case "SCRIPT_23":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script23($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_23';");

            break;

        case "SCRIPT_24":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script24($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_24';");

            break;

        case "SCRIPT_25":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script25($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_25';");

            break;

        case "SCRIPT_26":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script26($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_26';");

            break;

        case "SCRIPT_27":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script27($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_27';");

            break;

        case "SCRIPT_28":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script28($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_28';");

            break;

        case "SCRIPT_29":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script29($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_29';");

            break;

        case "SCRIPT_30":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script30($servidor->mat_siape, $servidor->cod_uorg);
                }

            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_30';");

            break;

        case "SCRIPT_31":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script31($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_31';");

            break;

        case "SCRIPT_32":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script32($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_32';");

            break;

        case "SCRIPT_33":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script33($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_33';");

            break;

        case "SCRIPT_34":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script34($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_34';");

            break;

        case "SCRIPT_35":

            while ($servidor = $oDBase->fetch_object()) {

                if(!empty($servidor->mat_siape) && !empty($servidor->cod_uorg)) {
                    script35($servidor->mat_siape, $servidor->cod_uorg);
                }
            }

            $oDBase->query("UPDATE controle_atualizacao SET executado = 'S' WHERE script = 'SCRIPT_35';");

            break;

        default:
            return false;
    }

}

// RECUPERA QUANTOS SCRIPTS AINDA NÃO FORAM RODADOS
$oDBase->query("SELECT * FROM controle_atualizacao WHERE executado = 'N'");
$restantes = $oDBase->num_rows();

?>

<!DOCTYPE html>

<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Atualização do banco de dados do SISREF</title>
</head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<script>
    $(document).ready(function () {
        $(document).on('click', '.atualizar', function () {
            $(this).prop('disabled', true);
            $('.form-update').submit();
        });
    });
</script>
<body>
<div class="container">
    <div class="container-fluid pb-video-container">
        <div class="col-md-10 col-md-offset-1">
            <h3 class="text-center">Atualização do banco de dados do SISREF
                <a>
                    <?php if ($restantes): ?>
                    <form method="post" action="atualizacao_dados_matricula.php" class="form-update">
                        <input type="hidden" name="atualizar" value="true">
                        <button type="button" class="btn btn-default atualizar"><span class="glyphicon glyphicon-play"></span>
                            Atualizar
                        </button>
                    </form>
                    <?php endif; ?>
                </a>
            </h3>
            <?php if ($restantes): ?>
                <div class="row pb-row">
                    <p class="text-center" style="font-size: 15px;">Ainda faltam <?= tratarHTML($restantes); ?> SCRIPTS, para que a atualização se complete!</p>
                </div>
            <?php else: ?>
                <div class="row pb-row">
                    <p class="text-center" style="font-size: 15px;">Atualização realizada com sucesso!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>




