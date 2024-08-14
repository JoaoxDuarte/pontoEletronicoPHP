<?php
//Rotina a ser rodada sempre ultimo dia de cada mes quando ativado o mes

include_once("config.php");

verifica_permissao("tabela_prazos");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho("Tabelas");
$oForm->setCSS(_DIR_CSS_ . "estilos.css");
$oForm->setLargura('950');
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Manutenção da Tabela de Recesso");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function verificadados()
    {
        if (document.form1.inicio.value.length == 0)
        {
            alert("A data é obrigatória !");
            document.form1.inicio.focus();
            return false;
        }

        if (document.form1.fim.value.length < 0)
        {
            alert("A data é obrigatória  !");
            document.form1.fim.focus();
            return false;
        }
        return true;
    }
</script>

<form method="POST" action="gravahorario.php" onsubmit="return verificadados()" id="form1" name="form1" >
    <input type='hidden' id='modo' name='modo' value='12'>
    <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0"><font size="1" face="Tahoma" color="#FF0000"></font></p>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td height="25" class="corpo"><div align="center"></div></td>
            <td height="25" class="corpo"><div align="center">In&iacute;cio</div></td>
            <td class="corpo"> <div align="center">Fim</div></td>
            <td class="corpo">&nbsp;</td>
        </tr>
        <tr>
            <td width="34%" height="25" class="corpo"><div align="center"> </div></td>
            <td width="16%" class="corpo"><div align="center">
                    <input name="inicio" type="text" class="centro" id="inicio" value="<?= tratarHTML($hvi); ?>" OnKeyPress="formatar(this, '##/##/####')"  size="10" maxlength="10">
                </div>
            </td>
            <td width="17%" class="corpo"><div align="center">
                    <input name="fim" type="text" class="centro" id="fim" value="<?= tratarHTML($hvf); ?>"  onKeyPress="formatar(this, '##/##/####')" size="10" maxlength="10">
                </div>
            </td>
            <td width="33%" class="corpo">&nbsp;</td>
        </tr>
    </table>

    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
