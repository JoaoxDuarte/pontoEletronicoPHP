<?php
/* _________________________________________________________________________*\
  |                                                                           |
  |   AUTENTICA O USUÁRIO E GRAVA O PRIMEIRO REGISTRO DE FREQUENCIA DO DIA    |
  |                                                                           |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

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

    //retornaAlteraUsuario('entrada.php', 'Nome de exibição foi alterado com sucesso! É necessário logar novamente!');
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
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
include_once('ilegal_entrada.php');

// Texto digitado no campo imagem, e transformando em mínúsculo.
// - Para haver distinção entre maiúsculas e minúsculas, retire o
//   strtoupper().
$txtImagem = strtoupper($_POST['txtImagem']);

// Caracteres que estão na imagem,
// também deixando tudo em minúsculo.
$valorImagem = strtoupper($_SESSION['autenticaIMG']);

// verificando se existe o usuário e senha informado
$result   = null;
$numrows  = 0;

$oUsuario = autenticacao_do_usuario($sCPF, $sSenha);

if (is_object($oUsuario))
{
    // Le dados do usuário
    include_once( 'entrada1_dados.le.php' );

    // informar tipo da origem: rh.php, chefia.php ou entrada.php
    $_SESSION['sHOrigem_1'] = 'entrada.php';

    // cria a seção do usuario e identifica suas permissões

    //CONCAT NA MATRICULA, PARA UNILA AO ÓRGÃO
    $sMatricula = $oUsuario->siape;
    $defvis     = $oUsuario->defvis;

    $_SESSION['sMatricula'] = $sMatricula;
    $_SESSION['sNome']      = $sNome;
    $_SESSION['upag']       = $upag;
}

##
#  Verifica se o usuário existe, se o texto digitado eh igual aos caracteres
#  que estão na imagem, e, se o servidor/estagiário NÃO estiver indicado no
#  cadastro como deficiente visual compara o texto digitado com a imagem
##
// Forçando o captcha ser válido
//$txtImagem = $valorImagem;

if (empty($numrows) && !$ja_esta_logado)
{
    retornaErro('entrada.php', 'Usuário inválido!');
    exit();
}

if (checkIpAccess() == false)
{
    retornaErro('entrada.php', 'Você não se encontra na faixa de IP autorizada.');
    exit();
}

if ($tabBancoDeHorasAcumulosController->verifyExistsAutorization($sMatricula))
{
    retornaErro('entrada.php', 'Você possuí usufruto total de horas autorizado para o dia de hoje, portanto não pode registrar o ponto.');
    exit();
}

if ($formSenha !== $oUsuario->senha && !$ja_esta_logado)
{
    retornaErro('entrada.php', 'Senha inválida!');
    exit();
}

if ($defvis != 'S' && !$ja_esta_logado && ($txtImagem != $valorImagem || empty($txtImagem)))
{
    retornaErro('entrada.php', 'Captcha inválido!');
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


// ATUALIZA INFORMAÇÕES DO SERVIDOR DE ACORDO COM O RETORNO DA API DO SIAPE
// SE FOR O PRIMEIRO LOGIN DO DIA PARA REGISTRO DE FREQUÊNCIA
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
#  Verifica situação cadastral do usuario, se cedido ou fixado está
#  dispensado de registrar a frequência no Órgão cedente.
#  - Sua frequência será informada pelo Órgão requisitante em que estiver
#    desempenhando suas funções.
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


// cria a seção do usuario e identifica suas permissões
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
$_SESSION['sDefVisual']            = $defvis; // Indica se o usuário é Deficiente Visual

// para controle do tempo da sessão
$_SESSION["sessiontime"]           = time() + 60 * getDuracaoDaSessaoEmMinutos();

$_SESSION['hora_frequencia_finalizada'] = ''; // Indica se o usuário finalizou expediente


##
#  TESTE DE SEGURANÇA
# - Evita login's consecutivos em uma mesma máquina.
#   Permite outro login após determinado tempo, para
#   evitar utilização de 'robôs' que burlem o captcha
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
    registraLog('entrada não foi registrada (servidor isento, ocupa '.$mensagem_isento.')');
    retornaErroReplaceLink('entrada.php', 'Servidor que ocupa '.$mensagem_isento.' está isento de registrar a frequência!');
}


##
#  FORÇA MUDAR A SENHA
##
//if ($prazo == '1' || $troca_senha == '1')

if ($prazo == '1' AND !$sigac)
{
    // grava o LOG
    registraLog('entrada não foi registrada (TROCAR SENHA)');
    replaceLink('trocasenha.php');
}

// definicao da competencia
$comp = date('mY');

/* define a data, hora e verifica se é dia útil */
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


