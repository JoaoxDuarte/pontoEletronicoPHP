<?php
include_once("config.php");
include_once("src/controllers/DadosServidoresController.php");


verifica_permissao("sAPS");

$dia      = (isset($dia) ? $dia : date('d/m/Y'));
$cmd      = (isset($cmd) ? $cmd : '2');
$orig     = (isset($orig) ? $orig : '1');
$qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);

$vDatas = conv_data($dia);
$mes    = dataMes($dia);
$ano    = dataAno($dia);
$comp   = $mes . $ano;

if ($mes != date('m'))
    header("Location: acessonegado.php");

if (pagina_de_origem() != 'autorizacao_acumulo.php')
{
    unset($_SESSION['bh_acumulo_ciclo']);
}


$oDBase = new DataBase('PDO');

$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setCSS( 'css/new/sorter/css/theme.bootstrap_3.min.css' );
$oForm->setJS( 'css/new/sorter/js/jquery.tablesorter.min.js' );
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();


// PRÉ FILTRO DO SELECT
$obj = boxCiclos();
$obj = $obj->fetch_assoc();
$id          = $obj['id'];
$data_inicio = $obj['data_inicio'];
$data_fim    = $obj['data_fim'];

if(!empty($_POST))
{
    $id                           = $_POST['id'];
    $_SESSION['bh_acumulo_ciclo'] = $id;
}
else if (isset($_SESSION['bh_acumulo_ciclo']) && !empty($_SESSION['bh_acumulo_ciclo']))
{
    $id = $_SESSION['bh_acumulo_ciclo'];
}


// seleciona os registros para homologação
$oDados           = new DadosServidoresController();
$oServidores      = $oDados->selecionaServidoresUnidadeBancoHoras();
$total_servidores = $oServidores->num_rows();

$boxCiclos = boxCiclos();

?>
<style>
    .inibir_opcao {
        opacity: 0.15;
        -moz-opacity: 0.15;
        filter: alpha(opacity=15);
    }
</style>
<script>
    $(document).ready(function () {
        $(function () {
            $('#myTable').tablesorter();
            $('.ciclos').change(function(){
                $("[name='filterhdn']").val(true);
                $("[name='form-autorizacoes']").submit();
            });
        });

        $('[data-load-remote-autoriza-acumulo]').on('click',function(e) {
            var oForm = $("#form_menu_acumulo");
            var $this = $(this);
            var remote = $this.data('load-remote-autoriza-acumulo');
            var siape  = $this.data('load-siape');

            $('#siape_autorizar').val( siape );

            e.preventDefault();

            console.log(remote);

            oForm.attr("onSubmit", "javascript:return true;");
            oForm.attr("action", remote);
            oForm.submit();
        });
    });
</script>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Autorização de Acúmulo de Horas</strong></h4>
        </div>

        <div class="row">
            <div class="col-md-2 text-right">
                <p><b>ÓRGÃO: </b><?= tratarHTML(getOrgaoByUorg( $qlotacao )); ?></p>
            </div>
            <div class="col-md-9 text-right">
                <p><b>UORG: </b><?= tratarHTML(getUorgMaisDescricao( $_SESSION['sLotacao'] )); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2 text-right" style="margin-top: 8px;">
                <p><b>Selecione o ciclo: </b></p>
            </div>
            <div class="col-md-4 text-left">
                <form method="POST" name="form-autorizacoes" action="autorizacoes_acumulos.php">
                    <select class="form-control ciclos" name="id">

                         <?php while ($ciclo = $boxCiclos->fetch_assoc()): ?>

                            <?php if($ciclo['id'] == $id): ?>
                                <option selected value="<?= tratarHTML($ciclo['id']); ?>"> <?= tratarHTML($ciclo['ciclo']); ?></option>
                            <?php else: ?>
                                 <option value="<?= tratarHTML($ciclo['id']); ?>"> <?= tratarHTML($ciclo['ciclo']); ?></option>
                            <?php endif; ?>

                         <?php  endwhile; ?>

                    </select>
                </form>
            </div>
        </div>

        <div class="col-md-12">
            <br>
            <div class="row">
                <fieldset width='100%'>Total de <?= tratarHTML($total_servidores); ?> registros.</fieldset>

                <form method="POST" id="form_menu_acumulo" name="form_menu_acumulo" action="#" onSubmit="javascript:return false;">
                    <input type="hidden" id="siape_autorizar" name="siape_autorizar" value="">
                    <input type="hidden" id="ciclo" name="ciclo" value="<?= tratarHTML($id); ?>">

                <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
                    <thead>
                        <tr>
                            <th class="text-center" style='vertical-align:middle;'>Matrícula</th>
                            <th class="text-center" style='vertical-align:middle;'>Nome do Servidor</th>
                            <th class="text-center" style='vertical-align:middle;'>Período Autorizado</th>
                            <th class="text-center" style='vertical-align:middle;'>Permite Acúmulo<br>de Banco de Horas</th>
                            <th class="text-center" style='vertical-align:middle;'>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        while ($servidor = $oServidores->fetch_object()):

                            $result = checkServidor($id , $servidor->siape);

                            if(empty($result))
                                $result['periodo'] = "Nenhum Período Autorizado";

                            ?>
                            <tr>
                                <td align='center'><?= tratarHTML(removeOrgaoMatricula( $servidor->siape )); ?></td>
                                <td class='text-left'><?= tratarHTML($servidor->nome); ?></td>
                                <td align='center'><?= tratarHTML($result['periodo']); ?></td>
                                <?php

                                $bool = verificaPermissoesAcumulo($servidor->siape, $servidor->horae, $servidor->motivo, $servidor->limite_horas, $servidor->permite_banco, $servidor->excecao, $servidor->plantao_medico);

                                if ($bool['blocked']): ?>
                                    <td align='center'>
                                        <img style="cursor: pointer"
                                             border='0'
                                             src='<?= _DIR_IMAGEM_; ?>warning.png'
                                             width='16'
                                             height='16'
                                             align='absmiddle'
                                             alt='Editar'
                                             title='<?= tratarHTML($bool['titulo']); ?>'>
                                    </td>
                                <?php else: ?>
                                    <td align='center'>
                                        <img style="cursor: pointer"
                                             border='0'
                                             src='<?= _DIR_IMAGEM_; ?>visto_blue.gif'
                                             width='16'
                                             height='16'
                                             align='absmiddle'>
                                    </td>
                                <?php endif; ?>

                                <?php if(checkCicloCurrent($id)):

                                    if (!$bool['blocked']): ?>
                                        <td align='center'>
                                            <a href="#"
                                               data-load-remote-autoriza-acumulo='autorizacao_acumulo.php'
                                               data-load-siape="<?= tratarHTML($servidor->siape); ?>">
                                                <span class="glyphicon glyphicon-pencil" alt="Editar" title="Editar"></span>
                                                <!--
                                                <img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg'
                                                     width='16'
                                                     height='16'
                                                     align='absmiddle'
                                                     alt='Editar'
                                                     title='<?= $bool['titulo']; ?>'></a>
                                                -->
                                        </td>
                                    <?php else: ?>
                                        <td align='center'></td>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <td align='center'></td>
                                <?php endif; ?>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>
                </table>

                </form>

            </div>
        </div>
    </div>
</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

