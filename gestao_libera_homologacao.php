<?php
// funcoes de uso geral
// Inicializa a sessão (session_start)
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao('corrigir_acesso');


$modo                = anti_injection($_REQUEST['modo']);
$modo                = (empty($modo) ? '0' : $modo);
$setor               = anti_injection($_REQUEST['setor']);
$descricao           = anti_injection($_REQUEST['descricao']);
$data_limite         = $_REQUEST['data_limite'];
$mes_ano_homologacao = anti_injection($_REQUEST['mes_ano_homologacao']);
$solicitante         = anti_injection($_REQUEST['solicitante']);
$email_solicitando   = anti_injection($_REQUEST['email_solicitando']);
$prorrogado_ate      = $_REQUEST['prorrogado_ate'];
$email_destinatarios = anti_injection($_REQUEST['email_destinatarios']);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setJSSelect2();
$oForm->setJSDialogProcessando();
$oForm->setJSDatePicker();
$oForm->setJS("gestao_libera_homologacao.js");
$oForm->setLargura('820px');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Liberar Homologação");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


/* ----------------------------------------*\
  |    FORMULARIO QUE CAPTURA OS DADOS       |
  \*---------------------------------------- */

$competencia         = new trata_datasys();
$mes_ano_homologacao = $competencia->getCompetAnterior();
$mes_ano_homologacao = substr($mes_ano_homologacao, 0, 2) . "/" . substr($mes_ano_homologacao, 2, 4);

/* -----------------------------------------*\
 |    FORMULARIO DE REGISTRO DO PEDIDO      |
 \*---------------------------------------- */
