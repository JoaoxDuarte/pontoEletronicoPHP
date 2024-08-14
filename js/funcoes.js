
/*-----------------------------------------------------------------------\
 | Função.....: semmenus                                                  |
 | Descrição..: Mantemos por compatibilidade com aplicações anteriores    |
 | Parametros.: nenhum                                                    |
 | Dependência: nenhuma                                                   |
 | Retorna....: nada                                                      |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function semmenus()
{
    var cyber = window.open('mensagem.htm', 'SIGA', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=no,maximized=yes, left=100, top=100 width=600, height=400')
}


/*-----------------------------------------------------------------------\
 | Função.....: pinta                                                     |
 | Descrição..: Destaca a linhas em que esta o mouse com cor diferente da |
 |              atual, retornando a mesma quando o foco sai da linha      |
 | Parametros.: arg1 -> se 1 registra a cor anterior e muda para a nova   |
 |              arg2 -> indica o elemento que terá a cor alterada         |
 | Dependência: nenhuma                                                   |
 | Retorna....: nada                                                      |
 |	Autor......: Desconhecido                                              |
 |	Alteracoes.: Edinalvo Rosa                                             |
 \-----------------------------------------------------------------------*/
var varBGColorAntes = '';
function pinta(arg1, arg2)
{
    if (arg1 == 1)
    {
        varBGColorAntes = arg2.style.backgroundColor;
        arg2.style.backgroundColor = '#C7E4CA';
    }
    else
    {
        arg2.style.backgroundColor = (varBGColorAntes == '' ? '#ffffff' : varBGColorAntes);
    }
}


/*-----------------------------------------------------------------------\
 | Função.....: voltar                                                    |
 | Descrição..: Volta à página anterior                                   |
 | Parametros.: x ----> qtd de páginas para retornar                      |
 |              url --> pagina de destino                                 |
 | Dependência: nenhuma                                                   |
 | Retorna....: nada                                                      |
 |	Autor......: Edinalvo Rosa                                             |
 \-----------------------------------------------------------------------*/
var voltarQtdPaginaRetorno = 1;
var voltarCaminhoDeRetorno = '';
function voltar($x, $url)
{
    var $x = ($x == null ? voltarQtdPaginaRetorno : $x);
    var $url = ($url == null ? voltarCaminhoDeRetorno : $url);

    // Se não houver URL (destino), apenas retorna páginas
    // Se $x igual a zero ($x => páginas para retornar), só realiza o redirecionamento
    if ($url == '')
    {
        window.history.go(-($x));
    }
    else if ($x == 0)
    {
        window.location.replace($url);
    }
    else
    {
        window.history.go(-($x));
        window.location.replace($url);
    }
}


/*-----------------------------------------------------------------------\
 | Função.....: abre                                                      |
 | Descrição..: Abre uma nova janela para exibição de uma outra página    |
 | Parametros.: url ----> pagina destino                                  |
 |              width --> largura da janela                               |
 |              height -> altura da janela                                |
 | Dependência: nenhuma                                                   |
 | Retorna....: nada                                                      |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function Abre(url, width, height)
{
    window.open(url, "_blank", "resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,width=" + width + ",height=" + height);
}



/*-----------------------------------------------------------------------\
 | Função.....: abreJanela                                                |
 | Descrição..: Abre uma nova janela para exibição de uma outra página    |
 | Parametros.: url ----> pagina destino                                  |
 |              width --> largura da janela                               |
 |              height -> altura da janela                                |
 | Dependência: nenhuma                                                   |
 | Retorna....: nada                                                      |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function AbreJanela(url, width, height)
{
    window.open(url, "_blank", "resizable=yes,toolbar=yes,status=yes,menubar=yes,scrollbars=yes,location=yes,width=" + width + ",height=" + height);
}


/*-----------------------------------------------------------------------\
 | Função.....: AbrirSisref                                               |
 | Descrição..: Abre uma nova janela para exibição de uma outra página    |
 | Parametros.: targeturl -> pagina destino                               |
 |              nome ------> nome da janela                               |
 |              w ---------> largura da janela                            |
 | Dependência: nenhuma                                                   |
 | Retorna....: nada                                                      |
 |	Autor......: Edinalvo Rosa                                             |
 \-----------------------------------------------------------------------*/
