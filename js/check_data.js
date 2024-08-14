function mascara_data(data, parm1, parm2)
{
    var mydata = '';
    mydata = mydata + data;
    if (mydata.length == 2)
    {
        mydata = mydata + '/';
        document.forms[parm2].elements[parm1].value = mydata;
    }
    if (mydata.length == 5)
    {
        mydata = mydata + '/';
        document.forms[parm2].elements[parm1].value = mydata;
    }

    //alvaro
    if (parm1 == "d_final")
    {
        if (document.all['d_final'].value == '' && mydata.length > 0)
        {
            document.all['d_inicio'].value = '';
            document.all['d_inicio'].focus();
            return false;
        }
    }

    if (mydata.length == 10)
    {
        verifica_data(parm1, parm2);
        //rotina de saltar p prox campo e verificar ult conv colocada na verifica_data
    }
}
// Fim mascara_data()



function verifica_data(parm1, parm2)
{
    dia = (document.forms[parm2].elements[parm1].value.substring(0, 2));
    mes = (document.forms[parm2].elements[parm1].value.substring(3, 5));
    ano = (document.forms[parm2].elements[parm1].value.substring(6, 10));

    situacao = "";
    // verifica o dia valido para cada mes
    if ((dia < 01) || (dia < 01 || dia > 30) && (mes == 04 || mes == 06 || mes == 09 || mes == 11) || dia > 31)
    {
        situacao = "falsa";
    }

    // verifica se o mes e valido
    if (mes < 01 || mes > 12)
    {
        situacao = "falsa";
    }

    // verifica se e ano bissexto
    if (mes == 2 && (dia < 01 || dia > 29 || (dia > 28 && (parseInt(ano / 4) != ano / 4))))
    {
        situacao = "falsa";
    }

    if (document.forms[parm2].elements[parm1].value == "")
    {
        situacao = "falsa";
    }

    if (situacao == "falsa")
    {
        alert("Data inválida!");
        document.forms[parm2].elements[parm1].value = "";
        document.forms[parm2].elements[parm1].focus();
        return false;
    }

    if (parm1 == "d_inicio")
    {
        ck_data_ini(parm1);
    }
    if (parm1 == "d_final")
    {
        ck_data_fim(parm1);
    }
}
// fim verifica_data()


function mascara_hora(hora, parm1, parm2)
{
    var myhora = '';
    myhora = myhora + hora;
    if (myhora.length == 2)
    {
        myhora = myhora + ':';
        document.forms[parm2].elements[parm1].value = myhora;
    }
    if (myhora.length == 5)
    {
        verifica_hora(parm1, parm2);
//aqui estava a rotina para saltar para o prox campo - foi transferida para dentro do verifica_hora
    }
}

function verifica_hora(parm1, parm2)
{
    hrs = (document.forms[parm2].elements[parm1].value.substring(0, 2));
    min = (document.forms[parm2].elements[parm1].value.substring(3, 5));

//alert('hrs '+ hrs);
//alert('min '+ min);

    situacao = "";
// verifica data e hora
    if ((hrs < 00) || (hrs > 23) || (min < 00) || (min > 59))
    {
        situacao = "falsa";
    }

    if (document.forms[parm2].elements[parm1].value == "")
    {
        situacao = "falsa";
    }

    if (situacao == "falsa")
    {
        alert("Hora inválida!");
        document.forms[parm2].elements[parm1].select();
        return false;
    }
//var ind = document.forms[parm2].elements[parm1].sourceIndex + 1;
//document.all[ind].focus();
}

//calendario juliano
function julianDay(m, d, y)
{
    var jYear, jDay, jMonth, jul;
    var greg = 15 + 10 * 31 + (12 * 31 * 1582); // Gregorian Adoption
    if (m > 2)
    { // Check for February
        jYear = y;
        jMonth = m + 1;
    }
    else
    {
        jYear = y - 1;
        jMonth = m + 13;
    }
    jul = Math.floor(365.25 * jYear) + Math.floor(30.6001 * jMonth) + d + 1720995; // Adapt to Julian

    if ((d + 31 * m + (12 * 31 * y)) > greg)
    { // After Adoption
        jDay = Math.floor(0.01 * jYear);
        jul += 2 - jDay + Math.floor(0.25 * jDay);
    }
    return jul;
}



