<?php

include_once( "config.php" );

verifica_permissao('administrador');

$messagedelete = false;
if(!empty($_GET['deletecargo'])) {
    $oDBase= new DataBase('PDO');

    $oDBase->query("SELECT count(*) AS contador FROM servativ WHERE cod_cargo = :cargo", array(
      array(":cargo", $_GET['deletecargo'], PDO::PARAM_STR))
    );

    if($oDBase->fetch_object()->contador > 0){
        echo json_encode(['success' => false , 'message' => utf8_encode('Esse cargo possui vinculos com outros registros dentro do sistema, sendo assim não pode vir a ser excluído.')]);die;
    } else {
        $oDBase->query("DELETE FROM tabcargo WHERE tabcargo.COD_CARGO = :cargo", array(array(":cargo", $_GET['deletecargo'], PDO::PARAM_STR)));
        echo json_encode(['success' => true , 'message' => 'Registro apagado com sucesso!']);die;
    }
}


/** Validação se as datas informadas no cadastro estão dentro do ciclo */
if($_GET['carregar-tabela']) {


    function getTitle($titulo){
        if($titulo == "SIM")
            return "Sim";

        return "Não";
    }

    $limit  = $_GET['limite-dados'];

    $filters = $_REQUEST;

    $cargos = buscaCargos($limit , $filters);

    $table = '
    <fieldset width="100%">Total de <b>' . tratarHTML($cargos->affected_rows()) . '</b> registros.</fieldset>
    <table id="tableServidores" class="table table-striped table-bordered text-center table-condensed tablesorter">';

        $table .= '<thead>
                      <tr>
                        <th class="text-center" style="vertical-align:middle;">Código do Cargo</th>
                        <th class="text-center" style="vertical-align:middle;">Nome do Cargo</th>
                        <th class="text-center" style="vertical-align:middle;">Permite Banco de Horas</th>
                        <th class="text-center" style="vertical-align:middle;">Ações</th>
                      </tr>
                   </thead>';

        $table .= '<tbody>';

        while ($cargo = $cargos->fetch_object()) {
            $table .= '<tr>
                         <td align="center">' . tratarHTML($cargo->COD_CARGO). '</td>
                         <td align="center">' . tratarHTML($cargo->DESC_CARGO). '</td>
                         <td align="center">'. tratarHTML(getTitle($cargo->PERMITE_BANCO)).'</td>
                         <td align="center">
                            <a href="cargos_funcoes_alterar.php?codcargo='. tratarHTML($cargo->COD_CARGO) .'"><img border="0" src="imagem/edicao2.jpg" width="16" height="16" align="absmiddle" alt="Horário" title="Editar"></a>
                            <a class="delete-cargo" data-codigo-cargo="'. tratarHTML($cargo->COD_CARGO) .'"><img border="0" src="imagem/lixeira2.jpg" width="16" height="16" align="absmiddle" alt="Horário" title="Excluir"></a></td>
                       </tr>';
        }

        if($cargos->affected_rows() > 0) {
            $bool = true;
        } else {
            $table .= '<tr><td colspan="4">Nenhum registro encontrado</td></tr>';
        }

    $table .= '</tbody></table>';

    echo  json_encode(array("success" => true,"table" => utf8_encode($table),"showbuttons" => $bool));die;
}


