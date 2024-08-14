<?php

include_once("config.php");

// verifica se o usuário tem permissão para acessar este módulo
verifica_permissao("administracao_central");

// verifica se o usuário tem permissão para acessar este módulo
// verifica_permissao('gravar_frequencia'); // TODO

// Busca os dados da tabela de ponto facultativo
$oDBase = new DataBase('PDO');

$queryPontoFacultativo = "SELECT * FROM feriados_ponto_facultativo";

if(isset($_REQUEST['escolha']) && $_REQUEST['escolha'] == 'dia'){
    $arrData = explode('/', $_REQUEST['chave']);
    $dia = isset($arrData[0]) ? $arrData[0] : 'null';
    $mes = isset($arrData[1]) ? $arrData[1] : 'null';

    $queryPontoFacultativo .= " WHERE dia = '".$dia."' AND mes = '".$mes."'";

}else if(isset($_REQUEST['escolha']) && $_REQUEST['escolha'] == 'descricao'){
    $queryPontoFacultativo .= " WHERE `desc` LIKE '%".$_REQUEST['chave']."%' OR descricao LIKE '%".$_REQUEST['chave']."%' ";
}


$oDBase->query($queryPontoFacultativo);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJS( 'js/jquery.blockUI.js?v2.38' );
$oForm->setJS( 'js/jquery.bgiframe.js' );
$oForm->setJS( 'js/plugins/jquery.dlg.min.js' );
$oForm->setJS( 'js/plugins/jquery.easing.js' );

$oForm->setSubTitulo( "Pesquisa Tabela de Ponto Facultativo" );

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

        $('#form1').attr('action', "ponto_facultativo.php");
        $('#form1').submit();
    }
}

</script>


<div class="container" id="form-comparecimento">
    <div class="row margin-10">

        <div class="col-md-12 text-left">
            <h6>
                Para pesquisar por data digite no formato dd/mm.<br>
                Para pesquisar por descri&ccedil;&atilde;o digite parte do nome do ponto facultativo ex. JORGE para pesquisar dia de S&atilde;o Jorge.<br>
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
                                <div class="col-md-5">
                                    <div class="radio">
                                        <label for="radios-0">
                                            <input type="radio" name="escolha" id="escolha" value="dia" <?= ($_REQUEST["escolha"] == 'dia' || $_REQUEST["escolha"] == '' ? 'checked' : ''); ?> onclick="$('#chave').focus()">
                                            Por Data
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="radio">
                                        <label for="radios-1">
                                            <input type="radio" name="escolha" id="escolha" value="descricao" <?= ($_REQUEST["escolha"] == 'descricao' ? 'checked' : ''); ?> onclick="$('#chave').focus()">
                                            Por Descrição
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
                        <span class="glphicon-search"></span> Pesquisar
                    </a>
                </div>
            </form>
        </div>

        <div class="row margin-10">
            <input type="button" class="btn btn-default btnyphicon gly_adicionar" style="padding-left:30px;padding-right:30px; margin-bottom: 4px;" value="Adicionar" onClick="document.location.href='ponto_facultativo_cadastrar.php'" />
            <table class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th class="text-center" style="vertical-align:middle;">Ações</th>
                        <th class="text-center" style="vertical-align:middle;">Dia</th>
                        <th class="text-center" style="vertical-align:middle;">Mês</th>
                        <th class="text-center" style="vertical-align:middle;">Título</th>
                        <th class="text-center" style="vertical-align:middle;">Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($pontoFacultativo = $oDBase->fetch_array()){ ?>
                        <tr>
                            <td><a href="ponto_facultativo_alterar.php?id=<?php echo $pontoFacultativo['id'] ?>">Alterar</a></td>
                            <td><?php echo $pontoFacultativo['dia']; ?></td>
                            <td><?php echo $pontoFacultativo['mes']; ?></td>
                            <td><?php echo $pontoFacultativo['desc']; ?></td>
                            <td><?php echo $pontoFacultativo['descricao']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
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
