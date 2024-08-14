<?php
// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// dados via formulario
$matricula = anti_injection($_POST['pSiape']);

// instancia o banco de dados
$oDBase = new DataBase('PDO');

$novamatricula = getNovaMatriculaBySiape($matricula);
// pesquisa servidor
$oDBase->query("SELECT nome_serv FROM servativ WHERE mat_siape = :siape",
    array(
        array( ':siape', $novamatricula, PDO::PARAM_STR ),
    ));
$nome = $oDBase->fetch_object()->nome_serv;

if (!$nome)
{
    mensagem("Matrícula do servidor não encontrda!", null, 1);
}

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Cadastro » Gerencial » Manter Histórico Função » Formulário');
$oForm->setOnLoad("$('#novafuncao').focus();");
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Manutenção de Histórico de Função - Formulário");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script language="javascript">
function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var Nnum1  = $('#Nnum1');
    var Nnum2  = $('#Nnum2');
    var Nnum3  = $('#Nnum3');
    var Nnum4  = $('#Nnum4');
    var inicio = $('#inicio');
    var fim    = $('#fim');
    var Ndata1 = $('#Ndata1');
    var Ndata2 = $('#Ndata2');
    var Ndata3 = $('#Ndata3');
    var Ndata4 = $('#Ndata4');

    if (Nnum1.val() != '' && $.isNumeric(Nnum1.val()) === false)
    {
        oTeste.setMsg("Número da Portaria de nomeação/designação inválido, o campo só permite números!",Nnum1);
    }

    if (Nnum2.val() != '' && $.isNumeric(Nnum2.val()) === false)
    {
        oTeste.setMsg("Número da publicação da Portaria de nomeação/designação inválido, o campo só permite números!", Nnum2);
    }

    if (Nnum3.val() != '' && $.isNumeric(Nnum3.val()) === false)
    {
        oTeste.setMsg("Número da Portaria de exoneração/dispensa inválido, o campo só permite números!", Nnum3);
    }

    if (Nnum4.val() != '' && $.isNumeric(Nnum4.val()) === false)
    {
        oTeste.setMsg("Número da publicação da Portaria de exoneração/dispensa inválido, o campo só permite números!", Nnum4);
    }

    // VALIDAÇÃO DAS DATAS

    if (inicio.val() != '' && data_valida(inicio.val()) == false)
    {
        oTeste.setMsg('Data de Início Inválida', inicio);
    }

    if (fim.val() != '' && data_valida(fim.val()) == false)
    {
        oTeste.setMsg('Data de Fim Inválida', fim);
    }

    if (Ndata1.val() != '' && data_valida(Ndata1.val()) == false)
    {
        oTeste.setMsg('Data de Portaria de nomeação/designação Inválida!', Ndata1 );
    }

    if (Ndata2.val() != '' && data_valida(Ndata2.val()) == false)
    {
        oTeste.setMsg('Data de Publicação Inválida!', Ndata2 );
    }

    if (Ndata3.val() != '' && data_valida(Ndata3.val()) == false)
    {
        oTeste.setMsg('Data de Portaria de exoneração/dispensa Inválida!', Ndata3 );
    }

    if (Ndata4.val() != '' && data_valida(Ndata4.val()) == false)
    {
        alert('Data de publicação da Portaria de exoneração/dispensa Inválida!', Ndata4 );
    }
}
</script>


