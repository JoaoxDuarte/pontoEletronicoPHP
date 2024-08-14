<?php

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao('relatorio_ocorrencia');

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

// parametros passados por formulario
$compet = $_REQUEST['competencias_opcoes'];  // Ex.: '11';
$mes = substr($compet,-2);  // Ex.: '11';
$ano = substr($compet,0,4);  // Ex.: '2011';


// UPAG Ex.: '57202000004618';
$upag = ($_SESSION['sOUTRO'] == "S" ? anti_injection(trim($_REQUEST['upag'])) : $_SESSION['upag']);

$sMatricula = $_SESSION["sMatricula"];
$magico     = $_SESSION["magico"];

// define lotacoes vinculadas a upag
$sLotacao = $_SESSION['sLotacao'];

// dados para retorno a este script
$_SESSION['sPaginaRetorno_sucesso'] = $_SERVER['REQUEST_URI'];

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setJS("sisref_relatorio_por_ocorrencia_html.js");
$oForm->setSubTitulo($_SESSION['sIMPTituloFormulario1']);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// Testa se a competencia encontra-se entre o mês de Out/2009 (inclusive) e o mês atual (inclusive)
$anocomp = $ano . $mes;
if ($anocomp < '200910' || $anocomp > date('Ym'))
{
    mensagem("Não é possível emitir relatório para competência anterior a 10/2009 ou posterior ao mes atual!", pagina_de_origem());
}

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// descrição da lotação
$oDBase->query("SELECT codigo, descricao FROM tabsetor WHERE cod_uorg = :cod_uorg ",
        array(
            array(":cod_uorg", $upag, PDO::PARAM_STR)
        ));
$oSetor    = $oDBase->fetch_object();
$lota      = $oSetor->codigo;
$wnomelota = $oSetor->descricao;

// descrição da ocorrência
$oDBase->setMensagem("Não foi possível consultar tabela de ocorrencia ");
$ocorrencia_codigo  = array();
$codigos_ocorrencia = "";

foreach ($_REQUEST['ocor'] AS $ocor)
{
    $oDBase->query("SELECT desc_ocorr FROM tabocfre WHERE siapecad = :siapecad ",
        array(
            array(":siapecad", $ocor, PDO::PARAM_STR)
        ));
    $descricao_da_ocorrencia     = $oDBase->fetch_object()->desc_ocorr;
    $ocorrencia_codigo[$ocor][0] = 0;
    $ocorrencia_codigo[$ocor][1] = $ocor;
    $ocorrencia_codigo[$ocor][2] = $descricao_da_ocorrencia;
    $codigos_ocorrencia          .= "'" . $ocor . "',";
}

$codigos_ocorrencia = "'" . implode("','", $_REQUEST['ocor']) . "'";


// grava dados em sessao para uso na impressao
$_SESSION['sIMPPaginaOrigem']      = pagina_de_origem();
$_SESSION['sIMPYear']              = $ano;
$_SESSION['sIMPComp']              = $mes;
$_SESSION['sIMPCaminho']           = 'Relatórios » Frequência » Por ocorrência (listagem)';
$_SESSION['sIMPUpag']              = $upag;
$_SESSION['sIMPLotacao']           = $lota;
$_SESSION['sIMPLotacaoDescricao']  = $wnomelota;
$_SESSION['sIMPTituloFormulario1'] = "Relatório por Código de Ocorrência";
$_SESSION['sIMPTituloFormulario2'] = "Servidores que aparecem com a ocorrência " . $_SESSION['sIMPOcorrencia'] . ", em " . $mes . "/" . $ano . ".";

$_SESSION['sIMPBaseFormulario1'] = "Obs: O relatório contempla registros não homologados e homologados.";

$_SESSION['saDadosEncontradosI'] = array();

$observacaoTopo = "Emitido em: " . date("d/m/Y");
if (($ano . $mes) == date('Ym'))
{
    $observacaoTopo .= "<br><font style='color: red; font-size: 10px; font-weight: bold;'>OCORRÊNCIAS DE " . $mes . "/" . $ano . " (mês corrente) ESTÃO SUJEITAS A ALTERAÇÕES APÓS A <u>HOMOLOGAÇÃO</u>.</font>";
}

$oForm->setObservacaoTopo($observacaoTopo);
//$oForm->setObservacaoBase("<center><font style='font-size: 9;'>" . $_SESSION['sIMPBaseFormulario1'] . "</font></center>");

// contadores
$sequencia             = 0;
$registros_processados = 0;

## lista de servidores
#
$oDBase               = seleciona_servidores_por_ocorrencia();
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
        // $ano: ano da homologacao
        // $mes: mes da homologacao
        // atualiza a tabela com dados
        // siapecad referentes ao servidor
        ////atualiza_frqANO( $oServidor->mat_siape, $mes, $a    no, '', false );

        atualiza_frqANO($dados->siape, $mes, $ano, '', false, true, true);

        $registros_processados++;
    }

    htmlDadosServidorFrequencia();

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
    global $mes, $ano, $sLotacao, $wnomelota, $codigos_ocorrencia, $ocorrencia_codigo, $upag;

    ?>
    <div class="container" style='padding-bottom:30px;margin-left:0%;'>

        <div class="col-md-12 text-left">
            <div class="col-md-3 text-left">
                <label for="dia" class="control-label">Mês/Ano</label><br>
                <big><?= tratarHTML($mes.'/'.$ano); ?></big>
            </div>
            <div class="col-md-8 text-left">
                <label for="orgao" class="control-label">Unidade</label><br>
                <big><?= tratarHTML(getUorgMaisDescricao( $_SESSION['sIMPLotacao'] )); ?></big>
            </div>
        </div>
        
    </div>
    
        <div align="left">
            <table id="AutoNumber2" class="display nowrap table-hover table table-striped table-bordered">
                <thead>
                    <tr bgcolor='#DBDBB7' height='20px'>
                        <td width='6%'  align='center'><b>Seq.</b></td>
                        <th width="8%"  align='left' nowrap>&nbsp;<b>Matr&iacute;cula</b>&nbsp;</th>
                        <th width="42%" align='left' nowrap>&nbsp;<b>NOME&nbsp;<b></th>
                        <td width="12%" align="left" nowrap>&nbsp;<b>C&oacute;d. SiapeNet&nbsp;<b></td>
                        <td width="9%" align='left' nowrap>&nbsp;<b>Data Inicial&nbsp;<b></td>
                        <td width="9%" align='left' nowrap>&nbsp;<b>Data Final&nbsp;<b></td>
                        <td width="2%" align='left' nowrap>&nbsp;<b>Dias&nbsp;<b></td>
                    </tr>
                </thead>
                <tbody>
    <?php
}

