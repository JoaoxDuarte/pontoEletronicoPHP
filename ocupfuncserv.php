<?php
include_once( "config.php" );

verifica_permissao("sRH");

// instancia o BD
$oDBase = new DataBase('PDO');

// seleciona os dados
$oDBase->query("
		SELECT
			func.cod_lot, und.descricao AS desc_lotacao, chf.num_funcao, func.desc_func, chf.nome_serv, chf.mat_siape, chf.sit_ocup, func.num_funcao, chf.dt_fim, func.ativo, func.upag
		FROM
			ocupantes AS chf
		LEFT JOIN
			tabfunc AS func ON chf.num_funcao = func.num_funcao
		LEFT JOIN
			tabsetor AS und ON func.cod_lot = und.codigo
		WHERE
			func.ativo = 'S'
			AND func.upag = '" . $_SESSION['upag'] . "'
			AND chf.dt_fim = '0000-00-00'
		ORDER BY
			func.cod_lot, IF(chf.sit_ocup='T',1,IF(chf.sit_ocup='S',2,IF(chf.sit_ocup='I',3,4)))
	");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Relatórios » Gerencial » Ocupantes de Fun&ccedil;&otilde;es");
$oForm->setJQuery();
$oForm->setCSS("
	<style>
		.border1 { border: 1px solid #F1F1E2; }
		.bgcolor1 { background-color: #DBDBB7; }
		.competencia { float: left; border: 1px solid #808040; width: 100%; text-align: center; padding: 3px 0px 3px 0px; }
		.titulo_lotacao  { float: left; width: 67px; text-align: center; padding: 2px 0px 2px 0px; }
		.titulo_codigo   { float: left; width: 47px; text-align: center; padding: 2px 0px 2px 0px; background-color: #DBDBB7; }
		.titulo_funcao   { float: left; width: 267px; text-align: left; padding: 2px 0px 2px 2px; background-color: #DBDBB7; }
		.titulo_situacao { float: left; width: 5%; text-align: center; padding: 2px 0px 2px 0px; }
		.titulo_ocupante { float: left; width: 35%; text-align: center; padding: 2px 0px 2px 0px; }
		.titulo_siape    { float: left; width: 5%; text-align: center; padding: 2px 0px 2px 0px; }
		.separador { float: left; width: 1px; background-color: transparent; padding: 2px 0px 2px 0px;  }
	</style>
	");
$oForm->setLargura('920px');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Ocupantes de Fun&ccedil;&otilde;es");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form action="#" method="post" id="form1" name="form1">
    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
        <tr>
            <td>
                <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
                    <tr height='22px' bgcolor="#DBDBB7" style='vertical-align: middle;'>
                        <td width="8%" align='center'><b>Lotação</td>
                        <td width="8%" align='center'><b>C&oacute;digo</td>
                        <td width="43%"><b>Descri&ccedil;&atilde;o do cargo ou fun&ccedil;&atilde;o</td>
                        <td width="8%"><div align="center"><strong>Situa&ccedil;&atilde;o</strong></div></td>
                        <td width="34%" align='center'><div align="left"><strong>Nome do ocupante</strong></div><b></td>
                        <td width="7%" align='center'><strong>Siape</strong></td>
                    </tr>
                    <?php
                    if ($oDBase->num_rows() > 0)
                    {
                        $bdcolor = '#eeeeee';
                        while ($pm      = $oDBase->fetch_object())
                        {
                            $bgcolor = ($bgcolor == '#eeeeee' ? '#ffffff' : '#eeeeee');
                            switch ($pm->sit_ocup)
                            {
                                case "T": $situacao = "TITULAR";
                                    break;
                                case "S": $situacao = "SUBSTITUTO";
                                    break;
                                default: $situacao = "INTERINO";
                                    break;
                            }
                            echo "
								<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18' style='background-color: $bgcolor;'>
									<td style='text-align:left;'  >&nbsp;" . tratarHTML($pm->cod_lot) . "&nbsp;</td>
									<td style='text-align:center;'>&nbsp;" . tratarHTML($pm->num_funcao) . "&nbsp;</td>
									<td style='text-align:left;'  >&nbsp;" . tratarHTML($pm->desc_func) . "</td>
									<td style='text-align:left;'  >&nbsp;" . tratarHTML($situacao) . "&nbsp;</td>
									<td style='text-align:left;'  >&nbsp;" . tratarHTML($pm->nome_serv) . "</td>
									<td style='text-align:center;'>&nbsp;<a href='veocupantefunc.php?novafuncao=" . tratarHTML($pm->num_funcao) . "&matricula=" . tratarHTML($pm->mat_siape) . "'>" . tratarHTML(removeOrgaoMatricula($pm->mat_siape)) . "</a>&nbsp;</td>
								</tr>";
                        }
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
