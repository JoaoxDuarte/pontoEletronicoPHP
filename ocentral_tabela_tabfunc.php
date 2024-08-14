<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");

?>
<table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:800px;">
    <thead>
        <tr role="row">
            <th  class="sorting">NUM_FUNCAO</th>
            <th  class="sorting">COD_FUNCAO</th>
            <th  class="sorting">DESC_FUNC</th>
            <th  class="sorting">COD_LOT</th>
            <th  class="sorting">tabfunc.cod_uorg</th>
            <th  class="sorting">UPAG</th>
            <th  class="sorting">tabsetor.cod_uorg</th>
            <th  class="sorting">tabsetor.UPAG</th>
            <th  class="sorting">SIT_PAG</th>
            <th  class="sorting">INDSUBS</th>
            <th  class="sorting">RESP_LOT</th>
            <th  class="sorting">ATIVO</th>
            <th  class="sorting">TIPO</th>
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ocentral_tabela_tabfunc_lista.php",
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
