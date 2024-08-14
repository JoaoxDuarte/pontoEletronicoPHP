<?php
// conexao ao banco de dados
// funcoes diversas
include_once("config.php");

// formulario padrao para entrada
include_once("class_form.entrada.php");

// class formulario
include_once("class_form.frequencia.php");

verifica_permissao('logado', 'entrada.php');

//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por n�o sabermos, de antem�o, os IPs da aplica��o
include_once('ilegal_entrada.php');

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


// Seleciona registro do dia do usu�rio
$oDBase = selecionaRegistroFrequenciaDoDia($sMatricula, $vDatas);

$oPonto = $oDBase->fetch_object();

$ent    = $oPonto->entra;
$iniint = $oPonto->intini;
$fimint = $oPonto->intsai;
$sai    = $oPonto->sai;


## Verifica se h� registro de:
#  - Frequ�ncia realizado neste dia;
#  - Hor�rio de fim do expediente j� realizado
#
VerificaRegistrosHorariosFrequenciaServidor(
    $sMatricula,     /* Matr�cula do Servidor Logado         */
    $vDatas,         /* Data atual de registro da frequ�ncia */
    'retorno_almoco' /* Momento do registro                  */
);


// horas trabalhadas
$horas_trabalhadas = horas_trabalhadas_ate_o_momento($sMatricula, $hoje);

if (empty($horas_trabalhadas))
{
    $horas_trabalhadas = ' - ';
}


## Verifica Hor�rio de ver�o e o Fuso Hor�rio
#  - Atribui o hor�rio da entrada a $vHoras, ap�s as verifica��es
#
$vHoras = horario_de_verao($vDatas);


// limite de horario de entrada e saida do �rg�o
$limites_inss = horariosLimiteINSS();


// verifica se o hor�rio de entrada � menor que limite de entrada definido
if (time_to_sec($vHoras) <= time_to_sec($limites_inss['entrada']['horario']))
{
    $vHoras = $limites_inss['entrada']['horario'] . ':00';
}
// fim da definicao da hora


// limita a sa�da �s 22:00:00
if ((time_to_sec($vHoras) >= time_to_sec($limites_inss['saida']['horario'])) && (liberado_registro_apos_22hs($sMatricula) != 'SIM'))
{
    $vHoras = $limites_inss['saida']['horario'] . ':00';
}


// verifica se hor�rio de retorno do almo�o
// n�o � menor que a entrada (in�cio expediente)
// ou que a sa�da para o almo�o
if (time_to_sec($ent) > time_to_sec($vHoras))
{
    retornaErro(
        'entrada.php',
        "Hor�rio de retorno do almo�o menor que Entrada (" . left($ent,5) . ")!"
    );
}
else if (time_to_sec($iniint) == 0)
{
    retornaErro(
        'entrada.php',
        "N�o � permitido registrar retorno do intervalo com in�cio do intervalo em branco!"
    );
}
else if (time_to_sec($iniint) > time_to_sec($vHoras))
{
    retornaErro(
        'entrada.php',
        "Hor�rio de retorno do almo�o menor que Sa�da para o almo�o (" . $iniint . ")!"
    );
}
// fim da definicao da hora


/* Verifica se o horario registrado e menor ou maior que o da tabela de setores */
if ((time_to_sec($vHoras) > time_to_sec($fim)) && ($aut == "N"))
{
    $idsaida = 1;
}
elseif ((time_to_sec($vHoras) > time_to_sec($fim)) && ($aut == "S"))
{
    $idh = 1;
}
else
{
    $idh = 2;
}

/* Define como encerramento do  intervalo o hor�rio de encerramento de atividades no setor */
if ($idsaida == 1)
{
    $vHoras = $fim;
}

// calculo das horas do intervalo em servativ
$ibanco = diferencaHoras($iniin, $fimin);

// calculo das horas registradas pelo servidor no sisref
$int = @substr($iniint, 0, 5);
$iform = diferencaHoras($int, $vHoras);

// Verifica o intervalo e permite o registro faltando quinze minutos
if (time_to_sec($iform) < time_to_sec("00:45"))
{
    retornaErro('entrada.php', "N�o � permitido registrar intervalo inferior a uma hora!");
}
elseif (time_to_sec($iform) >= time_to_sec("00:45") && time_to_sec($iform) < time_to_sec("01:00"))
{
    $vHoras = adicionaHoras($int, '01:00') . substr($iniint, 5, 3);
}
/* fim da verifica�ao se o intervalo � valido */

