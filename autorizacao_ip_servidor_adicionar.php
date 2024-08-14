<?php
include_once("config.php");

verifica_permissao("sRH ou chefia");

if(!empty($_POST)){

    $start_date = $_POST['data_inicio']; // Data Inicial sem formatações
    $end_date = $_POST['data_final']; // Data Final sem formatações

    // Formatação das datas
    $dateiniformated = databarra($start_date);
    $datefimformated = databarra($end_date);

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
    if ((strtotime(conv_data($dateiniformated)) > strtotime(conv_data($datefimformated))) AND $bool) {
        setMensagemUsuario('Data inicial não pode ser maior que a final!','danger');
        $bool = false;
    }


    if($bool){
        create_autorizacao_servidor_ip($_POST);
        registraLog("Cadastro de autorização de faixa de ip por servidor realizado.");
        replaceLink("autorizacao_ip_servidor.php");
    }

}

$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];

// dados voltar
$_SESSION['voltar_nivel_2'] = "autorizacoes_usufruto.php";
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

$oServidores = new DadosServidoresController();

// FORMATAÇÕES DAS DATAS PARA DENTRO DA TABLE
$dateiniformated = databarra($ciclo['data_inicio']);
$datefimformated = databarra($ciclo['data_fim']);

$_SESSION['tipo_solicitacao'] = $_GET['tiposolicitacao'];


$oForm = new formPadrao();
$oForm->setJSDatePicker();
$oForm->setJSSelect2();
$oForm->setJS('js/jquery.mask.min.js');

$oForm->setSubTitulo("Cadastrar autorização de faixa de ip por servidor");

// Topo do formulário

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>

    <script>
        $(document).ready(function () {
            // Set the "bootstrap" theme as the default theme for all Select2
            // widgets.
            //
            // @see https://github.com/select2/select2/issues/2927
            $.fn.select2.defaults.set("theme", "bootstrap");

            var placeholder = "Selecione uma Ocorrência";

            $(".select2-single").select2({
                placeholder: placeholder,
                width: null,
                containerCssClass: ':all:'
            });
        
            $('#date .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                language: "pt-BR",
                orientation: "bottom",
                autoclose: true,
                toggleActive: true
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
                orientation: "bottom",
                autoclose: true,
                toggleActive: true
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
            
            $(document).on('click', '.save', function () {
                submitForm();
            });
        });

        /**
         *  Início Variáveis Globais
         */
        var parsed;
        var validaDates;
        var validaDatesConflite;
        var validaSaldo;
        var validaIfServidorHasBalance;

        /**
         * Fim Variáveis Globais
         */

        /**
         * Inicia uma serie de validações
         */
        function validateForm() {

            var datestart     = $("[name='data_inicio']").val();
            var dateend       = $("[name='data_final']").val();
            var servidor      = $("[name='siape']").val();
            var justificativa = $("[name='justificativa']").val();
            var options       = $('#subIp option');
            var values        = $.map(options ,function(option) {
                return option.value;
            });


            // Verifica se o servidor foi informado
            if (servidor === "") {
                alert("Servidor não informado!");
                return false;
            }

            // Verifica se a data inicial foi informada
            if (datestart === "") {
                alert("Data inicial é obrigatório!");
                return false;
            }

            // Verifica se a data final foi informada
            if (dateend === "") {
                alert("Data final é obrigatório!");
                return false;
            }

            // Verifica se as datas não são de anos diferentes
            if (validateYears(datestart, dateend)) {
                alert("O Ciclo precisa estar dentro de um mesmo ano!");
                return false;
            }

            // Verifica se a data inicial é maior que a data final-
            if (ConverteParaData(datestart) > ConverteParaData(dateend)) {
                alert("Data inicial não pode ser maior que a final!");
                return false;
            }

            // Verifica se justificativa foi informada
            if (justificativa === "") {
                alert("Justificativa é obrigatória!");
                return false;
            }

            // Verifica se foi informado no minimo um ip
            if(values.length === 0){
                alert("É preciso informar no mínimo um IP.");
                return false;
            }else{
                $("[name='hdnips']").val(values);
            }
            submitForm();
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

            if (first[2] === last[2])
                return false;

            return true;
        }

        /**
         * Salvamento do form
         */

        function submitForm() {
            $('#form1').submit();
        }

        function addOption(selectbox,text,value )
        {
            var optn = document.createElement("OPTION");
            optn.text = text;
            optn.value = value;
            selectbox.options.add(optn);
        }

        function removeOptions(selectbox)
        {
            var i;
            for(i=selectbox.options.length-1;i>=0;i--)
            {
                if(selectbox.options[i].selected)
                    selectbox.remove(i);
            }
        }

        function addIP() {
            var ip  = $("[name='ip']").val();
            $("[name='ip']").val("");

            if(validateIP(ip) || ip === "*"){
                addOption(document.form1.subIp, ip,ip);
            }else{
                alert('Endereço IP inválido!');
            }
        }

        /**
         *
         * @param ip
         * @returns {boolean}
         */
        function validateIP(ip) {
            //Check Format
            var ip = ip.split(".");

            if (ip.length != 4) {
                return false;
            }

            //Check Numbers
            for (var c = 0; c < 4; c++) {
                //Perform Test
                if ( ip[c] <= -1 || ip[c] > 255 ||
                    isNaN(parseFloat(ip[c])) ||
                    !isFinite(ip[c])  ||
                    ip[c].indexOf(" ") !== -1 ) {

                    return false;
                }
            }
            return true;
        }

    </script>


    <div class="portlet-body form">

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <form id="form1" name="form1" method="POST" action="autorizacao_ip_servidor_adicionar.php">
            <input type="hidden" name="hdnips">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Servidor:</font>
                    
                    <?php $oServidores->montaSelectDeServidoresIPS($valor=null, $_SESSION['sLotacao']);?>
                    
                    <input type="hidden" name="tipo_solicitacao" value="<?= tratarHTML($_SESSION['tipo_solicitacao']); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6" id="date">
                    <font class="ft_13_003">Data Inicial:</font>
                    <div class='input-group date'>
                        <input type='text' name="data_inicio" placeholder="dd/mm/aaaa" class="form-control"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6" id="date2">
                    <font class="ft_13_003">Data Final:</font>
                    <div class='input-group date'>
                        <input type='text' name="data_final" placeholder="dd/mm/aaaa" class="form-control"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <font class="ft_13_003">Justificativa:</font>
                    <div>
                        <textarea class="form-control" rows="5" cols="80" name="justificativa"></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <br>
                <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6">
                    <div class="form-inline">
                        <input type="text" name="ip" class="form-control" placeholder="Informe o IP..." style="width: 50%;">
                        <input type="button" class="btn adicionar-ip" onClick="addIP()" value="Adicionar">
                    </div>
                    <br>
                    <select class="form-control" id="subIp" name="subIp[]" multiple size=6></select>
                    <br>
                    <input type="button" onClick="removeOptions(subIp)" class="btn excluir-ip" value="Excluir IP">
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
                           href="javascript:window.location.replace('/sisref/autorizacao_ip_servidor.php')" role="button">
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
