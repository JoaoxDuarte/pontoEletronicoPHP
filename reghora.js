$(document).ready(function ()
{
    //
});

function verificadados()
{
    if (document.form1.entra.value.length < 5)
    {
        alert("A Entrada é obrigatória no formato hh:mm!");
        document.form1.entra.focus();
        return false;
    }
    if (document.form1.sai.value.length < 5)
    {
        alert("A saida é obrigatória no formato hh:mm!");
        document.form1.sai.focus();
        return false;
    }

    var horainicio = parseFloat(document.form1.entra.value.substring(0, 2));
    var minutoinicio = parseFloat(document.form1.entra.value.substring(3, 5));
    var horapausa = parseFloat(document.form1.intini.value.substring(0, 2));
    var minutopausa = parseFloat(document.form1.intini.value.substring(3, 5));
    var horacontinuacao = parseFloat(document.form1.intsai.value.substring(0, 2));
    var minutocontinuacao = parseFloat(document.form1.intsai.value.substring(3, 5));
    var horafinal = parseFloat(document.form1.sai.value.substring(0, 2));
    var minutofinal = parseFloat(document.form1.sai.value.substring(3, 5));
    var jornada = parseFloat(document.form1.jd2.value);

    horainicio = horainicio * 60 + minutoinicio;
    horapausa = horapausa * 60 + minutopausa;
    horacontinuacao = horacontinuacao * 60 + minutocontinuacao;
    horafinal = horafinal * 60 + minutofinal;
    intervalo = horacontinuacao - horapausa;
    jorndia = horafinal - horainicio;

    if (jornada == 480 && (horainicio > horapausa || horainicio > horacontinuacao || horainicio > horafinal))
    {
        alert("Hora do inicio do expediente não pode ser maior que os demais horários!");
        document.form1.entra.focus();
        return false;
    }
    if (jornada < 480 && (horainicio > horafinal))
    {
        alert("Hora do inicio do expediente não pode ser maior que hora do fim do expediente!");
        document.form1.entra.focus();
        return false;
    }
    if (jornada == 480 && (document.form1.intini.value < 1))
    {
        alert("É obrigatório o inicio de intervalo para jornada de 08:00!");
        document.form1.intini.focus();
        return false;
    }
    if (jornada == 480 && (document.form1.intsai.value < 1))
    {
        alert("É obrigatório o fim de intervalo para jornada de 08:00!");
        document.form1.intsai.focus();
        return false;
    }
    if (horapausa > horacontinuacao || horapausa > horafinal)
    {
        alert("Hora do início do intervalo deve  ser  menor que fim do intervalo e fim do expediente !");
        document.form1.intini.focus();
        return false;
    }
    if (horacontinuacao > horafinal)
    {
        alert("Hora do fim do intervalo deve  ser  menor que fim do expediente !");
        document.form1.intsai.focus();
        return false;
    }
    if (jornada == 480 && horacontinuacao - horapausa < 60)
    {
        alert("O intervalo deve  ser  igual ou maior que uma hora !");
        document.form1.intini.focus();
        return false;
    }
    if (jorndia - intervalo > jornada)
    {
        alert("Não é permitido jornada  maior que a jornada legal!!");
        document.form1.sai.focus();
        return false;
    }
    if (jorndia - intervalo < jornada)
    {
        alert("Não é permitido jornada  menor que a jornada legal!");
        document.form1.sai.focus();
        return false;
    }
    if (document.form1.bhoras.value == 9 || document.form1.bhoras.value == '00')
    {
        alert('Favor autorizar ou não banco de horas!!');
        document.form1.bhoras.focus();
        return false;
    }
}


/*-------------------------------------------------------\
 |     AJAX - Grava horário                               |
 \-------------------------------------------------------*/
function calculaHorario(obj)
{
    // dados
    var entrada = $('#entra').val();
    var form_dados = $('#form1').serialize();

    if (entrada.length == 5)
    {
        // mensagem processando
        showProcessando();
        //create the ajax request
        $.ajax({
            type: "POST",
            url: "reghora_calcula.php", // a pagina que sera chamada
            data: form_dados, // dados enviados
            timeout: 3000,
            dataType: "json",
            beforeSend: function ()
            {
                // enquanto a função esta sendo processada, você
                // pode exibir na tela uma msg de carregando
                //$('#mensagem_aviso').html('<img src="imagem/loading.gif"/>');
            },
            success: function (response)
            {
                var ojson = response.dados;
                // Número de itens
                var tam = ojson.length;
                if (tam == 0 || ojson[0].erro == null)
                {
                    alert("Registro não localizado!");
                }
                else if (ojson[0].erro != '')
                {
                    alert(ojson[0].erro);
                }
                else
                {
                    $("#sai").val(ojson[0].saida);
                }
            },
            error: function (txt)
            {
                // em caso de erro
                hideProcessando();
                alert('Houve um problema interno. Tente novamente.');
            },
            complete: function (data)
            {
                hideProcessando();
            }

        });
    }
    return true;
}
