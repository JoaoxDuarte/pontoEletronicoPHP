<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sLog");

$ini    = $_REQUEST["inicio"];
$inicio = conv_data($ini);
$fin    = $_REQUEST["final"];
$final  = conv_data($fin);

$date     = substr($inicio, 0, 4) . substr($inicio, 5, 2) . substr($inicio, 8, 2);
$nextdate = somadiasadata($date, 30);    // Adiciona 60 dias
// instancia BD
$oDBase   = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Utilitários » Auditoria » Registros Alterados< » Alterações de Cadastro');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setJS(_DIR_JS_ . 'jquery.js');
$oForm->setJS(_DIR_JS_ . 'sorttable.js');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Servidores com dados Cadastrais Alterados");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


if ($final > $nextdate)
{
    ?>
    <p align='center' style='font-family:verdana;font-size:12pt; color:#336600;line-height:100%;margin-left:0px; margin-right:0px;margin-top:6px;'><br><br><br>O período de pesquisa não pode ser superior a 30 dias!</p>
    <p align='center' style='word-spacing:0px;line-height:100%;margin-left:0px;margin-right:0px;margin-top:6px;'><a href='vecadalt.php'><img border='0' src='<?= _DIR_IMAGEM_; ?>tras.gif' alt='Voltar'></a></p>
    <?php
}
else
{
    $oDBase->query("
			SELECT
				a.mat_siape, b.nome_serv, b.cod_sitcad
			FROM
				histcad AS a
			LEFT JOIN
				servativ AS b ON a.mat_siape = b.mat_siape
			WHERE
				a.dataalt >= '$inicio'
				AND a.dataalt <= '$final'
			GROUP BY
				a.mat_siape
			ORDER BY
				b.nome_serv
		");
    $tot = $oDBase->num_rows();
    ?>
    <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
        <tr>
            <td height="29"  align='center' style='font-family:verdana; font-size:12pt'>
                <font size="2" face="Tahoma"><a href="vecadalt.php" ><img border= "0" src="<?= _DIR_IMAGEM_; ?>copiar.gif" align="absmiddle"></a></font></td>
        </tr>
        <tr>
            <td height="19"  align='center' style='font-family:verdana; font-size:12pt'><font size="2">Per&iacute;odo de <?= tratarHTML($ini . " a " . $fin); ?></font></td>
        </tr>
    </table>

    <table class="thin sortable draggable" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber2" >
        <tr bgcolor="#DBDBB7">
            <td width="8%" align='center'><b> <div align="center">Siape</div></td>
            <td width="58%"><b>Nome</td>
            <td width="16%"><div align="center"><strong>Situa&ccedil;&atilde;o</strong></div></td>
            <td width="18%"><div align="center"><strong>A&ccedil;&atilde;o</strong></div></td>
        </tr>
        <?php
        while ($pm  = $oDBase->fetch_array())
        {
            if ($pm['cod_sitcad'] == "66")
            {
                $sit = 'ETG';
            }
            else
            {
                $sit = 'RJU';
            }

            if ($rows != "0")
            {
                ?>
                <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                    <td align='center'><font color='#000000'><?= tratarHTML($pm['mat_siape']); ?></a></td>
                    <td><?= tratarHTML($pm['nome_serv']); ?></td>
                    <td align='center'><?= tratarHTML($sit); ?></td>
                    <td align='center'><a href='histcad.php?mat=<?= tratarHTML($pm['mat_siape']) . "&ini=" . tratarHTML($inicio) . "&fin=" . tratarHTML($final); ?>' target='new'>Visualizar Registros</td>
                </tr>
                <?php
            }
        }
        ?>
    </tr>
    </table>

    <table width="100%" border="1" cellpadding="0" cellspacing="0" bordercolor="#F1F1E2" class="thin sortable draggable" id="AutoNumber2" style="border-collapse: collapse" >
        <tr bgcolor="#DBDBB7">
            <td width="82%"><div align="center"><strong>Total</strong></div></td>
            <td width="18%"><div align="center"><strong><?= tratarHTML($tot); ?></strong></div></td>
        </tr>
    </table>
    <?php
}

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
