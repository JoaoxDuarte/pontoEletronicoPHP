<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");


?>
<table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:800px;">
    <thead>
        <tr role="row">
            <th  class="sorting">id</th>
            <th  class="sorting">ocupantes.MAT_SIAPE</th>
            <th  class="sorting">servativ.NOME_SERV</th>
            <th  class="sorting">SIT_OCUP</th>
            <th  class="sorting">NUM_FUNCAO</th>
            <th  class="sorting">RESP_LOT</th>
            <th  class="sorting">COD_DOC1</th>
            <th  class="sorting">NUM_DOC1</th>
            <th  class="sorting">DT_DOC1</th>
            <th  class="sorting">COD_DOC2</th>
            <th  class="sorting">NUM_DOC2</th>
            <th  class="sorting">DT_DOC2</th>
            <th  class="sorting">COD_DOC3</th>
            <th  class="sorting">NUM_DOC3</th>
            <th  class="sorting">DT_DOC3</th>
            <th  class="sorting">COD_DOC4</th>
            <th  class="sorting">NUM_DOC4</th>
            <th  class="sorting">DT_DOC4</th>
            <th  class="sorting">DT_ALTERA</th>
            <th  class="sorting">DT_INICIO</th>
            <th  class="sorting">DT_FIM</th>
            <th  class="sorting">servativ.COD_SERV</th>
            <th  class="sorting">DT_ATUAL</th>
            <th  class="sorting">DECIR</th>
            <th  class="sorting">DTDECIR</th>
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ocentral_tabela_ocupantes_lista.php",
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
