<?php
include_once("config.php");

verifica_permissao("sRH");

$teste = false;

if(!empty($_POST)){
    $alterar['data_inicial'] = reverte_data($_POST['data_inicio']);
    $alterar['data_final'] = reverte_data($_POST['data_final']);

    $alterar['campos'] = $_POST['campos'];
    $alterar['minutos'] = sec_to_time(time_to_sec($_POST['minutos']),'hh:mm');
    $alterar['exibe'] = $_POST['exibe'];
    $alterar['ativo'] = $_POST['ativo'];
    $alterar['mensagem'] = $_POST['mensagens'];
    $alterar['observacao'] = $_POST['observacao'];
    $alterar['id'] = $_POST['id'];

    update_configuracoes($alterar);
    registraLog("Alterou o campo de id ".$alterar['id']);
    $mensagemUsuario = "Configurações alteradas com sucesso.";
    $teste = true;
}


$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// dados voltar
$_SESSION['voltar_nivel_2'] = 'gerais_alterar.php';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';


$oForm = new formPadrao();
$oForm->setSubTitulo("Editar Configuração");

$css = array();
$css[] = 'js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css';

$javascript = array();
$javascript[] = 'js/ckeditor/ckeditor.js';
$javascript[] = 'js/ckeditor/config.js';
$javascript[] = 'js/ckeditor/lang/pt-br.js';
$javascript[] = 'js/ckeditor/styles.js';
$javascript[] = 'js/jquery.mask.min.js';
$javascript[] = 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js';
$javascript[] = 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js';



// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


$dados_configuracao = getConfiguracao($_GET['id']);

if($dados_configuracao['ativo']){
    $ativo = '';
    if($dados_configuracao['ativo']=='N') {
        $ativo .= '<option value="' . tratarHTML($dados_configuracao['ativo']) . '">Não</option><option value="S">Sim</option>';
    }else{
        $ativo .= '<option value="' . tratarHTML($dados_configuracao['ativo']) . '">Sim</option><option value="N">Não</option>';
    }


}

if($dados_configuracao['exibe']){
    $exibe = '';
    if($dados_configuracao['exibe']=='N') {
        $exibe .= '<option value="' . tratarHTML($dados_configuracao['exibe']) . '">Não</option><option value="S">Sim</option>';
    }else{
        $exibe .= '<option value="' . tratarHTML($dados_configuracao['exibe']) . '">Sim</option><option value="N">Não</option>';
    }


}

