<?php

include_once( "config.php");
include_once( "src/controllers/TabBancoDeHorasCiclosController.php");

verifica_permissao("sAPS");

$dia = (isset($dia) ? $dia : date('d/m/Y'));
$cmd = (isset($cmd) ? $cmd : '2');
$orig = (isset($orig) ? $orig : '1');
$qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);

$vDatas = conv_data($dia);
$mes = dataMes($dia);
$ano = dataAno($dia);
$comp = $mes . $ano;


if ($mes != date('m'))
    header("Location: acessonegado.php");


$oDBase = new DataBase('PDO');


if($_GET['delete-autorization']){

    // HISTÓRICO
    $oDBase->query( "
        INSERT INTO autorizacoes_servidores_usufruto_historico 
        SELECT 
            0, 
            autorizacoes_servidores_usufruto.siape, 
            autorizacoes_servidores_usufruto.ciclo_id, 
            autorizacoes_servidores_usufruto.data_inicio, 
            autorizacoes_servidores_usufruto.data_fim, 
            autorizacoes_servidores_usufruto.tipo_autorizacao, 
            'E', 
            :acao_siape, 
            NOW()
        FROM 
            autorizacoes_servidores_usufruto
        WHERE 
            autorizacoes_servidores_usufruto.siape = :siape 
            AND autorizacoes_servidores_usufruto.data_inicio = :ini 
            AND autorizacoes_servidores_usufruto.data_fim = :fim 
            AND autorizacoes_servidores_usufruto.tipo_autorizacao = 'parcial'
    ", array(
        array(":siape",      $_GET['siape'],          PDO::PARAM_STR),
        array(":ini",        $_GET['data_inicial'],   PDO::PARAM_STR),
        array(":fim",        $_GET['data_final'],     PDO::PARAM_STR),
        array(":acao_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
    ));

    $oDBase->query("DELETE FROM autorizacoes_servidores_usufruto
                        WHERE autorizacoes_servidores_usufruto.siape = :siape AND
                              autorizacoes_servidores_usufruto.data_inicio = :ini AND
                              autorizacoes_servidores_usufruto.data_fim = :fim AND
                              autorizacoes_servidores_usufruto.tipo_autorizacao = 'parcial'
    ", array(
        array(":siape", $_GET['siape'],        PDO::PARAM_STR),
        array(":ini",   $_GET['data_inicial'], PDO::PARAM_STR),
        array(":fim",   $_GET['data_final'],   PDO::PARAM_STR),
    ));

    registraLog("Deletado autorização ".$_GET['siape']);

    if ($oDBase->affected_rows() > 0)
    {
        setMensagemUsuario('Exclusão de Autorização de Usufruto realizada com sucesso!','success');
        replaceLink("autorizacoes_usufruto.php");
    }
    else
    {
        setMensagemUsuario('Exclusão de Autorização de Usufruto NÃO realizada!','danger');
        replaceLink("autorizacoes_usufruto.php");
    }
}

// Instancia as classes
$tabBancoDeHorasCiclosController = new TabBancoDeHorasCiclosController();
$dadosServidoresController       = new DadosServidoresController(); // Include da class no functions.php/config.php


// Se há POST do ID
if(!empty($_POST))
{
    $id = $_POST['id'];
}
else
{
    // ID do Ciclo corrente/atual
    $id = $tabBancoDeHorasCiclosController->getCicloCurrent();
}

    
// seleciona os registros para homologação
$oServidores      = $dadosServidoresController->selecionaServidoresUnidadeBancoHorasUsufruto($id); 
$total_servidores = $oServidores->num_rows();

$boxCiclos = boxCiclos();



$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setSeparador(0);

$css = array();
$javascript = array();

$css[] = 'css/new/sorter/css/theme.bootstrap_3.min.css';
$javascript[] = 'css/new/sorter/js/jquery.tablesorter.min.js';
$oForm->exibeTopoHTML();

?>
<style>
    .inibir_opcao {
        opacity: 0.15;
        -moz-opacity: 0.15;
        filter: alpha(opacity=15);
    }
</style>
<script>
    $(document).ready(function () {
        $(function () {
            $('#myTable').tablesorter();
            $('.ciclos').change(function(){
                $("[name='filterhdn']").val(true);
                $("[name='form-autorizacoes']").submit();
            });
        });
        $(document).on('click', '.total', function () {
            var cicloid = $("[name='hdncicloid']").val();
            window.location.href = 'autorizacao_usufruto.php?ciclo_id=' + cicloid + '&tiposolicitacao=total';
        });
        $(document).on('click', '.parcial', function () {
            var cicloid = $("[name='hdncicloid']").val();
            window.location.href = 'autorizacao_usufruto.php?ciclo_id=' + cicloid + '&tiposolicitacao=parcial';
        });
    });
