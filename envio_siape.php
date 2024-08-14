<?php
include_once("config.php");
include_once("WsSiape.php");

verifica_permissao("sRH");

$oDBase = new DataBase('PDO');
$qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);


if($_GET['carregar-tabela']) {

    //MONTA O NOME DA TABELA
    $tabela = "ponto".$_GET['competencia'];
    $limit  = $_GET['limite-dados'];
    $bool = false;

    $servidores = buscaServidoresParaEnvioSiape($tabela, $limit);

    $table = '
    <fieldset width="100%">Total de <b>' . $servidores->affected_rows() . '</b> registros.</fieldset>
    <table id="tableServidores" class="table table-striped table-bordered text-center table-condensed tablesorter">';

    while ($matricula = $servidores->fetch_assoc()) {

        $table .= '<thead>
                        <tr><th class="text-center text-nowrap" style="vertical-align:middle;" colspan="11">'. tratarHTML($matricula['nomeservidor']).'</th></tr>
                        <tr>
                            <th class="text-center" style="vertical-align:middle;">Dia</th>
                            <th class="text-center" style="vertical-align:middle;">Entrada</th>
                            <th class="text-center" style="vertical-align:middle;">Ida Intervalo</th>
                            <th class="text-center" style="vertical-align:middle;">Volta Intervalo</th>
                            <th class="text-center" style="vertical-align:middle;">Saída</th>
                            <th class="text-center" style="vertical-align:middle;">Resultado</th>
                            <th class="text-center" style="vertical-align:middle;">Jornada Prevista</th>
                            <th class="text-center" style="vertical-align:middle;">Saldo Dia</th>
                            <th class="text-center" style="vertical-align:middle;">Ocorrencia</th>
                            <th class="text-center" style="vertical-align:middle;">Homologado</th>
                            <th class="text-center" style="vertical-align:middle;">Enviado ao SIAPE</th>
                        </tr>
                    </thead>';

        $table .='<tbody>';


        $infoservidores = getInfoServidor($tabela, $matricula['siape']);

        while ($infoservidor = $infoservidores->fetch_object()) {

            $table .=
                '<tr>
                 <td align="center">'. tratarHTML($infoservidor->dia). '</td>
                 <td align="center">'. tratarHTML($infoservidor->entra). '</td>
                 <td align="center">'. tratarHTML($infoservidor->intini). '</td>
                 <td align="center">'. tratarHTML($infoservidor->intsai). '</td>
                 <td align="center">'. tratarHTML($infoservidor->sai). '</td>
                 <td align="center">'. tratarHTML($infoservidor->jornd). '</td>
                 <td align="center">'. tratarHTML($infoservidor->jornp). '</td>
                 <td align="center">'. tratarHTML($infoservidor->jorndif). '</td>
                 <td align="center">'. tratarHTML($infoservidor->oco). '</td>
                 <td align="center">'. verifyHomologado($matricula['siape'], $_GET['competencia']). '</td>
                 <td align="center">'. verifyIfSendSiape($matricula['siape'], $_GET['competencia']).'</td>
            </tr>';
        }

         $table .= '</tbody>';
    }

    $table .= '</table>';

    if($servidores->affected_rows() > 0)
        $bool = true;

    echo  json_encode(array("success" => true,
                            "table" => utf8_encode($table),
                            "showbuttons" => $bool));die;
}

/** @info Envio para o SIAPE $oForm */
if($_GET['enviar-siape']){

    //FORMATA EM ARRAY AS MATRICULAS
    $matriculas = explode(',',$_GET['matriculas']);

    foreach ($matriculas as $index => $matricula) {

        $servidor = recuperarServidorParaEnviarSiape($matricula);

        criarXmlConsultaAfastamentosSiape($servidor, 2,3);

        $xmlAssinado = assinarXml();

        $arquivoConvertido = base64_encode($xmlAssinado);

        $obj = new WsSiape();
        $obj->consultarAfastamento($arquivoConvertido);
    }
}


$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setSeparador(0);

$css = array();
$javascript = array();

$css[] = 'css/new/sorter/css/theme.bootstrap_3.min.css';
$javascript[] = 'css/new/sorter/js/jquery.tablesorter.min.js';
$oForm->exibeTopoHTML();
?>
<style>
    .inibir_opcao {
        opacity: 0.15;
        -moz-opacity: 0.15;
        filter: alpha(opacity=15);
    }
