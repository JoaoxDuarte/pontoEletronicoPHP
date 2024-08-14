
/*---------------------------------------------------------------------
 Fun��o    : dataExtenso
 Descri��o : Data do sistema por extenso
 Retorna   : Data por extenso (Brasil)
 Autor     : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function dataExtenso()
{
    // Descri��o dos meses
    var aMes = new Array("Janeiro", "Fevereiro", "Mar�o", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");
    // Descri��o dos dias da semana
    var aDia = new Array("Domingo", "Segunda", "Ter�a", "Quarta", "Quinta", "Sexta", "S�bado");
    // Data de hoje desmembrada em dia, m�s, ano e dia da semana
    var dHoje = new Date();
    // Retorna a data por extenso
    return (aDia[dHoje.getDay()] + ", " +
        dHoje.getDate() + " de " +
        aMes[dHoje.getMonth()] + " de " +
        dHoje.getFullYear());
}

/*---------------------------------------------------------------------
 Fun��o    : DataPorExtenso
 Descri��o : Exibe a data por extenso
 Parametros:	Nenhum
 Retorna   : Nada
 Autor     : Edinalvo Rosa
 Observa��o: Manter compatibilidade
 ---------------------------------------------------------------------*/
function DataPorExtenso()
{
    document.write(dataExtenso());
}

