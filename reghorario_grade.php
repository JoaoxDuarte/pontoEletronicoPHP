<?php
// Inicializa a sessão (session_start)
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao('diretoria_rh_aud_adm_chefia');

// Le os dados passados via formulario
$lotacao = (isset($_REQUEST['lotacao']) ? anti_injection($_REQUEST['lotacao']) : $_SESSION["sLotacao"]);


// conexao com o banco de dados
$oDBase = new DataBase('PDO');

/* obtem lista de servidores da unidade e dados do horário de cada servidor */
$oDBase->query("
SELECT
    cad.mat_siape AS siape,
    LTRIM(RTRIM(cad.nome_serv)) AS nome,
    IF(IFNULL(chf.sit_ocup,'') IN ('T','R','I'),'S','N') AS chefia,
    IF((IFNULL(chf.resp_lot,'') = 'S') AND (IFNULL(chf.sit_ocup,'') IN ('T','R','I')),'R','') AS chefia_responsavel,
    IFNULL((SELECT IFNULL(jh.entra_trab,'00:00:00') FROM jornada_historico AS jh WHERE jh.siape = cad.mat_siape AND DATE_FORMAT(NOW(),'%Y-%m-%d') >= jh.data_inicio ORDER BY jh.data_inicio DESC, jh.data_registro DESC LIMIT 1),'00:00:00') AS horario_de_entrada,
    IFNULL((SELECT IFNULL(jh.ini_interv,'00:00:00') FROM jornada_historico AS jh WHERE jh.siape = cad.mat_siape AND DATE_FORMAT(NOW(),'%Y-%m-%d') >= jh.data_inicio ORDER BY jh.data_inicio DESC, jh.data_registro DESC LIMIT 1),'00:00:00') AS saida_para_almoco,
    IFNULL((SELECT IFNULL(jh.sai_interv,'00:00:00') FROM jornada_historico AS jh WHERE jh.siape = cad.mat_siape AND DATE_FORMAT(NOW(),'%Y-%m-%d') >= jh.data_inicio ORDER BY jh.data_inicio DESC, jh.data_registro DESC LIMIT 1),'00:00:00') AS retorno_do_almoco,
    IFNULL((SELECT IFNULL(jh.sai_trab,'00:00:00') FROM jornada_historico AS jh WHERE jh.siape = cad.mat_siape AND DATE_FORMAT(NOW(),'%Y-%m-%d') >= jh.data_inicio ORDER BY jh.data_inicio DESC, jh.data_registro DESC LIMIT 1),'00:00:00') AS horario_de_saida,
    cad.area       AS `area`,
    cad.cod_sitcad AS cod_sitcad,
    IF(IF(IFNULL(chf.sit_ocup,'') IN ('T','R','I'),'S','N') = 'S', '40', IF(IFNULL(tes.autorizacao,'2')='1',jh.jornada,cad.jornada)) AS jornada,
    cad.jornada    AS jornada_oficial,
    cad.bhoras     AS bhoras,
    cad.bh_tipo    AS bh_tipo,
    cad.dt_adm     AS dt_adm,
    cad.oco_exclu_dt AS oco_exclu_dt,
    cad.horae               AS horae,
    IFNULL(cad.processo,'') AS processo,
    cad.motivo  AS motivo,
    cad.dthe    AS dthe,
    cad.dthefim AS dthefim,
    cargo.cod_cargo  AS cod_cargo,
    cargo.desc_cargo AS desc_cargo,
    IF(IFNULL(tes.autorizacao,'2')='1','S','N') AS turno_estendido,
    DATE_FORMAT(jh.data_fim,'%d-%m-%Y')         AS data_fim,
    DATE_FORMAT(jh.data_inicio,'%d-%m-%Y')      AS data_inicio,
    cad.email AS email
FROM
    servativ AS cad
LEFT JOIN
    ocupantes AS chf ON (cad.mat_siape = chf.mat_siape) AND (IFNULL(chf.sit_ocup,'') IN ('T','R','I'))
LEFT JOIN
    tabcargo AS cargo ON (cad.cod_cargo = cargo.cod_cargo)
LEFT JOIN
    jornada_historico AS jh ON (cad.mat_siape = jh.siape) AND (cad.cod_lot = jh.cod_lot) AND (DATE_FORMAT(NOW(),'%Y-%m-%d') >= jh.data_inicio) AND jh.data_inicio = ANY (SELECT MAX(jornada_historico.data_inicio) FROM jornada_historico WHERE (jornada_historico.siape = cad.mat_siape) AND jornada_historico.cod_lot = cad.cod_lot AND DATE_FORMAT(NOW(),'%Y-%m-%d') >= jornada_historico.data_inicio GROUP BY jornada_historico.siape ORDER BY jornada_historico.data_inicio DESC, jornada_historico.data_registro DESC)
LEFT JOIN
    turno_estendido_supervisao AS tes ON (cad.cod_lot = tes.unidade_id) AND tes.ativo='S' AND (tes.data_efetivacao = ANY (SELECT turno_estendido_supervisao.data_efetivacao FROM turno_estendido_supervisao WHERE turno_estendido_supervisao.unidade_id = tes.unidade_id AND DATE_FORMAT(NOW(),'%Y-%m-%d') >= turno_estendido_supervisao.data_efetivacao ORDER BY turno_estendido_supervisao.data_efetivacao DESC))
WHERE
    (cad.excluido = 'N')
    AND (cad.cod_sitcad NOT IN ('08','66'))
    AND (cad.cod_lot = :lotacao)
GROUP BY
    cad.mat_siape
ORDER BY
    cad.nome_serv, jh.data_inicio DESC
",
array(
    array( ':lotacao', getNovaUorg($lotacao), PDO::PARAM_STR ),
));

