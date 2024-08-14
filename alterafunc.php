<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao('tabela_prazos');

$var1 = anti_injection($_GET['codigo']);

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// dados - fun��o
$oDBase->setMensagem("Problemas no acesso a Tabela FUN��ES (E000111.".__LINE__.").");
$oDBase->query("SELECT cod_funcao, desc_func, upag, cod_lot, resp_lot, ativo FROM tabfunc WHERE num_funcao = :num_funcao ", array(
    array(':num_funcao', $var1, PDO::PARAM_STR)
));
$oFuncao = $oDBase->fetch_object();

$descricao = $oFuncao->desc_func;
$codigo    = $oFuncao->cod_funcao;
$upag      = $oFuncao->upag;
$lot       = $oFuncao->cod_lot;
$ativo     = $oFuncao->ativo;
$resp      = $oFuncao->resp_lot;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setOnLoad("javascript: if($('#descricao')) { $('#descricao').focus() };");

$oForm->setSubTitulo("Mantuten��o da Tabela de Fun��es");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function verificadados()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var tCodigo    = $('#tCodigo');
        var sDescricao = $('#sDescricao');
        var sUorg      = $('#sUorg');
        var sUpag      = $('#sUpag');
        var sAtivo     = $('#sAtivo');

        if (tCodigo.val().length == 0)
        {
            oTeste.setMsg("O C�digo � obrigat�rio !", tCodigo);
        }
        if (tCodigo.val().length < 8)
        {
            oTeste.setMsg("O C�digo possui 8 n�meros !", tCodigo);
        }
        if (sDescricao.val().length == 0)
        {
            oTeste.setMsg("A Descricao � obrigat�ria !", sDescricao);
        }
        if (sUorg.val().length == 0)
        {
            oTeste.setMsg("a Uorg � um campo obrigat�rio !", sUorg);
        }
        if (sUpag.val().length == 0)
        {
            oTeste.setMsg("Upag � um campo obrigat�rio !", sUpag);
        }
        if (sAtivo.val().length == 0)
        {
            oTeste.setMsg("Ativo� um campo obrigat�rio !", sAtivo);
        }

        // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        return bResultado;
    }
</script>

<form method="POST" action="gravaalterafunc.php" onsubmit="return verificadados()" id="form1" name="form1" >
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="7%"  align="center" height="15" class="corpo">Numero</td>
            <td width="45%" align="center" height="25" class="corpo">Descri��o da Fun��o</td>
            <td width="9%"  align="center" height="25" class="corpo" p>Codigo</td>
            <td width="9%"  align="center" height="15" class="corpo">Lota��o</td>
            <td width="9%"  align="center" height="15" class="corpo">Upag</td>
            <td width="7%"  align="center" height="15" class="corpo">Ativo</td>
            <td width="14%" align="center" height="15" class="corpo">Respons�vel</td>
        </tr>
        <tr>
            <td class="left" height="15">
                <div align="center">
                    <input name="numfunc" type="text" class="caixa" id="numfunc" value="<?= tratarHTML($var1); ?>" size="10" maxlength="10" readonly>
                </div>
            </td>
            <td height="25" class="corpo">
                <input name="descricao" type="text" class="caixa" id="descricao" value="<?= tratarHTML($descricao); ?>" size="70" maxlength="70">
            </td>
            <td height="25" class="corpo">
                <div align="center">
                    <input name="codigo" type="text" class="centro" id="codigo" value="<?= tratarHTML($codigo); ?>" size="10" maxlength="10">
                </div>
            </td>
            <td class="corpo" height="15">
                <div align="center">
                    <input name="lot" type="text" class="centro" id="upag2" value="<?= tratarHTML($lot); ?>" size="10" maxlength="10">
                </div>
            </td>
            <td class="corpo" height="15">
                <div align="center">
                    <input name="upag" type="text" class="centro" id="upai2" value="<?= tratarHTML($upag); ?>" size="10" maxlength="10">
                </div></td>
            <td class="corpo" height="15">
                <div align="center">
                    <input name="ativo" type="text" class="centro" id="ativo" value="<?= tratarHTML($ativo); ?>" size="5" maxlength="5">
                </div></td>
            <td class="corpo" height="15">
                <div align="center">
                    <input name="resp" type="text" class="centro" id="resp" value="<?= tratarHTML($resp); ?>" size="4" maxlength="4">
                </div></td>
        </tr>
    </table>

    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px; margin-bottom: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>
<?php
// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
