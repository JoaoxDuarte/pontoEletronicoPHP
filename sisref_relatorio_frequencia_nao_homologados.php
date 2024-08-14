<?php

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao("sRH");

//definindo a competencia de homologacao
$oData = new trata_datasys();
$mes   = $oData->getMesAnterior();
$ano   = $oData->getAnoAnterior();

$sLotacao = $_SESSION['sLotacao'];
$upag     = $_SESSION['upag'];

// dados voltar
$_SESSION['voltar_nivel_1'] = 'sisref_relatorio_frequencia_nao_homologados.php';
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona descricao do setor
$oDBase->query("SELECT descricao FROM tabsetor WHERE codigo = :codigo ", array(
    array(":codigo", $sLotacao, PDO::PARAM_STR),
));
$wnomelota = $oDBase->fetch_object()->descricao;

// seleciona os servidores com frequencia homologada
$oDBase->setMensagem("Erro no acesso a tabela de CADASTRO!");
$oDBase->query("
		SELECT
			count(*) AS total
		FROM
			servativ AS cad
		LEFT JOIN
			tabsetor AS und ON cad.cod_lot = und.codigo
		LEFT JOIN
			homologados AS hom ON (cad.mat_siape = hom.mat_siape) AND (hom.compet = '" . $ano . $mes . "')
		WHERE
			cad.excluido = 'N'
			AND cad.cod_sitcad NOT IN ('02','15','08')
			AND IFNULL(hom.homologado,'N') = 'N'
			AND und.upag = '" . $upag . "'
			AND DATE_FORMAT(cad.dt_adm,'%Y%m') <= '" . $ano . $mes . "'
		GROUP BY
			cad.cod_lot
	");
$num_unidades = $oDBase->num_rows();

// seleciona os servidores com frequencia homologada
$oDBase->setMensagem("Erro no acesso a tabela de CADASTRO (2)!");
$oDBase->query("
		SELECT
			cad.mat_siape, cad.nome_serv, cad.cod_lot, cad.jornada, cad.freqh, und.upag, und.descricao, IFNULL(hom.homologado,'N') AS homologado
		FROM
			servativ AS cad
		LEFT JOIN
			tabsetor AS und ON cad.cod_lot = und.codigo
		LEFT JOIN
			homologados AS hom ON (cad.mat_siape = hom.mat_siape) AND (hom.compet = '" . $ano . $mes . "')
		WHERE
			cad.excluido = 'N'
			AND cad.cod_sitcad NOT IN ('02','15','08')
			AND IFNULL(hom.homologado,'N') = 'N'
			AND und.upag = '" . $upag . "'
			AND DATE_FORMAT(cad.dt_adm,'%Y%m') <= '" . $ano . $mes . "'
		ORDER BY
			cad.cod_lot, cad.nome_serv
	");
$num = $oDBase->num_rows();
$oDBases = new DataBase('PDO');
$oDBases->setMensagem("Erro no acesso a tabela de CADASTRO (2)!");
$oDBases->query("
		SELECT
			cad.mat_siape, cad.nome_serv, cad.cod_lot, cad.jornada, cad.freqh, und.upag, und.descricao, IFNULL(hom.homologado,'N') AS homologado
		FROM
			servativ AS cad
		LEFT JOIN
			tabsetor AS und ON cad.cod_lot = und.codigo
		LEFT JOIN
			homologados AS hom ON (cad.mat_siape = hom.mat_siape) AND (hom.compet = '" . $ano . $mes . "')
		WHERE
			cad.excluido = 'N'
			AND cad.cod_sitcad NOT IN ('02','15','08')
			AND IFNULL(hom.homologado,'N') = 'N'
			AND und.upag = '" . $upag . "'
			AND DATE_FORMAT(cad.dt_adm,'%Y%m') <= '" . $ano . $mes . "'
		ORDER BY
			cad.cod_lot, cad.nome_serv
	");
//Barra de progresso
/*$objBar = new PogProgressBar('pb0');
$objBar->setTheme('blue');
$objBar->draw('', '1px', '30%');*/


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » RH Atualizar » Verificar Homologações');
$oForm->setJQuery();
$oForm->setCSS(_DIR_CSS_ . "smoothness/jquery-ui-custom-px.min.css");

$oForm->setSeparador(0);
$oForm->setSeparadorBase(10);

$oForm->setSubTitulo("Unidade(s) com Pendência(s) de Homologação de Frequência(s)");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// monta a caixa de dialog para exibicao da mensagem/pagina
preparaDialogView(1020, 470);
?>
<style>
    .ui-tabs.ui-tabs-vertical {
        padding: 0;
        width: 100%;
    }
    .ui-tabs.ui-tabs-vertical .ui-widget-header {
        border: none;
    }
    .ui-tabs.ui-tabs-vertical .ui-tabs-nav {
        float: left;
        width: 10em;
        background: #CCC;
        border-radius: 4px 0 0 4px;
        border-right: 1px solid gray;
        height: 300px;
        background-color: white;
        overflow-x: hidden; overflow-y: auto
    }
    .ui-tabs.ui-tabs-vertical .ui-tabs-nav li {
        clear: left;
        width: 100%;
        margin: 0.2em 0;
        border: 1px solid gray;
        border-width: 1px 0 1px 1px;
        border-radius: 4px 0 0 4px;
        overflow: hidden;
        position: relative;
        right: -2px;
        z-index: 2;
    }
    .ui-tabs.ui-tabs-vertical .ui-tabs-nav li a {
        display: block;
        width: 100%;
        padding: 0.6em 1em;
    }
    .ui-tabs.ui-tabs-vertical .ui-tabs-nav li a:hover {
        cursor: pointer;
    }
    .ui-tabs.ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active {
        margin-bottom: 0.2em;
        padding-bottom: 0;
        border-right: 1px solid white;
    }
    .ui-tabs.ui-tabs-vertical .ui-tabs-nav li:last-child {
        margin-bottom: 10px;
    }
    .ui-tabs.ui-tabs-vertical .ui-tabs-panel {
        float: left;
        width: 57em;
        border-left: 1px solid gray;
        border-radius: 0;
        position: relative;
        left: -1px;
        background-color: white;
    }
</style>

<script>
    $(function ()
    {
        $('#tabs').tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
    });

    function enviar_email_periodo_homologacao(unidade)
    {
        // dados
        var dados = $('#dados' + unidade).serialize();
        //alert(dados);
        /*
         return false;
         //create the ajax request
         $.ajax({
         type: "POST",
         url: "sisref_relatorio_frequencia_nao_homologados_comunicacao.php", // a pagina que sera chamada
         data: "unidade=" + unidade,  // dados enviados
         timeout: 3000,
         dataType: "json",
         beforeSend: function() {
         // enquanto a função esta sendo processada, você
         // pode exibir na tela uma msg de carregando
         //$('#mensagem_aviso').html('<img src="imagem/loading.gif"/>');
         showProcessando();
         },
         success: function(response) {
         var ojson = response.dados;
         // Número de itens
         var tam = ojson.length;
         if (tam == 0 || ojson[0].erro == null | ojson[0].erro != '') {
         alert( "Problemas no envio do comunicado via e-mail!" );
         }
         else
         {
         alert( "Comunicado o período para homologação, via e-mail!" );
         }
         },
         error: function(txt) {
         // em caso de erro
         hideProcessando();
         alert('Houve um problema interno. Tente novamente.');
         },
         complete: function(data) {
         hideProcessando();
         }
         });
         */
    }
</script>

<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
    <tr>
        <td width="100%"  align='center' style='font-family:verdana; font-size:12pt'>
            <font size="2" face="Tahoma">M&ecirc;s
            <input name="mes" type="text" class='alinhadoAoCentro' id="mes"  value='<?= tratarHTML($mes); ?>' size="7" readonly>
            Ano
            <input name="ano" type="text" class='alinhadoAoCentro' id="ano" value='<?= tratarHTML($ano); ?>' size="7" readonly>
            </font>
        </td>
    </tr>
    <tr>
        <td  align='center' style='font-family:verdana; font-size:12pt'>
            <font size="2" face="Tahoma">Unidade Pagadora:
            <input name="lot" type="text" class='alinhadoAoCentro' id="lot" value='<?= tratarHTML(getUorgMaisDescricao( $sLotacao )); ?>' size="60" maxlength="60" readonly>
            </font>
        </td>
    </tr>
</table>
<?php
if ($num > 0)
{
    ?>
    <div id="tabs">
        <ul>
            <?php
            // contadores
            $unidades  = array();
            $total_sim = 0;
            $total_nao = 0;

            $sequencia             = 0;
            $registros_processados = 0;
            $numero_de_servidores  = $num + $num_unidades;

            $grupo_setor = "";
            while ($pm          = $oDBase->fetch_object())
            {
                if ($grupo_setor == "" || $grupo_setor != $pm->cod_lot)
                {
                    $total_sim = 0;
                    $total_nao = 0;

                    if (isset($_SESSION['tabPosition']))
                    {
                        $_SESSION['tabPosition'] = ($_SESSION['tabPosition'] == '' ? "#" . $pm->cod_lot : $_SESSION['tabPosition']);
                    }
                    else
                    {
                        $_SESSION['tabPosition'] = "#" . $pm->cod_lot;
                    }
                    ?>
                    <li>
                        <a href="#<?= tratarHTML($pm->cod_lot); ?>"><?= tratarHTML($pm->cod_lot); ?></a>
                    </li>
                    <?php
                    $grupo_setor = $pm->cod_lot;

                    $registros_processados++;
                }
                $total_sim              += ($pm->homologado == 'S' ? 1 : 0);
                $total_nao              += ($pm->homologado != 'S' ? 1 : 0);
                $unidades[$pm->cod_lot] = array($total_sim, $total_nao);
            }
            ?>
        </ul>
        <?php
        $oDBase->data_seek(0);
        $grupo_setor = "";
        while ($pm          = $oDBases->fetch_object())
        {
            if ($grupo_setor == "" || $grupo_setor != $pm->cod_lot)
            {
                if ($grupo_setor != "" && $grupo_setor != $pm->cod_lot)
                {
                    ?>
                </table>
                </form>
                </div>
                <?php
            }
            ?>
            <div id="<?= tratarHTML($pm->cod_lot); ?>">
                <form id='dados<?= tratarHTML($pm->cod_lot); ?>' name='dados<?= tratarHTML($pm->cod_lot); ?>'>
                    <div style='test-align: center;' align='center'>
                        <font size="2" face="Tahoma">Lota&ccedil;&atilde;o&nbsp;</font>
                        <input name="lot" type="text" class='alinhadoAoCentro' id="lot" style='font-size: 14px; font-weight: bold;' value='<?= tratarHTML(getUorgMaisDescricao( $pm->cod_lot )); ?>' size="95" maxlength='95' readonly>
                        &nbsp;&nbsp;&nbsp;<font size="2" face="Tahoma" color='red'>Não Homologados:&nbsp;</font><font size="2" face="Tahoma" color='red'><?= tratarHTML($unidades[$pm->cod_lot][1]); ?></font>
                    </div>
                    <div style='test-align: center;' align='center'>
                        <a href="javascript:enviar_email_periodo_homologacao('<?= tratarHTML($pm->cod_lot); ?>');" style='font-size: 9px;'>» Clique aqui para COMUNICAR À CHEFIA IMEDIATA QUE HÁ FREQUÊNCIA(S) SEM HOMOLOGAÇÃO «</a>
                        <input type="hidden" id="unidade" name="unidade" value='<?= tratarHTML($pm->cod_lot); ?>'>
                    </div>
                    <table class="thin sortable draggable" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber2" >
                        <tr bgcolor="#DBDBB7">
                            <td width="12%" align='center'><b>LOTAÇÃO</b></td>
                            <td width="14%" align='center'><b>Matr&iacute;cula</b></td>
                            <td width="60%"><b>NOME</b></td>
                        </tr>
                        <?php
                        $grupo_setor = $pm->cod_lot;
                    }
                    ?>
                    <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                        <td align='center'><?= tratarHTML($pm->cod_lot); ?></td>
                        <td align='center'><?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?><input type='hidden' id='mat_siape[]' name='mat_siape[]' value='<?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?>'></td>
                        <td><?= tratarHTML($pm->nome_serv); ?></td>
                    </tr>
                    <?php
                    $registros_processados++;
                 /*   $msg_processando = 'Processando ' . $pm->mat_siape . '- ' . substr($dpmados->nome_serv, 0, 20);
                    $objBar->setProgress(round($registros_processados * 100 / $numero_de_servidores), $registros_processados, $numero_de_servidores, 'top', $msg_processando);*/
                    //usleep(40);
                }
                ?>
            </table>
        </form>
    </div>
    </div>
    </form>
    <script>
        this.location = "<?= tratarHTML($_SESSION['tabPosition']); ?>";
    </script>
    <?php
    $objBar->hide();
}
else
{
    ?>
    <font face='verdana' size='2'>Não há Unidade com Pendência!</font>
    <?php
}

// Base do formulário
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