$nNumServidores = $oDBase->num_rows();

$_SESSION['hSiape']         = $siape;
$_SESSION['hLotacao']       = $lotacao;
$_SESSION['hUorg']          = $cod_uorg;
$_SESSION['hJornada']       = $jornada;
$_SESSION['hData_inicio']   = $h_data_inicio;
$_SESSION['hEntra_trab']    = $h_entra_trab;
$_SESSION['hIni_interv']    = $h_ini_interv;
$_SESSION['hSai_interv']    = $h_sai_interv;
$_SESSION['hSai_trab']      = $h_sai_trab;
$_SESSION['hNumServidores'] = $nNumServidores;

imprimir_cabecalho();

if ($oDBase->num_rows() == 0)
{
    imprimir_sem_registro();
}
else
{
    $linhas        = 0;
    $tabindex      = 1;
    $limite_linhas = 33;

    $resumoTotal = array();

//		$oDBase->data_seek();
    while ($oServidor = $oDBase->fetch_object())
    {
        if ($linha > $limite_linhas)
        {
            $linha = 0;
            imprimir_rodape($turno_estendido);
            imprimir_cabecalho();
        }

        imprimir_grade_horario();

        $linha++;
    } // fim do while
    imprimir_rodape($turno_estendido);

    $linha     = 0;
    $qtd_total = 0;
    ksort($resumoTotal);
    imprimir_cabecalho('1', $resumo    = true);
    imprimir_resumo();
    imprimir_rodape($turno_estendido);
}


####################################################
#                                                  #
#  IMPRIMIR CABEÇALHO                              #
#                                                  #
####################################################
#

