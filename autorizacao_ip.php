<?php
include_once("config.php");

verifica_permissao("sRH");

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$oDBase = new DataBase('PDO');

$boxSetores = listSetoresCombo();
$testaSetor = listSetoresCombo();

$setor = $testaSetor->fetch_assoc()['setor'];


if(!empty($_POST)){

    if(!empty($_POST['setorfind'])){
        $oIps = seleciona_ips_list($_POST['setorfind']);
        $total_ipes = $oIps->num_rows();
        $setor = $_POST['setorfind'];

    }else if (!empty($_POST['bulkinsert'])) {
        for($i = 0, $size = count($_POST['bulkinsert']); $i < $size; ++$i) {
            adicionarip($_POST['bulkinsert'][$i]);
        }
        return;
    }else{
        adicionarip($_POST);
        registraLog("Cadastro de IP");
        replaceLink("autorizacao_ip.php");
    }
}

$oIps = seleciona_ips_list($setor);
$total_ipes = $oIps->num_rows();

$dia = (isset($dia) ? $dia : date('d/m/Y'));
$cmd = (isset($cmd) ? $cmd : '2');
$orig = (isset($orig) ? $orig : '1');
$qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);

$vDatas = conv_data($dia);
$mes = dataMes($dia);
$ano = dataAno($dia);
$comp = $mes . $ano;



if(!empty($_GET['delete-autorization'])) {
    $oDBases= new DataBase('PDO');
    $oDBases->query("SELECT * FROM ips_setor WHERE ips_setor.id = :id", array(array(":id", $_GET['id'], PDO::PARAM_INT)));
    $registro = $oDBases->fetch_assoc();

    $oDBase->query("DELETE FROM ips_setor WHERE ips_setor.id = :id", array(array(":id", $_GET['id'], PDO::PARAM_INT)));
    registraLog("O gestor ".$_SESSION['sMatricula']." desautorizou o ip ".$registro['endereco'] . " a ter acesso ao sistema.");
    replaceLink("autorizacao_ip.php");
}


$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setCSS('css/new/sorter/css/theme.bootstrap_3.min.css');
$oForm->setJS('css/new/sorter/js/jquery.tablesorter.min.js');
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();

?>
<style>
    .inibir_opcao {
        opacity: 0.15;
        -moz-opacity: 0.15;
        filter: alpha(opacity=15);
    }

    /* The Modal (background) */
    .modal-csv {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content/Box */
    .modal-content-csv {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 60%; /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    #label-file-select {
        background-color: #0c691c;
        border-radius: 5px;
        color: #fefefe;
        cursor: pointer;
        margin: 10px;
        padding: 6px 20px;
    }
</style>
<script>
  var id = "";
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

    $(document).on('click', '.save', function () {
      var ip  = $("[name='ip']").val();

      if(validateIP(ip) || ip === "*"){
        $(".formip").submit();
      } else {
        alert('Endereço IP inválido!');
        $("[name='ip']").val("");
      }
    });

    $(document).on('click', '.importarcsv', function () {
        document.getElementById("myModal").style.display = "block";
    });

    $(document).on('click', '.close', function () {
        document.getElementById("myModal").style.display = "none";
    });

    function process(dataString) {
        var lines = dataString.split(/\n/);
        var payload = {
            bulkinsert: []
        };

        lines.forEach(function (line) {
            var data = line.replace('\r','').replace(/"/g,'').replace(/'/g,'').split(",");
            var setor = null;
            var ip = null;

            if (data.length === 2) {
                setor = data[0];
                ip = data[1];
            }
            else if (data.length === 1) {
                setor = $("#hdnsetor").val();
                ip = data[0];
            }

            if (ip != null && ip !== "" && (validateIP(ip) || ip === "*")) {
                payload.bulkinsert.push({hdnsetor: setor, ip: ip});
            }

        });

        if (payload.bulkinsert.length > 0) {
            //create the ajax request
            $.ajax({
                url: "autorizacao_ip.php",
                type: "POST",
                data: payload,
                dataType: "json"
                
            });

            $("#result-file-select").text("Arquivo processado com sucesso!");
            window.location.href = 'autorizacao_ip.php';
        } else {
            $("#result-file-select").text("Falha ao processar arquivo!");
        }
    }

    $(document).on('change', '#fileInput', function (e) {
        var file = e.target.files[0];

        $("#span-file-select").text(file.name);

        document.getElementById("result-file-select").style.display = "block";
        var reader = new FileReader();
        reader.onload = function() {
             process(reader.result);
        };

        reader.readAsText(file);
    });

    window.onclick = function(event) {
        if (event.target == document.getElementById("myModal")) {
            document.getElementById("myModal").style.display = "none";
        }
    }

    $(document).on('click', '.delete-ip', function () {
      id = $(this).attr('data-value');

      bootbox.confirm({
        locale: "br",
        title: "Excluir IP's",
        message: " Deseja mesmo excluir esse IP da faixa de IP's autorizados?",
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
          executaExclusaoIP(result);
        }
      });
    });

    $('.setor').change(function(){
      $("[name='form-setor']").attr('onsubmit','javascript:return true;');
      $("[name='form-setor']").attr('action','autorizacao_ip.php');
      $("[name='form-setor']").submit();
    });

    /**
     *
     * @param ip
     * @returns {boolean}
     */
    function validateIP(ip) {
      //Check Format
      var ip = ip.split(".");

      if (ip.length != 4) {
        return false;
      }

      //Check Numbers
      for (var c = 0; c < 3; c++) {
        //Perform Test
        if ( ip[c] <= -1 || ip[c] > 255 ||
          isNaN(parseFloat(ip[c])) ||
          !isFinite(ip[c])  ||
          ip[c].indexOf(" ") !== -1 ) {

          return false;
        }
      }

      return true;
    }
});

  function executaExclusaoIP(result)
  {
    if(result){
      window.location.href = 'autorizacao_ip.php?delete-autorization=true&id=' + id;
    }
  }
