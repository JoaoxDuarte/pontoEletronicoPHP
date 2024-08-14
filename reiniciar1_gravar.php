<?php

include_once( "config.php" );
include_once( "src/controllers/TaUsuariosController.php" );

// dados - formulario
$siape = anti_injection($_REQUEST['siape']);
$cpf   = limpaCPF_CNPJ(anti_injection($_REQUEST['cpf']));

$siape = getNovaMatriculaBySiape($siape);


// resulado da operação
$result   = array();

// validação
validaMatriculaSiape( $siape );
validaCodigoCPF( $cpf );


// dados do servidor
$oServativ = verificaSeExisteServidor($siape, $cpf);
$email     = $oServativ->email;
$ssenhat   = substr(md5($oServativ->dt_nasc), 0, 14);

atualizaSenhaUsuario($siape, $ssenhat, $email, $oServativ);

exit();



/* *********************************************************
 *                                                         *
 *                 FUNÇÕES COMPLEMENTARES                  *
 *                                                         *
 ********************************************************* */

/**
 *
 * @param string $siape
 */
function validaMatriculaSiape( $siape )
{
    // validacao dos campos
    $validacao = new valida();
    $validacao->setExibeMensagem(false);
    $validacao->setDestino($_SESSION['sHOrigem_2']);

    $validacao->siape( $siape );
    $mensagem = $validacao->getMensagem();

    // valida os dados
    if ($mensagem != '')
    {
        $result[0] = array(
            'siape'    => '',
            'mensagem' => utf8_iso88591(". Matrícula informada, inválida!")
        );
        retornaInformacao($result, "danger");
    }
}

/**
 *
 * @param string $cpf
 */
function validaCodigoCPF( $cpf )
{
    if (validaCPF($cpf) == false)
    {
        $result[0] = array(
            'siape'    => '',
            'mensagem' => utf8_iso88591(". CPF informado, inválido!")
        );
        retornaInformacao($result, "danger");
    }
}

/**
 *
 * @param string $siape
 * @param string $cpf
 * @return object
 */
function verificaSeExisteServidor($siape, $cpf)
{
    global $pagina_de_destino;

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($pagina_de_destino);
    $oDBase->setMensagem("Problema no acesso ao banco de dados,\\npor favor tente outra vez!");

    $oDBase->query("
    SELECT 
        DATE_FORMAT(a.dt_nasc, '%d%m%Y') AS dt_nasc, 
        a.email,
        a.nome_serv,
        a.cod_lot,
        tabsetor.upag,
        a.defvis
    FROM 
        servativ AS a
    LEFT JOIN 
        tabsetor ON a.cod_lot = tabsetor.codigo
    WHERE   
        a.cpf = :cpf
        AND a.mat_siape = :siape
        AND a.excluido='N'
    ",
    array(
        array( ':cpf',   $cpf,   PDO::PARAM_STR ),
        array( ':siape', getNovaMatriculaBySiape($siape), PDO::PARAM_STR ),
    ));

    if ($oDBase->num_rows() == 0)
    {
        $msg_erro = "Servidor não localizado, Matrícula e CPF inválidos!";
        $result[0] = array(
            'siape'    => $siape,
            'mensagem' => utf8_iso88591($msg_erro)
        );

        retornaInformacao($result,$severidade);
    }

    return $oDBase->fetch_object();
}

/**
 *
 * @global string $pagina_de_destino
 *
 * @param string $siape
 * @param string $ssenhat
 * @param string $email
 */
function atualizaSenhaUsuario($siape, $ssenhat, $email, $dados)
{
    global $pagina_de_destino;

    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Erro no acesso ao cadastro de Usuário!");
    $oDBase->setDestino($pagina_de_destino);

    $oDBase->query("SELECT siape FROM usuarios WHERE siape = :siape", array(
        array(':siape', $siape, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() == 0)
    {
        $oDBase->query("
        INSERT INTO usuarios
            SET
                siape  = :siape,
                nome   = :nome,
                acesso = :acesso,
                setor  = :setor,
                senha  = :senha,
                prazo  = :prazo,
                upag   = :upag,
                defvis = :defvis
        ",
        array(
            array(':siape',  getNovaMatriculaBySiape($siape),   PDO::PARAM_STR),
            array(':nome',   $dados->nome_serv, PDO::PARAM_STR),
            array(':acesso', 'NNSNNNNNNNNNN',     PDO::PARAM_STR),
            array(':setor',  $dados->cod_lot,   PDO::PARAM_STR),
            array(':senha',  $ssenhat,            PDO::PARAM_STR),
            array(':prazo',  '1',                 PDO::PARAM_STR),
            array(':upag',   $dados->upag,      PDO::PARAM_STR),
            array(':defvis', $dados->defvis,    PDO::PARAM_STR),
        ));
    }
    else
    {
        $oDBase->query("
        UPDATE usuarios
            SET senha = :senha, prazo = '1'
                WHERE siape = :siape
        ",
        array(
            array( ':senha', $ssenhat,   PDO::PARAM_STR ),
            array( ':siape', getNovaMatriculaBySiape($siape), PDO::PARAM_STR ),
        ));
    }

    // grava o LOG
    registraLog("reinicializou a senha do usuário ".getNovaMatriculaBySiape($siape)." na máquina de IP ".getIpReal());
    // fim do LOG
    
    enviarEmail(
        $email,
        'REINICIALIZACAO DE SENHA',
        "<br><br><big>Prezado servidor,<br>Informamos que sua senha foi "
        . "reinicializada passando a partir desse momento a ser sua "
        . "<b>data de nascimento<b>, sendo obrigatória a troca desta senha"
        . " no primeiro acesso.<br>Caso não tenha efetuado esta solicitação"
        . " altere imediatamente sua senha."
        . "<br> Atenciosamente<br> SISREF.</big><br><br>"
    );

    $msg_erro   = "Senha reiniciada com sucesso!";
    $severidade = 'success';

    $result[0] = array(
        'siape'    => $siape,
        'mensagem' => utf8_iso88591($msg_erro)
    );

    retornaInformacao($result, $severidade);
}