$ip = getIpReal(); //linha que captura o ip do usuario.

// elimina "/" e ":", depois define o tipo como inteiro
// para garantir a resultado do teste a seguir
$nvDatas = sonumeros($vDatas);
settype($nvDatas, 'integer');

if ($nvDatas != 0 && time_to_sec($vHoras) != 0)
{
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Falha no registro do ponto!");
    $oDBase->setDestino("entrada.php");
    $oDBase->query("
    UPDATE
        ponto$comp
    SET
        intsai = :vhoras,
        ip3    = :ip
    WHERE
        siape = :siape
        AND dia = :dia
    ",
    array(
        array( ':vhoras', $vHoras,     PDO::PARAM_STR ),
        array( ':ip',     $ip,         PDO::PARAM_STR ),
        array( ':siape',  $sMatricula, PDO::PARAM_STR ),
        array( ':dia',    $vDatas,     PDO::PARAM_STR ),
    ));

    // grava o LOG
    registraLog("Registrou fim intervalo (Almo�o)");

    $mensagemUsuario = "Registrou fim intervalo (Almo�o)";
    //setMensagemUsuario( "Registrou fim intervalo (Almo�o)", 'success' );
    //$mensagemUsuario = $_SESSION['mensagem-usuario'];
}
else
{
    retornaErro('entrada.php', 'Erro de processamento. Por favor, repita a opera��o!');
}

// dados da unidade
$oDBase->query("
SELECT
    und.descricao, taborgao.denominacao, taborgao.sigla
FROM
    tabsetor AS und
LEFT JOIN
    taborgao ON LEFT(und.codigo,5) = taborgao.codigo
WHERE
    und.codigo = :codigo
",
array(
    array( ':codigo', $sLotacao, PDO::PARAM_STR ),
));
$oSetor            = $oDBase->fetch_object();
$lotacao_descricao = $oSetor->descricao;    // descri��o do c�digo da unidade
$orgao_descricao   = $oSetor->denominacao;  // descri��o do c�digo do �rg�o
$orgao_sigla       = $oSetor->sigla;        // sigla do �rg�o


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
    <!-- Mensagem para o Usu�rio -->
    <div class="row">
        <?php
        if (!empty($mensagemUsuario))
        {
            echo getMensagemErroHTML($mensagemUsuario, 'info');
        }
        ?>
    </div>
    <!-- Linha referente aos hor�rios -->
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
    <!-- Row Referente aos dados dos funcion�rios  -->
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
                            <th class="text-center" style='vertical-align:middle;'>�RG�O</th>
                            <th class="text-center" style='vertical-align:middle;'>UNIDADE</th>
                            <th class="text-center" style='vertical-align:middle;'>COMPENSA��O</th>
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
                                    <strong><?= ($bhoras != "S" ? "N�O " : "AUTORIZADA"); ?></strong>
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
                <h4>Hor�rios do servidor - <?= tratarHTML($hoje); ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <h5><strong>Entrada</strong></h5>
            <p><?= tratarHTML($ent) . 'h'; ?></p>
        </div>
        <div class="col-md-6">
            <h5><strong>Intervalo</strong></h5>
            <p><?= tratarHTML($iniint) . 'h as ' . tratarHTML($vHoras) . 'h '; ?></p>
        </div>
        <div class="col-md-3">
            <h5><strong>Sa�da</strong></h5>
            <p><?= '00:00:00h'; ?></p>
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
 *                   FUN��ES COMPLEMENTARES                   *
 *                                                            *
 ************************************************************ */

/* @info Seleciona o registro do dia
 *
 * @param string $siape Matr�cula SIAPE do servidor
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

    // recupera o hor�rio de entrada
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

/* @info Atualiza o registro do dia, com o hor�rio
 *       de in�cio do almo�o
 *
 * @param string $siape   Matr�cula SIAPE do servidor
 * @param string $dia     Data de hoje
 * @param string $horario Hor�rio do registro
 * @param string $ip      IP da m�quina de registro
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
        $mensagemUsuario = "Registrou in�cio intervalo (Almo�o)";
        registraLog( $mensagemUsuario ); // grava o LOG
    }
    else
    {
        // grava o LOG
        registraLog( "Erro de processamento no Registro do in�cio intervalo (Almo�o)" );
        retornaErro(
            'entrada.php',
            'Erro de processamento.<br>Por favor, repita a opera��o!'
        );
    }


    return $mensagemUsuario;
}
