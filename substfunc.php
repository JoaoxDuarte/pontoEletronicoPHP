<?php
include_once( "config.php" );

verifica_permissao('sRH ou Chefia');

$upag = $_SESSION['upag'];

if (isset($_SESSION['sMatriculaSubstitutoEfetivar']) && empty($_SESSION['sMatriculaSubstitutoEfetivar']))
{
    $matricula = anti_injection($_REQUEST['matricula']);
}
else
{
    $matricula = $_SESSION['sMatriculaSubstitutoEfetivar'];
}

// instancia o banco de dados
$oDBase = new DataBase('PDO');

$novamatricula = getNovaMatriculaBySiape($matricula);

// dados do servidor
$oDBase->query("
    SELECT
        a.nome_serv, a.cod_lot,
        DATE_FORMAT(a.dt_ing_lot, '%d/%m/%Y') AS dt_ing_lot,
        IFNULL(b.upag,'') AS upag,
        IFNULL(c.num_funcao,'') AS num_funcao,
        IFNULL(d.desc_func,'') AS desc_funcao,
        IFNULL(d.cod_lot,'') AS cod_lot_funcao,
        IFNULL(d.cod_funcao,'') AS cod_funcao,
        IFNULL(e.cod_uorg,'') AS cod_uorg, IFNULL(e.area,'') AS `area`,
        IFNULL(e.uorg_pai,'') AS uorg_pai
    FROM
        servativ AS a
    LEFT JOIN
        tabsetor AS b ON a.cod_lot = b.codigo
    LEFT JOIN
        ocupantes AS c ON a.mat_siape = c.mat_siape
            AND c.sit_ocup = 'S' AND c.dt_fim = '0000-00-00'
    LEFT JOIN
        tabfunc AS d ON c.num_funcao = d.num_funcao
    LEFT JOIN
        tabsetor AS e ON d.cod_lot = e.codigo
    WHERE
          a.mat_siape = :siape
    ", array(
    array(":siape", $novamatricula, PDO::PARAM_STR),
));

$total_de_servidores = $oDBase->num_rows();

$oServidor                = $oDBase->fetch_object();
$nome_do_servidor         = $oServidor->nome_serv;
$lotacao_do_servidor      = $oServidor->cod_lot;
$data_ingresso_na_lotacao = $oServidor->dt_ing_lot;

$upag_do_setor       = $oServidor->upag; // obtem dados da uorg para saber se uorg ou upag e a mesma do usuario
// dados da funcao
$numero_da_funcao    = $oServidor->num_funcao;
$descricao_da_funcao = $oServidor->desc_funcao;
$lotacao_da_funcao   = $oServidor->cod_lot_funcao;
$sigla_da_funcao     = $oServidor->cod_funcao;

// area, uorg e uorg pai da função
$area               = $oServidor->area;
$codigo_da_uorg     = $oServidor->cod_uorg;
$codigo_da_uorg_pai = $oServidor->uorg_pai;

$situacao_do_ocupante = "SUBSTITUTO";

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJSDatePicker();
$oForm->setJS( 'js/phpjs.js' );
$oForm->setJS( 'substfunc.js?v.0.0.0.0.2' );

$oForm->setOnLoad("javascript: if($('#Ndata1')) { $('#Ndata1').focus() };");

// Titulo do formulário
$oForm->setSubTitulo("Registro de Efetiva Substitui&ccedil;&atilde;o de Fun&ccedil;&atilde;o");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