<form method="POST" action="grava_inclui_funcserv.php" id="form1" name="form1" onSubmit="return verificadados()">
    <input type='hidden' name='modo' value='4'>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="16%" height="38">Matr&iacute;cula:
                <input name="matricula" type="text" class='caixa' id="matricula2" value='<?= tratarHTML($matricula); ?>' size="7" readonly>
            </td>
            <td height="38" colspan="4">Nome:
                <input name="nome" type="text" class='caixa' id="nome" value='<?= tratarHTML($nome); ?>' size="50" readonly>
            </td>
        </tr>
        <tr>
            <td height="38" colspan="2">
                <font color="#000000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                Fun&ccedil;&atilde;o:
                <select class="drop" size="1" name="novafuncao"  id="novafuncao">
                    <?php
                    // Tabela de funcoes
                    $oDBase->query("SELECT num_funcao AS id, desc_func AS descricao FROM tabfunc ORDER BY num_funcao");
                    while ($campo = $oDBase->fetch_object())
                    {
                        echo "<option value='" . tratarHTML($campo->id) . "'";
                        if ($campo->id == $numfuncao)
                        {
                            echo " selected";
                        }
                        echo " >" . tratarHTML($campo->id) . " - " . tratarHTML($campo->descricao) . "</option>";
                    }
                    // Fim da tabela de funcoes
                    ?>
                </select>
                </font>
            </td>
            <td>
                In&iacute;cio de exerc&iacute;cio:
                <input name="inicio" type="text" class='caixa' id="inicio" onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($inicio); ?>" size="10" maxlength='10' >
            </td>
            <td width="19%">
                Fim de exerc&iacute;cio:
                <input name="fim" type="text" class='caixa' id="fim" onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($fim); ?>" size="10" maxlength='10'>
            </td>
            <td width="24%">
                <font color="#000000">Situa&ccedil;&atilde;o ocupante:</font><font color="#000000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                <select class="drop" size="1" name="ocupacao" >
                    <option value="V"<?= ($ocupacao == "V" ? " selected" : ""); ?>>Selecione a Situação</option>
                    <option value="T"<?= ($ocupacao == "T" ? " selected" : ""); ?>>TITULAR</option>
                    <option value="S"<?= ($ocupacao == "S" ? " selected" : ""); ?>>SUBSTITUTO</option>
                    <option value="R"<?= ($ocupacao == "R" ? " selected" : ""); ?>>INTERINO</option>
                </select>
                </font>
            </td>
        </tr>
        <tr>
            <td height="42" colspan="2">
                <p>Portaria de nomea&ccedil;&atilde;o/designa&ccedil;&atilde;o:</p>
            </td>
            <td  width="20%" >
                <p>N&uacute;mero:
                    <input name="Nnum1" type="text" class='caixa' id="Nnum1" value="<?= tratarHTML($num1); ?>"  size="9" maxlength='9'>
                </p>
            </td>
            <td colspan="2">
                <p>Data:
                    <input name="Ndata1" type="text" class='caixa' id="Ndata12" onKeyPress="formatar(this, '##/##/####')" value="<?= $data1; ?>" size="10" maxlength='10'>
                </p>
            </td>
        </tr>
        <tr>
            <td height="42" colspan="2" class="corpo">
                <p>Publica&ccedil;&atilde;o:
                    <select name="publicacao" id="publicacao">
                        <option value="V" >Selecione</option>
                        <option value="DO" <?= ($publicacao == "DO" ? " selected" : ""); ?>>DO</option>
                        <option value="BSL"<?= ($publicacao == "BSL" ? " selected" : ""); ?>>BSL</option>
                    </select>
                </p>
            </td>
            <td class="corpo">
                <p>N&uacute;mero:
                    <input name="Nnum2" type="text" class='caixa' id="Nnum2" value="<?= $num2; ?>"  size="9" maxlength='9'>
                </p>
            </td>
            <td colspan="2">
                <p>Data:
                    <input name="Ndata2" type="text" class='caixa' id="Ndata22" onKeyPress="formatar(this, '##/##/####')" value="<?= $data2; ?>" size="10" maxlength='10'>
                </p>
            </td>
        </tr>
        <tr>
            <td height="43" colspan="2">
                <p>Portaria de exonera&ccedil;&atilde;o/dispensa:</p>
            </td>
            <td>
                <p>N&uacute;mero:
                    <input name="Nnum3" type="text" class='caixa' id="Nnum3" value="<?= tratarHTML($num3); ?>"  size="9" maxlength='9'>
                </p>
            </td>
            <td colspan="2">
                <p>Data:
                    <input name="Ndata3" type="text" class='caixa' id="Ndata12" onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($data3); ?>" size="10" maxlength='10'>
                </p>
            </td>
        </tr>
        <tr>
            <td height="43" colspan="2" class="corpo">
                <p>Publica&ccedil;&atilde;o:
                    <select name="publicacao2" id="publicacao2">
                        <option value="V" >Selecione</option>
                        <option value="DO" <?= ($publicacao == "DO" ? " selected" : ""); ?>>DO</option>
                        <option value="BSL"<?= ($publicacao == "BSL" ? " selected" : ""); ?>>BSL</option>
                    </select>
                </p>
            </td>
            <td class="corpo">
                <p>N&uacute;mero:
                    <input name="Nnum4" type="text" class='caixa' id="Nnum4" value="<?= tratarHTML($num4); ?>"  size="9" maxlength='9'>
                </p>
            </td>
            <td colspan="2">
                <p>Data:
                    <input name="Ndata4" type="text" class='caixa' id="Ndata22" onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($data4); ?>" size="10" maxlength='10'>
                </p>
            </td>
        </tr>
    </table>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0">
        <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
    </p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
