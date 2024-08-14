<?php
include_once("config.php");

verifica_permissao('sRH e sTabServidor');

// recebe os parametros passados
$matricula = anti_injection($_REQUEST['matricula']);
$num_func  = anti_injection($_REQUEST['funcao']);
$exclusao  = anti_injection($_REQUEST['exclusao']);

// recupera dados gravados em sessao
if (isset($_SESSION['sGravaFuncaoSiape']) && $_SESSION['sGravaFuncaoSiape'] != NULL)
{
    $matricula = $_SESSION['sGravaFuncaoSiape'];
    $num_func  = $_SESSION['sGravaFuncaoNumFuncao'];
    $exclusao  = $_SESSION['sGravaFuncaoExclusao'];

    $Nnum3      = $_SESSION['sGravaFuncaoNnum3'];
    $Ndata3     = databarra($_SESSION['sGravaFuncaoNdata3']);
    $publicacao = $_SESSION['sGravaFuncaopublicacao'];
    $Nnum4      = $_SESSION['sGravaFuncaoNnum4'];
    $Ndata4     = databarra($_SESSION['sGravaFuncaoNdata4']);
    $fim        = databarra($_SESSION['sGravaFuncaofim']);
}
else
{
    $_SESSION['sGravaFuncaoSiape']     = $matricula;
    $_SESSION['sGravaFuncaoNumFuncao'] = $num_func;
    $_SESSION['sGravaFuncaoExclusao']  = $exclusao;
}


// grava em sessao dados do script atual
//$_SESSION['sHOrigem_1'] = "excfuncserv.php";
//$_SESSION['sHOrigem_2'] = 'excfuncserv2.php'; //$_SERVER['REQUEST_URI']; // historico_regfreq3.php
$_SESSION['sHOrigem_3'] = 'excfuncserv3.php';
$_SESSION['sHOrigem_4'] = '';

// tipo de situacao de ocupacao
$sit2 = array(
    'T' => "TITULAR",
    'S' => "SUBSTITUTO",
    'R' => "INTERINO",
    /*'E' => "EVENTUAL",*/
);


## ############################### ##
##                                 ##
##      VALIDA DADOS ENVIADOS      ##
##                                 ##
## ############################### ##


// class valida
$validar = new valida();
$validar->setDestino( $_SESSION['sHOrigem_1'] );
$validar->setExibeMensagem( false );

## MATRÍCULA SIAPE
$validar->siape( $matricula );

## FUNÇÃO
if ($funcao == '00000')
{
    $validar->setMensagem('- Selecione a Função!\\n');
}

// Exibe mensagem(ns) de erro, se houver
$validar->exibeMensagem();


if ($exclusao == "S")
{
    header("Location: grava_inclui_funcserv.php?modo=8&matricula=$matricula&funcao=$funcao");
    exit();
}


## ############################### ##
##                                 ##
##     DADOS PARA O FORMULÁRIO     ##
##                                 ##
## ############################### ##


// Pesquisa no cadastro
// Se não achar, exibe msg erro
// e volta a página anterior
$oDBase = verificaOcupaFuncao( $matricula, $num_func );

$oOcupante   = $oDBase->fetch_object();
$nome        = $oOcupante->nome_serv;
$inicio      = $oOcupante->dt_inicio;
$Nnum1       = $oOcupante->num_doc1;
$Ndata1      = $oOcupante->dt_doc1;
$Nnum2       = $oOcupante->num_doc2;
$Ndata2      = $oOcupante->dt_doc2;
$publicacao2 = $oOcupante->cod_doc2;
$respon      = $oOcupante->resp_lot;
$funcao      = $oOcupante->desc_func;
$lot         = $oOcupante->cod_lot;
$ocupacao    = $oOcupante->sit_ocup;

$sigla       = $oOcupante->cod_funcao; // sigla da função
$idsubs      = $oOcupante->indsubs;    // indica se pode ter substituto para ocupante da função

$uorg        = $oOcupante->cod_uorg;
$area        = $oOcupante->area;



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJSDatePicker();
$oForm->setJS("excfuncserv3.js");

$oForm->setOnLoad("javascript: if($('#Nnum3')) { $('#Nnum3').focus() };");

