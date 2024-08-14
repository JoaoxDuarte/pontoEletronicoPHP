<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao( 'sRH e sTabServidor' );

// carrega variaveis com valores de sessao
include_once("cadastro_sessao_le.php");
include_once("Siape.php");


$upag = $_SESSION['upag'];

if(!empty($_GET['getInfoByApiSiape']))
{
    $obj      = new Siape();
    $cpf      = limpaCPF_CNPJ($_GET['cpf']); // remove a formatação do cpf
    $response = [];

    // quando envia código do Órgão o serviço
    // não retorna dados e fica travado
    $orgao    = substr($_SESSION['upag'],0,5); //getOrgaoByUpag($_SESSION['uorg']);

    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT servativ.cod_uorg AS lotacao , nivel , cod_sitcad FROM servativ WHERE servativ.cpf = :cpf",
        array(array(":cpf", $cpf, PDO::PARAM_STR)));

    $result = $oDBase->fetch_object();

    $nivel_vigente   = $result->nivel;
    $lotacao_vigente = $result->lotacao;
    $sitcad          = $result->cod_sitcad;

    if (empty($nivel_vigente)){
        $nivel_vigente = "";
    }

    if (empty($lotacao_vigente)){
        $lotacao_vigente = "";
    }

    // RECUPERA OS DADOS PESSOAIS
    $dadosPessoais = $obj->buscarDadosPessoais($cpf , $orgao);

    // RECUPERA OS DADOS FUNCIONAIS
    $dadosFuncionais = $obj->buscarDadosFuncionais($cpf , $orgao);
    $situacao_funcional = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codSitFuncional;

    // VALIDA O RETORNO DA API
    if(!is_object($dadosPessoais) && !is_object($dadosFuncionais)) {
        die;
    }

    if (!is_string($situacao_funcional) || empty(trim($situacao_funcional)))
    {
        $situacao_funcional = $sitcad;
    }

    switch ($dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaRegimeJuridico){
        case 'EST':
            $regime = '1';
            break;
        case 'CLT':
            $regime = '2';
            break;
        case 'ETG':
            $regime = '3';
            break;
        case 'CDT':
            $regime = '4';
            break;
        case 'NES':
            $regime = '5';
            break;
        case 'MRD':
            $regime = '6';
            break;
        case 'ANS':
            $regime = '7';
            break;
        case 'RMI':
            $regime = '8';
            break;
        case 'RMP':
            $regime = '9';
            break;
        case 'PMM':
            $regime = '10';
            break;
        default:
         $regime = '0';
    }

    $oDBase2 = new DataBase('PDO');
    $oDBase2->query("SELECT codigo from tabsetor
                          where SUBSTRING(tabsetor.codigo, 6, 12) = :codigo",
        array(array(":codigo", $dadosFuncionais->dadosFuncionais->DadosFuncionais->codUorgExercicio, PDO::PARAM_STR)));

    $result2 = $oDBase2->fetch_object();

    //MONTANDO A RESPONSE
    $response = [
        'nome'                  => $dadosPessoais->nome,
        'cod_def_fisica'        => $dadosPessoais->codDefFisica,
        'pis_pasep'             => $dadosPessoais->numPisPasep,
        'data_nasc'             => $dadosPessoais->dataNascimento,
        'cpf'                   => $cpf,
        'unidade_exercicio'     => $result2->codigo,
        'localizacao'           => $result2->codigo,
        'ingresso_unidade'      => $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataExercicioNoOrgao,
        'ingresso_localizacao'  => $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataExercicioNoOrgao,
        'matricula'             => $dadosFuncionais->dadosFuncionais->DadosFuncionais->matriculaSiape,
        'identificacao_unica'   => $dadosFuncionais->dadosFuncionais->DadosFuncionais->identUnica,
        'situacao_funcional'    => $dadosFuncionais->dadosFuncionais->DadosFuncionais->codSitFuncional,
        'email'                 => $dadosFuncionais->dadosFuncionais->DadosFuncionais->emailServidor,
        'cargo_efetivo'         => $dadosFuncionais->dadosFuncionais->DadosFuncionais->codCargo,
        'regime'                => $regime,
        'regime_juridico_nome'  => $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeRegimeJuridico,
        'jornada_cod'           => $dadosFuncionais->dadosFuncionais->DadosFuncionais->codJornada,
        'jornada_nome'          => $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeJornada,
        'lotacao'               => $dadosFuncionais->dadosFuncionais->DadosFuncionais->codUpag, // valor do sisref
        'admissao'              => $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrIngressoOrgao,
        'ingresso_jornada'      => $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataExercicioNoOrgao,
        'ingresso_lotacao'      => $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataExercicioNoOrgao,
        'nivel'                 => $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaNivelCargo // valor do sisref
    ];

    echo json_encode(array("success" => true, "response" => $response));
    die;
}


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
$oForm->setJS( "js/jquery.blockUI.js?v2.38" );
$oForm->setJS( "cadastro_inclusao.js?v.1.0.0" );