function AbrirSisref(targeturl, nome, w, h)
{
    var w = (w == null ? 800 : w);
    var h = (w == null ? 500 : h);
    var janela = window.open(targeturl, nome, '_parent, toolbar=no, directories=no, status=no, menubar=no, resizable=yes, width=' + w + ', height=' + h + ', copyhistory=no ,marginheight=0,marginwidth=0,scrollbars=1,left=100,top=100')
}


/*-----------------------------------------------------------------------\
 | Função.....: expandingWindow                                           |
 | Descrição..: Amplia a nova janela para o tamanho da tela (dimensões)   |
 | Parametros.: nenhum                                                    |
 | Dependência: nenhuma                                                   |
 | Retorna....: nada                                                      |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function expandingWindow(w, h)
{
    var w = (w == null ? window.screen.availWidth : w);
    var h = (h == null ? window.screen.availHeight : h);
    window.moveTo(0, 0);
    window.resizeTo(w, h);
}


/*----------------------------------------------------------------------*\
 |	Função.....: data_valida                                               |
 |	Descrição..: Testa se a data informada é válida                        |
 |	Parametros.: valor -> data no formato dd/mm/aaaa                       |
 | Dependência: fcDiasNoMes                                               |
 |	Retorna....: True --> se data válida                                   |
 |              False -> inválida                                         |
 |	Autor......: Edinalvo Rosa                                             |
 \-----------------------------------------------------------------------*/
function data_valida(valor)
{
    var vr = (valor == null ? '' : valor);

    // Substitui os caracteres abaixo deixando apenas numeros.
    vr = vr.replace(/[\(a-z)\(!-+)\(:-ÿ)\.\,\-\\\ \/\ ]/gi, '');
    var tam = vr.length;

    // Se data inválida: Erro.
    var vrDia = vr.substr(0, 2);
    var vrMes = vr.substr(2, 2);
    var vrAno = vr.substr(4, 4);
    var numDias = fcDiasNoMes(vrMes, vrAno);

    //Testa o dia e o mês informado.
    if ((vrDia >= 1 && vrDia <= numDias) && (vrMes >= 1 && vrMes <= 12) && (vrAno >= 1900))
    {
        return true;
    }

    return false;
}

/*----------------------------------------------------------------------*\
 |	Função.....: fcDiasNoMes                                               |
 |	Descrição..: Informa a quantidade de dias de um determinado mês        |
 |	Parametros.: xMia - Mês da data                                        |
 |	             xAno - Ano da data                                        |
 | Dependência: fcAnoBissexto                                             |
 |	Retorna....: Dias no mês                                               |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function fcDiasNoMes(xMes, xAno)
{
    var xDias = 31;
    if (xMes == 1 || xMes == 3 || xMes == 5 || xMes == 7 || xMes == 8 || xMes == 10 || xMes == 12)
    {
        xDias = 31;
    }
    else if (xMes == 4 || xMes == 6 || xMes == 9 || xMes == 11)
    {
        xDias = 30;
    }
    else if (xMes == 2)
    {
        if (fcAnoBissexto(xAno) == true)
        {
            xDias = 29;
        }
        else
        {
            xDias = 28;
        }
    }
    return (xDias);
}


/*----------------------------------------------------------------------*\
 |	Função.....: fcAnoBissexto                                             |
 |	Descrição..: Verifica se o ano informado é bissexto                    |
 |	Parametros.: xAno - Ano da data                                        |
 | Dependência: nenhuma                                                   |
 |	Retorna....: True --> se ano bissexto                                  |
 |              False -> não é ano bissexto                               |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function fcAnoBissexto(xAno)
{
    if (((xAno % 4) == 0) && ((xAno % 100) != 0) || ((xAno % 400) == 0))
    {
        return (true);
    }
    else
    {
        return (false);
    }
}


/*----------------------------------------------------------------------*\
 |	Função.....: formatar                                                  |
 |	Descrição..: Formata o text confrome a mascara indicada                |
 |	Parametros.: src --> valor para formatar                               |
 |	             mask -> mascara que define a formatação                   |
 | Dependência: nenhuma                                                   |
 |	Retorna....: O texto formatado                                         |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function formatar(src, mask)
{
    var i = src.value.length;
    var saida = mask.substring(0, 1);
    var texto = mask.substring(i);
    if (texto.substring(0, 1) != saida)
    {
        src.value += texto.substring(0, 1);
    }
}


/*-----------------------------------------------------------------------\
 | Função.....: limpa_string                                              |
 | Descrição..: Retira qualquer string, deixando apenas números           |
 | Parametros.: S -> o texto a ser tratado                                |
 | Dependência: nenhuma                                                   |
 | Retorna....: os dados apenas númericos                                 |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function limpa_string(S)
{
    // Deixa so' os digitos no numero
    var Digitos = "0123456789";
    var temp = "";
    var digito = "";
    for (var i = 0; i < S.length; i++)
    {
        digito = S.charAt(i);
        if (Digitos.indexOf(digito) >= 0)
        {
            temp = temp + digito;
        }
    }
    return temp
}


/*----------------------------------------------------------------------------------------------------\
 | Monta mensagens de erro         |
 \--------------------------------*/
