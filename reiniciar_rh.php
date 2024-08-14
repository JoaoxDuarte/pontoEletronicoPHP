<?php
// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Reiniciar Senha');
//$oForm->setCSS( _DIR_CSS_ . 'estilos.css' );
//$oForm->setCSS( _DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css' );
$oForm->setDialogModal();
$oForm->setJS('reiniciar_rh.js');
$oForm->setOnLoad("$('#pSiape').focus();");
$oForm->setSeparador(30);
$oForm->setLogoExibe(($_SESSION['sHOrigem_1'] == "entrada.php"));
$oForm->setObservacaoTopo("<center><div style='width: 70%; text-align: justify;'>Para reiniciar a senha &eacute; necess&aacute;rio ter em m&atilde;os<br>o numero do cpf, da identifica&ccedil;&atilde;o &uacute;nica e da matricula siapecad que podem ser obtidos no contracheque.</div></center>");

$oForm->setSubTitulo("Reiniciar Senha");

$oForm->setObservacaoBase("<center><div style='width: 70%; text-align: justify;'>Após a reinicialização da senha, será encaminhado um email para o detentor da matrícula,<br>informando que sua senha foi reinicializada para a senha padrão (data de nascimento).</div></center>");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script type="text/javascript" src="reiniciar_rh.js"></script>
<script> var voltarOrigem = "<?= tratarHTML($_SESSION['sHOrigem_1']); ?>";</script>
<form method="POST" id="form1" name="form1" action='#'>
    <input type='hidden' id='tipo' name='tipo' value='<?= tratarHTML($tipo); ?>'>
    <div align="center">
        <p><b>Informe a matrícula siape do usuário</b></p>
        <p>
            <font size=1>
            <input type="text" id="pSiape" name="pSiape" class="centro" size="10" maxlength="7" onclick="alert('Informe o siape com 7 digitos inclusive 0 a esquerda')">
            </font>
            <br>
            <br>
            <br>
        <table border='0' align='center' cellpadding='0' cellspacing='0'>
            <tr>
                <td align='right'><?= botao('&nbsp;Ok&nbsp;', 'verificadados();'); ?></td>
                <td><img src='<?= _DIR_IMAGEM_; ?>transp1x1.gif' width='15px' border='0'></td>
                <td align='left'><?= botao('Voltar', 'javascript:window.location.replace("' . $_SESSION["sHOrigem_1"] . '");'); ?></td>
            </tr>
        </table>
        </p>
    </div>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
