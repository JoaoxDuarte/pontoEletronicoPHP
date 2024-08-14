<?php
// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );

verifica_permissao('sRH ou sTabServidor');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // le dados passados por formulario
    $var1 = urldecode($_REQUEST["codigo"]);
}
else
{
    // Valores passados - encriptados
    $dados = explode(":|:", base64_decode($dadosorigem));
    $var1  = $dados[0];
}

$var1 = (empty($var1) ? $_SESSION['sTablotaCodigo'] : $var1);

// prepara o arquivo de retorno com os valores passados
// atraves da sessao $_SESSION['sChaveCriterio']
// Exemplo: $_SESSION['sChaveCriterio'] = array( "chave" => $var1, "escolha" => $var2 );
//
$destino_retorno = valoresParametros("tablota.php");

// se chefia e não é do rh
$pesquisa = $_SESSION['sSQLPesquisa'];

// dados
$oDBase = CarregaDadosDosSetores( $var1 );

$oSetor          = $oDBase->fetch_object();
$tCodigo         = $oSetor->codigo;
$sDescricao      = $oSetor->descricao;
$sUorg           = $oSetor->cod_uorg;
$upag            = $oSetor->upag;
$upai            = $oSetor->uorg_pai;
$sUg             = $oSetor->ug;
$sAtivo          = $oSetor->ativo;
$area            = $oSetor->area;
$inicio          = $oSetor->inicio_atend;
$fim             = $oSetor->fim_atend;
$periodo_excecao = $oSetor->periodo_excecao;
$sigla           = $oSetor->sigla;
$codmun          = $oSetor->codmun;
$uf              = $oSetor->uf;
$cidade          = $oSetor->cidade;
$fuso_horario    = $oSetor->fuso_horario;
$horario_verao   = $oSetor->horario_verao;


if (($_SESSION['sAdmCentral']=='S' || $_SESSION['sSenhaI']=='S'))
{
    $alterar_uorgs = true;
}
else if (($_SESSION['sRH'] == "S" && $_SESSION['sTabServidor'] == "S"))
{
    $alterar_uorgs = false;
}



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setJS("tablota_alterar.js");
$oForm->setSubTitulo("Tabela de Setores - Alteração");

// Topo do formul?rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML('1350px');