var x__nmsg = 0;
var x__msg_erro = '';
var x__obj_erro = null;
function setMsg(msg, obj)
{
    if (msg == null)
    {
        nmsg = 0;
        x__msg_erro = '';
        x__obj_erro = null;
    }
    else
    {
        x__msg_erro += msg + '\n';
        x__obj_erro = (x__obj_erro == null ? obj : x__obj_erro);
        nmsg++;
    }
}

/*----------------------------------------------------------------------------------------------------\
 | Exibe mensagens de erro         |
 \--------------------------------*/
function exibeMensagemErro()
{
    if (x__obj_erro != null)
    {
        alert(x__msg_erro);
        x__obj_erro.focus();
        return false
    }
    return true
}


/*----------------------------------------------------------------------------------------------------\
 | Class mensagens de erro         |
 \--------------------------------*/
function alertaErro()
{
    var alertaErro = {
        mensagem: '',
        objeto: null,
        init: function ()
        {
            this.mensagem = '';
            this.objeto = null;
        },
        setMsgTitulo: function (txt)
        {
            var txt = (txt == null ? '' : txt + '\n');
            this.mensagem = txt;
            this.objeto = null;
        },
        setMensagem: function (txt, obj)
        {
            var txt = (txt == null ? '' : txt + '\n');
            var obj = (obj == null ? null : obj);
            this.mensagem += txt;
            this.objeto = (this.objeto == null ? obj : this.objeto);
        },
        setMsg: function (txt, obj)
        {
            var txt = (txt == null ? '' : txt);
            var obj = (obj == null ? null : obj);
            this.setMensagem(txt, obj);
        },
        show: function ()
        {
            if (this.mensagem != '' || this.objeto != null)
            {
                if (this.mensagem != '')
                {
                    mostraMensagem(this.mensagem, 'danger');
                }
                if (this.objeto != null)
                {
                    this.objeto.focus();
                }
                return false;
            }
            return true;
        },
        close: function ()
        {
            delete this.mensagem;
            delete this.objeto;
        }
    };
    return alertaErro;
}


function mostraMensagemAlerta(msg, title, type, url)
{
    var msg2 = (msg == null ? '' : msg);
    var title2 = (title == null || title == '' ? 'Informa&ccedil;&atilde;o' : title);
    var type2 = (type == null || type == '' ? 'type-default' : 'type-' + type);
    // type : sucess, danger, warning, info, default
    "use strict";
    BootstrapDialog.show({
        type: type2,
        title: title2,
        cssClass: has - success,
        message: msg2,
        closeByBackdrop: false,
        closeByKeyboard: false,
        callback: function ()
        {
            if (url != null)
            {
                location.replace('', url, '');
            }
        }
    });
}