</style>
<script>
    var competencia;

    $(document).ready(function () {
        $(document).on('click', '.buscar', function () {

            var botao = $(this);
            var todosdados = $(this).attr('data-limite');

            if(validacoes()){
                buscarDados(botao, todosdados);
            }
        });
        $(document).on('click', '.enviar-siape', function () {
            $("#confirmacao").modal();
        });
        $(document).on('click', '.save', function () {

            var matriculas = ["201130102249", "201130103276", "201130104957"];

            $.get(
                "envio_siape.php",
                "enviar-siape=true&" +
                "matriculas=" + matriculas,
                function (data) {
                    parsed = JSON.parse(data);
                    if (parsed.success) {

                        console.log(parsed);
                        return false;

                    }
                });

        });

        // impede a entrada de letras nos campos de mês e ano
        $(".ano , .mes").keypress(function(event) {
            return /\d/.test(String.fromCharCode(event.keyCode));
        });
    });


    function validacoes() {

        oTeste = new alertaErro();
        oTeste.init();

        var mes     = $("[name='mes']");
        var ano     = $("[name='ano']");
        var nMes = $("[name='mes']").val();
        var nAno = $("[name='ano']").val();
        var competencia_atual = (new Date()).getFullYear() + ""+ ((new Date()).getMonth() + 1);
        var competencia_inserida = nAno+nMes;


        if (nMes == '  ' || nMes == '00' || nMes.length < 2 || nMes > 12)
          oTeste.setMsg('- MES incorreto. Informe com 2 caracteres!', mes);

        if (nAno == '    ' || nAno == '0000' || nAno.length < 4 || nAno < 2009)
           oTeste.setMsg('- ANO inválido. Informe com 4 caracteres!', ano);

        if (competencia_inserida < "200910" || competencia_inserida > competencia_atual)
            oTeste.setMsg('Não é possível carregar dados para competência anterior a 10/2009 ou posterior ao mês atual!', ano);

        var bResultado = oTeste.show();

        if (bResultado == false)
          return bResultado;

        competencia = nMes+nAno;

        return true;
    }

    /**
     * @info recupera os servidores com ocorrência 00172
     */
    function buscarDados(botao, limite) {

        botao.attr('disabled', 'disabled');
        botao.html('Carregando...');

        // Timeout para dar tempo de mostrar o botão carregando hahaha
        setTimeout(function() {
            $.get(
                "envio_siape.php",
                "carregar-tabela=true&" +
                "limite-dados=" + limite +
                "&competencia=" + competencia,
                function (data) {
                    parsed = JSON.parse(data);
                    if (parsed.success) {

                        if(parsed.showbuttons) {
                            $('.enviar-siape').show();
                            $('.carregar-todos').show();
                        } else {
                            $('.carregar-todos , .enviar-siape').hide();
                        }

                        $('#tableServidores').empty().append(parsed.table);

                        botao.html('Buscar');
                        botao.removeAttr('disabled');

                        if(limite == 'nao')
                            $('.carregar-todos').html('Carregar todos os Servidores');
                    }
                });
        }, 1500);

    }

</script>

<div class="container" style='padding-left:0px;'>

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">

        <div class="modal fade" id="confirmacao" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Confirmação</h4>
                    </div>
                    <div class="modal-body">
                        <p>Deseja mesmo realizar o envio das ocorrências homologadas para o SIAPE?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default save" data-dismiss="modal">Ok</button>
                        <button type="button" class="btn btn-default cancel" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>

            </div>
        </div>

        <div class="row margin-10">
            <div class="col-md-2">
                <p><b>ÓRGÃO: </b><?= tratarHTML(getOrgaoByUorg( $qlotacao )); ?></p>
            </div>
            <div class="col-md-3">
                <p><b>UPAG: </b><?= tratarHTML($_SESSION['upag']); ?></p>
            </div>
        </div>

        <form class="form-inline" style="background: #d6d6df;">
            <div class="form-group">
                <label for="email">Mês:</label>
                <input type="text" name="mes" class="form-control mes" size="2" maxlength="2" placeholder="mm">
            </div>
            <div class="form-group">
                <label for="pwd">Ano:</label>
                <input type="text" name="ano" class="form-control ano" size="4" maxlength="4" placeholder="aaaa">
            </div>
            <button type="button" data-limite="sim" class="btn btn-default buscar">Buscar</button>
        </form>

        <div class="col-md-12 grid-servidores">
            <div class="row">
                <br>
                <table id="tableServidores"></table>
                <button type="button" style="display: none" class="btn btn-default enviar-siape">Enviar para o SIAPE</button>
                <button type="button" style="display: none" data-limite="nao" class="btn btn-default carregar-todos buscar">Carregar todos os Servidores</button>
            </div>
        </div>
    </div>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

