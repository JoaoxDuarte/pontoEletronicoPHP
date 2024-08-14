<?php
/* _________________________________________________________________________*\
  |                                                                           |
  |   AUTENTICA O USU�RIO E GRAVA O PRIMEIRO REGISTRO DE FREQUENCIA DO DIA    |
  |                                                                           |
  \*������������������������������������������������������������������������� */

// conexao ao banco de dados, funcoes diversas
include_once( 'config.php' );
include_once("class_ocorrencias_grupos.php");
include_once("src/controllers/TabServativController.php");
include_once( "src/controllers/TabBancoDeHorasAcumulosController.php" );


if(!empty($_POST['opcao'])){

    if($_POST['opcao']=='nome_original'){
        $nome_social = FALSE;
    }else{
        $nome_social = TRUE;
    }
    $variavel['matricula'] = anti_injection($_POST['matricula']);
    $variavel['flag_nome'] = $nome_social;

    updateServativ($variavel);

    if ($nome_social === TRUE)
    {
        $nome = getNomeSocialServidor($variavel['matricula']);
    }

    if (empty($nome))
    {
        $nome = getNomeServidor($variavel['matricula'], $com_siape=false);
    }

    //retornaAlteraUsuario('entrada.php', 'Nome de exibi��o foi alterado com sucesso! � necess�rio logar novamente!');
    echo $nome;
    exit();
}


## Instancia classes
#  - Banco de Horas
#
$tabBancoDeHorasAcumulosController = new TabBancoDeHorasAcumulosController();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo('Registro de Comparecimento');
$oForm->setJS("js/jquery.hotkeys-0.7.9.min.js?v.0.0.0.31");
$oForm->setJS('entrada1.js?v.0.0.3');
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();

if($_SESSION['SIGAC_LOGIN']) {

    $cpf = $_SESSION['SIGAC_CPF_SERVIDOR'];
    $result_sigac = existsServerByCpf($cpf);

    if($result_sigac){
        $_REQUEST['lSiape'] = $cpf;
        $_REQUEST['txtImagem'] = $_SESSION['autenticaIMG'];
        $_REQUEST['enviar'] = '';
    }
}


$ja_esta_logado = false;
if ($_SESSION['sNome'] && $_SESSION['sNome'] != "")
    $ja_esta_logado = true;

// dados enviados por formulario
$sCPF = limpaCPF_CNPJ(anti_injection($_REQUEST['lSiape']));


// CASO O LOGIN SEJA VIA SIGAC
if(!$_SESSION['SIGAC_LOGIN']) {
    $sSenha = anti_injection($_REQUEST['lSenha']);
    $sSenha = substr(md5($sSenha), 0, 14);
} else {
    $sSenha = $result_sigac->senha;
}

$formSenha  = $sSenha;
$txtImagem  = strtoupper($_POST['txtImagem']);

// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
// Comentado temporariamente por n�o sabermos, de antem�o, os IPs da aplica��o
include_once('ilegal_entrada.php');

// Texto digitado no campo imagem, e transformando em m�n�sculo.
// - Para haver distin��o entre mai�sculas e min�sculas, retire o
//   strtoupper().
$txtImagem = strtoupper($_POST['txtImagem']);

// Caracteres que est�o na imagem,
// tamb�m deixando tudo em min�sculo.
$valorImagem = strtoupper($_SESSION['autenticaIMG']);

// verificando se existe o usu�rio e senha informado
$result   = null;
$numrows  = 0;

$oUsuario = autenticacao_do_usuario($sCPF, $sSenha);

if (is_object($oUsuario))
{
    // Le dados do usu�rio
    include_once( 'entrada1_dados.le.php' );

    // informar tipo da origem: rh.php, chefia.php ou entrada.php
    $_SESSION['sHOrigem_1'] = 'entrada.php';

    // cria a se��o do usuario e identifica suas permiss�es

    //CONCAT NA MATRICULA, PARA UNILA AO �RG�O
    $sMatricula = $oUsuario->siape;
    $defvis     = $oUsuario->defvis;

    $_SESSION['sMatricula'] = $sMatricula;
    $_SESSION['sNome']      = $sNome;
    $_SESSION['upag']       = $upag;
}

##
#  Verifica se o usu�rio existe, se o texto digitado eh igual aos caracteres
#  que est�o na imagem, e, se o servidor/estagi�rio N�O estiver indicado no
#  cadastro como deficiente visual compara o texto digitado com a imagem
##
// For�ando o captcha ser v�lido
//$txtImagem = $valorImagem;

if (empty($numrows) && !$ja_esta_logado)
{
    retornaErro('entrada.php', 'Usu�rio inv�lido!');
    exit();
}

if (checkIpAccess() == false)
{
    retornaErro('entrada.php', 'Voc� n�o se encontra na faixa de IP autorizada.');
    exit();
}

if ($tabBancoDeHorasAcumulosController->verifyExistsAutorization($sMatricula))
{
    retornaErro('entrada.php', 'Voc� possu� usufruto total de horas autorizado para o dia de hoje, portanto n�o pode registrar o ponto.');
    exit();
}

if ($formSenha !== $oUsuario->senha && !$ja_esta_logado)
{
    retornaErro('entrada.php', 'Senha inv�lida!');
    exit();
}

if ($defvis != 'S' && !$ja_esta_logado && ($txtImagem != $valorImagem || empty($txtImagem)))
{
    retornaErro('entrada.php', 'Captcha inv�lido!');
    exit();
}


$oDBase3 = new DataBase('PDO');
$oDBase3->query("SELECT servativ.cod_lot, servativ.flag_nome_social, servativ.nome_social, servativ.nome_serv FROM servativ WHERE mat_siape = $sMatricula");

$result = $oDBase3->fetch_object();

$lotReal    = $result->cod_lot;
$nomeReal   = $result->nome_serv;
$nomeSocial = $result->nome_social;
$nome       = "";

if ($result->flag_nome_social === TRUE)
{
    $nome = $nomeSocial;
}

if (empty($nome))
{
    $nome = $nomeReal;
}


// ATUALIZA INFORMA��ES DO SERVIDOR DE ACORDO COM O RETORNO DA API DO SIAPE
// SE FOR O PRIMEIRO LOGIN DO DIA PARA REGISTRO DE FREQU�NCIA
if (naoRegistrouEntradaAntes($sMatricula) == true)
{
    if (updateServerBySiape($sMatricula))
    {
        $_SESSION['ano_inicial'] = date('Y');
        $_SESSION['mes_inicial']= date('m');
        $_SESSION['ano_final']= date('Y');
        $_SESSION['mes_final']= date('m');

        updateAfastamentosBySiape($sMatricula);
    }
}


