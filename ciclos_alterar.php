<?php

include_once( "config.php" );
include_once( "src/controllers/TabBancoDeHorasCiclosController.php" );

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

if($_GET['validarDatas']) {
    echo $objTabBancoDeHorasCiclosController->validaRangeDates($_GET['datastart'], $_GET['dataend'] , $_GET['id']);
    die;
}

if(!empty($_POST)){

    $id         = $_POST['id']; // id do ciclo
    $start_date = $_POST['data_inicio']; // Data Inicial sem formatações
    $end_date   = $_POST['data_final']; // Data Final sem formatações
    $lotacao    = $_POST['lota']; // Lotação

    // Formatação das datas
    $dateiniformated = conv_data($start_date);
    $datefimformated = conv_data($end_date);

    $bool = true; // Variável de controle

    // Verifica se a data final foi informada
    if(empty($end_date) AND $bool)
    {
        setMensagemUsuario('Data Final não informada!','danger');
        $bool = false;
    }

    // Verifica se as datas não são de anos diferentes
    if (validateYears($start_date , $end_date) AND $bool) 
    {
        setMensagemUsuario('O Ciclo precisa estar dentro de um mesmo ano!','danger');
        $bool = false;
    }

    // Verifica se a data inicial é maior que a data final-
    if ((inverteData($dateiniformated) > inverteData($datefimformated)) AND $bool) 
    {
        setMensagemUsuario('Data inicial não pode ser maior que a final!','danger');
        $bool = false;
    }

    // Vefifica se o range de datas selecionado é válido.
    $result = json_decode($objTabBancoDeHorasCiclosController->validaRangeDates($start_date, $end_date , $id));
    if($result->bloqueia_cadastro AND $bool)
    {
        setMensagemUsuario(' Já existe ciclo cadastrado dentro desse período ('.$start_date.' a '.$end_date.')!','danger');
        $bool = false;
    }

    if($bool) 
    {
        update_ciclo($_POST);
        registraLog("Alteração de ciclo");
        setMensagemUsuario('Ciclo alterado com sucesso para o período de '.$start_date.' a '.$end_date.'!','success');
        replaceLink( $link_retorno );
    }

}

$sLotacao = $_SESSION["sLotacao"];
$mensagemUsuario = $_SESSION["mensagem-usuario"];


$oForm = new formPadrao();
$oForm->setJSDatePicker();
$oForm->setSubTitulo("Alteração de Ciclo");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->query("SELECT * FROM ciclos WHERE SUBSTR(ciclos.orgao,1,5) = :orgao AND ciclos.id = :ciclo_id ORDER BY ciclos.id DESC",
    array(
        array(":orgao", substr($sLotacao, 0, 5), PDO::PARAM_STR),
        array(":ciclo_id", $_GET['id'], PDO::PARAM_INT)
    )
);

$ciclo = $oDBase->fetch_assoc();

// FORMATAÇÕES DAS DATAS PARA DENTRO DA TABLE
$dateiniformated = databarra($ciclo['data_inicio']);
$datefimformated = databarra($ciclo['data_fim']);

?>
    <script>
        $(document).ready(function () {
            $('#date2 .input-group.date').datepicker({
                format: "dd/mm/yyyy",
                language: "pt-BR",
                //daysOfWeekDisabled: "0,6",
                orientation: "bottom auto",
                startDate: "<?= $dateiniformated; ?>",
                endDate: "31/12/<?= dataAno($ciclo['data_fim']); ?>",
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
            var dateend   = $("[name='data_final']").val();

            // Verifica se a data inicial foi informada
            if (datestart === "") {
                mostraMensagem("Data inicial é obrigatório!", 'warning');
                hideProcessandoAguarde();
                return false;
            }

            // Verifica se a data final foi informada
            if (dateend === "") {
                mostraMensagem("Data final é obrigatório!", 'warning');
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
            validateRangeDates();
            setTimeout(function () {
                if (validaDates) {
                    mostraMensagem("Já existe ciclo cadastrado dentro desse período!", 'warning');
                    hideProcessandoAguarde();
                    return false;
                }else{
                    var oForm = $('#form1');

                    oForm.attr('onsubmit', 'javascript: return true;');
                    oForm.attr('action', 'ciclos_alterar.php');
                    oForm.submit();
                }
            }, 1000);
        }

        /**
         * Valida o range das datas para não ser criada um novo ciclo em um periodo onde ja existe outro para o  mesmo órgão
         */

        function validateRangeDates() {
            var datestart = $("[name='data_inicio']").val();
            var dateend = $("[name='data_final']").val();
            var id = $("[name='id']").val();

            $.get(
                "ciclos_alterar.php",
                "datastart=" + datestart +
                "&dataend=" + dateend +
                "&id=" + id +
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
    <div class="portlet-body form">

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <form id="form1" name="form1" method="POST" action="#" onSubmit="javascript:return false;">
            <input type="hidden" value="<?= tratarHTML($_GET['id']); ?>" name="id">

            <div class="row">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 col-md-offset-4 margin-20 ">
                    <label>Órgão:</label>
                    &nbsp;<input type="text"
                                 id="lota"
                                 name="lota"
                                 class="form-control"
                                 size="19"
                                 maxlength="19"
                                 value="<?= tratarHTML(getOrgaoMaisSigla( $sLotacao )); ?>"
                                 size="5"
                                 readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 col-md-offset-4 margin-10">
                    <label class='control-label'>Data Inicial:</label>
                    <div class='input-group date'>
                        <input type='text' name="data_inicio" placeholder="dd/mm/aaaa" value="<?= tratarHTML($dateiniformated); ?>" class="form-control" readonly/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 col-md-offset-4 margin-10" id='date2'>
                    <label class='control-label'>Data Inicial:</label>
                    <div class="input-group date">
                        <input type='text' name="data_final" placeholder="dd/mm/aaaa" value="<?= tratarHTML($datefimformated); ?>" class="form-control" autocomplete="off"/>
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
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
