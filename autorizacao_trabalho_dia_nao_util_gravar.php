<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Autorização de Trabalho em Dia Não Útil      |
 * |                - MODO 6                                     |
 * |                  Grava autorização de trabalho Dia Não Útil |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
include_once( "config.php" );

verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados             = explode(":|:", descriptografa($dadosorigem));
    $mat               = $dados[0];
    $dia               = $dados[1];
    $email             = $dados[2];
    $lotacao           = $dados[3];
    $orgao_sigla       = $dados[3];
    $lotacao_descricao = $dados[3];
    $idreg             = $dados[4];
}

// instancia o BD
$oDBase = new DataBase('PDO');

// pesquisa
$oDBase->setMensagem("Matrícula não localizada no banco de dados!");
$oDBase->query("
SELECT
    email
FROM
    servativ
WHERE
    mat_siape = :siape
", array(
    array(':siape', $mat, PDO::PARAM_STR),
));

// pesquisa
$oDBase->setMensagem("Data não localizada no banco de dados para essa matrícula!");
$oDBase->query("
UPDATE tabdnu
SET
    autorizado       = 'S',
    data_autorizado  = NOW(),
    siape_autorizado = :siape
WHERE
    siape = :siape
    AND dia = :dia
", array(
    array(':siape', $mat, PDO::PARAM_STR),
    array(':dia', conv_data($dia), PDO::PARAM_STR),
    array(':siape_autoriza', $_SESSION['sMatricula'], PDO::PARAM_STR),
));

$oDBase->query("
SELECT
    *
FROM
    tabdnu
WHERE
    autorizado = 'N'
    AND siape = :siape
", array(
    array(':siape', $mat, PDO::PARAM_STR),
));
$nrows = $oDBase->num_rows();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$title = _SISTEMA_SIGLA_ . ' | Autorização para trabalho em dia Não Útil';

// css extra
$css = array();

// js extra
$javascript = array();

// Topo do formulário
//
$oForm->exibeTopoHTML();
?>
<div class="container margin-20">
    <div class="row margin-10">
        <div class="corpo">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong>Autorização para trabalho em dia Não Útil</strong></h6>
            </div>
            <div class="col-md-12 margin-bottom-25"></div>

            <div class="col-md-12">
                <div class="row">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th class="text-center" style='vertical-align:middle;'>ÓRGÃO</th>
                                <th class="text-center" style='vertical-align:middle;'>LOTAÇÃO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center col-xs-2 text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></h4></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


            <div class='margin-bottom-25 text-center'>
                <font face='Tahoma' size='4'><br><br>AUTORIZAÇÃO REGISTRADA COM SUCESSO!</font>
            </div>

            <div class="col-md-4 col-xs-6 col-md-offset-3">
                <a class="btn btn-success btn-block" id="btn-continuar" href="autorizacao_trabalho_dia_nao_util_imprimir.php?dados=<?= base64_encode($mat . ':|:' . $dia); ?>" target='new' role="button">
                    <span class="glyphicon glyphicon-print"></span> Imprimir a Autorização de Entrada</a>
            </div>
            <div class="col-md-2 col-xs-6">
                <a class="btn btn-danger btn-block" id="btn-voltar" href="javascript:window.location.replace('<?= ($nrows == 0 ? "autorizacao_trabalho_dia_nao_util_entra.php" : "autorizacao_trabalho_dia_nao_util.php?qlotacao=$lotacao"); ?>');" role="button">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>

        </div>
    </div>
</div>
<?php
$count = 0;
$count = enviarEmail($email, 'AUTORIZACAO DE TRABALHO EM DIA NAO UTIL', "<br><br><big>Sua solicitação de trabalho para o dia " . tratarHTML($dia) . " foi autorizada, é necessário apresentar o documento de autorização assinado por sua chefia imediata ao setor responsável pela administração predial.<br>Atenciosamente,<br><br>SISREF.</big><br><br>");

if ($count != 0)
{
    mensagem("Email enviado com sucesso para " . tratarHTML($email) . "!");
}
else
{
    mensagem("Ocorreu um erro durante o envio do email para " . tratarHTML($email) . "!");
}

// Base do formulário
//
$oForm->exibeBaseHTML();