## - jornada do servidor, por cargo ou horário especial
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

## ocupantes de função
#
$ocupaFuncao = $oFreq->getChefiaAtiva();

if ($ocupaFuncao == 'S')
{
    // - Se titular da função ou em efetiva
    //   substituição, a jornada eh de 40hs
    //   exceto se tiver horário especial por
    //   limitação física ou decisão judidical
    $horario_especial        = $oFreq->getHorarioEspecial();
    $horario_especial_motivo = $oFreq->getHorarioEspecialMotivo();
    $jornada = ($horario_especial == 'S' &&
                    ($horario_especial_motivo == 'D'
                        || $horario_especial_motivo == 'J') ? $jornada : 40);
    $j = formata_jornada_para_hhmm( $jornada ); // compatibilidade
}

## Horário de Serviço
#
$entra    = $oFreq->getCadastroEntrada();         // horário estabelecido de entrada ao serviço
$sai      = $oFreq->getCadastroSaida();           // horário estabelecido do término do almoço
$iniin    = $oFreq->getCadastroInicioIntervalo(); // horário estabelecido de saída (fim do expediente)
$fimin    = $oFreq->getCadastroFimIntervalo();    // horário estabelecido do início do almoço


## verifica se feriado, fim de semana
#

// feriado nacional, estadual ou municipal e sábado ou domingo
// retorna N se feriado ou fim de semana, caso contrário S
$sDiaUtil = $oFreq->verificaSeDiaUtil();
$sd       = ($sDiaUtil == 'S' ? 0 : 1);
$fer      = ($sDiaUtil == 'S' ? 0 : 1);

$situacao_cadastral = $oFreq->getSituacaoCadastral();

#
## Fim DEFINIÇÃO DA JORNADA



##
#  INSERIR DIAS NAO REGISTRADOS
#
# - Inserir dias sem frequência registrada
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


// cria a seção do usuario e identifica suas permissões
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
    registraLog('entrada não foi registrada (DIA Ñ ÚTIL)');
    retornaErro('entrada.php', 'Você não está autorizado a registrar frequência em dia não útil!');
    exit();
    //voltar(1,$_SESSION['sHOrigem_1']);
}
elseif (($sd == 1 || $fer == 1) && ($autoriza == 'S' ))
{
    $j = '00:00';
}


## Verifica Horário de verão e o Fuso Horário
#  - Atribui o horário da entrada a $vHoras, após as verificações
#
$vHoras = horario_de_verao($vDatas);

$vHorasia     = $vHoras;
$sHoraEntrada = $vHoras;

## limite de horario de entrada e saida do Órgão
#  quarta-feira de cinzas
#
$limites_inss = horariosLimiteINSS();


// limite antes e depois do horário definido para entrada (20 min),
// e limite antes do horário definido para a saída (20 min).
// são apenas informativo.
$sec_hora_entrada = time_to_sec(left($sHoraEntrada, 5));

$sec_limite_inss_tolerancia = time_to_sec($limites_inss['tolerancia']); // tempo de tolerância, ex.: 15 minutos

$sec_limite_inss_entrada_horario = time_to_sec($limites_inss['entrada']['horario']); // registra entrada a partir deste horário, ex.: 6:30
$sec_limite_inss_saida_horario   = time_to_sec($limites_inss['saida']['horario']); // registra saída até este horário, ex.: 22:00
$sec_hora_entrada_com_tolerancia = ($sec_limite_inss_entrada_horario + $sec_limite_inss_tolerancia);

$sec_limite_cinzas_tolerancia_entrada = time_to_sec($limites_inss['cinzas_entrada']['horario']);

// horário de início de atendimento da unidade
$sec_hora_inicio_unidade                = time_to_sec($ini);
$sec_hora_inicio_unidade_com_tolerancia = ($sec_hora_inicio_unidade + $sec_limite_inss_tolerancia);


