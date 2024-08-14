<?php

$SIAPE_WSDL              = "https://www1.siapenet.gov.br/WSSiapenet/services/ConsultaSIAPE?wsdl";
$SIAPE_LOCATION          = "https://www1.siapenet.gov.br/WSSiapenet/services/ConsultaSIAPE";
$SIAPE_SIGLA_SISTEMA     = "SISREF";
$SIAPE_NOME_SISTEMA      = utf8_encode("Sistema de Registro Eletrônico de Frequência - ME");
$SIAPE_SENHA             = "RQ1HDLA";
$SIAPE_COD_ORGAO         = "''";
$SIAPE_PARM_EXIST_PAG    = "b";
$SIAPE_PARM_TIPO_VINCULO = "c";

    return array(
        'wsdl'            => $SIAPE_WSDL,
        'location'        => $SIAPE_LOCATION,
        'siglaSistema'    => $SIAPE_SIGLA_SISTEMA,
        'nomeSistema'     => $SIAPE_NOME_SISTEMA,
        'senha'           => $SIAPE_SENHA,
        'codOrgao'        => $SIAPE_COD_ORGAO,
        'parmExistPag'    => $SIAPE_PARM_EXIST_PAG,
        'parmTipoVinculo' => $SIAPE_PARM_TIPO_VINCULO
    );

/* 
    return array(
        'wsdl'            => getenv('SIAPE_WSDL'),
        'location'        => getenv('SIAPE_LOCATION'),
        'siglaSistema'    => getenv('SIAPE_SIGLA_SISTEMA'),
        'nomeSistema'     => getenv('SIAPE_NOME_SISTEMA'),
        'senha'           => getenv('SIAPE_SENHA'),
        'codOrgao'        => getenv('SIAPE_COD_ORGAO'),
        'parmExistPag'    => "b" //getenv('SIAPE_PARM_EXIST_PAG'),
        'parmTipoVinculo' => getenv('SIAPE_PARM_TIPO_VINCULO')
    );
*/