function imprimir_cabecalho($quebra = '0', $resumo = false)
{
    global $tabindex, $lotacao;

    // obtem informações da unidade
    $oSetor = seleciona_dados_da_unidade($lotacao);

    if ($quebra == '0')
    {
        ?>
        <html>
            <head>
                <title></title>
                <meta http-equiv="Content-Language" content="pt-br">
                <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
                <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>estilo.css'>
                <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>class_formpadrao.css'>
                <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>estilos.css'>
                <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>estilosIE.css'>
                <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>plugins/dlg.css'>
                <link type='text/css' rel='stylesheet' href='<?= _DIR_CSS_; ?>print3b.css'>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>funcoes.js'></script>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>fc_data.js'></script>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>desativa_teclas_f_frames.js'></script>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>jquery.js'></script>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>sorttable.js'></script>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>jquery.blockUI.js?v2.38'></script>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>plugins/jquery.dlg.min.js'></script>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>plugins/jquery.easing.js'></script>
                <script type='text/javascript' src='<?= _DIR_JS_; ?>jquery.ui.min.js'></script>
                <script type='text/javascript' src='reghorario_grade.js'></script>
                <style>
                    .bold { font-weight: bold; }
                    .left { text-align: left; }
                    .center { text-align: center; }
                    .right { text-align: right; }
                    .vtop { vertical-align: top; }
                    .vcenter { vertical-align: middle; }
                    .borda0px { border: 0px solid white; }
                    .titulo_colunas { height: 20px; background-color: "#DFDFBF"; font-size: 10px; }
                    .linhas { height: 20px; font-size: 10px; }
                    .campos { font-size: 11px; border: 0px solid white; }
                    .margem2px { padding: 2px; }
                </style>
            </head>

            <body bgcolor="#FFFFFF" >

        <?php
    }

    ?>
    <table class="table table-striped table-bordered text-center" style='width: 650px; <?= ($quebra != 0 ? "page-break-before: always;" : ""); ?>'>
        <tr>
            <td>

                <fieldset align='center' style='width: 100%; height: 100%;'>
                    <form id='form1' name="form1" method="POST" action="">
                        <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0">
                            <b>
                                <h3>
                                <div align='center'>
                                    <h3>
                                    <table class="table table-striped table-bordered text-center">
                                        <tr>
                                            <td style='width: 105px; border: 0px solid #808080;'>
                                                <?php

                                                if ($_SESSION["sAPS"] == 'S' && $tabindex == 0)
                                                {
                                                    print "<a id='prepara_impressao' title='Preparar Página para Impressão' href='javascript:imprimirGrade();'><img id='imagemPrinter' src='" . _DIR_IMAGEM_ . "printer.gif' height=40 border='0' style='display: ;'></a>";
                                                }
                                                else
                                                {
                                                    print "&nbsp;";
                                                }

                                                ?>
                                            </td>
                                            <td style=' width: 60%; height: 60px; vertical-align: middle;' nowrap><p align='center' class='ft_18_001'>SISREF - Sistema de Registro Eletrônico de Frequência</p></td>
                                            <td width='20%'><img src='<?= _DIR_IMAGEM_; ?>transp1x1.gif' height='40' width='105px' border='0'></td>
                                        </tr>
                                    </table>

                                    <table align='center' border='0' width="100%" cellspacing="0" cellpadding="0"><tr><td colspan='3' align='center'><font class='ft_16_001'>QUADRO DE HORÁRIO DOS SERVIDORES DA UNIDADE</font></td></tr></table>

                                    </h3>
                                </div>

                                <table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
                                    <tr>
                                        <td colspan="3" width="80%" height="20" bgcolor="#DFDFBF"><div align="center"><font size="1"><b>Descrição</b></font></div></td>
                                        <td width="20%" height="20" bgcolor="#DFDFBF"><div align="center"><font size="1"><b>Hor&aacute;rio do setor</b></font></div></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <div align="center">
                                                <font class='ft_13_002'><?= tratarHTML($oSetor->denominacao); ?></font><br>
                                                <font class='ft_13_002'><?= tratarHTML($oSetor->unidade_master); ?></font><br>
                                                <font class='ft_14_002'><?= tratarHTML(substr($lotacao, 5) . " - " . $oSetor->descricao); ?></font><br>
                                            </div>
                                        </td>
                                        <td>
                                            <div align="center">
                                                <font size="1">
                                                <input name="inicio" type="text"  class='centro' id="inicio" value="<?= tratarHTML($oSetor->inicio_atend); ?>" size="10" readonly>
                                                <br>
                                                &agrave;s
                                                <br>
                                                <input name="fim" type="text"  class='centro' id="fim" value="<?= tratarHTML($oSetor->fim_atend); ?>" size="10" readonly>
                                                </font>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="4">

                                            <table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
    <?php

    if ($resumo == false)
    {
        imprimir_titulo_colunas();
    }
    else
    {
        imprimir_titulo_colunas_resumo();
    }
}


####################################################
#                                                  #
#  IMPRIMIR TITULO DAS COLUNAS                     #
#                                                  #
####################################################
#
function imprimir_titulo_colunas()
{
    ?>
    <tr>
        <td class="titulo_colunas margem2px center">&nbsp;Seq.&nbsp;</td>
        <td class="titulo_colunas margem2px center" width="12%">&nbsp;Matrícula&nbsp;</td>
        <td class="titulo_colunas margem2px left"   width="72%">&nbsp;Nome</td>
        <td class="titulo_colunas margem2px center" width="9%">Jornada por Semana</td>
        <td class="titulo_colunas margem2px center" width="9%">&nbsp;Entrada&nbsp;</td>
        <td class="titulo_colunas margem2px center" width="9%">Saída para Almoço</td>
        <td class="titulo_colunas margem2px center" width="9%">Volta do Almoço</td>
        <td class="titulo_colunas margem2px center" width="9%">&nbsp;Saida&nbsp;</td>
        <td class="titulo_colunas margem2px center">Visto</td>
    </tr>
    <?php
}


