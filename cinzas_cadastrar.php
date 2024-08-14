<?php
include_once("config.php");

verifica_permissao("sRH");

if($_GET['validarDatas']) {
    echo validaRangeDatesCinzas($_GET['datastart'], $_GET['dataend']);
    die;
}

if(!empty($_POST)){

    $start_date = $_POST['data_inicio']; // Data Inicial sem formatações
    $end_date = $_POST['data_final']; // Data Final sem formatações

    // Formatação das datas
    $dateiniformated = conv_data($start_date);
    $datefimformated = conv_data($end_date);

    $bool = true; // Variável de controle

    // Verifica se a data inicial foi informada
    if(empty($start_date) AND $bool){
        setMensagemUsuario('Data Inicial não informada!','danger');
        $bool = false;
    }

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


    if($bool) {
        gravar_quarta_feira_de_cinzas($_POST);
        registraLog("Cadastro de quarta-feira de cinzas");
        replaceLink("tabvalida.php");
    }

}


$mensagemUsuario = $_SESSION["mensagem-usuario"];


$oForm = new formPadrao();
$oForm->setJSDatePicker();
$oForm->setSubTitulo("Cadastro de Quarta-Feira de Cinzas");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// instancia o banco de dados
$oDBase = new DataBase('PDO');

?>
    <script>
        $(document).ready(function () {

            $('#date .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                startDate: "10/01/2009",
                //endDate: "31/12/2019",
                maxViewMode: 0,
                language: "pt-BR",
                autoclose: true,
                todayHighlight: true,
                toggleActive: true,
                datesDisabled: ['10/06/2018', '10/21/2018']
            });

            $(document).on('click', '#btn-salvar', function () {
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

            var datestart = $("[name='data_inicio']").val();
            var data_current = new Date().toLocaleDateString();

            // Verifica se a data inicial foi informada
            if (datestart === "") {
                alert("Data é obrigatória!");
                return false;
            }

            if(ConverteParaData(datestart) < ConverteParaData(data_current)){
                alert("Data inicial não pode ser menor que a data atual!");
                return false;
            }

            // Vefifica se o range de datas selecionado é válido.
            validateRangeDates();
            setTimeout(function () {
                if (validaDates) {
                    alert('Já existe quarta-feira de cinzas registrada para este ano!');
                    return false;
                }else{
                    var oForm = $('#form1');

                    oForm.attr( 'onsubmit', 'javascript: return true;' );
                    oForm.attr( 'action', 'cinzas_cadastrar.php' );
                    oForm.submit();
                }
            }, 1000);
        }

        /**
         * Valida o range das datas para não ser criada um novo ciclo em um periodo onde ja existe outro para o  mesmo órgão
         */

        var alo;

        function validateRangeDates() {
            var datestart = $("[name='data_inicio']").val();

            $.get(
                "cinzas_cadastrar.php",
                "datastart=" + datestart +
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
            var novaData = new Date(dataArray[2] + "-" +dataArray[1] + "-"+dataArray[1]);

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

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <form id="form1" name="form1" method="POST" action="#" onSubmit="javascript:return false;">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 col-md-offset-4 margin-10">
                    <label>Data Inicial:</label>
                    <div class='input-group date'>
                        <input type='text' name="data_inicio" placeholder="dd/mm/aaaa" id="date" class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
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
                           href="javascript:window.location.replace('/sisref/tabvalida.php')" role="button">
                            <span class="glyphicon glyphicon-arrow-left"></span> Cancelar
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
