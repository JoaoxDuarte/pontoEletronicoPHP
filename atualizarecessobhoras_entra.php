<?php
// conexao ao banco de dados funcoes diversas
include_once( "config.php" );

verifica_permissao('sLog');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Tabelas » Manutenção Ocorrências ");
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setOnLoad("javascript: if($('#siape')) { $('#siape').focus() };");
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Recalcular compensa&ccedil;&atilde;o de recesso");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function validar()
    {
        var siape = $('#siape');
        
        if (siape.val().length < 6)
        {
            alert('O siape do servidor é obrigatório!!');
            document.form1.siape.focus();
            return false;
        }
        return true;
    }
</script>

<form action="atualizarecessobhoras_demo.php" method="post" id="form1" name="form1" onSubmit="return validar()">
    <p align="center"><h3>
        <input type="hidden" id='an' name="an" value="<?= date('Y'); ?>">
        <div align="center">
            <table width="9%" height="46" border="1" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
                <tr>
                    <td width="122" height="44" align="center" >
                        <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">Siape:</font></p>
                        <p align="center" style="margin-top: 0; margin-bottom: 0">
                            <input name="siape" type="text" class='alinhadoAoCentro' id="siape" title="Informe o siape do servidor" size="9" maxlength="7">
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <p align="center" style="word-spacing: 0; margin-top: 0; margin-bottom: 0">&nbsp;</p>
        <p align="center" style="word-spacing: 0; margin: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
