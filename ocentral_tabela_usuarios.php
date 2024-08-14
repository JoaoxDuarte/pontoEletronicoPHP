<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");


?>
<table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:800px;">
    <thead>
        <tr role="row">
            <th  class="sorting">siape</th>
            <th  class="sorting">nome</th>
            <th  class="sorting">acesso</th>
            <th  class="sorting">setor</th>
            <th  class="sorting">privilegio</th>
            <th  class="sorting">senha</th>
            <th  class="sorting">prazo</th>
            <th  class="sorting">magico</th>
            <th  class="sorting">upag</th>
            <th  class="sorting">defvis</th>
            <th  class="sorting">portaria</th>
            <th  class="sorting">datapt</th>
            <th  class="sorting">ptfim</th>
            <th  class="sorting">dtfim</th>
            <th  class="sorting">recalculo</th>
            <th  class="sorting">refaz_frqano</th>
            <th  class="sorting">nome_soundex</th>
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ocentral_tabela_usuarios_lista.php",
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
