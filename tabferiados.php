<?php

include_once("config.php");

// verifica se o usuário tem permissão para acessar este módulo
verifica_permissao("sRH ou Chefia");

// instancia banco de dados
$oDBase = new DataBase('PDO');

// pesquisa dados
$nAnoAtual = date('Y');

for ($n = 1; $n >= 0; $n--)
{
    $nAno = ($nAnoAtual + $n);

    $sTabela = 'feriados_' . $nAno;
  
    if (existeDBTabela($sTabela, 'sisref') == true)
    {
        $sTabela = 'feriados' . ($n == 0 ? '' : '_' . $nAno);
    }
    else
    {
        continue;
    }

    $pesquisa = "";

    $var1 = retira_acentos(anti_injection($_POST['chave']));
    $var2 = anti_injection($_POST['escolha']);
    $var3 = anti_injection($_POST['ano']);
    $dia  = substr($var1, 0, 2);
    $mes  = substr($var1, 3, 2);
}

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJS( 'js/jquery.blockUI.js?v2.38' );
$oForm->setJS( 'js/jquery.bgiframe.js' );
$oForm->setJS( 'js/plugins/jquery.dlg.min.js' );
$oForm->setJS( 'js/plugins/jquery.easing.js' );

$oForm->setSubTitulo( "Pesquisa Tabela de Feriados" );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script language="javascript">

$(document).ready(function ()
{
  /*  $("form").keypress(function () {
        validar();
    });*/

    $("#btn-continuar").click(function () {
        validar();
    });

});

function validar()
{
    if ($('#chave').val().length == 0)
    {
        $('#chave').focus();
        mostraMensagem('É obrigatório informar a chave para pesquisa!', 'warning');
        return false;
    }
    else
    {
        // mensagem processando
        showProcessandoAguarde();

        $('#form1').attr('action', "tabferiados.php");
        $('#form1').submit();
    }
}

</script>