$oForm->setIconeParaImpressao( "#" );
$oForm->setSubTitulo( "Inclusão de Servidores e Estagiários" );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<form id="form1" name='form' method='post'>
        <div class="row">
            <div class="col-sm-10">
                <div class="row import" style="display: none;">
                    <div class="col-md-3 text-right">
                        <input type="text" name="cpf-import" class="form-control cpf-import" maxlength="14" onkeypress="formatar(this,'000.000.000-00')" placeholder="CPF">
                    </div>
                    <div class="col-md-5">
                        <button type='button' class='btn btn-default import-go' data-dismiss='modal'>Importar</button>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-info" id="display-button-import">Importar do SIAPE</button>
                <br><br>
            </div>
        </div>
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
                    <input type="text" id="tSiape" name="tSiape" value='<?= tratarHTML($tSiape); ?>' size="7" maxlength="7" onkeyup="javascript:ve(this.value);" class='form-control'>
                </td>
                <td nowrap class='text-left' style='border-width:0px;'>
                    <label class='control-label'>Mat.Siapecad:</label>
                    <input type="text" id="Siapecad" name="Siapecad" value='<?= ($_SESSION["sRH"] == "S" ? $Siapecad : "********"); ?>' size="8" maxlength="8" onkeyup="javascript:ve(this.value);" class='form-control'>
                </td>
                <td nowrap class='text-left' style='border-width:0px;'>
                    <label class='control-label'>Identifica&ccedil;&atilde;o Única:</label>
                    <input type="text" id="idunica" name="idunica" size="9" value='<?= ($_SESSION["sRH"] == "S" ? $idunica : "*********"); ?>' maxlength="9" onkeyup="javascript:ve(this.value);" class='form-control'>
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

                    <?php if(!empty($_SESSION['limite-horas'])): ?>

                        <select name="limite-horas" class="form-control select2-single">
                            <?php if($_SESSION['limite-horas'] == "SIM"): ?>
                                <option value="SIM" selected>SIM</option>
                                <option value="NAO">NÃO</option>
                            <?php else: ?>
                                <option value="SIM">SIM</option>
                                <option value="NAO" selected>NÃO</option>
                            <?php endif; ?>
                        </select>

                    <?php else: ?>

                        <select name="limite-horas" class="form-control select2-single">
                            <option value="SIM">SIM</option>
                            <option value="NAO" selected>NÃO</option>
                        </select>

                    <?php endif; ?>


                </td>
                <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                    <label class='control-label'>APH:</label>

                    <?php if(!empty($_SESSION['plantao-medico'])): ?>

                        <select name="plantao-medico" class="form-control select2-single">
                            <?php if($_SESSION['plantao-medico'] == "SIM"): ?>
                                <option value="SIM" selected>SIM</option>
                                <option value="NAO">NÃO</option>
                            <?php else: ?>
                                <option value="SIM">SIM</option>
                                <option value="NAO" selected>NÃO</option>
                            <?php endif; ?>
                        </select>

                    <?php else: ?>

                        <select name="plantao-medico" class="form-control select2-single">
                            <option value="SIM">SIM</option>
                            <option value="NAO" selected>NÃO</option>
                        </select>

                    <?php endif; ?>
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
                <td nowrap class='text-left' style='border-width:0px;'>
                    <label class='control-label'>Nível:</label>
                    <input type="text" id="nivel" name="nivel" value='<?= tratarHTML($nivel); ?>' size="2" maxlength="2" onkeyup="javascript:ve(this.value);" class='form-control text-uppercase'>
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
                    <input type="text" id="Jornada" name="Jornada" value='<?= tratarHTML($Jornada); ?>' size="5" maxlength="5" onkeyup="javascript:ve(this.value);" class='form-control'>
                    </p>
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
                    <label class='control-label'>Unidade de Exercício:</label>
                    <select id="wlota" name="wlota" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
                        <?php
                        // tabela de cargo
                        $sql = "SELECT * FROM tabsetor WHERE upag = '$upag' OR codigo = '00000000000000' ORDER BY codigo";
                        print montaSelect($wlota, $sql, $tamdescr='', $imprimir=false);
                        ?>
                    </select>
                </td>
                <td height="40" nowrap class='text-left' style='border-width:0px;'>
                    <div class="col-md-2" id='dt-container' style='padding:0px;'>
                        <label class='control-label'>Ingresso na Unidade:</label>
                        <div class="input-group date">
                            <input type="text" id="datlot" name="datlot" value='<?= tratarHTML($datlot); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td height="40" nowrap class='text-left' style='border-width:0px;'>
                    <label class='control-label'>Localização:</label>
                    <select id="loca" name="loca" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
                        <?php
                        // tabela de cargo
                        $sql = "SELECT * FROM tabsetor WHERE upag = '$upag' OR codigo = '00000000000000' ORDER BY codigo";
                        print montaSelect($loca, $sql, $tamdescr='', $imprimir=false);
                        ?>
                    </select>
                </td>
                <td height="40" nowrap class='text-left' style='border-width:0px;'>
                    <div class="col-md-2" id='dt-container' style='padding:0px;'>
                        <label class='control-label'>Ingresso na Localiza&ccedil;&atilde;o:</label>
                        <div class="input-group date">
                            <input type="text" id="datloca" name="datloca" value='<?= tratarHTML($datloca); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="col-md-12">
            <div class="col-md-6 col-md-offset-4">
                <div class="form-group">
                    <div class="col-md-4 col-xs-6 col-md-offset-1">
                        <a class="btn btn-success btn-block" id="btn-continuar" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Gravar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

