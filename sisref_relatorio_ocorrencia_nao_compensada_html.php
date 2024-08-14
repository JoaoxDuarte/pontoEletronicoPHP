<?php
set_time_limit(0);

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );
include_once( _DIR_INC_ . "calculo_horas_comuns_saldos.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
//verifica_permissao('sRH ou sTabServidor');
// parametros passados
$competencia = tratarHTML($_REQUEST['competencia']);
$comp = substr($competencia,0,2);
$year = substr($competencia,-4);

//definindo a competencia de homologacao
$oData = new trata_datasys;
$year  = (empty($year) ? $oData->getAnoHomologacao() : $year);
$comp  = (empty($comp) ? $oData->getMesHomologacao() : $comp);

//definindo competência para cobrança de atrasos
$ano = (empty($year) ? $oData->getAnoCompensado() : ($comp == '01' ? ($year - 1) : $year));
$mes = (empty($comp) ? $oData->getMesCompensado() : ($comp == '01' ? "12" : substr("00" . ($comp - 1), -2)));


// ocorrências
$obj = new OcorrenciasGrupos();
$codigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($sitcad=null, $exige_horarios=true);
$codigoDebitoPadrao  = $obj->CodigoDebitoPadrao();



## ############################### ##
##                                 ##
##            VALIDAÇÃO            ##
##                                 ##
## ############################### ##

// class valida
$validar = new valida();
$validar->setDestino( pagina_de_origem() ); //"sisref_relatorio_ocorrencia_nao_compensada_competencia.php" );
$validar->setExibeMensagem( false );

## MÊS
$validar->mes( $mes );

## ANO
$validar->ano( $ano );

// Exibe mensagem(ns) de erro, se houver
$validar->exibeMensagem();

// Testa se a competencia encontra-se entre o mês de Out/2009 (inclusive) e o mês atual (inclusive)
$anocomp = $year . $comp;
if ($anocomp < '200910' || $anocomp > date('Ym'))
{
    mensagem("Não é possível emitir relatório de cobrança para competência anterior a 10/2009 ou posterior ao mes atual!", pagina_de_origem(), 1);
}


## ############################### ##
##                                 ##
##         GERAR RELATÓRIO         ##
##                                 ##
## ############################### ##

// define lotacoes vinculadas a upag
$sLotacao = $_SESSION['sLotacao'];

// descricao da lotacao do servidor
$oSetor    = seleciona_dados_da_unidade($sLotacao);
$wnomelota = $oSetor->descricao;
$upag      = $oSetor->upag;


// grava dados em sessao para uso na impressao
$_SESSION['sIMPPaginaOrigem']      = pagina_de_origem();
$_SESSION['sIMPMes']               = $mes;
$_SESSION['sIMPAno']               = $ano;
$_SESSION['sIMPYear']              = $year;
$_SESSION['sIMPComp']              = $comp;
$_SESSION['sIMPCaminho']           = 'Relatórios » Frequência » Para comando » Cobrança';
$_SESSION['sIMPUpag']              = $upag;
$_SESSION['sIMPLotacao']           = $sLotacao;
$_SESSION['sIMPLotacaoDescricao']  = $wnomelota;
$_SESSION['sIMPTituloFormulario1'] = "Relatório de Servidores que aparecem com Ocorrência não compensada";
$_SESSION['sIMPTituloFormulario2'] = "Servidores que aparecem com ocorrências em " . $comp . "/" . $year . " que podem gerar perda de remuneração.";
$_SESSION['sIMPTituloFormulario3'] = "Servidores com código " . implode(',', $codigosCompensaveis) . " em " . $mes . "/" . $ano . ", que aparecem como não compensados até o mês subsequente ao do registro da ocorrência.";
$_SESSION['sIMPBaseFormulario1']   = "Obs: O relatório contempla registros não homologados e homologados.";

$_SESSION['saDadosEncontradosI'] = array();
$_SESSION['saDadosEncontradosF'] = array();


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
$oForm->setCSS(_DIR_CSS_ . "table_sorter.css");
$oForm->setJS(_DIR_JS_ . "jquery.tablesorter.js");

$oForm->setJS("sisref_relatorio_ocorrencia_nao_compensada_html.js");

$oForm->setSeparador(0);
$oForm->setLargura(920);

//$oForm->setIconeParaImpressao("sisref_relatorio_ocorrencia_nao_compensada_imp.php");

$oForm->setSubTitulo($_SESSION['sIMPTituloFormulario1']);

$observacaoTopo = "Emitido em: " . date("d/m/Y");
if (($year . $comp) == date('Ym'))
{
    $observacaoTopo .= "<br><font style='color: red; font-size: 10px; font-weight: bold;'>OCORRÊNCIAS DE " . $comp . "/" . $year . " (mês corrente) ESTÃO SUJEITAS A ALTERAÇÕES APÓS A <u>HOMOLOGAÇÃO</u>.</font>";
}

$oForm->setObservacaoTopo($observacaoTopo);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


## lista de servidores sem remuneracao
#
$oDBase               = seleciona_servidores_sem_remuneracao();
$numero_de_servidores = $oDBase->num_rows();

## lista de servidores com desconto
#
$oDBaseDesconto       = seleciona_servidores_com_desconto();
$numero_de_servidores += $oDBaseDesconto->num_rows();

if ($numero_de_servidores == 0)
{
    mensagem("Não foram encontrados registros para esta UPAG", null, 1);
}
else
{
    // contadores
    $sequencia             = 0;
    $registros_processados = 0;
    $legenda               = array();

    ## cabecalho do html
    #
    htmlCabecalho();

    ## servidores sem remuneracao
    #
    htmlCabecalho_sem_remuneracao();
    if ($oDBase->num_rows() > 0)
    {
        while ($dados = $oDBase->fetch_object())
        {
            htmlServidor_sem_remuneracao($dados->siape);

            $registros_processados++;
        }
    }
    else
    {
        htmlSem_registros();
    }
    htmlRodape_sem_remuneracao();

    
    ## servidores com desconto
    #
    // contadores
    $sequencia = 0;
    $sequencia = 0;
    $legenda   = array();
    
    
    htmlCabecalho_com_desconto();
    if ($oDBaseDesconto->num_rows() > 0)
    {
        while ($dados = $oDBaseDesconto->fetch_object())
        {
            htmlServidor_com_desconto($dados->siape);

            $registros_processados++;
        }
    }
    else
    {
        htmlSem_registros();
    }
    htmlRodape_com_desconto();

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
    <div class="container">
        <!-- Row Referente aos dados Setor do funcionario  -->
        <div class="row margin-12 comparecimento">
            <div class="col-md-12" id="dados-funcionario">
                <div class="col-md-3">
                    <h5><strong>ÓRGÃO</strong></h5>
                    <p><?= tratarHTML(getOrgaoMaisSigla($sLotacao)); ?></p>
                </div>
                <div class="col-md-9">
                    <h5><strong>UNIDADE</strong></h5>
                    <p><?= getUorgMaisDescricao($sLotacao); ?></p>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="container text-center">
    <?php
}

function htmlRodape()
{
    ?>
    </div>
    <?php
}


##########################################################
#                                                        #
#               SERVIDORES SEM REMUNERACAO               #
#                                                        #
##########################################################
#
function htmlCabecalho_sem_remuneracao()
{
    global $comp, $year;
    
    ?>
    <table class="table table-striped table-bordered text-center table-hover">
        <thead>
            <tr>
                <th class="text-center" colspan="9">
                    <h4><b><?= tratarHTML($comp); ?>/<?= tratarHTML($year); ?></b></h4>
                    <?= tratarHTML($_SESSION['sIMPTituloFormulario2']); ?>
                    <br>
                    <font style='font-size:12px;font-weight:normal;'><?= tratarHTML($_SESSION['sIMPBaseFormulario1']); ?></font>
                </th>
            </tr>
            <tr>
                <th class="text-center" style="vertical-align:middle;width:100px;">Seq.</th>
                <th class="text-center" style="vertical-align:middle;">Matr&iacute;cula</th>
                <th class="text-center" style="vertical-align:middle;">NOME</th>
                <th class="text-center" style="vertical-align:middle;">Ocorrência</th>
                <th class="text-center" style="vertical-align:middle;">Sigla</th>
                <th class="text-center" style="vertical-align:middle;">Data Inicial</th>
                <th class="text-center" style="vertical-align:middle;">Data Final</th>
                <th class="text-center" style="vertical-align:middle;">Dias</th>
            </tr>
        </thead>
        <tbody>
    <?php
}

function htmlRodape_sem_remuneracao()
{
    global $legenda;
    
    ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-left" style="vertical-align:top;font-size:12px;font-weight:normal;" colspan="8">
                    <b>Descrição das Ocorrência:</b><br>
                    <?php
                    foreach ($legenda as $key => $value)
                    {
                        print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $key . ' : ' . $value . '<br>';
                    }
                    ?>
                </th>
            </tr>
        </tfoot>
    </table>
    <div>&nbsp;</div>
    <?php
}

function htmlServidor_sem_remuneracao($siape)
{
    global $sequencia, $year, $comp, $legenda;


    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $pagarEmFolha = $obj->PagarEmFolha($sitcad, $exige_horarios=true);
    
    // atualiza_frqANO(
    //   <matricula do servidor>, <mes>, <ano>,
    //   [[<arquivo destino>], [[<barra de progresso>], [[<calcular>], [<processa competencia atual>]]]]
    // );
    atualiza_frqANO($siape, $comp, $year, '', false, true, ($ocorr != ''));

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // dados do servidor
    $oDBase->setMensagem("Problemas no acesso a Tabela FRQ - " . $year . " (E000086.".__LINE__.").");
    $oDBase->query( "
    SELECT
        frq.mat_siape,
        frq.dia_ini,
        frq.dia_fim,
        frq.cod_ocorr,
        frq.dias,
        frq.horas,
        frq.minutos,
        cad.nome_serv,
        cad.cod_lot,
        oco.siapecad,
        oco.cod_siape,
        oco.semrem,
        oco.desc_ocorr,
        oco.cod_ocorr AS sigla
    FROM
        frq" . $year . " AS frq
    LEFT JOIN
        servativ AS cad ON frq.mat_siape = cad.mat_siape
    LEFT JOIN
        tabocfre AS oco ON frq.cod_ocorr = oco.siapecad
    WHERE
        frq.mat_siape = :siape
        AND frq.compet = :comp
        AND frq.cod_ocorr IN (" . implode(',', $pagarEmFolha) . ")
        AND oco.semrem = 'S'
    ORDER BY
        frq.mat_siape, frq.dia_ini
    ",
    array(
        array(':siape', $siape, PDO::PARAM_STR),
        array(':comp',  $year.$comp, PDO::PARAM_STR)
    ));

    $num = $oDBase->num_rows();

    if ($num > 0)
    {
        while ($pm = $oDBase->fetch_object())
        {
            $semrem = $pm->semrem;
            if ($semrem == "S")
            {
                $legenda[$pm->siapecad] = $pm->desc_ocorr;
                $sequencia++;
                $nome = htmlspecialchars(mb_convert_encoding(ajustar_acentos(retira_acentos($pm->nome_serv)), "UTF-8", "ISO-8859-1"));
                ?>
                <tr height='18'>
                    <td align='center'><?= tratarHTML($sequencia); ?></td>
                    <td align='center'><?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?></td>
                    <td nowrap><?= tratarHTML($nome); ?></td>
                    <td align='center'><?= tratarHTML($pm->siapecad); ?></td>
                    <td align='center'><small><?= tratarHTML($pm->sigla); ?></small></td>
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

function seleciona_servidores_sem_remuneracao()
{
    global $comp, $year, $upag;

    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $pagarEmFolha = $obj->PagarEmFolha($sitcad, $exige_horarios=true);

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // seleção dos servidores
    $oDBase->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA - " . $comp . '/' . $year . " (E000087.".__LINE__.").");
    $oDBase->query( "
    SELECT
        pto.siape, cad.nome_serv AS nome
    FROM
        ponto" . $comp . $year . " AS pto
    LEFT JOIN
        servativ AS cad ON pto.siape = cad.mat_siape
    LEFT JOIN
        tabocfre AS oco ON pto.oco = oco.siapecad
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    WHERE
        und.upag = :upag
        AND pto.oco IN (" . implode(',', $pagarEmFolha) . ")
        AND oco.semrem = 'S'
    GROUP BY
        pto.siape
    ORDER BY
        pto.siape, pto.dia
    ",
    array(
        array(':upag', $upag, PDO::PARAM_STR)
    ));
    
    return $oDBase;
}


##########################################################
#                                                        #
#                SERVIDORES COM DESCONTO                 #
#                                                        #
##########################################################
#
function htmlCabecalho_com_desconto()
{
    global $comp, $year;
    
    ?>
    <table class="table table-striped table-bordered text-center table-hover">
        <thead>
            <tr>
                <th class="text-center" colspan="8">
                    <h4><b><?= tratarHTML($comp); ?>/<?= tratarHTML($year); ?></b></h4>
                    <?= tratarHTML($_SESSION['sIMPTituloFormulario3']); ?>
                    <br>
                    <font style='font-size:12px;font-weight:normal;'><?= tratarHTML($_SESSION['sIMPBaseFormulario1']); ?></font>
                </th>
            </tr>
            <tr>
                <th class="text-center" style="vertical-align:middle;width:100px;">Seq.</th>
                <th class="text-center" style="vertical-align:middle;">Matr&iacute;cula</th>
                <th class="text-center" style="vertical-align:middle;">NOME</th>
                <th class="text-center" style="vertical-align:middle;">Ocorrência</th>
                <th class="text-center" style="vertical-align:middle;">Sigla</th>
                <th class="text-center" style="vertical-align:middle;">Data Inicial</th>
                <th class="text-center" style="vertical-align:middle;">Data Final</th>
                <th class="text-center" style="vertical-align:middle;">Dias</th>
            </tr>
        </thead>
        <tbody>
    <?php
}

function htmlRodape_com_desconto()
{
    global $legenda;
    
    ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-left" style="vertical-align:top;font-size:12px;font-weight:normal;" colspan="8">
                    <b>Descrição das Ocorrência:</b><br>
                    <?php
                    foreach ($legenda as $key => $value)
                    {
                        print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $key . ' : ' . $value . '<br>';
                    }
                    ?>
                </th>
            </tr>
        </tfoot>
    </table>
    <div>&nbsp;</div>
    <?php
}

function htmlServidor_com_desconto($siape)
{
    global $sequencia, $year, $comp, $upag, $mes, $ano;

    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);
    $codigoCreditoPadrao = $obj->CodigoCreditoPadrao($sitcad);
    $codigoDebitoPadrao  = $obj->CodigoDebitoPadrao($sitcad);


    // codigos a pesquisar
    $codigo_credito      = implode(',', $codigoCreditoPadrao); //'33333';
    $codigo_debito       = implode(',', $codigoDebitoPadrao); //'00172';
    $codigos_a_compensar = implode(',', $codigosCompensaveis); //"'00172','55555','62010','62012','62014','99999'";


    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // dados do servidor
    $oPonto = new DataBase('PDO');

    // pega o código siape que corresponde ao
    // código SiapeCAD 00172
    $oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIA (E000088.".__LINE__.").");
    $oDBase->query( "
    SELECT
        oco.siapecad, oco.cod_siape
    FROM
        tabocfre AS oco
    WHERE
        oco.siapecad IN (" . $codigo_debito . ")
        AND oco.ativo = 'S'
    ");

    $codigo_siape_para_00172 = $oDBase->fetch_object()->cod_siape;


    // horas comuns (créditos/débitos/compensações)
    $comp_inicial = ($mes == '' ? date('m') : $mes) . '/' . ($ano == '' ? date('    Y') : $ano);
    $comp_final   = $comp . '/' . $year;

    $aHorasComuns = resultado_horas_comuns($siape, $comp_inicial, $comp_final, $comp_admissao, $comp_exclusao);


    // apura horas devidas
    $oDBase->setMensagem("Problemas no acesso a Tabela BANCO DE COMPENSAÇÃO (E000089.".__LINE__.").");
    $oDBase->query( "
    SELECT
        bcoh.siape, cad.nome_serv AS nome, SEC_TO_TIME(ABS(TIME_TO_SEC(bcoh.sub_total))) AS sub_total
    FROM
        banco_de_horas AS bcoh
    LEFT JOIN
        servativ AS cad ON bcoh.siape = cad.mat_siape
    WHERE
        bcoh.siape = :siape
        AND bcoh.comp = :comp
        AND bcoh.sub_total < 0
        AND bcoh.tipo = '1'
    ORDER BY
        bcoh.siape
    ",
    array(
        array(':siape', $siape, PDO::PARAM_STR),
        array(':comp',  $year.$comp, PDO::PARAM_STR)
    ));    

    if ($oDBase->num_rows() > 0)
    {
        // dados
        $bcoh        = $oDBase->fetch_object();
        $siape       = $bcoh->siape;
        $nome        = htmlspecialchars(mb_convert_encoding(ajustar_acentos(retira_acentos($bcoh->nome)), "UTF-8", "ISO-8859-1"));
        $nFolha      = $bcoh->sub_total;
        $nFolhaSobra = $nFolha;
        
        $codigo_pgamp                   = (_SISTEMA_ORGAO_ == '57202' ? "90293" : "");
        $sub_secretaria_no_inss         = (_SISTEMA_ORGAO_ == '57202' ? " AND LEFT(cad.cod_lot,1) <> '9' " : "");
        $inss_aguardando_pericia_medica = (_SISTEMA_ORGAO_ == '57202' ? " AND pto.oco NOT IN ('80124') " : "");

        $bFinaliza = false;
        
        $oPonto->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA - " . $mes . '/' . $ano . " (E000090.".__LINE__.").");
        $oPonto->query("
        SELECT
            siape
        FROM
            ponto".$mes.$ano." AS pto
        LEFT JOIN
            servativ AS cad ON pto.siape = cad.mat_siape
        LEFT JOIN
            tabsetor AS und ON cad.cod_lot = und.codigo
        LEFT JOIN
            tabcargo AS crg ON cad.cod_cargo = crg.cod_cargo
        WHERE
            pto.siape = :siape
            AND und.upag = :upag
            AND cad.excluido = 'N'
            AND oco IN ('" . $codigo_pgamp . "')
            " . $inss_aguardando_pericia_medica . " 
            " . $sub_secretaria_no_inss . " 
        ORDER BY
            pto.siape
        ",
        array(
            array( ':siape', $siape, PDO::PARAM_STR ),
            array( ':upag',  $upag,  PDO::PARAM_STR ),
        ));
        
        $nopgamp = ($oPonto->num_rows() > 0 ? "PGAMP" : "");

        if (existeDBTabela('adesao_pgamp'))
        {
            $oPonto->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA - " . $mes . '/' . $ano . " (E000091.".__LINE__.").");
            $oPonto->query("
            SELECT
                DATE_FORMAT(pto.dia,'%d') AS dia,
                pto.dia AS dia_registro,
                pto.siape,
                pto.jorndif,
                pgamp.exclusao,
                '".$nopgamp."' AS nopgamp,
                pto.oco
            FROM
                ponto".$mes.$ano." AS pto
            LEFT JOIN
                servativ AS cad ON pto.siape = cad.mat_siape
            LEFT JOIN
                tabsetor AS und ON cad.cod_lot = und.codigo
            LEFT JOIN
                tabcargo AS crg ON cad.cod_cargo = crg.cod_cargo
            LEFT JOIN
                adesao_pgamp AS pgamp ON pto.siape = pgamp.siape
            WHERE
                pto.siape = :siape
                AND und.upag = :upag
                AND cad.excluido = 'N'
                AND oco IN (".$codigos_a_compensar.")
                " . $inss_aguardando_pericia_medica . " 
                " . $sub_secretaria_no_inss . " 
            ORDER BY
                pto.dia DESC
            ",
            array(
                array( ':siape', $siape, PDO::PARAM_STR ),
                array( ':upag',  $upag,  PDO::PARAM_STR ),
            ));
        }
        else
        {
            $oPonto->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA - " . $mes . '/' . $ano . " (E000092.".__LINE__.").");
            $oPonto->query("
            SELECT
                DATE_FORMAT(pto.dia,'%d') AS dia,
                pto.dia AS dia_registro,
                pto.siape,
                pto.jorndif,
                '0000-00-00 00:00:00' AS exclusao,
                '' AS nopgamp,
                pto.oco
            FROM
                ponto".$mes.$ano." AS pto
            LEFT JOIN
                servativ AS cad ON pto.siape = cad.mat_siape
            LEFT JOIN
                tabsetor AS und ON cad.cod_lot = und.codigo
            LEFT JOIN
                tabcargo AS crg ON cad.cod_cargo = crg.cod_cargo
            WHERE
                pto.siape = :siape
                AND und.upag = :upag
                AND cad.excluido = 'N'
                AND oco IN (".$codigos_a_compensar.")
                " . $inss_aguardando_pericia_medica . " 
                " . $sub_secretaria_no_inss . " 
            ORDER BY
                pto.dia DESC
            ",
            array(
                array( ':siape', $siape, PDO::PARAM_STR ),
                array( ':upag',  $upag,  PDO::PARAM_STR ),
            ));
        }
        
        $matricula_atual = "";

        if ($oPonto->num_rows() > 0)
        {
            while ($oFolha = $oPonto->fetch_object())
            {
                $nDia        = $oFolha->dia;
                $nJornadaDif = $oFolha->jorndif;
                
                
                $oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E000093.".__LINE__.").");
                $oDBase->query("
                SELECT
                    dia, codigo_debito
                FROM
                    tabfacultativo172
                WHERE
                    dia = '".$oFolha->dia_registro."'
                    AND ativo='S'
                    AND jogo_do_brasil='S'
                    AND (DATE_FORMAT('".$oFolha->dia_registro."','%Y%m') >= DATE_FORMAT(compensacao_inicio,'%Y%m')
                         AND DATE_FORMAT('".$oFolha->dia_registro."','%Y%m') <= DATE_FORMAT(compensacao_fim,'%Y%m'))
                ORDER BY
                    dia
                ",
                array(
                    array( ':dia_registro', $oFolha->dia_registro, PDO::PARAM_STR ),
                ));
                
                $eventoEsportivo = $oDBase->fetch_object();

                if ($oDBase->num_rows() > 0 && ($eventoEsportivo->codigo_debito == $oFolha->oco))
                {
                    continue;
                }
                

                // calculo
            	if ($nFolhaSobra > $nJornadaDif)
            	{
                    $nFolhaSobra = subtrairHoras( $nJornadaDif, $nFolhaSobra );
                    $total_horas = $nJornadaDif;
            	}
            	else
            	{
                    $total_horas = substr( $nFolhaSobra, 0, 5 );
                    $bFinaliza   = true;
            	}

                //if (time_to_sec($total_horas) > 0)
            	//{
                    if (empty($matricula_atual) || $matricula_atual != $siape)
                    {
                        $sequencia++;
                    }

                    if (empty($oFolha->nopgamp))
                    {
                        ?>
                        <tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'>
                            <td align='center'><?= tratarHTML($sequencia); ?></td>
                            <td align='center'><?= tratarHTML(removeOrgaoMatricula($siape)); ?></td>
                            <td nowrap><font color='#c3c3c3'><?= tratarHTML($nome); ?></font></td>
                            <td align='center'><?= tratarHTML($codigo_debito); ?></td>
                            <td align='center'><?= tratarHTML($codigo_siape_para_00172); ?></td>
                            <td align='center'><?= tratarHTML($nDia); ?></td>
                            <td align='center'><?= tratarHTML($total_horas); ?></td>
                        </tr>
                        <?php

                        array_push($_SESSION['saDadosEncontradosF'], array($siape,$nome,$codigo_debito,$codigo_siape_para_00172,$nDia,$total_horas));
                    }
            	//}

                $matricula_atual = $siape;

            	if ($bFinaliza == true)
            	{
                    break;
            	}
            }

            //$sequencia++;
            $total_horas = substr( $nFolha, 0, 5 );

            if (empty($oFolha->nopgamp))
            {
                ?>
                <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                    <td align='center'><b><?= tratarHTML($sequencia); ?></b></td>
                    <td align='center'><b><?= tratarHTML(removeOrgaoMatricula($siape)); ?></b></td>
                    <td nowrap><b><?= tratarHTML($nome); ?></b></td>
                    <td align='center'><b><?= tratarHTML($codigo_debito); ?></b></td>
                    <td align='center'><b><?= tratarHTML($codigo_siape_para_00172); ?></b></td>
                    <td align='center'><b>TOTAL</b></td>
                    <td align='center'><b><?= tratarHTML($total_horas); ?></b></td>
                    <td align='center'<?= tratarHTML($style_font); ?>><?= $total_horas; ?></td>
                </tr>
                <?php
            }
            else
            {
                ?>
                <tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'>
                    <td align='center'><b><?= tratarHTML($sequencia); ?></b></td>
                    <td align='center'><b><?= tratarHTML(removeOrgaoMatricula($siape)); ?></b></td>
                    <td nowrap><b><?= tratarHTML($nome); ?></b></td>
                    <td colspan='4' style='text-align:center;color:#ff0000;font-weight:bold;'><?= tratarHTML($oFolha->nopgamp); ?></td>
                </tr>
                <?php
            }

            array_push($_SESSION['saDadosEncontradosF'], array($siape,$nome,$codigo_debito,$codigo_siape_para_00172,"TOTAL",$total_horas));
        }
    }
}

function seleciona_servidores_com_desconto()
{
    global $mes, $ano, $upag;

    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;


    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigosCompensaveis = $obj->CodigosCompensaveis($sitcad, $exige_horarios=true);


    // codigos a pesquisar
    $codigos_a_compensar = implode(',', $codigosCompensaveis); //"'00172','55555','62010','62012','62014','99999'";

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // seleção dos servidores
    $oDBase->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA - " . $mes . '/' . $ano . " (E000094.".__LINE__.").");
    $oDBase->query("
    SELECT
        pto.siape,
        cad.nome_serv AS nome
    FROM
        ponto" . $mes . $ano . " AS pto
    LEFT JOIN
        servativ AS cad ON pto.siape = cad.mat_siape
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    WHERE
        und.upag = :upag
        AND pto.oco IN (" . $codigos_a_compensar . ")
    GROUP BY
        pto.siape
    ORDER BY
        pto.siape, pto.dia
    ",
    array(
        array(':upag', $upag, PDO::PARAM_STR)
    ));

    return $oDBase;
}

function htmlSem_registros()
{
    ?>
    <tr height='18'>
        <td align='center' colspan="8">Sem registros para exibir</td>
    </tr>
    <?php
}

