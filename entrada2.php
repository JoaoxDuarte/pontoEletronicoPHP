<?php

// conexao ao banco de dados
// funcoes diversas
include_once("config.php");

// formulario padrao para entrada
include_once("class_form.entrada.php");

// class formulario
include_once("class_form.frequencia.php");

//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
include_once('ilegal_entrada.php');

verifica_permissao('logado', 'entrada.php');

$sLotacao   = $_SESSION['sLotacao'];
$sMatricula = $_SESSION['sMatricula'];
$ini        = $_SESSION['ini'];
$fim        = $_SESSION['fim'];
$sNome      = $_SESSION['sNome'];
$entra      = $_SESSION['entra'];
$sai        = $_SESSION['sai'];
$iniin      = $_SESSION['iniin'];
$fimin      = $_SESSION['fimin'];
$aut        = $_SESSION['aut'];

$iniver = $_SESSION['iniver'];
$fimver = $_SESSION['fimver'];

$bhoras = $_SESSION['bhoras'];


//define a competencia da tabela
$comp = date('mY');

$vDatas = date("Y-m-d");
$hoje   = date("d/m/Y");


// Seleciona registro do dia do usuário
$oDBase = selecionaRegistroFrequenciaDoDia($sMatricula, $vDatas);

$oPonto = $oDBase->fetch_object();
$ent    = $oPonto->entra;
$iniint = $oPonto->intini;
$fimint = $oPonto->intsai;
$sai    = $oPonto->sai;


## Verifica se há registro de:
#  - Frequência realizado neste dia;
#  - Horário de fim do expediente já realizado
#
VerificaRegistrosHorariosFrequenciaServidor(
    $sMatricula,     /* Matrícula do Servidor Logado         */
    $vDatas,         /* Data atual de registro da frequência */
    'saida_almoco' /* Momento do registro                  */
);


// horas trabalhadas
$horas_trabalhadas = horas_trabalhadas_ate_o_momento($sMatricula, $hoje);

if (empty($horas_trabalhadas))
{
    $horas_trabalhadas = ' - ';
}


## Verifica Horário de verão e o Fuso Horário
#  - Atribui o horário da entrada a $vHoras, após as verificações
#
$vHoras = horario_de_verao($vDatas);


// limite de horario de entrada e saida do Órgão
$limites_inss = horariosLimiteINSS();


// verifica se o horário de entrada é menor que limite de entrada definido
if (time_to_sec($vHoras) <= time_to_sec($limites_inss['entrada']['horario']))
{
    $vHoras = $limites_inss['entrada']['horario'] . ':00';
}
// fim da definicao da hora


// limita a saída às 22:00:00
if ((time_to_sec($vHoras) >= time_to_sec($limites_inss['saida']['horario'])) && (liberado_registro_apos_22hs($sMatricula) != 'SIM'))
{
    $vHoras = $limites_inss['saida']['horario'] . ':00';
}


// verifica se horário de retorno do almoço
// não é menor que a entrada (início expediente)
// ou que a saída para o almoço
if (time_to_sec($ent) > time_to_sec($vHoras))
{
    retornaErro(
        'entrada.php',
        "Horário de saída para almoço menor que Entrada (" . left($ent,5) . ")!"
    );
}


//pegando o ip do usuario
$ip = getIpReal(); //linha que captura o ip do usuario.


// elimina "/" e ":", depois define o tipo como inteiro
// para garantir a resultado do teste a seguir
$nvDatas = (int) sonumeros($vDatas);
$nvHoras = (int) time_to_sec($vHoras);

if ($nvDatas != 0 && $nvHoras != 0)
{
    $mensagemUsuario = atualizaRegistroFrequenciaInicioAlmoco(
        $sMatricula,
        $vDatas,
        $vHoras,
        $ip
    );
}
else
{
    retornaErro('entrada.php', 'Erro de processamento. Por favor, repita a operação!');
}

$title = _SISTEMA_SIGLA_ . ' | Registro de Comparecimento';

$css = array();

$javascript = array();
//$javascript[] = 'principal.js';

include("html/html-base.php");
include("html/header.php");
?>
<script>
    $(document).ready(function ()
    {
        iniciar_relogio();
    });
</script>