$oForm = new formPadrao();
$oForm->setCaminho($sFormCaminho);
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setOnLoad("$('#chave').focus();");
$oForm->setSeparador(0);
$oForm->setSubTitulo('Pesquisa de cargos e funções');
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>

    <script language="javascript">
        $(document).ready(function () {
            $(document).on('click', '.delete-cargo', deleteCargo);
            $(document).on('click', '.search', searchCharges);
            $(document).on('click', 'input[name="filter-check"]', function () {
                    if(this.value == "permite"){
                        $('input[name="caixa"]').hide();
                        $('select[name="permite-banco"]').show();
                    }else{
                        $('input[name="caixa"]').show();
                        $('select[name="permite-banco"]').hide();
                    }
                });
         });

        // Pesquisa de cargos/funções
        function searchCharges() {
            var botao = $(this);
            var todosdados = $(this).attr('data-limite');
            var typesearch = $('input[name="filter-check"]:checked').val();
            var search = "";

            if(typesearch === "permite"){
                search = $('select[name="permite-banco"]').val();
            } else {
                search = $('input[name="caixa"]').val();

                if(search === ""){
                    alert("É necessário ao menos algum filtro selecionado!")
                    return false;
                }
            }

            buscarDados(search,typesearch,todosdados,botao);

            return false;
        }

        // Busca dos Cargos/Funções
        function buscarDados(search , typesearch , limite , botao = null) {

            botao.attr('disabled', 'disabled');
            botao.html('Carregando...');

            // Timeout para dar tempo de mostrar o botão carregando hahaha
            setTimeout(function() {
                $.get(
                    "cargos_funcoes.php",
                    "carregar-tabela=true&" +
                    "limite-dados=" + limite +
                    "&search=" + search +
                    "&typesearch=" + typesearch,
                    function (data) {
                        parsed = JSON.parse(data);
                        if (parsed.success) {

                            if(parsed.showbuttons) {
                                $('.carregar-todos').show();
                            } else {
                                $('.carregar-todos').hide();
                            }

                            $('#tableServidores').empty().append(parsed.table);

                            botao.html('<span class="glyphicon glyphicon-search"></span> Pesquisar');
                            botao.removeAttr('disabled');

                            if(limite == 'nao')
                                $('.carregar-todos').html('Carregar mais cargos...');
                        }
                    });
            }, 1500);
        }


        // Essa funcção exclui cargos
        function deleteCargo() {
            var codigo = $(this).attr('data-codigo-cargo');

            $.get(
                "cargos_funcoes.php",
                "deletecargo=" + codigo,
                function (data) {
                    parsed = JSON.parse(data);
                    if (parsed.success) {
                        alert(parsed.message);
                        window.location = "cargos_funcoes.php";
                    }
                    alert(parsed.message);
                }
            );
        }

    </script>

    <style>
        .fil {
            cursor: pointer;
        }
    </style>

    <form >
        <input type="hidden" name="modo" value="" >
        <input type="hidden" name="corp" value="">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="100%" colspan="3" style="word-spacing: 0; margin: 0">&nbsp;</td>
            </tr>
            <tr>
                <td class="corpo" width="100%" colspan="3">
                    <p id='so_na_tela' align="center" class='tahomaSize_1'>
                        <input type="radio" class="codigo-check fil" name="filter-check" value="codigo" checked>Por Código&nbsp;&nbsp;&nbsp;
                        <input type="radio" class="nome-check fil" name="filter-check" value="nome">Por Nome&nbsp;&nbsp;&nbsp;
                        <input type="radio" class="permite-check fil" name="filter-check" value="permite">Por Permissão de Banco de Horas&nbsp;&nbsp;&nbsp;
                    </p>
                </td>
            </tr>
            <tr>
                <td colspan='3' align="center" style="word-spacing: 0; margin: 0">&nbsp;</td>
            </tr>
            <tr>
                <td colspan='3' class="corpo" style="word-spacing: 0; margin: 0">
                    <div class="col-md-4 center">
                    </div>
                    <div class="col-md-4 center">
                        <p id='so_na_tela' align="center" class='tahomaSize_1'>
                            <input type="text" class="form-control caixa" id="caixa" name="caixa" size="28">
                            <select class="form-control" name="permite-banco" style="display: none;">
                                <option>SIM</option>
                                <option>NÃO</option>
                            </select>
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    
        <div class="col-md-12 margin-10 margin-bottom-10">
            <div class="text-center ">
                <button type="image" class="btn btn-sucess btn-success" id="btn-continuar">
                    <span class="glyphicon glyphicon-search"></span> Pesquisar
                </button>
            </div>
        </div>

    </form>

    <div class="col-md-12">
        <div class="row">

            <?php if($_SESSION['sRH'] == "S"): ?>

                <a href="cargos_funcoes_cadastrar.php">
                    <button type="button" data-limite="nao" class="btn btn-default adicionar">Novo Cargo e Função</button>
                </a>
                <br><br>

            <?php endif; ?>

            <table id="tableServidores" class="table table-striped table-bordered text-center table-condensed tablesorter"></table>
            <button type="button" style="display: none" data-limite="nao" class="btn btn-default carregar-todos search">Carregar mais cargos...</button>
        </div>

    </div>

<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
