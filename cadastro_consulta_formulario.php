<?php
// Inicia a sessão e carrega as funções de uso geral
    include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
    verifica_permissao('sRH ou Chefia');

    // Atualiza os dados do servidor com o siape
    if($_POST['atualiza_servidor_siape']){
        if(updateServerBySiape($_POST['atualiza_servidor_siape'])){
            die('dados atualizados com sucesso!');
        }else{
            die('Falha ao atualizar dados!');
        }

    }

// Valores passados - encriptados
    $dadosorigem = $_REQUEST['dados'];

    if (empty($dadosorigem))
    {
        // le dados passados por formulario
        $matricula_siape  = anti_injection($_REQUEST["mat"]);
        $lotacao_servidor = '';

        // prepara o arquivo de retorno com os valores passados
        // atraves da sessao $_SESSION['sChaveCriterio']
        // Exemplo: $_SESSION['sChaveCriterio'] = array( "chave" => $var1, "escolha" => $var2 );
        //
        $destino_retorno = valoresParametros("cadastro_consulta.php");
    }
    else
    {
        // Valores passados - encriptados
        $dados            = explode(":|:", descriptografa($dadosorigem));
        $matricula_siape  = $dados[0];
        $lotacao_servidor = $dados[1];
        $pagina           = $dados[2];
        $destino_retorno  = $_SESSION['sPaginaRetorno_sucesso'];
    }

    $matricula_siape = getNovaMatriculaBySiape($matricula_siape);

// se chefia e não é do rh
    $pesquisa         = "";
    $lotacao_servidor = ($lotacao_servidor == '' ? $_SESSION['sLotacao'] : $lotacao_servidor);

    $params   = array();
    $params[] = array(':siape', $matricula_siape, PDO::PARAM_STR);

    if ($_SESSION["sRH"] != "S" && $_SESSION["sAPS"] == "S")
    {
        $where_extra = " AND cod_lot = :cod_lot ";
        $params[]    = array(':cod_lot', $lotacao_servidor, PDO::PARAM_STR);
    }

    $oTbDados = new DataBase('PDO');

    $oTbDados->query("
SELECT
    a.mat_siape, a.limite_horas, a.plantao_medico, a.ident_unica, a.mat_dtp, a.mat_Siapecad, a.nome_serv,
    a.cod_cargo, d.desc_cargo, a.cod_lot, f.descricao AS descricao_lotacao,
    a.cod_loc, g.descricao, a.cod_sitcad, c.descsitcad, a.cod_classe,
    a.cod_padrao, a.jornada, a.reg_jur_at, e.desc_rj, a.email, a.nivel,
    f.upag, a.pis_pasep, a.cpf,
    DATE_FORMAT(a.dt_nasc, '%d/%m/%Y')     AS dtnasc,      DATE_FORMAT(a.dt_adm, '%d/%m/%Y')     AS dt_adm,
    DATE_FORMAT(a.dt_ing_lot, '%d/%m/%Y')  AS dt_ing_lot,  DATE_FORMAT(a.dt_ing_car, '%d/%m/%Y') AS dt_ing_car,
    DATE_FORMAT(a.dt_ing_jorn, '%d/%m/%Y') AS dt_ing_jorn, DATE_FORMAT(a.dt_ing_loc, '%d/%m/%Y') AS dt_ing_loc,
    IFNULL(b.cod_ocorr,'') AS cod_ocorr, IFNULL(h.desc_ocorr,'') AS desc_ocorr, IFNULL(b.dt_ocorr,'') AS dt_ocorr,
    a.jornada_cargo,a.nome_social
FROM
    servativ AS a
LEFT JOIN
    exclus AS b ON a.mat_siape = b.siape
LEFT JOIN
    tabsitcad AS c ON a.cod_sitcad = c.codsitcad
LEFT JOIN
    tabcargo AS d ON a.cod_cargo = d.cod_cargo
LEFT JOIN
    tabregime AS e ON a.reg_jur_at = e.cod_rj
LEFT JOIN
    tabsetor AS f ON a.cod_lot = f.codigo
LEFT JOIN
    tabsetor AS g ON a.cod_loc = g.codigo
LEFT JOIN
    tabocorr AS h ON b.cod_ocorr = h.cod_ocorr
WHERE
    mat_siape = :siape " .
        $where_extra, $params
    );


    $oServidor       = $oTbDados->fetch_object();

    $Dataprev        = $oServidor->mat_dtp;
    $Siapecad        = $oServidor->mat_Siapecad;
    $wdatinss        = $oServidor->dt_adm;
    $dtnasc          = $oServidor->dtnasc;
    $dtcarr          = $oServidor->dt_ing_car;
    $datlot          = $oServidor->dt_ing_lot;
    $matricula       = $oServidor->mat_siape;
    $idunica         = $oServidor->ident_unica;
    $wnome           = $oServidor->nome_serv;
    $wnome_social    = $oServidor->nome_social;
    $wcargo          = $oServidor->cod_cargo;
    $wlota           = $oServidor->cod_lot;
    $loca            = $oServidor->cod_loc;
    $datloca         = $oServidor->dt_ing_loc;
    $Codsit          = $oServidor->cod_sitcad;
    $Regjur          = $oServidor->reg_jur_at;
    $classe          = $oServidor->cod_classe;
    $padrao          = $oServidor->cod_padrao;
    $Jornada         = $oServidor->jornada;
    $Jornada_cargo   = $oServidor->jornada_cargo;
    $dtjorn          = $oServidor->dt_ing_jorn;
    $Situacao        = $oServidor->descsitcad;
    $cargo_descricao = $oServidor->desc_cargo;
    $regime_juridico = $oServidor->desc_rj;
    $wnomelota       = $oServidor->descricao_lotacao;
    $nomelocal       = $oServidor->descricao;
    $email           = $oServidor->email;
    $nivel           = $oServidor->nivel;
    $pis             = $oServidor->pis_pasep;
    $cpf             = $oServidor->cpf;
    $upag            = $oServidor->upag;
    $permitehoras    = $oServidor->limite_horas;
    $plantaoMedico    = $oServidor->plantao_medico;

    $motivo = $oServidor->desc_ocorr;

    $codoc  = $oServidor->cod_ocorr;
    $dtexcl = $oServidor->dt_ocorr;