##
#  Verifica situa��o cadastral do usuario, se cedido ou fixado est�
#  dispensado de registrar a frequ�ncia no �rg�o cedente.
#  - Sua frequ�ncia ser� informada pelo �rg�o requisitante em que estiver
#    desempenhando suas fun��es.
##

//entrada1_verifica_situacao($sitcad);

$sigac                    = $_SESSION['SIGAC_LOGIN'];
$tempolimite              = $_SESSION['tempolimite'];
$sModuloPrincipalAcionado = $_SESSION['sModuloPrincipalAcionado'];

// elimina resquicios da sessao anterior
session_unset();

// informar tipo da origem: rh.php, chefia.php ou entrada.php
$_SESSION['sHOrigem_1']               = 'entrada.php';
$_SESSION['sModuloPrincipalAcionado'] = (empty($sModuloPrincipalAcionado) ? 'entrada' : $sModuloPrincipalAcionado);
$_SESSION['tempolimite']              = $tempolimite;


// cria a se��o do usuario e identifica suas permiss�es
$_SESSION['sMatricula']            = $sMatricula;
$_SESSION['sNome']                 = $nomeReal;
$_SESSION['sIdentificacaoApelido'] = $identificacao_apelido; // identificacao ou apelido
$_SESSION['sSenha']                = $sSenha;
$_SESSION['sLotacao']              = $lotReal;
$_SESSION['upag']                  = $upag;
$_SESSION['uorg']                  = $uorg;
$_SESSION['sAPS']                  = substr($sTripa, 1, 1); // obtem indicador de perfil do usuario
$_SESSION['sGBNIN']                = substr($sTripa, 2, 1); // obtem indicador de perfil do usuario
$_SESSION['logado']                = 'SIM';
$_SESSION['autenticaIMG']          = $valorImagem;
$_SESSION['sDtAdm']                = $dtAdm; // data da admissao invertida Ex. 02/02/2012 -> 20120202
$_SESSION['sDefVisual']            = $defvis; // Indica se o usu�rio � Deficiente Visual

// para controle do tempo da sess�o
$_SESSION["sessiontime"]           = time() + 60 * getDuracaoDaSessaoEmMinutos();

$_SESSION['hora_frequencia_finalizada'] = ''; // Indica se o usu�rio finalizou expediente


##
#  TESTE DE SEGURAN�A
# - Evita login's consecutivos em uma mesma m�quina.
#   Permite outro login ap�s determinado tempo, para
#   evitar utiliza��o de 'rob�s' que burlem o captcha
##
// Comentado em 26/08/2019: delayNovoLogin( $_SESSION['sHOrigem_1'], 0.4 );


##
#  VERIFICA DAS4, 5 ou 6
#
# - Verifica se o usuario esta dispensado de
#   registro de ponto por ser ocupante de DAS4,5,6
##
$mensagem_isento = isento_de_ponto($_SESSION['sMatricula']);

if ($mensagem_isento != '')
{
    // grava o LOG
    registraLog('entrada n�o foi registrada (servidor isento, ocupa '.$mensagem_isento.')');
    retornaErroReplaceLink('entrada.php', 'Servidor que ocupa '.$mensagem_isento.' est� isento de registrar a frequ�ncia!');
}


##
#  FOR�A MUDAR A SENHA
##
//if ($prazo == '1' || $troca_senha == '1')

if ($prazo == '1' AND !$sigac)
{
    // grava o LOG
    registraLog('entrada n�o foi registrada (TROCAR SENHA)');
    replaceLink('trocasenha.php');
}

// definicao da competencia
$comp = date('mY');

/* define a data, hora e verifica se � dia �til */
$vDatas = date('Y-m-d');
$dthoje = date('Y-m-d');
$hoje   = date('d/m/Y');
$d      = date('d');
$m      = date('m');
$y      = date('Y');

## instancia classe frequencia
#
$oFreq = new formFrequencia;
$oFreq->setOrigem('entrada.php'); // Registra informacoes em sessao
$oFreq->setAnoHoje($hoje);        // ano (data atual)
$oFreq->setUsuario($sMatricula);  // matricula do usuario
$oFreq->setSiape($sMatricula);    // matricula do servidor que se deseja alterar a frequencia
$oFreq->setLotacao($_SESSION['sLotacao']);  // matricula do usuario
$oFreq->setMes($m); // mes que se deseja alterar a frequencia
$oFreq->setAno($y); // ano que se deseja alterar a frequencia
$oFreq->setNomeDoArquivo('ponto' . $comp); // nome do arquivo de frequencia
$oFreq->setData($vDatas);

## le dados do servidor e setor
#
$oFreq->loadDadosServidor();
$oFreq->loadDadosSetor();


## - jornada do servidor, por cargo ou hor�rio especial
#  - ponto facultativo (natal, ano novo e quarta-feira de cinzas)
#
#  - verifica se dia ponto facultativo e atribui a jornada correta para o dia
#
$jornada             = $oFreq->pontoFacultativo('3');
$quarta_feira_cinzas = $oFreq->getQuartaFeiraCinzas();

## - turno estendido
#
$turno_estendido = $oFreq->turnoEstendido('3');
$sTurnoEstendido = $oFreq->getTurnoEstendido(); // informa se o servidor encontra-se em unidade
                                                // autorizada a realizar o turno estendido

## Jornada
#
$jornada         = ($jornada > $turno_estendido && $turno_estendido != '00:00' ? $turno_estendido : $jornada);
$j               = formata_jornada_para_hhmm($jornada);

## ocupantes de fun��o
#
$ocupaFuncao = $oFreq->getChefiaAtiva();

if ($ocupaFuncao == 'S')
{
    // - Se titular da fun��o ou em efetiva
    //   substitui��o, a jornada eh de 40hs
    //   exceto se tiver hor�rio especial por
    //   limita��o f�sica ou decis�o judidical
    $horario_especial        = $oFreq->getHorarioEspecial();
    $horario_especial_motivo = $oFreq->getHorarioEspecialMotivo();
    $jornada = ($horario_especial == 'S' &&
                    ($horario_especial_motivo == 'D'
                        || $horario_especial_motivo == 'J') ? $jornada : 40);
    $j = formata_jornada_para_hhmm( $jornada ); // compatibilidade
}

## Hor�rio de Servi�o
#
$entra    = $oFreq->getCadastroEntrada();         // hor�rio estabelecido de entrada ao servi�o
$sai      = $oFreq->getCadastroSaida();           // hor�rio estabelecido do t�rmino do almo�o
$iniin    = $oFreq->getCadastroInicioIntervalo(); // hor�rio estabelecido de sa�da (fim do expediente)
$fimin    = $oFreq->getCadastroFimIntervalo();    // hor�rio estabelecido do in�cio do almo�o


