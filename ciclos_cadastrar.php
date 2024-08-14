<?php

include_once("config.php");
include_once( "src/controllers/TabBancoDeHorasCiclosControllers.php" );

verifica_permissao("sRH");

// instancia classe
$objTabBancoDeHorasCiclosController = new TabBancoDeHorasCiclosController();

// link de retorno
if(isset($_GET['org'])) 
{
    $link_retorno = "tabciclos_banco_horas.php?aba=pri";
}
else
{
    $link_retorno = "tabvalida.php?aba=qui";
}


if($_GET['validarDatas']) 
{
    echo $objTabBancoDeHorasCiclosController->validaRangeDates($_GET['datastart'], $_GET['dataend']);
    die;
}

if(!empty($_POST)){

    $start_date = $_POST['data_inicio']; // Data Inicial sem formata��es
    $end_date = $_POST['data_final']; // Data Final sem formata��es
    $lotacao = $_POST['lota']; // Lota��o

    // Formata��o das datas
    $dateiniformated = conv_data($start_date);
    $datefimformated = conv_data($end_date);

    $bool = true; // Vari�vel de controle

    // Verifica se a data inicial foi informada
    if(empty($start_date) AND $bool)
    {
        setMensagemUsuario('Data Inicial n�o informada!','danger');
        $bool = false;
    }

    // Verifica se a data final foi informada
    if(empty($end_date) AND $bool)
    {
        setMensagemUsuario('Data Final n�o informada!','danger');
        $bool = false;
    }

    // Verifica se as datas n�o s�o de anos diferentes
    if (validateYears($start_date , $end_date) AND $bool)
    {
        setMensagemUsuario('O Ciclo precisa estar dentro de um mesmo ano!','danger');
        $bool = false;
    }

    // Verifica se a data inicial � maior que a data final-
    if ((strtotime($dateiniformated) > strtotime($datefimformated)) AND $bool)
    {
        setMensagemUsuario('Data inicial n�o pode ser maior que a final!','danger');
        $bool = false;
    }

    // Vefifica se o range de datas selecionado � v�lido.
    $result = json_decode($objTabBancoDeHorasCiclosController->validaRangeDates($start_date, $end_date));
    
    if($result->bloqueia_cadastro AND $bool)
    {
        setMensagemUsuario(' J� existe ciclo cadastrado dentro desse per�odo ('.$start_date.' a '.$end_date.')!','danger');
        $bool = false;
    }


    if($bool)
    {
        gravar_ciclo($_POST);
        registraLog("Cadastro de ciclos");
        setMensagemUsuario('Ciclo cadastrado com sucesso para o per�odo de '.$start_date.' a '.$end_date.'!','success');
        replaceLink( $link_retorno );
    }

}


$sLotacao        = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];


$oForm = new formPadrao();
$oForm->setJSDatePicker();
$oForm->setSubTitulo("Cadastro de Ciclos");


// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// instancia o banco de dados
$oDBase = new DataBase('PDO');