//convertendo datas para exibir
    if ($dtexcl != "")
    {
        $dtexcl = databarra($dtexcl);
    }

// verifica se existe foto
// gravada em alguma máquina
//
    $sFoto = retornaFoto($matricula);

    $oTbDados->query("SELECT a.num_funcao, a.sit_ocup, a.dt_inicio, b.desc_func FROM ocupantes AS a INNER JOIN tabfunc AS b ON a.num_funcao = b.num_funcao WHERE a.mat_siape = :siape ORDER BY IF(a.sit_ocup='T',1,2) ", array(
        array(':siape', $matricula_siape, PDO::PARAM_STR),
    ));

    $nrows_fun = $oTbDados->num_rows();

    while ($oFuncao = $oTbDados->fetch_object())
    {
        $numero_da_funcao[]        = $oFuncao->num_funcao;
        $descricao_da_funcao[]     = $oFuncao->desc_func;
        $data_ocupacao_da_funcao[] = databarra($oFuncao->dt_inicio);
        switch ($oFuncao->sit_ocup)
        {
            case 'T': $situacao_ocupacao[] = 'TITULAR';
                break;
            case 'S': $situacao_ocupacao[] = 'SUBSTITUTO';
                break;
            case 'R': $situacao_ocupacao[] = 'INTERINO';
                break;
            default: $situacao_ocupacao[] = "";
                break;
        }
    }


    switch (substr($wcargo, 0, 3))
    {
        case 434: $tipo_carreira = "SEGURO SOCIAL";
            break;
        case 424: $tipo_carreira = "PREVIDENCIARIA";
            break;
        case 810:
        case 811:
        case 812:
        case 435: $tipo_carreira = "PERITO MEDICO PREVIDENCIARIO";
            break;
        case 480:
        case 481:
        case 482: $tipo_carreira = "PGPE";
            break;
        default: $tipo_carreira = "OUTRAS";
            break;
    }


