<?php
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("logado");

$sLotacao = $_SESSION['sLotacao'];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // le dados passados por formulario
    $mat    = anti_injection($_REQUEST['mat']);
    $dia    = $_REQUEST['dia'];
    $oco    = anti_injection($_REQUEST['oco']);
    $comp   = anti_injection($_REQUEST['comp']);
    $cmd    = anti_injection($_REQUEST['cmd']);
    $so_ver = "";

    $sMatricula = $_SESSION['sMatricula'];
    $sLotacao   = $_SESSION['sLotacao'];
    $sNome      = $_SESSION['sNome'];
}
else
{
    // Valores passados - encriptados
    $dados    = explode(":|:", base64_decode($dadosorigem));
    $mat      = $dados[0];
    //$sNome = iso88591_utf8($dados[1]);
    $sNome    = utf8_decode($dados[1]);
    $sLotacao = $dados[2];
    $comp     = $dados[3];
    $dia      = $dados[4];
    $just     = utf8_decode($dados[5]);
    $oco      = $dados[6];
    $cmd      = $dados[7];
    $so_ver   = $dados[8];
    $justchef = utf8_decode($dados[9]);

    $sMatricula = $mat;
}

$matual = date("mY");

$dt   = conv_data($dia);
$comp = dataMes($dia) . dataAno($dia);

$sMatricula = getNovaMatriculaBySiape($sMatricula);

$oDBase = selecionaServidor($sMatricula);
$sitcad = $oDBase->fetch_object()->sigregjur;

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona dados do Órgão e Unidade
$oDBase->query("
    SELECT
        und.descricao, taborgao.denominacao, taborgao.sigla
    FROM
        tabsetor AS und
    LEFT JOIN
        taborgao ON LEFT(und.codigo,5) = taborgao.codigo
    WHERE
        und.codigo = :lotacao
    ", array(
    array(":lotacao", $sLotacao, PDO::PARAM_STR)
));

$oOrgao = $oDBase->fetch_object();


$lotacao_descricao = $oOrgao->descricao;
$orgao_descricao   = utf8_decode($oOrgao->denominacao);
$orgao_sigla       = $oOrgao->sigla;


// seleciona a descrição da ocorrência
$oDBase->query("
    SELECT
        oco.desc_ocorr
    FROM
        tabocfre AS oco
    WHERE
        oco.siapecad = :siapecad
    ", array(
    array(":siapecad", $oco, PDO::PARAM_STR)
));

$oOcorrencia = $oDBase->fetch_object();

$descricao_ocorrencia = $oOcorrencia->desc_ocorr;


// seleciona nome do servidor e jornada
$oDBase->query("
    SELECT
        oco, just, justchef
    FROM
        ponto$comp
    WHERE
        siape = :siape
        AND dia = :dia
    ", array(
    array(":siape", $sMatricula, PDO::PARAM_STR),
    array(":dia", $dt, PDO::PARAM_STR),
));

$oPonto = $oDBase->fetch_object();

$oco      = $oPonto->oco;
$just     = $oPonto->just;
$justchef = $oPonto->justchef;

$mes_homologado = verifica_se_mes_homologado($mat, substr($comp, 2, 4) . substr($comp, 0, 2));



$title = _SISTEMA_SIGLA_ . ' | Justificativa para ocorr&ecirc;ncia';

$css = array();

$javascript = array();

include("html/html-base.php");
include("html/header.php");


// verifica se existe justificativa e se poderá alterar.
// alteração só é possível se for no próprio mês
//$so_ver = 'nao';
$ocorrencias_negativas = $codigosCompensaveis;
$so_ver                = ($comp == $matual && $mat == $_SESSION['sMatricula'] ? 'nao' : 'sim');
$pode_justificar       = (in_array($oco, $ocorrencias_negativas) ? 1 : 0);

if ($mat == $_SESSION['sMatricula'])
{
    if ($mes_homologado == 'HOMOLOGADO') // impede de justificar ocorrencias em meses homologados
    {
        $so_ver = 'sim';
    }
    elseif ($pode_justificar == 0 && $just != "")
    {
        $so_ver = ($comp == $matual && $mat == $_SESSION['sMatricula'] ? 'nao' : 'sim');
    }
    elseif ($comp != $matual && $mat == $_SESSION['sMatricula']) // impede de justificar ocorrencias em meses homologados
    {
        $so_ver = 'nao';
    }
    elseif ($pode_justificar == 0)
    {
        //mensagem("Não é permitido apresentar justificativa para essa ocorrência!", null, 1);
    }
}
else
{
    mensagem("Não é permitido apresentar justificativa por outro servidor!", null, 1);
}

//pegando o ip do usuario
$ip = getIpReal();

// pagina de retorno
if ($_SESSION['sPaginaDeRetorno1'] == '')
{
    $_SESSION['sPaginaDeRetorno1'] = $_SERVER['REQUEST_URI'];
    $_SESSION['sPaginaDeRetorno2'] = '';
}
else
{
    $_SESSION['sPaginaDeRetorno2'] = $_SERVER['REQUEST_URI'];
}

