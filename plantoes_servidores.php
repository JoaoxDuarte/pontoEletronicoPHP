<?php
include_once("config.php");
include_once( "src/controllers/TabPlantoesServidoresController.php" );

verifica_permissao("plantoes");

$oPlantonistas = new TabPlantoesServidoresController();


$dados = $oPlantonistas->registrosPlantoesServidores();

$oForm = new formPadrao();
$oForm->setCSS('css/new/sorter/css/theme.bootstrap_3.min.css');
$oForm->setJS('css/new/sorter/js/jquery.tablesorter.min.js');
$oForm->setSubTitulo( "Incluir Servidor em Plantão" );

// Topo do formul?rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();



if(!empty($_POST['autorizacao']))
{
  $retorno = $oPlantonistas->gravar();

  switch ($retorno['retorno'])
  {
    case 'gravou':
      mensagem( $retorno['mensagem'], 'plantoes_servidores.php', null, 'success' );
      break;

    case 'nao_gravou':
      mensagem( $retorno['mensagem'], 'plantoes_servidores.php', null, 'danger' );
      break;

    default:
      mensagem( "Servidor plantonista registrado com sucesso!", 'plantoes_servidores.php', null, 'danger' );
  }

  exit;
}

?>
<script>
  $(document).ready(function () {

    $('#btn-salvar').on('click', function () {
      var dados = $('#form1').serialize();

      console.log(dados);

      $('#autorizacao').val('sim');
      $('#form1').attr('onsubmit', 'javascript:return true;');
      $('#form1').submit();

      return false;
    });

  });

  function exibeEscala(obj,siape)
  {
    var sigla = $('#escala'+obj.value+'_'+siape).val();
    console.log(sigla);
    $('#escala_'+siape).html( sigla );
  }
</script>

