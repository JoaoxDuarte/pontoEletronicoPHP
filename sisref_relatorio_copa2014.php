<?php
// funcoes de uso geral
include_once( "config.php" );
include_once( _DIR_INC_ . "PogProgressBar.php" );
include_once( _DIR_INC_ . "calcula_horas_da_copa2014.php" );

set_time_limit(0);

// Verifica se o usuário tem a permissão para acessar este módulo
// Inicializa a sessão (session_start)
verifica_permissao("sRH");

// instancia bando de dados
$oDBase = new DataBase('PDO');

// define lotacoes vinculadas a upag
$sLotacao = $_SESSION['sLotacao'];

// descricao da lotacao do servidor
$oSetor    = seleciona_dados_da_unidade($sLotacao);
$wnomelota = $oSetor->descricao;
$upag      = $oSetor->upag;

$periodo = '01/06/2014 a 30/09/2014';

// Mensagem SEGEP/MP n° 555290/2014, que trata da prorrogação do prazo
// previsto no comunica 554955 - Compensação das horas não trabalhadas
// em decorrência dos jogos da Copa.
$periodo = '01/06/2014 a 31/10/2014';

// grava dados em sessao para uso na impressao
$_SESSION['saDadosEncontradosI']   = array();
$_SESSION['sIMPPeriodo']           = $periodo;
$_SESSION['sIMPComp']              = '10/2014';
$_SESSION['sIMPCaminho']           = 'Frequência » Visualizar » Consulta Compensações - Todos<br>(Copa do Mundo 2014)';
$_SESSION['sIMPUpag']              = $upag;
$_SESSION['sIMPLotacao']           = $_SESSION['sLotacao'];
$_SESSION['sIMPLotacaoDescricao']  = $wnomelota;
$_SESSION['sIMPTituloFormulario1'] = "Copa do Mundo 2014 - Período: " . $periodo . "";
$_SESSION['sIMPTituloFormulario2'] = "";

$_SESSION['sIMPBaseFormulario1'] = "";

//Barra de progresso
$objBar = new PogProgressBar('pb0');
$objBar->setTheme('blue');
$objBar->draw('', '1px', '30%');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho($_SESSION['sIMPCaminho']);
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setCSS(_DIR_CSS_ . 'plugins/dlg.css');
$oForm->setCSS(_DIR_JS_ . "plugins/tablesorter/css/theme.blue.css");
$oForm->setJS(_DIR_JS_ . "jquery.js");
$oForm->setJS(_DIR_JS_ . "jquery.blockUI.js?v2.38");
$oForm->setJS(_DIR_JS_ . "plugins/jquery.dlg.min.js");
$oForm->setJS(_DIR_JS_ . "plugins/jquery.easing.js");
$oForm->setJS(_DIR_JS_ . "jquery.ui.min.js");
$oForm->setJS(_DIR_JS_ . "plugins/tablesorter/jquery.tablesorter.min.js");
$oForm->setJS(_DIR_JS_ . "plugins/tablesorter/jquery.tablesorter.widgets.min.js");
$oForm->setJS(_DIR_JS_ . 'funcoes.js');

$oForm->setSeparador(0);

$oForm->setIconeParaImpressao("sisref_relatorio_copa2014_imp.php");

$oForm->setSubTitulo($_SESSION['sIMPTituloFormulario1']);

$oForm->setObservacaoTopo("Emitido em: " . date("d/m/Y"));

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// contadores
$sequencia             = 0;
$registros_processados = 0;

## lista de servidores
#
$oDBase               = seleciona_servidores_todos_os_servidores();
$numero_de_servidores = $oDBase->num_rows();

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
		while ($dados = $oDBase->fetch_object())
    {
        htmlDadosServidorFrequencia($dados->siape, $dados->nome);

        $registros_processados++;
        $msg_processando = 'Processando ' . $dados->siape . '- ' . substr($dados->nome, 0, 20);
        $objBar->setProgress(round($registros_processados * 100 / $numero_de_servidores), $registros_processados, $numero_de_servidores, 'top', $msg_processando);
        usleep(40);
    }

    ## rodape do html
    #
		htmlRodape();
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

$objBar->hide();
?>

<?php

