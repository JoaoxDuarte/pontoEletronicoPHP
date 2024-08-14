<?php
include_once('config.php');

verifica_permissao('corrigir_acesso');

$matricula = anti_injection($_REQUEST['matricula']);
$modo      = anti_injection($_REQUEST['modo']);
$modo      = (empty($modo) ? '0' : $modo);

// instancia do BD
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Utilit�rios � Gestores � Reinicializar Senha do Usu�rio");
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setOnLoad("javascript: if($('#matricula')) { $('#matricula').focus() };");
$oForm->setSeparador(0);

// Topo do formul�rio
//
$oForm->setSubTitulo("Reinicializar a Senha do Usu�rio");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function verificadados(modo)
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var modo = (modo == null ? 0 : modo);
        var matricula = $('#matricula');

        var mensagem = '';

        // validacao do campo siape
        // testa o tamanho
        mensagem = validaSiape(matricula.val());
        if (mensagem != '')
        {
            oTeste.setMsg(mensagem, matricula);
        }

        // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        if (bResultado == true)
        {
            var destino = 'javascript:window.location.replace("gestao_trocasenha_usuario.php?modo=' + modo + '&matricula=' + matricula.val() + '");';
            $("#form1").attr("action", destino);
            $('#form1').submit();
        }

        return bResultado;
    }
</script>
<?php
if ($modo == '0')
{
    ?>
    <center>
        <br>
        <form method="POST" action="#" onsubmit="return verificadados(1)" id="form1" name="form1">
            <p align="center"><h3>
                <div align="center">
                    <p><b>Matr�cula do servidor</b></p>
                    <table width="18%" cellspacing="0">
                        <tr>
                            <td align="center" valign="middle">
                                <div align="center">
                                    <font size=1>
                                    <input type="text" id="matricula" name="matricula" size="7" maxlength="7">
                                    </font>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <p><font color="#000000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                        </font></p>
                </div>
                <p align="center" style="word-spacing: 0; margin: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
        </form>
    </center>
    <script>
        $('#matricula').focus();
    </script>
    <?php
}
else if ($modo == '1')
{
    $oDBase->query("SELECT a.mat_siape, a.nome_serv FROM servativ AS a WHERE a.mat_siape IN ('$matricula') ");

    if ($oDBase->num_rows() == 0)
    {
        mensagem('Servidor/Estagi�rio n�o cadastrado!', 'gestao_trocasenha_usuario.php?modo=0', 1);
    }
    else
    {
        $dados = $oDBase->fetch_object();
        ?>
        <center>
            <br>
            <form method="POST" action="#" onsubmit="return verificadados(2)" id="form1" name="form1">
                <p align="center"><h3>
                    <div align="center">
                        <p><b>Confirma os dados?</b></p>
                        <table width="18%" cellspacing="0">
                            <tr>
                                <td align="center" valign="middle">
                                    <div align="left">
                                        <font style='font-size: 14px; font-weight: bold;'>
                                        Matr�cula:&nbsp;<input type="text" id="matricula" name="matricula" size="7" maxlength="7" value="<?= tratarHTML($matricula); ?>" readonly style='border: 0px solid $FFFFFF'>
                                        <br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nome:&nbsp;<input type="text" id="nome" name="nome" size="40" maxlength="40" value="<?= tratarHTML($dados->nome_serv); ?>" readonly style='border: 0px solid $FFFFFF'>
                                        </font>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <p><font color="#000000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                            </font></p>
                    </div>
                    <p align="center" style="word-spacing: 0; margin: 0">
                        <input type="button" id="enviar" name="enviar" alt="Submeter os valores" align="center" value='&nbsp;Sim&nbsp;' onclick='javascript:verificadados(2);'>&nbsp;&nbsp;
                        <input type="button" id="voltar" name="voltar" alt="Retorna sem confirmar" align="center" value='&nbsp;N�o&nbsp;' onclick="javascript:history.go(-1);window.location.replace('gestao_trocasenha_usuario.php?modo=0');">
                    </p>
            </form>
        </center>
        <?php
    }
}
else
{
    $oDBase->query("UPDATE usuarios SET senha = (SELECT SUBSTR(MD5(DATE_FORMAT(a.dt_nasc,'%d%m%Y')),1,14) FROM servativ AS a WHERE a.mat_siape IN ('$matricula')), prazo=1 WHERE usuarios.siape IN ('$matricula') ");
    $msg = $oDBase->affected_rows();
    if ($msg == 0)
    {
        mensagem('Usu�rio dever� usar a data de nascimento!\nSenha j� foi reinicializada!\n.', 'gestao_trocasenha_usuario.php?modo=0', 1);
    }
    else if ($msg == -1)
    {
        mensagem('Senha n�o foi reinicializada!\nPor favor, repita a opera��o.', 'gestao_trocasenha_usuario.php?modo=0', 1);
    }
    else
    {
        mensagem('Senha reinicializada com sucesso!!!\nO usu�rio dever� usar a data de nascimento.', 'gestao_trocasenha_usuario.php?modo=0', 1);
    }
}

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
