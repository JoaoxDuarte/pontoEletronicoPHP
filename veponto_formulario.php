<?php
include_once( "config.php" );
include_once( "class_form.frequencia.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao('logado');


// Le dados gravados em sessao
// Unidade de quem esta logado e permissao especial
$sLotacao = ($lotacao == '' ? $_SESSION['sLotacao'] : $lotacao);
$magico   = $_SESSION['magico'];

// Le os dados passados por POST
// via formulario
// Matricula, mes, ano e tipo da operacao

$path_parts       = pathinfo($_SERVER['PHP_SELF']);
$pagina_de_origem = $path_parts['basename'];


switch ($pagina_de_origem)
{
    ## ############################################################## ##
    #                                                                  #
    #  PONTOSER.PHP                                                    #
    #                                                                  #
    ## ############################################################## ##
    case 'pontoser.php':
        $pSiape        = $_SESSION['sMatricula'];
        $mes           = ($cmd == '2' ? anti_injection($_REQUEST['mes']) : date('m') );
        $ano           = ($cmd == '2' ? anti_injection($_REQUEST['ano']) : date('Y') );
        $paginaDestino = $pagina_de_origem . '?mes=' . $mes . '&ano=' . $ano . '&cmd=' . $cmd;
        break;


    ## ############################################################## ##
    #                                                                  #
    #  REGFREQ6.PHP                                                    #
    #                                                                  #
    ## ############################################################## ##
    case 'regfreq6.php':
    case 'veponto4_pesquisa9999.php':
    case 'veponto4_pesquisa_horario.php':
        $_SESSION['sPaginaRetorno_sucesso1'] = $pagina_de_origem . '?mat=' . $pSiape . '&mes=' . $mes . '&ano=' . $ano . '&anot=' . $ano . '&cmd=' . $cmd;
        break;


    ## ############################################################## ##
    #                                                                  #
    #  REGFREQ8.PHP                                                    #
    #  FREQUENCIA_VERIFICAR_HOMOLOGADOS_VISUALIZAR.PHP                 #
    #                                                                  #
    ## ############################################################## ##
    case 'regfreq8.php':
    case 'frequencia_verificar_homologados_visualizar.php':
        $paginaDestino = $_SERVER['REQUEST_URI'];
        break;


    ## ############################################################## ##
    #                                                                  #
    #  VEPONTO2.PHP                                                    #
    #  FREQUENCIA_ACOMPANHAR_REGISTROS_VEPONTO.PHP                     #
    #                                                                  #
    ## ############################################################## ##
    case 'veponto2.php':
    case 'frequencia_acompanhar_registros_veponto.php':
        $paginaDestino = $_SESSION['sPaginaRetorno_sucesso'];
        break;


    ## ############################################################## ##
    #                                                                  #
    #  VEPONTO_FORMULÁRIO.PHP                                          #
    #                                                                  #
    ## ############################################################## ##
    case 'veponto_formulario.php':
        $pSiape        = anti_injection($_REQUEST['pSiape']);
        $mes           = anti_injection($_REQUEST['mes1']);
        $ano           = anti_injection($_REQUEST['ano1']);
        $cmd           = anti_injection($_REQUEST['cmd']);
        $paginaDestino = $pagina_de_origem . '?pSiape=' . $pSiape . '&mes1=' . $mes . '&ano1=' . $ano . '&cmd=' . $cmd;
        break;


    ## ############################################################## ##
    #                                                                  #
    #  VEPONTO4.PHP                                                    #
    #                                                                  #
    ## ############################################################## ##
    case 'veponto4.php':
        $paginaDestino = $pagina_de_origem . '?pSiape=' . $pSiape . '&mes3=' . $mes . '&ano3=' . $ano . '&cmd=' . $cmd;
        break;

    default:
        $pSiape        = anti_injection($_REQUEST['pSiape']);
        $mes           = anti_injection($_REQUEST['mes3']);
        $ano           = anti_injection($_REQUEST['ano3']);
        $cmd           = anti_injection($_REQUEST['cmd']);
        $paginaDestino = $pagina_de_origem . '?pSiape=' . $pSiape . '&mes3=' . $mes . '&ano3=' . $ano . '&cmd=' . $cmd;
        break;
}

// pagina de retorno
if ($_SESSION['sPaginaDeRetorno1'] == '')
{
    $_SESSION['sPaginaDeRetorno1'] = $paginaDestino;
    $_SESSION['sPaginaDeRetorno2'] = '';
}
else
{
    $_SESSION['sPaginaDeRetorno2'] = $paginaDestino;
}
$_SESSION['sPaginaDeRetorno3'] = '';
$_SESSION['sPaginaDeRetorno4'] = '';

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->setPagina($paginaDestino);

// mes e ano competencia final (para periodo)
$mes2        = (empty($_REQUEST['mes2']) ? $mes : anti_injection($_REQUEST['mes2']));
$ano2        = (empty($_REQUEST['ano2']) ? $ano : anti_injection($_REQUEST['ano2']));
$anoFinal    = (empty($_REQUEST['anoFinal']) ? $ano : anti_injection($_REQUEST['anoFinal']));
$sMes_Inicio = $mes;
$sMes_Final  = (empty($_REQUEST['mesFinal']) ? $mes2 : anti_injection($_REQUEST['mesFinal']));
$sComp_Final = $ano2 . $mes2;
$sComp_Final = $anoFinal . $sMes_Final;

$_SESSION['sVePonto'] = $paginaDestino;


//instancia o banco de dados
$oDBase = new DataBase('PDO');

// instancia o objeto mens
$oMensagem = new mensagem();
$oMensagem->setDestino(pagina_de_origem());

// validacao dos campos
$validacao = new valida();
$validacao->setExibeMensagem(false);
if ($validacao->competencia($mes, $ano) == false)
{
    $oMensagem->exibeMensagem(102); // competência inválida
}

$comp                 = $mes . $ano;
$titulo_do_formulario = (empty($titulo_do_formulario) ? 'REGISTRO DE COMPARECIMENTO' : $titulo_do_formulario);

## instancia classe frequencia
# inserir feriados e
# carregar dados do servidor
#



$pSiape = getNovaMatriculaBySiape($pSiape);

$oFreq = new formFrequencia;

$oFreq->setOrigem('entrada.php'); // Registra informacoes em sessao
$oFreq->setAnoHoje($ano);        // ano (data atual)
$oFreq->setUsuario($_SESSION['sMatricula']);  // matricula do usuario
$oFreq->setSiape($pSiape);    // matricula do servidor que se deseja alterar a frequencia
$oFreq->setData(date('d') . '/' . $mes . '/' . $ano);
$oFreq->setDia(date('d'));         // dia que se inicia a verificação para incluir frequencia
$oFreq->setMes($mes); // mes que se deseja incluir frequencia
$oFreq->setAno($ano); // ano que se deseja incluir frequencia
$oFreq->setLotacao($lot);     // lotação do servidor que se deseja alterar a frequencia
$oFreq->setNomeDoArquivo('ponto' . $comp); // nome do arquivo de trabalho

## le dados do servidor e setor
#

$oFreq->loadDadosServidor();
$oFreq->loadDadosSetor();

$nome           = $oFreq->getNome();
$sLot           = $oFreq->getLotacao();
$cod_sitcad     = $oFreq->getSituacaoCadastral();
$sLot_descricao = $oFreq->getLotacaoDescricao();
$sUorg          = $oFreq->getSetorUorg();
$chefe          = $oFreq->getChefia();

$anomes_admissao        = $oFreq->getAnoMesAdmissao();
$anomes_exclusao        = $oFreq->getAnoMesExclusao();
$situacao_cadastral     = $oFreq->getSituacaoCadastral();
$banco_compensacao      = $oFreq->getBancoCompensacao();
$banco_compensacao_tipo = $oFreq->getBancoCompensacaoTipo();
$processo_hespecial     = $oFreq->getHorarioEspecialProcesso();
$data_hespecial         = $oFreq->getHorarioEspecialData();
$hora_especial          = $oFreq->getHorarioEspecial();
$motivo_hespecial       = $oFreq->getHorarioEspecialMotivo();

$upg                     = $oFreq->getSetorUpag();
$uorg_pai                = $oFreq->getSetorUorgPai();
$horario_do_setor_inicio = $oFreq->getInicioAtendimento();
$horario_do_setor_fim    = $oFreq->getFimAtendimento();

$sitcad = $oFreq->getSigRegJur();


## - jornada do servidor, por cargo ou horário especial
#  - ponto facultativo (natal, ano novo e quarta-feira de cinzas)
#
#  - verifica se dia ponto facultativo e atribui a jornada correta para o dia
#
$jornada = $oFreq->pontoFacultativo('3');


## - turno estendido
#
$turno_estendido = $oFreq->turnoEstendido('3'); // jornada
$sAutorizadoTE   = $oFreq->getTurnoEstendido();   // informa se o servidor encontra-se em unidade
// autorizada a realizar o turno estendido
## Jornada
#
$jornada         = ($jornada > $turno_estendido && $turno_estendido != '00:00' ? $turno_estendido : $jornada);
$jnd             = $jornada;
$jd              = $jornada / 5;
$j               = formata_jornada_para_hhmm($jornada);

## ocupantes de função
#
$ocupaFuncao = $oFreq->getChefiaAtiva();


if ($ocupaFuncao == 'S')
{
    // - Se titular da função ou em efetiva
    //   substituição, a jornada eh de 40hs
    $jornada = ($motivo_hespecial == 'D' || $motivo_hespecial == 'J' ? $jornada : '40');
    $jnd     = $jornada;
    $jd      = $jornada / 5;
    $j       = formata_jornada_para_hhmm($jornada); // compatibilidade
}
elseif ($sTurnoEstendido == 'N')
{
    // - Indica quem possui jornada menor que 40 horas semanais,
    //   independente do turno estendido
    // - valor de "$jnd" eh a jornada do servidor registrada no
    //   banco de dados SERVATIV (CADASTRO), e não do "$oForm"
    //
    $jornadaMenor8horas = ($jnd < 40);
}

## Horário de Serviço
#
$entrada_no_servico  = $oFreq->getCadastroEntrada();         // horário estabelecido de entrada ao serviço
$saida_do_servico    = $oFreq->getCadastroSaida();           // horário estabelecido do término do almoço
$saida_para_o_almoco = $oFreq->getCadastroInicioIntervalo(); // horário estabelecido de saída (fim do expediente)
$volta_do_almoco     = $oFreq->getCadastroFimIntervalo();    // horário estabelecido do início do almoço
## verifica se feriado, fim de semana
#
$sDiaUtil            = $oFreq->verificaSeDiaUtil(); // feriado nacional, estadual ou municipal e sábado ou domingo
// retorna N se feriado ou fim de semana, caso contrário S
$sd                  = ($sDiaUtil == 'S' ? 0 : 1);
$fer                 = ($sDiaUtil == 'S' ? 0 : 1);

#
## Fim DEFINIÇÃO DA JORNADA


## ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigosCompensaveis              = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);
$codigosDebito                    = $obj->CodigosDebito($sitcad);
$codigosCredito                   = $obj->CodigosCredito($sitcad, $temp=true);
$codigosTrocaObrigatoria          = $obj->CodigosTrocaObrigatoria($sitcad);

$grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);