if ($total_de_servidores == 0)
{
    mensagem("Servidor não encontrado!", 'subsfuncinf.php', 1);
}
elseif ($upag_do_setor != $upag)
{
    mensagem("Não é permitido alterar dados de servidor de outra UPAG!", 'subsfuncinf.php', 1);
}
elseif (empty($numero_da_funcao))
{
    mensagem("O servidor não consta como substituto de função!", 'subsfuncinf.php', 1);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form method="POST" action="javascript:void(0);" id="form1" name="form1" onsubmit="javascript:return false;">
                <input type='hidden' id='modo'     name='modo'     value='7'>
                <input type='hidden' id='lotat'    name='lotat'    value='<?= tratarHTML($lotacao_do_servidor); ?>'>
                <input type='hidden' id='dinglota' name='dinglota' value='<?= tratarHTML($data_ingresso_na_lotacao); ?>'>
                <input type='hidden' id='lota'     name='lota'     value='<?= tratarHTML($lotacao_da_funcao); ?>'>
                <input type='hidden' id='sigla'    name='sigla'    value='<?= tratarHTML($sigla_da_funcao); ?>'>
                <input type='hidden' id='uorg'     name='uorg'     value='<?= tratarHTML($codigo_da_uorg); ?>'>
                <input type='hidden' id='pai'      name='pai'      value='<?= tratarHTML($codigo_da_uorg_pai); ?>'>

                <div class="form-group">
                    <div class="col-md-9">
                        <label class="control-label " for="nome">
                            Nome
                        </label>
                        <input class="form-control" id="nome" name="nome" type="text" value='<?= tratarHTML($nome_do_servidor); ?>' readonly/>
                    </div>
                    <div class="col-md-2">
                        <label class="control-label " for="siape">
                            Siape
                        </label>
                        <input class="form-control" id="siape" name="siape" type="text" value='<?= tratarHTML($matricula); ?>' readonly/>
                    </div>
                </div>

                <div class="col-md-12">&nbsp;</div>

                <div class="form-group">
                    <div class="col-md-2">
                        <label class="control-label " for="num_funcao">
                            Fun&ccedil;&atilde;o
                        </label>
                        <input class="form-control" id="num_funcao" name="num_funcao" type="text" value='<?= tratarHTML($numero_da_funcao); ?>' readonly/>
                    </div>
                    <div class="col-md-7">
                        <label class="control-label " for="siape">&nbsp;</label>
                        <input class="form-control" id="funcao" name="funcao" type="text" value='<?= tratarHTML($descricao_da_funcao); ?>' readonly/>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label " for="siape">Situa&ccedil;&atilde;o do ocupante:</label>
                        <input class="form-control" id="situacao" name="situacao" type="text" value='<?= tratarHTML($situacao_do_ocupante); ?>' readonly/>
                    </div>
                </div>

                <div class="col-md-12">&nbsp;</div>

                <div class="form-group" style="border:">
                    <div class="col-md-12" style="border-bottom:1px solid #B9B9B9">
                        <label class="control-label uppercase" for="periodo">
                            Per&iacute;odo de substitui&ccedil;&atilde;o
                        </label>
                    </div>
                </div>

                <div class="col-md-12">&nbsp;</div>

                <div class="form-group">
                    <div class="col-md-2">
                        <label class="control-label " for="Ndata1">
                            Data de Início
                        </label>
                        <div id="Ndata1-container">
                            <div class="input-group date">
                                <input id="Ndata1" name="Ndata1" type="text" class="form-control" style="background-color:transparent;width:100px;" onKeyPress="formatar(this, '##/##/####')" size="10" maxlength='10' readonly/><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="control-label " for="Ndata2">
                            Data Fim
                        </label>
                        <div id="Ndata2-container">
                            <div class="input-group date">
                                <input id="Ndata2" name="Ndata2" type="text" class="form-control" style="background-color:transparent;width:100px;" onKeyPress="formatar(this, '##/##/####')" size="10" maxlength='10' readonly/><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="control-label " for="motivo">
                            Motivo
                        </label>
                        <select id="motivo" name="motivo" class="form-control input-md select2-single">
                            <option value="0">INFORME O MOTIVO</option>
                            <?php
// dados do motivo
                            $oDBase->query("SELECT codigo, descricao FROM tabmotivo_substituicao ORDER BY codigo ");
                            while ($oDados = $oDBase->fetch_object())
                            {
                                print "<option value='" . tratarHTML($oDados->codigo) . "1'>" . tratarHTML($oDados->descricao) . "</option>\n";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12 margin-25">
                        <div class="col-md-6 text-right">
                            <a class="btn btn-success btn-primary" id="btn-continuar">
                                <span class="glyphicon glyphicon-ok"></span> Continuar
                            </a>
                        </div>
                        <div class="col-md-6 text-left">
                            <a class="btn btn-primary btn-danger" href="javascript:window.location.replace('subsfuncinf.php');">
                                <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                            </a>
                        </div>
                    </div>
                </div>

            </form>

        </div>

        </form>
    </div>
</div>
</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
