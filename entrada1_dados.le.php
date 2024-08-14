<?php

/**
 * +-------------------------------------------------------------+
 * |                                                             |
 * | SISREF - Banco de dados                                     |
 * |                                                             |
 * | @package    : Le dados do banco de dados                    |
 * | @copyright  : (C) 2004-2013 INSS                            |
 * | @license    :                                               |
 * | @link       : http://www-sisref                             |
 * | @subpackage :                                               |
 * | @author     : Edinalvo Rosa                                 |
 * | @version    : Edinalvo Rosa                                 |
 * |                                                             |
 * +-------------------------------------------------------------+
 * */
##
#  LE OS DADOS DO USUARIO
#  BD USUARIOS, SERVATIV E SETOR
##
// dados do servidor/estagiário
//(Base: USUARIO)

$defvis                = $oUsuario->defvis;      // devidiente visual
$prazo                 = $oUsuario->prazo;       // se 1 solicita a troca da senha
$troca_senha           = $oUsuario->troca_senha; // se 1 solicita a troca da senha (senha igual a data de nascimento)
$magico                = $oUsuario->magico;      // usado para indicar a dispensa do registro de frequência
$sSenha                = $oUsuario->senha;       // senha de acesso
$sPrivilegio           = $oUsuario->privilegio;  // uso futuro
$sTripa                = $oUsuario->acesso;      // permissões de acesso
//(Base: SERVATIV)
$sNome                 = ($oUsuario->flag ==FALSE ? $oUsuario->nome : $oUsuario->nomesocial);     // nome do servidor
$identificacao_apelido = $oUsuario->identificacao_apelido; // identificacao ou apelido
$sitcad                = $oUsuario->cod_sitcad; // situação cadastral
$entra                 = $oUsuario->entra_trab; // horário estabelecido de entrada ao serviço
$sai                   = $oUsuario->sai_trab;   // horário estabelecido de saída (fim do expediente)
$iniin                 = $oUsuario->ini_interv; // horário estabelecido do início do almoço
$fimin                 = $oUsuario->sai_interv; // horário estabelecido do término do almoço
$aut                   = $oUsuario->autchef;    // autorização da chefia para trabalho após o horário da unidade
$bhoras                = $oUsuario->bhoras;     // autorização da chefia para registro de horas de compensação
$horae                 = $oUsuario->horae;      // registro de horário especial (deficiente, estudante, etc...)
$motivo                = $oUsuario->motivo;     // motivo do horário especial
$chefe                 = $oUsuario->chefia;     // indica se o servidor ocupa função ou está respondendo pela mesma (titular, substituto ou por delegação)
$jnd                   = $oUsuario->jornada;    // jornada do servidor
$j                     = formata_jornada_para_hhmm($oUsuario->jornada);
$nome_social           = $oUsuario->nomesocial;

$dtAdm              = $oUsuario->dt_adm; // data da admissao invertida Ex. 02/02/2012 -> 20120202
// - Indica quem possui jornada menor que 40 horas semanais,
//   independente do turno estendido
//
$jornadaMenor8horas = ($jnd < 40);

//(Base: SETOR)
$sLotacao          = $oUsuario->setor;        // unidade de lotação do servidor
$lotacao_descricao = $oUsuario->descricao;    // descrição do código da unidade
$orgao_descricao   = $oUsuario->denominacao;  // descrição do código do órgão
$orgao_sigla       = $oUsuario->sigla;        // sigla do órgão
$uorg              = $oUsuario->uorg;         // unidade organizacional - SIAPE
$upag              = $oUsuario->upag;         // unidade pagadora (única por gerência) - SIAPE
$ini               = $oUsuario->inicio_atend; // Horário de início do atendimento da unidade
$fim               = $oUsuario->fim_atend;    // Horário de término do atendimento da unidade
$codmun            = $oUsuario->codmun;       // Código do munícipio