$oForm->setSubTitulo("REGISTRO DE VACÂNCIA DE FUN&Ccedil;&Atilde;O");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<form method="POST" id="form1" name="form1">

    <input type='hidden' id='modo'        name='modo'        value='6'>
    <input type="hidden" id="num_funcao"  name="num_funcao"  value="<?= tratarHTML($num_func); ?>">
    <input type='hidden' id='ocupacao'    name='ocupacao'    value='<?= tratarHTML($ocupacao); ?>'>
    <input type='hidden' id='lota'        name='lota'        value='<?= $lot; ?>'>

    <table class='table table-condensed text-center'>
        <tr>
            <td colspan="1" class='text-left no-wrap' style='border-width:0px;width:90px;'>
                <label class='control-label' style='padding-top:5px;'>Matr&iacute;cula:</label>
                <input type="text" name="matricula" id="matricula" value="<?= tratarHTML($matricula); ?>" size="7" maxlength="7" class='form-control' readonly>
            </td>
            <td colspan='4' class='text-left no-wrap' style='border-width:0px;padding-left:10px;'>
                <label class='control-label' style='padding-top:5px;'>Nome:</label>
                <input type="text" id="nome" name="nome" value='<?= tratarHTML($nome); ?>' size="50" maxlength="50" class='form-control' readonly>
            </td>
        </tr>
        <tr>
            <td colspan="4" class='text-left no-wrap' style='border-width:0px;'>
                <label class='control-label' style='padding-top:5px;'>Função:</label>
                <input type="text" id="func" name="func" value="<?= tratarHTML($num_func) . ' - ' . tratarHTML($funcao); ?>" size="60" maxlenght="60" class='form-control' readonly>
            </td>
            <td colspan="1" class='text-left' style='border-width:0px;width:350px;'>
                <label class='control-label' style='padding-top:5px;'>Situa&ccedil;&atilde;o ocupante:</label>
                <input type="text" id="situacao" name="situacao" value="<?= tratarHTML($sit2[$ocupacao]); ?>" size="13" maxlength="13" class="form-control" readonly>
            </td>
        </tr>
        <tr>
            <td colspan="5" class='text-left no-wrap' style='border-top:0px;'>
                <table class='table table-condensed text-center' style='border-top:0px;'>
                    <tr>
                        <td nowrap style='border-width:0px;'>
                            <div class="col-md-2" style='padding:0px;border-width:0px;'>
                                <label class='control-label nowrap'>In&iacute;cio de exerc&iacute;cio:</label>
                                <div class="input-group">
                                    <input type="text" id="inicio" name="inicio" value='<?= tratarHTML($inicio); ?>' size="10" maxlength="10" style="width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                                </div>
                            </div>
                        </td>
                        <td class='text-left' style='border-width:0px;'>
                            <label class='control-label'>Portaria Número:</label>
                            <input type="text" name="Nnum1" id="Nnum1" value="<?= tratarHTML($Nnum1); ?>" size="9" maxlength='9' class="form-control" readonly>
                        </td>
                        <td class='text-left' style='border-width:0px;'>
                            <div class="col-md-1" id='dt-container' style='padding:0px;border-width:0px;'>
                                <label class='control-label'>Portaria&nbsp;Data:</label>
                                <div class="input-group">
                                    <input type="text" id="Ndata1" name="Ndata1" value='<?= tratarHTML($Ndata1); ?>' size="10" maxlength="10" style="width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                                </div>
                            </div>
                        </td>
                        <td width="200px" class='text-left' style='border-width:0px;'>
                            <label class='control-label'>Publicação:</label>
                            <select name="publicacao2" id="publicacao2" class="form-control select2-single" disabled readonly>
                                <option value="00"<?= ($publicacao2=='00'?' selected':''); ?> >SELECIONE</option>
                                <option value="DO"<?= ($publicacao2=='DO'?' selected':''); ?> >Diário&nbsp;Oficial&nbsp;da&nbsp;União</option>
                                <option value="BSL"<?= ($publicacao2=='BSL'?' selected':''); ?> >Boletim&nbsp;de&nbsp;Serviço&nbsp;Local</option>
                            </select>
                        </td>
                        <td class='text-left' width="100px" style='border-width:0px;'>
                            <label class='control-label'>Número:</label>
                            <input type="text" name="Nnum2" id="Nnum2" value="<?= tratarHTML($Nnum2); ?>" size="9" maxlength='9' class='form-control' readonly>
                        </td>
                        <td class='text-left' width="100px" style='border-width:0px;'>
                            <div class="col-md-1" id='dt-container' style='padding:0px;border-width:0px;'>
                                <label class='control-label'>Data:</label>
                                <div class="input-group">
                                    <input type="text" id="Ndata2" name="Ndata2" value='<?= tratarHTML($Ndata2); ?>' size="10" maxlength="10" style="width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' readonly><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan='5' style='border-top:0px;'>
                <table class='table table-condensed text-center'>
                    <tr>
                        <td colspan="4" style="text-align:left;vertical-align:middle;color:#D40000;padding:5px 0px 5px 0px;">
                            <font size="2" face="Tahoma">&nbsp;<strong>INFORME OS DADOS A SEGUIR</strong></font>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='4'>
                            <table class='table table-condensed table-bordered text-center'>
                                <tr>
                                    <th colspan="5" class="text-center">Portaria de Exoneração/Dispensa</th>
                                    <th width="15%" class="text-center">Fim de exerc&iacute;cio</th>
                                </tr>
                                <tr height='45'>
                                    <td style='border-width:0px;'>
                                        <label class='control-label'>Portaria Número:</label>
                                        <input type="text" name="Nnum3" id="Nnum3" value="<?= tratarHTML($Nnum3); ?>" size="9" maxlength='9' class="form-control">
                                    </td>
                                    <td style='border-width:0px;'>
                                        <div class="col-md-1" id='dt-container' style='padding:0px;border-width:0px;'>
                                            <label class='control-label'>Portaria&nbsp;Data:</label>
                                            <div class="input-group date">
                                                <input type="text" id="Ndata3" name="Ndata3" value='<?= tratarHTML($Ndata3); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control'><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td width="200px" class='text-left' style='border-width:0px;'>
                                        <label class='control-label'>Publicação:</label>
                                        <select name="publicacao" id="publicacao" class="form-control select2-single">
                                            <option value="00"  <?= ($publicacao == '00' ? 'selected' : ''); ?> >SELECIONE</option>
                                            <option value="DO"  <?= ($publicacao == 'DO' ? 'selected' : ''); ?> >Diário&nbsp;Oficial&nbsp;da&nbsp;União</option>
                                            <option value="BSL" <?= ($publicacao == 'BSL' ? 'selected' : ''); ?> >Boletim&nbsp;de&nbsp;Serviço&nbsp;Local</option>
                                        </select>
                                    </td>
                                    <td style='border-width:0px;'>
                                        <label class='control-label'>Número:</label>
                                        <input type="text" name="Nnum4" id="Nnum4" value="<?= tratarHTML($Nnum4); ?>" size="9" maxlength='9' class='form-control'>
                                    </td>
                                    <td style='border-width:0px;'>
                                        <div class="col-md-1" id='dt-container' style='padding:0px;border-width:0px;'>
                                            <label class='control-label'>Data:</label>
                                            <div class="input-group date">
                                                <input type="text" id="Ndata4" name="Ndata4" value='<?= tratarHTML($Ndata4); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control'><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td width="15%">
                                        <div class="col-md-2" id='dt-container' style='padding:0px;border-width:0px;'>
                                            <label class='control-label'>Data:</label>
                                            <div class="input-group date">
                                                <input type="text" id="fim" name="fim" value='<?= tratarHTML($fim); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control'><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
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
                <a class="btn btn-danger btn-block" href="javascript:window.location.replace('<?= tratarHTML($_SESSION['sHOrigem_2']); ?>');">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>

