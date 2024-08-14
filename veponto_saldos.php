<?php

include_once( "config.php" );
include_once( "class_form.frequencia.php" );
include_once( "inc/calculo_horas_comuns_saldos.php" );
include_once( "class_ocorrencias_grupos.php" );

//verifica_permissao( "logado" );

// Valores passados - encriptados
$dadosPonto = (isset($_REQUEST['dadosPonto']) ? $_REQUEST['dadosPonto'] : '');

if (!empty($dadosPonto))
{
    // Valores passados - encriptados
    $dados         = explode(":|:", base64_decode($dadosPonto));
    $pSiape        = $dados[0];
    $relatorioTipo = $dados[1];
}

// Le dados gravados em sessao
// Unidade de quem esta logado e permissao especial
$sLotacao   = isset($_SESSION["sLotacao"]) ? $_SESSION["sLotacao"] : '';
$magico     = isset($_SESSION["magico"]) ? $_SESSION["magico"] : '';
$sMatricula = (isset($sMatricula) ? $sMatricula : (isset($_SESSION['sMatricula']) ? $_SESSION['sMatricula'] : ''));


// Le os dados passados por POST
// via formulario
// Matricula, mes, ano e tipo da operacao
$bExibeResultados = (isset($_REQUEST["bExibeResultados"]) && ($_REQUEST["bExibeResultados"] == '' || $_REQUEST["bExibeResultados"] == '1') ? false : (isset($bExibeResultados) && ($bExibeResultados == '' || $bExibeResultados == false) ? false : true));

$sSiape          = (isset($pSiape)        ? $pSiape        : (empty($_REQUEST["pSiape"]) ? $sMatricula : anti_injection($_REQUEST["pSiape"])));
$mesIni          = (isset($mes)           ? $mes           : (empty($_REQUEST["mes"])    ? 10          : anti_injection($_REQUEST["mes"])));
$anoIni          = (isset($ano)           ? $ano           : (empty($_REQUEST["ano"])    ? 2009        : anti_injection($_REQUEST["ano"])));
$mesFim          = (isset($mes2)          ? $mes2          : (empty($_REQUEST["mes2"])   ? date("m")   : anti_injection($_REQUEST["mes2"])));
$anoFim          = (isset($ano2)          ? $ano2          : (empty($_REQUEST["ano2"])   ? date("Y")   : anti_injection($_REQUEST["ano2"])));
$relatorioTipo   = (isset($relatorioTipo) ? $relatorioTipo : ($_REQUEST['tipo'] == ''    ? "2"         : anti_injection($_REQUEST["tipo"])));
$competenciaHoje = date('mY');

$bSoSaldo = (isset($bSoSaldo) ? $bSoSaldo : false);
$bParcial = (isset($bParcial) ? $bParcial : false);

// instancia banco de dados
$oDBase = new DataBase('PDO');

$sSiape = getNovaMatriculaBySiape($sSiape);

// obtem dados dos servidores
// nome, codigo da lotacao, joranda de trabalho e se é ocupante de função
$rh = 'SELECT a.nome_serv, a.cod_lot, a.chefia, a.jornada, a.entra_trab, a.ini_interv, a.sai_interv, a.sai_trab, DATE_FORMAT(a.dt_adm,"%d/%m/%Y") AS dt_adm, DATE_FORMAT(a.oco_exclu_dt,"%d/%m/%Y") AS oco_exclu_dt, a.cod_sitcad, a.bhoras, a.bh_tipo, a.horae, a.processo, a.motivo, a.dthe, a.dthefim, a.sigregjur, b.upag, b.uorg_pai, b.inicio_atend, b.fim_atend FROM servativ AS a LEFT JOIN tabsetor AS b ON a.cod_lot = b.codigo WHERE mat_siape = :siape';
$oDBase->setMensagem("Saldo-1: Erro no acesso ao banco de dados!\\nPor favor, tente mais tarde.");

$oDBase->query($rh, array(
    array(':siape', $sSiape, PDO::PARAM_STR),
));

// testa matricula
if ($oDBase->num_rows() == 0)
{
    mensagem("Servidor não identificado!");
    exit();
}

$oServidor              = $oDBase->fetch_object();
$nome                   = $oServidor->nome_serv;
$lot                    = $oServidor->cod_lot;
$chefe                  = $oServidor->chefia;
$jnd                    = $oServidor->jornada;
$entrada_no_servico     = $oServidor->entra_trab;
$saida_para_o_almoco    = $oServidor->ini_interv;
$volta_do_almoco        = $oServidor->sai_interv;
$saida_do_servico       = $oServidor->sai_trab;
$anomes_admissao        = $oServidor->dt_adm;
$anomes_exclusao        = $oServidor->oco_exclu_dt;
$situacao_cadastral     = $oServidor->cod_sitcad;
$banco_compensacao      = $oServidor->bhoras;
$banco_compensacao_tipo = $oServidor->bh_tipo;
$processo_hespecial     = $oServidor->processo;
$data_hespecial         = $oServidor->dthe;
$hora_especial          = $oServidor->horae;

$comp = $anoFim . $mesFim;

/* obtem dados da upag para saber se é a mesma do usuario */
$upg                     = $oServidor->upag;
$uorg_pai                = $oServidor->uorg_pai;
$horario_do_setor_inicio = $oServidor->inicio_atend;
$horario_do_setor_fim    = $oServidor->fim_atend;

$qlotacao = $_SESSION["sLotacao"];

if (!empty($_REQUEST["sSiape"]))
{
    if ($_SESSION['sCAD'] == "S")
    {
    }
    elseif ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N" && ($lot != $qlotacao && $uorg_pai != $qlotacao) && $magico < '3')
    {
        mensagem("Não é permitido consultar/alterar dados de servidor de outro setor!");
    }
    elseif ($_SESSION['sRH'] == "S" && $upg != $_SESSION['upag'])
    {
        mensagem("Não é permitido consultar ponto de servidor de outra UPAG!");
    }
}

