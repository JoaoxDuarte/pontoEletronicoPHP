<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Solicita Autorização Trabalho Dia Não Útil   |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("logado", "entrada.php");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados  = explode(":|:", base64_decode($dadosorigem));
    $sMatricula = $dados[0];
    $codmun     = $dados[1];
}

// dados passados por sessao
//$sMatricula = $_SESSION['sMatricula'];


$title = _SISTEMA_SIGLA_ . ' | Solicitação de Autorização para trabalho em dia Não Útil';

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSDialogProcessando();
$oForm->setJSDatePicker();
$oForm->setJS( "js/phpjs.js" );

$oForm->setJS( 'autorizacao_trabalho_dia_nao_util_solicitacao.js' );

$oForm->setSubTitulo( "Solicitação de Autorização para trabalho em dia Não Útil" );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// instancia o banco de dados
$oDBase = new DataBase('PDO');


// seleciona dados
$oDBase->query("SELECT cad.mat_siape, cad.nome_serv, cad.cpf, cad.cod_lot, und.descricao, cad.entra_trab, cad.ini_interv, cad.sai_interv, cad.sai_trab, cad.jornada, cad.malt, taborgao.denominacao, taborgao.sigla,cad.nome_social as nomesocial,cad.flag_nome_social as flag FROM servativ AS cad LEFT JOIN tabsetor AS und ON cad.cod_lot = und.codigo LEFT JOIN taborgao ON LEFT(und.codigo,5) = taborgao.codigo WHERE cad.excluido = 'N' AND cad.mat_siape = :siape AND cad.cod_sitcad NOT IN ('02','08','15') ", array(
    array(":siape", $sMatricula, PDO::PARAM_STR)
));

if ($oDBase->num_rows() > 0)
{
    $oServidor       = $oDBase->fetch_object();
    $tSiape          = $oServidor->mat_siape;
    $limite          = date("Y-m-d");
    $sNome           = ($oServidor->flag ==FALSE ? $oServidor->nome_serv: $oServidor->nomesocial); ;
    $sCpf            = $oServidor->cpf;
    $sLotaca         = $oServidor->cod_lot;
    $wnomelota       = $oServidor->descricao;
    $descricao_sigla = $oServidor->sigla;
    $entra           = $oServidor->entra_trab;
    $sai             = $oServidor->sai_trab;
    $intini          = $oServidor->ini_interv;
    $intsai          = $oServidor->sai_interv;
    $malt            = $oServidor->malt;
    $jd              = $oServidor->jornada / 5;
    $j               = '0' . $jd;
}
else
{
    retornaErro(pagina_de_origem(), "Servidor não está ativo ou inexistente!");
}


$oDBase->query("SELECT DATE_FORMAT(dnu.dia, '%d/%m/%Y') AS dia, dnu.autorizado, IF(IFNULL(dnu.data_autorizado,'')='','',DATE_FORMAT(dnu.data_autorizado, '%d/%m/%Y às %H:%i:%s')) AS data_autorizado FROM tabdnu AS dnu WHERE dnu.siape = :siape AND dia > :limite ORDER BY dnu.dia DESC", array(
    array(":siape",  $tSiape, PDO::PARAM_STR),
    array(":limite", $limite, PDO::PARAM_STR),
));

$html_row  = "#f8f8f1";
$disabled  = "";
$html_rows = "";

while ($pm = $oDBase->fetch_object())
{
    $aut           = ($pm->autorizado == "N" ? "AGUARDANDO AUTORIZAÇÃO" : "AUTORIZADO");
    $autorizado_em = ($pm->data_autorizado == '' ? '--------------------' : $pm->data_autorizado);

    $disabled = (inverteData($pm->dia) > date("Ymd") ? "" : "disabled");
    $html_row = ($html_row != "#f8f8f1" ? "#f8f8f1" : "#e8e8d0");

    $html_rows .= '<tr>';
    $html_rows .= '<td>' . tratarHTML($pm->dia) . '</td><td>' . tratarHTML($aut) . '</td><td>' . tratarHTML($autorizado_em) . '</td>';
    $html_rows .= '</tr>';
} // fim do while

$hoje   = date("d/m/Y");
$limite = date("Y-m-d");
$ano    = date("Y");
$mes    = date("m");

// pagina atual
$_SESSION['voltar_nivel_1'] = $_SERVER['REQUEST_URI'];

?>
<div class="container">
    <div class="row" style="padding-top:0px;">
        <div class="">

            <div class="col-md-12 margin-bottom-25"></div>

            <form id="form1" name="form1" method="POST" action="#" onsubmit="javascript:return false;">

                <input type='hidden' id="modo" name='modo' value = '5'>
                <input type="hidden" id="tSiape" name="tSiape" value = "<?= tratarHTML($tSiape); ?>">
                <input type="hidden" id="sNome" name="sNome" value = "<?= tratarHTML($sNome); ?>">
                <input type="hidden" id="dnu2" name="dnu2" value = "<?= tratarHTML($hoje); ?>">
                <input type='hidden' id="dados" name='dados' value=''>
                <input type='hidden' id="codmun" name='codmun' value='<?= tratarHTML($codmun); ?>'>

                <div class="subtitle">
                    <strong>DADOS DO SERVIDOR</strong>
                </div>

                <div class="col-md-12">
                    <div class="row">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th class="text-center text-nowrap" style='vertical-align:middle;'>Mat. SIAPE</th>
                                    <th class="text-center" style='vertical-align:middle;'>NOME</th>
                                    <th class="text-center" style='vertical-align:middle;'>ÓRGÃO</th>
                                    <th class="text-center" style='vertical-align:middle;'>UNIDADE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><h4><?= tratarHTML(removeOrgaoMatricula($tSiape)); ?></h4></td>
                                    <td class="text-center col-xs-5"><h4><?= tratarHTML($sNome); ?></h4></td>
                                    <td class="text-center text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $sLotaca )); ?></h4></td>
                                    <td class="text-center"><h4><?= tratarHTML(getUorgMaisDescricao( $sLotaca )); ?></h4></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-2 col-lg-offset-4" id="dnu-container">
                        <label class="control-label " for="dnu">
                            Data de Início
                        </label>
                        <div class="input-group date">
                            <input id="dnu" name="dnu" type="text" class="form-control" style="background-color:transparent;width:105px;" onKeyPress="formatar(this, '##/##/####')" size="10" maxlength='10' readonly/><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="control-label " for="dnu">&nbsp;</label>
                        <button type="button" id="btn-enviar" name="enviar" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> Continuar
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    <div class="row margin-30">
        <div class="subtitle">
            <strong>SITUAÇÃO DAS SOLICITAÇÕES</strong>
        </div>
        <table class="table table-striped table-condensed table-bordered table-hover text-center">
            <thead>
                <tr>
                    <th class="text-center">Data da Solicitação</th>
                    <th class="text-center">Situação</th>
                    <th class="text-center">Data/Hora</th>
                </tr>
            </thead>
            <tbody>
                <?= $html_rows; ?>
            </tbody>
        </table>
    </div>
</div>
<?php

DataBase::fechaConexao();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
