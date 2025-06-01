# FTP-via-Web

# Gerenciador FTP em PHP

Um sistema web moderno e responsivo para gerenciar arquivos em servidores FTP, desenvolvido em PHP e utilizando [Bootstrap 5](https://getbootstrap.com/) para uma interface bonita e adaptável a qualquer dispositivo.

## Recursos

- **Login seguro** via FTP
- **Listagem de arquivos e pastas** com visual moderno
- **Upload** e **download** de arquivos
- **Criação e exclusão** de pastas
- **Criação e edição** de arquivos direto pelo navegador (incluindo modal para novo arquivo)
- **Suporte a múltiplos dispositivos** (responsivo)
- **Interface intuitiva** com ícones e feedback visual

## Instalação

1. **Requisitos**:
   - PHP 7.4 ou superior (com extensão FTP habilitada)
   - Servidor Web (Apache, Nginx, etc.)

2. **Passos**:
   - Faça o download ou clone este repositório:
     ```bash
     git clone https://github.com/seu-usuario/seu-repo.git
     ```
   - Copie todos os arquivos para o diretório público do seu servidor web.
   - Certifique-se de que o PHP tem permissão para criar arquivos temporários.

3. **Acesso**:
   - Abra o navegador e acesse `index.php`.
   - Entre com os dados do seu servidor FTP.

## Estrutura dos Arquivos

- `index.php` — Login FTP
- `dashboard.php` — Painel principal de gerenciamento
- `ftp.php` — Funções auxiliares de FTP
- `logout.php` — Encerrar sessão
- `README.md` — Este arquivo

## Como Usar

1. **Login:** Informe host, usuário e senha do FTP.
2. **Gerencie arquivos:** Faça upload, download, edite, crie ou exclua arquivos/pastas.
3. **Criação/Edição:** Use os botões ou ícones ao lado de cada arquivo para editar ou criar novos arquivos.
4. **Logout:** Use o botão "Sair" para encerrar a sessão.

## Segurança

- Seus dados de FTP não são salvos em banco de dados — apenas na sessão ativa.
- Recomenda-se utilizar em ambiente seguro e privado.

## Personalização

- Sinta-se livre para modificar cores e layout via Bootstrap.
- Para adicionar recursos (visualização de texto, sintaxe colorida, mover arquivos, etc), contribua ou abra um issue!

## Screenshots

> <a href="https://ibb.co/QF6W2b8c"><img src="https://i.ibb.co/DD7BxVGb/Screenshot-2025-06-01-10-26-19-458-com-android-chrome-2.jpg" alt="Screenshot-2025-06-01-10-26-19-458-com-android-chrome-2" border="0"></a>
<a href="https://ibb.co/39PZsSFr"><img src="https://i.ibb.co/LXs7P9gk/Screenshot-2025-06-01-10-25-35-777-com-android-chrome-2.jpg" alt="Screenshot-2025-06-01-10-25-35-777-com-android-chrome-2" border="0"></a>

## Licença

[MIT](LICENSE)

---

**Feito com ♥ por [Lelebr2030]**
