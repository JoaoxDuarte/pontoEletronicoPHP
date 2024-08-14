
	function validar()
	{
		// objeto mensagem
		oTeste = new alertaErro();
		oTeste.init();

		// dados
		var siape = document.getElementById('pSiape');

		var mensagem = '';

		// validacao do campo siape
		// testa o tamanho
		mensagem = validaSiape( siape.value );
		if (mensagem != '') { oTeste.setMsg( mensagem, siape ); }

		// se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
		var bResultado = oTeste.show();

		return bResultado;

	}

	function ve(parm1)
	{
		var siape = document.getElementById('pSiape');
		if (siape.value.length >= 7)
		{
			validar('siape');
		}
	}
