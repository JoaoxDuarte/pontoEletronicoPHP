<?php

include_once("config.php");
include_once("comparecimento_consulta_medica_funcoes.php");

verifica_permissao("chefia");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    // Valores passados - encriptados
    $dados = explode(":|:", descriptografa($dadosorigem));
    $siape = $dados[0];
    $dia   = $dados[1];
    $idreg = $dados[2];
    $grupo = $dados[3]; // acompanhar ou homologar
}

$qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);

$oDBase = new DataBase('PDO');

// dados voltar
$_SESSION['voltar_nivel_2'] = "comparecimento_consulta_medica.php?dados=" . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

$mes = dataMes($dia);
$ano = dataAno($dia);


$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setCSS('css/new/sorter/css/theme.bootstrap_3.min.css');
$oForm->setJS('css/new/sorter/js/jquery.tablesorter.min.js');
$oForm->setSubTitulo( "Comparecimento a Consulta Médica" );
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// AAPAGA O REGISTRO DO COMPARECIMENTO CONSULTA MÉDICA
if ($_GET['delete-consulta'])
{
    deleteComparecimentoConsultaMedica();
    unset($_GET['delete-consulta']);
}


$oComprovanteServidor = selecionaServidoresConsultaMedicaLista( $siape, $dia );
$total_registros = $oComprovanteServidor->num_rows();

?>
<style>
    .inibir_opcao {
        opacity: 0.15;
        -moz-opacity: 0.15;
        filter: alpha(opacity=15);
    }
</style>
<script>
    
var id = "";

$(document).ready(function () 
{
    $(document).on('click', '.delete-consulta', function () {
        id   = $(this).attr('data-value');

        bootbox.confirm({
            locale: "br",
            title: "Excluir Registro",
            message: " Deseja realmente excluir este registro?", 
            buttons: {
                confirm: {
                    label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Sim</p>",
                    className: 'btn-success'
                },
                cancel: {
                    label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Não</p>",
                    className: 'btn-danger'
                }
            },
            callback: function(result) {
                executaExclusaoConsulta(result,id);
            }
        });
    });

    $(document).on('click', '.adicionar', function () {
        showProcessandoAguarde();

        window.location.href = 'comparecimento_consulta_medica_adicionar.php?dados=<?= tratarHTML($dadosorigem); ?>';
    });
});
            
