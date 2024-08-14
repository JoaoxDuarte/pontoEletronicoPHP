/*
 Milonic DHTML Menu
 Written by Andy Woolley
 Copyright 2002 (c) Milonic Solutions. All Rights Reserved.
 Plase vist http://www.milonic.co.uk/menu or e-mail menu3@milonic.com
 You may use this menu on your web site free of charge as long as you place prominent links to http://www.milonic.co.uk/menu and
 your inform us of your intentions with your URL AND ALL copyright notices remain in place in all files including your home page
 Comercial support contracts are available on request if you cannot comply with the above rules.

 Please note that major changes to this file have been made and is not compatible with versions 3.0 or earlier.

 You no longer need to number your menus as in previous versions.
 The new menu structure allows you to name the menu instead, this means that you can remove menus and not break the system.
 The structure should also be much easier to modify, add & remove menus and menu items.

 If you are having difficulty with the menu please read the FAQ at http://www.milonic.co.uk/menu/faq.php before contacting us.

 Please note that the above text CAN be erased if you wish as long as copyright notices remain in place.
 */


function alerta(msg)
{
    alert(msg);
}

function replaceLink(link, sFrame)
{
    var sFrame = (sFrame == null ? 'main' : sFrame);
    if (sFrame == 'main')
    {
        parent.main.location.replace(link);
    }
    else
    {
        window.location.replace(link);
    }
}

function openPDF(link, modo, jan)
{
    var link = (link == null ? '' : link);
    var modo = (modo == null ? 1 : modo);
    var jan = (jan == null ? 'jan' : jan);
    if (link != '')
    {
        //link += '?modo='+modo;
        var jan = window.open(link, jan, 'top=0,left=0,width=799,height=700');
    }
}


//The following line is critical for menu operation, and MUST APPEAR ONLY ONCE. If you have more than one menu_array.js file rem out this line in subsequent files
menunum = 0;
menus = new Array();
_d = document;
function addmenu()
{
    menunum++;
    menus[menunum] = menu;
}
function dumpmenus()
{
    mt = "<script language=javascript>";
    for (a = 1; a < menus.length; a++)
    {
        mt += " menu" + a + "=menus[" + a + "];"
    }
    mt += "<\/script>";
    _d.write(mt)
}
//Please leave the above line intact. The above also needs to be enabled if it not already enabled unless this file is part of a multi pack.



////////////////////////////////////
// Editable properties START here //
////////////////////////////////////


// Special effect string for IE5.5 or above please visit http://www.milonic.co.uk/menu/filters_sample.php for more filters
if (navigator.appVersion.indexOf("MSIE 6.0") > 0)
{
    effect = "Fade(duration=0.2);Alpha(style=0,opacity=88);Shadow(color='#777777', Direction=135, Strength=5)"
}
else
{
    effect = "Shadow(color='#777777', Direction=135, Strength=5)"
}


timegap = 500					// The time delay for menus to remain visible
followspeed = 5				// Follow Scrolling speed
followrate = 40				// Follow Scrolling Rate
suboffset_top = 3;			// Sub menu offset Top position
suboffset_left = -2;			// Sub menu offset Left position
Frames_Top_Offset = 0 		// Frames Page Adjustment for Top
Frames_Left_Offset = 0		// Frames Page Adjustment for Left

var bSistemaCorAzul = (bSistemaCorAzul == null ? false : bSistemaCorAzul);
var off_back_color = (bSistemaCorAzul == false ? "669966" : "#4894D9"); //'#2367A4'
var on_back_color = (bSistemaCorAzul == false ? "336600" : '#2367A4');

plain_style = [// Menu Properties Array
    "FFFFFF", // Off Font Color
    off_back_color, // Off Back Color
    "FFEBCD", // On Font Color
    on_back_color, // On Back Color
    "B9D0B9", // Border Color
    10, // Font Size
    "normal", // Font Style
    "", // Font Weight
    "Verdana, Tahoma, Arial, Helvetica", // Font
    4, // Padding
    "imagem/arrow.gif"					// Sub Menu Image
        , // 3D Border & Separator
    , "66ccff"					// 3D High Color
        , "000099"					// 3D Low Color
]