$codigoSemFrequenciaPadrao        = $obj->CodigoSemFrequenciaPadrao($sitcad);
$codigoRegistroParcialPadrao      = $obj->CodigoRegistroParcialPadrao($sitcad);


## ############################################################## ##
#                                                                  #
#  FREQUENCIA_ACOMPANHAR_REGISTROS_VEPONTO.PHP                     #
#  VEPONTO4_PESQUISA9999.PHP                                       #
#  VEPONTO4_PESQUISA_HORARIO.PHP                                   #
#                                                                  #
## ############################################################## ##


switch ($pagina_de_origem)
{
    case 'frequencia_acompanhar_registros_veponto.php':
    //inserir_dias_sem_frequencia( $pSiape, date('d'), $mes, $ano, $jornada, $lot );
    //break;

    case 'veponto4_pesquisa9999.php':
    case 'veponto4_pesquisa_horario.php':
    //$dia_atual = '';
    //inserir_dias_sem_frequencia( $pSiape, $dia_atual, $mes, $ano, $jornada, $lot );
    //break;

    case 'entrada6.php':
    case 'veponto4.php':
        if ($oFreq->getExcluido() == 'N')
        {
            $dia_limite_para_inserir = ($ano == date('Y') && $mes == date('m') ? date('d') : '');
            inserir_dias_sem_frequencia($pSiape, $dia_limite_para_inserir, $mes, $ano, $jornada, $lot, '', $oFreq->getDataIngressoNoOrgao(), $oFreq->getAnoMesExclusao());
        }
        break;
}


// definicao a lotacao de acordo com a situacao,
// se $cmd==1 o codigo da unidade foi enviado,
// caso contrário será a do usuário
if ($cmd == '1')
{
    $qlotacao = ($_REQUEST['sLotacao'] == '' ? $sLot : anti_injection($_REQUEST['sLotacao']));
}
else
{
    $qlotacao = $_SESSION['sLotacao'];
}

// verifica permissões adicionais
if ($_SESSION['sCAD'] == 'S')
{

}
elseif ($_SESSION['sAPS'] == 'S' && $_SESSION['sRH'] == 'N' && ($sLot != $qlotacao && $uorg_pai != $_SESSION['sLotacao']) && $magico < '3')
{
    //$oMensagem->exibeMensagem(24);
}
elseif ($_SESSION['sRH'] == 'S' && $upg != $_SESSION['upag'])
{
    //$oMensagem->exibeMensagem(25);
}


$idInner   = '';
$html_form = array();

$comp_admissao = substr($anomes_admissao, 6, 4) . substr($anomes_admissao, 3, 2);
$comp_exclusao = substr($anomes_exclusao, 6, 4) . substr($anomes_exclusao, 3, 2);

// grava dados em sessao para uso na impressao
$_SESSION['sIMPPaginaOrigem']     = $pagina_de_origem;
$_SESSION['sIMPMatricula']        = $pSiape;
$_SESSION['sIMPNome']             = $nome;
$_SESSION['sIMPMes']              = $mes;
$_SESSION['sIMPAno']              = $ano;
$_SESSION['sIMPMes2']             = $mes2;
$_SESSION['sIMPAno2']             = $ano2;
$_SESSION['sIMPMes_Inicio']       = $sMes_Inicio;
$_SESSION['sIMPMes_Final']        = $sMes_Final;
$_SESSION['sIMPComp_Final']       = $sComp_Final;
$_SESSION['sIMPCaminho']          = $caminho_modulo_utilizado;
$_SESSION['sIMPCmd']              = $cmd;
$_SESSION['sIMPMagico']           = $magico;
$_SESSION['sIMPLotacao']          = $sLot;
$_SESSION['sIMPLotacaoDescricao'] = $sLot_descricao;
$_SESSION['sIMPTituloFormulario'] = $titulo_do_formulario;

