<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH ou sTabServidor');

// valores enviados via formulario
$matricula = anti_injection($_REQUEST['siape']);

$destino = 'cadastro_exclusao.php';

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCSS("css/select2.min.css");
$oForm->setCSS("css/select2-bootstrap.css");
$oForm->setCSS("js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css");

$oForm->setJS( "js/select2.full.js");
$oForm->setJS( 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
$oForm->setJS( 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js');

$oForm->setJS( "cadastro_exclusao_formulario.js?v.1.0" );

$oForm->setSubTitulo("Exclus&atilde;o de Servidores/Estagi&aacute;rios");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// testa se foi passada a matrícula siape
// senão verifica se há dados na seção sobre a matrícula do servidor
if (empty($matricula) && empty($_SESSION['sExc_Matricula_Siape']))
{
    mensagem("Obrigatório informar a matrícula do Servidor!", $destino, 1);
}

if (empty($matricula))
{
    $matricula = $_SESSION['sExc_Matricula_Siape'];
}
else
{
    // salvamos a matrícula do servidor
    // para que o teste de erro de upag
    // possa funcionar corretamente caso aconteça algum
    // problema na exclusão e retorne para este script
    $_SESSION['sExc_Matricula_Siape'] = $matricula;
}


// instancia o banco de dados
$oDBase = new DataBase('PDO');
$novamatricula = getNovaMatriculaBySiape($matricula);

// pesquisa e obtem dados da exclusão, se houver
$oDBase->query("
SELECT
    cad.mat_dtp, cad.mat_siape, cad.nome_serv, cad.mat_siapecad,
    cad.cod_sitcad, cad.cod_lot, exc.cod_ocorr, oco.desc_ocorr,
    DATE_FORMAT(exc.dt_ocorr, '%d/%m/%Y') AS dt_ocorr, exc.tp_doc, dipl.descdipl, exc.num_doc,
    DATE_FORMAT(exc.dt_doc, '%d/%m/%Y')   AS dt_doc, exc.cartorio,
    DATE_FORMAT(exc.dt_obito, '%d/%m/%Y') AS dt_obito, exc.reg_obito, exc.fol_obito, exc.liv_obito, exc.cod_orgao,
    DATE_FORMAT(exc.dt_exped,'%d/%m/%Y')  AS dt_exped, IFNULL(und.upag,'') AS upag, cad.excluido
FROM
    servativ AS cad
LEFT JOIN
    exclus AS exc ON cad.mat_siape = exc.siape
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
LEFT JOIN
    tabocorr AS oco ON exc.cod_ocorr = oco.cod_ocorr
LEFT JOIN
    tabdipl AS dipl ON exc.tp_doc = dipl.coddipl
WHERE
    cad.mat_siape = :siape
",
array(
    array( ':siape', $novamatricula, PDO::PARAM_STR ),
));

$nRows     = $oDBase->num_rows();
$oCadastro = $oDBase->fetch_object(); // informações do servidor

if ($nRows == 0)
{
    mensagem("Servidor não cadastrado!", $destino, 1);
}
elseif ($oCadastro->excluido == 'S')
{
    mensagem("Servidor já encontra-se excluído!\\n"
        . $oCadastro->nome_serv . ", excluído em " . $oCadastro->dt_ocorr . "\\n"
        . "Motivo: ".$oCadastro->desc_ocorr
    , $destino);
}

// dados do servidor
$tSiape       = $oCadastro->mat_siape;
$wnome        = $oCadastro->nome_serv;
$mat_siapecad = $oCadastro->mat_siapecad;
$sitcad       = $oCadastro->cod_sitcad;
$wlota        = $oCadastro->cod_lot;
$codoc        = $oCadastro->cod_ocorr;
$codoc_desc   = $oCadastro->desc_ocorr;
$dtexcl       = $oCadastro->dt_ocorr;
$tpdoc        = $oCadastro->tp_doc;
$tpdoc_desc   = $oCadastro->descdipl;
$numdoc       = $oCadastro->num_doc;
$dtdoc        = $oCadastro->dt_doc;
$cartorio     = $oCadastro->cartorio;
$dtobito      = $oCadastro->dt_obito;
$regobito     = $oCadastro->reg_obito;
$folobito     = $oCadastro->fol_obito;
$livobito     = $oCadastro->liv_obito;
$codorg       = $oCadastro->cod_orgao;
$dtexp        = $oCadastro->dt_exped;

// unidade pagadora do servidor e do usuário
$upag_do_servidor = $oCadastro->upag;
$upag_do_usuario  = $_SESSION['upag'];

$_SESSION['cad_upg'] = $upag_do_servidor;

// carrega variaveis com valores de sessao
if ((isset($_SESSION['cad_wnome']) && !empty($_SESSION['cad_wnome']))
    || (isset($_SESSION['cad_tSiape']) && !empty($_SESSION['cad_tSiape'])))
{
    // limpa sessão
    $tSiape   = $_SESSION['cad_tSiape'];
    $codocor  = $_SESSION['cad_codocor'];
    $wnome    = $_SESSION['cad_wnome'];
    $Dataocor = $_SESSION['cad_Dataocor'];
    $upg      = $_SESSION['cad_upg'];
}

// verifica se a upag do servidor eh a mesma do usuario
if ($upag_do_servidor != $upag_do_usuario)
{
    mensagem("Não é permitido excluir servidor de outra UPAG!", $destino, 1);
}


if (file_exists(_DIR_FOTO_ . $matricula . ".jpg"))
{
    $sFoto = 1; // O arquivo existe
    $anonimo = _DIR_FOTO_ . $matricula . ".jpg";
}
else
{
    $sFoto = 2; // O arquivo não existe
    $anonimo = _DIR_FOTO_ . "anonimo.jpg";
}


?>
<form id="form1" name='form' method='post'>
    <input type="hidden" name="sitcad" value='<?= tratarHTML($sitcad); ?>'>

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td height="47" colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Nome:</label>
                <input type="text" id="wnome" name="wnome" value='<?= tratarHTML($wnome); ?>' size="60" maxlength="60" class='form-control text-uppercase' readonly>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Mat.Siape:</label>
                <input type="text" id="tSiape" name="tSiape" value='<?= tratarHTML(removeOrgaoMatricula( $tSiape )); ?>' size="7" maxlength="7" onkeyup="javascript:ve(this.value);" class='form-control' readonly>
            </td>
            <td rowspan="2" nowrap style="text-align:center;vertical-align:middle;padding:0px 5px 5px 5px;border-width:0px;">
                <p class='p2'><img src="<?= tratarHTML(retornaFoto($tSiape)); ?>" width="82" height="110"></p>
                <br>
            </td>
        </tr>
        <tr>
            <td colspan="3" nowrap class='text-left' style='border-width:0px;'>
                <label class='control-label'>Motivo da exclus&atilde;o:</label>
                <select size="1" id="codocor" name="codocor" class='form-control select2-single'>
                    <?= listaCodOcorrenciaDeExclusao(tratarHTML($codoc)); ?>
                </select>
            </td>
            <td nowrap class='text-left' style='border-width:0px;'>
                <div class="col-md-2" id='dt-container' style='padding:0px;border-width:0px;'>
                    <label class='control-label'>Data:</label>
                    <div class="input-group date">
                        <input type="text" id="Dataocor" name="Dataocor" value='<?= tratarHTML($dtexcl); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    </div>
                </div>
            </td>

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
                <a class="btn btn-danger btn-block" href="javascript:window.location.replace('cadastro_exclusao.php');">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>

    <div align="left">
        <span style='height:0.2pt'>&nbsp;</span>
        <font size="1">
        <b>Observações:</b><br>
        - As ocorr&ecirc;ncias de aposentadoria 02031, 02032, 02211, 02080, 02074, 02129, 01124, e todas iniciadas com 05 geram altera&ccedil;&atilde;o autom&aacute;tica da situa&ccedil;&atilde;o cadastral para 02;<br>
        - As ocorr&ecirc;ncias 01120 e 01121 geram altera&ccedil;&atilde;o autom&aacute;tica da situa&ccedil;&atilde;o cadastral para 15.
        </font>
    </div>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


/*
 * Lista a tabela de códigos de situação
 * cadastral de servidores/estagiários
 */

function listaCodOcorrenciaDeExclusao($codoc = '')
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // situacao cadastral for 66 (estagiario)
    // os codigos de ocorrencias serão limitados
    if ($sitcad == "66")
    {
        $query1 = "SELECT cod_ocorr AS codigo, desc_ocorr AS descricao FROM tabocorr WHERE cod_ocorr IN ('02014','02030','07027','02081') ORDER BY desc_ocorr ";
    }
    else
    {
        $query1 = "SELECT cod_ocorr AS codigo, desc_ocorr AS descricao FROM tabocorr ORDER BY desc_ocorr ";
    }
    $oDBase->query($query1);

    $lista       = '';
    $lista       .= "<option value='00'>Selecione uma opção</option>";
    while ($oOcorrencia = $oDBase->fetch_object())
    {
        $lista .= "<option value='" . tratarHTML($oOcorrencia->codigo) . "'";
        if ($oOcorrencia->codigo == $codoc)
        {
            $lista .= " selected";
        }
        $lista .= ">" . tratarHTML($oOcorrencia->codigo) . " - " . tratarHTML($oOcorrencia->descricao) . "</option>";
    }
    // Fim da tabela
    return $lista;

}
