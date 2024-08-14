function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var entra    = $('#entra');
    var iniint   = $('#iniint');
    var fimint   = $('#fimint');
    var hsaida   = $('#hsaida');
    var jd2      = $('#jd2');
    var dia_util = $('#dutil');
    var ocor     = $('#ocor').val();

    if (entra.val() == "00:00:00")
    {
        oTeste.setMsg('Horário de entrada deve ser diferente de 00:00:00!', entra);
    }

    if (hsaida.val() == "00:00:00")
    {
        oTeste.setMsg('Horário de saida deve ser diferente de 00:00:00!', hsaida);
    }

    var horainicio        = parseFloat(entra.val().substring(0, 2));
    var minutoinicio      = parseFloat(entra.val().substring(3, 5));
    var horapausa         = parseFloat(iniint.val().substring(0, 2));
    var minutopausa       = parseFloat(iniint.val().substring(3, 5));
    var horacontinuacao   = parseFloat(fimint.val().substring(0, 2));
    var minutocontinuacao = parseFloat(fimint.val().substring(3, 5));
    var horafinal         = parseFloat(hsaida.val().substring(0, 2));
    var minutofinal       = parseFloat(hsaida.val().substring(3, 5));
    var jornada           = parseFloat(jd2.val());
    var dutil             = dia_util.val();

    horainicio        = horainicio      * 60 + minutoinicio;
    horapausa         = horapausa       * 60 + minutopausa;
    horacontinuacao   = horacontinuacao * 60 + minutocontinuacao;
    horafinal         = horafinal       * 60 + minutofinal;
    intervalo         = horacontinuacao - horapausa;
    jorndia           = horafinal       - horainicio;
    jornada_realizada = jorndia         - intervalo;

    if (jornada_realizada >= 480 && jornada == 480 && dutil != 'N' && ((horapausa != 0 && horainicio > horapausa) || (horacontinuacao != 0 && horainicio > horacontinuacao) || horainicio > horafinal))
    {
        oTeste.setMsg("Hora do inicio do expediente não pode ser maior que os horários seguintes!", entra);
    }
    if (jornada == 480 && dutil == 'N' && (horainicio > horafinal))
    {
        oTeste.setMsg("Hora do inicio do expediente não pode ser maior que hora do fim do expediente!", entra);
    }
    if (jornada < 480 && (horainicio > horafinal))
    {
        oTeste.setMsg("Hora do inicio do expediente não pode ser maior que hora do fim do expediente!", entra);
    }
    if (horapausa > horacontinuacao || horapausa > horafinal)
    {
        oTeste.setMsg("Hora do início do intervalo deve  ser  menor que fim do intervalo e fim do expediente!", iniint);
    }
    if (jornada_realizada >= 480 && horacontinuacao > horafinal)
    {
        oTeste.setMsg("Hora do fim do intervalo deve  ser  menor que fim do expediente!", fimint);
    }
    
    var diferenca = horacontinuacao - horapausa;
    
    if (jornada == 480 && dutil == 'S' && (diferenca != 0 && diferenca < 60))
    {
        oTeste.setMsg("O intervalo deve  ser  igual ou maior que uma hora!", iniint);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        $('#form1').submit();
    }

    return true;
}
