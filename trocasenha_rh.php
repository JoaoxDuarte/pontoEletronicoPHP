<?php

	// conexao ao banco de dados
	// funcoes diversas
	include_once("config.php");

	verifica_permissao( 'logado' );

	$siape =  $_SESSION["sMatricula"];

	switch ($_SESSION['sHOrigem_1'])
	{
		case "entrada.php":
		case "secaodousuario.php":
			$bExibeLogo = true;
			$bReadOnlySIAPE = true;
			$caminho_modulo_utilizado = "Login » Troca de Senha";
			break;

		default:
			$bExibeLogo = false;
			$bReadOnlySIAPE = false;
			$caminho_modulo_utilizado = "Utilitários » Usuários » Trocar Senha";
			verifica_permissao( 'logado', $_SESSION['sHOrigem_1'] );
			break;
	}

	## classe para montagem do formulario padrao
	#
	$oForm = new formPadrao();
	$oForm->setOnLoad( "javascript: if($('#senhaatual')) { $('#senhaatual').focus() };" );
	$oForm->setJS( "trocasenha_rh.js");

	$oForm->setSubTitulo( "Troca de Senha" );

	// Topo do formulário
	//
	$oForm->exibeTopoHTML();
	$oForm->exibeCorpoTopoHTML();

	?>
	<script> var voltarOrigem = "<?= tratarHTML($_SESSION['sHOrigem_1']); ?>"; </script>
	<form method="POST" id="form1" name="form1"  action="#" onsubmit="javascript:return false;">
		<table align="center" border="0" width="56%" cellspacing="0" cellpadding="0">
			<tr>
				<td width="51%" align="right"><font face="Verdana" size="1">Siape</font></td>
				<td width="2%"></td>
				<td width="47%"><input class="caixa" type="text" id="lSiape" name="lSiape" size="7" value="<?= tratarHTML($siape); ?>" <?= ($bReadOnlySIAPE==true?'readonly':''); ?>></td>
			</tr>
			<tr>
				<td align="right">&nbsp;</td>
				<td></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td width="51%" align="right"><font face="Verdana" size="1" color='red'><b>Senha Usada Atualmente</b></font></td>
				<td width="2%"></td>
				<td width="47%"><input class="caixa" type="password" id="senhaatual" name="senhaatual" size="8" maxlength="8" onkeyup="javascript:ve(this.value);"></td>
			</tr>
			<br>
			<tr>
				<td align="right">&nbsp;</td>
				<td></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td width="51%" align="right"><font face="Verdana" size="1">Nova Senha (com 8 dígitos)</font></td>
				<td width="2%"></td>
				<td width="47%"><input class="caixa" type="password" id="senhanova" name="senhanova" size="8" maxlength="8" onkeyup="javascript:ve(this.value);" title="Senha deve conter 8 caracteres alfanuméricos"></font></td>
			</tr>
			<tr>
				<td width="51%" align="right"><font face="Verdana" size="1">Confirmar Nova Senha (com 8 dígitos)</font></td>
				<td width="2%"></td>
				<td width="47%"><input class="caixa" type="password" id="senhanova_confirmar" name="senhanova_confirmar" size="8" maxlength="8" title="Senha deve conter 8 caracteres alfanuméricos"> </font></td>
			</tr>
		</table>
		<p style="word-spacing: 0; margin: 0">
			<br>
			<br>
			   <div class="form-group">
        <div class="col-md-12">
            <div class="col-md-2 col-md-offset-4">
                <button type="submit" id="btn-continuar" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-ok"></span> Gravar
                </button>
            </div>
            <div class="col-md-2">
                <a class="btn btn-danger btn-block" href="javascript:window.location.replace('<?=$_SESSION['sHOrigem_1'];?>');">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>







                  			





		</p>
	</form>
	<?php

	// Base do formulário
	//
	$oForm->exibeCorpoBaseHTML();
	$oForm->exibeBaseHTML();
