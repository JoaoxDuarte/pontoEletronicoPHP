<?php
include_once("config.php");
include_once( "src/controllers/TabPlantoesController.php" );

verifica_permissao("plantoes");

$oPlantao = new TabPlantoesController();

if(!empty($_GET['autorizacao']))
{
    $oPlantao->delete( $_GET['id'] );
    exit;
}

$dados = $oPlantao->registrosPlantoes();

$oForm = new formPadrao();
$oForm->setCSS('css/new/sorter/css/theme.bootstrap_3.min.css');
$oForm->setJS('css/new/sorter/js/jquery.tablesorter.min.js');
$oForm->setSubTitulo( "Configuração de Plantões" );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script>
    var dados = "";

    $(document).ready(function () {
        
        $(".btn_adicionar").on('click', function () {
            showProcessandoAguarde();
            window.location.href = 'plantoes_configurar_incluir.php';
        });
        
        $(".btn_Alterar").click( function(event) {
            var destino = $(this).attr('data-rel');

            console.log(destino);

            showProcessandoAguarde();
            window.location.href = destino;
        });

        $(".btn_Excluir").click( function(event) {
            dados = $(this).attr('data-rel');

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
                    executaExclusaoPlantao(result);
                }
            });
        });
    });
            
    function executaExclusaoPlantao(result)
    {
        if (result)
        {
            showProcessandoAguarde();

            $.ajax({
                
                url: "plantoes_configurar.php",
                type: "GET",
                data: "autorizacao=true&id=" + dados,
                dataType: 'json'

            }).done(function(resultado) {
                
                console.log(resultado.mensagem,resultado.tipo);
                hideProcessandoAguarde();
                mostraMensagem( resultado.mensagem, resultado.tipo, 'plantoes_configurar.php' );

            }).fail(function(jqXHR, textStatus ) {
                
                console.log("Request failed: " + textStatus);
                hideProcessandoAguarde();

            }).always(function() {
            
                console.log("completou");
                hideProcessandoAguarde();
            });
        }
    }
</script>

<div class="container">

    <div class="row margin-10">

    <div class="col-md-10 col-md-offset-1">

            <br>
            <div class="row">
                <div class="col-md-12 text-right margin-bottom-10">
                    <button type="button" class="btn btn-default btn_adicionar" style="padding-left:30px;padding-right:30px;">Adicionar</button>
                </div>
                <fieldset class="col-md-3" width='100%'>Total de <?= $dados->num_rows(); ?> registros.</fieldset>
                <table id="myTable" class="table table-striped table-bordered table-hover text-center table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-center" style='vertical-align:middle;'>ID</th>
                        <th class="text-center" style='vertical-align:middle;'>Escala</th>
                        <th class="text-center" style='vertical-align:middle;width:30%;'>Nome do Plantão</th>
                        <th class="text-center" style='vertical-align:middle;'>Hora Inicial</th>
                        <th class="text-center" style='vertical-align:middle;'>Hora Final</th>
                        <th class="text-center" style='vertical-align:middle;'>Ativo</th>
                        <th class="text-center" style='vertical-align:middle;'>Ações</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php while ($rows = $dados->fetch_object()): ?>

                    <tr>
                        <td align='center'><?= tratarHTML($rows->id); ?></td>
                        <td align='center' alt="<?= $rows->escala_descricao; ?>" title="<?= $rows->escala_descricao; ?>"><?= tratarHTML($rows->escala_sigla); ?></td>
                        <td align='left'><?= tratarHTML($rows->descricao); ?></td>
                        <td align='center'><?= tratarHTML(substr($rows->hora_inicial,0,5)); ?></td>
                        <td align='center'><?= tratarHTML(substr($rows->hora_final,0,5)); ?></td>
                        <td align='center'><?= tratarHTML(($rows->ativo=='S'?'Sim':'Não')); ?></td>

                        <td align='right'>
                            <div class="col-md-6">
                                <a class="btn_Alterar" data-rel='plantoes_configurar_alterar.php?id=<?= $rows->id; ?>' style="cursor:pointer;"><span class="glyphicon glyphicon-pencil" alt='Editar' title='Editar'></span></a>
                            </div>
                            <div class="col-md-2">
                                <a class='btn_Excluir' data-rel='<?= $rows->id; ?>' style="cursor:pointer;"><span class="glyphicon glyphicon-trash" alt='Excluir' title='Excluir'></span></a>
                            </div>
                        </td>
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
