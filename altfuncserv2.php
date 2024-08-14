<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sRH e sTabServidor");

// isntancia o banco de dados
$oDBase = new DataBase('PDO');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Cadastro » Gerencial » Alterar registro');
/*$oForm->setCSS(_DIR_CSS_ . 'estilos.css');*/
$oForm->setCSS('
	<style type="text/css">
		TABLE.1 {border-top:1px solid #808000;}
		TABLE {border-top: 0px solid #808000;border-left: 1px solid #808000;}
		TR TD {border-bottom: 1px solid #808000;border-right: 1px solid #808000;}
		P {font-size:10pt;font-family:Tahoma, Verdana;margin:3px;}
		INPUT.caixa {font-family:verdana, arial, helvetica;font-size:8pt;color:#808080;border:1 solid #696969;background-color:#f7f3f7;margin:3px;}
		SELECT {font-family:verdana, arial, helvetica;font-size:8pt;color:#808080;border:1 solid #CCE6FF;padding-left:1px;padding-top:1px;padding-bottom:1px;background-color:#f7f3f7;}
	</style>
	');
$oForm->setJS(_DIR_JS_ . 'desativa_teclas_f.js');
$oForm->setJS(_DIR_JS_ . 'funcoes.js');
$oForm->setJS(_DIR_JS_ . 'phpjs.js');
//	$oForm->setJS( 'altfuncserv2.js' );
$oForm->setOnLoad("$('#inicio').focus();");
$oForm->setSeparador(5);

$oForm->setSubTitulo("Altera&ccedil;&atilde;o de Dados de Ocupante de Fun&ccedil;&atilde;o");

//$oForm->setObservacaoTopo("Matrícula e situa&ccedil;&atilde;o do servidor");
// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


$matricula = anti_injection($_POST['matricula']);
$sit       = anti_injection($_POST['sit']);

$matricula = getNovaMatriculaBySiape($matricula);

$d1   = "SELECT cad.nome_serv, chf.num_funcao, chf.dt_inicio, chf.dt_fim, chf.num_doc1, chf.dt_doc1, chf.num_doc2, chf.dt_doc2, chf.cod_doc2, chf.resp_lot FROM ocupantes AS chf LEFT JOIN servativ AS cad ON chf.mat_siape = cad.mat_siape WHERE chf.mat_siape = '$matricula' AND sit_ocup = '$sit' AND dt_fim = '0000-00-00'";

$oDBase->setMensagem("Problemas no acesso a Tabela OCUPANTES DE FUNÇÕES (E000114.".__LINE__.").");
$res1 = $oDBase->query($d1);
$r1   = $oDBase->num_rows($res1);

if ($r1 == 0)
{
    mensagem("Servidor não ocupa função!", null, 1);
}

$oChefia    = $oDBase->fetch_object();
$nome       = $oChefia->nome_serv;
$numfunc    = $oChefia->num_funcao;
$inicio     = databarra($oChefia->dt_inicio);
$fim        = databarra($oChefia->dt_fim);
$Nnum1      = $oChefia->num_doc1;
$Ndata1     = databarra($oChefia->dt_doc1);
$Nnum2      = $oChefia->num_doc2;
$Ndata2     = databarra($oChefia->dt_doc2);
$publicacao = $oChefia->cod_doc2;
$respon     = $oChefia->resp_lot;
?>

<script language="javascript">
    function verificadados()
    {

        if (document.form1.Nnum1.value != '' && $.isNumeric(document.form1.Nnum1.value) === false)
        {
            alert('Número do documento inválido, o campo só permite números!');
            document.form1.Nnum1.focus();
            return false;
        }
        if (document.form1.Nnum2.value != '' && $.isNumeric(document.form1.Nnum2.value) === false)
        {
            alert('Número do documento inválido, o campo só permite números!');
            document.form1.Nnum2.focus();
            return false;
        }


        if (data_valida(document.form1.inicio.value) == false)
        {
            alert('Data de Inícioaass Inválida');
            document.form1.matricula.focus();
            return false;
        }

        if (data_valida(document.form1.Ndata1.value) == false)
        {
            alert('Data de Portaria de nomeação/designação Inválida')
            document.form1.Ndata1.focus();
            return false;
        }
        if (data_valida(document.form1.Ndata2.value) == false)
        {
            alert('Data de Publicação Inválida')
            document.form1.Ndata2.focus();
            return false;
        }


        if (document.form1.matricula.value.length < 7)
        {
            alert('Favor digite a matrícula com 7 digitos');
            document.form1.matricula.focus();
            return false;
        }

        if (document.form1.inicio.value.length < 10)
        {
            alert('Favor digite a data da nomeação no formato dd/mm/aaaa');
            document.form1.inicio.focus();
            return false;
        }
        if (document.form1.Ndoc1.value.length < 2)
        {
            alert('Favor digite o documento com 2 dígitos');
            document.form1.Ndoc1.focus();
            return false;
        }
        if (document.form1.Ndoc2.value.length < 2)
        {
            alert('Favor digite o documento com 2 dígitos');
            document.form1.Ndoc2.focus();
            return false;
        }

        if (document.form1.Nnum1.value.length == 0)
        {
            alert('Favor digite o número do documento');
            document.form1.Nnum1.focus();
            return false;
        }

        if (document.form1.Nnum2.value.length == 0)
        {
            alert('Favor digite o número do documento');
            document.form1.Nnum2.focus();
            return false;
        }



        if (document.form1.Ndata1.value.length < 10)
        {
            alert('Favor digite a data do documento no formato dd/mm/aaaa');
            document.form1.Ndata1.focus();
            return false;
        }
        if (document.form1.Ndata2.value.length < 10)
        {
            alert('Favor digite a data do documento no formato dd/mm/aaaa');
            document.form1.Ndata2.focus();
            return false;
        }
    }
</script>

<form method="POST" action="grava_inclui_funcserv.php" id="form1" name="form1" onSubmit="return verificadados()">
    <input type='hidden' name='modo' value='5'>
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="14%" height="34">Matr&iacute;cula:
                <input name="matricula" type="text" class='caixa' id="matricula2" value='<?= tratarHTML(removeOrgaoMatricula($matricula)); ?>' size="7" readonly>
            </td>
            <td colspan="4">Nome:
                <input name="nome" type="text" class='caixa' id="nome" value='<?= tratarHTML($nome); ?>' size="50" readonly>
            </td>
        </tr>
        <tr>
            <td colspan="2"><font color="#000000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                <?php
                $oDBase->setMensagem("Problemas no acesso a Tabela FUNÇÕES (E000115.".__LINE__.").");
                $oDBase->query("SELECT DESC_FUNC FROM tabfunc WHERE NUM_FUNCAO = '$numfunc' ");
                $descfunc   = $oDBase->fetch_object()->DESC_FUNC;
                ?>
                <input name="func" type="text" class='caixa' id="func" value="<?= tratarHTML($numfunc . ' - ' . $descfunc); ?>" size="60" readonly >
                <input type='hidden' name='numfunc' value='<?= tratarHTML($numfunc); ?>'>
                </font>
            </td>
            <td colspan="2" >In&iacute;cio de exerc&iacute;cio:
                <input name="inicio" type="text" class='caixa' id="inicio" onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($inicio); ?>" size="10" maxlength='10' >
            </td>
            <td width="25%">
                <font color="#000000">Situa&ccedil;&atilde;o ocupante:</font>
                <font color="#000000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                <select class="drop" size="1" name="ocupacao" >
                    <option value="V"<?= ($sit == "V" ? " selected" : ""); ?>>Selecione a Situação</option>
                    <option value="T"<?= ($sit == "T" ? " selected" : ""); ?>>TITULAR</option>
                    <option value="S"<?= ($sit == "S" ? " selected" : ""); ?>>SUBSTITUTO</option>
                    <option value="R"<?= ($sit == "R" ? " selected" : ""); ?>>INTERINO</option>
                </select>
                </font>
            </td>
        </tr>
        <tr>
            <td colspan="2"> <p>Portaria de nomea&ccedil;&atilde;o/designa&ccedil;&atilde;o:</p></td>
            <td  width="18%" >
                <p>N&uacute;mero:
                    <input name="Nnum1" type="text" class='caixa' id="Nnum1" value="<?= tratarHTML($Nnum1); ?>"  size="9" maxlength='9'>
                </p>
            </td>
            <td colspan="2">
                <p>Data:
                    <input name="Ndata1" type="text" class='caixa' id="Ndata12" onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($Ndata1); ?>" size="10" maxlength='10'>
                </p>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="corpo">
                <p>Publica&ccedil;&atilde;o:
                    <select name="publicacao" id="publicacao">
                        <option value="DO" <?= ($publicacao == "DO" ? " selected" : ""); ?>>DO</option>
                        <option value="BSL"<?= ($publicacao == "BSL" ? " selected" : ""); ?>>BSL</option>
                    </select>
                </p>
            </td>
            <td class="corpo">
                <p>N&uacute;mero:
                    <input name="Nnum2" type="text" class='caixa' id="Nnum2" value="<?= tratarHTML($Nnum2); ?>"  size="9" maxlength='9'>
                </p>
            </td>
            <td colspan="2">
                <p>Data:
                    <input name="Ndata2" type="text" class='caixa' id="Ndata22" onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($Ndata2); ?>" size="10" maxlength='10'>
                </p>
            </td>
        </tr>
    </table>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px; margin-bottom: 0">&nbsp;</p>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px;">
        <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
    </p>
    <?php
    //if($r > 0)
    //{
    //	print "<br><center><font color='#800000' face='Tahoma' size='4'>Há um ocupante nessa função </font></center> <br>";
    //}
    //if($r1 > 0)
    //{
    //	print "<br><center><font color='#800000' face='Tahoma' size='4'>O servidor já ocupa outra função </font></center> <br>";
    //}
    //print "<center> <a href='incfuncserv.php'> <font color='#2A5FFF' face = 'Tahoma' size = '2'> voltar </font> </a> ";
    //}
    ?>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
