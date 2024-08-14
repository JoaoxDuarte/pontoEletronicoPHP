<?php
include_once("config.php");

// verifica se o usuário tem permissão para acessar este módulo
verifica_permissao('sRH ou sTabServidor');

$lotusu = $_SESSION["sLotacao"];

// instancia banco de dados
$oDBase = new DataBase('PDO');

// monta select - lotação
// tabela de lotacao
$oSetor   = selecionaDadosDaUnidade( $lot );
$regional = $oSetor->regional;

$optionsUfs = selecionarOptionsUf();

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Tabelas » Feriados » Alterar");
$oForm->setJSSelect2();
$oForm->setSeparador(15);
$oForm->setJS('criarferiado.js?time='.date('YmdHis'));

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

            <form id="form1" name="form1" class="form-horizontal" action="#" onsubmit="return false;">
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="dia" class="control-label">Dia</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" id='dia' name="dia" class="form-control" value="" size="4" maxlength="2" required="required">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="mes" class="control-label">Mês</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" id='mes' name="mes" class="form-control" value="" size="4" maxlength="2" required="required">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="desc" class="control-label">Descrição&nbsp;do&nbsp;Feriado</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='desc' name="desc" class="form-control" value="" size="80" maxlength="80" required="required" style='width:450px;'>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="tipo" class="control-label">Tipo</label>
                    </div>
                    <div class="col-md-6">
                        <select id="tipo" name="tipo" class="form-control select2-single">
                            <option value="N">Nacional</option>
                            <option value="E">Estadual</option>
                            <option value="M">Municipal</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="lot" class="control-label">UF</label>
                    </div>
                    <div class="col-md-4">
                        <select id="lot" name="lot" class="form-control select2-single" disabled>
                            <?= $optionsUfs; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="codmun" class="control-label">Município</label>
                    </div>
                    <div class="col-md-6">
                        <select id="codmun" name="codmun" class="form-control select2-single" disabled></select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="base_legal" class="control-label">Fundamento Legal</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='base_legal' name="base_legal" class="form-control" value="" size="30" maxlength="30" required="required" style='width:400px;'>
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
 * @param string $lotacao Código da unidade
 * @return result
 */
function selecionaDadosDaUnidade( $lotacao )
{
    $oDBase = new DataBase();
    
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
 * @param type $mun
 */
function selecionaOptionsCidades( $mun )
{
    $oDBase = new DataBase();
    
    $oDBase->query("SELECT numero, nome FROM cidades ORDER BY nome ");

    while ($campo = $oDBase->fetch_object())
    {
        $optionsCidade .= "<option value='" 
            . $campo->numero . "'" 
            . ($campo->numero == $mun ? " selected" : "") 
            . ">" . $campo->numero . " - " . substr($campo->nome, 0, 20) 
            . "</option>";
    }

    return $optionsCidade;
}


/**
 * 
 * @return array
 */
function selecionarOptionsUf()
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

    $options = "<option value='' selected>Selecione uma uf</option>";
    foreach($ufs as $sigla => $nome){
        $options .= "<option value={$sigla}>{$sigla} - {$nome}</option>";
    }

    return $options;
}