function htmlDadosServidorFrequencia()
{
    global $sequencia, $ano, $mes, $codigos_ocorrencia, $ocorrencia_codigo, $upag;

    // selecao
    $sql = "
    SELECT
        cad.mat_siape, frq.dia_ini, frq.dia_fim, frq.cod_ocorr, frq.dias, frq.horas, frq.minutos, cad.nome_serv, cad.cod_lot, oco.siapecad, oco.cod_siape, oco.idsiapecad, oco.semrem
    FROM
        frq" . $ano . " AS frq
    LEFT JOIN
        servativ AS cad ON frq.mat_siape = cad.mat_siape
    LEFT JOIN
        tabsetor AS setor ON cad.cod_lot = setor.codigo
    LEFT JOIN
        tabocfre AS oco ON frq.cod_ocorr = oco.siapecad
    WHERE
        setor.upag = :upag
        AND frq.cod_ocorr IN (" . $codigos_ocorrencia . ")
        AND frq.compet = :compet
    ORDER BY
        cad.nome_serv, frq.mat_siape, frq.dia_ini
    ";

    $params = array(
        array( ':upag',   $upag, PDO::PARAM_STR ),
        array( ':compet', $ano . $mes, PDO::PARAM_STR ),
    );

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // dados do servidor
    $oDBase->query($sql, $params);

    $num = $oDBase->num_rows();

    if ($num > 0)
    {
        while ($pm = $oDBase->fetch_object())
        {
            $sequencia++;
            $nome = htmlspecialchars(mb_convert_encoding(ajustar_acentos(retira_acentos($pm->nome_serv)), "UTF-8", "ISO-8859-1"));

            ?>
            <tr height='18'>
                <td align='center'><?= tratarHTML($sequencia); ?></td>
                <td align='center'><?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?></td>
                <td nowrap>&nbsp;<?= tratarHTML($nome); ?></td>
                <td align='center'><?= tratarHTML($pm->siapecad); ?></td>
                <td align='center'><?= tratarHTML($pm->dia_ini) . "/" . tratarHTML($mes); ?></td>
                <td align='center'><?= tratarHTML($pm->dia_fim) . "/" . tratarHTML($mes); ?></td>
                <td align='center'><?= tratarHTML($pm->dias); ?></td>
            </tr>
            <?php
            array_push($_SESSION['saDadosEncontradosI'], array($pm->mat_siape, $nome, $pm->siapecad, $pm->cod_siape, "$pm->dia_ini/$mes", "$pm->dia_fim/$mes", $pm->dias));

            $ocorrencia_codigo[$pm->siapecad][0] += 1;
        }
    }
}

function htmlRodape()
{
    global $ocorrencia_codigo;

    ?>
            </tbody>
        </table>

        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumbe    r2" class="tablesorter">
            <tr>
                <td align='left' width='10%' style='vertical-align: top;' nowrap><b><small>&nbsp;Código(s)&nbsp;SIAPECad&nbsp;Pesquisado(s):</small></b></td>
                <td align='left' width='1%'>&nbsp;</td>
                <td align='left' width='89%'>
                    <?php
                    $codigos_ocorrencia_descricao = "<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#F1F1 E2' border='0'>";

                    foreach ($ocorrencia_codigo AS $cod)
                    {
                        $codigos_ocorrencia_descricao .= "<tr><td style='text-align: right'><small>" . number_format($cod[0], 0, ',', '.') . " linha(s) com&nbsp;</small></td><td><small>" . $cod[1] . " - " . $cod[2] . "</small></td></tr>";
                    }

                    $codigos_ocorrencia_descricao .= "</table>";
                    $_SESSION['sIMPOcorrencia']   = $codigos_ocorrencia_descricao;

                    print $_SESSION['sIMPOcorrencia'];
                    
                    print $_SESSION['sIMPBaseFormulario1'];

                    ?>
                </td>
            </tr>
        </table>

        </div>
    </p>
    <?php
}

function seleciona_servidores_por_ocorrencia()
{
    global $mes, $ano, $upag, $codigos_ocorrencia;

    // seleção dos servidores
    $sqlPonto = "
        SELECT
            pto.siape, cad.nome_serv AS nome
        FROM
            ponto" . $mes . $ano . " AS pto
        LEFT JOIN
            servativ AS cad ON pto.siape = cad.mat_siape
        LEFT JOIN
            tabsetor AS und ON cad.cod_lot = und.codigo
        WHERE
            und.upag = :upag
            AND pto.oco IN (" . $codigos_ocorrencia . ")
        GROUP BY
            pto.siape
        ORDER BY
            TRIM(cad.nome_serv)
    ";

    $params = array(
        array( ':upag', $upag, PDO::PARAM_STR ),
    );
        
    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query($sqlPonto, $params);

    return $oDBase;
}
