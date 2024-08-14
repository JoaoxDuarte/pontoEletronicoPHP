function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var portaria = $('#portaria');
    var datapt   = $('#datapt');
    
    if (portaria.val().length == 0)
    {
        oTeste.setMsg('Por favor digite os dados da portaria de delegação!', portaria);
    }
    if (datapt.val().length < 10)
    {
        oTeste.setMsg('Por favor digite a data da portaria no formato dd/mm/aaaa!', datapt);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

// calendario
jQuery(document).ready(function ()
{
    $.datepicker.setDefaults({
        closeText: 'Fechar',
        prevText: '&#x3c;Anterior',
        nextText: 'Pr&oacute;ximo&#x3e;',
        currentText: 'Hoje',
        dateFormat: 'dd/mm/yy',
        showOn: 'both',
        buttonImageOnly: true,
        buttonImage: './imagem/calendar.gif',
        buttonText: 'Calendário',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
        dayNamesMin: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
    });
    $('#datapt').datepicker();
});
