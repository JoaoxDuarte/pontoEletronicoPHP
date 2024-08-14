<?php
include_once("config.php");

// verifica se o usuário tem permissão para acessar este módulo
verifica_permissao('sRH ou sTabServidor');

$id     = addslashes($_REQUEST['id']);
$lotusu = $_SESSION["sLotacao"];

// instancia banco de dados
$oDBase = new DataBase('PDO');

// seleciona os dados
$oFeriados = selecionaDadosDoFeriado( $id );
$dia       = $oFeriados->dia;
$mes       = $oFeriados->mes;
$des       = $oFeriados->desc;
$tipo      = $oFeriados->tipo;
$lot       = $oFeriados->lot;
$mun       = $oFeriados->codmun;
$dtfer     = $oFeriados->data_feriado;
$flegal    = $oFeriados->base_legal;


// monta select - lotação
// tabela de lotacao
$oSetor   = selecionaDadosDaUnidade( $lot );
$regional = $oSetor->regional;

$optionsLotacao = selecionaOptionsLotacao( $regional );

// Fim da tabela de lotacao

// Select de ufs
$optionsUf = selecionarOptionsUf($lot);

// Select de cidades
$optionsCidade = selecionaOptionsCidades($lot, $mun);


// Fim da tabela de cidades



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Tabelas » Feriados » Alterar");
$oForm->setJSSelect2();
$oForm->setSeparador(15);
$oForm->setJS('alteraferiado.js?time='.rand(3222, 99999));

$oForm->setSubTitulo("Manutenção da Tabela de Feriados");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script> var nDiasDoMes = "<?= numero_dias_do_mes($mes, date('Y')); ?>";</script>

<div class="container">

    <div class="row align-vertical">
        <div class="col-md-8 col-md-offset-1">

            <form id="form1" name="form1" class="form-horizontal" method="POST" action="#" onsubmit="return false;">
                <input type="hidden" name="id"   id="id"   value="<?= tratarHTML($id); ?>">
                <input type="hidden" name="modo" id="modo" value="2">
                <input type="hidden" name="uf_lota" id="uf_lota" value="<?= tratarHTML($uf_lota); ?>">

                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="id" class="control-label">ID</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='id' name="id" class="form-control" value="<?= tratarHTML($id); ?>" size="4" maxlength="4" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="dia" class="control-label">Dia</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" id='dia' name="dia" class="form-control" value="<?= tratarHTML($dia); ?>" size="4" maxlength="2" required="required">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="mes" class="control-label">Mês</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" id='mes' name="mes" class="form-control" value="<?= tratarHTML($mes); ?>" size="4" maxlength="2" required="required">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="sDescricao" class="control-label">Descrição&nbsp;do&nbsp;Feriado</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='sDescricao' name="sDescricao" class="form-control" value="<?= tratarHTML($des); ?>" size="80" maxlength="80" required="required" style='width:450px;'>
                    </div>
                </div>
    
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="lot" class="control-label">Tipo</label>
                    </div>
                    <div class="col-md-6">
                        <select id="tipo" name="tipo" class="form-control select2-single">
                            <option value="N"<?= ($tipo == "N" ? " selected" : ''); ?>>Nacional</option>
                            <option value="E"<?= ($tipo == "E" ? " selected" : ''); ?>>Estadual</option>
                            <option value="M"<?= ($tipo == "M" ? " selected" : ''); ?>>Municipal</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="lot" class="control-label">UF</label>
                    </div>
                    <div class="col-md-6">
                        <select id="lot" name="lot" class="form-control select2-single">
                            <?= $optionsUf; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="codmun" class="control-label">Município</label>
                    </div>
                    <div class="col-md-6">
                        <select id="codmun" name="codmun" class="form-control select2-single">
                            <?= $optionsCidade; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="flegal" class="control-label">Fundamento Legal</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='flegal' name="flegal" class="form-control" value="<?= tratarHTML($flegal); ?>" size="30" maxlength="30" required="required" style='width:400px;'>

                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-4 col-xs-6 col-md-offset-3">
                        <button type="submit" id="btn-enviar" name="btn-enviar" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> Gravar
                        </button>
                    </div>
                    <div class="col-md-4 col-xs-6">
                        <a class="no-style" href="tabferiados.php" style="text-decoration:none;">
                            <button type="button" class="btn btn-danger btn-block">
                                <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                            </button>
                        </a>
                    </div>
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


