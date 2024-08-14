<?php
include_once("config.php");

// verifica se o usuário tem permissão para acessar este módulo
verifica_permissao('sRH ou sTabServidor');

$lotusu = $_SESSION["sLotacao"];

// instancia banco de dados
$oDBase = new DataBase('PDO');

// Select de ufs
$optionsUf = selecionarOptionsUf();

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Tabelas » Feriados » Adicionar");
$oForm->setJSSelect2();
$oForm->setSeparador(15);
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setJS('ponto_facultativo_cadastrar.js?time='.date('YmdHis'));

$oForm->setSubTitulo("Manutenção da Tabela de Ponto Facultativo");

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
                        <label for="desc" class="control-label">Descrição</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='desc' name="desc" class="form-control" value="" size="80" maxlength="80" required="required" style='width:450px;'>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="desc" class="control-label">Observação</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='descricao' name="descricao" class="form-control" value="" size="100" maxlength="100" required="required" style='width:450px;'>
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
                    <div class="col-md-6">
                        <select id="lot" name="lot" class="form-control select2-single" disabled>
                            <?= $optionsUf; ?>
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
                        <label for="data_feriado" class="control-label">Data do Feriado</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='data_feriado' name="data_feriado" class="form-control maskData" value="" size="10" maxlength="10" required="required" style='width:120px;'>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="data_feriado" class="control-label">Carga Horária</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='carga_horaria' name="carga_horaria" class="form-control maskHora" value="" size="5" maxlength="5" required="required" style='width:100px;'>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="hora_inicio" class="control-label">Hora Início</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='hora_inicio' name="hora_inicio" class="form-control maskHora" value="" size="5" maxlength="5" required="required" style='width:100px;'>
                    </div>
                </div>                
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="hora_termino" class="control-label">Hora Término</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='hora_termino' name="hora_termino" class="form-control maskHora" value="" size="5" maxlength="5" required="required" style='width:100px;'>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="grupo" class="control-label">Grupo</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='grupo' name="grupo" class="form-control" value="" size="20" maxlength="20" required="required" style='width:200px;'>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-2">
                        <label for="sigla" class="control-label">Sigla</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id='sigla' name="sigla" class="form-control" value="" size="1" maxlength="1" required="required" style='width:50px;'>
                    </div>
                </div>


                
                <div class="form-group">
                    <div class="col-md-4 col-xs-6 col-md-offset-3">
                        <button type="submit" id="btn-enviar" name="btn-enviar" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> Gravar
                        </button>
                    </div>
                    <div class="col-md-4 col-xs-6">
                        <a class="no-style" href="ponto_facultativo.php" style="text-decoration:none;">
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