//---------------------------------------------------------------------------//
//                                                                           //
// CADASTRO                                                                  //
//                                                                           //
//---------------------------------------------------------------------------//

addmenu(menu = ["cadastro"
        , 150, , 100, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "<font color='#FFEBCD'>Funcional</font>", "show-menu=funcional", , "Cadastro de servidores e estagiários", 0
        , "<font color='#FFEBCD'>Gerencial</font>", "show-menu=gerencial", , "Cadastro de ocupantes de função", 0
        , "<font color='#FFEBCD'>Movimentação</font>", "show-menu=movimentar", , "Movimentar servidores", 0
])

// CADASTRO -> FUNCIONAL
addmenu(menu = ["funcional"
        , , , 65, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Consultar</font>", "javascript:replaceLink(\"cadastro_consulta.php\")", , "Consultar cadastro de servidores e/ou estagiários", 0
        , "<font color='#FFEBCD'>Incluir</font>", "incluiserv.php", , "Incluir servidores e/ou estagiários", 0
        , "<font color='#FFEBCD'>Alterar</font>", "cadastro_alteracao.php", , "Alterar cadastro de servidores e/ou estagiários", 0
        , "<font color='#FFEBCD'>Excluir</font>", "show-menu=excluir", , "Excluir servidores e/ou estagiários", 0
])
addmenu(menu = ["excluir"
        , , , 112, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Efetivar Exclusão</font>", "cadastro_exclusao.php", , "Exclui servidor/estagiário do cadastro", 0
        , "<font color='#FFEBCD'>Cancelar Exclusão</font>", "cadastro_exclusao_cancela.php", , "Cancela exclusao por erro", 0
])

// CADASTRO -> GERENCIAL
addmenu(menu = ["gerencial"
        , , , 135, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Ocupar Funções</font>", "incfuncserv.php", , "Registrar Exercício de Funções", 0
        , "<font color='#FFEBCD'>Vagar Funções</font>", "excfuncserv.php", , "Registrar Vacância de Funções", 1
        , "<font color='#FFFFFF'>Efetivar Substituição</font>", "subsfuncinf.php", , "Registrar Efetiva Substituição", 0
        , "<font color='#FFFFFF'>Delegar Atribuição</font>", "show-menu=delegar", , "Delegar atividades do SISREF", 1
        , "<font color='#FFEBCD'>Alterar Registro</font>", "altfuncserv.php", , "Alterar Registrar de Funções", 0
        , "<font color='#FFEBCD'>Manter Histórico</font>", "manutencao_historico_funcao.php", , "Manutenção de Histórico de Funções do Servidor", 0
])
addmenu(menu = ["delegar"
        , , , 125, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "Registrar Delegação", "delegacao.php?modo=10", , "Delegar atividades do SISREF", 0
        , "Cancelar Delegação", "delegacao.php?modo=9", , "Cancelamento de delegação", 0
])

// CADASTRO -> MOVIMENTAR
addmenu(menu = ["movimentar"
        , , , 115, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Alterar Lotação</font>", "movserv.php", , "Movimentar servidores e estagiários", 0
        //,"<font color='#FFEBCD'>Alterar Localização</font>","localserv.php",,"Localizar servidores e estagiários",0
        , "<font color='#FFEBCD'>Mudar UPAG</font>", "show-menu=upag", , "Transferir servidor a outra upag.", 0
        , "<font color='#FFEBCD'>Manter Histórico</font>", "peshistlot.php", , "Manutenção de Histórico de lotação", 0
])
addmenu(menu = ["upag"
        , , , 115, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Liberar</font>", "liberupag.php", , "Libera servidor a outra upag", 0
        , "<font color='#FFEBCD'>Cancelar Liberação</font>", "canliberupag.php", , "Cancela liberação de servidor", 0
        , "<font color='#FFEBCD'>Receber</font>", "recupag.php", , "Recebe servidor de outra upag", 0
])


//---------------------------------------------------------------------------//
//                                                                           //
// FREQUENCIA                                                                //
//                                                                           //
//---------------------------------------------------------------------------//

addmenu(menu = ["frequencia"
        , 200, , 190, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "<font color='#FFEBCD'>Gerencial</font>", "regfreqger_entra.php", , "Acompanhar unidades", 1
        , "<font color='#FFEBCD'>Acompanhar</font>", "frequencia_acompanhar_entra.php", , "Acompanhar Frequência", 0
        , "<font color='#FFEBCD'>Homologar</font>", "frequencia_homologar_entra.php", , "Registro de Frequência", 0
        , "Autorização de Trabalho", "autorizacao_trabalho_dia_nao_util_entra.php", , "Solicitações de trabalho em dia não util", 1
        , "<font color='#FFEBCD'>Visualizar</font>", "show-menu=Visualizar", , "Consulta registro de Frequência", 1
        , "<font color='#FFEBCD'>RH Atualizar</font>", "show-menu=Manter", , "Manutenção do registro de Frequência", 0
])

// FREQUENCIA -> VISUALIZAR
addmenu(menu = ["Visualizar"
        , , , 190, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Folha de Frequência", "pontoser.php?cmd=1", , "Folha de Frequência do servidor", 0
        , "Ficha de Frequência - Resumo Anual", "ficha_de_frequencia_resumo_anual.php", , "Ficha de Frequência do Servidor", 1
        , "Consulta Frequência", "veponto.php", , "Consultar folha de Frequência do servidor", 0
        , "Consulta Extrato Frequência", "entrada9_individual.php?saldo=1", , "Consultar Extrato Frequência do servidor", 1
        , "Consulta Compensações - Matrícula (Recessos / Copa do Mundo 2014)", "entrada9_individual.php?saldo=2", , "Consultar Compensações do Servidor (Recessos e Copa do Mundo 2014)", 0
        , "Consulta Compensações - Todos (Copa do Mundo 2014)", "sisref_relatorio_copa2014.php", , "Consultar Compensações do Servidor (Copa do Mundo 2014)", 0
        //,"Alteração de horário","vesolhor.php",,"Solicitações de alteração de horário de Servidor",0
])

// FREQUENCIA -> MANTER
addmenu(menu = ["Manter"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Mes Corrente</font>", "show-menu=Atual", , "", 0
        , "<font color='#FFEBCD'>Mes em Homologação</font>", "show-menu=Homol", , "", 1
        , "<font color='#FFEBCD'>Histórico</font>", "javascript:replaceLink(\"historico_frequencia.php\")", , "", 1
        , "<font color='#FFEBCD'>Desomologar</font>", "frequencia_verificar_homologados_devolucao.php?tipo=1", , "Devolver Frequência à chefia para ajuste", 0
        , "<font color='#FFEBCD'>Verificar Homologações</font>", "frequencia_verificar_homologados.php", , "Verificar se há divergências na Frequência Homologada.", 0
        //,"Ferramentas","javascript:replaceLink(\"historico_frequencia.php\")",,"",1
])
addmenu(menu = ["Atual"
        , , , 120, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Incluir Ocorrência", "freqinclui.php", , "Inclusão de registro", 0
        , "Alterar Ocorrência", "freqaltera.php", , "Alteração de registro", 0
        , "Excluir Ocorrência", "freqexclui.php", , "Exclusão de registro", 0
])
addmenu(menu = ["Homol"
        , , , 120, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Incluir Ocorrência", "freqincluih.php", , "Inclusão de registro", 0
        , "Alterar Ocorrência", "freqaltera2.php", , "Alteração de registro", 0
        , "Excluir Ocorrência", "freqexclui2.php", , "Exclusão de registro", 0
])
addmenu(menu = ["Historico"
        , , , 120, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        //,"Incluir Ocorrência","freqinclui3.php",,"Inclusão de registro",0
        , "Incluir Ocorrência", "paginamanutencao.php", , "Inclusão de registro", 0
        //,"Retificar Frequência","freqretifica.php",,"Alteração de registro",0
        , "Retificar Frequência", "paginamanutencao.php", , "Alteração de registro", 0
])


//---------------------------------------------------------------------------//
//                                                                           //
// RELATORIOS                                                                //
//                                                                           //
//---------------------------------------------------------------------------//

addmenu(menu = ["relatorios"
        , 150, , 125, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "<font color='#FFEBCD'>Frequência</font>", "show-menu=freq", , "Relatórios de Frequência", 0
        , "<font color='#FFEBCD'>Gerencial</font>", "show-menu=Relacao", , "Relatórios de funções", 0
        , "<font color='#FFEBCD'>Movimentação</font>", "show-menu=mov", , "Relatórios de movimentações", 0
])

// RELATORIOS -> FREQ
addmenu(menu = ["freq"
        , , , 170, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Por Ocorrências", "sisref_relatorio_por_ocorrencia.php", , "Relatório por Ocorrência", 0
        , "Setores Pendentes", "relfrqsetorp2.php", , "Setores pendentes de lançamento.", 0
        , "Sem Homologação", "sisref_relatorio_frequencia_nao_homologados.php", , "Servidores sem Homologação.", 0
        , "Homologados", "relfrqhomol.php", , "Verificar servidores homologados.", 0
        , "Cedidos e Descentralizados", "sisref_relatorio_cedidos_descentralizados.php", , "Verificar servidores cedidos e descentralizados.", 1
        , "<font color='#FFEBCD'>Para Comando</font>", "show-menu=cmd", , "Relatórios de comando", 1
        , "Insconsistências", "show-menu=erro", , "Relatórios de verificação", 0
])
addmenu(menu = ["cmd"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        //,"Cobrança","rlsocofreq.php",,"Servidores com Ocorrências para cobrança.",0
        , "Cobrança", "sisref_relatorio_ocorrencia_nao_compensada_competencia.php", , "Servidores com Ocorrências para cobrança.", 0
        , "Comando SIAPECAD", "sisref_relatorio_comando_siapecad_competencia.php", , "Servidores com Ocorrências para comando siapecad", 0
        , "Recesso", "sisref_relatorio_recesso_nao_compensado_competencia.php", , "Servidores que não compensaram o recesso", 0
        , "Copa do Mundo 2014", "sisref_relatorio_copa2014_nao_compensado.php", , "Servidores que não compensaram horas devidas - Copa do Mundo 2014", 0
])
addmenu(menu = ["erro"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Dias Zerados", "rlsdiazero.php", , "Servidores com dia zerado.", 0
        //,"Comando siapecad","lsocosiapcd.php",,"Servidores com Ocorrências para comando siapecad",0
])

// RELATORIOS -> RELACAO
addmenu(menu = ["Relacao"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Ocupantes de Funções", "ocupfuncserv.php", , "Consulta Ocupantes de Função", 0
        , "Historico de Funções", "histfuncserv.php", , "Histórico de Funções do Servidor", 0
        , "Historico de Ocupantes", "histfuncserv2.php", , "Histórico Ocupantes de Funções", 0
        , "<font color='#FFEBCD'>Substituições</font>", "show-menu=sub", , "Verificar substituições", 0
])
addmenu(menu = ["sub",
    , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Substituições na UPAG</font>", "sisref_relatorio_substituicoes.php", , "Consulta Substituições efetivadas", 0
        , "<font color='#FFEBCD'>Substituições do Servidor</font>", "pessubs.php", , "Consulta substituições do servidor", 0

])

// RELATORIOS -> MOV
addmenu(menu = ["mov"
        , , , 145, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Consulta Histórico</font>", "sisref_relatorio_consulta_movimentacao_servidor.php", , "Consulta histórico de movimentação", 0
        , "<font color='#FFEBCD'>Consulta Movimentação</font>", "pesmov.php", , "Consulta movimentações", 0
])


//---------------------------------------------------------------------------//
//                                                                           //
// EXTRAORDINARIO                                                            //
//                                                                           //
//---------------------------------------------------------------------------//
addmenu(menu = ["Extra"
        , 150, , 100, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "Autorização", "relautextra_entra.php", , "Autorizar extraordinário", 0
        , "Execução", "relextrahom_entra.php", , "homologar extraordinário", 0
        , "Pagamento", "paginadesen.php", , "Emitir extraordinário", 0
        //,"<font color='#FFEBCD'>Movimentação</font>","show-menu=mov",,"Relatórios de movimentações",0
])


//---------------------------------------------------------------------------//
//                                                                           //
// TABELAS                                                                   //
//                                                                           //
//---------------------------------------------------------------------------//
addmenu(menu = ["tabelas",
    , , 100, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "<font color='#FFEBCD'>Lotações</font>", "tablota.php", , "Tabela de lotações", 0 //salario
        , "<font color='#FFEBCD'>Funções</font>", "tablfunca.php", , "Tabela de funções", 0 //salario
        , "<font color='#FFEBCD'>Feriados</font>", "tabferiados.php", , "Tabela de feriados", 0 //salario
        , "<font color='#FFEBCD'>Ocorrências</font>", "show-menu=ocorrencias", , "", 0
])

// TABELAS -> OCORRENCIAS
addmenu(menu = ["ocorrencias"
        , , , 120, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        //,"<font color='#FFEBCD'>Incluir</font>","tabocfrei.php",,"Inclui nova Ocorrência",0
        , "<font color='#FFEBCD'>Incluir</font>", "", , "Inclui nova Ocorrência", 0
        , "<font color='#FFEBCD'>Consultar / Alterar</font>", "tabela_ocorrencia_de_frequencia_visualizar.php", , "Consulta e Altera Ocorrências", 0
        //,"<font color='#FFEBCD'>Consultar / Alterar</font>","tabocfre.php",,"Consulta e Altera Ocorrências",0
]);


//---------------------------------------------------------------------------//
//                                                                           //
// UTILITARIOS                                                               //
//                                                                           //
//---------------------------------------------------------------------------//
addmenu(menu = ["senhas"
        , , , 100, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        //,"Usuários","acessoi.php",,"Incluir um novo usuário do sistema",0
        , "<font color='#FFEBCD'>Usuários</font>", "show-menu=usuarios", , "Manutenção de usuários", 0
        , "<font color='#FFEBCD'>Auditoria</font>", "show-menu=auditoria", , "Verificações no sistema", 0
        , "<font color='#FFEBCD'>Gestores</font>", "show-menu=gestores", , "Módulos de uso dos gestores", 0
])

// UTILITARIOS -> USUARIOS / SENHAS
addmenu(menu = ["usuarios"
        , , , 140, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        //,"<font color='#FFEBCD'>Incluir Usuário</font>","usuario_inc.php",,"Incluir novo usuário",0
        , "<font color='#FFEBCD'>Alterar/Excluir Usuário</font>", "javascript:replaceLink(\"usuario_lista.php\")", , "Alterar/Excluir usuários", 0
        , "<font color='#FFEBCD'>Reiniciar Senhas</font>", "reiniciar1.php", , "Reinicializar senhas", 0
        , "<font color='#FFEBCD'>Trocar Senha</font>", "trocasenha.php", , "Trocar senha", 0
])
// fim senhas e usuarios

// UTILITARIOS -> AUDITORIA
addmenu(menu = ["auditoria"
        , , , 140, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Operações de Usuário</font>", "perrellog.php", , "Pesquisar operações", 0
        , "<font color='#FFEBCD'>Identificar IP</font>", "pesquisa_ip.php", , "Pesquisar IP de registro", 0
        , "<font color='#FFEBCD'>Operações Ilegais</font>", "pesilegal.php", , "Pesquisar operações ilegais", 0
        , "<font color='#FFEBCD'>Registros Alterados</font>", "show-menu=regalt", , "Servidores com registros alterados ou excluídos", 0
])
addmenu(menu = ["regalt"
        , , , 150, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Alterações de Frequência</font>", "veregalterado.php", , "Servidores com registros de Frequência alterados ou excluídos", 0
        , "<font color='#FFEBCD'>Alterações de Cadastro</font>", "vecadalt.php", , "Servidores com cadastro alterado", 0
])

// UTILITARIOS -> GESTORES
addmenu(menu = ["gestores",
    , , 185, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "<font color='#FFEBCD'>Corrigir Acesso do Usuário</font>", "gestao_acesso_usuario.php", , "Corrigir problemas de acesso do usuário", 0
        , "<font color='#FFEBCD'>Liberar Homologação</font>", "gestao_libera_homologacao.php", , "Liberar Setor para Homologação", 0
        , "<font color='#FFEBCD'>Reinicializar Senha do Usuário</font>", "gestao_trocasenha_usuario.php", , "Reiniciar a Senha do Usuário", 1
        , "Prazos", "tabvalida.php", , "Tabela de Prazos do Sistema", 0 //salario
        , "Importar Ocorrências", "atualizaocor.php", , "Importa Ocorrências do siape", 0 //salario
        , "Recalcular Recesso", "atualizarecessobhoras_entra.php", , "Recalcular compensação do recesso", 0 //salario
        , "<font color='#FFEBCD'>Corrigir Substituição</font>", "suporte_substituicao1.php", , "Corrigir problemas com substituição", 0
        , "<font color='#FFEBCD'>Acertar Ficha Anual</font>", "atualiza_ficha_anual_individual.php", , "Corrigir problemas na ficha anual", 0

])


//---------------------------------------------------------------------------//
//                                                                           //
// ESTRATEGICA                                                               //
//                                                                           //
//---------------------------------------------------------------------------//
addmenu(menu = ["estrategica"
        , , , 155, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Consulta Frequência", "veponto.php", , "Consulta a Frequência do servidor", 0
//		,"Paralisações/Faltas (Médicos)","show-menu=estrategica_faltas",,"Relatório de Paralisações/Faltas (Médicos)",0
        , "Paralisações/Faltas", "relatorio_paralisacoes_quadro.php", , "Relatório de Paralisações/Faltas (Médicos)", 0
        , "Registro Fora do Horário", "relatorio_registro_fora_horario.php", , "Relatório de Registros fora do Horário", 1
        , "Quadro de Horário", "relatorio_registro_grade_horario.php", , "Grade de Horário das Unidades", 0
])
addmenu(menu = ["estrategica_faltas"
        , , , 155, 1, "", plain_style, , "left", effect, , , , , , , , , , , ,
        , "Todo o Brasil", "relatorio_paralisacoes.php?modo=1", , "Lista Paralisações", 0
        , "Administração Central", "relatorio_paralisacoes.php?modo=2", , "Lista Paralisações", 0
        , "Superintendência Regional", "relatorio_paralisacoes.php?modo=3", , "Lista Paralisações", 0
        , "Gerências Executivas", "relatorio_paralisacoes.php?modo=4", , "Lista Paralisações", 0
])


//---------------------------------------------------------------------------//
//                                                                           //
// ENCERRA A SEÇÃO - FINALIZA O USO DA APLICAÇÃO                             //
//                                                                           //
//---------------------------------------------------------------------------//

// sair
addmenu(menu = ["encerrar_sessao",
    , , 138, 1, "", plain_style, , "left", effect, , 0, , , , , , , , , ,
        , "Encerrar Sessão", "finaliza2.php?modulo=" + modulo_ativado, , "Finaliza a sessão, encerra o uso do sistema.", 0
])
// fim usuarios



dumpmenus()