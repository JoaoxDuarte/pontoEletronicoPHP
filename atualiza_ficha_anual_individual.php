<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Acerto da Ficha Anual                        |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// formulario padrao para siape e competencia
include_once("class_form.siape.competencia.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sLog');


## classe para montagem do formulario padrao
#
$oForm = new formSiapeCompetencia; // instancia o formulário
//	$oForm->setJS( "atualiza_ficha_anual_individual.js" ); // script extras utilizados pelo formulario
$oForm->setSiapeCompetenciaDestino("atualiza_ficha_anual_individual_grava.php"); // pagina de destino (action)
$oForm->setSiapeCompetenciaValidar("javascript:return validar();"); // script de teste dos dados do formulário e envio (onSubmit)
$oForm->setSeparador(30);

$oForm->setCaminho('Utilitários » Gestores » Acertar Ficha Anual'); // localizacao deste formulario
$oForm->setSubTitulo("Acerto da Ficha Anual"); // sub-titulo principal

$oForm->setOnLoad("$('#pSiape').focus();");

// campos hidden
$oForm->initInputHidden();
$oForm->setInputHidden("an", date('Y')); // ano da data atual
// definicao de campos
$oForm->setSiapeNome("siape"); // matricula para consulta
$oForm->setMesNome("mes"); // mes da consulta
$oForm->setMesTitulo('');
$oForm->setAnoNome("ano"); // ano da consulta
$oForm->setAnoTitulo('');

// exibe o formulario
$oForm->exibeForm();
?>
<script>

    function validar(soUm)
    {
        // objeto mensagem
	oTeste = new alertaErro();
	oTeste.init();

        // dados
        var siape = $('#siape');
        var mes   = $('#mes');
        var ano   = $('#ano');

        var soUm     = (soUm == null ? '' : soUm);
        var mensagem = '';

        // validacao do campo siape
        // testa o tamanho
        mensagem = validaSiape(siape.val());
        if (mensagem != '')
        {
            oTeste.setMsg(mensagem, siape);
        }

        // testa se o mes informado contem dois digitos
        // e se é um mes válido
        if (soUm == '' || soUm == 'mes')
        {
            mensagem = validaMes(mes.val());
            if (mensagem != '')
            {
                oTeste.setMsg(mensagem, mes);
            }
        }

        // testa se o ano informado contem quatro digitos
        // se não é menor que 2009, e se não é maior que o ano atual
        if (soUm == '' || soUm == 'ano')
        {
            mensagem = validaAno(ano.val(), mes.val());
            if (mensagem != '')
            {
                oTeste.setMsg(mensagem, ano);
            }
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

	return bResultado;
    }

    //
    // Verifica se atingiu o tmaanho do campo para passar para o próximo
    // aproveitamos para validar a matricula e o mes
    //
    function ve(parm1)
    {
        var siape = $('#siape');
        var mes   = $('#mes');
        var ano   = $('#ano');

        if (ano.val().length >= 4)
        {
            ano.focus();
            validar('ano');
        }
        else if (mes.val().length >= 2)
        {
            ano.focus();
            validar('mes');
        }
        else if (siape.val().length >= 7)
        {
            mes.focus();
            validar('siape');
        }
    }

</script>