function executaExclusaoConsulta(result,id)
{
    if (result)
    {
        showProcessandoAguarde();

        window.location.href = '?dados=<?= tratarHTML($dadosorigem); ?>&delete-consulta=true&id=' + id;
    }
}
</script>
<div class="container">

    <div class="row margin-10">
        <div class="row">
            <div class="col-md-2">
                <p><b>ÓRGÃO: </b><?= tratarHTML(getOrgaoByUorg( $qlotacao )); ?></p>
            </div>
            <div class="col-md-7">
                <p><b>UORG: </b><?= tratarHTML(getUorgMaisDescricao( $_SESSION['sLotacao'] )); ?></p>
            </div>
        </div>

        <div class="row">
            <form method="POST" name="form-autorizacoes" class="formip" action="#">
                <div class="col-md-9">

                </div>
                <div class="col-md-3">
                    <button type='button' class='btn btn-default adicionar' data-dismiss='modal' style="margin-left: 10%;">Adicionar</button>
                </div>
            </form>
        </div>

        <div class="col-md-12">
            <br>
            <div class="row">
                <fieldset width='90%'>Total de <?= tratarHTML($total_registros); ?> registro(s).</fieldset>
                <table id="myTable" class="table table-striped table-bordered text-center  table-hover table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-center" style='vertical-align:middle;'>Matrícula</th>
                        <th class="text-left"   style='vertical-align:middle;'>Servidor</th>
                        <th class="text-center" style='vertical-align:middle;'>Data</th>
                        <th class="text-center" style='vertical-align:middle;'>Tempo em<br>Consulta</th>
                        <th class="text-center" style='vertical-align:middle;width: 10%;'>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    $servidor_siape  = "";
                    $servidor_total  = 0;
                    $servidor_limite = 0;

                    while ($registro = $oComprovanteServidor->fetch_object()):

                        $servidor_limite = time_to_sec($registro->limite);

                        if (!empty($servidor_siape) && $servidor_siape != $registro->matricula)
                        {
                            ExibeTotalDia( $ano, $identifica_servidor, $servidor_total, $servidor_limite);
                            $servidor_total = 0;
                        }

                        $identifica_servidor = tratarHTML(substr($registro->matricula,5,11)) . " - ". tratarHTML($registro->servidor);

                        $servidor_siape  = $registro->matricula;
                        $servidor_total += time_to_sec($registro->total);

                        ?>
                        <tr>
                            <td align='center'><?= tratarHTML(removeOrgaoMatricula($registro->matricula)); ?></td>
                            <td align='left'><?= tratarHTML($registro->servidor); ?></td>
                            <td align='center'><?= tratarHTML($registro->dia); ?></td>
                            <td align='center'><?= sec_to_time(time_to_sec($registro->consulta),'hh:mm'); ?></td>
                            <td align='center'>
                                <?php if ($registro->editar == 'editar'): ?>
                                    <a class="delete-consulta" data-value="<?= tratarHTML($registro->id); ?>"><img border='0' src='<?= _DIR_IMAGEM_; ?>lixeira2.jpg' width='16' height='16' align='absmiddle' alt='Excluir' title='Excluir'></a></td>
                                <?php endif; ?>
                        </tr>
                        <?php

                    endwhile;

                    ?>
                    <tr>
                        <td colspan="3" align='right'><?= (empty($identifica_servidor) ? "" : $identifica_servidor." - <b>(".$ano.")</b>"); ?></td>
                        <td align='center' style="font-weight:bold"><?= (empty($servidor_total) ? "" : sec_to_time($servidor_total,'hh:mm')); ?></td>
                        <td align='center'>&nbsp;</td>
                    </tr>
                    <?php

                    if ($servidor_total > $servidor_limite)
                    {
                        ExibeTotalLimiteAnual( $ano, $identifica_servidor, $servidor_limite);
                    }

                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <br>
            <div class="form-group col-md-12 text-center">
                <div class="col-md-4"></div>
                <div class="col-md-2 col-xs-4">
                    <a class="btn btn-danger btn-block" id="btn-voltar"
                       href="javascript:window.location.replace('/sisref/<?= tratarHTML($_SESSION['voltar_nivel_1']); ?>')" role="button">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </div>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/*
 * FUNÇÕES AUXILIARES
 */
function ExibeTotalDia( $ano, $identifica_servidor, $servidor_total, $servidor_limite)
{
    ?>
    <tr>
        <td colspan="3" align='right'><?= tratarHTML($identifica_servidor); ?> <b>(<?= tratarHTML($ano); ?>)</b></td>
        <td align='center' style="font-weight:bold"><?= sec_to_time($servidor_total,'hh:mm'); ?></td>
        <td align='center'>&nbsp;</td>
    </tr>
    <?php

    if ($servidor_total > $servidor_limite)
    {
        ExibeTotalLimiteAnual( $ano, $identifica_servidor, $servidor_limite);
    }
}

function ExibeTotalLimiteAnual( $ano, $identifica_servidor, $servidor_limite)
{
    ?>
    <tr>
        <td colspan="3" align='right' style="color:red;font-weight:bold"><?= tratarHTML($identifica_servidor); ?> - LIMITE DE HORAS NO ANO (<?= $ano; ?>)</td>
        <td align='center' style="color:red;font-weight:bold"><?= sec_to_time($servidor_limite,'hh:mm'); ?></td>
        <td align='center'>&nbsp;</td>
    </tr>
    <?php
}
