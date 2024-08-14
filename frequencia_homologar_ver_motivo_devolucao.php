<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $mat   = $dados[0];
}

$sLotacao = $_SESSION["sLotacao"];

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleção
$oDBase->query(" SELECT nome_serv, motidev FROM servativ WHERE mat_siape = '$mat' ");
$oCadastro        = $oDBase->fetch_object();
$nome             = trata_aspas($oCadastro->nome_serv);
$motivo_devolucao = trata_aspas($oCadastro->motidev);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Homologar » Motivo Devolução');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setLargura("900px");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Visualiza Motivo da Desomologação");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="#" id="form1" name="form1">
    <div align="center">
        <table width="99%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
            <tr>
                <td height="20" bgcolor="#DFDFBF"> <p align="center"><b>Siape</b></td>
                <td width="378" height="20" bgcolor="#DFDFBF"><div align="center"><b>Nome</b></div>
                    <div align="center"></div>
                    <div align="center"></div></td>
                <td width="95" height="20" bgcolor="#DFDFBF"><div align="center"><b>Lota&ccedil;&atilde;o</b></div></td>
            </tr>
            <tr>
                <td width="90">
                    <div align="center">
                        <input name="siape" type="text" class='centro' id="siape2" OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($mat); ?>" size="15" readonly='10' >
                    </div>
                </td>
                <td>
                    <div align="left">
                        <input name="nome" type="text" class='Caixa' id="nome" OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($nome); ?>" size="64" readonly='70' >
                    </div>
                </td>
                <td>
                    <div align="center">
                        <input name="lotacao" type="text" class='centro' id="lotacao" OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($sLotacao); ?>" size="15" readonly='10'  >
                    </div>
                </td>
            </tr>
        </table>
        <table width="56%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
            <tr>
                <td height="114"> <div align="center"> </div>
                    <div align="center">
                        <textarea name='just' cols='142' rows='8' id="textarea" readonly><?= tratarHTML($motivo_devolucao); ?></textarea>
                    </div>
                </td>
            </tr>
        </table>
        <p>&nbsp;</p>
    </div>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