?>
    <script>
        $(document).ready(function () {
            <?php if ($teste): ?>
                alert("Alterado com sucesso!");
                window.location.href="gerais_lista.php";
            <?php endif; ?>

            $('.minutos').mask('000:00', {reverse: true});

            var configPadrao = {
                toolbarGroups: [
                    /*{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },*/
                    /*{ name: 'tools' },*/
                    /*{ name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },*/
                    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                    { name: 'paragraph',   groups: [ 'list', 'indent', 'align'/*, 'blocks'*/ ] },
                    { name: 'styles' },
                    /*{ name: 'colors' }*/
                ],
                resize_enabled: false,
            };
            
            CKEDITOR.replace('mensagem', configPadrao);
            CKEDITOR.replace('observacoes', configPadrao);

            $('#date2 .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                language: "pt-BR",
                orientation: "bottom auto",
                startDate: "<?= $ciclo['data_inicio']; ?>",
                endDate: "<?= $ciclo['data_fim']; ?>",
                autoclose: true,
                todayHighlight: true,
                toggleActive: true,
                maxViewMode: 0,
                datesDisabled: ['10/06/2018', '10/21/2018']
            }).on('show', function(ev){
                var $this = $(this); //get the offset top of the element
                var eTop  = $this.offset().top; //get the offset top of the element
                $("td.old.disabled.day").css('color', '#e9e9e9');
                $("td.new.disabled.day").css('color', '#e9e9e9');
                $("td.disabled.day").css('color', '#e9e9e9');
                $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
            });


            $(document).on('click', '#btn-salvar-ciclo', function () {
                if(!validateForm()){
                    return false;
                }

                $('#observacao').val(CKEDITOR.instances.observacoes.getData());
                jQuery('#form1').submit();


                // jQuery.ajax({
                //     type: "POST",
                //     url: "gerais_alterar.php",
                //     data: dados,
                //     success: function( data )
                //     {
                //         alert('Alteração realizada com sucesso!');
                //          window.location.href="gerais_lista.php";
                //         //window.location('gerais_lista.php');
                //     }
                // });

                return false;
            });

        });

        function validateForm() {

            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();
            var minutos = $("[name='minutos']").val();
            var exibe = $("[name='exibe']").val();
            var ativo = $("[name='ativo']").val();

            // // Verifica se a data inicial foi informada
            // if (datestart === "" || datestart=="00/00/0000") {
            //
            //     alert("Data início é obrigatório!");
            //     return false;
            // }

            // Verifica se a data final foi informada
            // if (dateend === "" || dateend=="00/00/0000") {
            //     alert("Data fim é obrigatório!");
            //     return false;
            // }

            // Verifica se a data inicial foi informada
            if (minutos === "") {
                alert("Minutos são obrigatórios!");
                return false;
            }
            if (exibe === "") {
                alert("Exibe é obrigatório!");
                return false;
            }
            if (ativo === "") {
                alert("Ativo é obrigatório!");
                return false;
            }


            return true;
            // Vefifica se o range de datas selecionado é válido.
           // validateRangeDates();

        }


        /**
         * Formata a data para futuras validações
         */
        function ConverteParaData(data) {

            var dataArray = data.split('/');
            var novaData = new Date(dataArray[2], dataArray[1], dataArray[0]);

            return novaData;
        }

        /**
         * Valida se os anos das duas datas são iguais
         */
        function validateYears(firstdate, lastdate) {
            var first = firstdate.split("/");
            var last = lastdate.split("/");
            console.log(first[2] === last[2]);

            if (first[2] === last[2])
                return false;

            return true;
        }

    </script>

    <div class="portlet-body form">

        <form id="form1" name="form1" method="POST" action="gerais_alterar.php">
            <input type="hidden" value="<?= tratarHTML($_GET['id']); ?>" name="id">
            <input type="hidden" value="" id="mensagens" name="mensagens">
            <input type="hidden" value="" id="observacao" name="observacao">
            <div class="row">
                <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                    <font class="ft_13_003">Campo:</font>
                    &nbsp;<input type="text" id="campo" name="campo" class="form-control"
                                 value="<?= tratarHTML($dados_configuracao['campo']); ?>"  readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-3 col-xs-6 col-sm-3">
                    <font class="ft_13_003">Minutos:</font>
                    &nbsp;<input type="text" id="minutos" name="minutos" class="form-control minutos"
                                 value="<?= tratarHTML($dados_configuracao['minutos']); ?>" size="6" maxlength="6" >
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Mensagem:</font>
                    <div class='input-group'>
                        <textarea  style="resize: none" type='text' name="mensagem" id="mensagem" class="mensagem">
                            <?= tratarHTML($dados_configuracao['mensagem']); ?>
                        </textarea>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Início:</font>
                    <div class='input-group date'>
                        <input type='text' name="data_inicio" placeholder="dd/mm/aaaa" value="<?= formata_data($dados_configuracao['inicio']); ?>"class="form-control" id="date2" />
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Fim:</font>
                    <div class='input-group date'>
                        <input type='text' name="data_final" placeholder="dd/mm/aaaa" value="<?= formata_data($dados_configuracao['fim']); ?>"  class="form-control date2"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Exibe:</font>
                    <select name="exibe" class="form-control form-control-lg">
                        <?= $exibe; ?>
                    </select>

                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Ativo:</font>
                    <select name="ativo" class="form-control form-control-lg">
                        <?= $ativo; ?>
                    </select>
                    </div>
            </div>
            <div class="row">

                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Observação:</font>
                     <textarea name="observacoes" style="resize:none" class="form-control col-lg-6 col-md-6 col-xs-6 col-sm-6 " id="observacoes" rows="6"><?= tratarHTML($dados_configuracao['observacao']); ?></textarea>
                </div>
            </div>
            <div class="row">
                <br>
                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <div class="col-md-2 col-xs-4 col-md-offset-2">

                        <a class="btn btn-success btn-block" id="btn-salvar-ciclo" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Salvar
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-4">
                        <a class="btn btn-danger btn-block" id="btn-voltar"
                           href="javascript:window.location.replace('/sisref/gerais_lista.php')" role="button">
                            <span class="glyphicon glyphicon-arrow-left"></span> Cancelar
                        </a>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>
        </form>
    </div>
<?php

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