####################################################
#                                                  #
#  IMPRIMIR GRADE DE HORÁRIOS DOS SERVIDORES       #
#                                                  #
####################################################
#
function imprimir_grade_horario()
{
    global $oServidor, $tabindex, $resumoTotal, $turno_estendido;

    // horário de serviço do servidor
    $horario_de_entrada = substr($oServidor->horario_de_entrada, 0, 5);
    $saida_para_almoco  = substr($oServidor->saida_para_almoco, 0, 5);
    $retorno_do_almoco  = substr($oServidor->retorno_do_almoco, 0, 5);
    $horario_de_saida   = substr($oServidor->horario_de_saida, 0, 5);

    if ($oServidor->chefia == 'S')
    {
        $formJornada = $oServidor->jornada . ($oServidor->chefia_responsavel == "R" ? "&nbsp;(R)" : "&nbsp;(F)");
    }
    else
    {
        $formJornada       = ($turno_estendido == 'S' && $oServidor->jornada_oficial >= 40 ? 'TE' : $oServidor->jornada);
        $saida_para_almoco = ($turno_estendido == 'S' || $oServidor->jornada_oficial < 40 ? "-----" : substr($oServidor->saida_para_almoco, 0, 5));
        $retorno_do_almoco = ($turno_estendido == 'S' || $oServidor->jornada_oficial < 40 ? "-----" : substr($oServidor->retorno_do_almoco, 0, 5));
    }

    $formHorarios               = $horario_de_entrada . '_' . $horario_de_saida . '_' . $formJornada;
    $resumoTotal[$formHorarios] += 1;

    ?>
    <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' style='background-color: white;'>
        <td class="linhas center vcenter margem2px"><?= $tabindex++; ?></td>
        <td class="linhas center vcenter margem2px"><?= tratarHTML($oServidor->siape); ?></td>
        <td class="linhas left   vcenter margem2px"><?= tratarHTML($oServidor->nome); ?></td>
        <td class="linhas center vcenter margem2px bold"><?= $formJornada; ?>
            <input type='hidden' id='jornada<?= tratarHTML($oServidor->siape); ?>' name='jornada<?= tratarHTML($oServidor->siape); ?>' value='<?= tratarHTML($oServidor->jornada); ?>'>
        </td>
        <td class="center vcenter">
            <input type='text' id='entrada<?= tratarHTML($oServidor->siape); ?>' name='entrada<?= tratarHTML($oServidor->siape); ?>' value='<?= tratarHTML($horario_de_entrada); ?>' size='5' maxlength='5' class="campos center vcenter" readonly>
        </td>
        <td class="center vcenter">
            <input type='text' id='entrada<?= tratarHTML($oServidor->siape); ?>' name='entrada<?= tratarHTML($oServidor->siape); ?>' value='<?= tratarHTML($saida_para_almoco); ?>' size='5' maxlength='5' class="campos center vcenter" readonly>
        </td>
        <td class="center vcenter">
            <input type='text' id='saida<?= tratarHTML($oServidor->siape); ?>' name='saida<?= tratarHTML($oServidor->siape); ?>' value='<?= tratarHTML($retorno_do_almoco); ?>' size='5' maxlength='5' class="campos center vcenter" readonly>
        </td>
        <td class="center vcenter">
            <input type='text' id='saida<?= tratarHTML($oServidor->siape); ?>' name='saida<?= tratarHTML($oServidor->siape); ?>' value='<?= tratarHTML($horario_de_saida); ?>' size='5' maxlength='5' class="campos center vcenter" readonly>
        </td>
        <td class="center vcenter">
            &nbsp;<img id='visto<?= tratarHTML($oServidor->siape); ?>' src='<?= ($horario_de_entrada == '' ? _DIR_IMAGEM_ . "transp1x1.gif" : _DIR_IMAGEM_ . "visto_blue.gif"); ?>' width='16' border='0'>&nbsp;</div>
        </td>
    </tr>
    <?php
}


####################################################
#                                                  #
#  IMPRIMIR TEXTO 'SEM REGISTRO...'                #
#                                                  #
####################################################
#
function imprimir_sem_registro()
{
    ?>
    <tr>
        <td colspan="9" height="30">Sem registros para exibir!</td>
    </tr>
    <?php
}


