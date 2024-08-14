
/*-----------------------------------------------------------------------\
 | Função.....: Verifica_CPF                                              |
 | Descrição..: Verifica se o CPF informado eh valido                     |
 | Parametros.: s -> valor para o teste                                   |
 | Dependência: nenhuma                                                   |
 | Retorna....: true se verdadeiro, ou false caso contrário               |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function Verifica_CPF(cpf)
{
    return validarCPF(cpf);
}

/*-----------------------------------------------------------------------\
 | Função.....: validarCPF                                               |
 | Descrição..: Verifica se o CPF informado eh valido                    |
 | Parametros.: s -> valor para o teste                                  |
 | Dependência: nenhuma                                                  |
 | Retorna....: true se verdadeiro, ou false caso contrário              |
 | Autor......: Gerador de CPF                                           |
 \-----------------------------------------------------------------------*/
function validarCPF(cpf) {
    
    cpf = cpf.replace(/[^\d]+/g,'');

    if(cpf === '') return false;
        
    // Elimina CPFs invalidos conhecidos
    if (cpf.length !== 11 ||
        cpf === "00000000000" ||
        cpf === "11111111111" ||
        cpf === "22222222222" ||
        cpf === "33333333333" ||
        cpf === "44444444444" ||
        cpf === "55555555555" ||
        cpf === "66666666666" ||
        cpf === "77777777777" ||
        cpf === "88888888888" ||
        cpf === "99999999999")
            return false;
        
    // Valida 1o digito
    add = 0;
    for (i=0; i < 9; i ++)
        add += parseInt(cpf.charAt(i)) * (10 - i);

    rev = 11 - (add % 11);

    if (rev === 10 || rev === 11)
        rev = 0;

    if (rev !== parseInt(cpf.charAt(9)))
        return false;

    // Valida 2o digito
    add = 0;

    for (i = 0; i < 10; i ++)
        add += parseInt(cpf.charAt(i)) * (11 - i);
	
    rev = 11 - (add % 11);
	
    if (rev === 10 || rev === 11)
        rev = 0;
	
    if (rev !== parseInt(cpf.charAt(10)))
        return false;
	
    return true;
}

/*-----------------------------------------------------------------------\
 | Função.....: ValidaPis                                                 |
 | Descrição..: Verifica se o PIS digitado en valido                      |
 | Parametros.: pis -> valor para teste                                   |
 | Dependência: ChecaPIS                                                  |
 | Retorna....: true se verdadeiro, ou false caso contrário               |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
function ValidaPis(pis)
{
    var pis = (pis == null ? $('#pis').val() : pis);
    if (!ChecaPIS(pis))
    {
        alert("PIS INVALIDO");
        return false;
    }
    else
    {
        alert("PIS VALIDO");
        return true;
    }
}


/*-----------------------------------------------------------------------\
 | Função.....: ChecaPIS                                                  |
 | Descrição..: Verifica se o PIS informado eh valido                     |
 | Parametros.: pis -> valor para o teste                                 |
 | Dependência: nenhuma                                                   |
 | Retorna....: true se verdadeiro, ou false caso contrário               |
 |	Autor......: Desconhecido                                              |
 \-----------------------------------------------------------------------*/
var ftap = "3298765432";
var total = 0;
var i;
var resto = 0;
var numPIS = 0;
var strResto = "";

function ChecaPIS(pis)
{
    total = 0;
    resto = 0;
    numPIS = 0;
    strResto = "";

    numPIS = pis;

    if (numPIS == "" || numPIS == null)
    {
        return false;
    }

    for (i = 0; i <= 9; i++)
    {
        resultado = (numPIS.slice(i, i + 1)) * (ftap.slice(i, i + 1));
        total = total + resultado;
    }

    resto = (total % 11)

    if (resto != 0)
    {
        resto = 11 - resto;
    }

    if (resto == 10 || resto == 11)
    {
        strResto = resto + "";
        resto = strResto.slice(1, 2);
    }

    if (resto != (numPIS.slice(10, 11)))
    {
        return false;
    }
    return true;
}
