<?php
include_once("config.php");

verifica_permissao("chefia");

$qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);

$oDBase = new DataBase('PDO');

if($_GET['delete-autorization']){
    $oDBase->query("DELETE FROM servidores_autorizacao WHERE servidores_autorizacao.id = :id", array(array(":id", $_GET['id'], PDO::PARAM_INT)));
    $oDBase->query("DELETE FROM ips_autorizacao WHERE ips_autorizacao.servidor_autorizacao_id = :id", array(array(":id", $_GET['id'], PDO::PARAM_INT)));
    registraLog("Deletado autorização por faixa de IP por servidor ".$_GET['id']);
}


$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setSeparador(0);

$css = array();
$javascript = array();

$css[] = 'css/new/sorter/css/theme.bootstrap_3.min.css';
$javascript[] = 'css/new/sorter/js/jquery.tablesorter.min.js';
$oForm->exibeTopoHTML();

$oIpsServidor = seleciona_ips_servidor_list();
$total_ipes = $oIpsServidor->num_rows();

?>
<script src="js/jquery.min.js"></script>
<script src="js/jquery.mask.min.js"></script>

<style>
    .inibir_opcao {
        opacity: 0.15;
        -moz-opacity: 0.15;
        filter: alpha(opacity=15);
    }
</style>
<script>
  var id = "";

  $(document).ready(function () {

    $("[name='ip']").keypress(function () {
      $("[name='ip']").unmask();
    });

    $("[name='ip']").change(function () {
      var ip = $("[name='ip']").val();

      if (ip[0] === '*') {
        $("[name='ip']").mask('*');
      } else {
        $("[name='ip']").mask('0ZZ.0ZZ.0ZZ.0ZZ', {translation: {'Z': {pattern: /[0-9]/, optional: true}}});
      }
    });

    $(document).on('click', '.save', function () {
      var ip = $("[name='ip']").val();

      if(ip === '' || ip === '...'){
        alert("O campo IP é obrigatório!");
        return false;
      }

      $(".formip").attr('onsubmit', 'javascript:return true;');
      $(".formip").attr('aciont', "autorizacao_ip.php");
      $(".formip").submit();
    });

    $(document).on('click', '.delete-ip', function () {
      id = $(this).attr('data-value');

      bootbox.confirm({
        locale: "br",
        title: "Excluir Autorização",
        message: " Deseja mesmo excluir essa autorização?",
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
          executaExclusaoAutorizacao(result);
        }
      });
    });

    $(document).on('click', '.adicionar', function () {
      window.location.href = 'autorizacao_ip_servidor_adicionar.php';
    });
  });

  function executaExclusaoAutorizacao(result)
  {
    if(result){
      window.location.href = 'autorizacao_ip_servidor.php?delete-autorization=true&id=' + id;
    }
  }
</script>

<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Autorização de Faixa de IP por servidor</strong></h4>
        </div>

        <div class="row">
            <div class="col-md-2">
                <p><b>ÓRGÃO: </b><?= tratarHTML(getOrgaoMaisSigla( $qlotacao )); ?></p>
            </div>
            <div class="col-md-7">
                <p><b>UORG: </b><?= tratarHTML(getUorgMaisDescricao( $_SESSION['sLotacao'] )); ?></p>
            </div>
        </div>

        <div class="row">
            <form method="POST" name="form-autorizacoes" class="formip" action="javascript:void(0);" onsubmit="javascript:return false;">
                <div class="col-md-9">

                </div>
                <div class="col-md-3">
                    <button type='button' class='btn btn-default adicionar' data-dismiss='modal' style="margin-left: 10%;">Adicionar</button>
                </div>
            </form>
        </div>

        <div class="col-md-12">
            <br>
            <div class="row">
                <fieldset width='90%'>Total de <?= tratarHTML($total_ipes); ?> registros.</fieldset>
                <table id="myTable" class="table table-striped table-bordered text-center  table-hover table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-left" style='vertical-align:middle;'>Matricula</th>
                        <th class="text-left" style='vertical-align:middle;'>Servidor</th>
                        <th class="text-left" style='vertical-align:middle;'>IP</th>
                        <th class="text-left" style='vertical-align:middle;'>Período Autorizado</th>
                        <th class="text-left" style='vertical-align:middle;'>Justificativa</th>
                        <th class="text-center" style='vertical-align:middle;width: 10%;'>Ações</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php while ($ip = $oIpsServidor->fetch_object()): ?>

                        <tr>
                            <td align='left'><?= tratarHTML(removeOrgaoMatricula( $ip->matricula )); ?></td>
                            <td align='left'><?= tratarHTML($ip->servidor); ?></td>
                            <td align='left'><?= tratarHTML(getIpsBySiape($ip->id)); ?></td>
                            <td align='left'><?= tratarHTML($ip->periodo); ?></td>
                            <td align='left'><?= tratarHTML($ip->justificativa); ?></td>
                            <td align='center'><a class="delete-ip" data-value="<?= tratarHTML($ip->id); ?>"><img border='0' src='<?= _DIR_IMAGEM_; ?>lixeira2.jpg' width='16' height='16' align='absmiddle' alt='Excluir' title='Excluir'></a></td>
                        </tr>

                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

