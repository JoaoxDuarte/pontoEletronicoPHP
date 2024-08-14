<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");

?>
<table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:800px;">
    <thead>
        <tr role="row">
            <th class="sorting">codigo</th>
            <th class="sorting">descricao</th>
            <th class="sorting">tb0700</th>
            <th class="sorting">uorg_anterior</th>
            <th class="sorting">cod_uorg</th>
            <th class="sorting">area</th>
            <th class="sorting">cod_uorg_pai</th>
            <th class="sorting">uorg_pai</th>
            <th class="sorting">upag</th>
            <th class="sorting">ug</th>
            <th class="sorting">inicio_atend</th>
            <th class="sorting">fim_atend</th>
            <th class="sorting">sigla</th>
            <th class="sorting">tfreq</th>
            <th class="sorting">dfreq</th>
            <th class="sorting">ativo</th>
            <th class="sorting">end_lota</th>
            <th class="sorting">num_lota</th>
            <th class="sorting">bairro_lota</th>
            <th class="sorting">cidade_lota</th>
            <th class="sorting">cep_lota</th>
            <th class="sorting">tel_lota</th>
            <th class="sorting">uf_lota</th>
            <th class="sorting">codmun</th>
            <th class="sorting">regiao</th>
            <th class="sorting">regional</th>
            <th class="sorting">fuso_horario</th>
            <th class="sorting">horario_verao</th>
            <th class="sorting">liberar_homologacao</th>
            <th class="sorting">periodo_excecao</th>
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ocentral_tabela_tabsetor_lista.php",
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