function mostraMensagem(msg, type = 'success', url, voltar)
{
    var type2 = null;
    var title = 'Informa&ccedil;&atilde;o';
    "use strict";
    switch (type)
    {
        case 'info':
            type2 = BootstrapDialog.TYPE_INFO;
            break;
        case 'primary':
            type2 = BootstrapDialog.TYPE_PRIMARY;
            break;
        case 'success':
            type2 = BootstrapDialog.TYPE_SUCCESS;
            break;
        case 'warning':
            type2 = BootstrapDialog.TYPE_WARNING;
            title = 'Aviso';
            break;
        case 'danger':
            type2 = BootstrapDialog.TYPE_DANGER;
            title = 'Aten&ccedil;&atilde;o';
            break;
        case 'default':
        default:
            type2 = BootstrapDialog.TYPE_DEFAULT;
            break;

    }

    BootstrapDialog.alert({
        type: type2,
        title: title,
        message: msg,
        closeByBackdrop: false,
        closeByKeyboard: false,
        callback: function ()
        {
            if (url == 'login')
            {
                if (window.top !== window.self) 
                {
                    console.log('frames');
                    window.parent.location.reload(true);
                }
                else
                {
                    console.log('SEM FRAMES');
                    window.location.reload(true);
                }

                return false;
            }
            else if (voltar != null && voltar > 0)
            {
                window.history.go(-(voltar));
            }
            else  if (url != null && url != '')
            {
                location.replace(url);
            }
        }
    });
}


function alertaNaPagina(msg = null, tipo = 'danger')
{
    if (msg != null)
    {
        if ($('#mensagem_do_sistema') == null)
        {
            $('#mensagem_do_sistema').hide();
            $('header').prepend('<div id="mensagem_do_sistema" style="width:950px;padding:550px 0px 20px 0px;margin:0 auto;"><div class="alert alert-' + tipo + '" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="$(\'#mensagem_do_sistema\').remove();"><span aria-hidden="true">&times;</span></button>' + msg + '</div><br><div style="padding:0px 0px 50px 0px;">&nbsp;</div></div>');
        }
        else
        {
            $('#mensagem_do_sistema').html('<div class="alert alert-' + tipo + '" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="$(\'#mensagem_do_sistema\').hide();"><span aria-hidden="true">&times;</span></button>' + msg + '</div>');
            $('#mensagem_do_sistema').show();
        }
}
}


/*----------------------------------------------------------------------------------------------------\
 | Função valida siape             |
 \--------------------------------*/
function validaSiape(siape, obrigatorio)
{
    var mensagem = '';
    var siape = (siape === null ? '' : siape);
    var obrigatorio = (obrigatorio === null ? true : obrigatorio);

    var valores_invalidos = new Array(
            "0000000",
            "1111111",
            "2222222x",
            "3333333",
            "4444444",
            "5555555",
            "6666666",
            "7777777",
            "8888888",
            "9999999"
        );

    var matricula = soNumeros(siape);

    if (obrigatorio === true && (matricula === '' || trim(siape).length < 7))
    {
        mensagem = 'É obrigatório informar a matrícula com no mínimo 7 caracteres!';
    }
    else if (matricula.length < 7 || valores_invalidos.indexOf(siape) > -1)
    {
        mensagem = 'Matrícula informada, inválida!';
    }
    return mensagem;
}


/*----------------------------------------------------------------------------------------------------\
 | Função valida SiapeCAD          |
 \--------------------------------*/
function validaSiapeCAD(str, obrigatorio)
{
    var mensagem = '';
    var str = (str == null ? '' : str);
    var obrigatorio = (obrigatorio == null ? true : obrigatorio);

    var valores_invalidos = new Array(
            "00000000",
            "11111111",
            "22222222x",
            "33333333",
            "44444444",
            "55555555",
            "66666666",
            "77777777",
            "88888888",
            "99999999x"
        );

    var matricula = soNumeros(str);

    if (obrigatorio == true && (matricula == '' && trim(str).length < 8))
    {
        mensagem = 'É obrigatório informar a matrícula SiapeCad com 8 caracteres!';
    }
    else if (matricula.length < 8 || valores_invalidos.indexOf(str) > -1)
    {
        mensagem = 'Matrícula informada, inválida!';
    }
    return mensagem;
}


/*----------------------------------------------------------------------------------------------------\
 | Função valida Identificação Única |
 \----------------------------------*/
