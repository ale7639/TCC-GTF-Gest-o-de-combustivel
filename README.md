# GFC — Gestão de Combustível e Frota

Sistema mobile-first e web para a Transportadora parceira da UNIALFA: controle de tanque, abastecimento, frota, manutenção, lavagem, relatórios e alertas.

O código segue o documento de TCC (sprints, regras de negócio e casos de teste), com estrutura de um desenvolvedor pleno/júnior: claro, organizado e sem overengineering.

## Stack

- Backend: PHP 8.2+ · Laravel 11 · API REST · Laravel Sanctum
- Frontend: React 18 · Vite · PWA (instalável no celular)
- Banco: MySQL 8
- API gratuita: [BrasilAPI / FIPE](https://brasilapi.com.br/) para sugerir modelos de caminhão no cadastro

## Perfis (RN)

| Perfil | Acesso |
|---|---|
| Administrador | Tudo, inclusive cadastro/edição/exclusão de caminhões e usuários |
| Supervisor | Operação: checklist, manutenção, lavagem, abastecimento, relatórios e alertas |
| Motorista | Dashboard, frota (consulta), abastecer e lavagem. Sem relatórios e sem exclusão |

## Como rodar (Docker)

Na pasta do projeto:

```bash
docker compose up -d --build
```

- App (telas de login e dashboard): http://localhost:5173
- API: http://localhost:8000 — página de status. O sistema em si não abre nesta porta.

```bash
docker compose logs -f app
```

### Alternativa: MySQL no Docker e Laravel no computador



Requisitos: PHP 8.2+ com extensões `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `curl`, `fileinfo` e Composer.

```bash
cd backend
copy .env.example .env
composer install
php artisan key:generate
```

Ajuste o `.env` se o MySQL não for `root` sem senha:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gfc
DB_USERNAME=root
DB_PASSWORD=
```

Depois:

```bash
php artisan migrate --seed
php artisan serve
```

API em `http://127.0.0.1:8000`.

### 3. Frontend (React)

```bash
cd frontend
npm install
npm run dev
```

App em `http://localhost:5173`. No computador ele aparece como um celular; no telefone ocupa a tela toda.

### 4. Alertas automáticos

O job diário das 6h está no scheduler (`gfc:gerar-alertas`). Em desenvolvimento:

```bash
cd backend
php artisan gfc:gerar-alertas
php artisan schedule:work
```

## Contas de demonstração

Senha de todos: `Senha123`

| Perfil | E-mail |
|---|---|
| Administrador | diego@gfc.com.br |
| Supervisor | ana@gfc.com.br |
| Motorista | joao@gfc.com.br |

Na tela de login há atalhos para preencher cada perfil.

## Segurança (qualidade de uso real)

- Senha com bcrypt (12 rounds), nunca em texto puro
- Token Sanctum com validade de 24 horas
- Mensagem genérica de login (não revela se o e-mail existe)
- Bloqueio da conta após 5 tentativas, por 15 minutos
- Recuperação de senha: mesma resposta para e-mail existente ou não, link de 1 hora, máximo 3 pedidos por IP/hora
- Abastecimento em transação (débito do tanque + registro juntos)
- Soft delete de caminhão, com auditoria (usuário, data, IP)
- Perfis validados no servidor, não só no menu
- Validação de placa (AAA-9999 e Mercosul) no frontend e no backend

## Tipografia e visual

- **DM Sans** — interface, leitura e formulários
- **Barlow Condensed** — placas, litros e indicadores (tipologia operacional de pátio)
- Paleta diesel / asfalto / âmbar de combustível, com ícone + cor nos status (acessível para daltonismo)

## Estrutura

```
backend/     API Laravel
frontend/    App React mobile-first
```

Regras de negócio principais ficam em `app/Services` (`FuelingService`, `ChecklistService`, `AlertService`) e nas Form Requests.

## Testes

```bash
cd backend
php artisan test
```

## Fluxo Git da equipe

```
Branch do desenvolvedor  →  testes locais  →  commit  →  push
        ↓
      test   (testes gerais da equipe)
        ↓
      main   (só depois da aprovação)
```

- `main` — versão estável, só entra com pull request aprovado
- `test` — integração para o parceiro testar
- `inicio-gfc` (ou `feature/...`) — branch de quem está desenvolvendo

Nunca commite `.env`, `vendor` nem `node_modules`.

