<?php
//Rotina a ser rodada sempre ultimo dia de cada mes quando ativado o mes

include_once("config.php");

verifica_permissao("tabela_prazos");

$qcinzas = anti_injection($_REQUEST['qcinzas']);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho("Tabelas");
$oForm->setLargura('950');
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Manutenção da Tabela de Quarta Feira de Cinzas");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function verificadados()
    {
        if (document.form1.qcinzas.value.length == 0)
        {
            alert("A data é obrigatória !");
            document.form1.qcinzas.focus();
            return false;
        }
    }
</script>


<form method="POST" action="gravahorario.php?modo=9" onsubmit="return verificadados()" id="form1" name="form1" >
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td height="25" class="corpo"><div align="center"></div></td>
            <td height="25" class="corpo"><div align="center">Dia</div></td>
            <td class="corpo">&nbsp;</td>
        </tr>
        <tr>
            <td width="34%" height="25" class="corpo"><div align="center"> </div></td>
            <td width="16%" class="corpo"><div align="center">
                    <input name="qcinzas" type="text" class="centro" id="qcinzas" value="<?= tratarHTML($qcinzas); ?>" OnKeyPress="formatar(this, '##/##/####')"  size="10" maxlength="10">
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
