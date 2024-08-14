<?php
include_once( "config.php");
include_once("class_ocorrencias_grupos.php");

verifica_permissao("sRH e sTabServidor");

$pSiape = $_REQUEST['pSiape'];
$mes    = $_REQUEST['mes'];
$ano    = $_REQUEST['ano'];
$perfil = $_REQUEST['perfil'];
$cmd    = $_REQUEST['cmd'];
$dia    = '01/' . $mes . '/' . $ano;
$dia2   = substr("00" . numero_dias_do_mes($mes, $ano), -2) . '/' . $mes . '/' . $ano;

$pSiape = getNovaMatriculaBySiape($pSiape);

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem("Problemas no acesso ao banco de dados do Servidor!");
$oDBase->setDestino("freqinclui.php");

// dados do servidor
$oDBase->query("SELECT cad.mat_siape, cad.nome_serv, cad.cpf, cad.cod_lot, cad.cod_loc, cad.cod_sitcad, DATE_FORMAT(cad.dt_ing_lot,'%d/%m/%Y') AS dt_ing_lot, DATE_FORMAT(cad.dt_ing_loc,'%d/%m/%Y') AS dt_ing_loc, cad.jornada, und.upag, und.descricao, cad.sigregjur FROM servativ AS cad LEFT JOIN tabsetor AS und ON cad.cod_lot = und.codigo WHERE cad.mat_siape='" . $pSiape . "' AND cad.excluido = 'N' AND (cad.cod_sitcad NOT IN ('02','15')) ");

if ($oDBase->num_rows() > 0)
{
    $oServidor = $oDBase->fetch_object();
    $tSiape    = $oServidor->mat_siape;
    $sNome     = $oServidor->nome_serv;
    $upg       = $oServidor->upag;
    $lot       = $oServidor->cod_lot;
    $wnomelota = $oServidor->descricao;
    $sitcad    = $oServidor->sigregjur;
    $jn        = $oServidor->jornada;
    $jnd       = $jn / 5;
}
else
{
    //header("Location: mensagem.php?modo=5");
    mensagem("Servidor não está ativo ou inexistente!", $_SESSION['inclusaoOrigem'], 1);
}


## ocorrências grupos
$obj = new OcorrenciasGrupos();
$grupoOcorrenciasNegativasDebitos = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);


/* verifica upag para saber se é a mesma do usuario */
if ($_SESSION['sRH'] == "S" && $upg != $_SESSION['upag'])
{
    //header("Location: mensagem.php?modo=35&cmd=1");
    mensagem("Não é permitido alterar dados de servidor de outra UPAG!", $_SESSION['inclusaoOrigem'], 1);
}

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI'] . "?pSiape=$pSiape&mes=$mes&ano=$ano&perfil=$perfil&cmd=$cmd");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho($_SESSION['inclusaoCaminho']);
$oForm->setJSSelect2();
$oForm->setJS( "js/jquery.blockUI.js?v2.38" );
$oForm->setJS("freqinclui2.js");
$oForm->setOnLoad("javascript: if($('#dt_ini')) { $('#dt_ini').focus() };");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Inclus&atilde;o de Ocorr&ecirc;ncia");

// formulario
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<style>
    label{font-size:13px}
</style>
<div class="col-md-12">


