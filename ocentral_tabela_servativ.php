<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");

// tabela
$tabela       = 'servativ';
$campos_extra = array();
$campos_extra[] = array( 'Index' => 10, 'Field' => 'tabsetor.descricao', 'Comment' =>'');

$campos = loadNameFieldsTables( $tabela, $campos_extra );

?>
<table id="tabela_dados" class="display nowrap table-hover table table-striped table-bordered" role="grid" style="width:800px;">
    <thead>
        <tr role="row">
            <?php foreach($campos as $field): ?>
                <th  class="sorting"><?= $field[0]; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
</table>

<script>
    var source;
    var tela      = null;
    var registros = 0;
    
    var arquivo_csv = "csv";

    $(document).ready(function() {
        var tabela = $('#tabela_dados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ocentral_tabela_servativ_lista.php",
                "type": "POST"
            },
            "dom": '<"top"flB>rt<"bottom"ip><"clear">',
            "scrollX": true,
            "language": {
                "url": "js/DataTables/i18n/pt_BR.json"
            },
            "stateSave": true,
            "pagingType": "full_numbers",
            "lengthMenu": [ [10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000] ],
            "select": true,
            "buttons": [
                {
                    title: 'CADASTRO',
                    text: '<span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Exportar para CSV',
                    extend: 'csv',
                    filename: 'sisref_cadastro.csv',
                    action: function (e, dt, node, config) {
                        startTask();
                    },
                    messageBottom: 'Fonte: SISREF Sistema de Registro Eletrônico de Frequência',
                    header: true,
                    footer: true
                }
            ]
        });
    });


    /**
     * @info Carga de dados 
     * 
     * @returns {undefined}
     */
    function startTask()
    {
        tela = new newTelaProgresso();
        tela.init();

        var filtrar = "search[value]=" + $('.form_control input[type=search], .input-sm').val();

        if (!!window.EventSource)
        {
            source = new EventSource("ocentral_tabela_servativ_csv.php?" + filtrar);

            source.addEventListener("message", function(e)
            {
                var result = JSON.parse( e.data );

                if(e.lastEventId == 'CLOSE')
                {
                    source.close();
                    tela.close();

                    if (result.download == 'novo_login')
                    {
                        window.location.replace( 'finaliza.php' );
                    }

                    if (registros !== 0)
                    {
                        // Create link and download
                        var tempLink = document.createElement('a');
                        
                        tempLink.href = result.download;
                        tempLink.setAttribute('download', "sisref_cadastro.csv");
                        tempLink.click();
                        
                        $.ajax({
                            "url": "ocentral_tabela_servativ_csv.php",
                            "method": "POST",
                            "data": "apagar=" + arquivo_csv
                        }).done(function(res, status, xhr) {
                            var dados = JSON.parse(res);
                            console.log(dados.download);
                        }).fail(function(jqXHR, textStatus ) {
                            console.log("Request failed: " + textStatus);
                        }).always(function() {
                            console.log("completou");
                        });
                    }

                    hideProcessandoAguarde();
                }
                else
                {
                    registros++;
                    tela.setProgress( result.progress );
                    tela.setRegistros( 'Registros: ' + result.seq + '/' + result.total );
                }
            }, false);

            source.addEventListener("open", function(e)
            {
                tela.open();
            }, false);

            source.addEventListener("error", function(e)
            {
                tela.close();
                source.close();
                hideProcessandoAguarde();
            }, false);
        }
        else
        {
            tela.close();
            hideProcessandoAguarde();
        }
    }
</script>
