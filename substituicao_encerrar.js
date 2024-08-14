function verificadados()
{
	if (document.form1.dtsai.value.length == 0) {
		alert("A SAIDA é obrigatória !");
		document.form1.dtsai.focus();
		return false;
  }
}
