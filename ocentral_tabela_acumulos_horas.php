<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");

?>
<table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:800px;">
    <thead>
        <tr role="row">
            <th  class="sorting">ID</th>
            <th  class="sorting">CICLO_ID</th>
            <th  class="sorting">ciclos.DATA_INICIO</th>
            <th  class="sorting">ciclos.DATA_FIM</th>
            <th  class="sorting">ORGAO</th>
            <th  class="sorting">taborgao.sigla</th>
            <th  class="sorting">SIAPE</th>
            <th  class="sorting">NOME</th>
            <th  class="sorting">HORAS</th>
            <th  class="sorting">USUFRUTO</th>
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ocentral_tabela_acumulos_horas_lista.php",
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
