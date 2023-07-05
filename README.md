# Show do Milhão 💰
Bem-vindo ao Show do Milhão API! Aqui está tudo o que você precisa saber para entender e começar a usar esta API. 🎉

-----

## Sobre o projeto
O Show do Milhão API é um jogo inspirado no famoso programa de TV onde os jogadores respondem a uma série de perguntas para ganhar um milhão de reais (fictícios, é claro!). Esta API foi construída utilizando o framework Laravel e é destinada a fornecer todos os recursos necessários para o jogo, como gerenciamento de perguntas e registro de pontuações.

Api utilizada para fornecer as perguntas: [Tryvia API](https://github.com/peterfritz/tryvia-api). Obrigado!

-----

## Configuração

#### Ambiente

Antes de iniciar, você precisa garantir que o ambiente esteja configurado corretamente. Siga as instruções abaixo para preparar tudo:

1. Certifique-se de ter o PHP instalado na versão 8.1 ou superior.
2. Instale o Laravel na sua máquina seguindo as instruções na [documentação oficial](https://laravel.com/docs). A versão utilizada nesse repo foi `9.52.10`.
3. Clone este repositório para o seu ambiente local.
4. No diretório do projeto, execute o comando `composer install` para instalar as dependências do Laravel.
5. Crie um arquivo `.env` na raiz do projeto e configure a conexão com o banco de dados.


#### Banco de Dados

O Show do Milhão API utiliza um banco de dados para armazenar as perguntas e as pontuações dos jogadores. Por padrão, o Laravel utiliza o banco de dados SQLite. Se preferir utilizar outro banco de dados, você pode configurar as informações de conexão no arquivo `.env` mencionado anteriormente.

Para criar as tabelas necessárias no banco de dados, execute o seguinte comando:

```bash
php artisan migrate
```
-----


## Utilização da API

Agora que tudo está configurado, você está pronto para começar a usar a API. Aqui estão alguns endpoints principais que você pode acessar:

### Começar partida
`POST /api/start` 

A cada requisição para esse endpoint, uma nova partida se iniciará. 

O body da requisição deve conter um array com objetos correspondentes aos jogadores, com o nome e a cor. Exemplo:
```json
{
    "players":[
		{"color":"#333", "name":"Silvio"},
		{"color":"#eee", "name":"Celso"},
		{"color":"#fff", "name":"Hebe"}
	]
}
```
Obs.: "color" é apenas uma gracinha que você talvez queira usar no frontend.

O retorno será um `matchId` que é usado no próximo endpoint. Ex.:
```json
{
	"matchId": 22
}
```

### Receber pergunta
`GET /api/question/{matchId}`

Quando o jogo começar, você deve chamar esse endpoint com o `matchId` que recebeu em `POST /api/start`.

Esse endpoint será chamado sempre que quisermos exibir a próxima questão.

Exemplo de resposta:
```json
{
	"end": false,
	"player": {
		"id": 59,
		"name": "Silvio",
		"color": "#333",
		"money": 0,
		"jumps": 3,
		"cards": 3
	},
	"rewards": {
		"miss": 0,
		"stop": 0,
		"score": 1000
	},
	"question": {
		"id": 791,
		"game_match_id": 22,
		"question": "Qual é o maior animal do mundo?",
		"difficulty": "easy",
		"answers": [
			"Baleia azul",
			"Elefante africano",
			"Girafa",
			"Lula colossal"
		],
		"remove_answers": 0
	},
	"board": []
}
```
Vamos dissecar essa resposta:

`board` - a única propriedade vazia nesse exemplo. Isso quando o jogo ainda não acabou pois ela é o placar final. Ex.:
```json
[
		{
			"id": 59,
			"name": "Silvio",
			"money": 2000,
			"color": "#333"
		},
		{
			"id": 60,
			"name": "Celso",
			"money": 1000,
			"color": "#eee"
		},
		{
			"id": 61,
			"name": "Hebe",
			"money": 0,
			"color": "#fff"
		}
	]
```

`end` - é a propriedade que indica se o jogo terminou. Quando ela é `true`, a propriedade `board` também é disponibilizada e todas as outras entregues como arrays vazios.

`player` - informações do jogador. 
`jump` e `cards` são "trapaças" que podem ser usadas nas perguntas. O próximo endpoint elucidará essas propriedes.

`rewards` - são as possibilidades do jogador e quanto dinheiro ele ganhará com cada um: errar, parar e acertar.

<br>
Após essa chamada é necessário chamar o próximo endpoint para continuar o jogo.

### Reponder pergunta
`POST /api/answer/{questionId}` 

Sempre que quiser que o usuário responda a uma pergunta fornecida, use esse endpoint.

Exemplo de requisição:
```json
{
	"answer":"Baleia azul",
	"jump":false,
	"cards":false,
	"stop":false
}
```
`answer` - resposta escolhida pelo usuário. Apenas irá ser registrada se as outras chaves tiverem o valo `false` como no exemplo.

`jump` - o usuário pode pular 3 perguntas de sua escolha. Caso seja `true`, a pergunta atual é ignorada e não aparecerá mais no jogo.

`cards` - o usuário pode usar as cartas 3 vezes. Essa ação eliminará de 1 a três alternativas incorretas.

`stop` - o usuário para de jogar e fica com o dinheiro atual.

<br>

Caso receba uma resposta `200` chame o endpoint  acima para continuar o jogo (`/api/question/{matchId}`).

----

## Contribuição

Se você quiser contribuir para este projeto e torná-lo ainda melhor, sinta-se à vontade para fazer um fork deste repositório, fazer as modificações desejadas e enviar um pull request. Ficaremos felizes em analisar suas contribuições :flag_br:.

----

## Problemas e dúvidas

Se você encontrar algum problema durante a utilização da API ou tiver alguma dúvida, por favor, abra uma issue neste repositório. Faremos o possível para ajudá-lo o mais rápido possível.

----

## Licença

Este projeto é licenciado sob a licença MIT. Leia o arquivo `LICENSE` para obter mais informações.

<br>
<br>
<br>

Aproveite o Show do Milhão API e divirta-se! 💰💡🎊