<div class="container">
    <!-- Mensagem para o Usuário -->
    <div class="row">
        <?php
        if (!empty($mensagemUsuario))
        {
            echo getMensagemErroHTML($mensagemUsuario, 'info');
        }
        ?>
    </div>
    <!-- Linha referente aos horários -->
    <div class="row">
        <div class="col-md-6 hora-atual">
            <?php
            include_once( _DIR_INC_ . 'relogio.php' );
            ?>
        </div>
        <div class="col-md-6 hora-atual">
            <h4>
                <strong>
                    <span class="uppercase">Horas Trabalhadas</span>
                </strong>
            </h4>
            <span class="hora">
                <span class="glyphicon glyphicon-time"></span>
                <?= tratarHTML($horas_trabalhadas); ?>
            </span>
        </div>
    </div>
    <!-- Row Referente aos dados dos funcionários  -->
    <div class="row margin-10">
        <div class="subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Meus Dados</strong></h4>
        </div>

        <div class="col-md-12">
            <div class="row">
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th class="text-center text-nowrap" style='vertical-align:middle;'>Mat. SIAPE</th>
                            <th class="text-center" style='vertical-align:middle;'>NOME</th>
                            <th class="text-center" style='vertical-align:middle;'>ÓRGÃO</th>
                            <th class="text-center" style='vertical-align:middle;'>UNIDADE</th>
                            <th class="text-center" style='vertical-align:middle;'>COMPENSAÇÃO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><h4><?= tratarHTML(removeOrgaoMatricula($sMatricula)); ?></h4></td>
                            <td class="text-left col-xs-4"><h4><?= tratarHTML($sNome); ?></h4></td>
                            <td class="text-center col-xs-2 text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $sLotacao )); ?></h4></td>
                            <td class="text-left col-xs-4"><h4><?= tratarHTML(getUorgMaisDescricao( $sLotacao )); ?></h4></td>
                            <td class="text-center col-xs-2 text-nowrap" style='color:red;'>
                                <h4>
                                    <strong><?= ($bhoras != "S" ? "NÃO " : "AUTORIZADA"); ?></strong>
                                </h4>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- Row Referente aos dados Setor do funcionario  -->
    <div class="row margin-10">
        <div class="subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Dados do seu Setor</strong></h4>
        </div>
        <div class="col-md-12" id="dados-setor">
            <div class="col-md-3">
                <h5>
                    <strong>Horario do Setor<?= ($sTurnoEstendido == 'S' ? ' - Unidade com Turno Estendido' : ''); ?></strong>
                </h5>
                <p>
                    <strong><?= tratarHTML($ini); ?></strong> as <strong><?= tratarHTML($fim); ?></strong>
                </p>
            </div>
            <div class="col-md-3">
                <h5><strong>Hora de Entrada</strong></h5>
                <p><?= tratarHTML($entra); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>Intervalo</strong></h5>
                <p><?= tratarHTML($iniin); ?> as <?= tratarHTML($fimin); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>Hora de Saida</strong></h5>
                <p><?= tratarHTML($sai); ?></p>
            </div>
        </div>
    </div>
    <!-- Row referente a Comparecimento-->
    <div class="row comparecimento">
        <h3>Registro de comparecimento</h3>
    </div>
    <!-- -->
    <div class="row" id="registros">
        <div class="col-md-12">
            <div class="col-md-6 col-md-offset-3">
                <h4>Horários do servidor - <?= tratarHTML($hoje); ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <h5><strong>Entrada</strong></h5>
            <p><?= tratarHTML($ent); ?>h</p>
        </div>
        <div class="col-md-6">
            <h5><strong>Intervalo</strong></h5>
            <p><?= tratarHTML($vHoras . 'h as 00:00:00h '); ?></p>
        </div>
        <div class="col-md-3">
            <h5><strong>Saída</strong></h5>
            <p><?= tratarHTML('00:00:00h'); ?></p>
        </div>
    </div>

    <!--
    <div class="row">
        <div class="col-md-12 col-md-offset-1 margin-bottom-5 margin-bottom-15 text-center">
            <div class="form-group col-md-2 col-md-offset-4" style="padding-left:20px;">
                <a class="btn btn-success btn-block" href="entrada.php" role="button">
                    <span class="glyphicon glyphicon-log-out"></span> Encerrar
                </a>
            </div>
        </div>
    </div>
    -->

</div>

<?php
include("html/footer.php");

DataBase::fechaConexao();

// elimina resquicios da sessao anterior
destroi_sessao();

exit();



/* ************************************************************
 *                                                            *
 *                   FUNÇÕES COMPLEMENTARES                   *
 *                                                            *
 ************************************************************ */

/* @info Seleciona o registro do dia
 *
 * @param string $siape Matrícula SIAPE do servidor
 * @param string $dia   Data de hoje
 *
 * @return result Dados selecionados
 *
 * @author Edinalvo Rosa
 */
function selecionaRegistroFrequenciaDoDia($siape, $dia)
{
    $comp = dataMes($dia) . dataAno($dia);

    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Falha no registro do ponto!");
    $oDBase->setDestino("entrada.php");

    // recupera o horário de entrada
    $oDBase->query("
    SELECT entra, intini, intsai, sai
        FROM ponto$comp
            WHERE siape = :siape
                  AND dia = :dia
    ",
    array(
        array( ':siape', $siape, PDO::PARAM_STR ),
        array( ':dia',   $dia,   PDO::PARAM_STR ),
    ));

    return $oDBase;
}

/* @info Atualiza o registro do dia, com o horário
 *       de início do almoço
 *
 * @param string $siape   Matrícula SIAPE do servidor
 * @param string $dia     Data de hoje
 * @param string $horario Horário do registro
 * @param string $ip      IP da máquina de registro
 *
 * @return string Mensagem
 *
 * @author Edinalvo Rosa
 */
function atualizaRegistroFrequenciaInicioAlmoco($siape, $dia, $horario, $ip)
{
    $comp = dataMes($dia) . dataAno($dia);

    $oDBase = new DataBase();

    $oDBase->query("
    UPDATE
        ponto$comp
    SET
        intini = :horario,
        ip2    = :ip
    WHERE
        siape = :siape
        AND dia = :dia
    ",
    array(
        array( ':horario', $horario, PDO::PARAM_STR ),
        array( ':ip',      $ip,      PDO::PARAM_STR ),
        array( ':siape',   $siape,   PDO::PARAM_STR ),
        array( ':dia',     $dia,     PDO::PARAM_STR ),
    ));

    if ($oDBase->affected_rows() > 0)
    {
        $mensagemUsuario = "Registrou início intervalo (Almoço)";
        registraLog( $mensagemUsuario ); // grava o LOG
    }
    else
    {
        // grava o LOG
        registraLog( "Erro de processamento no Registro do início intervalo (Almoço)" );
        retornaErro(
            'entrada.php',
            'Erro de processamento.<br>Por favor, repita a operação!'
        );
    }


    return $mensagemUsuario;
}
