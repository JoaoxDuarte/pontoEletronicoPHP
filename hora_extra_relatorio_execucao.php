<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

$ano = date('Y');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setDataTables();

$oForm->setSubTitulo( "Serviços Extraordinários - Execução" );

$oForm->exibeTopoHTML();
$oForm->printTituloTopoJanela( "Relatórios" );
$oForm->exibeCorpoTopoHTML($width='1250px');

?>
<script>
    $(document).ready(function() {
        var tabela = $('#hora_extra_executada').DataTable( {
            "processing": true,
            "ajax": {
                "url": "hora_extra_relatorio_execucao_lista.php",
                "dataType": "json",
                "type": "POST",
                "data": {
                    "ano": <?= $ano; ?>
                }
            },
            "dom": '<"top"flB>rt<"bottom"ip>',
            "language": {
                "url": "js/DataTables/i18n/pt_BR.json"
            },
            "order": [[ 1, 'asc' ]],
            "stateSave": true,
            "pagingType": "full_numbers",
            "displayLength": 25,
            "lengthMenu": [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "Todos"] ],
            "createdRow": function ( row, data, index ) {
                if ( data[5] != '00:00' ) {
                    $('td', row).eq(0).css({'color':'blue','padding-top':'8px','height':'23px'});
                    $('td', row).eq(1).css({'color':'blue','padding-top':'8px'});
                    $('td', row).eq(2).css({'color':'blue','padding-top':'8px'});
                    $('td', row).eq(3).css({'color':'blue','padding-top':'8px'});
                    $('td', row).eq(4).css({'color':'blue','padding-top':'8px'});
                    $('td', row).eq(5).css({'color':'blue','padding-top':'8px','font-weight':'bold'});
                    $('td', row).eq(6).css({'display':'none'});
                }
                else
                {
                    $('td', row).eq(6).css({'display':'none'});
                }
            },
            "footerCallback": function ( row, data, start, end, display ) {
                var api = this.api(), data;
 
                // Remove the formatting to get integer data for summation
                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '')*1 :
                        typeof i === 'number' ?
                            i : 0;
                };
 
                // Total over all pages
                total = api
                    .column( 6 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
 
                // Total over this page
                pageTotal = api
                    .column( 6, { page: 'current'} )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
 
                // Update footer
                $( api.column( 5 ).footer() ).html(
                    //'<div style="padding-right:95px;">'+sec_to_time(pageTotal) +'</div><div style="padding-right:95px;">('+ sec_to_time(total) +'&nbsp;total&nbsp;geral)</div>'
                    '<div style="padding-right:95px;">'+ sec_to_time(total) +'&nbsp;total&nbsp;geral</div>'
                );
            },
            "buttons": [
                //{
                //    "text": 'Reload',
                //    "action": function ( e, dt, node, config ) {
                //        dt.ajax.reload();
                //    }
                //},
                //{
                //    title: 'SERVIÇOS EXTRAORDINÁRIOS - EXECUÇÃO',
                //    text: '<img src="imagem/print.gif" height="25px">',
                //    extend: 'print',
                //    filename: 'sisref_serviços_extraordinarios_execucao',
                //    exportOptions: {
                //        columns: ':visible',
                //    },
                //    header: true,
                //    footer: true
                //},
                //{
                //    title: 'SERVIÇOS EXTRAORDINÁRIOS - EXECUÇÃO',
                //    text: '<img src="imagem/icone-pdf.png" height="25px" alt="Exportar para PDF" title="Exportar para PDF">',
                //    extend: 'pdf',
                //    filename: 'sisref_serviços_extraordinarios_execucao',
                //    exportOptions: {
                //        columns: ':visible',
                //    },
                //    customize: function ( doc ) {
                //        doc.content.splice( 1, 0, [
                //            {
                //                text: 'Serviços Extraordinários - Execução', 
                //                pageOrientation: 'landscape',
                //                style: 'header',
                //                bold: true,
                //                fontSize: 15,
                //                alignment: 'center',
                //                //italics: true,
                //                //background: 'red',
                //            },
                //            {
                //                text: ' '
                //            }
                //        ]);
                //    },
//
                //    //messageTop: 'Relatório de Serviços Extraordinários - Execução',
                //    messageBottom: 'Fonte: SISREF Sistema de Registro Eletrônico de Frequência',
                //    header: true,
                //    footer: true
                //},
                //{
                //    title: 'SERVIÇOS EXTRAORDINÁRIOS - EXECUÇÃO',
                //    text: '<img src="imagem/icone-csv.png" height="25px" alt="Exportar para CSV" title="Exportar para CSV">',
                //    extend: 'csv',
                //    filename: 'sisref_serviços_extraordinarios_execucao',
                //    exportOptions: {
                //        columns: ':visible',
                //    },
//
                //    //messageTop: 'Relatório de Serviços Extraordinários - Execução',
                //    messageBottom: 'Fonte: SISREF Sistema de Registro Eletrônico de Frequência',
                //    header: true,
                //    footer: true
                //},
                //{
                //    title: 'SERVIÇOS EXTRAORDINÁRIOS - EXECUÇÃO',
                //    text: '<img src="imagem/icone-xls.png" height="25px" alt="Exportar para EXCEL" title="Exportar para EXCEL">',
                //    extend: 'excel',
                //    filename: 'sisref_serviços_extraordinarios_execucao',
                //    exportOptions: {
                //        columns: ':visible',
                //    },
                //    header: true,
                //    footer: true
                //}
            ]
        });
    });
</script>


<table id="hora_extra_executada" class="display table table-striped table-hover table-condensed tablesorter" style="width: 100%;" role="grid">
    <thead>
        <tr role="row">
            <th class="sorting_asc" tabindex="0" aria-controls="example" rowspan="1" colspan="1" aria-sort="ascending" style="width:100px;">Matrícula<br</th>
            <th class="sorting text-center" tabindex="0" aria-controls="example" rowspan="1" colspan="1" style="width:74%;" nowrap>Nome do Servidor</th>
            <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" style="width:110px;" nowrap>Data Início</th>
            <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" style="width:110px;" nowrap>Data Fim</th>
            <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" style="width:110px;">Horas Programadas</th>
            <th class="sorting" tabindex="0" aria-controls="example" rowspan="1" colspan="1" style="width:10px;">Horas Realizadas</th>
            <th class="hidden" tabindex="0" aria-controls="example" rowspan="1" colspan="1" style="width:100px;">Horas Total</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th style="text-align:right">Total:</th>
            <th></th>
            <th></th>
        </tr>
    </tfoot>
</table>
<?php

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();