## verifica se feriado, fim de semana
#

// feriado nacional, estadual ou municipal e s�bado ou domingo
// retorna N se feriado ou fim de semana, caso contr�rio S
$sDiaUtil = $oFreq->verificaSeDiaUtil();
$sd       = ($sDiaUtil == 'S' ? 0 : 1);
$fer      = ($sDiaUtil == 'S' ? 0 : 1);

$situacao_cadastral = $oFreq->getSituacaoCadastral();

#
## Fim DEFINI��O DA JORNADA



##
#  INSERIR DIAS NAO REGISTRADOS
#
# - Inserir dias sem frequ�ncia registrada
# - Feriados e fins de semanas
#

if ($oFreq->getExcluido() == 'N')
{
    $oFreq->inserirDiasSemFrequencia();
}

#
## Fim INSERIR DIAS NAO REGISTRADOS


// pesquisa qual o mes ativo
$oTbValida = new DataBase('PDO');
$oTbValida->setMensagem('Problemas no acesso a tabela VALIDA.\\nPor favor tente mais tarde.');
$rx        = $oTbValida->query("SELECT id, compi, compf, DATE_FORMAT(gbnini,'%Y%m%d') AS gbnini, DATE_FORMAT(gbninf,'%Y%m%d') AS gbninf, hveraoi, hveraof, ativo, qcinzas FROM tabvalida WHERE ativo = 'S' ");

if ($rx)
{
    $oValida = $oTbValida->fetch_object();
    $sMesi   = $oValida->compi;
    $sMesf   = $oValida->compf;
    $sGbnini = $oValida->gbnini;
    $sGbninf = $oValida->gbninf;
    $iniver  = $oValida->hveraoi;
    $fimver  = $oValida->hveraof;
    $qcinzas = $oValida->qcinzas;
}
// fim do Tabvalida


// cria a se��o do usuario e identifica suas permiss�es
$_SESSION['sMesi']   = $sMes;
$_SESSION['sMesf']   = $sMesf;
$_SESSION['sGbnini'] = $sGbnin;
$_SESSION['sGbninf'] = $sGbninf;

$_SESSION['iniver'] = $iniver;
$_SESSION['fimver'] = $fimver;

$_SESSION['qcinzas'] = $qcinzas;

$autoriza = autorizacaoDiaNaoutil($vDatas, $sMatricula);

if (($sd == 1 || $fer == 1) && ($autoriza == 'N' || empty($autoriza) ))
{
    // grava o LOG
    registraLog('entrada n�o foi registrada (DIA � �TIL)');
    retornaErro('entrada.php', 'Voc� n�o est� autorizado a registrar frequ�ncia em dia n�o �til!');
    exit();
    //voltar(1,$_SESSION['sHOrigem_1']);
}
elseif (($sd == 1 || $fer == 1) && ($autoriza == 'S' ))
{
    $j = '00:00';
}


## Verifica Hor�rio de ver�o e o Fuso Hor�rio
#  - Atribui o hor�rio da entrada a $vHoras, ap�s as verifica��es
#
$vHoras = horario_de_verao($vDatas);

$vHorasia     = $vHoras;
$sHoraEntrada = $vHoras;

## limite de horario de entrada e saida do �rg�o
#  quarta-feira de cinzas
#
$limites_inss = horariosLimiteINSS();


// limite antes e depois do hor�rio definido para entrada (20 min),
// e limite antes do hor�rio definido para a sa�da (20 min).
// s�o apenas informativo.
$sec_hora_entrada = time_to_sec(left($sHoraEntrada, 5));

$sec_limite_inss_tolerancia = time_to_sec($limites_inss['tolerancia']); // tempo de toler�ncia, ex.: 15 minutos

$sec_limite_inss_entrada_horario = time_to_sec($limites_inss['entrada']['horario']); // registra entrada a partir deste hor�rio, ex.: 6:30
$sec_limite_inss_saida_horario   = time_to_sec($limites_inss['saida']['horario']); // registra sa�da at� este hor�rio, ex.: 22:00
$sec_hora_entrada_com_tolerancia = ($sec_limite_inss_entrada_horario + $sec_limite_inss_tolerancia);

$sec_limite_cinzas_tolerancia_entrada = time_to_sec($limites_inss['cinzas_entrada']['horario']);

// hor�rio de in�cio de atendimento da unidade
$sec_hora_inicio_unidade                = time_to_sec($ini);
$sec_hora_inicio_unidade_com_tolerancia = ($sec_hora_inicio_unidade + $sec_limite_inss_tolerancia);


## se eh quarta-feira de cinzas de 2013
#  verifica se entrada eh anterior �s 14:00
#
#// Por solicita��o do Diretor da DGP, � �poca (2013),
#// foi retirada a restri��o da entrada antes das 14 hs.
#// Mas, mantevesse a mensagem com altera��o pr�ximo ao
#// fim desta p�gina, para exibir apenas como informa��o.
#
if ($quarta_feira_cinzas == true && dataAno($vDatas) == '2013')
{
    if ($sec_hora_entrada < $sec_limite_cinzas_tolerancia_entrada)
    {
        //mensagem( preparaTextArea( $limites_inss['cinzas_entrada']['mensagem'], 'para_alert' ), 'entrada.php', 1 );
    }
}

## verifica se o registro de entrada do servidor esta acima
#  ou igual ao limite estabelecido para a entrada ao servi�o
#

if ($sec_hora_entrada < $sec_limite_inss_entrada_horario)
{
    // grava o LOG
    registraLog('entrada n�o foi registrada (ANTES DE ' . $limites_inss['entrada']['horario'] . ')');
    //retornaErro('entrada.php', preparaTextArea(strtr($limites_inss['entrada']['mensagem'], array('{hs}' => $limites_inss['entrada']['horario'])), 'para_alert'));
    retornaErro('entrada.php', "O SISREF estar� liberado para registro da frequ�ncia a partir das ".$limites_inss['entrada']['horario']."hs!");
    exit();
}


## Diminui 15 minutos, toler�ncia no registro de entrada
#  Exceto no caso da unidade estar com turno estendido
#


if ($sTurnoEstendido == 'N' || $jornadaMenor8horas == true || $ocupaFuncao == 'S')
{
    if ($sec_hora_entrada >= $sec_hora_entrada_com_tolerancia)
    {
        $vHoras = sec_to_time(($sec_hora_entrada - $sec_limite_inss_tolerancia));
        $vHoras = right($vHoras, 8);
    }
    elseif ($sec_hora_entrada < $sec_hora_entrada_com_tolerancia && ($sTurnoEstendido == 'N' || $jornadaMenor8horas == true || $ocupaFuncao == 'S'))
    {
        $vHoras = $limites_inss['entrada']['horario'] . ':00';
    }
}
elseif ($sTurnoEstendido == 'S' && $sec_hora_entrada < $sec_limite_inss_entrada_horario)
{
    $vHoras = $limites_inss['entrada']['horario'] . ':00';
}

