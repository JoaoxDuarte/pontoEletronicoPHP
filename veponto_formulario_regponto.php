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

// parametros
if (isset($_REQUEST['mes']) && isset($_REQUEST['ano']))
{
    $mes = anti_injection($_REQUEST['mes']);
    $ano = anti_injection($_REQUEST['ano']);
}
else
{
    $mes = anti_injection($_REQUEST['mes3']);
    $ano = anti_injection($_REQUEST['ano3']);
}

$pSiape        = anti_injection($_REQUEST['pSiape']);
$cmd           = anti_injection($_REQUEST['cmd']);
$paginaDestino = $pagina_de_origem . '?pSiape=' . $pSiape . '&mes3=' . $mes . '&ano3=' . $ano . '&cmd=' . $cmd;

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
$sAutorizadoTE   = $oFreq->getTurnoEstendido(); // informa se o servidor encontra-se em unidade
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
    $jornada = 40;
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


if ($oFreq->getExcluido() == 'N')
{
    $dia_limite_para_inserir = ($ano == date('Y') && $mes == date('m') ? date('d') : '');
    inserir_dias_sem_frequencia($pSiape, $dia_limite_para_inserir, $mes, $ano, $jornada, $lot, '', $oFreq->getDataIngressoNoOrgao(), $oFreq->getAnoMesExclusao());
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
    $oMensagem->exibeMensagem(24);
}
elseif ($_SESSION['sRH'] == 'S' && $upg != $_SESSION['upag'])
{
    $oMensagem->exibeMensagem(25);
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
    $oDBase->setMensagem("Erro no acesso a tabela Histórico de Lotação/Setor (1)");
    $oDBase->query("
    SELECT
        a.cod_lot, b.descricao, b.cod_uorg, b.uorg_pai, b.upag, a.dt_ing_lot,
        a.dt_sai_lot, c.denominacao, c.sigla
    FROM
        histlot AS a
    LEFT JOIN
        tabsetor AS b ON a.cod_lot = b.codigo
    LEFT JOIN
        taborgao AS c ON LEFT(b.codigo,5) = c.codigo
    WHERE
        a.siape= :siape
        AND DATE_FORMAT(a.dt_ing_lot,'%Y%m') <= :comp
        AND DATE_FORMAT(a.dt_sai_lot,'%Y%m') = '000000'
    ORDER BY
        a.dt_ing_lot DESC
    ", array(
        array(":siape", $pSiape, PDO::PARAM_STR),
        array(":comp", $comp_invertida, PDO::PARAM_STR)
    ));

    if ($oDBase->num_rows() > 0)
    {
        $oUORG = $oDBase->fetch_object();
    }
    else
    {
        $ultimo_dia_do_mes = $ano . '-' . $mes . '-' . numero_dias_do_mes($mes, $ano);

        $oDBase->setMensagem("Erro no acesso a tabela Histórico de Jornada (2)");
        $oDBase->query("
        SELECT
            jh.cod_lot, b.descricao, b.cod_uorg, b.uorg_pai, b.upag,
            dt_ing_lot, dt_sai_lot, c.denominacao, c.sigla
	FROM
            jornada_historico AS jh
        LEFT JOIN
            histlot AS a ON a.cod_lot = jh.cod_lot
        LEFT JOIN
            tabsetor AS b ON b.codigo = jh.cod_lot
        LEFT JOIN
            taborgao AS c ON LEFT(b.codigo,5) = c.codigo
        WHERE
            jh.siape = :siape AND :dia >= jh.data_inicio
        ORDER BY
            jh.data_inicio DESC, jh.data_registro DESC
        LIMIT
            1
        ", array(
            array(':siape', $pSiape, PDO::PARAM_STR),
            array(":dia", $ultimo_dia_do_mes, PDO::PARAM_STR)
        ));

        if ($oDBase->num_rows() > 0)
        {
            $oUORG = $oDBase->fetch_object();
        }
    }

    $lot           = $oUORG->cod_lot;
    $lot_descricao = $oUORG->descricao;
    $orgao_sigla   = $oUORG->sigla;

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
    $oDBase->query('
    SELECT
        a.entra, DATE_FORMAT(a.dia, "%d/%m/%Y") AS dia, a.intini, a.intsai,
        a.sai, a.jornd, a.jornp, a.jorndif, a.oco,
	REPLACE(a.just,";",":") AS just, a.idreg, b.desc_ocorr AS dcod,
        d.codmun, d.codigo, a.idreg, a.ip, a.matchef, a.siaperh,
	REPLACE(a.justchef,";",":") AS justchef
    FROM
        ponto' . $comp . ' AS a
    LEFT JOIN tabocfre AS b ON a.oco = b.siapecad
    LEFT JOIN servativ AS c ON a.siape = c.mat_siape
    LEFT JOIN tabsetor AS d ON c.cod_lot = d.codigo
    WHERE
        a.siape = :siape
        AND dia <> "0000-00-00"
    ORDER BY
        a.dia
    ', array(
        array(":siape", $pSiape, PDO::PARAM_STR)
    ));

    $html_form[$contador] = '<table align=\'center\' ' . ($quebra == 'sim' ? 'style=\'page-break-before: always\'' : '') . '><tr><td>';


    ## classe para montagem do formulario padrao
    #
    $oForm = new formPadrao();
    $oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
    $oForm->setDialogModal();
    $oForm->setJS('veponto_formulario.js');
    $oForm->setCaminho($caminho_modulo_utilizado);
    $oForm->setMes($mes);
    $oForm->setMesInicial($mes_inicial);
    $oForm->setSeparador(0);
    $oForm->setLargura('850px');

    $oForm->setSubTitulo($titulo_do_formulario);

    // grava o LOG
    $vHoras = strftime("%H:%M:%S", time());
    $vDatas = date("Y/m/d");
    $hoje   = date("d/m/Y");

    $registrosComparecimentoOcorrencia = array();

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
        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $codigosCompensaveis              = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);
        $codigosDebito                    = $obj->CodigosDebito($sitcad);
        $codigosCredito                   = $obj->CodigosCredito($sitcad, $temp=true);
        $codigosTrocaObrigatoria          = $obj->CodigosTrocaObrigatoria($sitcad);

        $grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);

        $codigoSemFrequenciaPadrao        = $obj->CodigoSemFrequenciaPadrao($sitcad);
        $codigoRegistroParcialPadrao      = $obj->CodigoRegistroParcialPadrao($sitcad);


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

            $sDados         = $pSiape . ":|:" . utf8_iso88591(str_replace('"', '', $nome)) . ":|:" . $sLot . ":|:" . $comp . ":|:" . $pm_partners->dia . ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->just)) . ":|:" . $pm_partners->oco . ":|:" . $cmd;
            $matual         = date("mY");
            $mes_homologado = verifica_se_mes_homologado($pSiape, substr($comp, 2, 4) . substr($comp, 0, 2));

            // justificativas só podem ser registradas ou alteradas antes da homologação
            if (($mes_homologado == 'HOMOLOGADO') || (isset($_SESSION['registrar_justificativa']) && $_SESSION['registrar_justificativa'] == false))
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

            $registrado_por = define_quem_registrou_descricao($pm_partners, $situacao_cadastral, $comp_invertida);

            if ($pm_partners->justchef != "")
            {
                $dados          = base64_encode($pSiape . ":|:" . $nome . ":|:" . $pm_partners->dia . ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->justchef)) . ":|:" . $pm_partners->oco . ":|:" . $cmd . ":|:sim");
                $registrado_por = "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href= 'regjustab.php?dados=" . $dados . "'>" . $registrado_por . "</a>";
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

            $registrosComparecimentoOcorrencia[] = array(
                'background'       => $background,
                'color'            => $color,
                'dia-title'        => $dia_nao_util[$xdia][4],
                'dia-value'        => trim($dia_nao_util[$xdia][2]) . '&nbsp;' . trim($xdia . $dia_nao_util[$xdia][3]),
                'pm_partners'      => $pm_partners,
                'saldo'            => $sinal . ' ' . $font_i_color . $pm_partners->jorndif . $font_f_color,
                'ocorrencia-title' => $pm_partners->dcod . "\n" . $pm_partners->just,
                'ocorrencia-value' => $codigo_de_ocorrencia
            );
        } // fim do while
    }


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

    $mesFim         = $mes;
    $anoFim         = $ano;
    $veponto_saldos = imprimirSaldoCompensacaoDoMes();


    if ($i === $sMes_Inicio)
    {
        $_SESSION['sIMPFormFrequencia'] = $html_form[$contador];
    }
    $idInner .= $html_form[$contador];

    if (($comp_admissao > $comp_invertida && $i < $sMes_Final) || ($comp_admissao > $comp_invertida))
    {
        break;
    }

    $contador++;
}

$title = _SISTEMA_SIGLA_ . ' | Registro de Comparecimento';

$css = array();

$javascript = array();
//$javascript[] = 'principal.js';

include("html/html-base.php");
include("html/header.php");

require("html/form-entrada-6.php");

include("html/footer.php");

DataBase::fechaConexao();