####################################################
#                                                  #
#  IMPRIMIR TITULO DAS COLUNAS - RESUMO            #
#                                                  #
####################################################
#
function imprimir_titulo_colunas_resumo()
{
    ?>
    <table width="50%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
        <tr>
            <td class="titulo_colunas margem2px center" width="8%">Seq.</td>
            <td class="titulo_colunas margem2px center" width="23%">Função</td>
            <td class="titulo_colunas margem2px center" width="23%">Jornada por Semana</td>
            <td class="titulo_colunas margem2px center" width="23%">Entrada</td>
            <td class="titulo_colunas margem2px center" width="23%">Saida</td>
            <td class="titulo_colunas margem2px center" width="23%">Qtd Servidores</td>
            <td class="titulo_colunas margem2px center" width="23%">Percentual</td>
        </tr>
    <?php
}


####################################################
#                                                  #
#  IMPRIMIR RESUMO                                 #
#                                                  #
####################################################
#
function imprimir_resumo()
{
    global $resumoTotal, $qtd_total;

    foreach ($resumoTotal as $chave => $qtd)
    {
        $qtd_total += $qtd;
    }

    foreach ($resumoTotal as $chave => $qtd)
    {
        $linha++;
        $horarios         = explode('_', $chave);
        $jornada          = explode('&nbsp;', $horarios[2]);
        $funcao           = (count($jornada) > 1 && ($jornada[1] == "(R)" || $jornada[1] == "(F)") ? "Sim " . $jornada[1] : "");
        $percentual       = number_format(($qtd / $qtd_total * 100), 12, '.', '');
        $total_percentual += $percentual;

        ?>
        <tr>
            <td width="8%" bgcolor="#DFDFBF" height="20"><div align="center"><font size="1">&nbsp;<?= tratarHTML($linha); ?>&nbsp;</font></div></td>
            <td width="23%" nowrap><div align="center"><font size="1">&nbsp;<?= tratarHTML($funcao); ?>&nbsp;</font></div></td>
            <td width="23%"><div align="center"><font size="1">&nbsp;<?= tratarHTML($jornada[0]); ?>&nbsp;</font></div></td>
            <td width="23%"><div align="center"><font size="1">&nbsp;<?= tratarHTML($horarios[0]); ?>&nbsp;</font></div></td>
            <td width="23%"><div align="center"><font size="1">&nbsp;<?= tratarHTML($horarios[1]); ?>&nbsp;</font></div></td>
            <td width="23%"><div align="center"><font size="1">&nbsp;<?= tratarHTML($qtd); ?>&nbsp;</font></div></td>
            <td width="23%"><div align="right"><font size="1">&nbsp;<?=tratarHTML( number_format(($qtd / $qtd_total * 100), 2, ',', '.')); ?>&nbsp;%&nbsp;&nbsp;</font></div></td>
        </tr>
        <?php
    }

    ?>
    <tr>
        <td colspan='5' width="100%" bgcolor="#DFDFBF" height="2"></td>
    </tr>
    <tr>
        <td colspan='5' width="54%" bgcolor="#DFDFBF" height="20"><div align="center"><font size="1">&nbsp;Total de Servidores&nbsp;</font></div></td>
        <td width="23%"><div align="center"><font size="1">&nbsp;<?= tratarHTML( number_format($qtd_total, 0, '.', ',')); ?>&nbsp;</font></div></td>
        <td width="23%" bgcolor="#DFDFBF"><div align="right"><font size="1">&nbsp;</font></div></td>
    </tr>
    <?php
}


####################################################
#                                                  #
#  IMPRIMIR RODAPÉ                                 #
#                                                  #
####################################################
#
function imprimir_rodape($turno_estendido = 'S')
{
    global $nNumServidores;

    ?>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table style='width: 100%;'>
                            <tr>
                                <td class='linhas left vtop' style='width: 50%;' nowrap>
                                    <b>(R):&nbsp;</b>Responsável pela unidade;&nbsp;&nbsp;<b>(F):</b>&nbsp;Detentor de Função/Supervisão<br>
                                    <?= ($turno_estendido == 'S' ? "<b>TE:</b>&nbsp;Turno Estendido (30 hs)" : ""); ?>
                                </td>
                                <td class='linhas right vtop' style='width: 50%;' nowrap>
                                    <b>Emitido em:</b>&nbsp;<?= date('d/m/Y | h:m'); ?>
                                </td>
                            </tr>
                        </table>

                        <input type='hidden' id='total_registros' name='total_registros' value='<?= tratarHTML($nNumServidores); ?>'>
                    </form>
                </fieldset>
            </table>
        </body>
    </html>
    <?php
}