$_SESSION['sIMPAnoMes_admissao']        = $anomes_admissao;
$_SESSION['sIMPAnomes_exclusao']        = $anomes_exclusao;
$_SESSION['sIMPSituacao_cadastral']     = $situacao_cadastral;
$_SESSION['sIMPBanco_compensacao']      = $banco_compensacao;
$_SESSION['sIMPBanco_compensacao_tipo'] = $banco_compensacao_tipo;
$_SESSION['sIMPProcesso_hespecial']     = $processo_hespecial;
$_SESSION['sIMPData_hespecial']         = $data_hespecial;
$_SESSION['sIMPHora_especial']          = $hora_especial;

$_SESSION['sIMPHorario_do_setor_inicio'] = $horario_do_setor_inicio;
$_SESSION['sIMPHorario_do_setor_fim']    = $horario_do_setor_fim;

$_SESSION['sIMPEntrada_no_servico']  = $entrada_no_servico;         // horário estabelecido de entrada ao serviço
$_SESSION['sIMPSaida_do_servico']    = $saida_do_servico;           // horário estabelecido do término do almoço
$_SESSION['sIMPSaida_para_o_almoco'] = $saida_para_o_almoco; // horário estabelecido de saída (fim do expediente)
$_SESSION['sIMPVolta_do_almoco']     = $volta_do_almoco;    // horário estabelecido do início do almoço

