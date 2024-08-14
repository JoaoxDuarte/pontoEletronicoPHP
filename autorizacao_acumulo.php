<?php

include_once("config.php");

verifica_permissao("sAPS");

if($_GET['validarDatas']) {
    echo validaRangeDatesIntoCiclo($_GET['datastart'], $_GET['dataend'] , $_GET['ciclo']);
    die;
}

if(!empty($_POST) && !empty($_POST['ciclo_id'])){

    $siape      = $_POST['siape']; // siape
    $ciclo_id   = $_POST['ciclo_id']; // Id do ciclo
    $start_date = $_POST['data_inicio']; // Data Inicial sem formatações
    $end_date   = $_POST['data_final']; // Data Final sem formatações

    // Formatação das datas
    $dateini         = explode("/", $start_date);
    $datefim         = explode("/", $end_date);
    $dateiniformated = $dateini[2] . "-" . $dateini[1] . "-" . $dateini[0];
    $datefimformated = $datefim[2] . "-" . $datefim[1] . "-" . $datefim[0];

    $bool = true; // Variável de controle

    // Verifica se a data final foi informada
    if(empty($end_date) AND $bool){
        setMensagemUsuario('Data Final não informada!','danger');
        $bool = false;
    }

    // Verifica se as datas não são de anos diferentes
    if (validateYears($start_date , $end_date) AND $bool) {
        setMensagemUsuario('O Ciclo precisa estar dentro de um mesmo ano!','danger');
        $bool = false;
    }

    // Verifica se a data inicial é maior que a data final-
    if ((strtotime($dateiniformated) > strtotime($datefimformated)) AND $bool) {
        setMensagemUsuario('Data inicial não pode ser maior que a final!','danger');
        $bool = false;
    }

    // Vefifica se o range de datas selecionado é válido.
    $result = json_decode(validaRangeDatesIntoCiclo($start_date, $end_date , $ciclo_id));
    if($result->bloqueia_cadastro AND $bool){
        setMensagemUsuario('Datas estão fora do ciclo!','danger');
        $bool = false;
    }

    if($bool) {
        create_or_update_autorizacao($_POST);

        replaceLink("autorizacoes_acumulos.php");
    }

}


if (!empty($_POST) && !empty($_POST['ciclo']))
{
    $_SESSION['bh_acumulo_ciclo'] = $_POST['ciclo'];
    $_SESSION['bh_acumulo_siape'] = $_POST['siape_autorizar'];
}


$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// dados voltar
$_SESSION['voltar_nivel_2'] = "frequencia_acompanhar_registros_horario_servico.php?dados=" . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';


$oForm = new formPadrao();
$oForm->setSubTitulo("Autorização de acúmulo de horas");
$oForm->setJSDatePicker();

// Topo do formulário
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// instancia o banco de dados
$oDBase = new DataBase('PDO');

$servidor = getServidorCiclo($_SESSION['bh_acumulo_siape'], $_SESSION['bh_acumulo_ciclo']);

// FORMATAÇÕES DAS DATAS PARA DENTRO DA TABLE
$oCiclo = getCiclo($_POST['ciclo']);
$ciclo = $oCiclo->fetch_assoc();
$dateiniformated = explode('-', $ciclo['data_inicio']);
$datefimformated = explode('-', $ciclo['data_fim']);

$dateiniformated = $dateiniformated[2]."/".$dateiniformated[1]."/".$dateiniformated[0];
$datefimformated = $datefimformated[2]."/".$datefimformated[1]."/".$datefimformated[0];

?>
    <script>
        $(document).ready(function () {
            $('#date .input-group.date').datepicker({
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

            $(document).on('click', '#btn-salvar-autorizacao', function () {
                validateForm();
            });
        });

        /**
         *  Início Variáveis Globais
         */
        var parsed;
        var validaDates;

        /**
         * Fim Variáveis Globais
         */


        /**
         * Inicia uma serie de validações
         */
        function validateForm() {

            showProcessandoAguarde();

            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();

            // Verifica se a data inicial foi informada
            if (datestart === "") {
                mostraMensagem("Data inicial é obrigatória!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se a data final foi informada
            if (dateend === "") {
                mostraMensagem("Data final é obrigatória!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se as datas não são de anos diferentes
            if (validateYears(datestart, dateend)) {
                mostraMensagem("O Ciclo precisa estar dentro de um mesmo ano!", 'warning');
                hideProcessandoAguarde();
                return false;
            }


            // Verifica se a data inicial é maior que a data final-
            if (ConverteParaData(datestart) > ConverteParaData(dateend)) {
                mostraMensagem("Data inicial não pode ser maior que a final!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Vefifica se o range de datas selecionado é válido.
            validateRangeDatesIntoCiclo();
            setTimeout(function () {
                if (validaDates) {
                    mostraMensagem("Datas estão fora do ciclo!", 'warning');
                    hideProcessandoAguarde();
                    return false;
                }else{
                    var oForm = $('#form1');
                
                    oForm.attr('onsubmit', 'javascript: return true;');
                    oForm.attr('action', 'autorizacao_acumulo.php');
                    oForm.submit();
                }
            }, 1000);
        }

        /**
         * Valida o range das datas para não ser criada um novo ciclo em um periodo onde ja existe outro para o  mesmo órgão
         */

        function validateRangeDatesIntoCiclo() {
            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();
            var ciclo = $("[name='ciclo_id']").val();

            $.get(
                "autorizacao_acumulo.php",
                "datastart=" + datestart +
                "&dataend=" + dateend +
                "&ciclo=" + ciclo +
                "&validarDatas=true",
                function (data) {
                    parsed = JSON.parse(data);
                    if (parsed.success) {
                        return validaDates = parsed.bloqueia_cadastro;
                    }
                });
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

       <!-- <h4 class=""><strong>Ciclos de Banco de Horas</strong></h4>-->


    <div class="portlet-body form">

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <form method='POST' id="form1" name="form1" action="#" onSubmit="javascript:return false;">
            <input type="hidden" value="<?= tratarHTML($_POST['siape_autorizar']); ?>" name="siape">
            <input type="hidden" value="<?= tratarHTML($_POST['ciclo']); ?>" name="ciclo_id">
            
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10">
                    <font class="ft_13_003">Servidor:</font>
                    &nbsp;<input type="text" id="lota" name="lota" class="form-control" size="19" maxlength="19"
                                 value="<?= tratarHTML($servidor['nome']); ?>" size="5" readonly>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10" id="date">
                    <font class="ft_13_003">Data Inicial:</font>
                    <div class='input-group date'>
                        <input type='text' id="data_inicio" name="data_inicio" placeholder="dd/mm/aaaa" value="<?= tratarHTML($servidor['data_inicio']); ?>" autocomplete="off" class="form-control"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10" id="date2">
                    <font class="ft_13_003">Data Final:</font>
                    <div class='input-group date'>
                        <input type='text' id="data_final" name="data_final" placeholder="dd/mm/aaaa" value="<?= tratarHTML($servidor['data_fim']); ?>" autocomplete="off" class="form-control"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <br>
                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <div class="col-md-2 col-xs-4 col-md-offset-2">
                        <a class="btn btn-success btn-block" id="btn-salvar-autorizacao" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Salvar
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-4">
                        <a class="btn btn-danger btn-block" id="btn-voltar"
                           href="javascript:window.location.replace('/sisref/autorizacoes_acumulos.php')" role="button">
                            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                        </a>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>
        </form>
    </div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
