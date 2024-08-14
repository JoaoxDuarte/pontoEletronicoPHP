<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_form.telas.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// pega o nome do arquivo origem
$pagina_de_origem = "liberupag.php";

// parametros passados por formulario
$siape = anti_injection($_REQUEST['siape']);

// valores registrado em sessao
// upag do cadastrador
$upag = $_SESSION['upag'];


if (empty($siape) && isset($_SESSION['sMov_Matricula_Siape']))
{
    $siape = $_SESSION['sMov_Matricula_Siape'];
}

if ((isset($_SESSION['sMov_Entra_Unidade']) && !empty($_SESSION['sMov_Entra_Unidade'])) ||
    (isset($_SESSION['sMov_Nova_Unidade'])  && !empty($_SESSION['sMov_Nova_Unidade'])))
{
    $dtingn    = ($_SESSION['sMov_Entra_Unidade'] == '0000-00-00' ? '' : databarra($_SESSION['sMov_Entra_Unidade']));
    $novalota  = $_SESSION['sMov_Nova_Unidade'];
}

// pesquisa servidor
$oDBase    = selecionaServidoresGlobal($siape);
$oServidor = $oDBase->fetch_object();
$nRows     = $oDBase->num_rows();



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCSS("css/select2.min.css");
$oForm->setCSS("css/select2-bootstrap.css");
$oForm->setCSS("js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css");

