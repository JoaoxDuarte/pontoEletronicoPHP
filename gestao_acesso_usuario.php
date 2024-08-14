<?php
// conexao ao banco de dados funcoes diversas
include_once( "config.php" );

verifica_permissao('corrigir_acesso');

$matricula = anti_injection($_REQUEST['matricula']);
$modo      = anti_injection($_REQUEST['modo']);
$modo      = (empty($modo) ? '0' : $modo);

// instancia do BD
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Utilitários » Gestores » Acesso do Usuário");
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setOnLoad("javascript: if($('#matricula')) { $('#matricula').focus() };");
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Corrigir Acesso Usuário");

// Topo do formulário
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

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        if (bResultado == true)
        {
            var destino = 'javascript:window.location.replace("gestao_acesso_usuario.php?modo=' + modo + '&matricula=' + matricula.val() + '");';
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
                    <p><b>Matrícula do servidor</b></p>
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
    <?php
}
else if ($modo == '1')
{
    $oDBase->query("SELECT a.mat_siape, a.nome_serv FROM servativ AS a WHERE a.mat_siape IN ('$matricula') ");

    if ($oDBase->num_rows() == 0)
    {
        mensagem('Servidor/Estagiário não cadastrado!', 'gestao_acesso_usuario.php?modo=0');
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
                                        Matrícula:&nbsp;<input type="text"  id="matricula" name="matricula" size="7" maxlength="7" value="<?= tratarHTML($matricula); ?>" readonly style='border: 0px solid $FFFFFF'>
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
                        <input type="button" id="voltar" name="voltar" alt="Retorna sem confirmar" align="center" value='&nbsp;Não&nbsp;' onclick="javascript:history.go(-1);window.location.replace('gestao_acesso_usuario.php?modo=0');"></p>
            </form>
        </center>
        <?php
    }
}
else
{

    $oDBase->query("
		SELECT
			1
		FROM usuarios AS a
		LEFT JOIN tabsetor AS b ON a.setor=b.codigo
		WHERE a.siape IN ('$matricula') ");

    $registro = $oDBase->fetch_assoc();

    if ($registro)
    {
        mensagem('Usuário já cadastrado!\nPor favor, reinicialize a senha do usuário.', 'gestao_acesso_usuario.php', 1);
    }

    $oDBase->query("
		INSERT usuarios
			(siape,nome,senha,setor,acesso,privilegio,prazo,magico,upag,defvis)
		SELECT
			a.mat_siape, a.nome_serv, SUBSTR(MD5(DATE_FORMAT(a.dt_nasc,'%d%m%Y')),1,14), a.cod_lot, 'NNSNNNNNNNNNN', '', '1', 0, b.upag, 'N'
		FROM servativ AS a
		LEFT JOIN tabsetor AS b ON a.cod_lot=b.codigo
		WHERE a.mat_siape IN ('$matricula') ");
    $msg = $oDBase->error();


    if (!empty($msg))
    {
        mensagem('Usuário não registrado!\nPor favor, repita a operação.', 'gestao_acesso_usuario.php', 1);
    }
    else
    {
        mensagem('Usuário registrado com sucesso!', 'gestao_acesso_usuario.php', 1);
    }
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
