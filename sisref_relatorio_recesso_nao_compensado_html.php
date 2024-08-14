<?php
// funcoes de uso geral
include_once( "config.php" );
include_once( _DIR_INC_ . "PogProgressBar.php" );
include_once( _DIR_INC_ . "calcula_horas_do_recesso.php" );
include_once( "class_ocorrencias_grupos.php" );

set_time_limit(0);

// Verifica se o usuário tem a permissão para acessar este módulo
// Inicializa a sessão (session_start)
verifica_permissao("sRH");

// definindo competencias
$periodo = $_REQUEST['periodo'];

if (empty($periodo))
{
    mensagem("É obrigatória a Seleção de um período!", null, 1);
}
else
{
    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoDebitoRecessoPadrao = $obj->CodigoDebitoRecessoPadrao($sitcad);


    // tabela recesso
    $oDBase0001 = new DataBase('PDO');
    $oDBase0001->query("
    SELECT
	    trec.periodo,
        DATE_FORMAT(trec.recesso_inicio,'%m%Y') AS recesso_inicio,
        DATE_FORMAT(trec.recesso_fim,'%m%Y')    AS recesso_fim,
        trec.recesso_inicio_compensacao,
        trec.recesso_fim_compensacao
    FROM
	    tabrecesso_fimdeano AS trec
    WHERE
	    trec.periodo = :periodo ",
    array(
        array(':periodo', str_replace(" / ", "/", $periodo), PDO::PARAM_STR)
    ));
    $oRecesso                   = $oDBase0001->fetch_object();
    $compet_recesso_inicio      = $oRecesso->recesso_inicio;
    $compet_recesso_fim         = $oRecesso->recesso_fim;
    $recesso_inicio_compensacao = $oRecesso->recesso_inicio_compensacao;
    $recesso_fim_compensacao    = $oRecesso->recesso_fim_compensacao;


    ## classe para montagem do formulario padrao
    #
	$oForm = new formPadrao;
    $oForm->setCaminho($_SESSION['sIMPCaminho']);

    // mensagem processando
    $oForm->setJS(_DIR_JS_ . "jquery.js");
    $oForm->setJS(_DIR_JS_ . "jquery.blockUI.js?v2.38");

    // tela alert
    $oForm->setCSS(_DIR_JS_ . 'plugins/dlg.css');
    $oForm->setJS(_DIR_JS_ . "plugins/jquery.dlg.min.js");
    $oForm->setJS(_DIR_JS_ . "plugins/jquery.easing.js");
    $oForm->setJS(_DIR_JS_ . "jquery.ui.min.js");

    // ordena tabela
    $oForm->setCSS(_DIR_JS_ . "plugins/tablesorter/css/theme.blue.css");
    $oForm->setJS(_DIR_JS_ . "plugins/tablesorter/jquery.tablesorter.min.js");
    $oForm->setJS(_DIR_JS_ . "plugins/tablesorter/jquery.tablesorter.widgets.min.js");

    $oForm->setJS("sisref_relatorio_recesso_nao_compensado_html.js");

    $oForm->setSeparador(0);

    //$oForm->setIconeParaImpressao("sisref_relatorio_recesso_nao_compensado_imp.php");

    $oForm->setSubTitulo($_SESSION['sIMPTituloFormulario1']);

    $oForm->setObservacaoTopo("Emitido em: " . date("d/m/Y"));

    // Topo do formulário
    //
    $oForm->exibeTopoHTML();
    $oForm->exibeCorpoTopoHTML();
    // Testa se 'ano' encontra-se entre 2010 e o ano atual (inclusive)
    if (dataAno($recesso_fim_compensacao) < '2009' || dataAno($recesso_fim_compensacao) > date('Y'))
    {
        mensagem("Não é possível emitir relatório de cobrança para ano anterior a 2009 ou posterior ao atual!", null, 1);
    }

    // define lotacoes vinculadas a upag
    $sLotacao = $_SESSION['sLotacao'];

    // descricao da lotacao do servidor
    $oSetor    = seleciona_dados_da_unidade($sLotacao);
    $wnomelota = $oSetor->descricao;
    $upag      = $oSetor->upag;


    // grava dados em sessao para uso na impressao
    $_SESSION['saDadosEncontradosI']   = array();
    $_SESSION['sIMPPeriodo']           = $periodo;
    $_SESSION['sIMPComp']              = dataMes($recesso_fim_compensacao) . '/' . dataAno($recesso_fim_compensacao);
    $_SESSION['sIMPCaminho']           = 'Relatório » Frequência » Para comando » Recesso';
    $_SESSION['sIMPUpag']              = $upag;
    $_SESSION['sIMPLotacao']           = $_SESSION['sLotacao'];
    $_SESSION['sIMPLotacaoDescricao']  = $wnomelota;
    $_SESSION['sIMPTituloFormulario1'] = "Relatório de Servidores que aparecem sem Compensação total/parcial do Recesso de Fim de Ano";
    $_SESSION['sIMPTituloFormulario2'] = "Servidores que aparecem com c&oacute;digo " . implode(',', $codigoDebitoRecessoPadrao) . " sem compensação total/parcial at&eacute; " . (date('Y-m-d') > $recesso_fim_compensacao ? databarra($recesso_fim_compensacao) : date('d/m/Y')) . ".<br> »» Período para compensação do recesso: De " . databarra($recesso_inicio_compensacao) . " a " . databarra($recesso_fim_compensacao) . "««";

    $_SESSION['sIMPBaseFormulario1'] = "";

    // contadores
    $sequencia             = 0;
    $registros_processados = 0;

    ## lista de servidores
    #
    $oDBase001               = seleciona_servidores_todos_os_servidores();
    $numero_de_servidores = $oDBase001->num_rows();

    if ($numero_de_servidores == 0)
    {
        mensagem("Não foram encontrados registros para esta UPAG", null, 1);
    }
    else
    {
        ## cabecalho do html
        #
	    htmlCabecalho();

        ## códigos siapecad registrado para servidores
        #
	    while ($dados = $oDBase001->fetch_object())
        {
            htmlDadosServidorFrequencia($dados->siape, $dados->nome);

            $registros_processados++;
            $msg_processando = 'Processando ' . $dados->siape . '- ' . substr($dados->nome, 0, 20);
        }

        ## rodape do html
        #
        htmlRodape();
    }

    // Base do formulário
    //
    $oForm->exibeCorpoBaseHTML();
    $oForm->exibeBaseHTML();
}