?>
    <script>
        $(document).ready(function () {

            $('#date1 .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                language: "pt-BR",
                //daysOfWeekDisabled: "0,6",
                orientation: "bottom auto",
                startDate: "01/01/<?= date("Y"); ?>",
                endDate: "31/12/<?= date("Y"); ?>",
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
                startDate: "01/01/<?= date("Y"); ?>",
                endDate: "31/12/<?= date("Y"); ?>",
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

            $('#btn-salvar-ciclo').on('click', function () {
                validateForm();
            });
        });

         /**
         *  In�cio Vari�veis Globais
         */
            var parsed;
            var validaDates;

         /**
         * Fim Vari�veis Globais
         */


        /**
         * Inicia uma serie de valida��es
         */
        function validateForm() {

            showProcessandoAguarde();

            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();
            var data_current = new Date().toLocaleDateString();

            // Verifica se a data inicial foi informada
            if (datestart === "") {
                mostraMensagem("Data inicial � obrigat�rio!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se a data final foi informada
            if (dateend === "") {
                mostraMensagem("Data final � obrigat�rio!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se as datas n�o s�o de anos diferentes
            if (validateYears(datestart, dateend)) {
                mostraMensagem("O Ciclo precisa estar dentro de um mesmo ano!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            if(ConverteParaData(datestart) < ConverteParaData(data_current)){
                mostraMensagem("Data inicial n�o pode ser menor que a data atual!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            if(ConverteParaData(dateend) < ConverteParaData(data_current)){
                mostraMensagem("Data final n�o pode ser menor que a data atual!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se a data inicial � maior que a data final-
            if (ConverteParaData(datestart) > ConverteParaData(dateend)) {
                mostraMensagem("Data inicial n�o pode ser maior que a final!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Vefifica se o range de datas selecionado � v�lido.
            validateRangeDates();
            setTimeout(function () {
                if (validaDates) {
                    mostraMensagem("J� existe ciclo cadastrado dentro desse per�odo!", 'warning');
                    hideProcessandoAguarde();
                    return false;
                }else{
                    var oForm = $('#form1');

                    oForm.attr( 'onsubmit', 'javascript: return true;' );
                    oForm.attr( 'action', 'ciclos_cadastrar.php' );
                    oForm.submit();
                }
            }, 1000);
        }

        /**
         * Valida o range das datas para n�o ser criada um novo ciclo em um periodo onde ja existe outro para o  mesmo �rg�o
         */

        var alo;

        function validateRangeDates() {
            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();

            $.get(
                "ciclos_cadastrar.php",
                "datastart=" + datestart +
                "&dataend=" + dateend +
                "&validarDatas=true",
                function (data) {
                    parsed = JSON.parse(data);

                    if (parsed.success) {
                        return validaDates = parsed.bloqueia_cadastro;
                    }
              });
        }

        /**
         * Formata a data para futuras valida��es
         */
        function ConverteParaData(data) {
            var dataArray = data.split('/');
            var novaData = new Date(dataArray[2] + "-" +dataArray[1] + "-"+dataArray[1]);

            return novaData;
        }

        /**
         * Valida se os anos das duas datas s�o iguais
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

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <form id="form1" name="form1" method="POST" action="#" onSubmit="javascript:return false;">
            <input type="hidden" id="lota" name="lota" value="<?= tratarHTML(getOrgaoByUorg($sLotacao)); ?>">

            <div class="row">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 col-md-offset-4 margin-20 ">
                    <label>�rg�o:</label>
                    &nbsp;<input type="text"
                                 id="lotacao"
                                 name="lotacao"
                                 class="form-control"
                                 size="19"
                                 maxlength="19"
                                 value="<?= tratarHTML(getOrgaoMaisSigla( $sLotacao )); ?>"
                                 size="5"
                                 readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 col-md-offset-4 margin-10" id='date1'>
                    <label class='control-label'>Data Inicial:</label>
                    <div class="input-group date">
                        <input type="text" id="data_inicio" name="data_inicio" placeholder="dd/mm/aaaa" OnKeyPress="formatar(this, '##/##/####')" class='form-control' autocomplete="off"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 col-md-offset-4 margin-10" id='date2'>
                    <label>Data Final:</label>
                    <div class='input-group date'>
                        <input type='text' name="data_final" id="data_final" placeholder="dd/mm/aaaa" OnKeyPress="formatar(this, '##/##/####')" class="form-control" autocomplete="off"/><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="row margin-10">
                <br>
                <div class="form-group col-md-11 text-center">
                    <div class="col-md-2"></div>
                    <div class="col-md-2 col-xs-4 col-md-offset-2">
                        <a class="btn btn-success btn-block" id="btn-salvar-ciclo" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Salvar
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-4">
                        <a class="btn btn-danger btn-block" id="btn-voltar"
                           href="javascript:window.location.replace('<?= $link_retorno; ?>')" role="button">
                            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                        </a>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>
        </form>
    </div>
<?php

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
