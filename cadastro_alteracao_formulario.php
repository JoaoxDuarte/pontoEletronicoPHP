<?php

// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    $sMatricula = anti_injection($_REQUEST["mat"]);
    $chave      = anti_injection($_REQUEST["chave"]);
    $escolha    = anti_injection($_REQUEST["escolha"]);
}
else
{
    $dados = explode(':|:',descriptografa($dadosorigem));
    $sMatricula = $dados[0];
}

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

$destino = 'cadastro_alteracao.php';


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCSS("css/select2.min.css");
$oForm->setCSS("css/select2-bootstrap.css");
$oForm->setCSS("js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css");

$oForm->setJS( "js/funcoes_valida_cpf_pis.js" );
$oForm->setJS( "js/select2.full.js");
$oForm->setJS( 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
$oForm->setJS( 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js');

$oForm->setJS( "cadastro_alteracao_formulario.js?v.0.0.0.0.0.2" );

$oForm->setIconeParaImpressao( "#" );
$oForm->setSubTitulo( "Alteração de Servidores/Estagiários" );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


$upag = $_SESSION['upag'];

// instancia banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino( $destino );
$oDBase->setMensagem("Problema no acesso ao banco de dados do Servidor!");

// seleciona dados do servidor
$oDBase->query("
SELECT
    cad.nome_serv, cad.mat_siape, cad.mat_siapecad, cad.ident_unica, cad.limite_horas, cad.plantao_medico,
    cad.cod_sitcad, cad.email, cad.cod_cargo, cad.nivel, cad.reg_jur_at,
    cad.jornada, cad.defvis, cad.pis_pasep, cad.cpf, und.upag, cad.cod_lot,
    cad.cod_loc, cad.horae, cad.processo, cad.motivo,
    DATE_FORMAT(cad.dt_adm,     '%d/%m/%Y') AS dt_adm,
    DATE_FORMAT(cad.dt_ing_jorn,'%d/%m/%Y') AS dt_ing_jorn,
    DATE_FORMAT(cad.dt_nasc,    '%d/%m/%Y') AS dt_nasc,
    DATE_FORMAT(cad.dt_ing_lot, '%d/%m/%Y') AS dt_ing_lot,
    DATE_FORMAT(cad.dt_ing_loc, '%d/%m/%Y') AS dt_ing_loc,
    DATE_FORMAT(cad.dthe,       '%d/%m/%Y') AS dthe,
    DATE_FORMAT(cad.dthefim,    '%d/%m/%Y') AS dthefim,
    cad.jornada_cargo,cad.nome_social
FROM
    servativ AS cad
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
WHERE
    cad.mat_siape = :siape
",
array(
    array( ':siape', $sMatricula, PDO::PARAM_STR ),
));

$oServidor   = $oDBase->fetch_object();

$wnome    = $oServidor->nome_serv;
$wnome_social = $oServidor->nome_social;
$tSiape   = $oServidor->mat_siape;
$Siapecad = $oServidor->mat_siapecad;
$idunica  = $oServidor->ident_unica;
$Situacao = $oServidor->cod_sitcad;
$email    = $oServidor->email;
$wcargo   = $oServidor->cod_cargo;
$nivel    = $oServidor->nivel;
$Regjur   = $oServidor->reg_jur_at;
$wdatinss = $oServidor->dt_adm;
$Jornada  = $oServidor->jornada;
$Jornada_cargo  = $oServidor->jornada_cargo;
$dtjorn   = $oServidor->dt_ing_jorn;
$defvis   = $oServidor->defvis;
$pis      = $oServidor->pis_pasep;
$cpf      = $oServidor->cpf;
$dtnasc   = $oServidor->dt_nasc;
$wlota    = $oServidor->cod_lot;
$datlot   = $oServidor->dt_ing_lot;
$loca     = $oServidor->cod_loc;
$datloca  = $oServidor->dt_ing_loc;

$horae    = $oServidor->horae;
$processo = $oServidor->processo;
$mothe    = $oServidor->motivo;
$dthe     = $oServidor->dthe;
$dthefim  = $oServidor->dthefim;
$limitehoras  = $oServidor->limite_horas;
$plantaoMedico = $oServidor->plantao_medico;

$upg      = $oServidor->upag;

$_SESSION['cad_upg'] = $upg;

$jo = $oServidor->jornada;

// carrega variaveis com valores de sessao
if ((isset($_SESSION['cad_wnome']) && !empty($_SESSION['cad_wnome']))
    || (isset($_SESSION['cad_tSiape']) && !empty($_SESSION['cad_tSiape'])))
{
    include_once( "cadastro_sessao_le.php" );
}

// testa se upag eh a mesma do usuario
if ($_SESSION["sLog"] != "S" && $upg != $upag)
{
    mensagem("Não é permitido alterar dados de servidor de outra UPAG!", $destino, 1);
}

if (file_exists(_DIR_FOTO_ . $tSiape . ".jpg"))
{
    $sFoto = 1; // O arquivo existe
}
else
{
    $sFoto = 2; // O arquivo não existe
}

$anonimo = _DIR_FOTO_ . "anonimo.jpg";

?>
<form id="form1" name='form' method='post' action="#" onsubmit="javascript:return false;">
    <input type="hidden" id="upg" id="upg" value="<?= tratarHTML($upg); ?>">

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="47" colspan="6" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nome:</label>
                <input type="text" id="wnome" name="wnome" value='<?= tratarHTML($wnome); ?>' size="60" maxlength="60" onkeyup="javascript:ve(this.value);" class='form-control text-uppercase'>
            </td>

            <td rowspan="2" nowrap style="text-align:center;vertical-align:middle;padding:0px 5px 0px 5px;border-width:0px;">
                <p class='p2'><img src="foto/anonimo.jpg" width="82" height="110"></p>
                <a class="btn btn-primary btn-primary" id="btn-foto" href="enviaarquivo2.php?siape=<?= tratarHTML($tSiape); ?>" style='width:82px;padding:3px 0px 3px 0px;margin:2px;'>
                <span class="glyphicon glyphicon-user"></span> Foto </a>
            </td>
        </tr>
        <tr>

            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Mat.Siape:</label>
                <input type="text" id="tSiape" name="tSiape" value='<?= tratarHTML(removeOrgaoMatricula($tSiape)); ?>' size="7" maxlength="7" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Mat.Siapecad:</label>
                <input type="text" id="Siapecad" name="Siapecad" value='<?= ($_SESSION["sRH"] == "S" ? tratarHTML($Siapecad) : "********"); ?>' size="8" maxlength="8" onkeyup="javascript:ve(this.value);" class='form-control'>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Identifica&ccedil;&atilde;o Única:</label>
                <input type="text" id="idunica" name="idunica" size="9" value='<?= ($_SESSION["sRH"] == "S" ? tratarHTML($idunica) : "*********"); ?>' maxlength="9" onkeyup="javascript:ve(this.value);" class='form-control'>
            </td>
            <td height="47" colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nome Social:</label>
                <input type="text" id="wnome_social" name="wnome_social" value='<?= tratarHTML($wnome_social); ?>' size="60" maxlength="60" onkeyup="javascript:ve(this.value);" class='form-control text-uppercase'>

            </td>
        </tr>
        <tr>
            <td height="47" colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Situação Funcional:</label>
                <select id="Situacao" name="Situacao" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
                    <?php
                    $sql = "SELECT * FROM tabsitcad WHERE codsitcad  NOT IN ('02','15') ORDER BY codsitcad";
                    print montaSelect($Situacao, $sql, $tamdescr='', $imprimir=false);
                    ?>
                </select>
            </td>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>E-mail:</label>
                <input type="text" id="email" name="email" value='<?= tratarHTML($email); ?>' size="50" maxlength="50" onkeyup="javascript:ve(this.value);" class='form-control'>
            </td>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>60 Horas ou DE:</label>
                <select name="limite-horas" class="form-control select2-single">
                    <?php if($limitehoras == "SIM"): ?>
                        <option value="SIM" selected>SIM</option>
                        <option value="NAO">NÃO</option>
                    <?php else: ?>
                        <option value="SIM">SIM</option>
                        <option value="NAO" selected>NÃO</option>
                    <?php endif; ?>
                </select>
            </td>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>APH:</label>
                <select name="plantao-medico" class="form-control select2-single">
                    <?php if($plantaoMedico == "SIM"): ?>
                        <option value="SIM" selected>SIM</option>
                        <option value="NAO">NÃO</option>
                    <?php else:?>
                        <option value="SIM">SIM</option>
                        <option value="NAO" selected>NÃO</option>
                    <?php endif; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Cargo Efetivo:</label>
                <select id="wcargo" name="wcargo" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
                    <?php
                    // tabela de cargo
                    $sql = "SELECT * FROM tabcargo ORDER BY cod_cargo";
                    print montaSelect($wcargo, $sql, $tamdescr='', $imprimir=false);
                    ?>
                </select>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Jornada do Cargo:</label>
                <input type="text" id="Jornada_cargo" name="Jornada_cargo" value='<?= tratarHTML($Jornada_cargo); ?>' size="5" maxlength="5" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>

            <td  nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nível:</label>
                <input type="text" id="nivel" name="nivel" value='<?= tratarHTML($nivel); ?>' size="2" maxlength="2" onkeyup="javascript:ve(this.value);" class='form-control'>
            </td>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Regime Jurídico:</label>
                <select id="Regjur" name="Regjur" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
                    <?php
                    // tabela de cargo
                    $sql = "SELECT * FROM tabregime ORDER BY cod_rj";
                    print montaSelect($Regjur, $sql, $tamdescr='', $imprimir=false);
                    ?>
                </select>
            </td>
        </tr>
    </table>

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;border-width:0px;'>
                    <label class='control-label'>Admissão:</label>
                    <div class="input-group date">
                        <input type="text" id="wdatinss" name="wdatinss" value='<?= tratarHTML($wdatinss); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    </div>
                </div>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Jornada:</label>
                <input type="hidden" name="jornada_real" id="jornada_real" value="<?= tratarHTML($jo); ?>">
                <input type="text" id="Jornada" name="Jornada" value='<?= tratarHTML($Jornada); ?>' size="5" maxlength="5" onkeyup="javascript:ve(this.value);" class='form-control'>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Ingresso na Jornada:</label>
                    <div class="input-group date">
                        <input type="text" id="datjorn" name="datjorn" value='<?= tratarHTML($dtjorn); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    </div>
                </div>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Deficiente Visual:</label>
                <select id="defvis" name="defvis" size="1" onkeyup="javascript:ve(this.value);" class="form-control">
                    <option value="N" <?= ($defvis == 'N' ? 'selected' : ''); ?>>N&Atilde;O </option>
                    <option value="S" <?= ($defvis == 'S' ? 'selected' : ''); ?>>SIM</option>
                </select>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>PIS-PASEP:</label>
                <input type="text" id="pis" name="pis" value='<?= tratarHTML($pis); ?>' size="11" maxlength="11" onkeyup="javascript:ve(this.value);" class='form-control'>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>CPF:</label>
                <input type="text" id="cpf" name="cpf" value='<?= tratarHTML($cpf); ?>' size="11" maxlength="11" onkeyup="javascript:ve(this.value);" class='form-control'>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Data de Nascimento:</label>
                    <div class="input-group date">
                        <input type="text" id="dtnasc" name="dtnasc" value='<?= tratarHTML($dtnasc); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Horário Especial:</label>
                <select id="horae" name="horae" size="1" onkeyup="javascript:ve(this.value);" class="form-control">
                    <option value="N" <?= ($horae == 'N' ? 'selected' : ''); ?>>N&Atilde;O </option>
                    <option value="S" <?= ($horae == 'S' ? 'selected' : ''); ?>>SIM</option>
                </select>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Processo:</label>
                <input type="text" id="processo" name="processo" value='<?= tratarHTML($processo); ?>' size="30" maxlength="30" onkeyup="javascript:ve(this.value);" class='form-control'>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Motivo:</label>
                <select id="mothe" name="mothe" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
                    <option value='00'>Selecione uma opção</option>
                    <?php
                    // tabela de cargo
                    $sql = "SELECT codigo, descricao, exige_data_termino FROM tabmotivo_horaespecial ORDER BY id";
                    print montaSelect($mothe, $sql, $tamdescr='', $imprimir=false);
                    ?>
                </select>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Data de Início:</label>
                    <div class="input-group date">
                        <input type="text" id="dthe" name="dthe" value='<?= tratarHTML($dthe); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    </div>
                </div>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Data de Encerramento:</label>
                    <div class="input-group date">
                        <input type="text" id="dthefim" name="dthefim" value='<?= tratarHTML($dthefim); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="form-group row">
        <font size="1"><b>Obs:</b><br>
        Para alterar a situa&ccedil;&atilde;o cadastral do servidor para aposentado utilize no m&oacute;dulo de exclus&atilde;o as ocorr&ecirc;ncias de exclus&atilde;o por aposentadoria 02031, 02032, 02211, 02080, 02074, 02129, 01124 ou as iniciadas com 05;<br>
        Para alterar a situa&ccedil;&atilde;o cadastral do servidor para instituidor utilize no m&oacute;dulo de exclus&atilde;o as ocorr&ecirc;ncias de exclus&atilde;o 01120 e 01121.</font>
    </div>

    <div class="form-group">
        <div class="col-md-12">
            <div class="col-md-2 col-md-offset-4">
                <button type="submit" id="btn-continuar" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-ok"></span> Gravar
                </button>
            </div>
            <div class="col-md-2">
                <a class="btn btn-danger btn-block" href="cadastro_alteracao.php">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
