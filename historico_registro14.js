
function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var entra  = $('#entra');
    var iniint = $('#iniint');
    var fimint = $('#fimint');
    var hsaida = $('#hsaida');
    var jd2    = $('#jd2');
    var dutil  = $('#dutil');
    var ocor   = $('#ocor');

    var codigoFrequenciaNormalPadrao = $('#codigoFrequenciaNormalPadrao').val();     //'00000';
    var grupoOcorrenciasViagem       = $('#grupoOcorrenciasViagem').val(); //00040,00041,00042,00043,00109,00110,00111

    if (time_to_sec(iniint.val()) == 0 && entra.val().lenght == 8 && time_to_sec(entra.val()) > 0)
    {
        iniint.val( '00:00:00' );
    }
    if (time_to_sec(fimint.val()) == 0 && entra.val().lenght == 8 && time_to_sec(entra.val()) > 0)
    {
        fimint.val( '00:00:00' );
    }
    if (time_to_sec(hsaida.val()) == 0 && entra.val().lenght == 8 && time_to_sec(entra.val()) > 0)
    {
        hsaida.val( '00:00:00' );
    }

    // verifica o horario de entrada
    if (codigoFrequenciaNormalPadrao.indexOf(ocor.val()) == 0
        && grupoOcorrenciasViagem.indexOf(ocor.val()) == 0
        && (time_to_sec(entra.val()) == 0 || entra.val().length < 8))
    {
        oTeste.setMsg('Horário de entrada deve ser diferente de 00:00:00!', entra);
    }
    if (codigoFrequenciaNormalPadrao.indexOf(ocor.val()) == 0
        && grupoOcorrenciasViagem.indexOf(ocor.val()) == 0
        && (time_to_sec(hsaida.val()) == 0 || hsaida.val().length < 8))
    {
        oTeste.setMsg('Horário de saída deve ser diferente de 00:00:00!', hsaida);
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
    var dutil             = dutil.val();

    horainicio        = horainicio * 60 + minutoinicio;
    horapausa         = horapausa  * 60 + minutopausa;
    horacontinuacao   = horacontinuacao * 60 + minutocontinuacao;
    horafinal         = horafinal  * 60 + minutofinal;
    intervalo         = horacontinuacao - horapausa;
    jorndia           = horafinal - horainicio;
    jornada_realizada = jorndia - intervalo;

    if (codigoFrequenciaNormalPadrao.indexOf(ocor.val()) == 0 && grupoOcorrenciasViagem.indexOf(ocor.val()) == 0)
    {
        if (jornada_realizada >= 480 && jornada == 480 && dutil == 'S' && (horainicio > horapausa || horainicio > horacontinuacao || horainicio > horafinal))
        {
            oTeste.setMsg('Hora do inicio do expediente não pode ser maior que os horários seguintes!', entra);
        }
        if (jornada == 480 && dutil == 'N' && (horainicio > horafinal))
        {
            oTeste.setMsg('Hora do inicio do expediente não pode ser maior que hora do fim do expediente!', entra);
        }
        if (jornada < 480 && (horainicio > horafinal))
        {
            oTeste.setMsg('Hora do inicio do expediente não pode ser maior que hora do fim do expediente!', entra);
        }
        if (horapausa > horacontinuacao || horapausa > horafinal)
        {
            oTeste.setMsg('Hora do início do intervalo deve ser menor que fim do intervalo e fim do expediente!', iniint);
        }
        if (jornada_realizada >= 480 && horacontinuacao > horafinal)
        {
            oTeste.setMsg('Hora do fim do intervalo deve ser menor que fim do expediente!', fimint);
        }
        if (jornada_realizada >= 480 && jornada == 480 && dutil == 'S' && (intervalo < 60))
        {
            oTeste.setMsg('O intervalo deve ser igual ou maior que uma hora!', iniint);
        }
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;

}

function testa(modo)
{
    var modo = (modo == null ? 0 : modo);
    if (modo != 0 || validar() == true)
    {
        document.form1.submit();
    }
}
