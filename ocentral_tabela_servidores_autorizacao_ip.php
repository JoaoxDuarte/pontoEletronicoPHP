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
            <th  class="sorting">justificativa</th> 
            <th  class="sorting">data_inicio</th>
            <th  class="sorting">data_fim</th>
            <th  class="sorting">ips_autorizacao.endereco</th>
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ocentral_tabela_servidores_autorizacao_ip_lista.php",
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
