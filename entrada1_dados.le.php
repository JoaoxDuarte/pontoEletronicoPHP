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
// dados do servidor/estagi�rio
//(Base: USUARIO)

$defvis                = $oUsuario->defvis;      // devidiente visual
$prazo                 = $oUsuario->prazo;       // se 1 solicita a troca da senha
$troca_senha           = $oUsuario->troca_senha; // se 1 solicita a troca da senha (senha igual a data de nascimento)
$magico                = $oUsuario->magico;      // usado para indicar a dispensa do registro de frequ�ncia
$sSenha                = $oUsuario->senha;       // senha de acesso
$sPrivilegio           = $oUsuario->privilegio;  // uso futuro
$sTripa                = $oUsuario->acesso;      // permiss�es de acesso
//(Base: SERVATIV)
$sNome                 = ($oUsuario->flag ==FALSE ? $oUsuario->nome : $oUsuario->nomesocial);     // nome do servidor
$identificacao_apelido = $oUsuario->identificacao_apelido; // identificacao ou apelido
$sitcad                = $oUsuario->cod_sitcad; // situa��o cadastral
$entra                 = $oUsuario->entra_trab; // hor�rio estabelecido de entrada ao servi�o
$sai                   = $oUsuario->sai_trab;   // hor�rio estabelecido de sa�da (fim do expediente)
$iniin                 = $oUsuario->ini_interv; // hor�rio estabelecido do in�cio do almo�o
$fimin                 = $oUsuario->sai_interv; // hor�rio estabelecido do t�rmino do almo�o
$aut                   = $oUsuario->autchef;    // autoriza��o da chefia para trabalho ap�s o hor�rio da unidade
$bhoras                = $oUsuario->bhoras;     // autoriza��o da chefia para registro de horas de compensa��o
$horae                 = $oUsuario->horae;      // registro de hor�rio especial (deficiente, estudante, etc...)
$motivo                = $oUsuario->motivo;     // motivo do hor�rio especial
$chefe                 = $oUsuario->chefia;     // indica se o servidor ocupa fun��o ou est� respondendo pela mesma (titular, substituto ou por delega��o)
$jnd                   = $oUsuario->jornada;    // jornada do servidor
$j                     = formata_jornada_para_hhmm($oUsuario->jornada);
$nome_social           = $oUsuario->nomesocial;

$dtAdm              = $oUsuario->dt_adm; // data da admissao invertida Ex. 02/02/2012 -> 20120202
// - Indica quem possui jornada menor que 40 horas semanais,
//   independente do turno estendido
//
$jornadaMenor8horas = ($jnd < 40);

//(Base: SETOR)
$sLotacao          = $oUsuario->setor;        // unidade de lota��o do servidor
$lotacao_descricao = $oUsuario->descricao;    // descri��o do c�digo da unidade
$orgao_descricao   = $oUsuario->denominacao;  // descri��o do c�digo do �rg�o
$orgao_sigla       = $oUsuario->sigla;        // sigla do �rg�o
$uorg              = $oUsuario->uorg;         // unidade organizacional - SIAPE
$upag              = $oUsuario->upag;         // unidade pagadora (�nica por ger�ncia) - SIAPE
$ini               = $oUsuario->inicio_atend; // Hor�rio de in�cio do atendimento da unidade
$fim               = $oUsuario->fim_atend;    // Hor�rio de t�rmino do atendimento da unidade
$codmun            = $oUsuario->codmun;       // C�digo do mun�cipio