## se eh quarta-feira de cinzas de 2013
#  verifica se entrada eh anterior às 14:00
#
#// Por solicitação do Diretor da DGP, à época (2013),
#// foi retirada a restrição da entrada antes das 14 hs.
#// Mas, mantevesse a mensagem com alteração próximo ao
#// fim desta página, para exibir apenas como informação.
#
if ($quarta_feira_cinzas == true && dataAno($vDatas) == '2013')
{
    if ($sec_hora_entrada < $sec_limite_cinzas_tolerancia_entrada)
    {
        //mensagem( preparaTextArea( $limites_inss['cinzas_entrada']['mensagem'], 'para_alert' ), 'entrada.php', 1 );
    }
}

## verifica se o registro de entrada do servidor esta acima
#  ou igual ao limite estabelecido para a entrada ao serviço
#

if ($sec_hora_entrada < $sec_limite_inss_entrada_horario)
{
    // grava o LOG
    registraLog('entrada não foi registrada (ANTES DE ' . $limites_inss['entrada']['horario'] . ')');
    //retornaErro('entrada.php', preparaTextArea(strtr($limites_inss['entrada']['mensagem'], array('{hs}' => $limites_inss['entrada']['horario'])), 'para_alert'));
    retornaErro('entrada.php', "O SISREF estará liberado para registro da frequência a partir das ".$limites_inss['entrada']['horario']."hs!");
    exit();
}


## Diminui 15 minutos, tolerância no registro de entrada
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


