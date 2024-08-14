<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    $cmd = anti_injection($_REQUEST["cmd"]);
}
else
{
    $dados   = explode(":|:", base64_decode($dadosorigem));
    $cmd     = $dados[0];
    $lotacao = $dados[1];
}

$lotacao = ($lotacao == "" ? $_SESSION['sLotacao'] : $dados[1]);

// dados voltar
$_SESSION['voltar_nivel_0'] = base64_decode($cmd . ':|:' . $lotacao);

// dados em sessao
$sMatricula = $_SESSION["sMatricula"]; // matricula do usu�rio logado

$_SESSION["dia_processado"] = '';

// Compet�ncia atual (m�s e ano)
$data               = new trata_datasys();
$ano                = $data->getAnoHomologacao();
$mes                = $data->getMesHomologacao();
$competencia        = $data->getCompetHomologacao(); // mes e ano, ex.: 032010


## classe para montagem do formulario padrao
#
$title = _SISTEMA_SIGLA_ . ' | Homologa��o de Frequ�ncia';

$oForm = new formPadrao();
$oForm->setSubTitulo( "Homologa��o de Frequ�ncia ");
$oForm->setCSS( "css/table_sorter.css" );
$oForm->setJS( "js/phpjs.js" );
$oForm->setJS( "js/jquery.tablesorter.js" );

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML($width='1300px;');


// verifica se est� dentro do per�odo v�lido para homologa��o
if (verificaLiberadaHomologacao($lotacao, $mes, $ano) === false)
{
    formularioHomologacaoExpirada($lotacao, $mes, $ano);
}
else
{
    formularioHomologacaoServidores($lotacao, $mes, $ano);
}

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/* *****************************************************
 *                                                     *
 *               FUN��ES COMPLEMENTARES                *
 *                                                     *
 ***************************************************** */

/* @info  Seleciona dados do servidor logado
 *
 * @param  string  $siape  Matr�cula SIAPE do usu�rio
 * @return  boolean  $retorno  TRUE homologa��o liberada
 * @author  Edinalvo Rosa
 */