$_SESSION['sPaginaDeRetorno3'] = '';
$_SESSION['sPaginaDeRetorno4'] = '';


// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

?>
<script language="javascript">
    $(document).ready(function ()
    {

        $("#btn-continuar").click(function ()
        {
            $('#form1').attr('action', "gravahorario.php?modo=2")
            $('#form1').submit();
        });

    });

    function verificadados()
    {
        return false;
    }
</script>

<div class="container">
    <div class="row" style="padding-top:90px;">
        <div class="corpo">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong>Justificativa para Ocorr&ecirc;ncia</strong></h6>
            </div>
            <div class="col-md-12 margin-bottom-25"></div>
            <?php

            if ($so_ver == 'sim' || $mes_homologado == 'HOMOLOGADO')
            {
                ?>
                <form id="form1" name="form1" method="POST" action="#">
                <?php
            }
            else
            {
                ?>
                <form id="form1" name="form1" method="POST">
                <?php
            }

            ?>
            <input type="hidden" id="comp"    name="comp"    value="<?= tratarHTML($comp); ?>">
            <input type="hidden" id="cmd"     name="cmd"     value="<?= tratarHTML($cmd); ?>">
            <input type="hidden" id="dados"   name="dados"   value="<?= tratarHTML($dadosorigem); ?>">
            <input type="hidden" id="siape"   name="siape"   value="<?= tratarHTML($sMatricula); ?>">
            <input type="hidden" id="nome"    name="nome"    value="<?= tratarHTML($sNome); ?>">
            <input type="hidden" id="lotacao" name="lotacao" value="<?= tratarHTML($sLotacao); ?>">

            <div class="col-md-12">
                <div class="row">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th class="text-center text-nowrap" style='vertical-align:middle;'>Mat. SIAPE</th>
                                <th class="text-center" style='vertical-align:middle;'>NOME</th>
                                <th class="text-center" style='vertical-align:middle;'>ÓRGÃO</th>
                                <th class="text-center" style='vertical-align:middle;'>LOTAÇÃO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><h4><?= tratarHTML(removeOrgaoMatricula( $sMatricula )); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML($sNome); ?></h4></td>
                                <td class="text-center col-xs-2 text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $sLotacao )); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML(getUorgMaisDescricao( $sLotacao )); ?></h4></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-group col-md-12">
                <div class="col-md-3 col-md-offset-1">
                    <label for="dia" class="control-label">Dia</label>
                </div>
                <div class="col-md-2">
                    <input type="text" id="dia" name="dia" size="10" maxlength="10" value="<?= tratarHTML($dia); ?>" class="form-control" readonly>
                </div>
            </div>
            <div class="form-group col-md-12">
                <div class="col-md-3 col-md-offset-1">
                    <label for="oco" class="control-label">Ocorrência</label>
                </div>
                <div class="col-md-1">
                    <input type="text" id="oco" name="oco" size="8" maxlength="8" value="<?= tratarHTML($oco); ?>" class="form-control" readonly style="width:80px;">
                </div>
                <div class="col-md-6">
                    <input type="text" id="desc_ocorr" name="desc_ocorr" size="30" maxlength="30" value="<?= tratarHTML($descricao_ocorrencia); ?>" class="form-control" readonly>
                </div>
            </div>
            <div class="form-group col-md-12">
                <div class="col-md-3 col-md-offset-1">
                    <label for="siapecad" class="control-label">Justificativa do Servidor</label>
                </div>
                <div class="col-md-8">
                    <textarea name='just' cols=80 rows=5 id="just" <?= ($so_ver == 'sim' || $mes_homologado == 'HOMOLOGADO' ? 'readonly' : ''); ?>><?= tratarHTML($just); ?></textarea>
                </div>
            </div>
            <?php

            if ($so_ver == 'sim' && $justchef != "")
            {
                ?>
                <div class="form-group col-md-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="siapecad" class="control-label">Justificativa da Chefia</label>
                    </div>
                    <div class="col-md-8">
                        <textarea name='justchef' cols=80 rows=5 id="justchef"><?= tratarHTML($justchef); ?></textarea>
                    </div>
                </div>
                <?php
            }

            ?>
            <div class="form-group col-md-12 text-center">
                <div class="col-md-2"></div>
                <?php

                if ($so_ver != 'sim' && $mes_homologado != 'HOMOLOGADO')
                {
                    ?>
                    <div class="col-md-2 col-xs-6 col-md-offset-2">
                        <button type="submit" name="enviar" id="btn-continuar" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> Continuar
                        </button>
                    </div>
                    <?php
                }

                ?>
                <div class="col-md-2 col-xs-6">
                    <a  class="btn btn-danger btn-block" id="btn-voltar" href='javascript:window.location.replace("<?= $_SESSION["sVePonto"]; ?>");'>
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                </div>

                <div class="col-md-2"></div>
            </div>

            </form>

        </div>
    </div>
</div>
<?php
if ($so_ver != 'sim' && $mes_homologado != 'HOMOLOGADO')
{
    ?>
    <script> $('#just').focus();</script>
    <?php
}

include("html/footer.php");

DataBase::fechaConexao();
