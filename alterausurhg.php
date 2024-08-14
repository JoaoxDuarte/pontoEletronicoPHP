<?php
include_once("config.php");

verifica_permissao("administrador_e_chefe_de_rh");

$siape = addslashes($_REQUEST['siape']);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Utilitários » Usuários » Alterar/Excluir usuários');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setLargura("790px");
$oForm->setSeparador(0);
$oForm->setSubTitulo("Alteração de Usuários de Recursos Humanos");
$oForm->setSeparadorTopo(10);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// instancia o banco de dados
$oDBase = new DataBase('PDO');

// dados do setor
// verifica se o usuário existe
$oDBase->setMensagem("Problemas no acesso a Tabela USUÁRIOS (E000113.".__LINE__.").");
$oDBase->query("SELECT a.siape, b.nome_serv AS nome, b.cod_lot AS setor, b.cod_uorg, c.upag, c.descricao, a.senha, a.acesso FROM usuarios AS a LEFT JOIN servativ AS b ON a.siape=b.mat_siape LEFT JOIN tabsetor AS c ON b.cod_lot=c.codigo WHERE a.siape='$siape' ");
$numrows = $oDBase->num_rows();

if ($numrows >= 1)
{
    $oUsuario  = $oDBase->fetch_object();
    $siape     = $oUsuario->siape;
    $nome      = $oUsuario->nome;
    $ssenha    = $oUsuario->senha;
    $lotacao   = $oUsuario->setor;
    $upag      = $oUsuario->upag;
    $descricao = $oUsuario->descricao;
    $sTripa    = $oUsuario->acesso;

    if (substr($lotacao, 2, 1) != 7 && substr($lotacao, 5, 1) != 7)
    {
        //header("Location: mensagem2.php?modo=41");
        //break;
        mensagem("A matricula informada não é de servidor lotado em RH!", null, 1);
    }
    elseif ($_SESSION['upag'] != $upag)
    {
        //header("Location: mensagem2.php?modo=41");
        //break;
        mensagem("Servidor de outra UPAG!", null, 1);
    }

    // pega as permissoes
    for ($i = 0; $i <= 44; $i = $i + 1)
    {
        $c[$i] = substr($sTripa, $i, 1);
    }
    ?>
    <style>
        .ft10px { font-size: 10px; }
        .ftTahoma { font-family: Tahoma; }
        .ftVerdana { font-family: Verdana; }
    </style>
    <form method="POST" action="confirmausuario.php" id="form1" name="form1">
        <input type="hidden" id="modo" name="modo" value="3">
        <table align="center" border="0" width="80%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="33%" align="right" class="ftVerdana ft10px">Siape</td>
                <td width="3%">&nbsp;</td>
                <td width="36%"><input type="text" class="caixa" name="lSiape" size="7" value="<?= tratarHTML($siape); ?>"></td>
            </tr>
            <tr>
                <td width="33%" align="right" class="ftVerdana ft10px">Nome</td>
                <td width="3%">&nbsp;</td>
                <td width="36%"><input type="text" class="caixa" name="lNome" size="60" value="<?= tratarHTML($nome); ?>"></td>
            </tr>
            <tr>
                <td width="33%" align="right" class="ftVerdana ft10px">Lotação</td>
                <td width="3%">&nbsp;</td>
                <td width="36%"><input type="text" name="lSetor" class="caixa" id="lSetor" value="<?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?>" size="70"></td>
            </tr>
            <tr>
                <td colspan='3'>&nbsp;</td>
            </tr>
            <tr>
                <td width="33%" align="right">&nbsp;</td>
                <td width="39%" colspan='2'>
                    <p align="center">
                    <fieldset style='width: 35%;'>
                        <legend>Acesso</legend>
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="40%" class='ftTahoma ft10px'>&nbsp;</td>
                                <td width="60%" class='ftTahoma ft10px'>
                                    <input type="radio" name="C[]" value="00" <?= (($c[0] == "N" || $c[9] == "N") ? " checked " : ""); ?>>&nbsp;Bloquear<br>
                                    <input type="radio" name="C[]" value="01" <?= (($c[0] == "S" && $c[9] == "N") ? " checked " : ""); ?>>&nbsp;Consulta<br>
                                    <input type="radio" name="C[]" value="09" <?= (($c[9] == "S") ? " checked " : ""); ?>>&nbsp;Altera&ccedil;&atilde;o
                            </tr>
                        </table>
                    </fieldset>
                    </p>
                </td>
            </tr>
            <tr>
        </table>

        <p><br></p>
        <p style="word-spacing: 0; margin: 0" align="center"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
    </form>
    <span style='font-family:verdana; font-size:8pt'>&nbsp;&nbsp;<i></span></font><br>
    <?php
}
else
{
    mensagem("Servidor não encontrado!", null, 1);
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
