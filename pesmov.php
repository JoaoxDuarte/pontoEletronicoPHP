<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sRH");

// isntancia o banco de dados
$oDBase = new DataBase('PDO');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Relatórios » Movimentação » Consulta');
$oForm->setJS(_DIR_JS_ . 'sorttable.js');
$oForm->setOnLoad("$('#chave').focus();");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Relatório de Movimentação de Servidores");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>

<form method="POST" action="pesmov.php?display-table=sim" id="form1" name="form1">
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>">
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
        <tr>
            <td width="100%" colspan="3" style="word-spacing: 0; margin: 0">&nbsp;</td>
        </tr>
        <tr>
            <td width="50%" colspan="3" class="text-center">
                <!-- Multiple Radios -->
                <div class="form-group">
                    <div class="col-md-2"></div>
                    <div class="col-md-3">
                        <div class="radio">
                            <label for="radios-0">
                                <input type="radio" name="escolha"  value="data" checked onclick="document.all['chave'].focus()">

                                A partir de uma Data
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="radio">
                            <label for="radios-1">
                                <input type="radio" name="escolha" value="lotacao" onclick="document.all['chave'].focus()">
                                Por Lotação
                            </label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="radio">
                            <label for="radios-1">
                                <input type="radio" name="escolha" value="gex" onclick="document.all['chave'].focus()">
                                Por Gerência
                            </label>
                        </div>
                    </div>

                </div>
            </td>
        </tr>
        <tr>
            <td colspan='3' align="center" style="word-spacing: 0; margin: 0">&nbsp;</td>
        </tr>
        <tr>
            <td colspan='3' class="corpo" style="word-spacing: 0; margin: 0">
                <div class="col-md-3"></div>
                <div class="form-group text-center">
                    <div class="col-md-6">
                        Chave<br>
                        <input type="text" class='form-control' id="chave" name="chave" title="Não informe pontos" size="28" maxlength="28" value='<?= tratarHTML(anti_injection($_REQUEST["chave"])); ?>'>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div class="row">
<!--    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">-->
<!--        <tr>-->
<!--            <td class="corpo" width="100%" colspan="3">-->
<!--                <p align="center" style="word-spacing: 0; margin: 0">-->
<!--                    <input type="radio" name="escolha"  value="data" checked onclick="document.all['chave'].focus()">-->
<!--                    <font face="Tahoma" size="1"> A partir de uma Data </font>-->
<!---->
<!--                    <font face="Tahoma" size="1"> Por Lotação </font>-->
<!---->
<!--                    <font face="Tahoma" size="1"> Por Gerência </font>-->
<!--                </p>-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td width="29%"><p style="word-spacing: 0; margin: 0"></p></td>-->
<!--            <td width="37%"><p style="word-spacing: 0; margin: 0">&nbsp;</p></td>-->
<!--            <td width="34%"><p style="word-spacing: 0; margin: 0"></p></td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td width="29%"> <p style="word-spacing: 0; margin: 0"></td>-->
<!--            <td class="corpo" width="37%">-->
<!--                <p align="center" style="word-spacing: 0; margin: 0">-->
<!--                    <font size="1" face="Tahoma">Chave&nbsp;</font>-->
<!--                    <input type="text" class="caixa" name="chave" title="Não informe pontos" size="28">-->
<!--                </p>-->
<!--            </td>-->
<!--            <td width="34%"> <p style="word-spacing: 0; margin: 0"></td>-->
<!--        </tr>-->
<!--    </table>-->
    <div class="col-md-12 margin-10 margin-bottom-10">
        <div class="text-center ">

            <button type="image" class="btn btn-sucess  btn-primary" id="btn-continuar">
                <span class="glyphicon glyphicon-ok"></span> OK
            </button>
        </div>

    </div>
    </div>

</form>

<?php if (empty($_GET['display-table'])): ?>
    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter" style="display: none;">
<?php else: ?>
    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
<?php endif; ?>

    <tr bgcolor='#008000'>
        <td width="6%"  align="center"><label class="label-control">SIAPE</label></td>
        <td width="38%" align='left'  ><label class="label-control">Nome</label></td>
        <td width="15%" align="center"><label class="label-control">Cargo</label></td>
        <td width="15%" align="center"><label class="label-control">Setor anterior</label></td>
        <td width="9%"  align="center"><label class="label-control">Saída</label></td>
        <td width="15%" align="center"><label class="label-control">Setor atual</label></td>
    </tr>
    <?php

    $chave   = anti_injection($_POST["chave"]);
    $escolha = anti_injection($_POST["escolha"]);

    $upag = $_SESSION['upag'];

    if (isset($chave))
    {
        $var1 = urldecode($chave);
        $var2 = urldecode($escolha);

        if ($var2 == "data")
        {
            //converter datas
            $var1         = conv_data($var1);
            $filtro_extra = " AND a.dt_ing_lot >= '$var1' AND a.dt_sai_lot <> '0000-00-00' ";
        }
        elseif ($var2 == "gex")
        {
            $qlotacao     = substr($var1, 0, 2) . "_" . substr($var1, 3, 2) . "%";
            $filtro_extra = " AND a.cod_lot LIKE '$qlotacao' AND a.dt_sai_lot <> '0000-00-00' ";
        }
        elseif ($var2 == "lotacao")
        {
            $filtro_extra = " AND a.cod_lot LIKE '$var1%' AND a.dt_sai_lot <> '0000-00-00' ";
        }

        $pesquisa = "
		SELECT
			a.mat_siape, a.nome_serv, a.cod_lot, a.cod_lot_ant,
			DATE_FORMAT(a.dt_sai_lot, '%d/%m/%Y') AS dt_sai_lot, a.cod_cargo/*, b.upag, b.descricao, b.descricao_ant*/
		FROM
			servativ AS a
		LEFT JOIN
			tabsetor AS b ON a.cod_lot = b.codigo
		LEFT JOIN
			tabsetor AS c ON a.cod_lot_ant = c.codigo
		WHERE
			b.upag = '$upag' AND a.cod_sitcad NOT IN ('02','15') " . $filtro_extra . "
		ORDER BY
			a.dt_ing_lot DESC
		";

        $oDBase->query($pesquisa);

        while ($pm_partners = $oDBase->fetch_object())
        {
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'   >
                <td align="center"><?= tratarHTML(removeOrgaoMatricula($pm_partners->mat_siape)); ?>&nbsp;</td>
                <td width="38%" align="center"><?= tratarHTML($pm_partners->nome_serv); ?></div></td>
                <td width="15%" align="center"><?= tratarHTML($pm_partners->cod_cargo); ?></td>
                <td width="15%" align="center" title='<?= tratarHTML($pm_partners->descricao_ant); ?>' alt='<?= tratarHTML($pm_partners->descricao_ant); ?>'>
                   <?= tratarHTML($pm_partners->cod_lot_ant); ?>
                </td>
                <td width="9%"  align="center"><?= tratarHTML($pm_partners->dt_sai_lot); ?></td>
                <td width="15%" align="center" title='<?= tratarHTML($pm_partners->descricao); ?>' alt='<?= tratarHTML($pm_partners->descricao); ?>'>
                    <?= tratarHTML($pm_partners->cod_lot); ?>
                </td>
            </tr>
            <?php
        } // fim do while
    }

    ?>
</table>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