</script>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Autorização de Usufruto</strong></h4>
        </div>

        <div class="row">
            <div class="col-md-2 text-right">
                <p><b>ÓRGÃO: </b><?= tratarHTML(getOrgaoByUorg( $qlotacao )); ?></p>
            </div>
            <div class="col-md-7 text-right">
                <p><b>UORG: </b><?= tratarHTML(getUorgMaisDescricao( $_SESSION['sLotacao'] )); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2 text-right" style="margin-top: 8px;">
                <p><b>Selecione o ciclo: </b></p>
            </div>
            <div class="col-md-4 text-left">
                <form method="POST" name="form-autorizacoes" action="autorizacoes_usufruto.php">
                    <select class="form-control ciclos" name="id">

                        <?php while ($ciclo = $boxCiclos->fetch_assoc()): ?>

                            <?php if($ciclo['id'] == $id): ?>
                                <option selected value="<?= tratarHTML($ciclo['id']); ?>"> <?= tratarHTML($ciclo['ciclo']); ?></option>
                                <?php $ano_do_ciclo_excolhido = substr($ciclo['ciclo'],-4); ?>
                            <?php else: ?>
                                <option value="<?= tratarHTML($ciclo['id']); ?>"> <?= tratarHTML($ciclo['ciclo']); ?></option>
                            <?php endif; ?>

                        <?php  endwhile; ?>

                    </select>
                </form>
            </div>
            <div class="col-md-5">
                <button type='button' class='btn btn-default parcial' data-dismiss='modal'>Autorização Parcial</button>
                <!--
                <button type='button' class='btn btn-default total' data-dismiss='modal' >Autorização Total</button>
                -->
                <input type="hidden" name="hdncicloid" value="<?= tratarHTML($id); ?>">
            </div>
        </div>

        <div class="col-md-12">
            <br>
            <div class="row">
                <fieldset>
                    <div style="float:left;width:49%;text-align:left;">Total de <?= tratarHTML($total_servidores); ?> registros.</div>
                    <div style="float:right;width:49%;text-align:right;">Saldo, Usufruto e Acumulos referentes ao ano de <b><?= tratarHTML($ano_do_ciclo_excolhido); ?></b>.</div>
                </fieldset>
                <table id="myTable" class="table table-striped table-hover table-bordered text-center table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-center" style='vertical-align:middle;'>Matrícula</th>
                        <th class="text-center" style='vertical-align:middle;'>Nome do Servidor</th>
                        <th class="text-center" style='vertical-align:middle;'>Período Autorizado</th>
                        <th class="text-center" style='vertical-align:middle;'>Acumulo</th>
                        <th class="text-center" style='vertical-align:middle;'>Usufruto</th>
                        <th class="text-center" style='vertical-align:middle;'>Saldo</th>
                        <th class="text-center" style='vertical-align:middle;'>Modalidade</th>
                        <th class="text-center" style='vertical-align:middle;'>Ações</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php while ($servidor = $oServidores->fetch_object()): ?>

                        <tr>
                            <td align='center'><?= tratarHTML(removeOrgaoMatricula( $servidor->siape )); ?></td>
                            <td align='center'><?= tratarHTML($servidor->nome); ?></td>
                            <td align='center'><?= tratarHTML($servidor->periodo); ?></td>
                            <td align='right'><?= tratarHTML(sec_to_time($servidor->acumulo,'hh:mm')); ?></td>
                            <td align='right'><?= tratarHTML(sec_to_time($servidor->usufruto,'hh:mm')); ?></td>
                            <td align='right'><?= ($servidor->saldo < 0 ? "-" : "") . tratarHTML(sec_to_time(abs($servidor->saldo),'hh:mm')); ?></td>
                            <td align='center'><?= tratarHTML($servidor->modalidade); ?></td>

                            <?php if($servidor->acao === 'bloqueia'): ?>
                                
                                <td align='center'>
                                    <img style="cursor: pointer" border='0' src='<?= _DIR_IMAGEM_; ?>warning.png' 
                                         width='16' height='16' align='absmiddle' 
                                         alt='Período vencido/Sem saldo' 
                                         title='Período vencido/Sem saldo'>
                                </td>
                                
                            <?php else: ?>
                            
                                <td align='center'>
                                    <a href='autorizacoes_usufruto.php?delete-autorization=true&siape=<?= tratarHTML($servidor->siape); ?>&data_inicial=<?= tratarHTML($servidor->data_inicial); ?>&data_final=<?= tratarHTML($servidor->data_final); ?>'><span class="glyphicon glyphicon-trash" alt="Excluir" title="Excluir"></span>
                                    </a>
                                </td>
                            
                            <?php endif; ?>
                                
                        </tr>

                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