<div class="container" id="form-comparecimento">
    <div class="row margin-10">

        <div class="col-md-12 text-left">
            <h6>
                Para pesquisar por data digite no formato dd/mm.<br>
                Para pesquisar por descri&ccedil;&atilde;o digite parte do nome do feriado ex. JORGE para pesquisar dia de S&atilde;o Jorge.<br>
                Para pesquisar por lota&ccedil;&atilde;o, digite o codigo da unidade com o do órgão. Ex.: 40107000000854 (Órgão 40107 UORG 000000854).
                Para pesquisar por estado, digite a sigla da unidade da federação.
            </h6>
        </div>

        <div class="col-md-12">
            <form method="POST" id="form1" name="form1">
                <input type="hidden" name="modo" value="<?= $modo; ?>" >
                <input type="hidden" name="corp" value="<?= $corp; ?>">

                <table class="table table-condensed">
                    <tr>
                        <td class="corpo" colspan="3" align='center'>

                            <!-- Multiple Radios -->
                            <div class="form-group">
                                <div class="col-md-1"></div>
                                <div class="col-md-2">
                                    <div class="radio">
                                        <label for="radios-0">
                                            <input type="radio" name="escolha" id="escolha" value="dia" <?= ($_REQUEST["escolha"] == 'dia' || $_REQUEST["escolha"] == '' ? 'checked' : ''); ?> onclick="$('#chave').focus()">
                                            Por Data
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="radio">
                                        <label for="radios-1">
                                            <input type="radio" name="escolha" id="escolha" value="descricao" <?= ($_REQUEST["escolha"] == 'descricao' ? 'checked' : ''); ?> onclick="$('#chave').focus()">
                                            Por Descrição
                                        </label>
                                    </div>
                                </div>
                                <!--
                                <div class="col-md-2">
                                    <div class="radio">
                                        <label for="radios-1">
                                            <input type="radio" name="escolha" id="escolha" value="lotacao" <?= ($_REQUEST["escolha"] == 'lotacao' ? 'checked' : ''); ?> onclick="$('#chave').focus()">
                                                Por Lotação
                                        </label>
                                    </div>
                                </div>
                                -->
                                <div class="col-md-2">
                                    <div class="radio">
                                        <label for="radios-1">
                                            <input type="radio" name="escolha" id="escolha" value="municipio" <?= ($_REQUEST["escolha"] == 'municipio' ? 'checked' : ''); ?> onclick="$('#chave').focus()">
                                            Por Município
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="radio">
                                        <label for="radios-1">
                                            <!--
                                            <input type="radio" name="escolha" id="escolha" value="estado" <?= ($_REQUEST["escolha"] == 'estado' ? 'checked' : ''); ?> onclick="$('#chave').focus()">
                                            -->
                                            <input type="radio" name="escolha" id="escolha" value="lotacao" <?= ($_REQUEST["escolha"] == 'lotacao' ? 'checked' : ''); ?> onclick="$('#chave').focus()">
                                            Por Estado (sigla)
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <td width="15%">&nbsp;</td>
                        <td class="corpo" width="65%" align="center">
                            <div class='col-md-11 col-md-offset-1'>
                                <font class="tahomaSize_2">Chave: </font>
                                <input type="text" class="form-control" id="chave" name="chave" value="<?= $_REQUEST["chave"]; ?>" title="Não informe pontos" size="50" maxlength='100'>
                            </div>
                        </td>
                        <td width="20%">&nbsp;</td>
                    </tr>
                </table>

                <div class="col-md-2 col-lg-offset-5 text-center" style="padding-bottom:15px;">
                    <a class="btn btn-success btn-block" id="btn-continuar" role="button">
                        <span class="glyphicon glyphicon-search"></span> Pesquisar
                    </a>
                </div>

            </form>
        </div>

        <div class="row margin-10">
            <?php if((isset($_SESSION['sGestaoUPAG']) && $_SESSION['sGestaoUPAG'] == 'S') || (isset($_SESSION['sAdmCentral']) && $_SESSION['sAdmCentral'] == 'S')){ ?>
                <input type="button" class="btn btn-default btn_adicionar" style="padding-left:30px;padding-right:30px; margin-bottom: 4px;" value="Adicionar" onClick="document.location.href='criarferiado.php'" />
            <?php } ?>
            <table class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th class="text-center" style="vertical-align:middle;">Ações</th>
                        <th class="text-center" style="vertical-align:middle;">Data</th>
                        <th class="text-center" style="vertical-align:middle;">Descrição</th>
                        <th class="text-center" style="vertical-align:middle;">Tipo</th>
                        <th class="text-center" style="vertical-align:middle;">UF</th>
                        <th class="text-center" style="vertical-align:middle;">Município</th>
                        <th class="text-center" style="vertical-align:middle;" nowrap>Fundamento Legal</th>
                    </tr>
                </thead>
                <tbody>

                    <?php imprimeListaDosFeriados($sTabela, $var1, $var2, $dia, $mes); ?>

                </tbody>
            </table>
        </div>

        <div class="col-md-12 text-left">
            <h6>
                <b>Observações:</b><br>
                <ul class="list-group">
                    <li class="list-group-item">
                        1)&nbsp;Para manuten&ccedil;&atilde;o dos feriados que tem suas datas alteradas anualmente pelas Prefeituras e Governos Estaduais e Distrital, localize o feriado pela data vigente no ano anterior, clique no link <strong>alterar</strong> e modifique os campos dia e mes informando o fundamento legal da altera&ccedil;&atilde;o na pr&oacute;xima tela que se abrir&aacute;.
                    </li>
                    <li class="list-group-item">
                        2)&nbsp;Os feriados Nacionais ser&atilde;o mantidos pela Administra&ccedil;&atilde;o Central ficando a cargo dos Servi&ccedil;os de Recursos Humanos das Superintend&ecirc;ncias a manuten&ccedil;&atilde;o dos feriados municipais e estaduais de sua jurisdi&ccedil;&atilde;o.
                    </li>
                    <li class="list-group-item">
                        3)&nbsp;A inclus&atilde;o de novos feriados e a exclus&atilde;o de feriados ficar&atilde;o a cargo da Administra&ccedil;&atilde;o Central.<br>Exemplo: O feriado de Corpus Christi em 2010 foi em 03/06 utilizar essa data para localizar o feriado, uma vez localizado em 2011 ser&aacute; em 23/06 preencher os campos dia e mes com esses valores bem como a fundamenta&ccedil;&atilde;o legal.
                    </li>
                </ul>
            </h6>
        </div>

    </div>

</div>

<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



## ################################################ ##
##                                                  ##
##              FUNÇÕES COMPLEMENTARES              ##
##                                                  ##
## ################################################ ##


/**
 * @info Prepara a claúsla WHERE
 * 
 * @param string $var1 Campo a ser pesquisado
 * @param string $var2 Valor para pesquisa
 * @param string $dia  Dia do feriado
 * @param string $mes  Mês do feriado
 * 
 * @return string Claúsula WHERE pronta
 * 
 * Edinalvo Rosa
 */
