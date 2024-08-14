<?php
// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $siape = $dados[0];
    $dia   = databarra($dados[1]);
}

// instancia banco de dados
$oDBase = new DataBase('PDO');

/* obtem dados da uorg  para saber se uorg ou upag e a mesma do usuario */
$oDBase->query("
SELECT
    cad.nome_serv, cad.cod_cargo, cad.cod_lot, cad.entra_trab,
    cad.sai_trab, und.descricao, taborgao.denominacao, taborgao.sigla
FROM
    servativ AS cad
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
LEFT JOIN
    taborgao ON LEFT(und.codigo,5) = taborgao.codigo
WHERE
    cad.mat_siape = :siape
", array(
    array(':siape', $siape, PDO::PARAM_STR),
));

$oServidor         = $oDBase->fetch_object();
$nome              = $oServidor->nome_serv;
$lotacao           = $oServidor->cod_lot;
$lotacao_descricao = $oServidor->descricao;
$orgao_sigla       = $oServidor->sigla;
$orgao_denominacao = $oServidor->denominacao;
$ent               = $oServidor->entra_trab;
$sai               = $oServidor->sai_trab;

if ($oDBase->num_rows() == 0)
{
    mensagem("Servidor não está ativo ou inexistente!", null, 1);
}
?>
<!DOCTYPE html>
<html lang='pt-br'>
    <head>
        <title></title>
        <meta http-equiv='Content-Language' content='pt-br'>
        <meta http-equiv='Content-Type' content='text/html; charset=windows-1252'>
        <meta http-equiv='X-UA-Compatible' content='IE=Edge'/>
        <meta http-equiv='Pragma' content='no-cache'>
        <meta http-equiv='Expires' content='-1'>

        <!--<link type="text/css" rel="stylesheet" href="./css/sisref.min.css">-->
        <link type="text/css" rel="stylesheet" href="./css/new/css/bootstrap.min.css">
        <link type="text/css" rel="stylesheet" href="./css/new/css/custom.css">
        <link type="text/css" rel="stylesheet" href="./css/estilos_new_layout.css">
        <!-- <link type="text/css" rel="stylesheet" href="./css/new/css/bootstrap-theme-menu-app.css"> -->
        <link type="text/css" rel="stylesheet" href="./css/new/css/bootstrap-dialog.min.css">
        <link type="text/css" rel="stylesheet" href="./css/bootstrap-print.css" media='print'>

        <style>
            label { font:  bold 11px Verdana; margin: 0; }
            td { border-top:solid black 1.0pt; border-left:solid black 1.0pt; border-bottom:solid black 1.0pt; border-right:none; padding:5px 0 5px 5px; }
            span { font: bold 11px Verdana, Arial, Helvetica, sans-serif; margin:0; }
            p { font: 13px Verdana, Arial, Helvetica, sans-serif; margin: 0; }
        </style>
    </head>
    <body>

        <div class="col-md-12 text-center" style="padding-top:10px;height:52px;margin:0px;">
            <img src="imagem/brazao2.png" height='50px'>
        </div>
        <div class="col-md-12 text-center" style="height:22px;margin:0px;padding:0px;">
            <h5><?= tratarHTML($orgao_denominacao); ?></h5>
        </div>
        <div class="col-md-12 text-center" style="height:42px;margin:0px;padding:0px;">
            <h5><?= tratarHTML($lotacao_descricao); ?></h5>
        </div>
        <div class="col-md-12 margin-bottom-25"></div>
        <div class="col-md-12 text-center">
            <h3>AUTORIZA&Ccedil;&Atilde;O DE ENTRADA</h3>
        </div>
        <div class="col-md-12 margin-bottom-25"></div>

        <table border=0 align="center" cellpadding=0 cellspacing=0 class=MsoNormalTable style='border-collapse:collapse' width="85%">
            <tr style='page-break-inside:avoid'>
                <td colspan="16" style="border-right:1px solid #000;">
                    <div style='padding: 35px 15px 25px 15px;'>
                        <p style='text-align: justify; text-indent: 40px;'>Autorizo o servidor <b><?= tratarHTML($nome); ?></b>, matr&iacute;cula <b><?= tratarHTML(removeOrgaoMatricula( $siape )); ?></b>, a utilizar as depend&ecirc;ncias da <b><?= tratarHTML($lotacao_descricao); ?></b> no dia <b><?= tratarHTML($dia); ?></b>, para execu&ccedil;&atilde;o de trabalhos inerentes a categoria funcional no hor&aacute;rio de <b><?= tratarHTML($ent); ?></b> às <b><?= tratarHTML($sai); ?></b>, com vistas &agrave; compensa&ccedil;&atilde;o de jornada de trabalho.</p>
                    </div>
                </td>
            </tr>
            <tr style='page-break-inside:avoid'>
                <td height="57" colspan=9> <label>Local e Data</label> <br> &nbsp; </td>
                <td colspan=7 style="border-right:1px solid #000;"> <label>Assinatura da chefia</label> <br> &nbsp; </td>
            </tr>
        </table>
        <p>&nbsp;</p>
    </body>
</html>