$vHorasTeste = $vHoras;


// n�o permite registro a partir das 22:00:00
if ((time_to_sec($vHoras) >= $sec_limite_inss_saida_horario) && (liberado_registro_apos_22hs($sMatricula) != 'SIM'))
{
    $hs     = $limites_inss['saida']['horario'] . ':00';
    $vHoras = $limites_inss['saida']['horario'] . ':00';

    // grava o LOG
    registraLog('entrada n�o foi registrada (AP�S AS ' . $limites_inss['saida']['horario'] . ')');
    retornaErro('entrada.php', "N�o � permitido registrar entrada ap�s as ".$limites_inss['saida']['horario']."hs!");
    exit();
}


/* ******************************************************* *
 * Verifica se o horario  registrado eh maior ou menor que *
 * o da tabela de setores                                  *
 *                                                         *
 * Alterado em: 31/07/2019 (Edinalvo Rosa)                 *
 * ******************************************************* */
if (($sec_hora_entrada < $sec_hora_inicio_unidade) && ($aut == 'N'))
{
    // grava o LOG
    registraLog('entrada n�o foi registrada (FORA DO HOR�RIO DA UNIDADE)');
    retornaErro(
        'entrada.php', 'N�o � permitido registrar entrada antes do hor�rio de funcionamento da unidade sem autoriza��o da chefia! Voc� deve registrar novamente a partir das ' . substr($ini, 0, 5) . "hs!"
    );
    exit();
}
elseif (($sec_hora_entrada <= $sec_hora_inicio_unidade_com_tolerancia) && ($aut == 'N'))
{
    $vHoras = sec_to_time($sec_hora_inicio_unidade);
    $vHoras = right($vHoras, 8);
}
elseif (($vHorasTeste < $ini) && ($aut == 'S') && ($sTurnoEstendido == 'N' || $jornadaMenor8horas == true || $ocupaFuncao == 'S'))
{
    $vini = subtraiHoras($ini, $limites_inss['tolerancia'], 'H:i:s', true);
}


if (($vHorasTeste < $vini) && ($aut == 'N'))
{
    // grava o LOG
    registraLog('entrada n�o foi registrada (FORA DO HOR�RIO DA UNIDADE)');
    retornaErro(
        'entrada1.php', 'N�o � permitido registrar entrada antes do hor�rio de funcionamento da unidade sem autoriza��o da chefia!!! Voc� deve registrar novamente a partir das ' . substr($ini, 0, 5) . "hs!"
    );
    exit();
}
elseif (($vHorasTeste < $ini) && ($aut == 'S'))
{
    $idh = 1;
}
else
{
    $idh = 2;
}
/* fim da verificacao de registro de horario */


$_SESSION['registro_inicial'] = 0;

//pegando o ip do usuario
$ip        = getIpReal(); //linha que captura o ip do usuario.


$oDBase = selecionaServidor($sMatricula);
$sitcad = $oDBase->fetch_object()->sigregjur;

## ocorr�ncias grupos
$obj = new OcorrenciasGrupos();
$codigoRegistroParcialPadrao  = $obj->CodigoRegistroParcialPadrao($sitcad);
$codigoFrequenciaNormalPadrao = $obj->CodigoFrequenciaNormalPadrao($sitcad);
$codigoSemFrequenciaPadrao    = $obj->CodigoSemFrequenciaPadrao($sitcad);


//Implementar busca para saber se j� ocorreu o registro de entrada do dia
$rowsPonto = 0;
$oTbPonto  = new DataBase('PDO');
$oTbPonto->setMensagem('Erro de acesso a tabela PONTO!\\nTabela de ponto nao existe.');
$oTbPonto->query('SELECT pto.entra, pto.sai, pto.intini, pto.intsai, pto.oco, oco.desc_ocorr FROM ponto' . $comp . ' AS pto LEFT JOIN tabocfre as oco ON pto.oco = oco.siapecad WHERE dia = :dia AND siape = :siape', array(
    array(":dia", $vDatas, PDO::PARAM_STR),
    array(":siape", $sMatricula, PDO::PARAM_STR)
));

$rowsPonto = $oTbPonto->num_rows();

$oPonto           = $oTbPonto->fetch_object();
$ent              = $oPonto->entra;
$saida            = $oPonto->sai;
$iniint           = $oPonto->intini;
$fimint           = $oPonto->intsai;
$codigo           = $oPonto->oco;
$codigo_descricao = $oPonto->desc_ocorr;