/*---------------------------------------------------------------------
 Fun��o    : setNewDate
 Descri��o : Define uma nova data
 Parametros:
 d    - Data nova
 sepr - Separador (ex: '/')
 Uso       : novaData = setNewDate('01/01/2004');
 Retorna   : Nova data
 Autor     : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function setNewDate(d, sepr)
{
    var sepr = ((sepr === null) ? '/' : sepr);
    var d = d.replace(/[\(a-z)\(!-+)\(:-�)\.\,\-\\\ \ ]/gi, sepr);
    var dt = d.split(sepr);
    var d = new Date(dt[1] + sepr + dt[0] + sepr + dt[2]);
    return d;
}

/*---------------------------------------------------------------------
 Fun��o     : dataSys
 Descri��o  : Pega a data do sistema
 Parametros : 0 = Completa (Tue Oct 19 10:51:16 UTC-0200 2004)
 1 = Formato de data 'dd/mm/aaaa'
 Retorna    : data atual
 Autor      : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function dataSys(tipo)
{
    var tipo = ((tipo === null) ? 0 : 1);
    var d = new Date();
    if (tipo === 1)
    {
        d = d.getDate() + "/" + (d.getMonth() + 1) + "/" + d.getFullYear();
    }
    return (d);
}

/*---------------------------------------------------------------------
 Fun��o     : diaHoje
 Descri��o  : Pega o dia da data atual
 Parametros : Nenhum
 Retorna    : dia atual
 Autor      : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function diaHoje()
{
    var d = new Date();
    var dia = '00' + '' + d.getDate();
    dia = (dia <= 9 ? '0' + '' + dia : dia);
    return dia;
}

/*---------------------------------------------------------------------
 Fun��o     : mesHoje
 Descri��o  : Pega o mes da data atual
 Parametros : Nenhum
 Retorna    : mes atual
 Autor      : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function mesHoje()
{
    var d = new Date();
    var mes = (d.getMonth() + 1);
    mes = (mes <= 9 ? '0' + '' + mes : mes);
    return mes;
}

/*---------------------------------------------------------------------
 Fun��o     : anoHoje
 Descri��o  : Pega o ano da data atual
 Parametros : Nenhum
 Retorna    : ano atual (com 4 digitos)
 Autor      : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function anoHoje()
{
    var d = new Date();
    return d.getFullYear();
}

/*---------------------------------------------------------------------
 Fun��o     : dateAdd
 Descri��o  : Adiciona dia(s), mes(es) e ano(s) a data desejada
 Parametros :
 rData  - Data a ser incrementada (ex: '01/01/2000')
 nDias  - Numero de dias a acrescentar
 nMeses - Numero de meses a acrescentar
 nAnos  - Numero de anos a acrescentar
 Utiliza    : setNewDate()
 Retorna    : data incrementada
 Autor      : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function dateAdd(rData, nDias, nMeses, nAnos)
{
    var rData = setNewDate(rData);
    // Testa parametros
    var nDias = ((nDias === null) ? 0 : nDias);
    var nMeses = ((nMeses === null) ? 0 : nMeses);
    var nAnos = ((nAnos === null) ? 0 : nAnos);
    // Incrementa a data
    rData.setDate(rData.getDate() + nDias);				// Incrementa dia(s)
    rData.setMonth(rData.getMonth() + nMeses);	// Incrementa Mes(es)
    rData.setYear(rData.getFullYear() + nAnos);		// Incrementa ano(s)
    // Retorna a nova data
    return rData;
}

/*---------------------------------------------------------------------
 Fun��o     : yearAdd
 Descri��o  : Adiciona ano(s) a data desejada
 Parametros :
 rData  - Data a ser incrementada (ex: '01/01/2000')
 nAnos  - Numero de anos a acrescentar
 Utiliza    : dateAdd()
 Retorna    : data incrementada
 Autor      : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function yearAdd(rData, nAnos)
{
    var rData = rData;
    var nAnos = nAnos;
    return dateAdd(rData, 0, 0, nAnos);
}

/*---------------------------------------------------------------------
 Fun��o    : monthAdd
 Descri��o : Adiciona mes(es) a data desejada
 Parametros :
 rData  - Data a ser incrementada (ex: '01/01/2000')
 nMeses - Numero de anos a acrescentar
 Utiliza    : dateAdd()
 Retorna    : data incrementada
 Autor      : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function monthAdd(rData, nMeses)
{
    var rData = rData;
    var nMeses = nMeses;
    return dateAdd(rData, 0, nMeses, 0);
}

/*---------------------------------------------------------------------
 Fun��o    : dayAdd
 Descri��o : Adiciona dia(s) a data desejada
 Parametros :
 rData  - Data a ser incrementada (ex: '01/01/2000')
 nMeses - Numero de anos a acrescentar
 Utiliza    : dateAdd()
 Retorna    : data incrementada
 Autor      : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function dayAdd(rData, nDias)
{
    var rData = rData;
    var nDias = nDias;
    return dateAdd(rData, nDias, 0, 0);
}

/*---------------------------------------------------------------------
 Fun��o    : dataToString
 Descri��o : Inverte a data para aaaammdd
 Parametros:
 dDt     - Data
 inverte - True  (ex. 20000101)
 False (ex. 01012000)
 sepr    - char p/ separar a data (ex. 2000/01/01 ou 01/01/2000
 Retorna   : Data string [e invertida]
 Autor     : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function dataToString(dDt, xInv, sepr)
{
    var xInv = ((xInv === null) ? false : xInv);
    var sepr = ((sepr == null) ? "" : sepr);
    var dDia = '0' + dDt.getDate();
    var dMes = '0' + (dDt.getMonth() + 1);
    dDia = dDia.substr(dDia.length - 2, 2);
    dMes = dMes.substr(dMes.length - 2, 2);
    dAno = dDt.getFullYear();
    //
    if (xInv)
    {
        xTmp = dDia;
        dDia = dAno;
        dAno = xTmp;
    }
    dDataStr = dDia + '' + sepr + '' + dMes + '' + sepr + '' + dAno;
    return (dDataStr.toString());
}

/*---------------------------------------------------------------------
 Fun��o    : invDataToString
 Descri��o : Inverte a data para aaaammdd
 Parametros:	dDt - Data
 Utiliza   : dataToString()
 Retorna   : Data invertida e string
 Autor     : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function invDataToString(dDt)
{
    return dataToString(dDt, true);
}

/*---------------------------------------------------------------------
 Fun��o    : invDataToNumber
 Descri��o : Inverte a data para aaaammdd
 Parametros:	dDt - Data
 Utiliza   : dataToString()
 Retorna   : Data invertida e numero
 Autor     : Edinalvo Rosa
 ---------------------------------------------------------------------*/
function invDataToNumber(dDt)
{
    return parseInt(dataToString(dDt, true));
}

//-->