<div class="container">

  <form id="form1" name="form1" method="POST" action="plantoes_servidores.php" onsubmit="return false;">

    <input type="hidden" id="autorizacao" name="autorizacao" value="">

    <div class="row">
      <div class="col-md-11">
        <br>
        <div class="row">

          <fieldset class="col-md-3" width='100%'>Total de <?= $dados->num_rows(); ?> registros.</fieldset>
          <table id="myTable" class="table table-striped table-bordered table-hover text-center table-condensed tablesorter">
            <thead>
              <tr>
                <th class="text-center" style='vertical-align:middle;'>Matrícula</th>
                <th class="text-center" style='vertical-align:middle;'>Nome do Servidor</th>
                <th class="text-center" style='vertical-align:middle;'>Plantão</th>
                <th class="text-center" style='vertical-align:middle;'>Escala</th>
                <th class="text-center" style='vertical-align:middle;'>Compensação</th>
                <th class="text-center" style='vertical-align:middle;' nowrap>Fora do Horário</th>
                </tr>
            </thead>
            <tbody>

              <?php while ($rows = $dados->fetch_object()): ?>

                <tr>
                  <td style="text-align:center;vertical-align:middle;width:1%;">
                    <?= $rows->siape; ?></td>
                  <td style="text-align:left;vertical-align:middle;width:30%;">
                    <?= $rows->nome; ?></td>
                  <td style="text-align:left;width:30%;" nowrap>

                    <input type="hidden" id="id['<?= $rows->siape; ?>']" name="id['<?= $rows->siape; ?>']" value="<?= $rows->id; ?>">

                    <select id="id_plantao<?= $rows->siape; ?>"
                            name="id_plantao['<?= $rows->siape; ?>']"
                            class="form-control select2-single"
                            onchange="javascript:exibeEscala(this,'<?= $rows->siape; ?>');">
                        <option value="0" data-escala=''>Sem Plantão</option>
                      <?php

                      $sem_plantao = true;

                      $opcoes_sigla = "";
                      $selected     = "";
                      $inputs       = "<input type='hidden' id='escala0_".$rows->siape."' value=''>";

                      $query = "
                      SELECT plantoes.id, plantoes.id_escala, plantoes.descricao,
                             plantoes.hora_inicial, plantoes.hora_final, plantoes.uorg,
                             plantoes.data_criacao, plantoes.data_encerramento,
                             plantoes.ativo, escalas.descricao AS escala_descricao,
                             CONCAT(escalas.trabalhar,'x',escalas.folgar) AS escala_sigla,
                             IFNULL(escalas.jornada,'') AS jornada
                      FROM plantoes
                      LEFT JOIN escalas ON plantoes.id_escala = escalas.id
                      WHERE plantoes.uorg = :uorg
                            AND plantoes.ativo = 'S'
                            AND IFNULL(escalas.jornada,'') = '".$rows->jornada."'
                      ";

                      $param = array(
                        array( ':uorg', $_SESSION['sLotacao'], PDO::PARAM_STR),
                      );

                      $oDBase = new DataBase();
                      $oDBase->query( $query, $param );

                      while ($opcoes_plantao = $oDBase->fetch_object())
                      {
                        $sem_plantao = false;

                        if ($opcoes_plantao->id == $rows->id_plantao)
                        {
                          $opcoes_sigla = $opcoes_plantao->escala_sigla;
                        }

                        $inputs .= "<input type='hidden' id='escala".$opcoes_plantao->id."_".$rows->siape."' value='".$opcoes_plantao->escala_sigla."'>";

                        ?>
                        <option value='<?= $opcoes_plantao->id; ?>'
                                <?= ($opcoes_plantao->id == $rows->id_plantao ? ' selected' : ''); ?>>
                          <?= $opcoes_plantao->descricao; ?>
                        </option>
                        <?php
                      }

                      ?>
                    </select>
                    <?= $inputs; ?>
                  </td>
                  <td style="text-align:left;vertical-align:middle;width:1%;" nowrap id="escala_<?= $rows->siape; ?>"><?= $opcoes_sigla; ?></td>
                  <td style="text-align:left;width:1%;">
                    <select id="compensar['<?= $rows->siape; ?>']"
                            name="compensar['<?= $rows->siape; ?>']"
                            class="form-control">

                      <?php if ($sem_plantao == false): ?>

                        <option value="N" <?= ($rows->compensar != 'S' ? ' selected' : ''); ?>>Não</option>
                        <option value="S" <?= ($rows->compensar == 'S' ? ' selected' : ''); ?>>Sim</option>

                      <?php else: ?>

                        <option value="N" <?= ($rows->compensar != 'S' ? ' selected' : ''); ?>>Não</option>

                      <?php endif; ?>

                    </select>
                  </td>
                  <td style="text-align:left;width:1%;">
                    <select id="fora_do_horario['<?= $rows->siape; ?>']"
                            name="fora_do_horario['<?= $rows->siape; ?>']"
                            class="form-control">

                      <?php if ($sem_plantao == false): ?>

                        <option value="N" <?= ($rows->fora_do_horario != 'S' ? ' selected' : ''); ?>>Não</option>
                        <option value="S" <?= ($rows->fora_do_horario == 'S' ? ' selected' : ''); ?>>Sim</option>

                      <?php else: ?>

                        <option value="N" <?= ($rows->fora_do_horario != 'S' ? ' selected' : ''); ?>>Não</option>

                      <?php endif; ?>

                    </select>
                  </td>
                </tr>

              <?php endwhile; ?>

            </tbody>
          </table>

        </div>
      </div>
    </div>

    <div class="row">
      <br>
      <div class="form-group col-md-12 text-center">
        <div class="col-md-3"></div>
        <div class="col-md-2 col-xs-4 col-md-offset-2">
          <a class="btn btn-success btn-block" id="btn-salvar" role="button">
            <span class="glyphicon glyphicon-ok"></span> Salvar
          </a>
        </div>
        <div class="col-md-3"></div>
      </div>
    </div>

  </form>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