</form>
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

function verificaOcupaFuncao( $matricula=null, $funcao=null )
{
    global $sit2;

    $matricula = getNovaMatriculaBySiape($matricula);
    
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $_SESSION['sHOrigem_1'] );
    $oDBase->setMensagem("Problema no acesso a Funções!");

    // nome do servidor
    $oDBase->query("
    SELECT
        b.nome_serv, a.num_funcao, a.sit_ocup, c.desc_func, c.cod_lot,
        c.cod_funcao, a.num_doc1, a.num_doc2, a.cod_doc2, a.resp_lot, c.indsubs,
        IF(a.dt_inicio='0000-00-00','',DATE_FORMAT(a.dt_inicio,'%d/%m/%Y')) AS dt_inicio,
        IF(a.dt_fim='0000-00-00','',DATE_FORMAT(a.dt_fim,'%d/%m/%Y')) AS dt_fim,
        IF(a.dt_doc1='0000-00-00','',DATE_FORMAT(a.dt_doc1,'%d/%m/%Y')) AS dt_doc1,
        IF(a.dt_doc2='0000-00-00','',DATE_FORMAT(a.dt_doc2,'%d/%m/%Y')) AS dt_doc2
    FROM
        ocupantes AS a
    LEFT JOIN
        servativ AS b ON a.mat_siape = b.mat_siape
    LEFT JOIN
        tabfunc AS c ON a.num_funcao = c.num_funcao
    LEFT JOIN
        tabsetor AS d ON c.cod_lot = d.codigo AND d.ativo = 'S'
    WHERE
        a.mat_siape = :siape
        AND a.num_funcao = :funcao
        AND a.dt_fim = '0000-00-00'
    ",
    array(
        array( ':siape',  $matricula, PDO::PARAM_STR ),
        array( ':funcao', $funcao,    PDO::PARAM_STR ),
    ));

    if ($oDBase->num_rows() == 0)
    {
        $oOcupante = $oDBase->fetch_object();
        mensagem(
            "O servidor " . $oOcupante->nome_serv . " não ocupa esta função:\\n"
            . $oOcupante->desc_func . ' - ' . $oOcupante->num_funcao
            . '(' . $sit2[$oOcupante->sit_ocup] . ')'
            , $destino
        );
    }

    return $oDBase;
}
