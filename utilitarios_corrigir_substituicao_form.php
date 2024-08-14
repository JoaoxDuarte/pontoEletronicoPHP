<?php

	// Inicia a sessão e carrega as funções de uso geral
	include_once("config.php");

	// Inicia a sessão e carrega as funções de uso geral
	include_once("config.php");

	// Verifica se existe um usuário logado e se possui permissão para este acesso
	verifica_permissao( 'sRH e sTabServidor' );

	$matricula = $_REQUEST['pSiape'];

	// instancia banco de dados
	$oDBase = new DataBase('mysqli');

    $matricula = getNovaMatriculaBySiape($matricula);


	$oDBase->query( "
	SELECT chf.sit_ocup, chf.dt_inicio, IFNULL(chf.num_funcao,'') AS num_funcao, IFNULL(func.desc_func,'') AS desc_funcao, IFNULL(func.cod_lot,'') AS cod_lot_funcao, cad.nome_serv, cad.cod_lot, cad.chefia, usu.acesso, und.descricao, und.cod_uorg, und.upag
	FROM ocupantes AS chf
	LEFT JOIN tabfunc  AS func ON chf.num_funcao = func.num_funcao AND func.ativo='S'
	LEFT JOIN servativ AS cad  ON chf.mat_siape = cad.mat_siape
	LEFT JOIN usuarios AS usu  ON chf.mat_siape = usu.siape
	LEFT JOIN tabsetor AS und  ON func.cod_lot = und.codigo AND und.ativo='S'
	WHERE chf.mat_siape='$matricula' AND chf.dt_fim='0000-00-00' ;
	" );
	$oChefia = $oDBase->fetch_object();
	$sit         = $oChefia->sit_ocup;
	$dtini       = databarra($oChefia->dt_inicio);
	$num_funcao  = $oChefia->num_funcao;
	$desc_funcao = $oChefia->desc_funcao;
	$lot_funcao  = $oChefia->lot_funcao;
	$nome        = $oChefia->nome_serv;
	$lot         = $oChefia->cod_lot;
	$chefia      = $oChefia->chefia;
	$acesso      = $oChefia->acesso;
	$ace         = substr($acesso,1,1);
	$upag        = $oChefia->upag;


	if ($oDBase->num_rows() == 0)
	{
		mensagem( "Servidor não ocupa função!" );
		voltar(1);
	}
	else if ($_SESSION['upag'] != $upag)
	{
        mensagem( "Servidor não pertence a sua UPAG!" );
		voltar(1);
	}
	else if ($_SESSION['sRH'] == 'N' && $_SESSION['sLotacao'] != $lot)
	{
		mensagem( "Servidor não pertence a sua lotação!" );
		voltar(1);
	}
	else
	{
		switch ($sit)
		{
			case 'T': $sit2 = "TITULAR";    break;
			case 'S': $sit2 = "SUBSTITUTO"; break;
			case 'R': $sit2 = "INTERINO";   break;
		}

		$oDBase->query( "
		SELECT
			subs.id, subs.situacao, subs.numfunc, subs.inicio, subs.fim, func.desc_func, func.cod_funcao, func.cod_lot, func.resp_lot, und.cod_uorg, und.descricao
		FROM
			substituicao AS subs
		LEFT JOIN
			tabfunc AS func ON subs.numfunc = func.num_funcao AND func.ativo = 'S'
		LEFT JOIN
			tabsetor AS und ON func.cod_lot = und.codigo AND und.ativo = 'S'
		WHERE
			subs.siape='$matricula' AND subs.situacao = 'A'
		" );
		$oSubst = $oDBase->fetch_object();
		$num    = $oDBase->num_rows();
		$situacao = $oSubst->situacao;
		$numfunc  = $oSubst->numfunc;
		$inicio   = databarra($oSubst->inicio);
		$fim      = databarra($oSubst->fim);
		$funcao   = $oSubst->desc_func;
		$sigla    = $oSubst->cod_funcao;
		$lot      = $oSubst->cod_lot;
		$rlot     = $oSubst->resp_lot;
		$uorg     = $oSubst->cod_uorg;
		$dlot     = $oSubst->descricao;
	}

	## classe para montagem do formulario padrao
	#
	$oForm = new formPadrao();
	$oForm->setCaminho( 'Utilitários » Manutenção » Corrigir Substituição' );
	$oForm->setCSS( _DIR_CSS_."estiloIE.css" );
	$oForm->setSeparador( 0 );
	$oForm->setSubTitulo( "Tela para Acerto de Erros com Substituição" );

	// Topo do formulário
	//
	$oForm->exibeTopoHTML();
	$oForm->exibeCorpoTopoHTML();

	?>

	<form method="POST" action="utilitarios_corrigir_substituicao_grava.php" name="form1">
		<input type='hidden' id='modo' name='modo' value='1'>
		<input type='hidden' id="rlot" name='rlot' value="<?= tratarHTML($rlot); ?>">
		<input type='hidden' id="acesso" name='acesso' value="<?= tratarHTML($acesso); ?>">
		<p style="margin-top: 0; margin-bottom: 0" align="center"><b> <font size="4" face="Tahoma"></p>

		&nbsp;<strong><font size="2">DADOS DO SERVIDOR</font></strong>&nbsp;
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td height="22" class="corpo" style='width:10px;text-align:left;padding:0px 0px 0px 15px;'>
					<p>Matr&iacute;cula:<br>
					<input name="matricula" type="text" class='alinhadoAoCentro' id="matricula" value='<?= tratarHTML(removeOrgaoMatricula($matricula)); ?>' size="15" readonly>
					</p>
				</td>
				<td colspan='2' class="corpo" style='width:10px;text-align:left;padding:0px 0px 0px 5px;'>
					<p>Nome:<br>
					<input name="nome" type="text" class='alinhadoAoCentro' id="nome" value='<?= tratarHTML($nome); ?>' size="50" readonly>
					</p>
				</td>
			</tr>
			<tr>
				<td height="26" class="corpo" style='width:10px;text-align:left;padding:0px 0px 0px 15px;'>
					<p>Numero da fun&ccedil;&atilde;o:
					<input type="text" id="nfunc" name="nfunc2" class='alinhadoAoCentro' value='<?= tratarHTML($num_funcao); ?>' size="15" readonly>
					</p>
				</td>
				<td class="corpo" style='width:10px;text-align:left;padding:0px 0px 0px 5px;'>
					<p>Denomina&ccedil;&atilde;o:<br>
					<input name="nome2" type="text" class='alinhadoAoCentro' id="nome3" value='<?= tratarHTML($desc_funcao); ?>' size="85" readonly>
					</p>
				</td>
				<td class="corpo" style='width:10px;text-align:left;padding:0px 0px 0px 0px;'>
					<p>Situa&ccedil;&atilde;o:<br>
					<input name="situacao" type="text" class='alinhadoAoCentro' id="matricula4" value='<?= tratarHTML($sit2); ?>' size="15" readonly>
					</p>
				</td>
			</tr>
		</table>
<!--
		<font size="2"><strong>DADOS DO CADASTRO DE USU&Aacute;RIOS</strong> </font>
		<br>
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td width="353"><div align="center">Chefia?
					<input name="acesso" type="text" class='alinhadoAoCentro' id="acesso" size="4" value="<?= tratarHTML($ace); ?>" readonly='15'>
					- Perfil
					<input name="acesso2" type="text" class='alinhadoAoCentro' id="acesso2" value="<?= tratarHTML($acesso); ?>" size="16" readonly='15'>
					-
					<select name="acess" class="alinhadoAoCentro" id="acess">
						<option value="NSNNNNNNNNNNS">selecione</option>
						<option value="NSNNNNNNNNNNS" <?= ($acesso == 'NSNNNNNNNNNNN' ? "selected" : ""); ?>>chefia</option>
						<option value="SSNNNNNNNSNNS" <?= ($acesso == 'SSNNNNNNNSNNN' ? "selected" : ""); ?>>chefia rh</option>
						<option value="NNSNNNNNNSNNS" <?= ($acesso == 'SNSNNNNNNSNNN' ? "selected" : ""); ?>>sem chefia rh</option>
						<option value="NNSNNNNNNNNNS" <?= ($acesso == 'NNSNNNNNNNNNN' ? "selected" : ""); ?>>sem chefia</option>
					</select>
					</div>
				</td>
				<td width="670"> <p>Unidade:
					<input name="lot" type="text" class='alinhadoAoCentro' id="lot" size="9" value="<?= tratarHTML($lot); ?>" readonly='9'>
					-
					<input name="dlot" type="text" class='alinhadoAoCentro' id="nome23" value='<?= tratarHTML($dlot); ?>' size="80" readonly>
					</p>
				</td>
				<td width="125"> <p>Upag:
          <input name="upag" type="text" class='alinhadoAoCentro' id="upag" size="10" value="<?= tratarHTML($upag); ?>" readonly='10'>
					</p></td>
			</tr>
		</table>
-->
		<br>
		<font size="2"><strong>DADOS DO CADASTRO DE SUBSTITUI&Ccedil;&Atilde;O</strong> </font>
		<br>
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td style='width:126px;text-align:center;background-color:#DBDBB7;'>Id</td>
				<td style='width:510px;text-align:center;background-color:#DBDBB7;'>Numero da fun&ccedil;&atilde;o</td>
				<td style='width:217px;text-align:center;background-color:#DBDBB7;'>In&iacute;cio</td>
				<td style='width:196px;text-align:center;background-color:#DBDBB7;'>Fim</td>
				<td style='width:151px;text-align:center;background-color:#DBDBB7;'>Situa&ccedil;&atilde;o</td>
			</tr>
			<?php

			if ($num>0)
			{
				$oDBase->data_seek();
				while ($pm = $oDBase->fetch_object())
				{
					?>
					<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18' style='border:1px solid #bfbfbf;'>
						<td align='center'><a href='substituicao_encerrar.php?id=<?= tratarHTML($pm->id); ?>&orig=1' target='new' style='font-family:Verdana;font-size:11px;font-weight:bold;'><?= tratarHTML($pm->id); ?></a></td>
						<td align='center'><?= tratarHTML($pm->numfunc); ?></td>
						<td align='center'><?= tratarHTML($pm->inicio); ?></td>
						<td align='center'><?= tratarHTML($pm->fim); ?></td>
						<td align='center'><?= tratarHTML($pm->situacao); ?></td>
					</tr>
					<?php
				}
			}
			else
			{
					?>
					<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'>
						<td colspan='5' align='center' style='border:1px solid #bfbfbf;'>Não há dados de substituição Ativa.</td>
					</tr>
					<?php
			}

			?>
		</table>

		<p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0">
			<br>
			<input type="image" border="0" src="./imagem/ok.gif" name="enviar" alt="Submeter os valores" align="center" >
		</p>
	</form>
	<?php

	// Base do formulário
	//
	$oForm->exibeCorpoBaseHTML();
	$oForm->exibeBaseHTML();
