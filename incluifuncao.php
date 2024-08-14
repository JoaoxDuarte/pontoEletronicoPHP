<?php

include_once( "config.php" );

// verifica se o usuário tem permissão para acessar este módulo
verifica_permissao('sRH ou sTabServidor');

// recebe os parametros passados
$matricula       = getNovaMatriculaBySiape(anti_injection($_REQUEST['matricula']));
$novafuncao      = anti_injection($_REQUEST['novafuncao']);
$ocupacao        = anti_injection($_REQUEST['ocupacao']);
$ocupacao_select = anti_injection($_REQUEST['ocupacao_select']);

// recupera dados gravados em sessao
if (isset($_SESSION['grava_inclui_funcao']) && $_SESSION['grava_inclui_funcao'] != NULL)
{
    if (!empty($matricula) && !empty($_SESSION['sGravaFuncaoSiape']) && $matricula == $_SESSION['sGravaFuncaoSiape'])
    {
        $matricula  = getNovaMatriculaBySiape($_SESSION['sGravaFuncaoSiape']);
        $novafuncao = $_SESSION['sGravaFuncaoNumFuncao'];
        $ocupacao   = $_SESSION['sGravaFuncaoSituacao'];
    }

    $funcao     = $_SESSION['sGravaFuncaoFuncao'];
    $lota       = $_SESSION['sGravaFuncaoLota'];
    $sigla      = $_SESSION['sGravaFuncaoSigla'];
    $uorg       = $_SESSION['sGravaFuncaoUorg'];
    $ugpai      = $_SESSION['sGravaFuncaoPai'];
    $nome       = $_SESSION['sGravaFuncaoNome'];
    $lotat      = $_SESSION['sGravaFuncaoLotat'];
    $dinglota   = databarra($_SESSION['sGravaFuncaoDingLota']);

    $area       = $_SESSION['sGravaFuncaoArea'];
    $publicacao = $_SESSION['sGravaFuncaoPublicacao'];

    $inicio     = databarra($_SESSION['sGravaFuncaoInicio']);

    $Ndoc1      = $_SESSION['sGravaFuncaoNdoc1'];
    $Ndoc2      = $_SESSION['sGravaFuncaoNdoc2'];
    $Nnum1      = $_SESSION['sGravaFuncaoNnum1'];
    $Nnum2      = $_SESSION['sGravaFuncaoNnum2'];

    $Ndata1     = databarra($_SESSION['sGravaFuncaoNdata1']);
    $Ndata2     = databarra($_SESSION['sGravaFuncaoNdata2']);
}


// grava dados em sessao
$_SESSION['sGravaFuncaoNovaFuncao'] = $novafuncao;
$_SESSION['sGravaFuncaoIdSubs']     = $idsubs;
$_SESSION['sGravaFuncaoMatricula']  = getNovaMatriculaBySiape($matricula);
$_SESSION['sGravaFuncaoOcupacao']   = $ocupacao;

$destino = "incfuncserv.php";


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCSS("css/select2.min.css");
$oForm->setCSS("css/select2-bootstrap.css");
$oForm->setCSS("js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css");