function htmlCabecalho()
{
    ?>
    <p align="center"><h3>
        <div align="center">
            <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="920px" id="AutoNumber1">
                <tr>
                    <td width="100%"  align='center' class="tahomaSize_2">
                        Período:&nbsp;<input name="periodo" type="text" class='alinhadoAoCentro' id="periodo" value='<?= tratarHTML($_SESSION['sIMPPeriodo']); ?>' size="25" readonly>
                    </td>
                </tr>
                <tr>
                    <td  align='center' class="tahomaSize_2">
                        Lota&ccedil;&atilde;o <input name="lot" type="text" class='alinhadoAoCentro' id="lot" value='<?= tratarHTML( getUorgMaisDescricao( $_SESSION['sIMPLotacao']) ); ?>' size="60" maxlength="60" readonly>
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
                        <td width="15%" align="center"><strong>Horas devidas<br>(62014)</strong></td>
                        <td width="15%" align="center"><strong>Horas excedentes<br>(92014)</strong></td>
                        <td width="16%" align='center'><b>Saldos</b></td>
                    </tr>
                </thead>
                <tbody>
                    <?php

                }

                function htmlDadosServidorFrequencia($siape = '', $nome = '')
                {
                    global $sequencia, $periodo;

                    /* -----------------------------------------------------------------------*\
                      |                                                                         |
                      |     CALCULO DE HORAS DEVIDAS E NAO COMPENSADAS - COPA DO MUNDO 2014     |
                      |                                                                         |
                      \*----------------------------------------------------------------------- */
                    $copa2014 = new CopaDoMundo2014();
                    $copa2014->setSiape($siape);
                    $copa2014->saldosCopaMundo2014(false);
                    /* -----------------------------------------------------------------------*\
                      |  FIM DO CALCULO DE HORAS DEVIDAS E NAO COMPENSADAS - COPA DO MUNDO 2014 |
                      \*----------------------------------------------------------------------- */

                    $dadosCopa2014 = $copa2014->getValores();
                    //$total2 = str_replace('-','',strip_tags($dadosCopa2014[2][6]));
                    $cod62014      = $dadosCopa2014[0][6];
                    $cod92014      = $dadosCopa2014[1][6];
                    $total2        = str_replace('font-size:17px', 'font-size:12px', $dadosCopa2014[2][6]);
                    $nome          = trata_aspas($nome);

                    $sequencia++;
                    ?>
                    <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                        <td align='center'><?= tratarHTML($sequencia); ?></td>
                        <td align='center'><?= tratarHTML($siape); ?></td>
                        <td nowrap><?= tratarHTML($nome); ?></td>
                        <td align='center'><?= tratarHTML($cod62014); ?></td>
                        <td align='center'><?= tratarHTML($cod92014); ?></td>
                        <td align='center'><?= tratarHTML($total2); ?></td>
                    </tr>
                    <?php
                    array_push($_SESSION['saDadosEncontradosI'], array($siape, $nome, $cod62014, $cod92014, $total2));

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
    global $upag;

    // seleção dos servidores JUNHO a OUTUBRO / 2014 *****SETEMBRO / 2014*****
    // com código 62014 ou 92014, horas devidas ref. Copa do Mundo 2014
    $sqlPonto = "";
    for ($x = 6; $x <= 10; $x++)
    {
        $sqlPonto .= "
		SELECT
			cad.mat_siape AS siape, cad.nome_serv AS nome, DATE_FORMAT(cad.dt_adm,'%Y%m%d') AS dt_admissao
		FROM
			ponto" . str_pad($x, 2, "0", STR_PAD_LEFT) . "2014 AS pto
		LEFT JOIN
			servativ AS cad ON pto.siape = cad.mat_siape
		LEFT JOIN
			tabsetor AS und ON cad.cod_lot = und.codigo
		WHERE
			und.upag = '" . $upag . "'
			AND cad.excluido = 'N'
			AND (pto.oco = '62014' OR pto.oco = '92014')
		";

        if ($x != 10)
        {
            $sqlPonto .= "
			UNION
			";
        }
    }

    $sqlPonto .= "
	GROUP BY siape
	ORDER BY nome
	";

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query($sqlPonto);
    return $oDBase;

}