$oForm->setJS( "js/select2.full.js");
$oForm->setJS( 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
$oForm->setJS( 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js');

$oForm->setJS( "liberupag1.js" );

$oForm->setSubTitulo("Liberação de Servidor para outra UPAG");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// testa se foi passada a matrícula siape
// senão verifica se há dados na seção sobre a matrícula do servidor
$validar   = new valida();
$validar->setDestino( $pagina_de_origem );
$validar->setExibeMensagem( false );

$validar->siape($siape);

if ($validar->getMensagem() != "") 
{
    // Há erro na matrícula informada
    $validar->exibeMensagem();
    die();
}

if ($nRows > 0 && $oServidor->upag != $upag) 
{
    $validar->setMensagem("Não é permitido liberar servidor de outra UPAG!");
    $validar->exibeMensagem();
    die();
}

if ($nRows == 0)
{
    $validar->setMensagem("Servidor não está ativo ou inexistente!");
    $validar->exibeMensagem();
    die();
}

if ($oServidor->cod_sitcad == "66")
{
    $validar->setMensagem("Não é permitido liberar <b>Estagiário</b> para outra UPAG!");
    $validar->exibeMensagem();
    die();
}

if (ocupanteFuncaoDelegacao($siape))
{
    // ocupa função ou está com delegação
    $validar->exibeMensagem();
    die();
}

if (verificaSeJaLiberado($siape))
{
    // verifica se servidor já foi lierado antes
    $validar->exibeMensagem();
    die();
}


// salvamos a matrícula do servidor
// para que o teste de erro de upag
// possa funcionar corretamente caso aconteça algum
// problema na movimentação e retorne para este script
$_SESSION['sMov_Matricula_Siape'] = $siape;


?>
<form id="form1" name='form' method='post'>
    <input type="hidden" id="modo" name="modo" value='1'>
    <input type="hidden" id="atuallota" name="atuallota" value='<?= tratarHTML($oServidor->cod_lot); ?>'>

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="47" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nome:</label>
                <input type="text" id="nome" name="nome" value='<?= tratarHTML($oServidor->nome_serv); ?>' size="60" maxlength="60" class='form-control text-uppercase' readonly>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Mat.Siape:</label>
                <input type="text" id="siape" name="siape" value='<?= tratarHTML(removeOrgaoMatricula($oServidor->mat_siape)); ?>' size="7" maxlength="7" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
        </tr>
        <tr>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>UPAG atual:</label>
                <?php selectUpagOrigem( $oServidor->upag ); ?>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt_inglot' style='padding:0px;'>
                    <label class='control-label'>Data de Início</label>
                    <div class="input-group date datepicker text-nwrap">
                        <input type="text" id="dt_ing_lot" name="dt_ing_lot" value='<?= tratarHTML($oServidor->dt_ing_lot); ?>' size="10" maxlength="10" style="width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nova UPAG:</label>
                <?php selectUpagDestino( $oServidor->upag ); ?>
            </td>
            <td height="40" nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;'>
                    <label class='control-label'>Data de Ingresso</label>
                    <div class="input-group date">
                        <input type="text" id="dtingn" name="dtingn" value='<?= tratarHTML($dtingn); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnKeyPress="formatar(this, '##/##/####')" class='form-control'><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="form-group">
        <div class="col-md-12">
            <div class="col-md-2 col-md-offset-4">
                <button type="submit" id="btn-continuar" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-ok"></span> Gravar
                </button>
            </div>
            <div class="col-md-2">
                <a class="btn btn-danger btn-block" href="javascript:window.location.replace('<?= $pagina_de_origem; ?>');">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>

    <div>
        <div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>
            <fieldset style='border:1px solid white;text-align:left;'>
                <legend style='font-size:12px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>
                <p style='padding:1px;margin:0px;'>
                    <b>Nova Lotação&nbsp;:&nbsp;</b>Selecione a unidade de destino do servidor/estagiário;
                </p>
                <p style='padding:1px;margin:0px;'>
                    <b>Data de Ingresso&nbsp;:&nbsp;</b>Indique a data de ingresso na nova lotação (unidade). A data não pode ser menor que a data de início na lotação atual;
                </p>
            </fieldset>
        </div>
    </div>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


/* *********************************************
 *                                             *
 *           FUNÇÕES COMPLEMENETARES           *
 *                                             *
 ********************************************* */

/*
 * @Function  selecionaServidor
 * @info      Realiza a seleção do servidor
 *
 * @param   string  $siape  Matrícula siape do servidor
 * @return  result O resultado da seleção
 * @usage   selecionaServidor('9999999');
 * @author  Edinalvo Rosa
 *
 * @dependence : class DataBase
 */
function selecionaServidoresGlobal($siape) 
{
    global $pagina_de_origem;
    
    // instancia a class DataBase
    // instancia a class DataBase
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Falha na liberação do servidor para outra UPAG.");
    $oDBase->setDestino($pagina_de_origem); // se há erro redireciona

    $oDBase->query('
    SELECT
        cad.mat_siape, cad.nome_serv, cad.cpf, cad.cod_lot,
        cad.cod_loc, cad.cod_sitcad,
        DATE_FORMAT(cad.dt_ing_lot,"%d/%m/%Y") AS dt_ing_lot,
        DATE_FORMAT(cad.dt_ing_loc,"%d/%m/%Y") AS dt_ing_loc, cad.area,
        und.upag, und.descricao, cad.excluido, cad.chefia
    FROM
        servativ AS cad
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    WHERE
        cad.mat_siape LIKE :siape
        AND cad.cod_sitcad NOT IN ("02","15")
    ',
        array(
            array(':siape', '%'.$siape, PDO::PARAM_STR),
        ));

    return $oDBase;
}

/*
 * @Function  verificaDelegacao
 * @info      Verifica se ha delegacao
 *
 * @param   string  $siape  Matrícula siape do servidor
 * @return  int     Número de registros selecionados
 * @usage   verificaDelegacao('9999999');
 * @author  Edinalvo Rosa
 *                                                              |
 * @dependence : class DataBase
 */
function verificaDelegacao($siape) 
{
    global $pagina_de_origem;
    
    $siape = getNovaMatriculaBySiape($siape);

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Falha na liberação do servidor para outra UPAG.");
    $oDBase->setDestino($pagina_de_origem); // se há erro redireciona

    // verifica se ha delegacao
    $oDBase->query("
    SELECT
        a.siape, b.nome_serv, b.cod_lot
    FROM
        usuarios AS a
    LEFT JOIN
        servativ AS b ON a.siape=b.mat_siape
    WHERE
        a.siape = :siape
        AND (DATE_FORMAT(NOW(),'%Y-%m-%d') >= IF(a.datapt='0000-00-00','9999-99-99',a.datapt)
        AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= IF(a.dtfim='0000-00-00','9999-99-99',a.dtfim))
    ",
    array(
        array( ':siape', $siape, PDO::PARAM_STR ),
    ));

    return $oDBase->num_rows();
}

/*
 * @Function  ocupanteFuncaoDelegacao
 * @info      Verifica se ocupa função e ha delegacao
 *
 * @param   string  $siape  Matrícula siape do servidor
 * @return  boolean  Número de registros selecionados
 * @usage   verificaDelegacao('9999999');
 * @author  Edinalvo Rosa
 *
 * @dependence : class DataBase
 */
function ocupanteFuncaoDelegacao($siape)
{
    global $oServidor, $validar;
    
    // verifica se ha delegacao
    $nDelegado = verificaDelegacao($siape);

    // pesquisa chefia
    $dados_funcao = ocupante_de_funcao($siape);

    // testa se ocupa função, substituto ou titular,
    // e se tem delegação de atribuição no SISREF
    if ($dados_funcao->funcao != '' || $nDelegado > 0 || $oServidor->excluido == 'S')
    {
        $mensagem_funcao = '';
        if ($dados_funcao->funcao != '')
        {
            $strlen_unidade = strlen($dados_funcao->unidade . ' - ' . $dados_funcao->funcao);
            $mensagem_funcao = 'Servidor(a) ' . nome_sobrenome($dados_funcao->nome)
                . ' (' . $dados_funcao->siape
                . ') não pode ser liberado(a) para outra UPAG '
                . 'por ser ocupante da função ' . $dados_funcao->funcao . ' ('
                . $dados_funcao->unidade . ') - ' . $dados_funcao->ocupacao . '.';
        }

        $mensagem  = ($dados_funcao->funcao != '' ? $mensagem_funcao . "\\n" : "");
        $mensagem .= ($nDelegado > 0 ? "Servidor possui Delegação de atribuição registrada!\\n" : "");
        $mensagem .= ($oServidor->excluido == 'S' ? "Servidor com exclusão registrada!\\n" : "");
        $mensagem .= "Liberação não pode ser realizada!";

        $validar->setMensagem( $mensagem );
        
        return true;
    }
    
    return false;
}

/*
 * @Function  verificaSeJaLiberado
 * @info      Verifica se o servidor ja foi liberado
 *
 * @param   string  $siape  Matrícula siape do servidor
 * @return  boolean Número de registros selecionados
 * @usage   verificaSeJaLiberado('9999999');
 * @author  Edinalvo Rosa
 *
 * @dependence : class DataBase
 */
function verificaSeJaLiberado($siape)
{
    global $oServidor, $validar, $pagina_de_origem;

    $siape = getNovaMatriculaBySiape($siape);

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Falha na liberação do servidor para outra UPAG.");
    $oDBase->setDestino($pagina_de_origem); // se há erro redireciona

    $oDBase->query("
    SELECT
        siape
    FROM
        liberupag
    WHERE
        siape = :siape
        AND lotor = :lotacao
        AND dtrecebe = '0000-00-00'
    ",
    array(
        array( ':siape',   $siape,              PDO::PARAM_STR ),
        array( ':lotacao', $oServidor->cod_lot, PDO::PARAM_STR ),
    ));

    if ($oDBase->num_rows() > 0)
    {
        $validar->setMensagem( "Servidor já liberado!<br>Para alterar a liberação, cancele a atual e libere novamente!" );
        
        return true;
    }
    
    return false;
}

/*
 * @Function  selectUpagAtual
 * @info      Seleciona a UPAG de origem
 *
 * @param   string  $upag  Código da UPAH de origem
 * @return  void
 * @usage   selectUpagAtual('9999900004236');
 * @author  Edinalvo Rosa
 */
function selectUpagOrigem($upag)
{                
    ?>
    <select id="lota" name="lota" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
        <?php
        
        // tabela de cargo
        $sql = "SELECT codigo, descricao FROM tabsetor WHERE upag = '".$upag."' OR codigo IN ('00000000000000','00000000') ORDER BY codigo";
        print montaSelect($upag, $sql, $tamdescr='', $imprimir=false);
       
        ?>
    </select>
    <?php
}

/*
 * @Function  selectUpagDestino
 * @info      Seleciona a UPAG de origem
 *
 * @param   string  $upag  Código da UPAH de origem
 * @return  void
 * @usage   selectUpagDestino('9999900004236');
 * @author  Edinalvo Rosa
 */
function selectUpagDestino($upag)
{
    ?>
    <select id="novalota" name="novalota" size="1" onkeyup="javascript:ve(this.value);" class="form-control select2-single">
        <?php
                    
        // tabela de cargo
        $sql = "SELECT codigo, descricao FROM tabsetor WHERE (codigo <> '".$upag."' AND codigo = upag) OR codigo IN ('00000000000000','00000000') GROUP BY upag";
        print montaSelect($novalota, $sql, $tamdescr='', $imprimir=false);
                    
        ?>
    </select>
    <?php
}
