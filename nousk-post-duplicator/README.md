# Nousk Post Duplicator

### Sobre

Plugin para WordPress que adiciona um botão para duplicar posts, páginas e custom post types diretamente no painel administrativo.

### Funcionalidades

- Duplica posts
- Duplica páginas
- Duplica custom post types
- Copia conteúdo e resumo
- Copia imagem destacada
- Copia taxonomias, como categorias e tags
- Copia metadados do post
- Copia atributos de página, como página ascendente e ordem
- Cria a cópia como rascunho
- Mantém o usuário na listagem após duplicar

### Como funciona

O plugin adiciona um link **Duplicar** na listagem de conteúdos do WordPress.

Ao clicar nesse link, é criada uma nova cópia do conteúdo original como rascunho, preservando os principais dados do post ou da página.

### Instalação

1. Faça upload da pasta do plugin para `/wp-content/plugins/`
2. Ative o plugin no painel do WordPress
3. Acesse a listagem de posts, páginas ou custom post types
4. Clique em **Duplicar**

### Requisitos

- WordPress ativo
- Permissão para editar o conteúdo que será duplicado

### Observações

- A cópia é criada como rascunho
- Após duplicar, o usuário retorna para a listagem de conteúdos
- Alguns metadados internos do WordPress, como `_edit_lock` e `_edit_last`, não são copiados intencionalmente