function preparaWhere($var1, $var2, $dia, $mes)
{    
    $where = "";
    
    switch ($var2)
    {
        case "dia":
            $where .= "(a.dia like '$dia' AND a.mes like '$mes') ";
            break;
        case "descricao":
            $where .= "a.desc like '%$var1%' ";
            break;
        case "lotacao":
        case "estado":
            $where .= "a.lot LIKE '%$var1%' ";
            break;
        //case "estado":
        //    $where .= "d.uf_lota = UPPER('$var1') ";
        //    break;
        case "municipio":
            $oDBase = new DataBase();
            $oDBase->query("SELECT SUBSTR(codigo,1,2) AS estado FROM tabsetor WHERE cidade_lota LIKE '%$var1%' ");
            $oUF   = $oDBase->fetch_object();
            
            if ($_SESSION['sSenhaI'] == 'S')
            {
                $where .= "c.nome LIKE '%$var1%' ";
            }
            else
            {
                $where .= "c.nome LIKE '%$var1%' ";
            }
            break;
        default:
            $where = 'true';
            break;
    }

    return $where;
}

/**
 * @info Seleciona os feriados
 * 
 * @param string $var1 Campo a ser pesquisado
 * @param string $var2 Valor para pesquisa
 * @param string $dia  Dia do feriado
 * @param string $mes  Mês do feriado
 * 
 * @return string Claúsula WHERE pronta
 * 
 * Edinalvo Rosa
 */
function selecionaFeriados($tabela, $var1, $var2, $dia, $mes)
{    
    $where = preparaWhere($var1, $var2, $dia, $mes);

    $pesquisa = "
        SELECT
            a.id,
            a.dia,
            a.mes,
            a.desc,
            a.tipo,
            a.lot,
            IF(IFNULL(c.numero,'')='','',c.nome) AS municipio,
            base_legal
        FROM
            " . $tabela . " AS a
        LEFT JOIN
            cidades AS c ON a.codmun=c.numero
        LEFT JOIN
            tabsetor AS d ON a.codmun=d.codmun
        WHERE
            " . $where . "
        GROUP BY
            a.dia, a.mes, a.tipo, a.codmun
        ORDER BY
            IF(a.tipo='N',1,IF(a.tipo='E',2,3)), a.mes, a.dia "; //ORDER BY a.mes, a.dia
    
    $oDBase = new DataBase();
    $oDBase->query($pesquisa);
    
    return $oDBase;
}



function imprimeListaDosFeriados($sTabela, $var1, $var2, $dia, $mes)
{
    $oDBase = selecionaFeriados($sTabela, $var1, $var2, $dia, $mes);    

    $nRows = $oDBase->num_rows();
    
    if ($nRows == 0)
    {
        ?>
        <tr>
            <td class='text-center' colspan='6'>
                Sem registros para exibir
            </td>
        </tr>
        <?php
    }
    else
    {
        while ($oFeriados = $oDBase->fetch_object())
        {
            $permissao_direcao_central  = (($oFeriados->tipo == 'N' && $_SESSION['sSenhaI'] == 'S') || $_SESSION['sSenhaI'] == 'S');
            $permissao_superintendencia = ($oFeriados->tipo == 'E' && $_SESSION["sTabPrazo"] == "N" && substr($_SESSION["sLotacao"], 2, 6) == "150700" && $_SESSION['sRelGer'] == "S");
            $permissao_gerencia         = ($oFeriados->tipo == 'M' && $oFeriados->uf_cod == substr($_SESSION['sLotacao'], 0, 2) && $_SESSION["sTabPrazo"] == "S");

            $acao = '&nbsp;';
            
            if ($permissao_direcao_central || $permissao_superintendencia || $permissao_gerencia)
            {
                $acao = "<a href='alteraferiado_reg.php?id=" . tratarHTML($oFeriados->id) . "'>Alterar</a>";
            }
           
            ?>
            <tr>
                <th scope="row">
                    <?php
 
                    if ($_SESSION['sRH'] == "S")
                    {
                        echo $acao;
                    }
                    
                    ?>
                </th>
                <td><?= tratarHTML($oFeriados->dia) . '/' . tratarHTML($oFeriados->mes); ?></td>
                <td class="text-left"><?= tratarHTML($oFeriados->desc); ?></td>
                <td>
                    <?= ($oFeriados->tipo == 'N' ? 'Nacional' : ($oFeriados->tipo == 'E' ? 'Estadual' : ($oFeriados->tipo == 'M' ? 'Municipal' : '---------'))); ?>
                </td>
                <td><?= ($oFeriados->lot == '' ? '&nbsp;' : tratarHTML($oFeriados->lot)); ?></td>
                <!-- <td><?= ($oFeriados->uf == '' ? '--' : tratarHTML($oFeriados->uf)); ?>&nbsp;</td> -->
                <td><?= ($oFeriados->municipio == '' ? '-' : tratarHTML($oFeriados->municipio)); ?></td>
                <td><?= ($oFeriados->base_legal == '' ? '-' : tratarHTML($oFeriados->base_legal)); ?></td>
            </tr>
            <?php
           
        } // fim do while
    }
}