/**
 *
 * @param integer $id Número da chave de registro
 * @return result
 */
function selecionaDadosDoFeriado( $id )
{
    $oDBase = new DataBase();

    $oDBase->setMensagem("Problemas no acesso a Tabela FERIADOS (E000106.".__LINE__.").");
    $oDBase->query("
    SELECT dia, mes, `desc`, tipo, lot, codmun, data_feriado, base_legal
        FROM feriados
            WHERE `id` = :id
    ",
    array(
        array( ':id', $id, PDO::PARAM_STR ),
    ));

    return $oDBase->fetch_object();
}


/**
 *
 * @param string $lotacao Código da unidade
 * @return result
 */
function selecionaDadosDaUnidade( $lotacao )
{
    $oDBase = new DataBase();

    $oDBase->setMensagem("Problemas no acesso a Tabela FERIADOS (E000107.".__LINE__.").");
    $oDBase->query("
    SELECT codigo, descricao, regional, uf_lota
        FROM tabsetor
            WHERE codigo = :unidade
    ",
    array(
        array( ':unidade', $lotacao, PDO::PARAM_STR ),
    ));

    return $oDBase->fetch_object();
}

/**
 *
 * @param string $regional
 *
 * @return string
 */
function selecionaOptionsLotacao( $regional )
{
    $oDBase = new DataBase();

    $oDBase->setMensagem("Problemas no acesso a Tabela FERIADOS (E000108.".__LINE__.").");
    $oDBase->query("
    SELECT codigo, descricao
        FROM tabsetor
            WHERE regional = :regional
                  OR codigo != '00000000'
                    ORDER BY codigo
    ",
    array(
        array( ':regional', $regional, PDO::PARAM_STR ),
    ));

    while ($campo = $oDBase->fetch_object())
    {
        $optionsLotacao .= "<option value='"
            . $campo->codigo . "'"
            . ($campo->codigo == $regional ? " selected" : "")
            . ">" . $campo->codigo . " - " . substr($campo->descricao, 0, 20)
            . "</option>";
    }

    return $optionsLotacao;
}


/**
 * 
 * @param string $uf
 * @return array
 */
function selecionarOptionsUf($uf)
{
    $ufs =  array(
        'AC'=>'Acre',
        'AL'=>'Alagoas',
        'AP'=>'Amapá',
        'AM'=>'Amazonas',
        'BA'=>'Bahia',
        'CE'=>'Ceará',
        'DF'=>'Distrito Federal',
        'ES'=>'Espírito Santo',
        'GO'=>'Goiás',
        'MA'=>'Maranhão',
        'MT'=>'Mato Grosso',
        'MS'=>'Mato Grosso do Sul',
        'MG'=>'Minas Gerais',
        'PA'=>'Pará',
        'PB'=>'Paraíba',
        'PR'=>'Paraná',
        'PE'=>'Pernambuco',
        'PI'=>'Piauí',
        'RJ'=>'Rio de Janeiro',
        'RN'=>'Rio Grande do Norte',
        'RS'=>'Rio Grande do Sul',
        'RO'=>'Rondônia',
        'RR'=>'Roraima',
        'SC'=>'Santa Catarina',
        'SP'=>'São Paulo',
        'SE'=>'Sergipe',
        'TO'=>'Tocantins'
    );

    $options = "<option value=''>Selecione uma uf</option>";
    foreach($ufs as $sigla => $nome){
        $options .= "<option value='{$sigla}' ".($uf == $sigla ? 'selected' : '')." >{$sigla} - {$nome}</option>";
    }

    return $options;
}

/**
 * 
 * @param type $uf
 * @param type $mun
 */
function selecionaOptionsCidades($uf, $mun)
{
    $oDBase = new DataBase();
    $oDBase->query("SELECT * FROM cidades WHERE uf = :uf ORDER BY nome ASC", array(array(':uf', $uf, PDO::PARAM_STR )));
    
    $options = "<option value=''>Selecione</option>";

    while ($campo = $oDBase->fetch_object()){
        $options .= "<option value='{$campo->numero}' ". ($campo->numero == $mun ? " selected" : "") . ">" . $campo->numero . " - " . substr($campo->nome, 0, 20) . "</option>";
    }

    return $options;
}