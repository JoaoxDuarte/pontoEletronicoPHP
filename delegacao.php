<?php
include_once( "config.php" );
include_once( 'abrangencia.php' ); // abrangencia de consulta

verifica_permissao("sRH ou Chefia");

// pega o nome do arquivo origem
$pagina_de_origem       = pagina_de_origem();
$_SESSION['sHOrigem_1'] = $_SERVER['REQUEST_URI'];

// dados
$modo = anti_injection($_REQUEST['modo']);

// dados do servidor
$oDBase = CadastroServidor();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Cadastro » Gerencial » Delegação » Atribuir delegação');
$oForm->setDialogModal();
//$oForm->setCSS(_DIR_CSS_ . "table_sorter.css");
//$oForm->setJS(_DIR_JS_ . "jquery.tablesorter.js");
$oForm->setCSS( 'css/new/sorter/css/theme.bootstrap_3.min.css' );
$oForm->setJS( 'css/new/sorter/js/jquery.tablesorter.min.js' );
$oForm->setJS(_DIR_JS_ . 'jquery.quicksearch.js');
$oForm->setSeparador(0);
$oForm->setLargura("950px");
$oForm->setSubTitulo("Delegação de Competência");

if ($modo == 10)
{
    $oForm->setObservacaoTopo("<p align='justify'><b><font size='1'><small>1)&nbsp;</small>Para delegação de competência, selecione o servidor indicado.<br><small>2)&nbsp;</small>A delegação deve recair em servidor da mesma unidade de  da chefia, excluído dessa possibilidade o substituto previamente designado.<br><small>3)&nbsp;</small>O usuário delegado poderá realizar todas as atividades do SISREF destinadas à chefia exceto quanto à sua própria matricula.</font></b></p>");
}

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script type='text/javascript'>
    $(function ()
    {
        $('input#id_search1').quicksearch('table#AutoNumber1 tbody tr');
        $('input#id_search2').quicksearch('table#AutoNumber2 tbody tr');

        $('[data-load-remote-delegacao]').on('click',function(e) {
            var oForm = $("#form1");
            var $this = $(this);
            var remote = $this.data('load-remote-delegacao');
            var matricula = $this.data('load-remote-delegacao-atributo');

            $("#matricula").val(matricula);

            e.preventDefault();

            console.log(remote);

            oForm.attr("onSubmit", "javascript:return true;");
            oForm.attr("action", remote);
            oForm.submit();
        });
    });
</script>

