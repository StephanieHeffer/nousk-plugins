# Nousk FAQ

Plugin WordPress para transformar conteúdo escrito com marcações simples em FAQs interativos (accordion), com validação automática para evitar erros estruturais.

## Sobre o plugin

O **Nousk FAQ** nasceu de um problema real:

> Como permitir que qualquer pessoa crie FAQs diretamente no editor do WordPress, sem depender de blocos complexos ou interfaces confusas?

A solução foi criar um sistema baseado em **marcações simples no conteúdo**, onde o próprio texto define o que é pergunta e resposta.

O plugin:

* interpreta essas marcações
* valida a estrutura antes de publicar
* transforma automaticamente em um FAQ interativo no front-end

## Como funciona

Quando o FAQ está ativado em um post ou página, o plugin procura blocos com estas marcações:

```
nsk-faq-inicio:
nsk-perg:
nsk-resp:
nsk-faq-fim:
```

Esses blocos são interpretados e renderizados como um accordion (abre/fecha).

## Como usar

1. No editor do WordPress, marque a opção:

 **"Ativar FAQ neste conteúdo"**

2. No conteúdo do post, escreva assim:

```
Texto normal antes.

nsk-faq-inicio:

nsk-perg:
O que este plugin faz?

nsk-resp:
Ele transforma conteúdo marcado em FAQ expansível.

nsk-perg:
Posso usar mais de um parágrafo?

nsk-resp:
Sim.

Pode usar vários parágrafos normalmente.

nsk-faq-fim:

Texto normal depois.
```

## Regras importantes

* O FAQ só funciona entre `nsk-faq-inicio:` e `nsk-faq-fim:`
* Cada pergunta deve começar com `nsk-perg:`
* Cada resposta deve começar com `nsk-resp:`
* A ordem deve ser sempre: **pergunta → resposta**
* Não é permitido:

  * pergunta sem resposta
  * resposta sem pergunta
  * conteúdo solto dentro do bloco
* Perguntas e respostas não podem estar vazias
* É possível ter **mais de um bloco FAQ no mesmo conteúdo**
* Blocos FAQ **não podem ser aninhados**

## Validação automática

Antes de publicar ou atualizar o conteúdo, o plugin valida a estrutura do FAQ.

Se houver erro:

* o post não é publicado
* o conteúdo é salvo como rascunho
* uma mensagem de erro é exibida no topo

Isso evita:

* FAQs quebrados
* erros de estrutura
* problemas no front-end

## Resultado no front-end

O conteúdo marcado é transformado automaticamente em um FAQ interativo:

* Perguntas clicáveis
* Respostas expansíveis
* Suporte a múltiplos itens abertos
* Estrutura acessível com `aria-*`

## Acessibilidade

O plugin utiliza boas práticas básicas de acessibilidade:

* `button` para interação
* `aria-expanded` para estado
* `aria-controls` para associação
* uso de `hidden` para controle de visibilidade

## Filosofia do plugin

Este plugin foi construído com foco em:

* simplicidade para quem edita conteúdo
* controle total via código
* independência de builders ou blocos complexos
* validação para evitar erro humano

## Possíveis melhorias futuras

* opções de estilo (borda, ícones, etc.)
* animações
* abrir apenas um item por vez
* suporte a schema.org (SEO)
* customização via painel

## Licença

GPL-2.0+

## Autor

Desenvolvido por Nousk
https://nousk.com.br