?>

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <?php exibeDescricaoOrgaoUorg($tCodigo); // Exibe órgão e Uorg com suas descrições padrão: $_SESSION['sLotacao']; ?>

    <form method="POST" action="javascript:void(0);" onsubmit="return false" id="form1" name="form1" >

    <table class="table table-striped text-center table-bordered table-condensed tablesorter margin-10" width='100%' align='center'>
        <tr>
            <td colspan='13'>
                <label>Descrição:</label>
                <input type="text" id="descricao" name="descricao" class="form-control uppercase"
                       value="<?= $sDescricao; ?>" size="110" maxlength="110">
            </td>
        </tr>
        <tr>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>UORG</label></th>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>UORG PAI</label></th>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>UPAG</label></th>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>UG</label></th>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>Área</label></th>
            <th class="text-center" style="vertical-align:middle;" colspan="2">
                <label>Atendimento</label></th>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>Ativo</label></th>
            <th class="text-center" style="vertical-align:middle;width:77px;" rowspan="2">
                <label>Período de Exceção para Banco de Horas?</label></th>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>Sigla</label></th>
            <th class="text-center" style="vertical-align:middle;width:312px;" rowspan="2">
                <label>Município</label></th>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>FUSO HORÁRIO</label></th>
            <th class="text-center" style="vertical-align:middle;" rowspan="2">
                <label>HORÁRIO DE VERÃO</label></th>
        </tr>
        <tr>
            <th class="text-center" style="vertical-align:middle;background-color:#FFFFFF;">
                <label>Início:</label></th>
            <th class="text-center" style="vertical-align:middle;background-color:#FFFFFF;">
                <label>Fim:</label></th>
        </tr>
        </tr>
        <tr>
            <td class="text-center" style="background-color:#FFFFFF;">
                <input type="hidden" id="sUorgAntes" name="sUorgAntes" value="<?= $sUorg; ?>">
                <input type="text" id="sUorg" name="sUorg" class="form-control"
                       value="<?= $sUorg; ?>" size="14" maxlength="14" style="width:140px;" <?= ($alterar_uorgs == false ? "readonly" : ""); ?>>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">
                <input type="text" id="upai" name="upai" class="form-control"
                       value="<?= $upai; ?>" size="14" maxlength="14" style="width:140px;" <?= ($alterar_uorgs == false ? "readonly" : ""); ?>>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">

                <input type="text" id="upag" name="upag" class="form-control"
                       value="<?= $upag; ?>" size="14" maxlength="14" style="width:140px;" <?= ($alterar_uorgs == false ? "readonly" : ""); ?>>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">

                <input type="text" id="sUg" name="sUg" class="form-control"
                       value="<?= $sUg; ?>" size="6" maxlength="6" style="width:75px;" <?= ($alterar_uorgs == false ? "readonly" : ""); ?>>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">

                <input type="text" id="area" name="area" class="form-control"
                       value="<?= $area; ?>" size="2" maxlength="2" style="width:40px;" <?= ($alterar_uorgs == false ? "readonly" : ""); ?>>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">
                <input type="text" id="inicio" name="inicio"
                       class="form-control horas"
                       value="<?= $inicio; ?>"
                       size="8" maxlength="8" style="width:80px;">
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">
                <input type="text" id="fim" name="fim"
                       class="form-control horas"
                       value="<?= $fim; ?>"
                       size="8" maxlength="8" style="width:80px;">
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">
                <select id="sAtivo" name="sAtivo" class="form-control" style="width:70px;">
                    <option value="N"<?= ($sAtivo != 'S' ? ' selected' : ''); ?>>Não</option>
                    <option value="S"<?= ($sAtivo == 'S' ? ' selected' : ''); ?>>Sim</option>
                </select>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">
                <select id="excessao" name="excessao" class="form-control" style="width:77px;">
                    <?php if($periodo_excecao == "SIM"): ?>
                        <option value="SIM" selected>SIM</option>
                        <option value="NAO">NÃO</option>
                    <?php else: ?>
                        <option value="SIM">SIM</option>
                        <option value="NAO" selected>NÃO</option>
                    <?php endif; ?>
                </select>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">
                <input type="text" id="sigla" name="sigla" class="form-control" style="width:110px;" value="<?= $sigla; ?>" size="12" maxlength="12" <?= ($alterar_uorgs == false ? "readonly" : ""); ?>>
            </td>
            <td class="text-center"  style="background-color:#FFFFFF;">
                <SELECT id="codmun" name="codmun" size="1" class="form-control select2-single text-left" title="Selecione uma opção!">
                    <?= montaSelectDadosDosMunicipios( $codmun ); ?>
                </SELECT>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">
                <input type="text" id="fuso_horario" name="fuso_horario" class="form-control"
                       value="<?= $fuso_horario; ?>" size="2" maxlength="2" <?= ($alterar_uorgs == false ? "readonly" : ""); ?>>
            </td>
            <td class="text-center" style="background-color:#FFFFFF;">
                <select id="horario_verao" name="horario_verao" class="form-control" style="width:77px;" <?= ($alterar_uorgs == false ? "disable" : ""); ?>>
                    <?php if($horario_verao == "S"): ?>
                        <option value="S" selected>SIM</option>
                        <option value="N">NÃO</option>
                    <?php else: ?>
                        <option value="S">SIM</option>
                        <option value="N" selected>NÃO</option>
                    <?php endif; ?>
                </select>
            </td>
        </tr>
    </table>

    <div class="row">
        <br>
        <div class="form-group col-md-12 text-center">
            <div class="col-md-2"></div>
            <div class="col-md-2 col-xs-4 col-md-offset-2">
                <a class="btn btn-success btn-block" id="btn-salvar" role="button">
                    <span class="glyphicon glyphicon-ok"></span> Salvar
                </a>
            </div>
            <div class="col-md-2 col-xs-4">
                <a class="btn btn-danger btn-block" id="btn-voltar"
                   href="tablota.php" role="button">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>

    </form>

<?php

unset($_SESSION['sTablotaCodigo']);

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