<div class="container text-center">
    <div class="row margin-10 comparecimento">
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-3">
                <h5><strong>ÓRGÃO</strong></h5>
                <p><?= tratarHTML(getOrgaoMaisSigla( $sTitulo )); ?></p>
            </div>
            <div class="col-md-9">
                <h5><strong>UNIDADE/UPAG</strong></h5>
                <p><?= tratarHTML(getUorgMaisDescricao( $sTitulo )); ?></p>
            </div>
        </div>
    </div>

    <div class="row margin-10">
    <?php

    $grupo1 = 0;
    $grupo2 = 0;

    while ($oLista = $oDBase->fetch_object())
    {
        if ($oLista->portaria_inicio_data != '0000-00-00' && $oLista->portaria_inicio_data != '' && ($oLista->portaria_fim_data == '0000-00-00' || $oLista->portaria_fim_data == ''))
        {
            if ($grupo2 == 0)
            {
                $seq             = 1;
                $finalizarGrupo2 = true;

                ?>
                <form method="POST" action='#' id="form1" name="form1" onSubmit="javascript:return false;">
                    <input type="hidden" name="matricula" id="matricula" value="">
        <table class="table table-striped table-condensed table-bordered text-center" id='AutoNumber2'>
                        <thead>
                            <tr>
                                <td height='33' colspan='2' align='center'>
                                    <b>Filtrar delegados:</b>
                                    <input type='text' id='id_search2' name='search' value='' placeholder='Search' autofocus/>&nbsp;
                                </td>
                                <td style="height:33px;vertical-align:middle;" colspan='5' class='text-center'><b>SERVIDORES COM DELEGAÇÃO DE ATRIBUIÇÃO DE CHEFIA</b></td>
                            </tr>
                            <tr>
                                <td width='6%'  align='center'><b>Seq.</b></td>
                                <th width='10%' align='left' nowrap>&nbsp;<b>Matrícula</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                <?php

                                if ($_SESSION['sRH'] == 'S' && $_SESSION['sTabServidor'] == 'S')
                                {
                                    /* GERENCIA */
                                    ?>
                                    <th width='42%' align='left' nowrap>&nbsp;<b>Nome<b>&nbsp;</th>
                                    <th width='12%' align='left' nowrap>&nbsp;<b>Unidade<b>&nbsp;</th>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <th width='42%' align='left' colspan='2' nowrap>&nbsp;<b>Nome<b>&nbsp;</th>
                                    <?php
                                }

                                ?>
                                <td width='9%' align='left' nowrap>&nbsp;<b>Data da Portaria<b>&nbsp;</td>
                                <td width='9%' align='left' nowrap>&nbsp;<b>Delegação - Unidade<b>&nbsp;</td>
                                <td width='9%' align='center' nowrap>&nbsp;<b>Ação<b>&nbsp;</td>
                            </tr>
                        </thead>
                        <tbody>
                <?php

                $grupo2 = 1;
            }

            ?>
            <tr height='18'>
                <td align='center'><?= tratarHTML($seq++); ?></td>
                <td align='center'><?= tratarHTML(removeOrgaoMatricula($oLista->siape)); ?>&nbsp;</td>
                <?php

                if ($_SESSION['sRH'] == 'S' && $_SESSION['sTabServidor'] == 'S')
                {
                    /* GERENCIA */
                    ?>
                    <td width='42%' align='left' nowrap>&nbsp;<?= tratarHTML($oLista->nome); ?></td>
                    <td width='12%' align='left' nowrap title="<?= tratarHTML(getUorgDescricao($oLista->cod_lot)); ?>" alt="<?= tratarHTML(getUorgDescricao($oLista->cod_lot)); ?>">&nbsp;<?= tratarHTML(removeOrgaoLotacao($oLista->cod_lot)); ?>&nbsp;</td>
                    <td align='center'>&nbsp;<?= tratarHTML(databarra($oLista->portaria_inicio_data)); ?></td>
                    <td align='center'>&nbsp;<?= tratarHTML(($oLista->delegacao_para == '' ? "---------------" : $oLista->delegacao_para)); ?></td>
                    <?php
                }
                else
                {
                    ?>
                    <td width='42%' align='left' colspan='2' nowrap>&nbsp;<?= tratarHTML($oLista->nome); ?></td>
                    <td align='center'><?= tratarHTML($oLista->portaria_inicio_data); ?></td>
                    <td align='center'>&nbsp;---------------&nbsp;</td>
                    <?php
                }

                ?>
                <td align='center' nowrap>&nbsp;<a href="javascript:void(0)"
                                   data-load-remote-delegacao="delegaatofim.php"
                                   data-load-remote-delegacao-atributo="<?= tratarHTML($oLista->siape); ?>">Finalizar Delegação</a><b>&nbsp;</td>
            </tr>
            <?php
        }
        else
        {
            if ($grupo1 == 0)
            {
                $seq = 1;

                if ($finalizarGrupo2 == true)
                {
                    ?>
                    </tbody>
                    </table>
                    </form>
                    </div>
                    <br>
                    <?php
                }
                else
                {
                    ?>
                    </form>
                    <?php
                }

                ?>
                <form action='#' id="form1" name="form1">
       <table class="table table-striped table-condensed table-bordered text-center" id='AutoNumber1'>
                        <thead>
                            <tr>
                                <td height='33' colspan='2' align='center'>
                                    <b>Filtrar servidores:</b> <input type='text' id='id_search1' name='search' value='' placeholder='Search' autofocus/>&nbsp;
                                </td>
                                <td height='33' colspan='6' align='center'><b>SERVIDORES SEM DELEGAÇÃO</b></td>
                            </tr>
                            <tr>
                                <td width='6%'  align='center'><b>Seq.</b></td>
                                <th width='10%' align='left' nowrap>&nbsp;<b>Matrícula</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                <?php

                                if ($_SESSION['sRH'] == 'S' && $_SESSION['sTabServidor'] == 'S')
                                {
                                    /* GERENCIA */
                                    ?>
                                    <th width='42%' align='left' colspan='4' nowrap>&nbsp;<b>Nome<b>&nbsp;</th>
                                    <th width='12%' align='left' nowrap>&nbsp;<b>Unidade<b>&nbsp;</th>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <th width='42%' align='left' colspan='5' nowrap>&nbsp;<b>Nome<b>&nbsp;</th>
                                    <?php
                                }

                                ?>
                                <td width='9%' align='center' nowrap>&nbsp;<b>Ação<b>&nbsp;</td>
                            </tr>
                        </thead>
                    <tbody>
                <?php
            }

            ?>
            <tr height='18'>
                <td align='center'><?= tratarHTML($seq++); ?></td>
                <td align='center'><?= tratarHTML(removeOrgaoMatricula($oLista->siape)); ?>&nbsp;</td>
                <?php

                if ($_SESSION['sRH'] == 'S' && $_SESSION['sTabServidor'] == 'S')
                {
                    /* GERENCIA */
                    ?>
                    <td width='42%' align='left' colspan='4' nowrap>&nbsp;<?= tratarHTML($oLista->nome); ?></td>
                    <td width='12%' align='left' nowrap title="<?= tratarHTML(getUorgDescricao($oLista->cod_lot)); ?>" alt="<?= tratarHTML(getUorgDescricao($oLista->cod_lot)); ?>">&nbsp;<?= tratarHTML(removeOrgaoLotacao($oLista->cod_lot)); ?>&nbsp;</td>
                    <?php
                }
                else
                {
                    ?>
                    <td width='42%' align='left' colspan='5' nowrap>&nbsp;<?= tratarHTML($oLista->nome); ?></td>
                    <?php
                }

                ?>
                    <td align='center' nowrap>&nbsp;<a href='delegaato.php?dados=<?= tratarHTML(criptografa($oLista->siape)); ?>'>Delegar Competência</a><b>&nbsp;</td>
            </tr>
            <?php

            $grupo1          = 1;
            $finalizarGrupo1 = true;
        }
    }

    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/* ************************************************ *
 *                                                  *
 *              FUNÇÕES COMPLEMENTARES              *
 *                                                  *
 * ************************************************ */