$_SESSION['sIMPAutorizadoTE'] = $sAutorizadoTE;    // indica se a unidade encontra-se em turno estendido
//$ano = 2009;
$quebra                       = '';
$contador                     = 0;
for ($i = $sMes_Inicio; $i <= 13; $i++)
{

    if ($i == 13)
    {
        $ano++;
        $i = 1;
    }

    $mes            = substr('00' . $i, -2);
    $comp           = $mes . $ano;
    $comp_invertida = $ano . $mes;

    if ($comp_invertida > $sComp_Final)
    {
        break;
    }


    /* obtem dados da upag para saber se é a mesma do usuario */
    $oDBase->query(
        'SELECT a.cod_lot, b.descricao, b.cod_uorg, b.uorg_pai, b.upag, dt_ing_lot, dt_sai_lot
			FROM histlot AS a
				LEFT JOIN tabsetor AS b ON a.cod_lot = b.codigo
			WHERE a.siape= :siape AND DATE_FORMAT(a.dt_ing_lot,"%Y%m") <= :comp
				AND DATE_FORMAT(a.dt_sai_lot,"%Y%m") = "000000"
			ORDER BY a.dt_ing_lot DESC ', array(
        array(":siape", $pSiape, PDO::PARAM_STR),
        array(":comp", $comp_invertida, PDO::PARAM_STR)
        )
    );
    if ($oDBase->num_rows() > 0)
    {
        $oUORG = $oDBase->fetch_object();
    }
    else
    {
        $oDBase->query(
            'SELECT a.cod_lot, b.descricao, b.cod_uorg, b.uorg_pai, b.upag, a.dt_ing_lot, a.dt_sai_lot
				FROM histlot AS a
					LEFT JOIN tabsetor AS b ON a.cod_lot = b.codigo
				WHERE a.siape = :siape AND DATE_FORMAT(a.dt_ing_lot,"%Y%m") = :comp
					AND DATE_FORMAT(a.dt_sai_lot,"%Y%m") <> "000000"
				ORDER BY a.dt_sai_lot DESC ', array(
            array(":siape", $pSiape, PDO::PARAM_STR),
            array(":comp", $comp_invertida, PDO::PARAM_STR)
            )
        );
        if ($oDBase->num_rows() > 0)
        {
            $oUORG = $oDBase->fetch_object();
        }
    }
    $lot           = $oUORG->cod_lot;
    $lot_descricao = $oUORG->descricao;
    if (empty($lot))
    {
        $lot           = $sLot;
        $lot_descricao = $sLot_descricao;
    }

    $sem_registros_para_exibir = 'Sem registros para exibir!';

    $quebra = (empty($quebra) ? 'nao' : 'sim');

    //obtem dados da homologação
    $status = verifica_se_mes_homologado($pSiape, $ano . $mes);

    $oDBase->setMensagem('VePonto: Erro no acesso ao banco de dados!\\nPor favor, tente mais tarde.');
    $oDBase->query(
        'SELECT a.entra, DATE_FORMAT(a.dia, "%d/%m/%Y") AS dia, a.intini, a.intsai, a.sai, a.jornd, a.jornp, a.jorndif, a.oco,
				REPLACE(a.just,";",":") AS just, a.idreg, b.desc_ocorr AS dcod, d.codmun, d.codigo, a.idreg, a.ip, a.matchef, a.siaperh,
				REPLACE(a.justchef,";",":") AS justchef
			FROM ponto' . $comp . ' AS a
				LEFT JOIN tabocfre AS b ON a.oco = b.siapecad
				LEFT JOIN servativ AS c ON a.siape = c.mat_siape
				LEFT JOIN tabsetor AS d ON c.cod_lot = d.codigo
			WHERE a.siape = :siape AND dia <> "0000-00-00" ORDER BY a.dia ', array(
        array(":siape", $pSiape, PDO::PARAM_STR)
        )
    );

    $html_form[$contador] = '<table align=\'center\' ' . ($quebra == 'sim' ? 'style=\'page-break-before: always\'' : '') . '><tr><td>';


    ## ############################################################## ##
    #                                                                  #
    #  FREQUENCIA_ACOMPANHAR_REGISTROS_VEPONTO.PHP                     #
    #                                                                  #
    ## ############################################################## ##
    if ($pagina_de_origem == 'frequencia_acompanhar_registros_veponto.php')
    {
        $html_form[$contador] .= "<font color='red'>ATENÇÃO</font>: Para alterar o registro, clique sobre o código da ocorrência, ou sobre a palavra 'Abonar' se optar por abonar o dia.";
    }


    $html_form[$contador] .= "
		<form method='post' action='" . $form_action . "' " . ($form_submit == "" ? "" : "onSubmit='" . $form_submit . "'") . " id='form1' >
			<input type='hidden' id='mes'   name='mes'   value='" . substr($comp, 0, 2) . "'>
			<input type='hidden' id='ano'   name='ano'   value='" . substr($comp, 2, 4) . "'>
			<input type='hidden' id='dados' name='dados' value='" . base64_encode($pSiape . ':|:' . $ano . '-' . $mes . '-01') . "'>
		";

    ## ############################################################## ##
    #                                                                  #
    #  REGFREQGEX.PHP                                                  #
    #                                                                  #
    ## ############################################################## ##
    if (substr_count($_SESSION['sPaginaRetorno_sucesso'], 'regfreqgex.php') > 0)
    {
        $html_form[$contador] .= "
			<table border='0' cellpadding='0' cellspacing='0' width='100%'>
			<tr>
			<td align='left'><div><a href=\"javascript:AbreJanela('veponto.php',920,700);\">Clique aqui para Consultar outra competência</a></div></td>
			<td align='right'><div>" . botao("Voltar", "window.location.replace(\"" . $_SESSION['sPaginaRetorno_sucesso'] . "\");") . "</div><td>
			</tr>
			</table>";
    }

    $html_form[$contador] .= "
		<table class='tablew2' width='100%' border='0' align='center' cellpadding='0' cellspacing='0' style='border-collapse: collapse'>
		<tr>
		<td>

		<table class='tablew21' width='100%' border='0' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse;font-size: 13px'>
		<tr>
		<td colspan='5' valign='middle' height='30' class='ftFormFreq-bc-2'><label class='control-label'>" . substr($comp, 0, 2) . "/" . substr($comp, 2, 4) . "</label></td>
		</tr>";

    ## ############################################################## ##
    #                                                                  #
    #  ENTRADA6.PHP                                                    #
    #                                                                  #
    ## ############################################################## ##
    if ($pagina_de_origem == 'entrada6.php')
    {
        $html_form[$contador] .= "
			<tr>
			<td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='4'>SITUACAO</td>
			<td height='20' class='ftFormFreq-tit-bc'>ADMISSAO</td>
			</tr>
			<tr>
			<td height='20' colspan='4' class='ftFormFreq-bc'>" . tratarHTML($status) . "</td>
			<td height='20' colspan='1' class='ftFormFreq-c-2'>" . tratarHTML($anomes_admissao) . "</td>
			</tr>";
    }
    else
    {
        $html_form[$contador] .= "
			<tr>
			<td height='20' class='ftFormFreq-tit-bc' width='30%'><label class='control-label'>Compensação : " . ($banco_compensacao == 'S' ? "" : "NAO ") . "AUTORIZADA" . "</label></td>
			</label>
			</td>
			<td height='20' class='ftFormFreq-tit-bc' width='15%' colspan='2'><label class='control-label'>Situação : " . $status . "</label></td>
			<td height='20' class='ftFormFreq-tit-bc' width='20%' ><label class='control-label'>Admissão : " . tratarHTML($anomes_admissao) . "</label></td>
			</tr>
			<tr>



			</tr>";
    }

    $html_form[$contador] .= "
		<tr>
		<td height='20' class='ftFormFreq-tit-bc'><label class='control-label'>SIAPE : " . removeOrgaoMatricula($pSiape) . "</label>
		<input type='hidden' id='siape' name='siape' class='centro' value='" . tratarHTML($pSiape) . "' size='10' readonly>
		</td>
		<td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='4'><label class='control-label'>Nome : " . tratarHTML($nome) . "</label>
		<input type='hidden' id='nome' name='nome' class='Caixa' value='" . tratarHTML($nome) . "' size='65' readonly>
		</td>
		</tr>

		<tr>
		<td height='20' colspan='3' class='ftFormFreq-tit-bc' align='left'><label class='control-label'>Lotação : " . tratarHTML($lot) . "  -  " . tratarHTML($lot_descricao) . "</label>
		<input name='lotacao' type='hidden' class='centro' id='lotacao' value='" . tratarHTML($lot) . "' size='15' readonly>
		<input name='lotacao_descricao' type='hidden' class='Caixa' id='lotacao_descricao' value=' " . tratarHTML($lot_descricao) . "' size='90' readonly>
		</td>
		</tr>
		<tr>
		<td height='25' colspan='5'>
		<table border='0' style='width: 100%; text-align: center;'><tr><td>


		</td></tr></table>
		</td>
		</tr>";

    ## ############################################################## ##
    #                                                                  #
    #  TODOS EXCETO "ENTRADA6.PHP"                                     #
    #                                                                  #
    ## ############################################################## ##
    if ($pagina_de_origem != 'entrada6.php')
    {

        $html_form[$contador] .= "
			<tr>
			<td colspan='1' rowspan='2' class='ftFormFreq-tit-bc-3'><label class='control-label'>Horário do Setor</label></td>
			<td height='20' colspan='3' class='ftFormFreq-tit-bc-3'><label class='control-label'>Horário do Servidor</label></td>
			<td height='20' rowspan='2' class='ftFormFreq-tit-bc-3'><label class='control-label'>Horário Especial</label></td>
			</tr>
			<tr>
			<td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Entrada</td>
			<td width='25%' height='20' class='ftFormFreq-tit-bc-3'>Intervalo</td>
			<td width='15%' height='20' class='ftFormFreq-tit-bc-3'>Saída</td>
			<td></td>
			</tr>
			<tr>
			<td height='25' colspan='1' align='left' nowrap>
			&nbsp;<label class='control-label'><input type='text' id='inicio' name='inicio' class='form-control-input centro' value='" . tratarHTML($horario_do_setor_inicio) . "' size='8' readonly>
			&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>
			&nbsp;<input type='text' id='fim' name='fim' class='form-control-input centro' value='" . tratarHTML($horario_do_setor_fim) . "' size='8' readonly>&nbsp;
			</label>
			</td>
			<td height='25' align='left'><label class='control-label'>
			&nbsp;<input type='text' id='entrada' name='entrada' class='form-control-input centro' value='" . tratarHTML($entrada_no_servico) . "' size='10' readonly>&nbsp;</label>
			</td>
			<td height='25' align='left'><label class='control-label'>
			&nbsp;<input type='text' id='interve' name='interve' class='form-control-input centro' value='" . tratarHTML($saida_para_o_almoco) . "' size='10' readonly>
			&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>
			&nbsp;<input type='text' id='intervs' name='intervs' class='form-control-input centro' value='" . tratarHTML($volta_do_almoco) . "' size='10' readonly>&nbsp;</label>
			</td>
			<td height='25' align='left'><label class='control-label'>
			&nbsp;<input name='saida' type='text' class='form-control-input centro' id='saida' value='" . tratarHTML($saida_do_servico) . "' size='10' readonly>&nbsp;</label>
			</td>
			<td height='25' colspan='1' class='ftFormFreq-c'>
			<b>" . ($hora_especial == "S" ? "SIM, $processo_hespecial" : "NAO") . "</b>
			</td>
			</tr>";
    }


    $html_form[$contador] .="</table>

		</td>
		</tr>
		<tr>
		<td colspan='4'>

		<table id='myTable' class='table table-striped table-bordered text-center table-condensed tablesorter' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
		<tr>
		<td width='10%' height='22' class='ftFormFreq-bc-1'><label class='control-label'>Dia</label></td>
		<td width='9%' class='ftFormFreq-bc-1'><label class='control-label'>Entrada</label></td>
		<td width='9%' class='ftFormFreq-bc-1'><label class='control-label'>Ida intervalo</label></td>
		<td width='9%' class='ftFormFreq-bc-1'><label class='control-label'>Volta Intervalo</label></td>
		<td width='9%' class='ftFormFreq-bc-1'><label class='control-label'>Saida</label></td>
		<td width='9%' class='ftFormFreq-bc-1'><label class='control-label'>Resultado</label></td>
		<td width='10%' class='ftFormFreq-bc-1'><label class='control-label'>" . ($sAutorizadoTE == 'S' ? 'Turno Previsto' : 'Jornada prevista') . "</label></td>
		<td width='9%' class='ftFormFreq-bc-1'><label class='control-label'>Saldo do dia</label></td>
		<td width='8%' class='ftFormFreq-bc-1'><label class='control-label'>Ocorrencia</label></td>";


    ## ############################################################## ##
    #                                                                  #
    #  PONTOSER.PHP                                                    #
    #  REGFREQ6.PHP                                                    #
    #  REGFREQ8.PHP                                                    #
    #  FREQUENCIA_VERIFICAR_HOMOLOGADOS_VISUALIZAR.PHP                 #
    #  VEPONTO2.PHP                                                    #
    #  VEPONTO_FORMULÁRIO.PHP                                          #
    #  VEPONTO4.PHP                                                    #
    #  e OUTROS EXCETO "ENTRADA6.PHP"                                  #
    #                                                                  #
    ## ############################################################## ##
    if ($pagina_de_origem == 'frequencia_acompanhar_registros_veponto.php')
    {
        $html_form[$contador] .= "
			<td width='9%' class='ftFormFreq-bc-1'>Ação</td>
			<td width='9%' class='ftFormFreq-bc-1'>Registrado</td>";
    }

    ## ############################################################## ##
    #                                                                  #
    #  PONTOSER.PHP                                                    #
    #  REGFREQ6.PHP                                                    #
    #  REGFREQ8.PHP                                                    #
    #  FREQUENCIA_VERIFICAR_HOMOLOGADOS_VISUALIZAR.PHP                 #
    #  VEPONTO2.PHP                                                    #
    #  VEPONTO_FORMULÁRIO.PHP                                          #
    #  VEPONTO4.PHP                                                    #
    #  e OUTROS EXCETO "ENTRADA6.PHP"                                  #
    #                                                                  #
    ## ############################################################## ##
    else if ($pagina_de_origem != 'entrada6.php')
    {
        $html_form[$contador] .= "
			<td width='9%' class='ftFormFreq-bc-1'><label class='control-label'>Registrado</label></td>";
    }

    $html_form[$contador] .= "
		</tr>";

    // grava o LOG
    $vHoras = strftime("%H:%M:%S", time());
    $vDatas = date("Y/m/d");
    $hoje   = date("d/m/Y");

    $nlinhas = $oDBase->num_rows();
    if ($nlinhas == 0)
    {
        if ($comp_admissao > $comp_invertida && $i == $sMes_Final)
        {
            $sem_registros_para_exibir = "<center><br>" . ($situacao_cadastral == '66' ? "ESTAGIARIO(A) REGISTRADO(A) EM " : "SERVIDOR(A) ADMITIDO(A)/REGISTRADO(A) EM ") . $anomes_admissao . "<br>- Sem Registro de Frequencia em meses anteriores -<br><br></center>";
        }
        $html_form[$contador] .= "
			<tr>
				<td colspan='10' style='height: 30; text-align: center;'>$sem_registros_para_exibir</td>
			</tr>";
    }
    else
    {
        $umavez      = true;
        while ($pm_partners = $oDBase->fetch_object())
        {
            if ($umavez == true)
            {
                $umavez       = false;
                $dia_nao_util = marca_dias_nao_util($mes, $ano, $pm_partners->codmun, $pm_partners->codigo);
            }
            $xdia       = $pm_partners->dia;
            $background = $dia_nao_util[$xdia][0];
            $color      = $dia_nao_util[$xdia][1];

            $regjustab = "regjustab.php?mat=$pSiape&nome=$nome&dia=$dia&oco=$oco&cmd=1";

            if (in_array($pm_partners->oco, $codigosTrocaObrigatoria))
            {
                $pm_partners_oco = "<font color='red'><b>" . $pm_partners->oco . "</b></font>";
            }
            else
            {
                $pm_partners_oco = $pm_partners->oco;
            }

            switch ($pagina_de_origem)
            {
                case 'pontoser.php':
                case 'entrada6.php':
                    $sDados         = $pSiape . ":|:" . utf8_iso88591(str_replace('"', '', $nome)) . ":|:" . $sLot . ":|:" . $comp . ":|:" . $pm_partners->dia . ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->just)) . ":|:" . $pm_partners->oco . ":|:" . $cmd;
                    $matual         = date("mY");
                    $mes_homologado = verifica_se_mes_homologado($pSiape, substr($comp, 2, 4) . substr($comp, 0, 2));
                    if ($mes_homologado == 'HOMOLOGADO') //define que soh poderah ver justificativa de ocorrencias em meses anteriores
                    {
                        $sDados .= ":|:sim";
                    }
                    else
                    {
                        $sDados .= ":|:";
                    }
                    $sDados .= ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->justchef));

                    $dados                = base64_encode($sDados);
                    $codigo_de_ocorrencia = ($pm_partners->just == "" ? "" : "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;") . "<a id='dia" . substr($pm_partners->dia, 0, 2) . "' href='regjust.php?dados=" . $dados . "'>" . $pm_partners_oco . "</a>";
                    break;

                case 'veponto2.php':
                    $sDados = $pSiape . ":|:" . utf8_iso88591(trata_aspas($nome)) . ":|:" . $comp . ":|:" . $pm_partners->dia . ":|:" . utf8_iso88591(trata_aspas($pm_partners->just)) . ":|:" . $pm_partners->oco . ":|:" . $pm_partners->idreg . ":|:" . $cmd . ":|:" . $jnd1 . ":|:" . $so_ver . ':|:' . utf8_iso88591(trata_aspas($pm_partners->justchef));
                    $dados  = base64_encode($sDados);
                    if (empty($pm_partners->just))
                    {
                        $codigo_de_ocorrencia = "<img src='" . _DIR_IMAGEM_ . "transp1x1.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href= 'vejust.php?dados=" . $dados . "'>" . $pm_partners_oco . "</a>";
                    }
                    else
                    {
                        $codigo_de_ocorrencia = "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href='vejust.php?dados=" . $dados . "' alt='" . $pm_partners->just . "'>" . $pm_partners_oco . "</a>";
                    }
                    break;


                ## ############################################################## ##
                #                                                                  #
                #  FREQUÊNCIA ACOMPANHAR REGISTROS - VE PONTO (MANUTENÇÃO)         #
                #                                                                  #
                ## ############################################################## ##
                case 'frequencia_acompanhar_registros_veponto.php':
                    $dados = base64_encode($pSiape . ":|:" . $pm_partners->dia . ":|:" . $cod_sitcad . ":|:" . $cmd . ":|:" . $so_ver . ":|:acompanhar");
                    if (empty($pm_partners->just))
                    {
                        $codigo_de_ocorrencia = "<img src='" . _DIR_IMAGEM_ . "transp1x1.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href= 'javascript:window.location.replace(\"frequencia_ver_justificativa.php?dados=" . $dados . "\");'>" . $pm_partners_oco . "</a>";
                    }
                    else
                    {
                        $codigo_de_ocorrencia = "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href= 'javascript:window.location.replace(\"frequencia_ver_justificativa.php?dados=" . $dados . "\");' alt='" . trata_aspas($pm_partners->just) . "\");'>" . $pm_partners_oco . "</a>";
                    }


                    // ABONO - Prepara o link
                    if (in_array($pm_partners->oco, $grupoOcorrenciasPassiveisDeAbono))
                    {
                        $dados       = base64_encode($pSiape . ":|:" . $pm_partners->dia . ":|:1:|:" . $so_ver . ":|:acompanhar_veponto");
                        $link_abonar = "<a href='javascript:window.location.replace(\"frequencia_justificativa_abono.php?dados=" . $dados . "\");' style='color: #4b4b4b;'>Abonar</a>";
                    }
                    else
                    {
                        $link_abonar = "<a href='javascript:return false;' style='cursor: none; text-decoration: none; color: #f8f8f8;' title='" . $pm_partners->justchef . "' alt='" . $pm_partners->justchef . "' disabled>Abonar</a>";
                    }
                    break;



                ## ############################################################## ##
                #                                                                  #
                #  REGFREQ6.PHP                                                    #
                #                                                                  #
                ## ############################################################## ##
                case 'regfreq6.php':
                    //$sRegistro8 = base64_encode( $pSiape . ":|:" . $nome . ":|:" . $pm_partners->dia . ":|:" . $pm_partners->oco . ":|:" . $lot . ":|:" . $pm_partners->idreg . ":|:" . $cmd );
                    if ($pm_partners->just == "")
                    {
                        //$codigo_de_ocorrencia = "<a>" . $pm_partners->oco . "</a> - <a href='registro8.php?dados=" . $sRegistro8 . "'>Alterar</a>";
                        $codigo_de_ocorrencia = "<a>" . $pm_partners_oco . "</a> - <a href='registro8.php?mat=$pSiape&nome=$nome&dia=" . $pm_partners->dia . "&lot=$lot&mes=$mes&ano=$anot&c=$cmd'><img border=\"0\" src=\"./imagem/edicao2.jpg\" width=\"16\" height=\"16\" align=\"absmiddle\" title=\"Registrar por dia\" alt=\"Registrar por dia\"></a>";
                    }
                    else
                    {
                        $sDados               = $mat . ":|:" . utf8_iso88591(str_replace('"', '', $nome)) . ":|:" . $comp . ":|:" . $pm_partners->dia . ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->just)) . ":|:" . $pm_partners->oco . ":|:" . $pm_partners->idreg . ":|:" . $cmd . ":|:" . $jnd1 . ":|:" . $so_ver . ':|:' . utf8_iso88591(str_replace('"', '', $pm_partners->justchef));
                        $dados                = base64_encode($sDados);
                        $codigo_de_ocorrencia = "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href=\"javascript:Abre('vejust.php?dados=" . $dados . "',855,460)\">" . $pm_partners_oco . "</a> - <a href='registro8.php?mat=$pSiape&nome=$nome&dia=" . $pm_partners->dia . "&lot=$lot&mes=$mes&ano=$anot&c=$cmd'>Alterar</a>";
                    }
                    break;
                case 'regfreq8.php':
                case 'frequencia_verificar_homologados_visualizar.php':
                default:
                    if ($pm_partners->just == "")
                    {
                        $codigo_de_ocorrencia = "<table border='0' cellpadding='0' cellspacing='0' align='center'><tr><td>&nbsp;</td><td class='ftFormFreq-cn-1' style='" . $color . "; text-align: right;'>" . $pm_partners_oco . "</td></tr></table>";
                    }
                    else
                    {
                        $dados                    = base64_encode($pSiape . ":|:" . utf8_iso88591(str_replace('"', '', $nome)) . ":|:" . $comp . ":|:" . $pm_partners->dia . ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->just)) . ":|:" . $pm_partners->oco . ":|:" . $pm_partners->idreg . ":|:" . $cmd . ":|:" . $jnd . ":|:sim:|:" . utf8_iso88591(str_replace('"', '', $pm_partners->justchef)));
                        $_SESSION['sDadosBase64'] = $dados;

                        $codigo_de_ocorrencia = "<table border='0' cellpadding='0' cellspacing='0' align='center'><tr><td><img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''></td><td class='ftFormFreq-cn-1' style='" . $color . "; text-align: right;'><a href= 'vejust.php?dados=" . $dados . "'>" . $pm_partners_oco . "</a></td></tr></table>";
                    }
                    break;
            }

            $registrado_por = define_quem_registrou_descricao($pm_partners, $situacao_cadastral, $comp_invertida);

            if ($pm_partners->justchef != "")
            {
                if ($pagina_de_origem == 'frequencia_acompanhar_registros_veponto.php')
                {
                    $dados          = base64_encode($pSiape . ":|:" . $pm_partners->dia . ":|:" . $cod_sitcad . ":|:" . $cmd . ":|:sim:|:acompanhar");
                    $registrado_por = "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href='javascript:window.location.replace(\"frequencia_ver_justificativa.php?dados=" . $dados . "\");'>" . $registrado_por . "</a>";
                }
                else
                {
                    $dados          = base64_encode($pSiape . ":|:" . $nome . ":|:" . $pm_partners->dia . ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->justchef)) . ":|:" . $pm_partners->oco . ":|:" . $cmd . ":|:sim");
                    $registrado_por = "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href= 'regjustab.php?dados=" . $dados . "'>" . $registrado_por . "</a>";
                }
            }

            $font_i_color = "";
            $font_f_color = "";
            $sinal        = '&nbsp;';

            // elimina "/" e ":", depois define o tipo como inteiro
            // para garantir a resultado do teste a seguir
            $jornada_dif = alltrim(sonumeros($pm_partners->jorndif), '0');
            settype($jornada_dif, 'integer');


            if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCompensaveis))
            {
                $font_i_color = "<font color='red'>";
                $font_f_color = "</font>";
                $sinal        = "<font color='red'> - </font>";
            }
            else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosDebito))
            {
                $font_i_color = "<font color='red'>(";
                $font_f_color = ")</font>";
                $sinal        = "";
            }
            else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCredito))
            {
                $sinal = " + ";
            }

            if (in_array($pm_partners->oco, $codigosTrocaObrigatoria))
            {
                $codigo_da_ocorrencia = "<font color='red'><b>" . $pm_partners->oco . "</b></font>";
            }
            else
            {
                $codigo_da_ocorrencia = $pm_partners->oco;
            }

            $html_form[$contador] .= "
				<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' style='" . $background . "'>
					<td width='100px' class='ftFormFreq-cn-1' style='" . $color . "' title='" . $dia_nao_util[$xdia][4] . "' nowrap>&nbsp;" . trim($dia_nao_util[$xdia][2]) . '&nbsp;' . trim($xdia . $dia_nao_util[$xdia][3]) . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm_partners->entra) . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm_partners->intini) . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm_partners->intsai) . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm_partners->sai) . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm_partners->jornd) . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm_partners->jornp) . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;'>
					<table border='0' cellpadding='0' cellspacing='0' align='center'>
					<tr>
					<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;' width='13'>" . $sinal . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;' width='37'>" . $font_i_color . tratarHTML($pm_partners->jorndif) . $font_f_color . "</td>
					</tr>
					</table>
					</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;' title='" . tratarHTML($pm_partners->dcod) . "\n" . tratarHTML($pm_partners->just) . "' " . ($pagina_de_origem == 'regfreq6.php' ? 'nowrap' : '') . ">" . $codigo_de_ocorrencia . "</td>";

            if ($pagina_de_origem == 'frequencia_acompanhar_registros_veponto.php')
            {
                $html_form[$contador] .= "
					<td class='ftFormFreq-cn-1' style='text-align: center;'>" . $link_abonar . "</td>
					<td class='ftFormFreq-cn-1' style='" . $color . "'>" . $registrado_por . "</td>";
            }
            else if ($pagina_de_origem != 'entrada6.php')
            {
                $html_form[$contador] .= "
					<td class='ftFormFreq-cn-1' style='" . $color . "'>" . $registrado_por . "</td>";
            }

            $html_form[$contador] .= "
				</tr>";
        } // fim do while
    }

    $html_form[$contador] .= "
		</table>
		</td>
		</tr>
		<tr>
		<td colspan='4'>";

    // ROTINA DE TOTALIZAÇÃO DAS HORAS
    $total_horas = rotina_de_totalizacao_de_horas($pSiape, $comp);

    // FIM DO CALCULO DOS TOTAIS

    $rowspan       = 0;
    $width_percent = '71%';
    $nbspace       = '&nbsp;';
    switch ($pagina_de_origem)
    {
        case 'regfreq8.php':
        case 'frequencia_verificar_homologados_visualizar.php':
            $rowspan       = 1;
            $regfreq7      = "<td rowspan='4' valign='middle' width='21%'><div align='center'>&nbsp;&nbsp;<font style='font-size: 10; font-family: verdana;'>Acao:</font><a href='frequencia_verificar_homologados_devolucao.php?mat=" . $pSiape . "' style='color: #0055AA; font-size: 10;'>REJEITAR</a>&nbsp;&nbsp;</div></td>";
            $width_percent = '66%';
            $nbspace       = "<img src='" . _DIR_IMAGEM_ . "transp1x1.gif' width='77' height='1'>";
            break;
    }
    $rowspan += ($total_horas->recesso[1] != 0 ? 1 : 0);
    $rowspan += ($total_horas->instrutoria[1] != 0 ? 1 : 0);
    $rowspan += ($total_horas->extras[1] != 0 ? 1 : 0);
    $rowspan += ($total_horas->copa2014[1] != 0 ? 1 : 0);

    $html_form[$contador] .= "
		<table class='tablew21' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>";
    if ($regfreq7 != '')
    {
        $html_form[$contador] .= "
			<tr>
			" . $regfreq7 . "
			<td colspn='4' width='" . $width_percent . "'><div align='center'>&nbsp;</div></td>
			</tr>";
    }

    if (time_to_sec(substr($total_horas->recesso[1], 1, 5)) != 0)
    {
        $html_form[$contador] .= "
			<tr>
			<td><div align='center'><font size='1'>Total de horas Recesso Anual</font></div></td>
			<td><div align='center'>&nbsp;<font size='1'>" . tratarHTML($total_horas->recesso[1]) . "</font>&nbsp;</div></td>
			<td><div align='center'>&nbsp;<font size='1'>" . tratarHTML($total_horas->recesso[0]) . "</font>&nbsp;</div></td>
			<td><div align='center'>&nbsp;</div></td>
			</tr>";
    }

    if (time_to_sec(substr($total_horas->instrutoria[1], 1, 5)) != 0)
    {
        $html_form[$contador] .= "
			<tr>
			<td><div align='center'><font size='1'>Total de horas Instrutoria</font></div></td>
			<td><div align='center'>&nbsp;<font size='1'>" . tratarHTML($total_horas->instrutoria[1]) . "</font>&nbsp;</div></td>
			<td><div align='center'>&nbsp;<font size='1'>" . tratarHTML($total_horas->instrutoria[0]) . "</font>&nbsp;</div></td>
			<td><div align='center'>&nbsp;</div></td>
			</tr>";
    }

    if (time_to_sec(substr($total_horas->extras[1], 1, 5)) != 0)
    {
        $html_form[$contador] .= "
			<tr>
			<td><div align='center'><font size='1'>Total de horas Horas-extras</font></div></td>
			<td><div align='center'>&nbsp;<font size='1'>" . tratarHTML($total_horas->extras[1]) . "</font>&nbsp;</div></td>
			<td><div align='center'>&nbsp;<font size='1'>" . tratarHTML($total_horas->extras[0]) . "</font>&nbsp;</div></td>
			<td><div align='center'>&nbsp;</div></td>
			</tr>";
    }

    if (time_to_sec(substr($total_horas->copa2014[1], 1, 5)) != 0)
    {
        $html_form[$contador] .= "
			<tr>
			<td><div align='center'><font size='1'>Total de horas Copa do Mundo 2014</font></div></td>
			<td><div align='center'>&nbsp;<font size='1'>" . tratarHTML($total_horas->copa2014[1]) . "</font>&nbsp;</div></td>
			<td><div align='center'>&nbsp;<font size='1'>" . tratarHTML($total_horas->copa2014[0]) . "</font>&nbsp;</div></td>
			<td><div align='center'>&nbsp;</div></td>
			</tr>";
    }

    $html_form[$contador] .= "
		</table>
		</td>
		</tr>
		<tr>
		<td colspan='4' style='border-top: 0 solid #808040; border-left: 0 solid #808040; border-right: 0 solid #808040; border-bottom: 0 solid #808040;'>";


    $html_form[$contador] .= "
		<table class='tablew21' width='100%' border='0' align='center' cellpadding='0' cellspacing='0' id='AutoNumber1' style='border-collapse: collapse;'>
		<tr>
		<td style='font-size: 8px;'><font color='red'><b>D: </b></font>Domingo&nbsp;&nbsp;&nbsp;&nbsp;<font color=red><b>S: </b></font>Sabado&nbsp;&nbsp;&nbsp;&nbsp;<font color=red><b>F: </b></font>Feriado/Facultativo (Posicione o mouse sobre o dia para ver a descricao)</td>
		</tr>
		</table>

		</td>
		</tr>
		<tr>
		<td colspan='4'>";

    ## *******************************************************
    # SALDOS DE HORAS COMUNS NO MES
    #
		# Atribui o código html resultante a uma variavel "$html"
    # se o valor de "$bExibeResultados" for igual a "true"
    # ********************************************************
    #
		$bSoSaldo         = true;
    $bParcial         = ($status == 'HOMOLOGADO' ? false : true);
    $bImprimir        = false;
    $bExibeResultados = false;
    $relatorioTipo    = '0';
    //$mes2 = date('m');
    //$ano2 = date('Y');
    $tipo             = 0;

    //
    // $pSiape : definido no início do script
    // $mes    : definido no início do script
    // $ano    : definido no início do script
    // $mes2   : definido no início do script
    // $ano2   : definido no início do script
    $mesFim = $mes;
    $anoFim = $ano;

    include_once( "veponto_saldos.php" );

    $mesFim               = $mes;
    $anoFim               = $ano;
    $html_form[$contador] .= imprimirSaldoCompensacaoDoMes();

    //$html_form[$contador] .= $html;
    #
		## *******************************************************

    $html_form[$contador] .= "
		</td>
		</tr>
		</table>";

    // A variável '$comp', que está no formato (mmAAAA) sofre inversão
    // para (AAAAmm) no script "veponto_saldos.php" incluído acima,
    // na linha 1009 aproximadamente, então, aqui retornamos os dados
    // da '$comp' para o formato (mmAAAA)
    $comp = substr($comp, -2) . substr($comp, 0, 4);

    $total_ocorrencias = rotina_contar_ocorrencias($pSiape, $comp, implode(',', $codigosTrocaObrigatoria));

    for ($x = 0; $x < count($total_ocorrencias); $x++)
    {
      if (in_array($total_ocorrencias[$x][0], $codigoRegistroParcialPadrao))
      {
        $ocorrencias_88888 = $total_ocorrencias[$x][1];
      }

      if (in_array($total_ocorrencias[$x][0], $codigoSemFrequenciaPadrao))
      {
        $ocorrencias_99999 = $total_ocorrencias[$x][1];
      }

      if ($total_ocorrencias[$x][0] == $codigosTrocaObrigatoria[0]) //'-----'
      {
        $ocorrencias_tracos = $total_ocorrencias[$x][1];
      }
    }

    $dias_registrados   = numero_dias_do_mes($mes, $ano);
    $total_de_registros = $nlinhas;

    switch ($pagina_de_origem)
    {
        case 'regfreq8.php':
            $html_form[$contador] .= "
				<div align='center'>
				<p><font size='2'><strong>
				<input type='hidden' id='teste'  name='teste'  value='" . tratarHTML($ocorrencias_88888) . "' readonly>
				<input type='hidden' id='teste2' name='teste2' value='" . tratarHTML($total_de_registros) . "' readonly>
				<input type='hidden' id='teste3' name='teste3' value='" . tratarHTML($dias_registrados) . "'  readonly>
				<input type='hidden' id='teste9' name='teste9' value='" . tratarHTML($ocorrencias_99999) . "' readonly>
				<input type='hidden' id='teste_tracos' name='teste_tracos' value='" . tratarHTML($ocorrencias_tracos) . "' readonly>

				<input type='hidden' id='codigoSemFrequenciaPadrao'   name='codigoSemFrequenciaPadrao'   value='" . tratarHTML(implode(',', $codigoSemFrequenciaPadrao)) . "'>
				<input type='hidden' id='codigoRegistroParcialPadrao' name='codigoRegistroParcialPadrao' value='" . tratarHTML(implode(',', $codigoRegistroParcialPadrao)) . "'>
                <input type='hidden' id='codigosTrocaObrigatoria'     name='codigosTrocaObrigatoria'     value='" . tratarHTML($codigosTrocaObrigatoria[0]) . "'>

				Para Confirmar a verificacao clique em CONCLUIR caso contrario clique em REJEITAR</strong></font><br>

                <table border='0' align='center'>
					<tr>
						<td align='center'>
							<input type='image' border='0' src='" . _DIR_IMAGEM_ . "concluir.gif' name='enviar' alt='Confirmar verificação de ponto Homologado' align='center' />
						</td>
					</tr>
				</table>
				</p>
				</div>";
            //		<td align='left'>". botao('Voltar', 'javascript:window.location.replace("'.$_SESSION['voltar_nivel_1'].'");') ."</td>
            break;

        case 'frequencia_verificar_homologados_visualizar.php':
            $html_form[$contador] .= "
				<div align='center'>
				<p><font size='2'><strong>
				<input type='hidden' id='teste'  name='teste'  value='" . tratarHTML($ocorrencias_88888) . "' readonly>
				<input type='hidden' id='teste2' name='teste2' value='" . tratarHTML($total_de_registros) . "' readonly>
				<input type='hidden' id='teste3' name='teste3' value='" . tratarHTML($dias_registrados ). "'  readonly>
				<input type='hidden' id='teste9' name='teste9' value='" . tratarHTML($ocorrencias_99999 ). "' readonly>
				<input type='hidden' id='teste_tracos' name='teste_tracos' value='" . tratarHTML($ocorrencias_tracos) . "' readonly>

				<input type='hidden' id='codigoSemFrequenciaPadrao' name='codigoSemFrequenciaPadrao' value='" . tratarHTML(implode(',', $codigoSemFrequenciaPadrao)) . "'>
				<input type='hidden' id='codigoSemFrequenciaPadrao' name='codigoSemFrequenciaPadrao' value='" . tratarHTML(implode(',', $codigoSemFrequenciaPadrao)) . "'>
                <input type='hidden' id='codigosTrocaObrigatoria'   name='codigosTrocaObrigatoria'   value='" . tratarHTML($codigosTrocaObrigatoria[0]) . "'>

                Para Confirmar a verificacao clique em CONCLUIR caso contrario clique em REJEITAR</strong></font><br>

				<table border='0' align='center'>
					<tr>
						<td align='left'>
							<input type='image' border='0' src='" . _DIR_IMAGEM_ . "concluir.gif' name='enviar' alt='Confirmar verificação de ponto Homologado' align='center' />
						</td>
						<td>&nbsp;&nbsp;&nbsp;</td>
						<td align='left'>" . botao('Fechar', "javascript:window.parent.closeIFrame();") . "</td>
					</tr>
				</table>
				</p>
				</div>";
            break;

        case 'frequencia_acompanhar_registros_veponto.php':
            $html_form[$contador] .= "
				<div align='center'>
				<p>
				<table border='0' align='center'>
					<tr>
						<td align='left'>" . botao('Voltar', 'javascript:window.location.replace("' . $_SESSION['voltar_nivel_1'] . '");') . "</td>
					</tr>
				</table>
				</p>
				</div>";
            break;
    }

    $html_form[$contador] .= "
		</form>";

    switch ($pagina_de_origem)
    {
        case 'entrada6.php':
            $html_form[$contador] .= "
				<style> .ftRodape { font-size: 10px; font-family: Time New; font-weight: normal; word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; height: 15px; vertical-align: middle; } </style>
				<table width='100%' border='0' align='left' cellpadding='0' cellspacing='0' style='border-collapse: collapse'>
				<tr><td class='ftRodape'>Obs: Clique na ocorr&ecirc;ncia para apresentar justificativa.</td></tr>
				<!-- <tr><td class='ftRodape'>Obs: As horas credoras no mês, decorrente de compensação autorizada pela chefia, são utilizadas exclusivamente para compensa&ccedil;&atilde;o de atrasos, faltas justificadas, sa&iacute;das antecipadas e aus&ecirc;ncias dentro do mês de competência e do imediatamente anterior.</td></tr> //-->
				<tr><td class='ftRodape'>Obs: As horas negativas constantes dos c&oacute;digos " . implode(',', $obj->CodigosAgrupadosParaDesconto($sitcad)) . " s&atilde;o totalizadas no codigo " . $obj->CodigoDebitoPadrao($sitcad) . " para fins de compensa&ccedil;&atilde;o.</td></tr>
				</table>";
            break;
    }

    $html_form[$contador] .= '
		</td>
		</tr>
		</table>
		<div id=\'dialog-saldos\' title=\'Extrato Frequência (' . date('d/m/Y') . ')\' style=\'display: none; margin: 3px;\'></div>';

    if ($i === $sMes_Inicio)
    {
        $_SESSION['sIMPFormFrequencia'] = $html_form[$contador];
    }
    $idInner .= $html_form[$contador];

    if (($comp_admissao > $comp_invertida && $i < $sMes_Final) || ($comp_admissao > $comp_invertida))
    {
        break;
    }
}

$title = _SISTEMA_SIGLA_ . ' | Justificativa para ocorr&ecirc;ncia';

$css = array();

$javascript   = array();
$javascript[] = 'veponto_formulario.js';

include("html/html-base.php");
include("html/header.php");

?>
<style>
    .form-control-input {
        /* display: block; */
        width: 90px;
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;

        background-image: none;
        border: 1px solid #ccc;
        border-radius: 4px;
    </style>
<div class="container">
    <div class="row" style="padding-top:50px;">
        <div class="corpo">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong><?= tratarHTML($titulo_do_formulario); ?></strong></h6>
            </div>
            <div class="col-md-12 margin-bottom-25"></div>

            <?php print $html_form[$contador]; ?>

        </div>
    </div>
</div>
<?php
include("html/footer.php");

DataBase::fechaConexao();
