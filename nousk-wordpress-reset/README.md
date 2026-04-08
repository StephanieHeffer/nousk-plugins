# Nousk WordPress Reset
Ferramenta para resetar uma instalação WordPress de forma rápida, removendo conteúdos, plugins e temas extras, mantendo apenas o essencial para continuar o desenvolvimento.

Este plugin foi pensado principalmente para ambientes de **desenvolvimento, testes e staging**, onde é comum precisar limpar o site várias vezes sem reinstalar o WordPress do zero.

## Atenção
Esta ação é **destrutiva e irreversível**.

Todo o conteúdo do site será removido permanentemente ao executar o reset.

## Funcionalidades
Ao executar o reset, o plugin:
- Remove posts, páginas e custom post types
- Remove anexos (mídias)
- Remove conteúdos da lixeira
- Remove categorias, tags e outras taxonomias
- Remove comentários e dados relacionados
- Remove plugins instalados comuns (exceto este plugin)
- Remove temas extras

## O que é mantido
- Usuários do WordPress
- Este plugin
- Um tema padrão do WordPress, se houver um instalado

Caso nenhum tema padrão esteja disponível, o tema atual será mantido para evitar que o site fique sem tema utilizável.

## Observações importantes

### Banco de dados
O plugin **não apaga indiscriminadamente todas as tabelas do banco de dados**.

Isso é intencional, para evitar a remoção de opções internas do WordPress que podem quebrar a instalação.

Dependendo do histórico do site, alguns dados técnicos de plugins antigos podem permanecer no banco e podem ser revisados manualmente.

### Plugins especiais
Alguns tipos de plugins não são removidos automaticamente:
- **Must-use plugins (mu-plugins)** 
- **Drop-ins** (ex: `object-cache.php`, `advanced-cache.php`)

Esses arquivos podem fazer parte da configuração da hospedagem ou do servidor.

### Multisite
Este plugin **não oferece suporte para instalações Multisite**.

## Quando usar
Este plugin é ideal para:
- Ambientes de desenvolvimento
- Testes de plugins ou temas
- Limpeza rápida de instalações WordPress
- Reset de projetos em andamento

Também pode ser utilizado em outros ambientes, desde que haja total consciência de que o conteúdo será apagado permanentemente.

## Instalação
1. Faça upload da pasta do plugin para: /wp-content/plugins/
2. Ative o plugin no painel do WordPress
3. Acesse o menu **Nousk Reset**
4. Clique em **Executar Reset**

## Requisitos
- WordPress 5.x ou superior
- Permissão de administrador (`manage_options`)

## Como funciona
O plugin utiliza funções nativas do WordPress para remoção de conteúdo (`wp_delete_post`, `wp_delete_attachment`, `wp_delete_comment`, etc.), garantindo uma limpeza consistente.
Para garantir que todos os itens sejam removidos, incluindo conteúdos na lixeira, o plugin também realiza consultas diretas ao banco para localizar registros existentes.

## Autor
**Nousk** 
https://nousk.com.br

## Licença
Este plugin é licenciado sob a **GPL-2.0+**.
