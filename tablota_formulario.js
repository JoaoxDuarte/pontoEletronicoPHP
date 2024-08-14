$(".horas").mask('##:##:00');

$(document).ready(function(){

    setTimeout(function(){
        if($('#tablota_formulario').length && $('#setor-massa').length){
            $('#setor-massa').show();
        }
    }, 3000);

    $('.btn-alterar-horario').click(function(){
        var msg = new alertaErro();
        msg.init();
    
        if($('#inicio_atend').val() == "" || $('#fim_atend').val() == ""){
            alert('Informe o horário de início e término de funcionamento do setor!');
            return false;
        }

        var uorgs = false;
        var setores = new Array();
        var totalUorgs = 0;
        
        $('input[name="uorgs[]"]:checked').each(function (){ 
            totalUorgs++;
            uorgs = true; 
            setores.push($(this).val());
        });

        if(!uorgs){
            alert('Selecione pelo menos um setor!');
            return false;
        }

        if(totalUorgs > 1000){
            alert('Só é permitido a alteração de até 1000 setores de uma vez!');
            return false;
        }
        
        var data = {
            'inicio_atend': $('#inicio_atend').val(),
            'fim_atend': $('#fim_atend').val(),
            'uorgs':  setores
        }

        $.ajax({
            url: "tablota_formulario_horario_ajax.php",
            type: "POST",
            data: {data}
        }).done(function(resultado) {
            alert(resultado);            

        }).fail(function(jqXHR, textStatus ) {
            alert('Falha ao atualizar horários!')

        }).always(function() {
            $('#inicio_atend').val('');
            $('#fim_atend').val('')
        });

        return bResultado;
    });
});