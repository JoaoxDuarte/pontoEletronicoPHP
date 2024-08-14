<?php
include_once( "config.php" );

verifica_permissao("logado");

$id = $_REQUEST['id'];

if ($_SESSION["sTabPrazo"] == "S")
{
    header("Location: alteraferiado.php?id=$id");
    exit();
}
elseif ($_SESSION["sTabPrazo"] == "N" && substr($_SESSION["sLotacao"], 2, 6) == "150700" && $_SESSION['sRelGer'] == "S") //
{
    /////
}
else
{
    header("Location: acessonegado.php");
    exit();
}

// instancia banco de dados
$oDBase = new DataBase('PDO');

$oDBase->setMensagem("Problemas no acesso a Tabela FERIADOS (E000110.".__LINE__.").");
$oDBase->query("SELECT dia, mes, `desc`, tipo, lot, codmun, data_feriado, base_legal FROM feriados WHERE `id`= :id ", array(
    array( ':id', $id, PDO::PARAM_INT )
));
$oDados = $oDBase->fetch_object();
$dia    = $oDados->dia;
$mes    = $oDados->mes;
$des    = $oDados->desc;
$tipo   = $oDados->tipo;
$lot    = $oDados->lot;
$mun    = $oDados->codmun;
$dtfer  = $oDados->data_feriado;
$flegal = $oDados->base_legal;

$lotusu = $_SESSION["sLotacao"];


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Tabelas » Feriados");

$oForm->setSubTitulo("Manutenção da Tabela de Feriados");

?>
<script language="javascript">
    function verificadados()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var dia = $('#dia');
        var mes = $('#mes');

        var sMsgErro = "Digite:\n";

        if (dia.val().length == 0)
        {
            oTeste.setMsg(sMsgErro + '.O dia é obrigatório!', dia);
        }
        if (dia.val().length == 1)
        {
            oTeste.setMsg(sMsgErro + '.Informe o Dia com 2 caracteres!', dia);
        }
        if (dia.val().length > 31)
        {
            oTeste.setMsg(sMsgErro + '.Dia inválido!', dia);
        }

        if (mes.val().length == 0)
        {
            oTeste.setMsg(sMsgErro + '.O mês é um campo obrigatório!', mes);
        }
        if (mes.val().length > 12)
        {
            oTeste.setMsg(sMsgErro + '.Mês inválido!', mes);
        }
        if (mes.val().length == 1)
        {
            oTeste.setMsg(sMsgErro + '.Informe o Mês com 2 caracteres!', mes);
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        return bResultado;
    }
</script>

<form method="POST" action="gravaalteraferiado.php" onsubmit="return verificadados()" id="form1" name="form1" >
    <input name="id" type="hidden" class="centro" id="id" value="<?= tratarHTML($id); ?>" size="4" readonly="4" >
    <input name="modo" type="hidden" class="centro" id="modo" value="1" size="2" readonly="2" >
    <div align="center">
        <table border="0" width="89%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="6%"  align="center" height="25" class="corpo"><div align="center">Id</div></td>
                <td width="5%"  align="center" class="corpo"><div align="center">Dia</div></td>
                <td width="6%"  align="center" class="corpo">M&ecirc;s</td>
                <td width="52%"height="25" align="center" class="corpo" p><div align="center">Descri&ccedil;&atilde;o do Feriado</div></td>
                <td width="11%" align="center"height="25"class="corpo">Fundamento legal</td>
                <td width="9%"  align="center"height="25" class="corpo">&nbsp;</td>
            </tr>
            <tr>
                <td width="6%"  p align="center" class="corpo">
                    <div align="center"><input name="id" type="text" class="centro" id="dia5" value="<?= tratarHTML($id); ?>" size="4" readonly></div>
                </td>
                <td height="25" class="corpo">
                    <div align="center"><input name="dia" type="text" class="centro" id="dia4" value="<?= tratarHTML($dia); ?>" size="4" maxlength="2" ></div>
                </td>
                <td height="25" class="corpo">
                    <div align="center"><input name="mes" type="text" class="centro" id="mes4" value="<?= tratarHTML($mes); ?>" size="4" maxlength="2" ></div>
                </td>
                <td class="corpo">
                    <div align="center"><input name="sDescricao" type="text" class="caixa" id="sDescricao" value="<?= tratarHTML($des); ?>" size="80" readonly="80"></div>
                </td>
                <td class="corpo" height="25">
                    <div align="center"><input name="flegal" type="text" class="caixa" id="flegal2" value="<?= tratarHTML($flegal); ?>" size="30" maxlength="30"></div>
                </td>
                <td class="corpo" height="25"><div align="center"> </div></td>
            </tr>
            <tr>
                <td height="25" colspan="3" align="center" class="corpo"  p>&nbsp;</td>
                <td class="corpo"><div align="center"></div></td>
                <td class="corpo" height="25">&nbsp;</td>
                <td class="corpo" height="25">&nbsp;</td>
            </tr>
        </table>
    </div>
    <p align="center" style="word-spacing:0px;line-height:100%;margin-left:0px;margin-right:0px;margin-top:6px;margin-bottom:0px;">
        <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
    </p>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px;">&nbsp;</p>
    <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px;"><font size="1">Obs:
        Esta tela destina-se a manuten&ccedil;&atilde;o dos feriados que tem suas
        datas alteradas anualmente pelas Prefeituras e Governos Estaduais e Distrital.<br>
        Para alterar localize o feriado pela data vigente no ano anterior e modifique
        os campos dia e mes informando o fundamento legal da altera&ccedil;&atilde;o.</font></p>
    <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px;"><font size="1">Os
        feriados Nacionais ser&atilde;o mantidos pela Administra&ccedil;&atilde;o
        Central ficando a cargo dos Servi&ccedil;os de Recursos Humanos das Superintend&ecirc;ncias
        a manuten&ccedil;&atilde;o dos feriados municipais e estaduais de sua jurisdi&ccedil;&atilde;o.<br>
        <br>
        A inclus&atilde;o de novos feriados e a exclus&atilde;o de feriados ficar&atilde;o
        a cargo da Administra&ccedil;&atilde;o Central.<br>
        Exemplo: O feriado de Corpus Christi em 2010 foi em 03/06 utilizar essa data
        para localizar o feriado, uma vez localizado em 2011 ser&aacute; em 23/06
        preencher os campos dia e mes com esses valores bem como a fundamenta&ccedil;&atilde;o
        legal.</font></p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
