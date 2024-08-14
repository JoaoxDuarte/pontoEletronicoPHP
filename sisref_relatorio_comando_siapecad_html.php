<?php
// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao('sRH ou sTabServidor');

// parametros passados
$comp = tratarHTML($_REQUEST['mes']);
$year = tratarHTML($_REQUEST['ano']);

//definindo a competencia de homologacao
$oData = new trata_datasys;
$year  = (empty($year) ? $oData->getAnoHomologacao() : $year);
$comp  = (empty($comp) ? $oData->getMesHomologacao() : $comp);

//definindo competência para cobrança de atrasos
$ano = (empty($year) ? $oData->getAnoCompensado() : ($comp == '01' ? ($year - 1) : $year));
$mes = (empty($comp) ? $oData->getMesCompensado() : ($comp == '01' ? "12" : substr("00" . ($comp - 1), -2)));

// Testa se a competencia encontra-se entre o mês de Out/2009 (inclusive) e o mês atual (inclusive)
$anocomp = $year . $comp;
if ($anocomp < '200910' || $anocomp > date('Ym'))
{
    mensagem("Não é possível emitir relatório para competência anterior a 10/2009 ou posterior ao mes atual!", pagina_de_origem(), 1);
}

// define lotacoes vinculadas a upag
$sLotacao = $_SESSION['sLotacao'];

// descricao da lotacao do servidor
$oSetor    = seleciona_dados_da_unidade($sLotacao);
$wnomelota = $oSetor->descricao;
$upag      = $oSetor->upag;


// grava dados em sessao para uso na impressao
$_SESSION['sIMPPaginaOrigem']     = pagina_de_origem();
$_SESSION['sIMPYear']             = $year;
$_SESSION['sIMPComp']             = $comp;
$_SESSION['sIMPCaminho']          = utf8_encode('Relatórios > Frequência > Para comando > Comando siapecad');
$_SESSION['sIMPUpag']             = $upag;
$_SESSION['sIMPLotacao']          = $sLotacao;
$_SESSION['sIMPLotacaoDescricao'] = $wnomelota;
$_SESSION['sIMPTituloFormulario'] = "Relatório de Servidores com Ocorrência para Comando Siapecad";

$_SESSION['saDadosEncontradosI'] = array();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Relatórios » Frequência » Para comando » Comando siapecad');

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
$oForm->setJS("sisref_relatorio_comando_siapecad_html.js");
$oForm->setSeparador(0);
$oForm->setLargura(920);

$oForm->setIconeParaImpressao("sisref_relatorio_comando_siapecad_imp.php");

$oForm->setSubTitulo("Relat&oacute;rio de Servidores com Ocorr&ecirc;ncia para Comando Siapecad");
$oForm->setObservacaoTopo("Emitido em: " . date("d/m/Y"));
$oForm->setObservacaoBase("<center><font style='font-size: 9;'>Obs: Os dados somente s&atilde;o exibidos nesse relat&oacute;rio ap&oacute;s fechado o mes para atualiza&ccedil;&atilde;o.</font></center>");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// contadores
$sequencia             = 0;
$registros_processados = 0;

## lista de servidores
#
$oDBase               = seleciona_servidores_para_siapecad();
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

        htmlDadosServidorFrequencia($dados->siape);

        $registros_processados++;
    }

    ## rodape do html
    #
    htmlRodape();
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



function htmlCabecalho()
{
    global $comp, $year, $sLotacao, $wnomelota;
    ?>
    <p align="center"><h3>
        <div align="center">
            <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="920px" id="AutoNumber1">
                <tr>
                    <td width="100%"  align='center' class="tahomaSize_2">
                        M&ecirc;s <input name="mes" type="text" class='alinhadoAoCentro' id="mes"  value='<?= tratarHTML($comp); ?>' size="7" readonly>
                        Ano <input name="ano" type="text" class='alinhadoAoCentro' id="ano" value='<?= tratarHTML($year); ?>' size="7" readonly>
                        Para emitir outro mes clique na figura: <a href="javascript:window.history.go(-1);" ><img border= "0" src="<?= _DIR_IMAGEM_; ?>copiar.gif" align="absmiddle"></a>
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
                        <td width='6%'  align='center'><b>Seq.</b></td>
                        <th width="8%"  align='left' nowrap>&nbsp;<b>Matr&iacute;cula</b>&nbsp;</th>
                        <th width="42%" align='left' nowrap>&nbsp;<b>NOME&nbsp;<b></th>
                        <td width="12%" align="left" nowrap>&nbsp;<b>Código Siapenet&nbsp;<b></td>
                        <td width="9%" align='left' nowrap>&nbsp;<b>Data Inicial&nbsp;<b></td>
                        <td width="9%" align='left' nowrap>&nbsp;<b>Data Final&nbsp;<b></td>
                        <td width="5%" align='left' nowrap>&nbsp;<b>Dias&nbsp;<b></td>
                    </tr>
                </thead>
                <tbody>
    <?php
}

