<?php

// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao( 'sRH e sTabServidor' );

set_time_limit( 10000 );

//definindo a competencia de homologacao
$oData           = new trata_datasys();
$mes             = $oData->getMesAnterior();
$ano             = $oData->getAnoAnterior();
$_SESSION['mes'] = $mes;
$_SESSION['ano'] = $ano;

// dados do usu�rio logado
$setor = $_SESSION['sLotacao'];
$upag = $_SESSION['upag'];

$dados = base64_encode( $mes . ':|:' . $ano . ':|:' . $upag );


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo( "Relat�rio de Servidores Cedidos e Descentralizados no m�s de homologa��o" );
$oForm->setJS( "js/phpjs.js" );
$oForm->setJS( "src/views/relatorios/cedidos_descentralizados.js?v.0.6.2.0.3" );
$oForm->setLargura();
$oForm->setSeparador( 0 );

//$oForm->setIconeParaImpressao( 'imprimir' );

$oForm->setObservacaoBase( "
    Observa��o: Com as informa��es encaminhadas pelo �rg�o cession�rio ou de exerc�cio descentralizado o Recursos Humanos/Gest�o de Pessoas<br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    deve lan�ar a frequ�ncia do servidor por interm�dio do menu 'Frequ�ncia->Atualizar->M�s em homologa��o'.
    ",
    "left"
);

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML("1153px");

?>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php  ?>

    <?php exibeDescricaoOrgaoUorg($setor); // Exibe �rg�o e Uorg com suas descri��es padr�o: $_SESSION['sLotacao'] ?>

    <div class="row margin-25">
        <div class="row">
            <div class="col-md-2 col-md-offset-2 text-right" style="margin-top: 8px;">
                <p style='padding-top:20px'><b>Filtrar registros: </b></p>
            </div>
            <div class="col-md-5">
                Digite a matr�cula, ou parte do nome, ou unidade.<br>
                <input type='text' id='dados_filtrar' name='dados_filtrar' class='form-control' class='alinhadoAEsquerda' value='' size='50' maxlength="50">
            </div>
            <div class="col-md-2 text-right">
                <br>
                <button class="btn btn-success btn-block" id="botao_filtrar" role="button" onclick="startTask();" style="display:block;">
                    <span class="glyphicon glyphicon-ok"></span> Filtrar
                </button>
            </div>
        </div>
    </div>

    <div class="row margin-10">
        <div>
                    <!-- <input type="button" id='interromper' onclick="stopTask();"  value="Interromper" style='display:;'/> -->
                    <!-- <input type="button" id='reiniciar' onclick="startTask();" value="Reiniciar" style='display:none;'/> -->
        </div>
    </div>

    <table class="table table-striped table-bordered text-center table-hover margin-25">
        <thead>
            <tr style="border:1px solid white">
                <td colspan="5" style="text-align:bottom;padding:0px;border:0px solid white;">
                    <div class="row">
                        <div class="col-md-4 text-left" id="total_de_registros">
                            Total de <?= number_format( $num, 0, ',', '.' ); ?> registros.
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="text-center" colspan="5"><h4><b><?= $mes . '/' . $ano; ?></b></h4></th>
            </tr>
            <tr>
                <th class="text-center" style="width: 1%; height: 25px; text-align: center;">SEQ</th>
                <th class="text-center" style="width: 13%; text-align: center;">MATR&Iacute;CULA</th>
                <th class="text-left"   style="width: 60%; text-align: left; text-indent: 3px;">NOME</th>
                <th class="text-center" style="width: 12%; text-align: center;">LOTA&Ccedil;&Atilde;O</th>
                <th class="text-center" style="width: 12%; text-align: center;">SITUA��O</th>
                <th class="text-center" style="width: 14%; text-align: center;">A&Ccedil;&Atilde;O</th>
            </tr>
        </thead>
        <tbody id='registros_selecionados' class='sse_listar'>
        </tbody>
    </table>
</div>
<?php
// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