function htmlCabecalho()
{
    global $periodo, $sLotacao, $wnomelota;
    ?>
    <p align="center"><h3>
        <div align="center">
            <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="920px" id="AutoNumber1">
                <tr>
                    <td width="100%"  align='center' class="tahomaSize_2">
                        Período:&nbsp;<input name="periodo" type="text" class='alinhadoAoCentro' id="periodo" value='<?= tratarHTML($periodo); ?>' size="15" readonly>
                    </td>
                </tr>
                <tr>
                    <td  align='center' class="tahomaSize_2">
                        Lota&ccedil;&atilde;o <input name="lot" type="text" class='alinhadoAoCentro' id="lot" value='<?= tratarHTML(getUorgMaisDescricao( $sLotacao )); ?>' size="60" maxlength="60" readonly>
                    </td>
                </tr>
            </table>
            <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="920px" id="AutoNumber2" class="tablesorter">
                <thead>
                    <tr bgcolor="#DBDBB7">
                        <td colspan="5" align='center'><?= tratarHTML($_SESSION['sIMPTituloFormulario2']); ?></td>
                    </tr>
                    <tr bgcolor="#DBDBB7">
                        <td width='6%'  align='center'><b>SEQ.</b></td>
                        <th width="8%"  align='center'><b>Matr&iacute;cula</b></th>
                        <th width="44%" align='left'><b>&nbsp; NOME</b></th>
                        <!-- <th width="15%"><div align="center"><strong>C&oacute;digo Siapecad</strong></div></th> //-->
                        <td width="15%" align="center"><strong>C&oacute;digo</strong></td>
                        <td width="16%" align='center'><b>Horas</b></td>
                    </tr>
                </thead>
                <tbody>
    <?php
}

function htmlDadosServidorFrequencia($siape = '', $nome = '')
{
    global $sequencia, $periodo;

    $dadosRecesso = calculaHorasDoRecesso($siape, substr($periodo, 7, 4));
    $cod          = $dadosRecesso[0];

    if (!empty($cod))
    {
        $sequencia++;
        $total2 = str_replace('-', '', strip_tags($dadosRecesso[1]));
        $nome   = trata_aspas($nome);

        ?>
        <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
            <td align='center'><?= tratarHTML($sequencia); ?></td>
            <td align='center'><?= tratarHTML($siape); ?></td>
            <td nowrap><?= tratarHTML($nome); ?></td>
            <td align='center'><?= tratarHTML($cod); ?></td>
            <td align='center'><?= tratarHTML($total2); ?></td>
        </tr>
        <?php

        array_push($_SESSION['saDadosEncontradosI'], array($siape, $nome, $cod, $total2));
    }
}

function htmlRodape()
{
    ?>
                </tbody>
            </table>
        </div>
    </p>
    <p><font size="1"><?= $_SESSION['sIMPBaseFormulario1']; ?></font></p>
    <?php
}

function seleciona_servidores_todos_os_servidores()
{
    global $upag, $periodo, $compet_recesso_inicio, $compet_recesso_fim, $codigoDebitoRecessoPadrao;

    // cria a tabela se não existir
    CreateTablePonto("ponto" . $compet_recesso_inicio);
    CreateTablePonto("ponto" . $compet_recesso_fim);

    // seleção dos servidores
    $sqlPonto = "
		SELECT
			cad.mat_siape AS siape, cad.nome_serv AS nome, DATE_FORMAT(cad.dt_adm,'%Y%m%d') AS dt_admissao
		FROM
			ponto" . $compet_recesso_inicio . " AS pto
		LEFT JOIN
			servativ AS cad ON pto.siape = cad.mat_siape
		LEFT JOIN
			tabsetor AS und ON cad.cod_lot = und.codigo
		WHERE
			und.upag = '" . $upag . "'
			AND cad.excluido = 'N'
			AND pto.oco IN (" . implode(',', $codigoDebitoRecessoPadrao) . ")
	";
    if ($compet_recesso_inicio != "" && $compet_recesso_fim != "" && $compet_recesso_inicio != $compet_recesso_fim)
    {
        $sqlPonto .= "
			UNION
			SELECT
				cad.mat_siape AS siape, cad.nome_serv AS nome, DATE_FORMAT(cad.dt_adm,'%Y%m%d') AS dt_admissao
			FROM
				ponto" . $compet_recesso_fim . " AS pto
			LEFT JOIN
				servativ AS cad ON pto.siape = cad.mat_siape
			LEFT JOIN
				tabsetor AS und ON cad.cod_lot = und.codigo
			WHERE
				und.upag = '" . $upag . "'
				AND cad.excluido = 'N'
				AND pto.oco IN (" . implode(',', $codigoDebitoRecessoPadrao) . ")
		";
    }
    $sqlPonto .= "
		GROUP BY siape
		ORDER BY nome
	";

    // instancia banco de dados
    $oDBase002 = new DataBase('PDO');
    $oDBase002->query($sqlPonto);

    return $oDBase002;
}
