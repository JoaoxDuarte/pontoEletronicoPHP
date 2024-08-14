<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setDataTables();
$oForm->setCSS( 'css/select2.min.css' );
$oForm->setCSS( 'css/select2-bootstrap.css' );
$oForm->setJS( 'js/select2.full.js' );
$oForm->setJS( 'js/jquery.mask.min.js');

$oForm->setSubTitulo( "Perfil de Usuários RH - Administração SISREF" );

$oForm->exibeTopoHTML();
$oForm->printTituloTopoJanela( "Órgão Central" );
$oForm->exibeCorpoTopoHTML($width='1000px');

?>
<table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:800px;">
    <thead>
        <tr role="row">
            <th  class="sorting">SIAPE</th>
            <th  class="sorting">NOME</th>
            <th  class="sorting">CPF</th>
            <th  class="sorting">DT. INICIO</th>
            <th  class="sorting">DT. FIM</th>,
            <th  class="sorting">UPAG</th>
            <th  class="sorting">PRAZO</th>
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "relatorio_perfil_rh_usuarios_lista.php",
                "type": "POST"
            },
            "dom": '<"top"fl>rt<"bottom"ip><"clear">',
            "scrollX": true,
            "language": {
                "url": "js/DataTables/i18n/pt_BR.json"
            },
            "stateSave": true,
            "pagingType": "full_numbers",
            "lengthMenu": [ [10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000] ]
            /*"lengthMenu": [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "Todos"] ]*/
        });
    });
</script>
