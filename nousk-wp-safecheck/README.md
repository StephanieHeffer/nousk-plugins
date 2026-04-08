# Nousk WP SafeCheck

Checklist de segurança para WordPress com feedback técnico acessível.

Este plugin analisa aspectos comuns de segurança em uma instalação WordPress e apresenta um diagnóstico simples, organizado por categorias, com recomendações práticas.

Não é um scanner invasivo e não realiza alterações automáticas no sistema.

## Objetivo

O Nousk WP SafeCheck foi criado para desenvolvedores e pessoas que trabalham com WordPress e precisam de uma visão rápida sobre o estado de segurança de um site.

A proposta é funcionar como um checklist técnico, ajudando a identificar pontos de atenção sem prometer uma “análise completa” ou substituição de práticas de segurança.

## O que o plugin verifica

### Servidor e transporte

* HTTPS ativo
* Headers de segurança (HSTS, CSP, X-Frame-Options, etc.)

### Configuração do WordPress

* Existência de usuário com login "admin"
* Edição de arquivos pelo painel administrativo
* Plugins com atualização pendente
* Temas com atualização pendente

### Acesso público

* Acesso ao `wp-login.php`
* Acesso ao `xmlrpc.php`
* Acesso ao `wp-config.php`
* Acesso ao arquivo `.env`
* Acesso à pasta `.git`
* Exposição de diretórios sensíveis (`uploads`, `plugins`, `themes`, `includes`)

### Revisão manual

* Análise do `robots.txt`

## Como interpretar os resultados

Cada verificação retorna um status:

* **OK**
  Nenhum problema detectado dentro da verificação realizada.

* **Atenção**
  Existe um ponto que pode aumentar a superfície de ataque ou que merece revisão.

* **Risco**
  Foi identificado um comportamento potencialmente inseguro.

* **Manual**
  A verificação exige análise humana. O plugin apenas indica o que deve ser revisado.

## O que este plugin NÃO faz

* Não garante que o site está seguro
* Não substitui auditorias de segurança completas
* Não corrige automaticamente vulnerabilidades
* Não realiza varreduras profundas no servidor
* Não analisa código de plugins ou temas

O plugin foi projetado como uma ferramenta de apoio, não como solução definitiva.

## Instalação

1. Faça upload da pasta do plugin para `/wp-content/plugins/`
2. Ative o plugin no painel do WordPress
3. Acesse o menu **Nousk SafeCheck**
4. Visualize os resultados

## Requisitos

* WordPress ativo
* Permissão de administrador

## Observações técnicas

* Algumas verificações utilizam requisições HTTP para validar endpoints públicos
* Informações de atualização de plugins e temas dependem dos dados internos do WordPress
* Algumas análises utilizam heurísticas simples e podem não cobrir todos os cenários
* Resultados devem sempre ser interpretados dentro do contexto do projeto

## Público recomendado

* Desenvolvedores WordPress
* Profissionais de TI
* Pessoas que mantêm sites em ambiente de desenvolvimento ou staging
* Quem deseja entender melhor a segurança básica de uma instalação WordPress

## Autor

Nousk
https://nousk.com.br


