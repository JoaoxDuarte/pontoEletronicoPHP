$(document).ready(function ()
{
  $("#form1").keypress(function (e) {
    if (e.witch === 13)
    {
      $('#btn-continuar').click();
    }
  });

  $("#btn-continuar").click(function () {
    validar();
  });

  $(document).on('click', '.delete-consulta', function () {
    var id   = $(this).attr('data-id');
    var nome = $(this).attr('data-nome');
 
    bootbox.confirm({
      locale: "br",
      title: "Excluir Registro",
      message: " Deseja realmente excluir este registro?",
      buttons: {
        confirm: {
          label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Sim</p>",
          className: 'btn-success'
        },
        cancel: {
          label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Não</p>",
          className: 'btn-danger'
        }
      },
      callback: function(result) {
        if (result){
          var dados = base64_encode("delete=sim&id=" + id + "&nome=" + nome);
          window.location.href = '?dados=' + dados;
        }
      }
    });
  });

  $(document).on('click', '.save', function () {
    var siape = $(this).attr('data-siape');
    var mensagem = "";

    // mensagem processando
    showProcessandoAguarde();

    mensagem = validaSiape(siape);

    if (mensagem === "")
    {
      $("[name='siape']").val(siape);

      $(".formsiape").attr('onsubmit', "javascript:return true;");
      $(".formsiape").attr('action', "hora_extra_autorizacao_registro.php");
      $(".formsiape").submit();
    }
    else
    {
      hideProcessandoAguarde();
      mostraMensagem(mensagem);
      return false;
    }
  });

  $('#chave').focus();
});

function validar()
{
  // objeto mensagem
  oTeste = new alertaErro();
  oTeste.init();

  var chave   = $('#chave');
  var escolha = $('#escolha').val();

  console.log('validar: 60');

  if (chave.val().length == 0 && escolha !== 'todos')
  {
    oTeste.setMsg('É obrigatório informar a chave para pesquisa!', chave);
  }
  else if (escolha === 'todos')
  {
    chave.val("");
  }

  // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
  var bResultado = oTeste.show();
  if (bResultado == false)
  {
    return bResultado;
  }
  else
  {
    // mensagem processando
    showProcessandoAguarde();

    $('#form1').attr('onsubmit', "javascript:return true;");
    $('#form1').attr('action', "hora_extra_autorizacao.php");
    $('#form1').submit();
  }
}