// calculo das horas
$comp_inicial  = $mesIni . '/' . $anoIni;
$comp_final    = $mesFim . '/' . $anoFim;
$comp_admissao = substr($anomes_admissao, 6, 4) . substr($anomes_admissao, 3, 2);
$comp_exclusao = substr($anomes_exclusao, 6, 4) . substr($anomes_exclusao, 3, 2);


// instancia grupo de ocorrencia
$obj = new OcorrenciasGrupos();

$codigoCreditoPadrao          = $obj->CodigoCreditoPadrao( $oServidor->sigregjur );
$codigoDebitoPadrao           = $obj->CodigoDebitoPadrao( $oServidor->sigregjur );
$codigosAgrupadosParaDesconto = $obj->CodigosAgrupadosParaDesconto( $oServidor->sigregjur );


## resultado_horas_comuns()
#
#  $sSiape: matrícula SIAPE do servidor/estagiário
#  $comp_inicial.: mes/ano de referencia para inicio do calculo
#  $comp_final...: mes/ano de referencia para finalizar o calculo
#  $comp_admissao: mes/ano de referencia, usado também para finalizar o calculo (tem precedência sobre a 'comp_final')
#  $comp_exclusao: mes/ano de referencia, usado também para finalizar o calculo (também tem precedência sobre a 'comp_final')
#
#  retorna um vetor com os serguintes dados:
#   [0]: competencia, no formato mm/aaaa;
#   [1]: descricao referente ao valor;
#   [2]: sinal do valor '+' quando positivo, '-' se negativo e '' (vazio) quando o valor for zero;
#   [3]: informa se foi homologado ou não
##


// horas comuns (créditos/débitos/compensações)
$aHorasComuns = resultado_horas_comuns($sSiape, $comp_inicial, $comp_final, $comp_admissao, $comp_exclusao);



// horas utilizadas para instrutoria (débitos/compensações)
//$aInstrutoria = resultado_horas_instrutoria($sSiape, $comp_inicial, $comp_final, $comp_admissao, $comp_exclusao);

// horas utilizadas para recesso final de ano/natal (débitos/compensações)
//$aRecesso = resultado_horas_recesso($sSiape, $comp_inicial, $comp_final, $comp_admissao, $comp_exclusao);

// horas extras realizadas (créditos)
//$aHoraExtra = resultado_horas_hora_extra($sSiape, $comp_inicial, $comp_final, $comp_admissao, $comp_exclusao);