//verifica se h� ocorr�ncia
if ($rowsPonto != 0 && $codigo != $codigoSemFrequenciaPadrao[0])
{
    $_SESSION['registro_inicial'] = 1;

    //$mensagem_de_registro_da_entrada = 'Voc� j� registrou entrada!';
    if (($ent == '00:00:00') || ($codigo != $codigoFrequenciaNormalPadrao[0] && $codigo != $codigoRegistroParcialPadrao[0]) || ($ent != '00:00:00' && $codigo == $codigoFrequenciaNormalPadrao[0]))
    {
        setMensagemUsuario('Consta registro de ocorr�ncia ' . $codigo . ' (' . $oPonto->desc_ocorr . ') para esse servidor neste dia,<br>ou j� registrou a saida do expediente!', 'warning');
        replaceLink("pontoser.php?cmd=1");
    }
}
else
{

    $mensagem_de_registro_da_entrada = 'Entrada registrada com sucesso!';

    // elimina '/' e ':', depois define o tipo como inteiro
    // para garantir o resultado do teste a seguir
    $_tst_vDatas = alltrim(sonumeros($vDatas), '0');
    $_tst_vHoras = alltrim(sonumeros($vHoras), '0');
    $_tst_j      = alltrim(sonumeros($j), '0');
    $_tst_ip     = alltrim(sonumeros($ip), '0');

    settype($_tst_vDatas, 'integer');
    settype($_tst_vHoras, 'integer');
    settype($_tst_j, 'integer');
    settype($_tst_ip, 'integer');

    $oTbPonto->setMensagem('Falha no registro do ponto!');

    if ($dthoje == $qcinzas || $autoriza == 'S')
    {
        if (!empty($_tst_vDatas) && !empty($_tst_vHoras) && !empty($_tst_ip))
        {
            $ent        = $vHoras;
            $parametros = array();

            if (in_array($codigo, $codigoSemFrequenciaPadrao))
            {
                $query = '
                UPDATE ponto' . $comp . '
                SET
                    entra   = :entra,
                    jornp   = :jornp,
                    jorndif = "00:00",
                    oco     = "' . $codigoRegistroParcialPadrao[0] . '",
                    idreg   = "S",
                    ip      = :ip
                WHERE
                    siape = :siape
                    AND dia = :dia
                ';

                $parametros = array(
                    array(":siape", $sMatricula, PDO::PARAM_STR),
                    array(":dia",   $vDatas,     PDO::PARAM_STR),
                    array(":entra", $vHoras,     PDO::PARAM_STR),
                    array(":jornp", $j,          PDO::PARAM_STR),
                    array(":ip",    $ip,         PDO::PARAM_STR),
                );
            }
            else
            {
                $query = '
                INSERT INTO ponto' . $comp . '
                    (siape, dia, entra, jornp, oco, idreg, ip, ip2, ip3, ip4, ipch, iprh)
                VALUES
                    (:siape, :dia, :entra, :jornp, "' . $codigoRegistroParcialPadrao[0] . '", "S", :ip, "", "", "", "", "")
                ';

                $parametros = array(
                    array(":siape", $sMatricula, PDO::PARAM_STR),
                    array(":dia",   $vDatas,     PDO::PARAM_STR),
                    array(":entra", $vHoras,     PDO::PARAM_STR),
                    array(":jornp", $j,          PDO::PARAM_STR),
                    array(":ip",    $ip,         PDO::PARAM_STR),
                );
            }

            $oTbPonto->query($query, $parametros);
        }
        else
        {
            retornaErro('entrada.php', 'Erro de processamento. Por favor, repita a opera��o!');
            exit();
        }
    }
    else
    {

        // VERIFICAR JORNADA?? if (!empty($_tst_vDatas) && !empty($_tst_vHoras) && !empty($_tst_j) && !empty($_tst_ip))
        if (!empty($_tst_vDatas) && !empty($_tst_vHoras) && !empty($_tst_ip))
        {
            $ent        = $vHoras;

            $parametros = array();


            if (in_array($codigo, $codigoSemFrequenciaPadrao))
            {
                $query = '
                UPDATE ponto' . $comp . '
                SET
                    entra   = :entra,
                    jornp   = :jornp,
                    jorndif = "00:00",
                    oco     = "'.$codigoRegistroParcialPadrao[0].'",
                    idreg   = "S",
                    ip      = :ip
                WHERE
                    siape = :siape
                    AND dia = :dia
                ';

                $parametros = array(
                    array(":siape", $sMatricula, PDO::PARAM_STR),
                    array(":dia",   $vDatas,     PDO::PARAM_STR),
                    array(":entra", $vHoras,     PDO::PARAM_STR),
                    array(":jornp", $j,          PDO::PARAM_STR),
                    array(":ip",    $ip,         PDO::PARAM_STR),
                );
            }
            else
            {
                $query = '
                INSERT INTO ponto' . $comp . '
                    (siape, dia, entra, jornp, oco, idreg, ip, ip2, ip3, ip4, ipch, iprh)
                VALUES
                    (:siape, :dia, :entra, :jornp, "' . $codigoRegistroParcialPadrao[0] . '", "S", :ip, "", "", "", "", "")';

                $parametros = array(
                    array(":siape", $sMatricula, PDO::PARAM_STR),
                    array(":dia",   $vDatas,     PDO::PARAM_STR),
                    array(":entra", $vHoras,     PDO::PARAM_STR),
                    array(":jornp", $j,          PDO::PARAM_STR),
                    array("ip",     $ip,         PDO::PARAM_STR)
                );
            }

            $oTbPonto->query($query, $parametros);
        }
        else
        {
            retornaErro('entrada.php', 'Erro de processamento. Por favor, repita a opera��o!');
            exit();
        }
    }

    // grava o LOG
    registraLog('registrou a ENTRADA'); //, $_SESSION['sMatricula'], $_SESSION['sNome'] );
}

// Busca o historico de autoriza��es de banco de horas
$oAutorizacaoBanco = new DataBase('PDO');
$oAutorizacaoBanco->query('SELECT DATE_FORMAT(data_inicio, "%d/%m/%Y") as data_inicio, DATE_FORMAT(data_fim, "%d/%m/%Y") as data_fim FROM autorizacoes_servidores WHERE NOW() BETWEEN data_inicio AND data_fim AND siape = '.$_SESSION['sMatricula'].' ');

$oAutorizacoesBanco = new DataBase('PDO');
$oAutorizacoesBanco->query('SELECT autorizacoes_servidores.*, ciclos.data_inicio as data_inicio_ciclo, ciclos.data_fim as data_fim_ciclo FROM autorizacoes_servidores INNER JOIN ciclos ON ciclos.id = autorizacoes_servidores.ciclo_id WHERE autorizacoes_servidores.siape = '.$_SESSION['sMatricula'].' ');

// Busca o hist�rico de autoriza��es de usufruto
$oAutorizacaoUsufruto = new DataBase('PDO');
$oAutorizacaoUsufruto->query('SELECT DATE_FORMAT(data_inicio, "%d/%m/%Y") as data_inicio, DATE_FORMAT(data_fim, "%d/%m/%Y") as data_fim FROM autorizacoes_servidores_usufruto WHERE NOW() BETWEEN data_inicio AND data_fim  AND siape = '.$_SESSION['sMatricula'].' ');

$oAutorizacoesUsufruto = new DataBase('PDO');
$oAutorizacoesUsufruto->query('SELECT autorizacoes_servidores_usufruto.*, ciclos.data_inicio as data_inicio_ciclo, ciclos.data_fim as data_fim_ciclo  FROM autorizacoes_servidores_usufruto INNER JOIN ciclos ON ciclos.id = autorizacoes_servidores_usufruto.ciclo_id  WHERE autorizacoes_servidores_usufruto.siape = '.$_SESSION['sMatricula'].' ');

// Busca o hist�rico de autoriza��o de horas extras
$oAutorizacaoHoraExtra = new DataBase('PDO');
$oAutorizacaoHoraExtra->query('SELECT DATE_FORMAT(data_inicio, "%d/%m/%Y") as data_inicio, DATE_FORMAT(data_fim, "%d/%m/%Y") as data_fim FROM autorizacoes_hora_extra WHERE NOW() BETWEEN data_inicio AND data_fim AND siape = '.$_SESSION['sMatricula'].' ');

$oAutorizacoesExtra = new DataBase('PDO');
$oAutorizacoesExtra->query('SELECT autorizacoes_hora_extra.*  FROM autorizacoes_hora_extra WHERE autorizacoes_hora_extra.siape = '.$_SESSION['sMatricula'].' ORDER BY autorizacoes_hora_extra.data_inicio DESC');