/*
 * @param string $ano  Ano da competência da homologação
 * @param string $mes  Mês da competência da homologação
 * @param string $upag UPAG da unidade do servidor/estagiário
 *
 * @info Total de servidores/estagiarios por UPAG
 */
function CadastroServidor()
{
    global $sWhere, $sTitulo, $modo;

    include_once( 'abrangencia_nova.php' );

    $sql = "
    SELECT
        servativ.mat_siape AS siape,
        servativ.nome_serv AS nome,
        servativ.cod_lot,
        tabsetor.descricao,
        IFNULL(usuarios.datapt,'') AS portaria_inicio_data,
        IFNULL(usuarios.dtfim,'')   AS portaria_fim_data,
        IF(servativ.cod_lot = usuarios.setor,
            '',
            CONCAT('',usuarios.setor,' - ',und.descricao,'')) AS delegacao_para
    FROM
        servativ
    LEFT JOIN tabsetor  ON servativ.cod_lot = tabsetor.codigo
    LEFT JOIN ocupantes ON servativ.mat_siape = ocupantes.mat_siape
    LEFT JOIN usuarios  ON servativ.mat_siape = usuarios.siape
    LEFT JOIN tabsetor  AS und ON usuarios.setor = und.codigo
    WHERE
        servativ.excluido='N'
        " . $sWhere . "
        AND IF((usuarios.datapt <> '0000-00-00' AND usuarios.dtfim='0000-00-00'),9,10) = '".$modo."'
        AND ISNULL(ocupantes.mat_siape)
    ORDER BY
        IF((usuarios.datapt <> '0000-00-00' AND usuarios.dtfim = '0000-00-00'),0,1), servativ.nome_serv
    ";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query($sql);

    return $oDBase;
}