## classe para montagem do formulario padrao
#
    $oForm = new formPadrao();
    $oForm->setCSS("css/select2.min.css");
    $oForm->setCSS("css/select2-bootstrap.css");
    $oForm->setCSS("js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css");

    $oForm->setJS( "js/select2.full.js");
    $oForm->setJS( 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
    $oForm->setJS( 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js');

    if (isset($_REQUEST["chave"]))
    {
        $oForm->setIconeParaImpressao("imprimir_consulta_formulario.php?mat=0403739&amp;chave=0403739&amp;escolha=siape");
//        $oForm->setIconeParaImpressao( "pesquisa_servidor_imp.php" );
    }

// Topo do formulário
//
    $oForm->setSubTitulo("Consulta dados Funcionais");

// Topo do formulário
//
    $oForm->exibeTopoHTML();
    $oForm->exibeCorpoTopoHTML();

?>
    <style>
        .datepicker {
            background-color: #eeeeee;
        }
    </style>

    <script>
        $(document).ready(function ()
        {

            // Set the "bootstrap" theme as the default theme for all Select2
            // widgets.
            //
            // @see https://github.com/select2/select2/issues/2927
            $.fn.select2.defaults.set("theme", "bootstrap");

            var placeholder = "Selecione uma Ocorrência";

            $(".select2-single").select2({
                placeholder: placeholder,
                width: null,
                containerCssClass: ':all:'
            });
            $(".select2-single").prop("disabled", true);

            $('#dt-container .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                language: "pt-BR",
                //daysOfWeekDisabled: "0,6",
                autoclose: true,
                todayHighlight: true,
                toggleActive: true,
                enableOnReadonly: false
            });

        });

         /**
         * Atualiza os dados do servidor com as informações vindas do WsSiape
         *
         * @return void
         */
        function atualizarSiape(matricula, callback){
            var data = {
                'atualiza_servidor_siape': matricula
            };

            $.post('cadastro_consulta_formulario.php', data, function(retorno){
                alert(retorno);
                window.location.replace(callback);
            });
        }
    </script>

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="47" colspan="6" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nome:</label>
                <input type="text" id="wnome" name="wnome" value='<?= tratarHTML($wnome); ?>' size="60" maxlength="60" onkeyup="javascript:ve(this.value);" class='form-control text-uppercase' readonly>
            </td>

            <td rowspan="2" nowrap style="text-align:center;vertical-align:middle;padding:0px 5px 0px 5px;border-width:0px;">
                <p class='p2'><img src="foto/anonimo.jpg" width="82" height="110"></p>
                <a class="btn btn-primary btn-primary" id="btn-foto" href="#" style='width:82px;padding:3px 0px 3px 0px;margin:2px;' disabled>
                    <span class="glyphicon glyphicon-user"></span> Foto </a>
            </td>
        </tr>
        <tr>

            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Mat.Siape:</label>
                <input type="text" id="tSiape" name="tSiape" value='<?= removeOrgaoMatricula(tratarHTML($matricula)); ?>' size="7" maxlength="7" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Mat.Siapecad:</label>
                <input type="text" id="Siapecad" name="Siapecad" value='<?= ($_SESSION["sRH"] == "S" ? tratarHTML($Siapecad) : "********"); ?>' size="8" maxlength="8" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Identifica&ccedil;&atilde;o Única:</label>
                <input type="text" id="idunica" name="idunica" size="9" value='<?= ($_SESSION["sRH"] == "S" ? tratarHTML($idunica)   : "*********"); ?>' maxlength="9" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td height="47" colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nome Social:</label>
                <input type="text" id="wnome_social" name="wnome_social" value='<?= tratarHTML($wnome_social); ?>' size="60" maxlength="60" onkeyup="javascript:ve(this.value);" class='form-control text-uppercase' readonly>

            </td>
        </tr>
        <tr>
            <td height="47" colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Situação Funcional:</label>
                <select id="Situacao" name="Situacao" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single" readonly>
                    <?php
                        $sql = "SELECT * FROM tabsitcad WHERE codsitcad  NOT IN ('02','15') ORDER BY codsitcad";
                        print montaSelect($Codsit, $sql, $tamdescr='', $imprimir=false);
                    ?>
                </select>
            </td>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>E-mail:</label>
                <input type="text" id="email" name="email" value='<?= tratarHTML($email); ?>' size="50" maxlength="50" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>60 Horas ou DE:</label>
                <select name="limite-horas" class="form-control select2-single" readonly>

                    <?php if ($permitehoras == "SIM"): ?>
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
                <select name="plantao-medico" class="form-control select2-single" readonly>
                    <?php

                        if($plantaoMedico == "SIM"): ?>
                            <option value="SIM" selected>SIM</option>
                            <option value="NAO">NÃO</option>
                        <?php else: ?>
                            <option value="SIM">SIM</option>
                            <option value="NAO" selected>NÃO</option>
                        <?php endif; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Cargo Efetivo:</label>
                <select id="wcargo" name="wcargo" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single" readonly>
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
                <input type="text" id="nivel" name="nivel" value='<?= tratarHTML($nivel); ?>' size="2" maxlength="2" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Regime Jurídico:</label>
                <select id="Regjur" name="Regjur" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single" readonly>
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
                    <div class="input-group date datepicker">
                        <input type="text" id="wdatinss" name="wdatinss" value='<?= tratarHTML($wdatinss); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Jornada:</label>
                <input type="text" id="Jornada" name="Jornada" value='<?= tratarHTML($Jornada); ?>' size="5" maxlength="5" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Ingresso na Jornada:</label>
                    <div class="input-group date datepicker">
                        <input type="text" id="datjorn" name="datjorn" value='<?= tratarHTML($dtjorn); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Deficiente Visual:</label>
                <select id="defvis" name="defvis" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single" readonly>

                    <option value="N" <?= ($defvis == 'N' ? 'selected' : ''); ?>>N&Atilde;O </option>
                    <option value="S" <?= ($defvis == 'S' ? 'selected' : ''); ?>>SIM</option>
                </select>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>PIS-PASEP:</label>
                <input type="text" id="pis" name="pis" value='<?= tratarHTML($pis); ?>' size="11" maxlength="11" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>CPF:</label>
                <input type="text" id="cpf" name="cpf" value='<?= tratarHTML($cpf); ?>' size="11" maxlength="11" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Data de Nascimento:</label>
                    <div class="input-group date datepicker">
                        <input type="text" id="dtnasc" name="dtnasc" value='<?= tratarHTML($dtnasc); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
        </tr>
    </table>


    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Unidade de Exercício:</label>
                <select id="wlota" name="wlota" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single" readonly>
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
                    <div class="input-group date datepicker">
                        <input type="text" id="datlot" name="datlot" value='<?= tratarHTML($datlot); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Localização:</label>
                <select id="loca" name="loca" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single" readonly>
                    <?php
                        // tabela de cargo
                        $sql = "SELECT * FROM tabsetor WHERE upag = '$upag' OR codigo = '00000000000000' ORDER BY codigo";
                        print montaSelect($loca, $sql, $tamdescr='', $imprimir=false);
                    ?>
                </select>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;' disabled>
                    <label class='control-label'>Ingresso na Localiza&ccedil;&atilde;o:</label>
                    <div class="input-group date datepicker">
                        <input type="text" id="datloca" name="datloca" value='<?= tratarHTML($datloca); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
        </tr>
    </table>


    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Horário Especial:</label>
                <select id="horae" name="horae" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single" readonly>

                    <option value="N" <?= ($horae == 'N' ? 'selected' : ''); ?>>N&Atilde;O </option>
                    <option value="S" <?= ($horae == 'S' ? 'selected' : ''); ?>>SIM</option>
                </select>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Processo:</label>
                <input type="text" id="processo" name="processo" value='<?= tratarHTML($processo); ?>' size="30" maxlength="30" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Motivo:</label>
                <select id="mothe" name="mothe" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single" readonly>
                    <option value='00'>Selecione uma opção</option>
                    <?php
                        // tabela de cargo
                        $sql = "SELECT codigo, descricao, exige_data_termino FROM tabmotivo_horaespecial ORDER BY id";
                        print montaSelect($motivo, $sql, $tamdescr='', $imprimir=false);
                    ?>
                </select>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Data de Início:</label>
                    <div class="input-group date datepicker">
                        <input type="text" id="dthe" name="dthe" value='<?= tratarHTML($dthe); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Data de Encerramento:</label>
                    <div class="input-group date datepicker">
                        <input type="text" id="dthefim" name="dthefim" value='<?= tratarHTML($dthefim); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
        </tr>
    </table>


    <table width="98%" border="0" align="center">
        <tr>
            <td width="32%"><div align="center"></div></td>
            <td width="33%">
                <div class="row" align="center" >
                    <div class="col-md-5">
                        <?= botao("Atualizar Siape", "javascript:atualizarSiape('".$matricula_siape."', '" . $destino_retorno . "')", false, 'glyphicon-refresh'); ?>
                    </div>
                    <div class="col-md-4">
                        <?= botao("Voltar", "javascript:window.location.replace('" . $destino_retorno . "');"); ?>
                    </div>
                </div>
            </td>
            <td width="35%"><div align="center"></div></td>
        </tr>
    </table>

<?php
// Base do formulário
//
    $oForm->exibeCorpoBaseHTML();
    $oForm->exibeBaseHTML();