// não permite registro a partir das 22:00:00
if ((time_to_sec($vHoras) >= $sec_limite_inss_saida_horario) && (liberado_registro_apos_22hs($sMatricula) != 'SIM'))
{
    $hs     = $limites_inss['saida']['horario'] . ':00';
    $vHoras = $limites_inss['saida']['horario'] . ':00';

    // grava o LOG
    registraLog('entrada não foi registrada (APÓS AS ' . $limites_inss['saida']['horario'] . ')');
    retornaErro('entrada.php', "Não é permitido registrar entrada após as ".$limites_inss['saida']['horario']."hs!");
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
    registraLog('entrada não foi registrada (FORA DO HORÁRIO DA UNIDADE)');
    retornaErro(
        'entrada.php', 'Não é permitido registrar entrada antes do horário de funcionamento da unidade sem autorização da chefia! Você deve registrar novamente a partir das ' . substr($ini, 0, 5) . "hs!"
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
    registraLog('entrada não foi registrada (FORA DO HORÁRIO DA UNIDADE)');
    retornaErro(
        'entrada1.php', 'Não é permitido registrar entrada antes do horário de funcionamento da unidade sem autorização da chefia!!! Você deve registrar novamente a partir das ' . substr($ini, 0, 5) . "hs!"
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

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigoRegistroParcialPadrao  = $obj->CodigoRegistroParcialPadrao($sitcad);
$codigoFrequenciaNormalPadrao = $obj->CodigoFrequenciaNormalPadrao($sitcad);
$codigoSemFrequenciaPadrao    = $obj->CodigoSemFrequenciaPadrao($sitcad);


//Implementar busca para saber se já ocorreu o registro de entrada do dia
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


//verifica se há ocorrência
if ($rowsPonto != 0 && $codigo != $codigoSemFrequenciaPadrao[0])
{
    $_SESSION['registro_inicial'] = 1;

    //$mensagem_de_registro_da_entrada = 'Você já registrou entrada!';
    if (($ent == '00:00:00') || ($codigo != $codigoFrequenciaNormalPadrao[0] && $codigo != $codigoRegistroParcialPadrao[0]) || ($ent != '00:00:00' && $codigo == $codigoFrequenciaNormalPadrao[0]))
    {
        setMensagemUsuario('Consta registro de ocorrência ' . $codigo . ' (' . $oPonto->desc_ocorr . ') para esse servidor neste dia,<br>ou já registrou a saida do expediente!', 'warning');
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
            retornaErro('entrada.php', 'Erro de processamento. Por favor, repita a operação!');
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
            retornaErro('entrada.php', 'Erro de processamento. Por favor, repita a operação!');
            exit();
        }
    }

    // grava o LOG
    registraLog('registrou a ENTRADA'); //, $_SESSION['sMatricula'], $_SESSION['sNome'] );
}

// Busca o historico de autorizações de banco de horas
$oAutorizacaoBanco = new DataBase('PDO');
$oAutorizacaoBanco->query('SELECT DATE_FORMAT(data_inicio, "%d/%m/%Y") as data_inicio, DATE_FORMAT(data_fim, "%d/%m/%Y") as data_fim FROM autorizacoes_servidores WHERE NOW() BETWEEN data_inicio AND data_fim AND siape = '.$_SESSION['sMatricula'].' ');

$oAutorizacoesBanco = new DataBase('PDO');
$oAutorizacoesBanco->query('SELECT autorizacoes_servidores.*, ciclos.data_inicio as data_inicio_ciclo, ciclos.data_fim as data_fim_ciclo FROM autorizacoes_servidores INNER JOIN ciclos ON ciclos.id = autorizacoes_servidores.ciclo_id WHERE autorizacoes_servidores.siape = '.$_SESSION['sMatricula'].' ');

// Busca o histórico de autorizações de usufruto
$oAutorizacaoUsufruto = new DataBase('PDO');
$oAutorizacaoUsufruto->query('SELECT DATE_FORMAT(data_inicio, "%d/%m/%Y") as data_inicio, DATE_FORMAT(data_fim, "%d/%m/%Y") as data_fim FROM autorizacoes_servidores_usufruto WHERE NOW() BETWEEN data_inicio AND data_fim  AND siape = '.$_SESSION['sMatricula'].' ');

$oAutorizacoesUsufruto = new DataBase('PDO');
$oAutorizacoesUsufruto->query('SELECT autorizacoes_servidores_usufruto.*, ciclos.data_inicio as data_inicio_ciclo, ciclos.data_fim as data_fim_ciclo  FROM autorizacoes_servidores_usufruto INNER JOIN ciclos ON ciclos.id = autorizacoes_servidores_usufruto.ciclo_id  WHERE autorizacoes_servidores_usufruto.siape = '.$_SESSION['sMatricula'].' ');

// Busca o histórico de autorização de horas extras
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


$_SESSION['entra'] = $entra; // horário estabelecido de entrada ao serviço
$_SESSION['sai']   = $sai;   // horário estabelecido de saída (fim do expediente)
$_SESSION['iniin'] = $iniin; // horário estabelecido do início do almoço
$_SESSION['fimin'] = $fimin; // horário estabelecido do término do almoço
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
# verifica se o usuario está como substituto.
# se o período expirou cancela a permissao
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

    <!-- Mensagem para o Usuário -->
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
            <div><i>Saída para Almoçar:</i> Alt+2. <i>Retorno do Almoço:</i> Alt+3. <i>Fim do Expediente:</i> Alt+4. <i>Solicitar trabalhar em dia não útil:</i> Alt+5. <i>Visualizar frequências do mês:</i> Alt+6. <i>Visualizar frequências anteriores:</i> Alt+7. <i>Visualizar saldos de compensações:</i> Alt+8.</div>
        </div>
        <div class="row" style='width:100%;padding-bottom:50px;'></div>

    <?php endif; ?>

    <!-- Linha referente aos horários -->
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

    <!-- Row Referente aos dados dos funcionários  -->
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
                <h5><strong>LOTAÇÃO</strong></h5>
                <p><?= tratarHTML($lotReal); ?></p>
            </div>
        </div>
    </div>
 <div class="row margin-10">
        <div class="subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Minhas Autorizações</strong></h4>
        </div>
        <div class="col-md-2" style="text-align: center;">
            <strong>COMPENSAÇÃO</strong>
            <?= $bhoras == "S" ? "<div style='text-align: center; '>AUTORIZADA</div>" : "<div class='red' style='color:red;'>NÃO AUTORIZADA</div>"; ?>
        </div>
        <div class="col-md-3" style="text-align: center;" >
            <strong>REGISTRO FORA DO<br>HORÁRIO DO SETOR</strong>
            <div id="at-registro-fora-horario" style="text-align: center;"><?php echo $aut == 'S' ? 'AUTORIZADO' : 'NÃO AUTORIZADO'; ?></div>
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
                            echo "Não possuí";
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
                            echo "Não possuí";
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
                            echo "Não possuí";
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
                    <strong>HORÁRIO DO SETOR<?= ($sTurnoEstendido == 'S' ? ' - Unidade com Turno Estendido' : ''); ?></strong>
                </h5>
                <p>
                    <strong><?= tratarHTML($ini); ?></strong> às <strong><?= tratarHTML($fim); ?></strong>
                </p>
            </div>
            <div class="col-md-3">
                <h5><strong>HORA DE ENTRADA</strong></h5>
                <p><?= tratarHTML($entra); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>INTERVALO</strong></h5>
                <p><?= tratarHTML($iniin) . " às " . tratarHTML($fimin); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>HORA DE SAÍDA</strong></h5>
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
                <h4>HORÁRIOS DO SERVIDOR - <?= tratarHTML($hoje); ?></h4>
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
                    echo ($iniint == '00:00:00' ? "<a id='iniciar_almoco' class='btn btn-primary btn-md' href=\"javascript:confirma ('Deseja realmente registrar o início do intervalo?','entrada2.php')\"><span class=\"glyphicon glyphicon-flag\"></span>  Iniciar </a>" : "");
                }

                echo tratarHTML(' ' . $iniint . ' às ' . $fimint . 'h ');

                if ($jornada == '08:00' || $bhoras == 'S' || $ocupaFuncao == 'S')
                {
                    echo ($fimint == '00:00:00' ? "<a id='finalizar_almoco' class='btn btn-danger btn-md' href=\"javascript:confirma ('Deseja realmente registrar o retorno do intervalo?','entrada3.php')\"><span class=\"glyphicon glyphicon-remove\"></span>  Finalizar</a>" : "");
                }
                ?>
            </p>
        </div>
        <div class="col-md-3">
            <h5><strong>SAÍDA</strong></h5>
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
                </div> Solicitação para trabalho<br>em dia não útil
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-info btn-block"  onclick="window.open('pontoser.php?cmd=1');"><div class="btn-icon">
                    <span class="glyphicon glyphicon-eye-open"></span>
                </div> Visualizar frequência<br>do mês</button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-warning btn-block" onclick="window.open('entrada8.php?cmd=2&orig=1');"><div class="btn-icon">
                    <span class="glyphicon glyphicon-book"></span>
                </div> Visualizar meses<br>anteriores</button>
        </div>
        <div class="col-md-12 text-center margin-10">
            <a class="demonstrativo" href="entrada9.php" target="new">Visualizar demonstrativo de compensações.</a>
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
<!-- Modal de autorização de acumulo-->
<div class="modal fade" id="autorizacoes-acumulo" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Autorizações de acumulo de banco de horas</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Ciclo</th>
                            <th>Data de Início</th>
                            <th>Data de Término</th>
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
                                <td colspan="3"><i>Nenhuma autorização de acumulo registrada.</i></td>
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


<!-- Modal de autorização de hora extra-->
<div class="modal fade" id="autorizacoes-hora-extra" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Autorizações de Horas Extras</h4>
            </div>
            <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Data de Início</th>
                                <th>Data de Término</th>
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
                                <td colspan="3"><i>Nenhuma autorização de hora extra registrada.</i></td>
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

<!-- Modal de autorização de usufruto-->
<div class="modal fade" id="autorizacoes-usufruto" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Autorizações de usufruto de banco de horas</h4>
            </div>
            <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ciclo</th>
                                <th>Data de Início</th>
                                <th>Data de Término</th>
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
                                <td colspan="3"><i>Nenhuma autorização de usufruto registrada.</i></td>
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
# ou 20 min depois do horário de entrada definido
#
//mensagemHorarioDifere(
//    $sMatricula, 'limite_entrada', $sTurnoEstendido, $ocupaFuncao, $sLotacao, $situacao_cadastral, $entra, $sHoraEntrada, $sDiaUtil
//);

// Base do formulário
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
 * |                se for cedido ou fixado (??) não registra    |
 * |                frequência, a informação é encaminhado pelo  |
 * |                pelo orgão requisitante para o SOGP lançar.  |
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
            registraLog('entrada não foi registrada (SITCAD indefinido)');
            retornaErro('entrada.php', 'Você não pode registrar frequência no SISREF, situação cadastral indefinida!');
            break;

        case '08':
        //case '18':
            // grava o LOG
            registraLog('entrada não foi registrada (CEDIDO)');
            retornaErro('entrada.php', 'Você não pode registrar frequência no SISREF por estar cedido ou fixado!');
            break;

        default:
            break;
    }
}


/**
 * @info Se o usuário não registrou entrada antes, sistema carrega dados
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

