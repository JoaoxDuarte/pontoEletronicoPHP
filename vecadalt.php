<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sLog");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setJS(_DIR_JS_ . 'cal2.js');
$oForm->setJS(_DIR_JS_ . 'cal_conf2.js');
$oForm->setOnLoad("javascript: if($('#inicio')) { $('#inicio').focus() };");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Consulta Cadastro Alterado");

$oForm->setObservacaoTopo('O per&iacute;odo a ser consultado n&atilde;o pode ser superior a 30 dias.');


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function validar()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var inicio = $('#inicio');
        var dfinal = $('#dfinal');

        if (inicio.val().length < 10)
        {
            oTeste.setMsg('É obrigatório informar a data de ínício com 10 caracteres!', inicio);
        }
        if (dfinal.val().length < 10)
        {
            oTeste.setMsg('É obrigatório informar a data final com 10 caracteres!', dfinal);
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        return bResultado;
    }

    function ve(parm1)
    {
        var pSiape = $('#pSiape');
        var mes    = $('#mes');
        var ano    = $('#ano');

        if (pSiape.val().length == 7)
        {
            mes.focus();
        }
        if (mes.val().length == 2)
        {
            ano.focus();
        }
    }
</script>

<form action="cadsalterados.php" method="post" id="form1" name="form1" onSubmit="return validar()">
    <input type="hidden" name="an" value="<?= date('Y'); ?>">
    <div align="center">
        <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="39%" id="AutoNumber1">
            <tr>
                <td height="42" align="center" >
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <font size="2">Período</font>
                        <small><a href="javascript:showCal('perrelcon1')"><img src='<?= _DIR_IMAGEM_; ?>calendario.gif' border='0'  width='15' height='15'></a></small>
                        <input class="caixa" type="text" id="inicio" name="inicio" OnKeyUp="mascara_data(this.value, this.name, '0')" size="10" maxlength="10" value="" onkeypress='if (event.keyCode < 48 || event.keyCode > 57)
                                    event.returnValue = false;'>
                        <font size="2">&nbsp;a</font>&nbsp;<small><a href="javascript:showCal('perrelcon2')"><img src='<?= _DIR_IMAGEM_; ?>calendario.gif' border='0' width='15' height='15'></a></small>
                        <input class="caixa" type="text" id="final" name="final" OnKeyUp="mascara_data(this.value, this.name, '0')" size="10" maxlength="10" value="" onkeypress='if (event.keyCode < 48 || event.keyCode > 57)
                                    event.returnValue = false;'>
                        &nbsp;
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