if (empty($iniint))
{
    $iniint = '00:00:00';
}
if (empty($fimint))
{
    $fimint = '00:00:00';
}
if (empty($saida))
{
    $saida = '00:00:00';
}

$horas_trabalhadas = horas_trabalhadas_ate_o_momento($sMatricula, $hoje);
$horas_trabalhadas_segundos = horas_trabalhadas_ate_momento_segundos($sMatricula, $hoje);

if (empty($horas_trabalhadas))
{
    $horas_trabalhadas = ' ------ ';
}

//guarda variaveis na sessao.
$_SESSION['ini'] = $ini;
$_SESSION['fim'] = $fim;


$_SESSION['entra'] = $entra; // hor�rio estabelecido de entrada ao servi�o
$_SESSION['sai']   = $sai;   // hor�rio estabelecido de sa�da (fim do expediente)
$_SESSION['iniin'] = $iniin; // hor�rio estabelecido do in�cio do almo�o
$_SESSION['fimin'] = $fimin; // hor�rio estabelecido do t�rmino do almo�o
$_SESSION['aut']   = $aut;

$_SESSION['autorizacao_dia_nao_util'] = $autoriza;

$_SESSION['ent']    = $ent;
$_SESSION['iniint'] = $iniint;
$_SESSION['fimint'] = $fimint;
$_SESSION['saida']  = $saida;

$_SESSION['vHorasia'] = $vHorasia;

$_SESSION['bhoras'] = $bhoras;
$_SESSION['horae']  = $horae;
$_SESSION['motivo'] = $motivo;
$_SESSION['chefe']  = $chefe;
$_SESSION['jnd']    = $jornada;
$_SESSION['codmun'] = $codmun;

$_SESSION['sModuloPrincipalAcionado'] = 'entrada';


##
# verifica se o usuario est� como substituto.
# se o per�odo expirou cancela a permissao
# para atuar como chefe da unidade
#

trata_substituicao($sMatricula);

?>
<script>
    $(document).ready(function ()
    {
        iniciar_relogio();
    });
</script>

