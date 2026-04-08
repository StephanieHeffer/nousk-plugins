# Nousk CPT Manager

Plugin para WordPress que permite criar e gerenciar Custom Post Types (CPTs) diretamente pelo painel administrativo, sem necessidade de código.

Ideal para desenvolvedores, estudantes e usuários que desejam estruturar conteúdos personalizados de forma simples e rápida.


## Funcionalidades

- Criação de Custom Post Types pelo painel
- Edição de CPTs existentes
- Exclusão de CPTs cadastrados
- Geração automática de slug a partir do nome
- Seleção de ícones (Dashicons)
- Definição de suportes (editor, imagem destacada, resumo)
- Registro automático dos CPTs no WordPress
- Compatível com ACF (Advanced Custom Fields)

## Como funciona

Os CPTs criados são armazenados no banco de dados utilizando `options` do WordPress.

Durante a inicialização (`init`), o plugin registra automaticamente todos os CPTs salvos utilizando `register_post_type()`.

## Segurança

- Uso de nonces para ações de criação, edição e exclusão
- Validação de permissões (`manage_options`)
- Sanitização de dados de entrada

## Observações importantes

### Exclusão de CPTs

Ao excluir um Custom Post Type pelo plugin:

- O CPT deixa de ser registrado no WordPress
- *Os conteúdos (posts) daquele tipo NÃO são apagados do banco*

Isso é intencional para evitar perda de dados inesperada.

### Slugs duplicados

O plugin impede a criação de CPTs com slugs duplicados para evitar conflitos.

## Integração com ACF

Os CPTs criados podem ser utilizados normalmente com o plugin Advanced Custom Fields (ACF).

Basta criar um grupo de campos e definir a regra:
Post Type is igual ao slug do CPT


## Instalação

1. Faça upload da pasta do plugin para:

/wp-content/plugins/

2. Ative o plugin no painel do WordPress

3. Acesse o menu *CPT Manager*

4. Crie seu Custom Post Type

## Requisitos

- WordPress 5.x ou superior
- Permissão de administrador

## Quando usar

Este plugin é ideal para:

- Criar CPTs rapidamente sem código
- Prototipar estruturas de conteúdo
- Projetos com ACF
- Ambientes de desenvolvimento
- Sites institucionais com conteúdo personalizado

## Autor
*Nousk*  
https://nousk.com.br

## Licença

Este plugin é licenciado sob a **GPL-2.0+**
