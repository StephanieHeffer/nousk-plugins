# Nousk Ad

Plugin WordPress para inserção de anúncios em posts e páginas com controle editorial simples e previsível.

Permite escolher entre três modos de funcionamento: sem anúncios, automático e manual, garantindo flexibilidade para diferentes tipos de conteúdo.

## Objetivo

Este plugin foi criado para facilitar a inserção de anúncios sem depender de plugins externos ou configurações complexas.

A ideia é dar controle para quem edita o conteúdo, sem comprometer a estrutura do HTML ou a legibilidade do post.

## Funcionalidades

- Inserção de anúncios via configuração global
- Controle por post ou página
- Três modos de funcionamento:
  - Sem anúncios
  - Automático
  - Manual
- Shortcode para inserção manual
- Inserção automática baseada em parágrafos
- Validação de configuração no admin
- Não quebra a estrutura do HTML

## Configuração global

No menu **Nousk Ad > Configuração**, é possível definir o código do anúncio.

Esse código pode conter:
- HTML
- CSS
- Scripts

O conteúdo será inserido exatamente como salvo.

### Exemplo de teste

```html
<div style="padding:20px; background:#f5f5f5; border:1px solid #dcdcdc; text-align:center; font-weight:bold;">
	Anúncio de teste
</div>
````

## Configuração por post/página

Cada post ou página possui uma metabox com três opções:

### Sem anúncios

O conteúdo é exibido sem alterações.

### Automático

O plugin insere anúncios automaticamente com base no intervalo definido.

Regras:

* Conta apenas parágrafos `<p>` com conteúdo real
* Parágrafos vazios são ignorados
* Insere anúncios a cada X parágrafos válidos
* Não insere anúncios consecutivos
* Sempre garante um anúncio ao final do conteúdo

### Manual

O editor define onde o anúncio será exibido usando o shortcode:

```
[nousk_ads]
```

Regras:

* Funciona apenas quando o modo está como "Manual"
* Em outros modos, o shortcode é ignorado

## Validações

### Erros (bloqueiam salvamento)

* Intervalo vazio no modo automático
* Intervalo menor que 1

### Avisos (não bloqueiam)

* Intervalo maior que a quantidade de parágrafos válidos

Nesse caso:

* Nenhuma inserção intermediária será feita
* O anúncio aparecerá apenas ao final do conteúdo

## Como funciona (visão técnica)

O plugin atua no filtro `the_content`, analisando o conteúdo do post e aplicando regras de inserção conforme o modo selecionado.

No modo automático:

* O conteúdo é dividido por parágrafos (`</p>`)
* Cada bloco é analisado para verificar se possui conteúdo real
* A contagem de parágrafos válidos determina onde os anúncios serão inseridos
* Ao final, o plugin garante a presença de um anúncio

## Limitações da v1

* Suporte a apenas um tipo de anúncio global
* Não diferencia mobile e desktop
* Não possui opções de layout ou estilo
* Parser baseado em `<p>`, podendo não cobrir todos os cenários de HTML complexo

## Licença
GPL-2.0+
https://www.gnu.org/licenses/gpl-2.0.html

## Autor
Nousk
https://nousk.com.br](https://nousk.com.br