<div class="container" style="position:relative;top:-50px;">

    <!-- Mensagem para o Usu�rio -->
    <div class="row">
        <?php
        if (!empty($mensagem_de_registro_da_entrada))
        {
            echo getMensagemErroHTML($mensagem_de_registro_da_entrada, 'info');
        }
        ?>
    </div>

    <?php if ($_SESSION['sDefVisual'] == 'S'): ?>

        <div class="row">
            <div><b>Teclas de Atalho:</b><br></div>
            <div><i>Sa�da para Almo�ar:</i> Alt+2. <i>Retorno do Almo�o:</i> Alt+3. <i>Fim do Expediente:</i> Alt+4. <i>Solicitar trabalhar em dia n�o �til:</i> Alt+5. <i>Visualizar frequ�ncias do m�s:</i> Alt+6. <i>Visualizar frequ�ncias anteriores:</i> Alt+7. <i>Visualizar saldos de compensa��es:</i> Alt+8.</div>
        </div>
        <div class="row" style='width:100%;padding-bottom:50px;'></div>

    <?php endif; ?>

    <!-- Linha referente aos hor�rios -->
    <script type="text/javascript">
        var timerVar = setInterval(countTimer, 1000);
        var totalSeconds = <?php echo $horas_trabalhadas_segundos; ?> ;

        function countTimer() {
            ++totalSeconds;
            var hour = Math.floor(totalSeconds /3600);
            var minute = Math.floor((totalSeconds - hour*3600)/60);
            var seconds = totalSeconds - (hour*3600 + minute*60);

            if(hour < 10){}
                hour = "0"+hour;

            if(minute < 10)
                minute = "0"+minute;

            if(seconds < 10)
                seconds = "0"+seconds;

            document.getElementById("horas-trabalhadas").innerHTML = '<span class="glyphicon glyphicon-time"></span>'+hour + ":" + minute + ":" + seconds;
        }
    </script>
            
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
            <span class="hora" id="horas-trabalhadas"></span>
        </div>
    </div>

    <!-- Row Referente aos dados dos funcion�rios  -->
    <div class="row margin-10">
        <div class="subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Meus Dados</strong></h4>
        </div>
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-4">
                <h5><strong>SIAPE</strong></h5>
                <p><?= tratarHTML(removeOrgaoMatricula($sMatricula)); ?></p>
            </div>
            <div class="col-md-5">
                <h5><strong>NOME</strong></h5>
                <p><div id="nome_do_servidor"  style="float:left;color:#52504e;font-size:1.5em;"><?= tratarHTML("<h1></h1>" . $nome); ?></div><div style="float:left;padding-top:5px;">&emsp;
                    <a type="button" class="" data-toggle="modal" data-target="#exampleModal" href='#'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Indicar Nome desejado (Social/Cadastro)' title='Indicar Nome desejado (Social/Cadastro)'></a></div></p>

            </div>
            <div class="col-md-3">
                <h5><strong>LOTA��O</strong></h5>
                <p><?= tratarHTML($lotReal); ?></p>
            </div>
        </div>
    </div>
 <div class="row margin-10">
        <div class="subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Minhas Autoriza��es</strong></h4>
        </div>
        <div class="col-md-2" style="text-align: center;">
            <strong>COMPENSA��O</strong>
            <?= $bhoras == "S" ? "<div style='text-align: center; '>AUTORIZADA</div>" : "<div class='red' style='color:red;'>N�O AUTORIZADA</div>"; ?>
        </div>
        <div class="col-md-3" style="text-align: center;" >
            <strong>REGISTRO FORA DO<br>HOR�RIO DO SETOR</strong>
            <div id="at-registro-fora-horario" style="text-align: center;"><?php echo $aut == 'S' ? 'AUTORIZADO' : 'N�O AUTORIZADO'; ?></div>
        </div>
        <div class="col-md-2" style="text-align: center;" >
            <strong>ACUMULO DE<br>BANCO DE HORAS</strong>
            <div id="at-acumulo-banco" title="Clique aqui para ver a lista de acumulos autorizados.">
                <div onclick="$('#autorizacoes-acumulo').modal(); " style="cursor: pointer; text-align: center;">
                    <?php 
                        if($oAutorizacaoBanco->num_registros() > 0){
                            $autorizacaoBanco = $oAutorizacaoBanco->fetch_array();
                            echo $autorizacaoBanco['data_inicio']." - ".$autorizacaoBanco['data_fim'];
                        }else{
                            echo "N�o possu�";
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3" style="text-align: center;">
            <strong>USUFRUTO DO<br>BANCO DE HORAS</strong>
            <div id="at-usufruto-banco" title="Clique aqui para ver a lista de usufruto autorizado.">
                <div onclick="$('#autorizacoes-usufruto').modal(); " style="cursor: pointer; text-align: center;">
                    <?php 
                        if($oAutorizacaoUsufruto->num_registros() > 0){
                            $autorizacaoUsufruto = $oAutorizacaoUsufruto->fetch_array();
                            echo $autorizacaoUsufruto['data_inicio']." - ".$autorizacaoUsufruto['data_fim'];
                        }else{
                            echo "N�o possu�";
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-2" style="text-align: center;" >
            <strong>HORAS EXTRAS</strong>
            <div id="at-hora-extra" title="Clique aqui para ver a lista de horas extras autorizadas.">
                <div onclick="$('#autorizacoes-hora-extra').modal(); " style="cursor: pointer; text-align: center;">
                    <?php 
                        if($oAutorizacaoHoraExtra->num_registros() > 0){
                            $autorizacaoHoraExtra = $oAutorizacaoHoraExtra->fetch_array();
                            echo $autorizacaoHoraExtra['data_inicio']." - ".$autorizacaoHoraExtra['data_fim'];
                        }else{
                            echo "N�o possu�";
                        }
                    ?>
                </div>
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
                    <strong>HOR�RIO DO SETOR<?= ($sTurnoEstendido == 'S' ? ' - Unidade com Turno Estendido' : ''); ?></strong>
                </h5>
                <p>
                    <strong><?= tratarHTML($ini); ?></strong> �s <strong><?= tratarHTML($fim); ?></strong>
                </p>
            </div>
            <div class="col-md-3">
                <h5><strong>HORA DE ENTRADA</strong></h5>
                <p><?= tratarHTML($entra); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>INTERVALO</strong></h5>
                <p><?= tratarHTML($iniin) . " �s " . tratarHTML($fimin); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>HORA DE SA�DA</strong></h5>
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
                <h4>HOR�RIOS DO SERVIDOR - <?= tratarHTML($hoje); ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <h5><strong>ENTRADA</strong></h5>
            <p><?= ($ent == '' ? tratarHTML($vHoras) : tratarHTML($ent)) . 'h'; ?></p>
        </div>
        <div class="col-md-6">
            <h5><strong>INTERVALO</strong></h5>
            <p>
                <?php

                if ($jornada == '08:00' || $bhoras == 'S' || $ocupaFuncao == 'S')
                {
                    echo ($iniint == '00:00:00' ? "<a id='iniciar_almoco' class='btn btn-primary btn-md' href=\"javascript:confirma ('Deseja realmente registrar o in�cio do intervalo?','entrada2.php')\"><span class=\"glyphicon glyphicon-flag\"></span>  Iniciar </a>" : "");
                }

                echo tratarHTML(' ' . $iniint . ' �s ' . $fimint . 'h ');

                if ($jornada == '08:00' || $bhoras == 'S' || $ocupaFuncao == 'S')
                {
                    echo ($fimint == '00:00:00' ? "<a id='finalizar_almoco' class='btn btn-danger btn-md' href=\"javascript:confirma ('Deseja realmente registrar o retorno do intervalo?','entrada3.php')\"><span class=\"glyphicon glyphicon-remove\"></span>  Finalizar</a>" : "");
                }
                ?>
            </p>
        </div>
        <div class="col-md-3">
            <h5><strong>SA�DA</strong></h5>
            <p>
                <?php

                echo tratarHTML($saida . 'h');
                echo $saida == '00:00:00' ? " <a id='fim_expediente' class=\"btn btn-success btn-md\" href=\"javascript:confirma ('Deseja realmente registrar o fim do expediente?','entrada4.php')\"><span class=\"glyphicon glyphicon-ok\"></span> Marcar</a>" : "";
                ?>
            </p>
        </div>
    </div>
    <!-- -->
    <div class="row" id="botoes-iteracao">
        <div class="col-md-4">
            <button class="btn btn-primary btn-block" onclick="window.open('autorizacao_trabalho_dia_nao_util_solicitacao.php?dados=<?= base64_encode(tratarHTML($_SESSION['sMatricula']).":|:".tratarHTML($codmun)); ?>');">
                <div class="btn-icon">
                    <span class="glyphicon glyphicon-search"></span>
                </div> Solicita��o para trabalho<br>em dia n�o �til
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-info btn-block"  onclick="window.open('pontoser.php?cmd=1');"><div class="btn-icon">
                    <span class="glyphicon glyphicon-eye-open"></span>
                </div> Visualizar frequ�ncia<br>do m�s</button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-warning btn-block" onclick="window.open('entrada8.php?cmd=2&orig=1');"><div class="btn-icon">
                    <span class="glyphicon glyphicon-book"></span>
                </div> Visualizar meses<br>anteriores</button>
        </div>
        <div class="col-md-12 text-center margin-10">
            <a class="demonstrativo" href="entrada9.php" target="new">Visualizar demonstrativo de compensa��es.</a>
        </div>
    </div>


    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Qual o nome que deseja, que seja exibido nas telas do sistema?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="form1" name="form1" method="POST" action="#" onsubmit="javascript:return false;">

                    <?php if ($_SESSION['sDefVisual'] == 'S'): ?>
                        <input type="hidden" name="defvis"  id="defvis"  value="<?= tratarHTML($_SESSION['sDefVisual']); ?>">
                        <input type="hidden" name="dados"   id="dados"   value="<?= base64_encode(tratarHTML($_SESSION['sMatricula']).':|:'.tratarHTML($codmun)); ?>">
                        <input type="hidden" name="lotacao" id="lotacao" value="<?= tratarHTML($_SESSION['sLotacao']); ?>">
                    <?php endif; ?>

                    <div class="modal-body">
                        <input type="hidden" name="matricula" id="matricula" value="<?= tratarHTML(removeOrgaoMatricula($sMatricula)); ?>">
                        <select class="form-control" name="opcao" id="opcao">
                            <option value="nome_original">Nome Original</option>
                            <option value="nome_social">Nome Social</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary salvar_nome">Salvar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<!-- Modal de autoriza��o de acumulo-->
<div class="modal fade" id="autorizacoes-acumulo" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Autoriza��es de acumulo de banco de horas</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Ciclo</th>
                            <th>Data de In�cio</th>
                            <th>Data de T�rmino</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($oAutorizacoesBanco->num_registros()): ?>
                            <?php while($autorizacaoBanco = $oAutorizacoesBanco->fetch_object()): ?>
                                <tr>
                                    <td>
                                        <?php 
                                            $dtiniciociclo = new \DateTime($autorizacaoBanco->data_inicio_ciclo); 
                                            $dtfimciclo = new \DateTime($autorizacaoBanco->data_fim_ciclo); 
                                            echo $dtiniciociclo->format('d/m/Y')." - ". $dtfimciclo->format('d/m/Y'); 
                                        ?>
                                    </td>
                                    <td><?php $dtinicio = new \DateTime($autorizacaoBanco->data_inicio); echo $dtinicio->format('d/m/Y'); ?></td>
                                    <td><?php $dtfim = new \DateTime($autorizacaoBanco->data_fim); echo $dtfim->format('d/m/Y'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3"><i>Nenhuma autoriza��o de acumulo registrada.</i></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default save" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal de autoriza��o de hora extra-->
<div class="modal fade" id="autorizacoes-hora-extra" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Autoriza��es de Horas Extras</h4>
            </div>
            <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Data de In�cio</th>
                                <th>Data de T�rmino</th>
                                <th>Horas</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($oAutorizacoesExtra->num_registros()): ?>
                            <?php while($autorizacaoHora = $oAutorizacoesExtra->fetch_object()): ?>
                                <tr>
                                    <td><?php $dtiniciohoraextra = new \DateTime($autorizacaoHora->data_inicio); echo $dtiniciohoraextra->format('d/m/Y'); ?></td>
                                    <td><?php $dtfimhoraextra = new \DateTime($autorizacaoHora->data_inicio); echo $dtfimhoraextra->format('d/m/Y'); ?></td>
                                    <td><?php echo $autorizacaoHora->horas; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3"><i>Nenhuma autoriza��o de hora extra registrada.</i></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default save" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de autoriza��o de usufruto-->
<div class="modal fade" id="autorizacoes-usufruto" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Autoriza��es de usufruto de banco de horas</h4>
            </div>
            <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ciclo</th>
                                <th>Data de In�cio</th>
                                <th>Data de T�rmino</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($oAutorizacoesUsufruto->num_registros()): ?>
                            <?php while($autorizacaoUsufruto = $oAutorizacoesUsufruto->fetch_object()): ?>
                                <tr>
                                    <td>
                                        <?php 
                                            $dtiniciociclo = new \DateTime($oAutorizacoesUsufruto->data_inicio_ciclo); 
                                            $dtfimciclo = new \DateTime($oAutorizacoesUsufruto->data_fim_ciclo); 
                                            echo $dtiniciociclo->format('d/m/Y')." - ". $dtfimciclo->format('d/m/Y'); 
                                        ?>
                                    </td>
                                    <td><?php $dtinicio = new \DateTime($autorizacaoUsufruto->data_inicio); echo $dtinicio->format('d/m/Y'); ?></td>
                                    <td><?php $dtfim = new \DateTime($autorizacaoUsufruto->data_fim); echo $dtfim->format('d/m/Y'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3"><i>Nenhuma autoriza��o de usufruto registrada.</i></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default save" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>
<?php

##
# Registra se a entrada foi 20 min antes
# ou 20 min depois do hor�rio de entrada definido
#
//mensagemHorarioDifere(
//    $sMatricula, 'limite_entrada', $sTurnoEstendido, $ocupaFuncao, $sLotacao, $situacao_cadastral, $entra, $sHoraEntrada, $sDiaUtil
//);

// Base do formul�rio
//
$oForm->exibeBaseHTML();

DataBase::fechaConexao();

exit();



/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : autenticacao_do_usuario                      |
 * | @description : autenticacaodo usuario (siape e senha)       |
 * |                                                             |
 * | @param  : [<string>] - $siape                               |
 * | @param  : [<string>] - $senha                               |
 * |                                                             |
 * | @return : void                                              |
 * |                                                             |
 * | @usage  : autenticacao_do_usuario( '9000000', 'xxxxxx' );   |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : (class) DataBase   class_database.php         |
 * +-------------------------------------------------------------+
 * */
function autenticacao_do_usuario($cpf = '', $senha = '')
{
    global $result, $numrows;

    $result   = false;
    $numrows  = 0;
    $oUsuario = false;

    $obj = new TabServativController();
    $oDBase = $obj->selecionaServidor($cpf, 'entrada.php', 1);

    if ($oDBase)
    {
        $numrows  = $oDBase->num_rows();
        $oUsuario = $oDBase->fetch_object();
    }

    return $oUsuario;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : entrada1_verifica_situacao                   |
 * | @description : verifica a situacao cadastral do servidor,   |
 * |                se for cedido ou fixado (??) n�o registra    |
 * |                frequ�ncia, a informa��o � encaminhado pelo  |
 * |                pelo org�o requisitante para o SOGP lan�ar.  |
 * |                                                             |
 * | @param  : [<string>] - $sitcad                              |
 * |                                                             |
 * | @return : void                                              |
 * |                                                             |
 * | @usage  : entrada1_verifica_situacao( '08' );               |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : (func) registraLog   fucntions.php            |
 * |               (func) mensagem      fucntions.php            |
 * +-------------------------------------------------------------+
 * */
function entrada1_verifica_situacao($sitcad = '')
{
    switch ($sitcad)
    {
        case '':
            // grava o LOG
            registraLog('entrada n�o foi registrada (SITCAD indefinido)');
            retornaErro('entrada.php', 'Voc� n�o pode registrar frequ�ncia no SISREF, situa��o cadastral indefinida!');
            break;

        case '08':
        //case '18':
            // grava o LOG
            registraLog('entrada n�o foi registrada (CEDIDO)');
            retornaErro('entrada.php', 'Voc� n�o pode registrar frequ�ncia no SISREF por estar cedido ou fixado!');
            break;

        default:
            break;
    }
}


/**
 * @info Se o usu�rio n�o registrou entrada antes, sistema carrega dados
 *       cadastrais e afastamentos do SIAPE/SIAPENet para o sistema de
 *       frequencia
 *
 * @param  string $siape
 * @return boolean
 *
 * @author : Edinalvo Rosa
 */
function naoRegistrouEntradaAntes($siape = null)
{
    $siape = ( !is_null($siape) && !empty(trim($siape)) ? $_SESSION['sMatricula'] : $siape );

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino('entrada.php');
    $oDBase->setMensagem('Problemas no acesso a tabela PONTO.\\nPor favor tente mais tarde.');
    $oDBase->query("
    SELECT
        pto.siape, pto.entra
    FROM
        ponto".date('mY')." AS pto
    WHERE
        pto.siape = :siape
        AND pto.dia = :dia
    ", array(
        array(':siape', $siape,        PDO::PARAM_STR),
        array(':dia',   date('Y-m-d'), PDO::PARAM_STR),
    ));

    $resultado = ($oDBase->num_rows() == 0);

    return $resultado;
}