$oForm->setJS( "js/funcoes_valida_cpf_pis.js" );
$oForm->setJS( "js/select2.full.js");
$oForm->setJS( 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
$oForm->setJS( 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js');

$oForm->setJS("incluifuncao.js?v.1.0.1");

$oForm->setSubTitulo("Registro de Ocupante de Função");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


## ############################### ##
##                                 ##
##      VALIDA DADOS ENVIADOS      ##
##                                 ##
## ############################### ##

$tipo = 'danger';

// class valida
$validar = new valida();
$validar->setDestino( $destino );
$validar->setExibeMensagem( false );

## NÚMERO DA FUNÇÃO
if ($novafuncao == '00000')
{
    retornaInformacao('Selecione uma função!', $tipo);
}

## MATRÍCULA SIAPE
$mensagem = $validar->siape( $matricula );

## SITUAÇÃO DA OCUPAÇÃO
if ($ocupacao == 'V')
{
    retornaInformacao('Selecione a situação!', $tipo);
}


// grava em sessao dados do script atual
//$_SESSION['sHOrigem_1'] = "excfuncserv.php";
$_SESSION['sHOrigem_2'] = $_SERVER['REQUEST_URI']
                          . "?sigla=$sigla&matricula="
                          . removeOrgaoMatricula($matricula);
$_SESSION['sHOrigem_3'] = '';
$_SESSION['sHOrigem_4'] = '';

// pega o nome do arquivo origem
$path_parts       = pathinfo($_SERVER['HTTP_REFERER']);
$pagina_de_origem = $path_parts['basename'];

$mensagem = "";

// tipo de situacao de ocupacao
$tipo_de_ocupacao = array(
    'T' => "TITULAR",
    'S' => "SUBSTITUTO",
    'R' => "INTERINO",
    /*'E' => "EVENTUAL",*/
);



## ############################### ##
##                                 ##
##     VALIDAÇÃO  COMPLEMENTAR     ##
##                                 ##
## ############################### ##

// Pesquisa no cadastro
// Se não achar, exibe msg erro
// e volta a página anterior
$oDBase = verificaCadastro( $matricula );

// dados do servidor
$oServidor = $oDBase->fetch_object();
$nome      = $oServidor->nome_serv;
$lotat     = $oServidor->cod_lot;
$dinglota  = $oServidor->dt_ing_lot;


// Se situação escolhida for titular ou interino
if ($ocupacao == 'T' || $ocupacao == 'R')
{
    // Pesquisa se a função já está ocupada
    // Se já estiver ocupada, exibe msg erro
    // e volta a página anterior
    $oDBase = verificaSeFuncaoJaOcupada($matricula, $ocupacao, $novafuncao);

    // Pesquisa se o servidor é titular de outras funções
    // Se já titular de 2 funções, exibe msg erro
    // e volta a página anterior
    $oDBase = verificaSeTitularDeOutraFuncao($matricula, $ocupacao, $novafuncao);
}
else // Se situação escolhida for substituto
{
    // Pesquisa se a função já está ocupada
    // Se já estiver ocupada, exibe msg erro
    // e volta a página anterior
    $oFuncao = verificaSePermiteSubstituto($novafuncao, $ocupacao);

    // Pesquisa se o servidor já consta como
    // ocupante da função com outra situação
    // Se já constar, exibe msg erro
    // e volta a página anterior
    $oDBase = verificaSeSubstitutoDaMesmaFuncao($matricula, $ocupacao, $novafuncao);

    // Pesquisa se o servidor é substituto de outras funções
    // Se já substituto de 2 funções, exibe msg erro
    // e volta a página anterior
    $oDBase = verificaSeSubstitutoDuasFuncoes($matricula, $ocupacao, $novafuncao);
}


## ############################### ##
##                                 ##
##     DADOS PARA O FORMULÁRIO     ##
##                                 ##
## ############################### ##


// tabfunc: tabela de funções
$oDBase  = carregaDadosFuncao($novafuncao);
$oFuncao = $oDBase->fetch_object();

$funcao   = $oFuncao->desc_func;  // descricao da função
$num_func = $oFuncao->num_funcao; // número da função
$sigla    = $oFuncao->cod_funcao; // sigla da função
$lot      = $oFuncao->cod_lot;    // unidade de lotação da função
$idsubs   = $oFuncao->indsubs;    // indica se pode ter substituto para ocupante da função

// tabsetor: tabela de setores, lotações
$uorg     = $oFuncao->cod_uorg;
$area     = $oFuncao->area;

?>
<form method="POST" id="form1" name="form1" onsubmit="javascript:return false;">

    <input type='hidden' id='modo'       name='modo'       value='1'>
    <input type="hidden" id="num_funcao" name="num_funcao" value="<?= tratarHTML($num_func); ?>">
    <input type="hidden" id="funcao"     name="funcao"     value="<?= tratarHTML($funcao); ?>">
    <input type='hidden' id='lota'       name='lota'       value='<?= tratarHTML($lot); ?>'>
    <input type='hidden' id='area'       name='area'       value='<?= tratarHTML($area); ?>'>
    <input type='hidden' id='sigla'      name='sigla'      value='<?= tratarHTML($sigla); ?>'>
    <input type='hidden' id='uorg'       name='uorg'       value='<?= tratarHTML($uorg); ?>'>
    <input type='hidden' id='ocupacao'   name='ocupacao'   value='<?= tratarHTML($ocupacao); ?>'>

    <table class='table table-condensed text-center'>
        <tr>
            <td colspan="1" class='text-left no-wrap' style='border-width:0px;width:90px;'>
                <label class='control-label' style='padding-top:5px;'>Matr&iacute;cula:</label>
                <input type="text" name="matricula" id="matricula" value="<?= tratarHTML(removeOrgaoMatricula($matricula)); ?>" size="7" maxlength="7" class='form-control' readonly>
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
                <label class='control-label' style='padding-top:5px;'>Situa&ccedil;&atilde;o do ocupante:</label>
                <input type="text" id="situacao" name="situacao" value="<?= tratarHTML($tipo_de_ocupacao[$ocupacao]); ?>" size="13" maxlength="13" class="form-control" readonly>
            </td>
        </tr>
    </table>
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
                        <th width="15%" class="text-center">In&iacute;cio de exerc&iacute;cio</th>
                        <th colspan="5" class="text-center">Portaria de nomea&ccedil;&atilde;o/designa&ccedil;&atilde;o</th>
                    </tr>
                    <tr height='45'>
                        <td width="15%">
                            <div class="col-md-2" id='dt-container' style='padding:0px;border-width:0px;'>
                                <label class='control-label'>Data:</label>
                                <div class="input-group date">
                                    <input type="text" id="inicio" name="inicio" value='<?= tratarHTML($inicio); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                </div>
                            </div>
                        </td>
                        <td style='border-width:0px;'>
                            <label class='control-label'>Portaria Número:</label>
                            <input type="text" name="Nnum1" id="Nnum1" value="<?= tratarHTML($Nnum1); ?>" size="9" maxlength='9' class="form-control"></p>
                        </td>
                        <td style='border-width:0px;'>
                            <div class="col-md-1" id='dt-container' style='padding:0px;border-width:0px;'>
                                <label class='control-label'>Portaria&nbsp;Data:</label>
                                <div class="input-group date">
                                    <input type="text" id="Ndata1" name="Ndata1" value='<?= tratarHTML($Ndata1); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
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
                            <input type="text" name="Nnum2" id="Nnum2" value="<?= tratarHTML($Nnum2); ?>" size="9" maxlength='9' class='form-control'>
                        </td>
                        <td style='border-width:0px;'>
                            <div class="col-md-1" id='dt-container' style='padding:0px;border-width:0px;'>
                                <label class='control-label'>Data:</label>
                                <div class="input-group date">
                                    <input type="text" id="Ndata2" name="Ndata2" value='<?= tratarHTML($Ndata2); ?>' size="10" maxlength="10" style="background-color:transparent;width:105px;" OnBlur="javascript:ve(this.value);" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                </div>
                            </div>
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
                <a class="btn btn-danger btn-block" href="javascript:window.location.replace('incfuncserv.php');">
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

function verificaCadastro($matricula=null)
{
    global $destino;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $destino );
    $oDBase->setMensagem('Problemas no acesso ao Cadastro!');
    $novamatricula = getNovaMatriculaBySiape($matricula);

    $oDBase->query("
    SELECT
        a.nome_serv, a.cod_lot,
        DATE_FORMAT(a.dt_ing_lot,'%d/%m/%Y') AS dt_ing_lot,
        b.upag
    FROM
        servativ AS a
    LEFT JOIN
        tabsetor AS b ON a.cod_lot = b.codigo
    WHERE
        a.mat_siape = :siape
        AND a.excluido = 'N'
        AND b.upag = :upag
        AND a.cod_sitcad NOT IN ('02','08','15','66')
    ",
    array(
        array(":siape", $novamatricula,        PDO::PARAM_STR),
        array(":upag",  $_SESSION['upag'], PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() == 0)
    {
        mensagem( "Servidor não encontrado!", $destino );
    }

    return $oDBase;
}


function verificaSeFuncaoJaOcupada($matricula=null, $ocupacao=null, $novafuncao=null)
{
    global $destino, $tipo_de_ocupacao;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $destino );
    $oDBase->setMensagem('Problemas no acesso ao Cadastro!');

    $oDBase->query("
    SELECT
        chf.mat_siape AS siape,
        cad.nome_serv AS nome,
        fun.num_funcao,
        fun.desc_func
    FROM
        ocupantes AS chf
    LEFT JOIN
        servativ AS cad ON chf.mat_siape = cad.mat_siape
    LEFT JOIN
        tabfunc AS fun ON chf.num_funcao = fun.num_funcao
    WHERE
        chf.num_funcao = :funcao
        AND chf.sit_ocup = :sitocup
        AND chf.dt_fim = '0000-00-00'
    ",
    array(
        array(":sitocup", $ocupacao,   PDO::PARAM_STR),
        array(":funcao",  $novafuncao, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        $oOcupante = $oDBase->fetch_object();
        mensagem(
            "O servidor " . $oOcupante->nome
            . ', matrícula ' . removeOrgaoMatricula($oOcupante->siape)
            . ", é o " . uc_words($tipo_de_ocupacao[$ocupacao])
            . " atual desta função!\\n"

            , $destino
        );
    }

    return $oDBase;
}


function verificaSeTitularDeOutraFuncao($matricula=null, $ocupacao=null, $novafuncao=null)
{
    global $destino, $tipo_de_ocupacao;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $destino );
    $oDBase->setMensagem('Problemas no acesso ao banco de dados de Ocupantes de Função!');

    $oDBase->query("
    SELECT
        chf.mat_siape AS siape,
        cad.nome_serv AS nome,
        fun.num_funcao,
        fun.desc_func
    FROM
        ocupantes AS chf
    LEFT JOIN
        servativ AS cad ON chf.mat_siape = cad.mat_siape
    LEFT JOIN
        tabfunc AS fun ON chf.num_funcao = fun.num_funcao
    WHERE
        chf.mat_siape = :siape
        AND chf.sit_ocup = :sitocup
        AND chf.sit_ocup <> 'S'
        AND chf.num_funcao <> :novafuncao
        AND chf.dt_fim = '0000-00-00'
    ",
    array(
        array(":siape",      $matricula,  PDO::PARAM_STR),
        array(":sitocup",    $ocupacao,   PDO::PARAM_STR),
        array(":novafuncao", $novafuncao, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        $oOcupante = $oDBase->fetch_object();
        mensagem(
            "O servidor " . $oOcupante->nome
            . ', matrícula ' . removeOrgaoMatricula($oOcupante->siape)
            . ", já é o " . uc_words($tipo_de_ocupacao[$ocupacao])
            . " da função " . $oOcupante->num_funcao . ' - ' . uc_words($oOcupante->desc_func) . "!\\n"
            , $destino
        );
    }

    return $oDBase;
}



function verificaSePermiteSubstituto($novafuncao=null, $ocupacao=null)
{
    global $destino;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $destino );
    $oDBase->setMensagem('Problemas no acesso ao banco de dados de Função!');

    //busca na tabela Tabfunc para saber a descrição das funções
    $oDBase->query("
    SELECT
        a.desc_func, a.num_funcao, a.cod_funcao, a.cod_lot, a.indsubs,
        b.cod_uorg, b.area
    FROM
        tabfunc AS a
    LEFT JOIN
        tabsetor AS b ON a.cod_lot = b.codigo AND b.ativo = 'S'
    WHERE
        a.num_funcao = :funcao
        AND a.indsubs = 'N'
        AND a.ativo = 'S'
    ",
    array(
        array(":funcao", $novafuncao, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        $oFuncao  = $oDBase->fetch_object();

        mensagem(
            "Não é permitido nomear substituto para essa função!\\n"
            . $oFuncao->desc_func . ' - ' . $oFuncao->num_funcao
            , $destino
        );
    }

    return $oFuncao;
}


function verificaSeSubstitutoDaMesmaFuncao($matricula=null, $ocupacao=null, $novafuncao=null)
{
    global $destino, $tipo_de_ocupacao;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $destino );
    $oDBase->setMensagem('Problemas no acesso a Funções!');

    $oDBase->query("
    SELECT
        chf.sit_ocup, fun.desc_func, fun.num_funcao
    FROM
        ocupantes AS chf
    LEFT JOIN
        tabfunc AS fun ON chf.num_funcao = fun.num_funcao
    WHERE
        chf.mat_siape = :siape
        AND chf.num_funcao = :funcao
        AND chf.dt_fim = '0000-00-00'
    ",
    array(
        array(":siape",     $matricula,  PDO::PARAM_STR),
        array(":funcao",    $novafuncao, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        $oOcupante = $oDBase->fetch_object();
        mensagem(
            "O servidor já está designado como " . uc_words($tipo_de_ocupacao[$oOcupante->sit_ocup]) . ' desta função ('
            . $oOcupante->desc_func . ' - ' . $oOcupante->num_funcao . ')'
            , $destino
        );
    }

    return $oDBase;
}



function verificaSeSubstitutoDuasFuncoes($matricula=null, $ocupacao=null)
{
    global $destino, $tipo_de_ocupacao;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $destino );
    $oDBase->setMensagem('Problemas no acesso ao banco de dados de Ocupantes de Função!');

    $oDBase->query("
    SELECT
        chf.mat_siape, cad.nome_serv, fun.num_funcao, fun.desc_func
    FROM
        ocupantes AS chf
    LEFT JOIN
        servativ AS cad ON chf.mat_siape = cad.mat_siape
    LEFT JOIN
        tabfunc AS fun ON chf.num_funcao = fun.num_funcao
    WHERE
        chf.mat_siape = :siape
        AND chf.sit_ocup = :sitocup
        AND chf.sit_ocup = 'S'
        AND chf.dt_fim = '0000-00-00'
    ",
    array(
        array(":siape",   $matricula,  PDO::PARAM_STR),
        array(":sitocup", $ocupacao,   PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() >= 2)
    {
        $contar  = 1;
        $funcoes = '';
        while ($oOcupante = $oDBase->fetch_object())
        {
            $funcoes .= $contar . ') '
            . $oOcupante->num_funcao . ' - ' . $oOcupante->desc_func . '\\n';
            $contar++;
        }
        mensagem(
            "O servidor já é SUBSTITUTO em 2 outras funções!\\n" . $funcoes
            , $destino
        );
    }

    return $oDBase;
}


function carregaDadosFuncao($novafuncao=null)
{
    global $destino;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino( $destino );
    $oDBase->setMensagem('Problemas no acesso aos dados de Função!');

    $oDBase->query("
    SELECT
        a.desc_func, a.num_funcao, a.cod_funcao, a.cod_lot, a.indsubs,
        b.cod_uorg, b.area
    FROM
        tabfunc AS a
    LEFT JOIN
        tabsetor AS b ON a.cod_lot = b.codigo AND b.ativo = 'S'
    WHERE
        a.num_funcao = :funcao
        AND a.ativo = 'S'
    ",
    array(
        array(":funcao", $novafuncao, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() == 0)
    {
        mensagem( "Função não localizada!", $destino );
    }

    return $oDBase;
}