switch ($relatorioTipo)
{
    case '0': // Relatório de Registro de comparecimento
    /*    $html2 = imprimirSaldoCompensacaoDoMes();
        if ($bExibeResultados == true)
        {
            print $html2;
        }
        else
        {
            //$html_form[$contador] .= $html2; // origem veponto_formulario.php
            $idInner .= $html2; // origem veponto_formulario_imp.php
        }*/
        break;

    case '1': // Extrato em outro documento
        $larguraPagina = "1000px";

        ?>
        <table class="form_control row table text-center margin-30 margin-bottom-10 noborder" style="border:0px solid white;width:<?= $larguraPagina; ?>;">
            <thead>
                <tr style="border:0px solid white;padding:0px;margin:0px;">
                    <th class="text-center" style="border:0px solid white;padding-top:35px;padding-bottom:10px;margin:0px;">
                        <p><b>SISREF - Sistema de Registro Eletrônico de Frequência</b></p>
                    </th>
                </tr>
            </thead>
        </table>
        <table class="table table-striped text-center table-hover" style="border:0px solid white;width:<?= $larguraPagina; ?>;">
            <thead>
                <tr>
                    <td class="text-center" colspan='9' align='center' style="border:0px solid white;padding-bottom:25px;margin:0px;font-size:30px;border:0px solid white;">
                        <b>EXTRATO FREQUÊNCIA</b>
                    </td>
                </tr>
                <tr>
                    <td colspan='9' nowrap style='height:30px;vertical-align:bottom;font-size:14px;font-weight:bold;font-type:italic;width:<?= $larguraPagina; ?>;text-align:left;'>
                        <?= removeOrgaoMatricula($sSiape).'&nbsp;-&nbsp;'.$nome; ?>
                    </td>
                </tr>
                <tr style="font-size:10px;">
                    <th class='text-center' width='40'>MÊS/ANO</th>
                    <th nowrap class='text-center' width='40'>&nbsp;DÉBITO&nbsp;MÊS ANTERIOR<br><small>(A)</small>&nbsp;</th>
                    <th nowrap class='text-center' width='40'>&nbsp;CRÉDITOS&nbsp;<br><small>(B)</small></th>
                    <th nowrap class='text-center' width='40'>&nbsp;SUB-TOTAL&nbsp;<br><small>(C)</small></th>
                    <th nowrap class='text-center' width='40' style='text-align: center;'>DÉBITO&nbsp;MÊS<br>ANTERIOR&nbsp;QUE<br>APARECE&nbsp;SEM<br>COMPENSAÇÃO<br><small>(D)</small></th>
                    <th nowrap class='text-center' width='40'>&nbsp;SUB-TOTAL&nbsp;<br><small>(E)</small></th>
                    <th nowrap class='text-center' width='40'>&nbsp;DÉBITOS&nbsp;<br><small>(F)</small></th>
                    <th nowrap class='text-center' width='40'>&nbsp;SALDO DEVEDOR&nbsp;<br><small>(G)</small></th>
                    <th nowrap class='text-center' width='40'>SITUAÇÃO</th>
                </tr>
            </thead>
            <tbody>
                <?php

                    $comp_inicial = $anoIni . $mesIni;
                    $comp_final   = $anoFim . $mesFim;

                    $contar   = 0;
                    $corLinha = '#f2f2f2';
                    $tam      = count($aHorasComuns);

                    for ($nI = $tam; $nI >= 0; $nI--)
                    {
                        $comp_invertida = substr($aHorasComuns[$nI][0], 2, 4) . substr($aHorasComuns[$nI][0], 0, 2);

                        if ($comp_invertida >= $comp_inicial && $comp_invertida <= $comp_final)
                        {
                            // style
                            $style = array();
                            $style[""]  = "";
                            $style["-"] = "style='color:red;font-weight:bold;'";
                            $style['+'] = "style='font-weight:bold;'";
                            $style['D'] = "style='color:#ae5700;font-weight:bold;'";

                            // DEBITO DO MES ANTERIOR
                            $sDebitoMesAnterior = $aHorasComuns[$nI + 0][2] . $aHorasComuns[$nI + 0][3];
                            $sDebitoMesAnterior = (strlen($sDebitoMesAnterior) > 8 ? substr($sDebitoMesAnterior,0,strlen($sDebitoMesAnterior)-3) : $sDebitoMesAnterior);
                            $colunaA            = $style[$aHorasComuns[$nI + 0][2]];

                            // CREDITOS MES ATUAL
                            $sCreditoMesAtual = $aHorasComuns[$nI + 1][2] . $aHorasComuns[$nI + 1][3];
                            $sCreditoMesAtual = (strlen($sCreditoMesAtual) > 8 ? substr($sCreditoMesAtual,0,strlen($sCreditoMesAtual)-3) : $sCreditoMesAtual);
                            $colunaB          = $style[$aHorasComuns[$nI + 1][2]];

                            // SUB-TOTAL
                            $sSubTotal = $aHorasComuns[$nI + 2][2] . $aHorasComuns[$nI + 2][3];
                            $sSubTotal = (strlen($sSubTotal) > 8 ? substr($sSubTotal,0,strlen($sSubTotal)-3) : $sSubTotal);
                            $colunaC   = $style[$aHorasComuns[$nI + 2][2]];

                            // DEBITOS EM FOLHA
                            if ($aHorasComuns[$nI + 2][3] != '00:00' && $aHorasComuns[$nI + 2][2] == '-')
                            {
                                $sDebitoEmFolha = str_replace('-', '+', $sSubTotal);
                                $colunaD        = $style['D'];
                            }
                            else
                            {
                                $sDebitoEmFolha = "";
                                $colunaD        = $style[$aHorasComuns[$nI + 3][2]];
                            }

                            // SUB-TOTAL (2)
                            if ($aHorasComuns[$nI + 2][3] != '00:00' && $aHorasComuns[$nI + 2][2] == '-')
                            {
                                $sSubTotal_2 = '00:00';
                                $colunaE     = "";
                            }
                            else
                            {
                                $sSubTotal_2 = tratarHTML($sSubTotal);
                                $colunaE     = $style[$aHorasComuns[$nI + 2][2]];
                            }

                            // DEBITOS MES ATUAL
                            $sDebitoMesAtual = $aHorasComuns[$nI + 3][2] . $aHorasComuns[$nI + 3][3];
                            $sDebitoMesAtual = (strlen($sDebitoMesAtual) > 8 ? substr($sDebitoMesAtual,0,strlen($sDebitoMesAtual)-3) : $sDebitoMesAtual);
                            $colunaF         = $style[$aHorasComuns[$nI + 3][2]];

                            // SALDO
                            $sSaldo  = $aHorasComuns[$nI + 4][2] . $aHorasComuns[$nI + 4][3];
                            $sSaldo  = (strlen($sSaldo) > 8 ? substr($sSaldo,0,strlen($sSaldo)-3) : $sSaldo);
                            $sSaldo  = ($aHorasComuns[$nI + 4][2] == '-' ? $sSaldo : ($sSaldo == '00:00' ? '----------' : $sSaldo));
                            $colunaG = ($aHorasComuns[$nI + 4][2] == '-' ? $style["-"] : ($sSaldo == '----------' ? $style[""] : $style["+"]));

                            // SITUAÇÃO DA HOMOLOGAÇÃO
                            $situacaoDaHomologacao = ($comp_invertida == date('Ym') ? "<i>EM ANDAMENTO</i>" : tratarHTML($aHorasComuns[$nI + 4][4]));
                            $colunaSituacao        = ($comp_invertida == date('Ym') ? "" : (tratarHTML($aHorasComuns[$nI + 4][4]) == "HOMOLOGADO" ? $style['+'] : $style['-']));

                            $contar++;
                            if ($contar == 5)
                            {
                                $compet       = substr($aHorasComuns[$nI][0], 2, 4) . substr($aHorasComuns[$nI][0], 0, 2);
                                $oHomologacao = getDadosHomologacao( $sSiape, $compet );
                                
                                if (empty($oHomologacao->homologado_siape))
                                {
                                    ?>
                                    <tr>
                                        <td nowrap style="vertical-align:middle;">&nbsp;&nbsp;<b><?= tratarHTML(substr($aHorasComuns[$nI][0], 0, 2)) . ' / ' . tratarHTML(substr($aHorasComuns[$nI][0], 2, 4)); ?></b></td>
                                        <!-- coluna A --><td nowrap <?= $colunaA; ?>>&nbsp;<?= tratarHTML($sDebitoMesAnterior); ?>&nbsp;</td>
                                        <!-- coluna B --><td nowrap <?= $colunaB; ?>>&nbsp;<?= tratarHTML($sCreditoMesAtual); ?>&nbsp;</td>
                                        <!-- coluna C --><td nowrap <?= $colunaC; ?>>&nbsp;<?= tratarHTML( $sSubTotal ); ?>&nbsp;</td>
                                        <!-- coluna D --><td nowrap <?= $colunaD; ?>>&nbsp;<?= tratarHTML($sDebitoEmFolha); ?>&nbsp;</td>
                                        <!-- coluna E --><td nowrap <?= $colunaE; ?>>&nbsp;<?= ($aHorasComuns[$nI + 2][3] != '00:00' && $aHorasComuns[$nI + 2][2] == '-' ? '00:00' : tratarHTML($sSubTotal)); ?>&nbsp;</td>
                                        <!-- coluna F --><td nowrap <?= $colunaF; ?>>&nbsp;<?= tratarHTML($sDebitoMesAtual); ?>&nbsp;</td>
                                        <!-- coluna G --><td nowrap <?= $colunaG; ?>>&nbsp;<?= tratarHTML($sSaldo); ?>&nbsp;</td>
                                        <td nowrap <?= $colunaSituacao; ?>>&nbsp;<?= tratarHTML($situacaoDaHomologacao); ?>&nbsp;</td>
                                    </tr>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <tr>
                                        <td nowrap rowspan="2" style="vertical-align:top;">&nbsp;&nbsp;<b><?= tratarHTML(substr($aHorasComuns[$nI][0], 0, 2)) . ' / ' . tratarHTML(substr($aHorasComuns[$nI][0], 2, 4)); ?></b></td>
                                        <!-- coluna A --><td nowrap <?= $colunaA; ?>>&nbsp;<?= tratarHTML($sDebitoMesAnterior); ?>&nbsp;</td>
                                        <!-- coluna B --><td nowrap <?= $colunaB; ?>>&nbsp;<?= tratarHTML($sCreditoMesAtual); ?>&nbsp;</td>
                                        <!-- coluna C --><td nowrap <?= $colunaC; ?>>&nbsp;<?= tratarHTML( $sSubTotal ); ?>&nbsp;</td>
                                        <!-- coluna D --><td nowrap <?= $colunaD; ?>>&nbsp;<?= tratarHTML($sDebitoEmFolha); ?>&nbsp;</td>
                                        <!-- coluna E --><td nowrap <?= $colunaE; ?>>&nbsp;<?= ($aHorasComuns[$nI + 2][3] != '00:00' && $aHorasComuns[$nI + 2][2] == '-' ? '00:00' : tratarHTML($sSubTotal)); ?>&nbsp;</td>
                                        <!-- coluna F --><td nowrap <?= $colunaF; ?>>&nbsp;<?= tratarHTML($sDebitoMesAtual); ?>&nbsp;</td>
                                        <!-- coluna G --><td nowrap <?= $colunaG; ?>>&nbsp;<?= tratarHTML($sSaldo); ?>&nbsp;</td>
                                        <td nowrap <?= $colunaSituacao; ?>>&nbsp;<?= tratarHTML($situacaoDaHomologacao); ?>&nbsp;</td>
                                    </tr>
                                
                                    <tr>
                                        <td nowrap colspan="8" style="text-align:right;">&nbsp;<?= tratarHTML(
                                            (empty($oHomologacao->homologado_nome) ? "" : "Homologado por: " . $oHomologacao->homologado_nome)  . 
                                            (empty($oHomologacao->homologado_siape)  ? "" : " - "            . $oHomologacao->homologado_siape) . 
                                            (empty($oHomologacao->homologado_data)  ? "" : "  |  Data: "     . $oHomologacao->homologado_data)); ?>&nbsp;</td>
                                    </tr>
                                    <?php
                                }

                                $contar   = 0;
                            }
                        }
                    }

                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class='lnTitulo' colspan="9" height='22' valign='top'>&nbsp;<b>Observação:</b>&nbsp;O saldo do mês <?= date('m/Y'); ?> é parcial e está sujeito a alterações.</th>
                </tr>

            </tfoot>
        </table>

        <?php $nWidth = '1000px'; ?>
        <?= legendaSaldoCompensacaoDoMes(1, $nWidth); ?>
        <?= legendaSaldoCompensacaoDoMes(3, $nWidth); ?>

        <?php

        // prepara para impressao em pdf
        $_SESSION['sIMPExtratoFrequencia'] = $html2;

        break;

    case '2': // 1a versao
    default: // Extrato
        if ($bSoSaldo == false)
        {
            $html = "<html><head><title></title><meta http-equiv='Content-Language' content='pt-br'><meta http-equiv='Content-Type' content='text/html; charset=windows-1252'><link rel='stylesheet' type='text/css' href='" . _DIR_CSS_ . "estilos.css'></style><link rel='stylesheet' type='text/css' href='" . _DIR_CSS_ . "print3.css'></style><script language=JavaScript src='" . _DIR_JS_ . "funcoes.js'></script><style>.titulo { font-size: 11px; font-family: arial; font-weight: bold; height: 20px; background-color: #DFDFBF; text-align: center; vertical-align: middle; }.textos10 { font-size: 12px; font-family: arial; font-weight: normal; background-color: transparent; text-align: justify; vertical-align: middle; }.textos11 { font-size: 12px; font-family: arial; font-weight: normal; background-color: transparent; text-align: center; vertical-align: middle; }.textos20 { font-size: 13px; font-family: arial; font-weight: normal; background-color: transparent; text-align: justify; vertical-align: middle; }.textos10b { font-size: 12px; font-family: arial; font-weight: normal; background-color: #f3f3f3; text-align: justify; vertical-align: middle; }.textos11b { font-size: 12px; font-family: arial; font-weight: normal; background-color: #f3f3f3; text-align: center; vertical-align: middle; }.textos20b { font-size: 13px; font-family: arial; font-weight: normal; background-color: #f3f3f3; text-align: justify; vertical-align: middle; }</style></head><body bgcolor='#FFFFFF' ><script language=JavaScript src='" . _DIR_JS_ . "menu/frames_body_array.js' type=text/javascript></script><script language=JavaScript src='" . _DIR_JS_ . "menu/mmenu.js' type=text/javascript></script><fieldset align='center' style='width: 800; height: 100%;'><table align='center'" . ($quebra == 'sim' ? " style='page-break-before: always'" : "") . "><tr><td><p align='center' style='word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0'><b><h3>
			<div align='center'><p>&nbsp;</p>
			<p><font face='Tahoma' size='4' color='#333300'>SISREF - Sistema de Registro Eletrônico de Frequência</font></p>
			</div></b></h3>
			<table width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
			<tr>
			<td height='20' bgcolor='#DFDFBF'> <p align='center'><b>Siape</b></td>
			<td width='54%' height='20' bgcolor='#DFDFBF' colspan='2'><div align='center'><b>Nome</b></div></td>
			<td height='20' bgcolor='#DFDFBF'><div align='center'><b>Lota&ccedil;&atilde;o</b></div></td>
			</tr>
			<tr>
			<td width='10%'>
			<div align='center'>
			<input name='siape' type='text' class='centro' id='siape' value='" . tratarHTML($sSiape) . "' size='10' readonly='10' >
			</div>
			</td>
			<td colspan='2'>
			<p align='left'>
			<input name='nome' type='text' class='Caixa' id='nome' value='" . tratarHTML($nome) . "' size='70' readonly='70' >
			</td>
			<td width='16%'>
			<p align='center'>
			<input name='lotacao' type='text' class='centro' id='lotacao' value='" . tratarHTML($lot) . "' size='15' readonly='10'  >
			</td>
			</tr>

			<tr>
			<td colspan='1' rowspan='2' bgcolor='#DFDFBF'>
			<p align='center'><font size='1'>Hor&aacute;rio do setor</font>
			</td>
			<td height='20' colspan='3' bgcolor='#DFDFBF'>
			<div align='center'><font size='1'>Hor&aacute;rio do servidor</font></div>
			</td>
			</tr>
			<tr>
			<td width='18%' height='20' bgcolor='#DFDFBF'><div align='center'><font size='1'>Entrada</font></div></td>
			<td width='36%' bgcolor='#DFDFBF'><div align='center'><font size='1'>Intervalo</font></div></td>
			<td height='20' bgcolor='#DFDFBF'><div align='center'><font size='1'>Sa&iacute;da</font></div></td>
			</tr>
			<tr>
			<td colspan='1' nowrap>
			<div align='center'>
			<font size='1'>
			<input name='inicio' type='text'  class='centro' id='inicio' value='" . tratarHTML($horario_do_setor_inicio) . "' size='10' readonly>
			&agrave;s
			<input name='fim' type='text'  class='centro' id='fim' value='" . tratarHTML($horario_do_setor_fim) . "' size='10' readonly>
			</font>
			</div>
			</td>
			<td>
			<div align='center'>
			<font size='1'>
			<input name='entrada' type='text'  class='centro' id='entrada' value='" . tratarHTML($entrada_no_servico) . "' size='10' readonly>
			</font>
			</div>
			</td>
			<td>
			<div align='center'>
			<font size='1'>
			<input name='interve' type='text'  class='centro' id='interve' value='" . tratarHTML($saida_para_o_almoco) . "' size='10' readonly>
			&agrave;s
			<input name='intervs' type='text'  class='centro'' id='intervs' value='" . tratarHTML($volta_do_almoco) . "' size='10' readonly>
			</font>
			</div>
			</td>
			<td>
			<div align='center'>
			<font size='1'>
			<input name='saida' type='text'  class='centro' id='saida' value='" . tratarHTML($saida_do_servico) . "' size='10' readonly>
			</font>
			</div>
			</td>
			</tr>

			<tr>
			<td height='20' colspan='4'>
			<div align='center'>
			<font size='1'>
			Horário Especial: <b>" . ($hora_especial == 'S' ? "SIM, ".tratarHTML($processo_hespecial) : 'NÃO') . "</b>
			</font>
			</div>
			</td>
			</tr>";
        }
        else
        {
            $html = "
				<style>
				.titulo { font-size: 8px; font-family: arial; font-weight: bold; height: 20px; background-color: #DFDFBF; text-align: center; vertical-align: middle; }
				.textos10 { font-size: 9px; font-family: arial; font-weight: normal; background-color: transparent; text-align: justify; vertical-align: middle; }
				.textos11 { font-size: 9px; font-family: arial; font-weight: normal; background-color: transparent; text-align: center; vertical-align: middle; }
				.textos20 { font-size: 10px; font-family: arial; font-weight: normal; background-color: transparent; text-align: justify; vertical-align: middle; }
				.textos10b { font-size: 9px; font-family: arial; font-weight: normal; background-color: #f3f3f3; text-align: justify; vertical-align: middle; }
				.textos11b { font-size: 9px; font-family: arial; font-weight: normal; background-color: #f3f3f3; text-align: center; vertical-align: middle; }
				.textos20b { font-size: 10px; font-family: arial; font-weight: normal; background-color: #f3f3f3; text-align: justify; vertical-align: middle; }
				</style>
				<fieldset align='center' style='width: 800;'>
				<table width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse; page-break-before: always'>";
        }


        $html .= "
			<tr>
			<td height='20' colspan='4' bgcolor='#DFDFBF'>
			<p align='center'><b>Saldo Horas Comuns " . ($bParcial == true ? " - <small>PARCIAL</small>" : "") . "</b> <font face='Tahoma' size='2' color='#333300'></font>
			</td>
			</tr>

			<tr>
			<td colspan='4'>

			<table width='100%' border='0' cellpadding='0' cellspacing='0' bordercolor='#808040' style='border: 1px solid #E2E2C5;'>
			<tr>
			<td width='10%' class='titulo' style='border: 1px solid #eaeaea;'>MÊS/ANO</td>
			<td width='60%'  class='titulo' style='border: 1px solid #eaeaea;'>DESCRIÇÃO</td>
			<td width='20%'  class='titulo' style='border: 1px solid #eaeaea;'>HORAS</td>
			<td width='10%'  class='titulo' style='border: 1px solid #eaeaea;'>SITUAÇÃO</td>
			</tr>";

        $nRows = count($aHorasComuns) - 1;
        for ($xx = $nRows; $xx > 0; $xx -= 5)
        {
            $class = ($xx % 2 == 0 ? '' : 'b');
            for ($y = 4; $y >= 0; $y--)
            {
                // exibe em vermelho se negativo
                $fti_red = "<font style='color: red; font-weight: bold;'>";
                $ftf_red = "</font>";

                $style = ($y == 3 || $y == 1 ? "style='border-bottom: 2px double #a8a8a8;'" : "style='border-bottom: 1px solid " . ($y == 0 ? "#000000" : "#eeeeee") . ";'");
                $nI    = $xx - $y;

                // saldo
                $sHoras = $aHorasComuns[$nI][3];
                $sHoras = ($aHorasComuns[$nI][2] == '-' ? $fti_red . $sHoras . $ftf_red : $sHoras);

                if ($y == 4)
                {
                    $html .= "<tr><td class='textos20" . $class . "' rowspan='6' nowrap style='border-right: 1px solid #eeeeee; border-bottom: 1px solid #000000;'>&nbsp;&nbsp;" . tratarHTML(substr($aHorasComuns[$nI][0], 0, 2)) . " / " . tratarHTML(substr($aHorasComuns[$nI][0], 2, 4)) . "&nbsp;&nbsp;</td></tr>";
                }
                $sDescricao = $aHorasComuns[$nI][1];
                $sDescricao = (substr_count($sDescricao, 'Descontar') > 0 ? $fti_red : "") . $sDescricao;
                $sDescricao = $sDescricao . (substr_count($sDescricao, 'Descontar') > 0 ? $ftf_red : "");
                $html       .= "<tr><td class='textos10" . $class . "' $style><div style='margin: 7px;'>" . $sDescricao . "</div></td><td class='textos11" . $class . "' $style><table border='0'><tr><td class='textos11" . $class . "' style='width: 10px; vertical-align: middle;'>" . tratarHTML($aHorasComuns[$nI][2]) . "</td><td class='textos11" . $class . "' style='vertical-align: middle;'>" . $sHoras . "</td></tr></table></td>";
                if ($y == 4)
                {
                    if (substr_count($aHorasComuns[$nI][4], 'NÃO HOMOLOGADO') > 0)
                    {
                        $sTexto = "<br><font color=red>N<br>&nbsp;Ã<br>&nbsp;&nbsp;O<br><br>&nbsp;&nbsp;H<br>&nbsp;&nbsp;&nbsp;O<br>&nbsp;&nbsp;&nbsp;&nbsp;M<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;L<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;G<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;D<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O</font><br>";
                    }
                    else
                    {
                        $sTexto = "<br>H<br>&nbsp;O<br>&nbsp;&nbsp;M<br>&nbsp;&nbsp;&nbsp;O<br>&nbsp;&nbsp;&nbsp;&nbsp;L<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;G<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;D<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;O<br>";
                    }
                    //$sTexto = "<font style='font-size: 10px;'>".$aHorasComuns[$nI][4]."</font>";
                    $html .= "<td class='textos10" . $class . "' rowspan='6' nowrap style='text-align: center; border-left: 1px solid #eeeeee; border-bottom: 1px solid #000000;'>" . $sTexto . "&nbsp;&nbsp;</td>";
                }
                $html .= "</tr>";
            }
        }

        $html .= "
			</table>
			</td>
			</tr>
			</table>
			</fieldset>
			<br><br>";

        if ($bSoSaldo == false)
        {
            $html .= "
				</table>
				</form>
				</fieldset>
				<br><br>
				</body>
				</html>";
        }

        if ($bExibeResultados == true)
        {
            print $html;
        }

        break;
}

function imprimirSaldoCompensacaoDoMes($extrato = false, $dados=NULL)
{
    global $sSiape, $mesFim, $anoFim, $aHorasComuns, $codigoCreditoPadrao, $codigoDebitoPadrao;

    if (is_array($dados))
    {
        $sSiape       = $dados['siape'];
        $mesFim       = $dados['mes_fim'];
        $anoFim       = $dados['ano_fim'];
        if (count($dados) > 3)
        {
            $aHorasComuns = $dados['horas'];
        }
    }

    $comp = $anoFim . $mesFim;

    // CSS
    $html2 = ""; //cssSaldoCompensacaoDoMes();

    $corTituloBG = "#b5b56a"; //#808040 //#DFDFBF < #ddddff
    $html2       .= "<table class='table table-striped table-bordered text-center'>";
    $html2       .= "<thead>";
    $html2       .= "<tr>";
    $html2       .= "<th class=\"text-center\" colspan=\"9\">";
    $html2       .= "<h4><b>Relatório de Horas Comuns para o Servidor</b></h4>";
    $html2       .= "</th>";
    $html2       .= "</tr>";
    $html2       .= "<tr>";
    $html2       .= "<th class='text-center text-nowrap col-md-4' style='vertical-align:middle;'>MÊS/ANO</th>";
    $html2       .= "<th class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>&nbsp;Débito<br>Mês Anterior<br><small>(A)</small>&nbsp;</th>";
    $html2       .= "<th class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>&nbsp;Créditos&nbsp;<br><small>(B)</small></th>";
    $html2       .= "<th class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>&nbsp;Sub-Total&nbsp;<br><small>(C)</small></th>";

    $html2 .= "<th class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>Débito&nbsp;Mês<br>Anterior&nbsp;que<br>Aparece&nbsp;sem<br>Compensação<br><small>(D)</small></th>";

    $html2 .= "<th class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>&nbsp;Sub-Total&nbsp;<br><small>(E)</small></th>";
    $html2 .= "<th class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>&nbsp;Débitos&nbsp;<br><small>(F)</small></th>";
    $html2 .= "<th class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>&nbsp;Saldo&nbsp;<br><small>(G)</small></th>";
    $html2 .= "<th class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>&nbsp;Ocorrência&nbsp;</th>";
    $html2 .= "</tr>";
    $html2 .= "</thead>";
    $html2 .= "<tbody>";

    // exibe em vermelho se negativo
    $fti_red = "<font style='color: red; font-weight: bold;'>";
    $ftf_red = "</font>";

    // Saldo de horas comuns
    $contar = 0;
    $tam    = count($aHorasComuns) - 1;
    for ($nI = $tam; $nI >= 0; $nI--)
    {
        $sCompetencia = substr($aHorasComuns[$nI][0], 2, 4) . substr($aHorasComuns[$nI][0], 0, 2);

        if ($sCompetencia == $comp || $extrato == true)
        {
            $contar++;
            if ($contar == 5 || $extrato == true)
            {
                $sMesAnterior = preparaHoraParaExibir($aHorasComuns[$nI + 0][2], $aHorasComuns[$nI + 0][3]);
                $sCreditos    = preparaHoraParaExibir($aHorasComuns[$nI + 1][2], $aHorasComuns[$nI + 1][3]);
                $sSubTotal    = preparaHoraParaExibir($aHorasComuns[$nI + 2][2], $aHorasComuns[$nI + 2][3]);
                $sDebitos     = preparaHoraParaExibir($aHorasComuns[$nI + 3][2], $aHorasComuns[$nI + 3][3]);
                $sSaldo       = preparaHoraParaExibir($aHorasComuns[$nI + 4][2], $aHorasComuns[$nI + 4][3]);
                $sSaldo       = ($sSaldo == '00:00' ? '----------' : $sSaldo);

                $corLinha = ($corLinha == '#f2f2f2' ? '#FFFFFF' : '#f2f2f2');

                $html2         .= "<tr>";
                $html2         .= "<td class='text-center text-nowrap col-md-4' style='vertical-align:middle;height:50px;'><b>Horas Comuns - &nbsp;" . substr($aHorasComuns[$nI][0], 0, 2) . ' / ' . substr($aHorasComuns[$nI][0], 2, 4) . "</b></td>";
                $html2         .= "<td class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>" . tratarHTML($sMesAnterior) . "</td>";
                $html2         .= "<td class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>" . tratarHTML($sCreditos) . "</td>";
                $html2         .= "<td class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>" . tratarHTML($sSubTotal) . "</td>";
                $html2         .= "<td class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>" . ($aHorasComuns[$nI + 2][3] != '00:00' && $aHorasComuns[$nI + 2][2] == '-' ? '<b>' . str_replace('red', '#ae5700', str_replace('-', '+', tratarHTML($sSubTotal))) . '</b>' : '00:00') . "</td>";
                $html2         .= "<td class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>" . ($aHorasComuns[$nI + 2][3] != '00:00' && $aHorasComuns[$nI + 2][2] == '-' ? '00:00' : $sSubTotal) . "</td>";
                $html2         .= "<td class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>" . tratarHTML($sDebitos) . "</td>";
                $html2         .= "<td class='text-center text-nowrap col-md-1' style='vertical-align:middle;'>" . ($sCompetencia == date('Ym') || $sCompetencia == $comp ? tratarHTML($sSaldo) : '----------') . "</td>";
                $html2         .= "<td class='text-center text-nowrap col-md-1' style='vertical-align:middle;'><b>" . ($aHorasComuns[$nI + 4][3] != '00:00' && $aHorasComuns[$nI + 4][2] == '-' ? tratarHTML(implode(',', $codigoDebitoPadrao)) : ($aHorasComuns[$nI + 4][3] == '00:00' ? '----------' : tratarHTML(implode(',', $codigoCreditoPadrao)))) . "</b></td>";
                $html2         .= "</tr>";
                $saldo_parcial = (date('Ym') == $comp);
                $contar        = 0;
            }
        }
    }

    $html2 .= "<tr>";
    if ($saldo_parcial == true)
    {
        $html2 .= "<td class='text-left text-nowrap col-md-12' colspan='9'>Observação: O saldo do mês é parcial e está sujeito a alterações.</td>";
    }
    else
    {
        $html2 .= "<td class='text-center text-nowrap col-md-12' colspan='9'>&nbsp;</td>";
    }
    $html2 .= "</tr>";
    $html2 .= "</tbody>";
    $html2 .= "</table>";
    $html2 .= str_replace("SALDO DEVEDOR", "SALDO", legendaSaldoCompensacaoDoMes(3));

    return $html2;

}

function preparaHoraParaExibir($sinal = '', $horas = '')
{
    $fti_red = "<font style='color: red; font-weight: bold;'>";
    $ftf_red = "</font>";
    $sHoras  = sec_to_time(time_to_sec($horas), 'hh:mm');
    $sHoras  = ($sinal == '-' && $sHoras != '00:00' ? $fti_red . $sinal . ' ' . $sHoras . $ftf_red : $sHoras);
    return $sHoras;

}

function cssSaldoCompensacaoDoMes()
{
    $html2 .= "<style>";
    $html2 .= ".tdTitulo { font-color: #000000; font-size: 9px; font-family: arial; font-weight: bold; text-align: center; background-color: #DFDFBF; border: 1px solid #b5b56a }";
    $html2 .= ".tdTitulo2 { font-color: #000000; font-size: 9px; font-family: arial; font-weight: bold; text-align: center; background-color: #DFDFBF; width: 60; border: 1px solid #b5b56a }";
    $html2 .= ".tdLinha { color: #000000; font-size: 10px; font-family: arial; font-weight: normal; text-align: center; border: 1px solid #b5b56a; height: 15; }";
    $html2 .= ".tdLinha2 { color: #000000; font-size: 10px; font-family: arial; font-weight: normal; text-align: right; border: 1px solid #b5b56a }";
    $html2 .= ".tdLinha4 { color: #000000; font-size: 10px; font-family: arial; font-weight: normal; text-align: justify; vertical-align: top; border: 0px solid #b5b56a }";
    $html2 .= ".lnTitulo { color: #000000; font-size: 9px; font-family: arial; font-weight: normal; text-align: left; border: 0px solid #b5b56a }";
    $html2 .= ".lnTitulo2 { color: #000000; font-size: 10px; font-family: arial; font-weight: bold; text-align: justify; vertical-align: top; }";
    $html2 .= "</style>";

    $html2 .= "<style>";
    $html2 .= ".ftFormFreq-tit-bc { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }";
    $html2 .= ".ftFormFreq-tit-bc-3 { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }";
    $html2 .= ".ftFormFreq-bc-1 { font-size: 7.5pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; vertical-align: middle; }";
    $html2 .= ".ftFormFreq-cn-1 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; }";
    $html2 .= ".ftFormFreq-cn-2 { font-family: verdana,arial,tahoma; font-size: 7.7pt; color: #000000; text-align: center; vertical-align: middle; }";
    $html2 .= ".ftFormFreq-c { font-size: 7.5pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }";
    $html2 .= ".ftFormFreq-bc { font-size: 7.5pt; color: #000000; font-weight: bold; background-color: #FFFFFF; text-align: center; vertical-align: middle; }";
    $html2 .= ".ftFormFreq-c-2 { font-size: 8pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }";
    $html2 .= ".ftFormFreq-c-3 { font-size: 9pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }";
    $html2 .= ".ftFormFreq-bc-2 { font-family: Tahoma,verdana,arial; font-size: 11pt; font-weight: bold; color: #333300; background-color: #FFFFFF; text-align: center; vertical-align: middle; }";
    $html2 .= "</style>";
    return $html2;

}

function legendaSaldoCompensacaoDoMes($tipo = 1, $width = '100%')
{
    global $relatorioTipo, $codigoCreditoPadrao, $codigoDebitoPadrao, $codigosAgrupadosParaDesconto;
    $legenda = '';
    switch ($tipo)
    {
        case 1:
            $legenda .= "<fieldset class='fieldsetw' align='center' style='width: $width; word-spacing: 0; margin-top: 0; margin-bottom: 0'>";
            //$legenda .= "<table width='$width' cellpadding='0' cellspacing='1' style='border-collapse: collapse;'>";
            //$legenda .= "<tr><td class='lnTitulo' height='20'>&nbsp;<b><i><u>Legendas</u></i></b>&nbsp;</td></tr>";
            break;

        case 2:
            $legenda .= "<tr><td><table cellpadding='0' cellspacing='0'><tr><td class='lnTitulo2' nowrap>MÊS/ANO:&nbsp;</td><td class='tdLinha4'>Mês e ano de competência;</td></tr></table></td></tr>";
            break;

        case 3:
            $legenda .= '
            <div class="row col-md-12 margin-25 margin-bottom-50 text-left">
                <p>
                    <strong>Legendas:</strong><br>
                    <br>
                    <strong>MÊS/ANO:</strong> Mês e ano de competência;<br>
                    <strong>(A) DÉBITO MÊS ANTERIOR:</strong> Contém o resultado da diferença apurada no mês anterior, SALDO. Ocorrências ' . tratarHTML(implode(', ', $codigosAgrupadosParaDesconto)) . ';<br>
                    <strong>(B) CRÉDITOS:</strong> Horas de crédito acumulados dentro do mês de competência (' . tratarHTML(implode(", ", $codigoCreditoPadrao)) . ');<br>
                    <strong>(C) SUB-TOTAL: </strong>(A-B): Diferença entre as horas de CRÉDITOS (B) acumulados dentro do mês de competência e as de DÉBITO do MÊS ANTERIOR (A);<br>
                    <strong>(D) DÉBITO MÊS ANTERIOR QUE APARECE SEM COMPENSAÇÃO:</strong> 	Débito do mês anterior que aparece como não compensado. Resultado da coluna "C".<br>
                    <strong>(E) SUB-TOTAL:</strong> (C-D): Resultado da diferença entre a coluna "C" e "D";<br>
                    <strong>(F) DÉBITOS:</strong> Horas de atrasos, saídas antecipadas e faltas justificadas acumuladas dentro do mês de competência;<br>
                    <strong>(G) SALDO:</strong> (E-F): Resultado final do mês de competência, sendo negativo é tansportado para o mês seguinte.<br>
                    <br>
                    Quando consta \'----------\', na coluna "Ocorrência", significa que não há débito no mês de competência.
                    <br>
                    <br>
                    <strong>Obs:</strong>
                    <br>
                    1) O código na coluna "Ocorrência", quando exibido, refere-se à coluna "Saldo". <br>
                    2) As horas credoras no mês, decorrente de compensação autorizada pela chefia, são utilizadas exclusivamente para compensação de atrasos, faltas justificadas, saídas antecipadas e ausências dentro do mês de competência e do imediatamente anterior.<br>
                    Obs: Clique na ocorrência para apresentar justificativa.<br>
                    Obs: As horas negativas constantes dos códigos ' . tratarHTML(implode(', ', $codigosAgrupadosParaDesconto)) . ' são totalizadas no codigo ' . tratarHTML(implode(", ", $codigoDebitoPadrao)) . ' para fins de compensação.<br>
                </p>
            </div>
            ';
            break;
    }
    return $legenda;

}
