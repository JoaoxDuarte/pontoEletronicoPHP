<?php
include_once('config.php');

session_start();
destroi_sessao();
session_start();

include_once( _DIR_INC_ . "imgSet.php");
?>
<html>
    <head>
        <title>Principal</title>
        <link rel="stylesheet" type="text/css" href="<?= _DIR_CSS_; ?>estilos.css">

        <script language="JavaScript">
            function ve(parm1)
            {
                var lSiape = document.form.lSiape;
                var lSenha = document.form.lSenha;
                var txtImagem = document.form.txtImagem;
                if (lSiape.value.length >= 7)
                {
                    lSenha.focus();
                }
                if (lSenha.value.length == 8)
                {
                    txtImagem.focus();
                }
            }

            top.document.title = 'SISREF - Sistema de Registro Eletrônico de Frequência | <?= date("d/m/Y"); ?> | Usuário: <?= "$sMatricula $sNome"; ?>';
            document.status = '';
        </script>

    </head>
    <body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onload="$('#lSiape').focus()">
        <br>
    <center>
        <p align="center" style='font-family: Trebuchet MS; font-size: 16; font-weight: bold;'>
            <br>
            Sistema de Registro Eletrônico de Frequência
            <br>
            <font style='font-size: 11px'>- Acompanhamento das Paralisações / Greve</font>
        </p>
        <fieldset style='width: 600px;'>
            <form method="post" action="relatorio_paralisacoes_secaodousuario.php" id="form1" name="form" onsubmit="javascript:location.replace('relatorio_paralisacoes_secaodousuario.php');">
                <p style='margin-left:20pt; margin-right:20pt; font-family:verdana; font-size:8pt; color:#333300' align="center">
                    <br>
                    Para acessar informe o Siape e senha!!!
                <center>
                    <font size=1>
                    Siape&nbsp;&nbsp;<br>
                    <input type="text" name="lSiape" class="caixa" size="7" maxlength="7" onkeyup="javascript:ve(this.value);" >
                    <br>
                    Senha&nbsp;<br>
                    <input type="password" class="centro" name="lSenha" size="10" maxlength="8" onkeyup="javascript:ve(this.value);">
                    </font>
                    <br>
                    <img src="<?= _DIR_INC_; ?>imgGera.php"><br>
                    <font size=1>Digite os caracteres da figura acima.&nbsp;</font><br>
                    <input name="txtImagem" type="text" class="centro" size="9" maxlength="3">
                    <br>
                    <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
                    </p>
                    </form>
                    </fieldset>
                </center>
                </body>
                </html>
