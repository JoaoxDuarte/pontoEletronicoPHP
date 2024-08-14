<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");


?>
<table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:800px;">
    <thead>
        <tr role="row">
            <th  class="sorting">id</th>
            <th  class="sorting">siape</th>
            <th  class="sorting">servativ.nome_serv</th>
            <th  class="sorting">numfunc</th>
            <th  class="sorting">tabfunc.desc_func</th>
            <th  class="sorting">upai</th>
            <th  class="sorting">sigla</th>
            <th  class="sorting">inicio</th>
            <th  class="sorting">fim</th>
            <th  class="sorting">motivo</th>
            <th  class="sorting">tabmotivo_substituicao.descricao</th>
            <th  class="sorting">situacao</th>
            <th  class="sorting">siape_registro</th>
            <th  class="sorting">data_registro</th>
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ocentral_tabela_substituicao_lista.php",
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