function verificaLiberadaHomologacao($lotacao, $mes, $ano)
{
    if (verificaLiberadaHomologacaoMensal() === true || verificaUnidadeLiberadaHomologacao($lotacao, $mes, $ano) === true)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/* @info  Verifica se o dia encontra-se dentro do intervalo para homologa��o
 *
 * @param  void
 * @return  boolean  $retorno  TRUE homologa��o liberada
 * @author  Edinalvo Rosa
 */
function verificaLiberadaHomologacaoMensal()
{
    $retorno = false;
    $hoje    = date("Ymd");

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela de PRAZOS (E000122.".__LINE__.")");
    $oDBase->query("
    SELECT 
        compi
    FROM 
        tabvalida 
    WHERE 
        ativo = 'S'
        AND DATE_FORMAT(NOW(),'%Y-%m-%d') BETWEEN apsi AND apsf
    ");

    return ($oDBase->num_rows() > 0);
}


/* @info  Seleciona dados do servidor logado
 *
 * @param  string  $siape  Matr�cula SIAPE do usu�rio
 * @return  boolean  $retorno  TRUE homologa��o liberada
 * @author  Edinalvo Rosa
 */
function verificaUnidadeLiberadaHomologacao($lotacao, $mes, $ano)
{
    $compet = $ano.$mes;
    
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela HOMOLOGA��O DILA��O DE PRAZO (E000123.".__LINE__.").");
    $oDBase->query("
    SELECT 
        setor,
        compet,
        IF((homologacao_dilacao_prazo.deliberacao = 'Deferido' 
            AND homologacao_dilacao_prazo.homologacao_limite >= DATE_FORMAT(NOW(),'%Y-%m-%d')) 
            OR (ISNULL(homologacao_dilacao_prazo.deliberacao) 
                AND homologacao_dilacao_prazo.homologacao_limite = '0000-00-00'),
                    'ativa',
                    'expirada') AS prazo
    FROM 
        homologacao_dilacao_prazo 
    WHERE 
        homologacao_dilacao_prazo.setor = :setor 
        AND homologacao_dilacao_prazo.compet = :compet
    ORDER BY 
        homologacao_dilacao_prazo.data_registro DESC
    LIMIT 1
    ", array(
        array(':compet', $compet,  PDO::PARAM_STR),
        array(':setor',  $lotacao, PDO::PARAM_STR)
    ));

    $dilacao = $oDBase->fetch_object()->prazo;

    return ($oDBase->num_rows() > 0 && $dilacao == 'ativa');
}


/* @info  Formul�rio com informa��es sobre prazo de homologa��o
 *
 * @param  string  $mes  M�s de homologa��o
 * @param  string  $ano  Ano de homologa��o
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function formularioHomologacaoExpirada($lotacao, $mes, $ano)
{
    $sApsi = $_SESSION["sApsi"]; // data inicial
    $sApsf = $_SESSION["sApsf"]; // data final

    formularioHomologacaoJavascript();

    ?>
    <div class="container margin-20" id="form-comparecimento">
        <div class="row margin-10">
            
            <?php formularioHomologacaoBotaoVoltar(); ?>

            <div class="col-md-12" id="dados-funcionario">
                <div class="col-md-2">
                    <h5><strong>COMPET�NCIA</strong></h5>
                    <p><b><?= tratarHTML($mes) . '/' . tratarHTML($ano); ?></b></p>
                </div>
                <!-- Row Referente aos dados Setor do funcionario  -->
                <div class="col-md-10">
                    <div class="row">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th class="text-center" style='vertical-align:middle;'>�RG�O</th>
                                    <th class="text-center" style='vertical-align:middle;'>LOTA��O</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?></h4></td>
                                    <td class="text-center"><h4><?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></h4></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row col-md-offset-3 text-center" style="color: black; font-size:12px; font-family:Verdana, serif;">
                        <table class="table table-condensed table-handle">
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        PER�ODO PARA HOMOLOGA��O DA FREQU�NCIA
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!--
                                    <td class="text-center" style="text-shadow: 1px 1px 2px #b1b1b1, 0 0 1em white, 0 0 0.2em white; color: black; font-size:20px; font-family:Verdana, serif;">
                                    -->
                                    <td class="text-center" style="color:black;font-size:16px; font-family:Verdana, serif;">
                                        DE <?= dataseca($sApsi); ?> A <?= dataseca($sApsf); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="font-weight:normal;">
                                        Solicitar Dila��o de prazo para Homologa��o:
                                        <a href="javascript:void(0);" 
                                           data-load-solicitar-liberacao-homologar="gestao_liberar_homologacao_solicitacao.php?unidade=<?= tratarHTML($lotacao); ?>">Clique Aqui</a>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php formularioHomologacaoBotaoVoltar(); ?>

        </div>
    </div>
    <?php
}


/* @info  Formul�rio servidores do setor
 *
 * @param  string  $lotacao  Unidade para homologar
 * @param  string  $mes  M�s de homologa��o
 * @param  string  $ano  Ano de homologa��o
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function formularioHomologacaoServidores($lotacao, $mes, $ano)
{
    global $dadosorigem, $mes, $ano;
    
    $sApsi = $_SESSION["sApsi"]; // data inicial
    $sApsf = $_SESSION["sApsf"]; // data final

    ##
    #  SELECIONA OS REGISTROS PARA HOMOLOGA��O
    #
    $registrosHomologar = seleciona_servidores($link, $lotacao, 'N', $ano . $mes, $homologacao=true);
    $num_rows           = $registrosHomologar->num_rows();

    formularioHomologacaoJavascript();
    
    ?>
    <div class="container margin-20" id="form-comparecimento">
        <div class="row margin-10">
            
            <?php formularioHomologacaoBotaoVoltar(); ?>

            <div class="col-md-12" id="dados-funcionario">
                <div class="col-md-2">
                    <h5><strong>COMPET�NCIA</strong></h5>
                    <p><b><?= tratarHTML($mes) . '/' . tratarHTML($ano); ?></b></p>
                </div>
                <!-- Row Referente aos dados Setor do funcionario  -->
                <div class="col-md-10">
                    <div class="row">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th class="text-center" style='vertical-align:middle;'>�RG�O</th>
                                    <th class="text-center" style='vertical-align:middle;'>LOTA��O</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?></h4></td>
                                    <td class="text-center"><h4><?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></h4></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row margin-10">
            <table class="table text-center" style="height:0px;padding:0px;margin:0px;vertical-align: bottom;">
                <thead>
                    <!-- COVID-19 -->
                    <tr style="text-align: left;vertical-align: bottom;">
                        <td colspan="1" style="text-align: left;vertical-align: bottom;">
                            <fieldset width='100%'>Total de <?= tratarHTML($num_rows); ?> registros.</fieldset>
                        </td>
                        <td colspan="4" style="text-align:right;vertical-align: bottom;">
                            <div class="col-md-12">
                                <div class="col-md-1 text-right" style="text-align:right;vertical-align: bottom;">
                                    <label for="lot" class="control-label">&nbsp;</label>
                                </div>
                                <div class="col-md-11 text-right" style="text-align:right;vertical-align: bottom;">
                                    <a class="btn btn-default" href="javascript:void(0);" data-load-homologar-frequencia-covid19="frequencia_inclusao_por_lote.php?dados=<?= $dadosorigem; ?>">
                                        <span class="glyphicon glyphicon-list-alt"></span> Incluir por Lote COVID-19
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <!-- Fim COVID-19 -->
                </thead>
            </table>
            <table class="table table-striped table-bordered text-center table-hover">
                <thead>
                    <tr>
                        <th class="text-center">SIAPE</th>
                        <th class="text-center">Nome</th>
                        <th class="text-center">Situa��o</th>
                        <th class="text-center">A��o</th>
                        <th class="text-center">Devolvida</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($num_rows == 0)
                    {
                        //mensagem("Sem registro para homologa��o");
                        ?>
                        <tr>
                            <td class="text-center" colspan='5'>Sem registro para homologa��o</td>
                        </tr>
                        <?php
                    }
                    else
                    {
                        while ($pm = $registrosHomologar->fetch_object())
                        {
                            ?>
                            <tr>
                                <td class='text-center'><?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?></td>
                                <td class='text-left'><?= tratarHTML($pm->nome_serv); ?></td>
                                <td class='text-center'><?= tratarHTML($pm->situacao); ?></td>
                                <?php

                                if ($pm->freqh == "S" && $pm->frequencia_devolvida == 'N')
                                {
                                    ?>
                                    <td class='text-center' style='cursor:help;' alt='<?= tratarHTML($pm->homologador); ?>' title='<?= tratarHTML($pm->homologador); ?>'>HOMOLOGADO</td>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <td class='text-center'>
                                        <a href='javascript:window.location.replace("frequencia_homologar_registros.php?dados=<?= base64_encode(tratarHTML($pm->mat_siape) . ':|:' . tratarHTML($pm->cod_lot) . ':|:' . tratarHTML($pm->jornada) . ':|:' . tratarHTML($pm->cod_sitcad)); ?>")' style='font-weight:bold;color:red;'>HOMOLOGAR</a>
                                    </td>
                                    <?php
                                }

                                ?>
                                <td align='center'>
                                    <?php

                                    if ($oDesomologado->dfreq == "S" && $pm->motidev != "" && $pm->frequencia_devolvida == 'S')
                                    {
                                        preparaShowDivIFrame('ver' . $pm->mat_siape, 856, 370); //preparaShowDivIFrame( 'ver'.$pm->mat_siape, 960, 410);
                                        ?>
                                        <a id='ver<?= tratarHTML($pm->mat_siape); ?>' href='#' src='frequencia_homologar_ver_motivo_devolucao.php?dados=<?= base64_encode(tratarHTML($pm->mat_siape) . ':|:0'); ?>' title='Motivo da Desomologa��o' style='font-size:12px;'>Desomologado</a>
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        &nbsp;
                                        <?php
                                    }

                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
            
            <?php formularioHomologacaoBotaoVoltar(); ?>

        </div>
    </div>
    <?php
}


/* @info  M�todos Javascript
 *
 * @param  vorid
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function formularioHomologacaoJavascript()
{
    ?>
    <script>
        $(document).ready(function (){
    
            $('[data-load-homologar-frequencia-voltar]').on('click',function(e) {
                var $this = $(this);
                var remote = $this.data('load-homologar-frequencia-voltar');

                e.preventDefault();

                window.location.replace( remote );
            });

            $('[data-load-homologar-frequencia-covid19]').on('click',function(e) {
                var $this = $(this);
                var remote = $this.data('load-homologar-frequencia-covid19');

                e.preventDefault();

                window.location.replace( remote );
            });

            $('[data-load-solicitar-liberacao-homologar]').on('click',function(e) {
                var $this = $(this);
                var remote = $this.data('load-solicitar-liberacao-homologar');

                e.preventDefault();

                window.location.replace( remote );
            });
        });
    </script>
    <?php
}


/* @info  Exibe bot�o voltar
 *
 * @param  vorid
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function formularioHomologacaoBotaoVoltar()
{
    global $lotacao;
    
    if ($_SESSION["sSenhaI"] == "S")
    {
        ?>
        <div class="col-md-12">
            <div class="col-md-12 text-right">
                <a class="btn btn-danger" href="javascript:void(0);" data-load-homologar-frequencia-voltar="frequencia_homologar_entra.php?unidade=<?= tratarHTML($lotacao); ?>">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
        <?php
    }
}