function validaIdUnica(str, obrigatorio)
{
    var mensagem = '';
    var str = (str == null ? '' : str);
    var obrigatorio = (obrigatorio == null ? true : obrigatorio);

    var valores_invalidos = new Array(
            "000000000",
            "111111111",
            "222222222x",
            "333333333",
            "444444444",
            "555555555",
            "666666666",
            "777777777",
            "888888888",
            "999999999"
        );

    var matricula = soNumeros(str);

    if (obrigatorio == true && (matricula == '' && trim(str).length < 9))
    {
        mensagem = 'É obrigatório informar a Identificação Única com 9 caracteres!';
    }
    else if (matricula.length < 9 || valores_invalidos.indexOf(str) > -1)
    {
        mensagem = 'Identificação Única informada, inválida!';
    }
    return mensagem;
}


/*----------------------------------------------------------------------------------------------------\
 | Função valida mes               |
 \--------------------------------*/
function validaMes(mes, obrigatorio)
{
    var mensagem = '';
    var mes = (mes == null ? '' : mes);
    var obrigatorio = (obrigatorio == null ? true : obrigatorio);

    var nMes = parseInt(mes,10);

    if (obrigatorio == true && ((Number.isInteger(nMes) == false) || (nMes == 0) || (mes.length != 2)))
    {
        mensagem = 'É obrigatório informar o mês, com 2 caracteres!';
    }
    else if (nMes < 1 || nMes > 12)
    {
        mensagem = 'Mês incorreto! Informe de 01 a 12!';
    }

    return mensagem;
}



/*----------------------------------------------------------------------------------------------------\
 | Função valida ano               |
 \--------------------------------*/
function validaAno(ano, mes, obrigatorio)
{
    var mensagem = '';
    var ano = (ano == null ? '0' : ano);
    var mes = (mes == null ? '0' : mes);
    var obrigatorio = (obrigatorio == null ? true : obrigatorio);

    var ano_hoje = anoHoje();
    var anomesHoje = parseInt(ano_hoje + '' + mesHoje(),10);
    var anomesForm = parseInt(ano + '' + mes,10);

    var nAno = parseInt(ano,10);
    var nMes = parseInt(mes,10);

    if (obrigatorio == true && ((Number.isInteger(nAno) == false) || (nAno == 0) || (ano.length < 4)))
    {
        mensagem = 'É obrigatório informar o ano, com 4 caracteres!';
    }
    else
    {
        if (nAno < 2009 || nAno > parseInt(ano_hoje, 10))
        {
            mensagem = 'Ano inválido!';
        }

        if ((Number.isInteger(nMes) == true) && (nMes > 0) && (mes.length == 2) && (anomesForm > anomesHoje))
        {
            mensagem = 'Competência inválida!';
        }
    }

    return mensagem;
}


/*----------------------------------------------------------------------------------------------------\
 | Função zebra                    |
 \--------------------------------*/
function zebra(id, classe)
{
    var tabela = $('#'+id);
    var linhas = $("tr");
    for (var i = 0; i < linhas.length; i++)
    {
        ((i % 2) == 0) ? linhas[i].className = classe : void(0);
    }
}


/*----------------------------------------------------------------------------------------------------\
 | Função soNumeros                |
 \--------------------------------*/
function soNumeros(string)
{
    var numsStr = string.replace(/[^0-9]/g, '');
    return numsStr;
}


/*----------------------------------------------------------------------------------------------------\
 | Função soLetras                 |
 \--------------------------------*/
function soLetras(string)
{
    var sStr = string.replace(/[^a-z]/g, '');
    return sStr;
}


/*----------------------------------------------------------------------------------------------------\
 | Função trim (completo)          |
 \--------------------------------*/
function trim(str)
{
    return str.replace(/^\s+|\s+$/g, "");
}


/*----------------------------------------------------------------------------------------------------\
 | Função ltrim (left)             |
 \--------------------------------*/
function ltrim(str)
{
    return str.replace(/^\s+/, "");
}


/*----------------------------------------------------------------------------------------------------\
 | Função rtrim (right)            |
 \--------------------------------*/
function rtrim(str)
{
    return str.replace(/\s+$/, "");
}


/*----------------------------------------------------------------------------------------------------\
| Função retirarExcessoEspacos (espaços, mesmo entre palavras) |
\-------------------------------------------------------------*/
function retirarExcessoEspacosInternos(str) {
	return str.replace(/\s{2,}/g, ' ');
}


/*----------------------------------------------------------------------------------------------------\
 | Função Utf8                     |
 \--------------------------------*/