<form method="POST" action="gravaregfreq1.php?modo=10" onsubmit="return verificadados()" id="form1" name="form1">
    <input type='hidden' id="jnd"     name='jnd'     value='<?= tratarHTML($jnd); ?>'>
    <input type='hidden' id="cmd"     name='cmd'     value='<?= tratarHTML($cmd); ?>'>
    <input type='hidden' id="compete" name='compete' value='<?= tratarHTML($mes . $ano); ?>'>
    <input type='hidden' id="dia"     name='dia'     value='<?= tratarHTML($dia); ?>'>
    <input type='hidden' id="dia2"    name='dia2'    value='<?= tratarHTML($dia2); ?>'>

    <div class="row" >
         <div class="col-md-12 margin-bottom-10">
                <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px; margin-bottom: 0">
                    <strong><font size="2" face="Tahoma">Dados do Servidor:</font></strong></p>
         </div>
    </div>
        <div class="row" style="border: 1px solid #ddd;">
             <div class="col-md-12 margin-bottom-10" >
                <div class="col-md-8" >
                    <label class='control-label'>Nome:</label>
                    <input type="text" name="sNome" id="sNome" class='form-control text-uppercase' value='<?= tratarHTML($sNome); ?>' size="60" maxlength="60" readonly>
                </div>
                <div class="col-md-4" >
                    &nbsp;<label class='control-label'>Mat.Siape:</label>
                    <input type="text" name="mat" id="mat" class='form-control text-uppercase' value='<?= tratarHTML(removeOrgaoMatricula($tSiape)); ?>' size="7" maxlength="7" readonly>
                </div>
            </div>
            <div class="col-md-12 margin-bottom-10" >
                <div class="col-md-2" >
                <label >Lotação atual:</label>
                <input name="lot" type="text" style="width: 130px" class='form-control text-uppercase'id="lot"  value="<?= tratarHTML($lot); ?>" size="11" readonly>
                </div>
                <div class="col-md-6">
                    <label > &nbsp;</label>
                    <input name="nomelota" type="text" class='form-control text-uppercase' id="nomelota"  value="<?= tratarHTML($wnomelota); ?>" size="70" readonly>
                </div>

                    <div class="col-md-2">
                        <label class='control-label'>Competência:</label>
                        <input name="mes" type="text" class='form-control text-uppercase' id="mes"   value="<?= tratarHTML($mes); ?>" size="6" readonly>
                    </div>

                    <div class="col-md-2">
                        <label class='control-label'> &nbsp;</label>
                        <input name="ano" type="text" class='form-control  text-uppercase' id="ano"  value="<?= tratarHTML($ano); ?>" size="8" readonly>
                    </div>




            </div>
            <div class="col-md-12 margin-bottom-25" >
                <div class="col-md-8" >
                    <label class='control-label'>
                        <br>
                        &nbsp;Codígo da Ocorrência:
                    </label>
                    <select class='form-control select2-single' name="ocor" size="1" id="ocor">
                        <?php

                        // tabela de ocorrencia
                        $oDBase->setMensagem("Problemas no acesso ao banco de dados das Ocorrências!");
                        $oDBase->query("
                        SELECT
                            oco.siapecad, oco.desc_ocorr, oco.cod_ocorr
                        FROM
                            tabocfre AS oco
                        WHERE
                            oco.resp IN ('RH' ,'AB')
                            AND (oco.siapecad NOT IN (" . implode(',', $grupoOcorrenciasNegativasDebitos) . "))
                            AND oco.ativo = 'S'
                        ORDER BY
                            oco.desc_ocorr
                        ");

                        while ($campo = $oDBase->fetch_object())
                        {
                            ?>
                            <option value="<?= tratarHTML($campo->siapecad); ?>"><?= tratarHTML($campo->siapecad) . " - " . tratarHTML(substr($campo->desc_ocorr, 0, 60)) . " -  " . (empty($campo->desc_ocorr) ? "Selecione uma ocorrência" : "SIRH") . " " . tratarHTML($campo->cod_ocorr); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>

                    <div class="col-md-2">
                        <label class='control-label'>Início da<br>Ocorrência:</label>
                        <input name="dt_ini" type="text" class='form-control  text-uppercase' id="dt_ini" size="6" maxlength="2">
                    </div>
                    <div class="col-md-2">
                        <label class='control-label'>Fim da<br>Ocorrência:</label>
                        <input name="dt_fim" type="text" class='form-control  text-uppercase' id="dt_fim2" size="6" maxlength="2">
                    </div>



            </div>
        </div>

        <div class="form-group col-md-8 text-center">
            <div class="col-md-8 col-md-offset-6 margin-10">
                <div class="col-md-7 text-center">
                    <a class="btn btn-success btn-primary" id="btn-continuar">
                        <span class="glyphicon glyphicon-ok"></span> Continuar
                    </a>
                </div>
            </div>
        </div>
    </div>

</form>
    </div>
<?php

// Base do formulário
//
$oForm->exibeBaseHTML();