</script>

<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Autorização de Faixa de IP</strong></h4>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="col-md-2">
                    <p><b>ÓRGÃO: </b><?= tratarHTML(getOrgaoByUorg( $qlotacao )); ?></p>
                </div>
                <div class="col-md-8">
                    <p><b>UPAG: </b><?= tratarHTML($_SESSION['upag']); ?></p>
                </div>
                <div class="col-md-2 ">
                    <button type='button' class='btn btn-default importarcsv' data-dismiss='modal' style="margin-bottom: 5px; float: right">Importar CSV</button>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-2">
                    <label><b>SETORES: </b> </label>
                </div>
                <div class="col-md-10">
                    <form method="POST" name="form-setor" action="javascript:void(0);" onsubmit="javascript:return true;">
                        <select class="form-control setor boxcust select2-single" name="setorfind" style>

                            <?php while ($setores = $boxSetores->fetch_assoc()): ?>

                                <?php if($setor == $setores['setor']): ?>
                                    <option selected value="<?= tratarHTML($setores['setor']); ?>"> <?= tratarHTML($setores['descricao']); ?></option>
                                <?php else: ?>
                                    <option value="<?= tratarHTML($setores['setor']); ?>"> <?= tratarHTML($setores['descricao']); ?></option>
                                <?php endif; ?>

                            <?php endwhile; ?>

                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="row margin-25">
            <form method="POST" name="form-autorizacoes" class="formip" action="autorizacao_ip.php">
                <div class="col-md-3 text-right">
                    <input type="text" name="ip" class="form-control" placeholder="Informe o IP..." style="margin-left: 20%;">
                </div>
                <input id="hdnsetor" type="hidden" name="hdnsetor" value="<?= tratarHTML($setor); ?>" class="form-control">

                <div class="col-md-5">
                    <button type='button' class='btn btn-default save' data-dismiss='modal' style="margin-left: 10%;">Adicionar</button>
                </div>
            </form>

        </div>

        <div class="col-md-12">
            <br>
            <div class="row">
                <fieldset width='90%'>Total de <?= tratarHTML($total_ipes); ?> registros.</fieldset>
                <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-left" style='vertical-align:middle;'>IP</th>
                        <th class="text-center" style='vertical-align:middle;width: 10%;'>Ações</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php while ($ip = $oIps->fetch_object()): ?>

                        <tr>
                            <td align='left'><?= tratarHTML($ip->endereco); ?></td>
                            <td align='center'><a class="delete-ip" data-value="<?= tratarHTML($ip->id); ?>"><img border='0' src='<?= _DIR_IMAGEM_; ?>lixeira2.jpg' width='16' height='16' align='absmiddle' alt='Excluir' title='Excluir'></a></td>
                        </tr>

                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- The Modal -->
<div id="myModal" class="modal-csv">

    <!-- Modal content -->
    <div class="modal-content-csv">
        <span class="close">&times;</span>
        <div>
            <label id='label-file-select' for='fileInput'>Selecionar um arquivo &#187;</label>
            <span id='span-file-select' style="overflow: hidden;">Sem arquivo.</span>
            <input type="file" id="fileInput" style="display: none;" accept=".csv">
        </div>
        <p></p>
        <div id="result-file-select" style="display: none;"></div>
    </div>

</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