var Utf8 = {
    // public method for url encoding
    encode: function (string)
    {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++)
        {
            var c = string.charCodeAt(n);
            if (c < 128)
            {
                utftext += String.fromCharCode(c);
            }
            else if ((c > 127) && (c < 2048))
            {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else
            {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    },
    // public method for url decoding
    decode: function (utftext)
    {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
        while (i < utftext.length)
        {
            c = utftext.charCodeAt(i);
            if (c < 128)
            {
                string += String.fromCharCode(c);
                i++;
            }
            else if ((c > 191) && (c < 224))
            {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else
            {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
}



var spinner = "";

function showProcessandoAguarde()
{
    spinner = $('.loading-spinner');
    spinner.css('display', 'block');
    spinner.addClass('active');
}
function hideProcessandoAguarde()
{
    spinner.css('display', 'none');
    spinner.removeClass('active');
}



function showProcessando(tipo_msg, nTipo)
{
    var tipo_msg = (tipo_msg == null ? 2 : tipo_msg);
    var nTipo = (nTipo == null ? 1 : nTipo);
    if (tipo_msg == 1)
    {
        $('block_tabela_principal').block({
            message: '<h3><img src=\"imagem/sisref.gif\"/><br><div id=\"msgProcessando\"> Processando... <img src=\"imagem/loading2.gif\"/></div></h3>',
            css: {
                width: '240px',
                css: {cursor: 'default'}
            }
        });
    }
    else
    {
        switch (nTipo)
        {
            case 1:
                $.blockUI({
                    message: '<h3><img src=\"imagem/sisref.gif\"/><br><div id=\"msgProcessando\"> Processando... <img src=\"imagem/loading2.gif\"/></div></h3>',
                    css: {
                        width: '240px',
                        top: ($(window).height() - 240) / 2 + 'px',
                        left: ($(window).width() - 240) / 2 + 'px',
                        css: {cursor: 'default'}
                    }
                });
                break;

            case 2:
                $.blockUI({
                    message: '<img src="./imagem/carregando.gif" border="0">',
                    css: {
                        width: '66px',
                        height: '66px',
                        top: ($(window).height() - 66) / 2 + 'px',
                        left: ($(window).width() - 66) / 2 + 'px',
                        cursor: "wait",
                        border: '0px solid #aaa',
                        backgroundColor: '#c0c0c0',
                        zIndex: 9999999
                    }
                });
                break;
        }
    }
}

function hideProcessando(tipo_msg)
{
    var tipo_msg = (tipo_msg == null ? 2 : tipo_msg);
    if (tipo_msg == 1)
    {
        $('table.block_tabela_principal').unblock();
    }
    else
    {
        $.unblockUI({css: {cursor: 'default'}});
    }
}

function inverter(valor)
{
    var inverter = valor;
    valor = inverter.item(0).value.toString().split("");
    normal = valor.reverse().join("");
    return normal;
}


function alerta(msg)
{
    alert(msg);
}

function replaceLink(link, sFrame)
{
    var sFrame = (sFrame == null ? 'main' : sFrame);
    //if (sFrame=='main')
    //{
    //	parent.main.location.replace(link);
    //}
    //else
    //{
    location.replace(link);
    //}
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


/*----------------------------------------------------------------------------------------------------\
| Class Tela Processando         |
\--------------------------------*/
function telaProcessando()
{
	var telaProcessando = {
		quadro: '',
		objeto: null,

		init: function(){
			this.quadro = '<div id="pbContainer0" style="position:absolute;top:25%;left:41%;width:50%;height:50%;border-width:0px;background-color:transparent;display:none;z-index:100000;"><table id="pbBarProgressor" class="borda_arredondada borda_arredondada_sombra" style="text-align:center;width:210px;border:1px solid #50aa50;background-color:white;" cellpadding="0" cellspacing="2" max="100"><thead><tr><td style="border: 0px;"><h3><img src="imagem/sisref.gif"/><br><img src="imagem/loading.gif"/><br> Processando... </h3></td></tr></thead></table></div><div id="black_overlay"></div>';
			$('body').append(this.quadro);
		},

		setMensagem: function(txt,obj){
			var txt = (txt==null?'':txt+'\n');
			var obj = (obj==null?null:obj);
			this.quadro += txt;
			this.objeto = (this.objeto==null?obj:this.objeto);
		},

		open: function(){
			$('#pbContainer0').css('display','');
			$('#black_overlay').css('display','block');
		},

		close: function() {
			$('#pbContainer0').remove();
			$('#black_overlay').remove();
			delete this.quadro;
			delete this.objeto;
		}
	};
	return telaProcessando;
}



/*----------------------------------------------------------------------------------------------------\
| Class Tela Progresso            |
\--------------------------------*/
function telaProgresso()
{
    var telaProgresso = {
        quadro: '',
        objeto: null,
        pBar: null,
        pRow: null,
        perc: null,

        init: function(){
            this.quadro = '<div id="pbContainer0" class="loading-center-imagem-processando" style="padding:5px"><table id="pbBarProgressor" class="borda_arredondada borda_arredondada_sombra" style="text-align:center;width:300px;border:0px solid #50aa50;background-color:white;"><tr><td style="border:0px;" class="borda_arredondada borda_arredondada_sombra"><img src="imagem/sisref.gif"/><h3><small>Aguarde, pesquisa em andamento!</small></h3></td></tr><tr><td style="text-align:center;"><div id="pbBar0_top" style="display:none;white-space:nowrap;background-color:transparent;height:12px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;">&nbsp;</div></td></tr><tr><td style="border:1px solid #50aa50;background-color:#ddffdd;height:10px;width:300px;"><div id="percentage" class="sombra-texto" style="white-space:nowrap;background-color:#30aa30;height:12px;width:0px;color:white;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;padding:0px 0px 0px 0px;"></div></td></tr><tr><td style="text-align:center;padding:5px;"><div id="pbBar0_row" style="white-space:nowrap;background-color:transparent;height:12px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;">Registros: </div></td></tr><tr><td style="text-align:center;"><div id="pbBar0_bottom" style="display:none;white-space:nowrap;background-color:transparent;height:12px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;">&nbsp;</div></td></tr></table></div><div id="black_overlay"></div>';
            //$('body').append(this.quadro);
            $('#progresso_sse_center_processando').html(this.quadro);
        },

        setProgress: function(valor){
            this.pBar.val( valor );
            this.perc.html( valor + "%" );
            this.perc.width( Math.floor((this.pBar.width()-6) * (valor/100)) + 'px' );
        },

        setRegistros: function(txt){
            this.pRow.html( txt );
        },

        open: function(){
            $('#pbContainer0').css('display','block');
            $('#black_overlay').css('display','block');
            this.pBar = $('#pbBarProgressor');
            this.pRow = $('#pbBar0_row');
            this.perc = $('#percentage');
        },

        close: function() {
            $('#pbContainer0').remove();
            $('#black_overlay').remove();
            delete this.quadro;
            delete this.objeto;
        }
    };

    return telaProgresso;
}



/*----------------------------------------------------------------------------------------------------\
| Class Tela Progresso            |
\--------------------------------*/
function newTelaProgresso()
{
    var telaProgresso = {
        quadro: '',
        objeto: null,
        pBar: null,
        pRow: null,
        perc: null,
	progresso: null,

        init: function(){
            this.quadro = '<img src="imagem/sisref.gif"/><br><font style="font-size:12px;font-style:italic;font-weight:bold;">Aguarde, processando...</font><div id="pbBar0_top" style="display:none;white-space:nowrap;background-color:transparent;height:12px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;">&nbsp;</div><table id="pbBarProgressor" class="borda_arredondada borda_arredondada_sombra" style="text-align:center;width:300px;border:0px solid #30aa30;background-color:white" cellpadding="0" cellspacing="2" max="100"><thead><tr><td><div style="position:relative;left:0px;top:0px;border:1px solid #a3d3a3;white-space:nowrap;background-color:#e3f2e3;"><div id="progresso" class="sombra-texto" style="background-color:#30aa30;height:15px;width:0px;color:white;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;padding:3px 0px 0px 0px;"></div></div><div id="percentage" style="position:relative;left:0px;top:-15px;white-space:nowrap;background-color:transparent;height:15px;width:100%;color:#575757;font-size:9px;font-family:verdana;text-align:center;font-weight:bold;padding:1px 0px 0px 0px;text-shadow: #9b9b9b 1px 2px 1px;">0%</div><div id="pbBar0_row" style="position:relative;left:0px;top:-12px;white-space:nowrap;background-color:transparent;height:12px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;padding:0px 0px 5px 0px;">Registros:</div></td></tr></thead></table>';
            $('#progresso_sse_center_processando').html(this.quadro);
            showProcessandoAguarde();
        },

        setProgress: function(valor){
            this.pBar.val( valor );
            this.perc.html( valor + "%" );
            if (valor == 50)
            {
                this.perc.css( 'color', 'white' );
                this.perc.css( 'text-shadow', '#000000 1px 2px 1px;' );
            }
            this.progresso.width( Math.floor((this.pBar.width()-2) * (valor/100)) + 'px' );
        },

        setRegistros: function(txt){
            this.pRow.html( txt );
        },

        open: function() {
            $('#pbContainer0').css('display','');
            $('#black_overlay').css('display','block');
            this.pBar = $('#pbBarProgressor');
            this.pRow = $('#pbBar0_row');
            this.perc = $('#percentage');
            this.progresso = $('#progresso');
        },

        close: function() {
            $('#pbContainer0').remove();
            $('#black_overlay').remove();
            delete this.quadro;
            delete this.objeto;
            hideProcessandoAguarde();
        }
    };

    return telaProgresso;
}


/**
 * @info Converte horas em segundos
 *
 * @name   time_to_sec()
 *
 * @param {string} horas Horas para converter em segundos
 * @returns {float} Segundos
 */
function time_to_sec(horas)
{
    var a       = horas.split(':'); // split it at the colons
    var seconds = 0;
    var tam     = a.length;

    // minutes are worth 60 seconds. Hours are worth 60 minutes.
    switch (tam)
    {
        case 1:
            seconds = (+a[0]) * 60 * 60;
            break;
        case 2:
            seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60;
            break;
        case 3:
            seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]);
            break;
    }

    console.log(seconds);

    return seconds;
}


/**
 * @info Converte segundos em horas (hh:mm[:ss])
 *
 * @name   sec_to_time()
 *
 * @param {integer} seconds Segundos para conversão em horas
 * @param {integer} format  Formato de saída (hh:mm[:ss])
 * @returns {String} Hora conforme 'format' ou (hh:mm:ss)
 */
function sec_to_time(seconds,format)
{
    var seconds = (seconds == null ? 0 : seconds);
    var format  = (format  == null ? 'hh:mm' : format);

    var totalSeconds = seconds; // seconds

    var hours = Math.floor(totalSeconds / 3600);
    totalSeconds %= 3600;

    var minutes = Math.floor(totalSeconds / 60);
    var seconds = totalSeconds % 60;

    //console.log("hours: "   + hours);
    //console.log("minutes: " + minutes);
    //console.log("seconds: " + seconds);

    // If you want strings with leading zeroes:
    //hours   = String(hours).padStart(2, "0");
    //minutes = String(minutes).padStart(2, "0");
    //seconds = String(seconds).padStart(2, "0");
    hours   = ("00" + hours).slice(-2);
    minutes = ("00" + minutes).slice(-2);
    seconds = ("00" + seconds).slice(-2);

    // formato hh:mm
    var retorno = "";

    switch (format)
    {
        case 'hh:mm:ss':
            retorno = hours + ":" + minutes + ":" + seconds;
            break;
        case 'hh:mm':
        default:
            retorno = hours + ":" + minutes;
            break;
    }

    //console.log( retorno );

    return retorno;
}

/**
 * @info Pausa
 *
 * @param {integer} milliseconds
 * @returns {void}
 */
function sleep(milliseconds)
{
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++)
    {
        if ((new Date().getTime() - start) > milliseconds)
        {
            break;
        }
    }
}


/**
 * @info Verifica se a hora está correta
 *
 * @name   verifica_hora()
 *
 * @param {object} campo "This" do campo do formulário
 * @returns {boolean} True se hora correta
 */
function verificaHora(campo){
    hrs = (campo.val().substring(0,2));
    min = (campo.val().substring(3,5));

    estado = "";

    if ((hrs < 00 ) || (hrs > 23) || ( min < 00) ||( min > 59)){
        return false;
    }

    if (campo.val() == "") {
        return false;
    }

    return true;
}