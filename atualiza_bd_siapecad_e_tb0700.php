<?php

/*
 * Atualiza as tabelas CADMES (SIAPE) e TB0700 (SDC) no SISREF.
 *   - Tabela cadmes, atualiza dados cadastrais no SISREF;
 *   - Tabela unidade_organica (tb0700), atualiza dados de unidades no SISREF;
 *   - Tabela feriados e ponto facultativo (tb0700), atualiza dados de feriados no SISREF.
 *
 * Atualiza as tabelas utilizadas por usuários externos.
 *   - Tabela vw_sisrefsae, uso do sistema SISAGE/SAE (Sistema de Agendamento Eletrônico);
 *   - Tabela vw_dirat, uso dos sistemas da DIRAT (Diretoria de Atendimento);
 *   - Tabela vw_smpm, uso dos sistemas da DIRSAT (Diretoria de Saúde do Trabalhador).
 */

include_once( '../../sisref/inc/email_lib.php' );
include_once( '../../sisref/config.php' );

// Define limite duração do processo
set_time_limit(108000);

/*
 * Atualiza CADMES e TB0700
 */
$atualiza = new AtualizaTb0700SiapeNoSisref();
$atualiza->AtualizaSiapeCAD();
$atualiza->AtualizaTB0700();

exit();

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class       : ConexaoBD                                           |
 * | @description : Conecta-se ao banco de dados                        |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class ConexaoBD
{

    public $linkSISREF;
    public $linkSIAPE;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->linkSISREF = new mysqli('10.120.2.5', 'sisref_crontab', 'SisReF2015crontab', 'siapecad');
        if ($this->linkSISREF->connect_error)
        {
            comunicaErro("Falha na conexão com Banco de Dados:<br> (" . $this->linkSISREF->connect_errno . ') ' . $this->linkSISREF->connect_error);
            exit();
        }
        else
        {
            // Conexão com o banco de dados SIAPE
            $this->linkSIAPE = new mysqli('10.120.3.17', 'sisref_atualizar', 'sis!ref@atualizar', 'siape');
            if ($this->linkSIAPE->connect_error)
            {
                comunicaErro("Falha na conexão com Banco de Dados:<br> (" . $this->linkSIAPE->connect_errno . ') ' . $this->linkSIAPE->connect_error);
                exit();
            }
        }

    }

}

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class    : AtualizaTb0700SiapeNoSisref                            |
 * | @description : Atualização BD SIAPECAD e TB0700 no SISREF com      |
 * |                dados do SIAPE (CADMES) e do SDC (TB0700)           |
 * |                                                                    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class AtualizaTb0700SiapeNoSisref extends ConexaoBD
{

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->conexao = new ConexaoBD();

    }

    // Atualização CADMES do DB SIAPECAD (SISREF)
    public function AtualizaSiapeCAD()
    {
        // ultima atualizacao do CADMES (SIAPE) no SISREF
        $sql               = 'SELECT Update_time FROM information_schema.tables WHERE table_schema = "siapecad" AND table_name = "cadmes" ';
        $rsSISREF          = $this->conexao->linkSISREF->query($sql);
        $dados             = $rsSISREF->fetch_assoc();
        $atualizado_sisref = $dados['Update_time'];
        #print 'SISREF: '.$atualizado_sisref.'<br>'.$sql.'<br>';
        // Base SIAPE
        $sql               = 'USE siape';
        $this->conexao->linkSIAPE->query($sql);
        #print $sql.'<br>';

        $sql              = 'SELECT Update_time FROM information_schema.tables WHERE table_schema = "siape" AND table_name = "cadmes" ';
        $rsSIAPE          = $this->conexao->linkSIAPE->query($sql);
        $dados            = $rsSIAPE->fetch_assoc();
        $atualizado_siape = $dados['Update_time'];
        #print 'SIAPE: '.$atualizado_siape.'<br>'.$sql.'<br>';

        if ($atualizado_siape > $atualizado_sisref)
        {
            $sql     = 'TRUNCATE TABLE siapecad.cadmes ';
            $this->conexao->linkSISREF->query($sql);
            #print $sql.'<br>';
            // lendo CADMES (SIAPE)
            $sql     = 'SELECT * FROM siape.cadmes ORDER BY siape ';
            $rsSIAPE = $this->conexao->linkSIAPE->query($sql);
            #print $sql.'<br>';

            $contar = 0;

            while ($cadmes = $rsSIAPE->fetch_assoc())
            {
                $sql = 'INSERT siapecad.cadmes SET ';
                while (list($key, $val) = each($cadmes))
                {
                    $sql .= $key . ' = "' . strtr(trim($val), array("'" => "`", '"' => "`", '/' => "", '\\' => "")) . '", ';
                }
                $sql = substr($sql, 0, -2);
                $this->conexao->linkSISREF->query($sql);
                #print $sql.'<br>';
            }
        }

    }

    // Atualização TB0700 do SDC para o SISREF
    public function AtualizaTB0700()
    {
        $tabelas = array('abrangencia', 'aps', 'feriadoestadual', 'feriadomunicipal', 'feriadonacional', 'municipios', 'pontofacultativoestadual', 'pontofacultativomunicipal', 'pontofacultativonacional', 'unidadeorganica');

        $nlin = count($tabelas);

        for ($i = 0; $i < $nlin; $i++)
        {
            $sql     = 'TRUNCATE TABLE tb0700.' . $tabelas[$i];
            $this->conexao->linkSISREF->query($sql);
            #print $sql.'<br>';
            // lendo tabela em TB0700 (SIAPE)
            $sql     = 'SELECT * FROM tb0700.' . $tabelas[$i] . ' ';
            $rsSIAPE = $this->conexao->linkSIAPE->query($sql);
            #print $sql.'<br>';

            while ($campos = $rsSIAPE->fetch_assoc())
            {
                $sql = 'INSERT tb0700.' . $tabelas[$i] . ' SET ';
                while (list($key, $val) = each($campos))
                {
                    $sql .= $key . ' = "' . strtr(trim($val), array("'" => "`", '"' => "`", '/' => "", '\\' => "")) . '", ';
                }
                $sql = substr($sql, 0, -2);
                $this->conexao->linkSISREF->query($sql);
                #print $sql.'<br>';
            }
        }

    }

}
