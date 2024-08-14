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

$setor = $_SESSION['sLotacao'];
$upag  = $_SESSION['upag'];

// dados voltar
$_SESSION['voltar_nivel_1'] = 'sisref_relatorio_servicos_extraordinarios_realizados.php';
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

// seleciona descricao do setor
$wnomelota = getUorgDescricao($setor);

// seleciona os servidores com frequencia homologada
$oDBase = DadosDoServidor($ano, $mes, $upag);
$num    = $oDBase->num_rows();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
//$oForm->setCSS("css/bootstrap-print.css");
$oForm->setJQuery();
$oForm->setJSSelect2();
$oForm->setSubTitulo("Relatório Serviços Extraordinários Realizados");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<style>
@media print {
}
</style>
<script>
    $(document).ready(function ()
    {
        // Set the "bootstrap" theme as the default theme for all Select2
        // widgets.
        //
        // @see https://github.com/select2/select2/issues/2927
        $.fn.select2.defaults.set("theme", "bootstrap");

        var placeholder = "Selecione uma unidade";

        $(".select2-single").select2({
            placeholder: placeholder,
            width: '100%',
            containerCssClass: ':all:'
        });

        var $antes = "";
        $('select').on('change', function() {
            if ($antes !== "")
            {
                $($antes).css("display","none");
            }

            if (this.value === 'todos')
            {
                $("[name=unidades]").css('display', "block");
            }
            else
            {
                $("[name=unidades]").css('display', "none");
            }
            $antes = this.value;

            $($antes).css("display","block");
            return false;
        });

        $('[data-load-remote]').on('click',function(e) {
            e.preventDefault();
            var $this = $(this);
            var remote = $this.data('load-remote');
            if(remote) {
                $($this.data('remote-target')).load(remote);
            }
        });

        /*JS print click handler*/
        $('#btnPrint').on('click', function(){
            var ficha = $('.modal-body-conteudo').html();
            var ventimp = window.open(' ', 'popimpr');


            ficha = '<html>'
                    + '<head>'
                    + '<link type="text/css" rel="stylesheet" href="css/new/css/bootstrap.min.css">'
                    + '<link type="text/css" rel="stylesheet" href="css/new/css/custom.css">'
                    + '<link type="text/css" rel="stylesheet" href="css/estilos_new_layout.css">'
                    + '<link type="text/css" rel="stylesheet" href="css/new/css/bootstrap-dialog.min.css">'
                    + '<link type="text/css" rel="stylesheet" href="css/new/js/bootstrap-table/bootstrap-table.css">'
                    + '<link type="text/css" rel="stylesheet" href="css/bootstrap-print-small.css" media="print">'
                    + '<script type="text/javascript" src="js/jquery-2.2.0.min.js"><\/script>'
                    + '<script type="text/javascript" src="js/funcoes.js"><\/script>'
                    + '<script type="text/javascript" src="js/fc_data.js"><\/script>'
                    + '<script type="text/javascript" src="css/new/js/bootstrap.min.js"><\/script>'
                    + '<script type="text/javascript" src="css/new/js/bootstrap-dialog.min.js"><\/script>'
                    + '</head>'
                    + '<body>'
                    + $('.modal-body-conteudo').html()
                    + '<footer class="footer">'
                    + '<div class="container">'
                    + '<p class="text-muted">Sistema de Registro Eletrônico de Frequência | Ministério do Planejamento</p>'
                    + '</div>'
                    + '</footer>'
                    + '</body>'
                    + '</html>';

            ventimp.document.write( ficha );
            ventimp.document.close();
            ventimp.print( );
            //ventimp.close();
        });
    });

    function closeIFrame()
    {
        $('#dialog-view').dialog('close');
        parent.main.location.reload();
        return false;
    }
</script>

