$(document).ready(function ()
{
    var dados_visualizar = "";

    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma unidade";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: '100%',
        containerCssClass: ':all:'
    });

    var $antes = "";

    $('[name=unidades_opcoes]').change(function() {

        if ($antes !== "")
        {
            $($antes).css("display","none");
        }

        if (this.value === 'todos')
        {
            $("[name=unidades]").css('display', "block");
        }
        else
        {
            $("[name=unidades]").css('display', "none");
        }

        $antes = this.value;

        $($antes).css("display","block");
        return false;
    });


    $("[name=unidades]").css('display', "none");
    $antes = $("[name=unidades_opcoes]").val();
    $($antes).css("display","block");

});
