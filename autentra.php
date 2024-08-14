<?php
// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao("sAPS");

// dados passados via formulario
$siape = anti_injection($_REQUEST['siape']);
$dia   = $_REQUEST['dia'];
$dia   = databarra($dia);

// instancia banco de dados
$oDBase = new DataBase('PDO');

/* obtem dados da uorg  para saber se uorg ou upag e a mesma do usuario */
$oDBase->query("
		SELECT
			cad.nome_serv, cad.cod_cargo, cad.cod_lot, und.descricao, cad.entra_trab, cad.sai_trab
		FROM
			servativ AS cad
		LEFT JOIN
			tabsetor AS und ON cad.cod_lot = und.codigo
		WHERE
			cad.mat_siape = '" . $siape . "'
	");
$oServidor = $oDBase->fetch_object();
$nome      = $oServidor->nome_serv;
$lot       = $oServidor->cod_lot;
$desc      = $oServidor->descricao;
$ent       = $oServidor->entra_trab;
$sai       = $oServidor->sai_trab;

if ($oDBase->num_rows() == 0)
{
    mensagem("Servidor não está ativo ou inexistente!", null, 1);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title>Documento sem t&iacute;tulo</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style>
            label {
                font:  bold 11px Verdana;
                margin: 0;
            }
            td {
                border-top:solid black 1.0pt;
                border-left:solid black 1.0pt;
                border-bottom:solid black 1.0pt;
                border-right:none;
                padding:5px 0 5px 5px;
            }
            span {
                font: bold 11px Verdana, Arial, Helvetica, sans-serif;
                margin:0;
            }
            p {
                font: 13px Verdana, Arial, Helvetica, sans-serif;
                margin: 0;
            }
        </style>
    </head>

    <body>
        <table border=0 align="center" cellpadding=0 cellspacing=0 class=MsoNormalTable style='border-collapse:collapse' width="85%">
            <tr>
                <td colspan="16" valign=top style='width:753.15pt;border:solid black 0pt;border-right:none;padding:0cm 3.5pt 0cm 3.5pt'>
                    <p class=CartaN align=center style='margin-top:0cm;margin-right:0pt;margin-bottom:0cm;margin-left:0cm;margin-bottom:.0001pt;text-align:center;'><b><img src="<?= _DIR_IMAGEM_; ?>logo_inss.jpg" width="126" height="57"></b></p>
                    <p align="center" class=CartaN style='margin-top:0cm;margin-right:0pt;margin-bottom:0cm;margin-left:0cm;margin-bottom:.0001pt;'><b><span lang=PT-BR style='font-size:10.0pt'>Instituto Nacional do Seguro Social</span></b></p>
                    <p align="center" class=CartaN style='margin-top:0cm;margin-right:0pt;margin-bottom:0cm;margin-left:0cm;margin-bottom:.0001pt'><b><span lang=PT-BR style='font-size:9.0pt'>Diretoria de Recursos Humanos</span></b></p>
                    <p align="center" class=CartaN style='margin-top:0cm;margin-right:0pt;margin-bottom:0cm;margin-left:0cm;margin-bottom:0pt'><b><span lang=PT-BR style='font-size:9.0pt'>Coordena&ccedil;&atilde;o Geral de Administra&ccedil;&atilde;o de Recursos Humanos</span></b></p>
                    <p align="center" class=CartaN style='margin-top:0cm;margin-right:0pt;margin-bottom:0cm;margin-left:0cm;margin-bottom:0pt'>&nbsp;</p>
                    <p align="center" class=CartaN style='margin-top:0cm;margin-right:0pt;margin-bottom:0cm;margin-left:0cm;margin-bottom:0pt'>&nbsp;</p>
                    <p align="center" class=CartaN style='margin-top:0cm;margin-right:0pt;margin-bottom:0cm;margin-left:0cm;margin-bottom:0pt'><b></b></p>
                </td>
            </tr>
            <tr style='page-break-inside:avoid'>
                <td colspan=16 valign=top style='width:749.75pt;border-left:solid black 0pt;border-top:none;border-right:solid black 0pt;border-left:solid black 0pt;padding:0cm 3.5pt 0cm 3.5pt'>
                    <div align="center"><b><span lang=PT-BR style='font-size:10.0pt;font-family:Verdana'>AUTORIZA&Ccedil;&Atilde;O
                                DE ENTRADA</span></b></div>
                </td>
            </tr>
            <tr style='page-break-inside:avoid'>
                <td colspan="16" style="border-right:1px solid #000;">
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>Autorizo o servidor <?= tratarHTML($nome); ?>, matr&iacute;cula <?= tratarHTML(removeOrgaoMatricula( $siape )); ?>, a utilizar as depend&ecirc;ncias da <?= tratarHTML($desc); ?> no dia <?= tratarHTML($dia); ?>, para execu&ccedil;&atilde;o de trabalhos inerentes a categoria funcional no hor&aacute;rio de <?= tratarHTML($ent); ?> às <?= tratarHTML($sai); ?>, com vistas &agrave; compensa&ccedil;&atilde;o de jornada de trabalho.</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                </td>
            </tr>
            <tr style='page-break-inside:avoid'>
                <td height="57" colspan=9> <label>Local e Data</label> <br> &nbsp; </td>
                <td colspan=7 style="border-right:1px solid #000;"> <label>Assinatura da chefia</label> <br> &nbsp; </td>
            </tr>
            <tr height=0>
                <td width=271 height="2" style='border:none'></td>
                <td width=5 style='border:none'></td>
                <td width=5 style='border:none'></td>
                <td width=118 style='border:none'></td>
                <td width=5 style='border:none'></td>
                <td width=5 style='border:none'></td>
                <td width=117 style='border:none'></td>
                <td width=9 style='border:none'></td>
                <td width=6 style='border:none'></td>
                <td width=5 style='border:none'></td>
                <td width=100 style='border:none'></td>
                <td width=16 style='border:none'></td>
                <td width=5 style='border:none'></td>
                <td width=67 style='border:none'></td>
                <td width=189 style='border:none'></td>
                <td width=94 style='border:none'></td>
            </tr>
        </table>

        <p>&nbsp;</p>

        <table border=0 align="center" cellpadding=0 cellspacing=0 class=MsoNormalTable style='margin-left:-.25pt;border-collapse:collapse'>
            <tr height=0>
                <td width=116 height="2" style='border:none'></td>
                <td width=1 style='border:none'></td>
                <td width=1 style='border:none'></td>
                <td width=118 style='border:none'></td>
                <td width=1 style='border:none'></td>
                <td width=3 style='border:none'></td>
                <td width=117 style='border:none'></td>
                <td width=9 style='border:none'></td>
                <td width=6 style='border:none'></td>
                <td width=1 style='border:none'></td>
                <td width=100 style='border:none'></td>
                <td width=16 style='border:none'></td>
                <td width=1 style='border:none'></td>
                <td width=67 style='border:none'></td>
                <td width=189 style='border:none'></td>
                <td width=280 style='border:none'></td>
            </tr>
        </table>

        <p>&nbsp;</p>
    </body>
</html>
