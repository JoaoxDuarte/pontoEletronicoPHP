<?php
// pega o conteudo dos campos para alterar
include ("config.php");

// parametros
$siape = anti_injection($_REQUEST['pSiape']);

// banco de dados
$oTbDados = new DataBase('PDO');

$oTbDados->query("SELECT * FROM usuarios WHERE siape = '$siape' ");
$oUsuarios = $oTbDados->fetch_object();
$nRows     = $oTbDados->num_rows();

if ($nRows == 0)
{
    mensagem('Usuário não localizado!', 'reiniciar.php');
}

$oTbDados->query("SELECT a.nome_serv, a.cod_lot, b.descricao, a.mat_siape, a.mat_siapecad, a.ident_unica, a.cpf, a.email FROM servativ AS a LEFT JOIN tabsetor AS b ON a.cod_lot = b.codigo WHERE a.mat_siape = '$siape' AND a.excluido='N' ");
$oServativ         = $oTbDados->fetch_object();
$nome              = $oServativ->nome_serv;
$cpf               = $oServativ->cpf;
$lotacao           = $oServativ->cod_lot;
$lotacao_descricao = $oServativ->descricao;
$email             = $oServativ->email;
$siapecad          = $oServativ->mat_siapecad;
$idunica           = $oServativ->ident_unica;

// dados para reinicializar a senha
$_SESSION['sReiniciaSenha'] = $siape . '|' . $cpf . '|' . $idunica . '|' . $siapecad . '|' . $email;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Reiniciar Senha');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setDialogModal();
$oForm->setJS('reiniciar3_rh.js');
$oForm->setOnLoad("$('#cpf').focus();");
$oForm->setSeparador(30);
$oForm->setLogoExibe(($_SESSION['sHOrigem_1'] != "principal.php"));
$oForm->setObservacaoTopo("Para reiniciar senha informe os dados solicitados");

$oForm->setSubTitulo("Reiniciar Senha");

$oForm->setObservacaoBase("<center><div style='width: 70%; text-align: justify;'>Após a reinicialização da senha, será encaminhado um email para o detentor da matrícula,<br>informando que sua senha foi reinicializada para a senha padrão (data de nascimento).</div></center>");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script type="text/javascript" src="reiniciar3_rh.js"></script>
<script> var voltarOrigem = "<?= $_SESSION['sHOrigem_1']; ?>";</script>
<form method="POST" id="form1" name="form1" action="#">
    <p style="word-spacing: 0; margin: 0" align="center">
    <table align="center" border="1" width="500px" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699" >
        <tr>
            <td colspan="2" bgcolor="#CECE9D" height='25px' align='center'><font face="Verdana" size="2" color="#000000"><b>Dados do Usu&aacute;rio</b></font></td>
        </tr>
        <tr>
            <td width="17%" align="center" class='verdanaSize_1BP'>Usuário</td>
            <td width="83%" align="left" nowrap>&nbsp;<?= tratarHTML(substr($siape . ' - ' . $nome, 0, 50)); ?></td>
        </tr>
        <tr>
            <td width="17%" align="center" class='verdanaSize_1BP'>Lota&ccedil;&atilde;o</td>
            <td width="83%" align="left" nowrap>&nbsp;<?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></td>
        </tr>
        <tr>
            <td width="17%" height="28" align='center' class='verdanaSize_1BP'>CPF</font></div></td>
            <td width="83%" align="center"><input type="text" id="cpf" name="cpf" class="centro" size="25" maxlength="11" onkeyup="javascript:ve(this.value);" title="CPF deve conter 11 caracteres numéricos"></td>
        </tr>
        <tr>
            <td width="17%" height="28" align="center" class='verdanaSize_1BP' nowrap>&nbsp;Identifica&ccedil;&atilde;o &Uacute;nica&nbsp;</td>
            <td width="83%" align="center"><input type="text" id="idunica" name="idunica" class="centro" size="25" maxlength="9" onkeyup="javascript:ve(this.value);" title="Identificação Única deve conter 9 caracteres numéricos"></td>
        </tr>
        <tr>
            <td width="17%" height="28" align="center" class='verdanaSize_1BP' nowrap>Mat. Siapecad</td>
            <td width="83%" align="center"><input type="text" id="siapecad" name="siapecad" class="centro" size="25" maxlength="8" title="SIAPECAD deve conter 8 caracteres numéricos"></td>
        </tr>
        <tr>
            <td colspan="2" bgcolor="#CECE9D">&nbsp;</td>
        </tr>
    </table>
    <p style="word-spacing: 0; margin: 0">
        <br>
        <br>
    <table border='0' align='center' cellpadding='0' cellspacing='0'>
        <tr>
            <td align='right'><?= botao('&nbsp;Ok&nbsp;', 'verificadados();'); ?></td>
            <td><img src='<?= _DIR_IMAGEM_; ?>transp1x1.gif' width='15px' border='0'></td>
            <td align='left'><?= botao('Voltar', 'window.location.replace("reiniciar_rh.php");'); ?></td>
        </tr>
    </table>
</p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