if ($modo == '0')
{
    formularioRegistroPedido();
}
else if ($modo == '1')
{
    /* -----------------------------------------*\
     |   FORMULARIO DE CONFIRMAÇÃO DOS DADOS    |
    \*----------------------------------------- */
    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT a.codigo, a.descricao, DATE_FORMAT(a.liberar_homologacao,'%d/%m/%Y') AS liberar_homologacao FROM tabsetor AS a WHERE a.codigo IN ('$setor') ");
    $nRows  = $oDBase->num_rows();
    if ($nRows == 0)
    {
        mensagem('Setor não cadastrado!', 'gestao_libera_homologacao.php?modo=0', 1);
    }
    else
    {
        $dados = $oDBase->fetch_object();
        ?>
        <div style='width: 100%; text-align: center'>
            <fieldset width='100%'><b>Confirma os dados?</b></fieldset>
            <fieldset style='width: 100%; height: 30px; vertical-align: middle;'>
                <form method="POST" action="#" onsubmit="return verificadados()" id="form1" name="form1">
                    <input type="hidden" id="modo"                name="modo"                value="2">
                    <input type="hidden" id="setor"               name="setor"               value="<?= $setor; ?>">
                    <input type="hidden" id="mes_ano_homologacao" name="mes_ano_homologacao" value="<?= $mes_ano_homologacao; ?>">
                    <input type="hidden" id="descricao"           name="descricao"           value="<?= $descricao; ?>">
                    <input type="hidden" id="prorrogado_ate"      name="prorrogado_ate"      value="<?= $prorrogado_ate; ?>">
                    <input type="hidden" id="solicitante"         name="solicitante"         value="<?= $solicitante; ?>">
                    <input type="hidden" id="email_solicitando"   name="email_solicitando"   value="<?= str_replace("'", "*", str_replace('"', "*", str_replace("\n", "<br />", $email_solicitando))); ?>">
                    <input type="hidden" id="email_destinatarios" name="email_destinatarios" value="<?= $email_destinatarios; ?>">
                    <table width="100%" cellspacing="0">
                        <tr>
                            <td align="center" valign="middle" nowrap>
                                <font style='font-size: 14px; font-weight: bold;'>
                                Prorrogação até:&nbsp;
                                </font>
                            </td>
                            <td>
                                <font style='font-size: 14px; font-weight: bold;'>
                                <input type="text" id="data_limite" name="data_limite" size="10" maxlength="10" value="<?= $prorrogado_ate; ?>" readonly style='border: 0px solid $FFFFFF'>
                                </font>
                            </td>
                        </tr>
                        <tr>
                            <td align="right" valign="middle">
                                <font style='font-size: 14px; font-weight: bold;'>
                                Setor:&nbsp;
                                </font>
                            </td>
                            <td>
                                <font style='font-size: 14px; font-weight: bold;'>
                                <input type="text" id="unidade" name="unidade" size="90" maxlength="90" value="<?= getUorgMaisDescricao( $dados->codigo ); ?>" readonly style='border: 0px solid $FFFFFF'>
                                </font>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <p align="center" style="word-spacing: 0; margin: 0">
                        <input type="button" name="enviar" alt="Submeter os valores" align="center" value='&nbsp;Sim&nbsp;' onclick='javascript:verificadados(2);'>&nbsp;&nbsp;
                        <input type="button" name="voltar" alt="Retorna sem confirmar" align="center" value='&nbsp;Não&nbsp;' onclick="window.location.replace('gestao_libera_homologacao.php?modo=0');">
                    </p>
                </form>
                <table width="100%" cellspacing="0" border='1'>
                    <tr style="background-color: #CECEFF">
                        <td align="center" valign="middle" nowrap>
                            <font style='font-size: 14px; font-weight: bold;'>&nbsp;Codigo&nbsp;</font>
                        </td>
                        <td>
                            <font style='font-size: 14px; font-weight: bold;'>&nbsp;Setor&nbsp;</font>
                        </td>
                        <td align="right" valign="middle">
                            <font style='font-size: 14px; font-weight: bold;'>&nbsp;Última Liberação&nbsp;</font>
                        </td>
                    </tr>
                    <?php
                    $oDBase->query("SELECT prorrogado_ate FROM liberacao_homologacao WHERE unidade = '$setor' ORDER BY prorrogado_ate DESC ");
                    if ($oDBase->num_rows() > 0)
                    {
                        while ($oProrrogados = $oDBase->fetch_object())
                        {
                            ?>
                            <tr>
                                <td align="center" valign="middle" nowrap>
                                    <font style='font-size: 14px; font-weight: bold;'>&nbsp;<?= $dados->codigo; ?>&nbsp;</font>
                                </td>
                                <td>
                                    <font style='font-size: 14px; font-weight: bold;'>&nbsp;<?= $dados->descricao; ?>&nbsp;</font>
                                </td>
                                <td align="right" valign="middle">
                                    <font style='font-size: 14px; font-weight: bold;'>&nbsp;<?= databarra($oProrrogados->prorrogado_ate); ?>&nbsp;</font>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    else
                    {
                        ?>
                        <tr>
                            <td align="center" valign="middle" nowrap colspan='3'>
                                <font style='font-size: 14px; font-weight: bold;'>&nbsp;Sem registros para exibir&nbsp;</font>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </fieldset>
        </div>
        </center>
        <?php
    }

    /* -----------------------------------------*\
      |            GRAVAÇÃO DOS DADOS             |
      \*----------------------------------------- */
}
else if ($modo == '2')
{
    $mes_ano_homologacao = substr($mes_ano_homologacao, 3, 4) . substr($mes_ano_homologacao, 0, 2);

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino(pagina_de_origem());
    $oDBase->query("SELECT * FROM liberacao_homologacao WHERE mes_ano_homologacao = '$mes_ano_homologacao' AND unidade = '$setor' AND prorrogado_ate = '" . conv_data($prorrogado_ate) . "' ");
    if ($oDBase->num_rows() == 0)
    {
        $oDBase->query("INSERT INTO liberacao_homologacao SET mes_ano_homologacao = '$mes_ano_homologacao', solicitante = '$solicitante', email_solicitando = '" . $email_solicitando . "', unidade = '$setor', prorrogado_ate = '" . conv_data($prorrogado_ate) . "', data_registro = now(), email_destinatarios = '$email_destinatarios' ");
    }
    else
    {
        $oDBase->query("UPDATE liberacao_homologacao SET mes_ano_homologacao = '$mes_ano_homologacao', solicitante = '$solicitante', email_solicitando = '$email_solicitando', unidade = '$setor', prorrogado_ate = '" . conv_data($prorrogado_ate) . "', data_registro = now(), email_destinatarios = '$email_destinatarios' WHERE mes_ano_homologacao = '$mes_ano_homologacao' AND unidade = '$setor' AND prorrogado_ate = '" . conv_data($prorrogado_ate) . "' ");
    }

    $oDBase->query("UPDATE tabsetor SET liberar_homologacao = '" . conv_data($prorrogado_ate) . "' WHERE codigo = '$setor' ");
    $nRows = $oDBase->affected_rows();
    if ($nRows == 0)
    {
        mensagem("Prorrogação para o Setor até $prorrogado_ate\\n$descricao, já registrada!", 'gestao_libera_homologacao.php?modo=0', 1);
    }
    else
    {
        $oDBase->query("SELECT d.email FROM tabsetor AS a LEFT JOIN tabfunc AS b ON a.codigo = b.cod_lot LEFT JOIN ocupantes AS c ON b.num_funcao = c.num_funcao LEFT JOIN servativ AS d ON c.mat_siape = d.mat_siape WHERE (a.codigo='$setor' OR (a.codigo LIKE CONCAT(SUBSTR('$setor',1,2),'7',SUBSTR('$setor',4,2),'_____') OR a.codigo LIKE CONCAT(SUBSTR('$setor',1,2),'_',SUBSTR('$setor',4,2),'7__') OR a.codigo LIKE CONCAT(SUBSTR('$setor',1,2),'15070_'))) AND a.ativo='S' AND b.resp_lot='S' AND d.chefia='S' ");

        while ($oEmail = $oDBase->fetch_object())
        {
            $email_destinatarios .= ($email_destinatarios == '' ? '' : ',') . $oEmail->email;
        }

        enviarEmail($email_destinatarios . ',sisref@previdencia.gov.br', 'PRORROGAÇÃO DA HOMOLOGACAO DA FREQUENCIA', "<br><br><big>Prezados(as) Colega(s),<br><br>Atendendo solicitação encaminhada via e-mail, excepcionalmente estamos prorrogando a homologação da unidade $setor - $descricao, até $prorrogado_ate.<br>Salientamos, que as unidades deverão, rigorosamente, homologar as frequências dos servidores dentro do prazo legal estipulado, pois o não cumprimento poderá ocasionar prejuízos para os colegas.<br>Informamos que esta solicitação está sendo registrada para futuras consultas.<br>Atenciosamente,<br><br>Equipe SISREF<br></big><br><br>");
        $oDBase->query("UPDATE liberacao_homologacao SET email_destinatarios = '$email_destinatarios' WHERE mes_ano_homologacao = '$mes_ano_homologacao' AND unidade = '$setor' AND prorrogado_ate = '" . conv_data($prorrogado_ate) . "' ");

        mensagem('Setor liberado para homologação até ' . $prorrogado_ate . '.', 'gestao_libera_homologacao.php?modo=0', 1);
    }
}


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



function formularioRegistroPedido()
{
    global $mes_ano_homologacao, $wdatinss, $email_destinatarios, $solicitante, $email_solicitando;

    ?>
    <form method="POST" action="#" onsubmit="return verificadados(1)" id="form1" name="form1">
        <input type="hidden" id="modo" name="modo" value="1">

        <div class="row " style="padding-bottom:5px;">
            <div class="col-md-12 table table-condensed">
                <div class="col-md-12 margin-bottom-10 margin-10">
                    <div class="col-md-2">
                        <label class="control-label">Competência:</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="mes_ano_homologacao" name="mes_ano_homologacao" size="8" maxlength="8" value="<?= $meo_homologacao; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-2 ">
                        <label class="control-label">Código:</label>
                    </div>
                    <div class="col-md-9">
                        <select id="loca" name="setor" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
                            <?php
                            // tabela de cargo
                            $sql = "SELECT * FROM tabsetor WHERE upag = '".$_SESSION['upag']."' OR codigo = '00000000000000' ORDER BY codigo";
                            print montaSelect($loca, $sql, $tamdescr='', $imprimir=false);
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-12 margin-bottom-10" id="dt-container" style="margin-top:0px;padding-top:10px;">
                    <div class="col-md-2">
                        <label class="control-label">Data Limite:</label>
                    </div>
                    <div class="col-md-1 input-group date" style="padding-left:15px;">
                        <input type="text"
                               class='form-control'
                               id="prorrogado_ate"
                               name="prorrogado_ate"
                               size="10"
                               maxlength="10"
                               value='<?= $wdatinss; ?>'
                               OnBlur="javascript:ve(this.value);"
                               OnKeyPress="formatar(this, '##/##/####')"
                               style="background-color:transparent;width:105px;" />
                            <span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="col-md-12 margin-bottom-10">
                    <div class="col-md-2 ">
                        <label class="control-label">Destinatários:</label>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control"
                                  id="email_destinatarios"
                                  name="email_destinatarios"
                                  rows="4" cols="81">
                            <?= $email_destinatarios; ?>
                        </textarea>
                    </div>
                </div>
                <div class="col-md-12 margin-bottom-10">
                    <div class="col-md-2 ">
                        <label class="control-label">Solicitante:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control"
                               type="text"
                               id="solicitante"
                               name="solicitante"
                               size="93"
                               maxlength="100"
                               value='<?= $solicitante; ?>' />
                    </div>
                </div>
                <div class="col-md-12 margin-bottom-10">
                    <div class="col-md-2 ">
                        <label class="control-label">Cópia do Email:</label>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control"
                                  id="email_solicitando"
                                  name="email_solicitando"
                                  rows="10"
                                  cols="81">
                            <?= $email_solicitando; ?>
                        </textarea>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-2 col-md-offset-4">
                            <button type="submit" id="btn-continuar" class="btn btn-success btn-block">
                                <span class="glyphicon glyphicon-ok"></span> Continuar
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <script>
        $('#setor').focus();
    </script>
    <?php
}