<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <?php exibeDescricaoOrgaoUorg($upag); // Exibe Órgão e Uorg com suas descrições padrão: $_SESSION['sLotacao']; ?>

    <div class="row margin-25">
        <div class="row">
            <div class="col-md-2 text-left" style="margin-top: 5px;">
                <p><b>Selecione a Unidade: </b></p>
            </div>
            <div class="col-md-8 text-left">
                <?php CarregaSelectUnidades($ano, $mes, $upag); ?>
            </div>
        </div>

        <table class="table table-striped table-bordered text-center table-hover">
            <thead>
                <tr>
                    <th class="text-center" colspan="5"><h4><b><?= tratarHTML($mes) . '/' . tratarHTML($ano); ?></b></h4></th>
                </tr>
            </thead>
            <?php

            if ($num === 0)
            {
                ?>
                <tbody id='registros_selecionados' class='sse_listar'>
                    <tr>
                        <td colspan='4'>
                            <font face='verdana' size='2'>Não há servidores para verificação!</font>
                        </td>
                    </tr>
                </tbody>
                <?php
            }

            ?>
        </table>
        <?php

        if ($num > 0)
        {
            $dados       = array();
            $grupo_setor = "";

            while ($pm = $oDBase->fetch_object())
            {
                if ($grupo_setor != "" && $grupo_setor != $pm->cod_lot)
                {
                    ServidoresPorUnidade($dados);
                    $dados = array();
                }
                $grupo_setor = $pm->cod_lot;

                $dados[] = array(
                    'siape'     => $pm->mat_siape,
                    'nome'      => $pm->nome_serv,
                    'cod_lot'   => $pm->cod_lot,
                    'descricao' => $pm->descricao,
                    'status'    => $pm->status,
                    'jornada'   => $pm->jornada,
                );
            }
        }

        ?>
    </div>

</div>

<!-- Aqui o conteúdo será mostrado -->
<div class="modal fade" id="myModalVisual" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-body modal-dialog modal-lg modal-content" style='width:1100px;'>
        <div class="modal-footer text-right margin-25">
            <button class="btn btn-inverse text-left" id="btnPrint">Imprimir</button>
            <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
            <button class="btn btn-primary">Rejeitar</button>
        </div>
        <div class="modal-body-conteudo">One fine body…</div>
        <div class="modal-footer text-right">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
            <button class="btn btn-primary">Rejeitar</button>
        </div>
    </div>
</div>
<div id="htmlPrintHeader"></div>
<div id="htmlPrintFooter"></div>

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
function UnidadesTotalDeServidores($ano, $mes, $upag)
{
    $compet = $ano . $mes;

    $sql = "
    SELECT
        cad.cod_lot, und.descricao, und.cod_uorg_pai, und.uorg_pai,
        SUM(IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),1,0)) AS homologados_nao,
        SUM(IF(IFNULL(homologados.homologado,'N') IN ('V','S'),1,0))     AS homologados_sim,
        SUM(IF(IFNULL(homologados.homologado,'N')='V',1,0))              AS homologados_visto,
        COUNT(*) AS total
    FROM
        servativ AS cad
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        homologados ON (cad.mat_siape = homologados.mat_siape) AND (homologados.compet = :compet)
    WHERE
        cad.excluido = 'N'
        AND cad.cod_sitcad NOT IN ('02','15','08')
        AND und.upag = :upag
        AND DATE_FORMAT(cad.dt_adm,'%Y%m') <= :compet
    GROUP BY
        cad.cod_lot
    ";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query(
        $sql,
        array(
            array( ":compet", $compet, PDO::PARAM_STR ),
            array( ":upag",   $upag,   PDO::PARAM_STR ),
        )
    );

    return $oDBase;
}


/*
 * @param string $ano  Ano da competência da homologação
 * @param string $mes  Mês da competência da homologação
 * @param string $upag UPAG da unidade do servidor/estagiário
 *
 * @info Lista as unidades e servidores para verificar se foram homologados
 */
function DadosDoServidor($ano, $mes, $upag)
{
    $compet = $ano . $mes;

    $sql = "
    SELECT
        cad.mat_siape, cad.nome_serv, cad.jornada, cad.cod_lot, und.descricao,
        IFNULL(homologados.homologado,'N') AS `status`
    FROM
        servativ AS cad
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        homologados ON (cad.mat_siape = homologados.mat_siape) AND (homologados.compet = :compet)
    WHERE
        cad.excluido = 'N'
        AND cad.cod_sitcad NOT IN ('02','15','08')
        AND und.upag = :upag
        AND DATE_FORMAT(cad.dt_adm,'%Y%m') <= :compet
    ORDER BY
        cad.cod_lot,
        IF(IFNULL(homologados.homologado,'N')='N',1,
            IF(IFNULL(homologados.homologado,'N')='S',2,3)),
        cad.nome_serv
    ";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query(
        $sql,
        array(
            array( ":compet", $compet, PDO::PARAM_STR ),
            array( ":upag",   $upag,   PDO::PARAM_STR ),
        )
    );

    return $oDBase;
}