function htmlDadosServidorFrequencia($siape)
{
    global $sequencia, $year, $comp;

    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;


    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $excluirDosSemRemuneracao = $obj->ExcluirDosSemRemuneracao($sitcad, $exige_horarios=true);

    // codigos a pesquisar
    $codigos_a_excluir = implode(',', $excluirDosSemRemuneracao);

    // atualiza a tabela com dados
    // siapecad referentes ao servidor
    ////atualiza_frqANO( $oServidor->mat_siape, $comp, $year, '', false );
    atualiza_frqANO($siape, $comp, $year, '', false, true);

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query("
    SELECT
        frq.mat_siape, frq.dia_ini, frq.dia_fim, frq.cod_ocorr, frq.dias, frq.horas, frq.minutos, cad.nome_serv, cad.cod_lot, oco.siapecad, oco.cod_siape, oco.idsiapecad
    FROM
        frq" . $year . " AS frq
    LEFT JOIN
        servativ AS cad ON frq.mat_siape = cad.mat_siape
    LEFT JOIN
        tabocfre AS oco ON frq.cod_ocorr = oco.siapecad
    WHERE
        frq.mat_siape = :siape
        AND frq.compet = :comp
        AND oco.idsiapecad = 'S'
        AND frq.cod_ocorr NOT IN (" . $codigos_a_excluir . ")
    ORDER BY
        frq.mat_siape, frq.dia_ini
    ",
    array(
        array(':siape', $siape, PDO::PARAM_STR),
        array(':comp', $year.$comp, PDO::PARAM_STR)
    ));

    if ($num > 0)
    {
        while ($pm = $oDBase->fetch_object())
        {
            $idsipc = $pm->idsiapecad;
            if ($idsipc == "S")
            {
                $sequencia++;
                $nome = htmlspecialchars(mb_convert_encoding(ajustar_acentos(retira_acentos($pm->nome_serv)), "UTF-8", "ISO-8859-1"));
                ?>
                <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                    <td align='center'><?= tratarHTML($sequencia); ?></td>
                    <td align='center'><?= tratarHTML($pm->mat_siape); ?></td>
                    <td nowrap><?= tratarHTML($nome); ?></td>
                    <td align='center'><?= tratarHTML($pm->siapecad); ?></td>
                    <td align='center'><?= tratarHTML($pm->dia_ini) . "/" . tratarHTML($comp); ?></td>
                    <td align='center'><?= tratarHTML($pm->dia_fim) . "/" . tratarHTML($comp); ?></td>
                    <td align='center'><?= tratarHTML($pm->dias); ?></td>
                </tr>
                <?php
                array_push($_SESSION['saDadosEncontradosI'], array($pm->mat_siape, $nome, $pm->siapecad, $pm->cod_siape, "$pm->dia_ini/$comp", "$pm->dia_fim/$comp", $pm->dias));
            }
        }
    }
}

function htmlRodape()
{
    ?>
                </tbody>
            </table>
        </div>
    </p>
    <?php
}

function seleciona_servidores_para_siapecad()
{
    global $comp, $year, $upag;

    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;


    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $excluirDosSemRemuneracao = $obj->ExcluirDosSemRemuneracao($sitcad, $exige_horarios=true);

    // codigos a pesquisar
    $codigos_a_excluir = implode(',', $excluirDosSemRemuneracao); //"'00000','00172','55555','62010','62012','62014','99999'";

    // seleção dos servidores
    $sqlPonto = "
        SELECT
            pto.siape, cad.nome_serv AS nome
        FROM
            ponto" . $comp . $year . " AS pto
        LEFT JOIN
            servativ AS cad ON pto.siape = cad.mat_siape
        LEFT JOIN
            tabsetor AS und ON cad.cod_lot = und.codigo
        LEFT JOIN
            tabocfre AS oco ON pto.oco = oco.siapecad
        WHERE
            und.upag = :upag
            AND pto.oco NOT IN (" . $codigos_a_excluir . ")
            AND oco.idsiapecad = 'S'
        GROUP BY
            pto.siape
        ORDER BY
            cad.nome_serv
    ";
    
    $params = array(
        array(':upag', $upag, PDO::PARAM_STR)
    );

    // instancia banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query($sqlPonto, $params);

    return $oDBase;
}