/*
 * @param string $ano  Ano da competência da homologação
 * @param string $mes  Mês da competência da homologação
 * @param string $upag UPAG da unidade do servidor/estagiário
 *
 * @info Lista as unidades
 */
function CarregaSelectUnidades($ano, $mes, $upag)
{
    $oDBase = UnidadesTotalDeServidores($ano, $mes, $upag);

    ?>
    <select class="form-control select2-single">
        <option value=''>Selecione uma unidade</option>
        <option value='todos'>TODAS AS UNIDADES</option>
        <?php

        $num_unidades = $oDBase->num_rows();

        while ($pm = $oDBase->fetch_object())
        {
            ?>
            <option value='#<?= tratarHTML($pm->cod_lot); ?>'><?= tratarHTML(getUorgMaisDescricao($pm->cod_lot)); ?></option>
            <?php
        }

        ?>
    </select>
    <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(<?= tratarHTML($num_unidades); ?> unidades)</small>
    <?php
}

/*
 * @param array $dados  Dados dos servidores selecionados
 *
 * @info Lista servidores, por unidade, para verificar se foram homologados
 */
function ServidoresPorUnidade($dados)
{
    ?>
    <div id="<?= $dados[0]['cod_lot']; ?>" name='unidades' style="display:none;">
        <table class="table table-striped table-bordered text-center table-hover margin-25">
            <thead>
                <tr style="border:1px solid white;padding:0px;margin:0px;">
                    <td colspan="4" style="border:0px solid white;padding:0px;margin:0px;">
                            <div class="col-md-8 text-left" id="total_de_registros" style="padding:0px;margin:0px;">
                                <p style="padding:0px;margin:0px;vertical-align:bottom;"><b>Unidade: <?= tratarHTML(getUorgMaisDescricao($dados[0]['cod_lot'])); ?></b></p>
                            </div>
                            <div class="col-md-4 text-right" id="total_de_registros" style="vertical-align:bottom;">
                                Total de <?= number_format( count($dados), 0, ',', '.' ); ?> registros.
                            </div>
                    </td>
                </tr>
                <tr>
                    <th class="text-center" style="width: 12%;">UNIDADE</th>
                    <th class="text-center" style="width: 14%;">MATR&Iacute;CULA</th>
                    <th class="text-left"   style="width: 60%;">NOME</th>
                    <th class="text-center" style="width: 14%;">A&Ccedil;&Atilde;O</th>
                </tr>
            </thead>
            <tbody id='registros_selecionados' class='sse_listar'>
                <?php

                for ($x=0; $x < count($dados); $x++)
                {
                    ?>
                    <tr>
                        <td class="text-center" style="width: 12%;"><?= tratarHTML(removeOrgaoLotacao($dados[$x]['cod_lot'])); ?></td>
                        <td class="text-center" style="width: 14%;"><?= tratarHTML(removeOrgaoMatricula($dados[$x]['siape'])); ?></td>
                        <td class="text-left"   style="width: 60%;"><?= tratarHTML($dados[$x]['nome']); ?></td>
                        <td class="text-center" style="width: 14%;">
                            <?php

                                switch ($dados[$x]['status'])
                                {
                                    case 'S':
                                        $params = base64_encode(tratarHTML($dados[$x]['siape']) . ":|:" . tratarHTML($dados[$x]['cod_lot']) . ":|:" . tratarHTML($dados[$x]['jornada']));

                                        ?>
                                        <a href="#myModalVisual" role="button" class="btn" data-toggle="modal" data-load-remote="frequencia_verificar_homologados_visualizar.php?dados=<?= $params; ?>" data-remote-target="#myModalVisual .modal-body-conteudo">Homologado (Verificar)</a>
                                        <!--
                                        <span class="btn btn-primary" data-toggle="modal" data-target="#appModal" data-remote="frequencia_verificar_homologados_visualizar.php?dados=<?= $params; ?>">Homologado (Verificar)</span>
                                        -->
                                        <?php
                                        break;

                                    case 'V':
                                        ?>
                                        <font style="color:blue;">Concluído</font>
                                        <?php
                                        break;

                                    case 'N':
                                    default:
                                        ?>
                                        <font style="color:red;font-weight:bold">Não Homologado</font>
                                        <?php
                                        break;
                                }
                            ?>
                        </td>
                    </tr>
                    <?php
                }

                ?>
            </tbody>
        </table>
    </div>
    <?php
}