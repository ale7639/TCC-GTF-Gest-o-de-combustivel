# Fluxo de Trabalho do Projeto

## Objetivo

Este projeto será desenvolvido em equipe por três integrantes:

* Wallace
* Diego
* Gabriel

Para manter o código organizado e evitar conflitos, utilizaremos uma estratégia de versionamento com Git baseada em branches. Todos os integrantes devem seguir estas regras durante todo o desenvolvimento do projeto.

---

# Estrutura das Branches

O repositório possuirá as seguintes branches:

* **main** → Contém apenas versões estáveis e aprovadas do sistema.
* **test** → Utilizada para integração e testes das funcionalidades desenvolvidas.
* **wallace** → Branch de desenvolvimento do Wallace.
* **diego** → Branch de desenvolvimento do Diego.
* **gabriel** → Branch de desenvolvimento do Gabriel.

Cada integrante deverá trabalhar exclusivamente na sua própria branch.

---

# Fluxo de Desenvolvimento

O fluxo de desenvolvimento seguirá a seguinte sequência:

```
Branch do Desenvolvedor
        ↓
Desenvolvimento
        ↓
Testes Locais
        ↓
Commit
        ↓
Push
        ↓
Merge para test
        ↓
Testes Gerais
        ↓
Merge para main
```

---

# Regras de Desenvolvimento

## 1. Trabalhar apenas na própria branch

Cada integrante é responsável apenas pela sua branch.

Exemplo:

* Wallace → branch `wallace`
* Diego → branch `diego`
* Gabriel → branch `gabriel`

Não é permitido desenvolver diretamente nas branches `test` ou `main`.

---

## 2. Antes de realizar um commit

Todo código enviado ao repositório deve:

* Compilar sem erros.
* Estar testado.
* Não conter código incompleto.
* Não conter arquivos temporários ou desnecessários.
* Estar relacionado a uma funcionalidade específica.

Evite realizar commits contendo diversas alterações sem relação entre si.

---

## 3. Commits

Os commits devem possuir mensagens objetivas e descritivas.

Exemplos:

```
feat: cadastro de caminhões

feat: tela de abastecimento

fix: corrigido cálculo de consumo

refactor: reorganização do serviço de abastecimento
```

Evite mensagens como:

```
teste

update

aaa

alterações
```

---

## 4. Enviando alterações

Após finalizar uma funcionalidade:

1. Testar o sistema.
2. Realizar o commit.
3. Enviar para o GitHub através da própria branch.

Exemplo:

```
git add .
git commit -m "feat: cadastro de caminhões"
git push origin wallace
```

---

# Integração na Branch Test

Quando uma funcionalidade estiver completamente pronta e funcionando:

* Realizar o Merge (ou Pull Request) da branch do desenvolvedor para a branch **test**.

Na branch **test** serão realizados testes de integração para verificar:

* funcionamento da funcionalidade;
* compatibilidade com o código dos demais integrantes;
* possíveis conflitos;
* estabilidade do sistema.

Caso seja identificado algum problema, ele deverá ser corrigido na branch do responsável antes de um novo merge.

---

# Publicação na Main

A branch **main** representa a versão oficial do projeto.

Somente será permitido realizar merge para a **main** quando:

* todas as funcionalidades estiverem funcionando corretamente;
* não existirem conflitos;
* o sistema estiver validado pela equipe.

A branch **main** nunca deverá conter código em desenvolvimento.

---

# Atualização da Branch

Antes de iniciar novas funcionalidades, cada integrante deverá atualizar sua branch com as alterações presentes na branch **test**, reduzindo a chance de conflitos durante futuros merges.

---

# Organização da Equipe

Cada integrante será responsável pelas funcionalidades sob sua responsabilidade, mantendo seu código organizado, documentado e testado.

Sempre que necessário, os integrantes deverão comunicar alterações que possam impactar o trabalho dos demais.

---

# Boas Práticas

* Trabalhar somente na própria branch.
* Testar antes de enviar qualquer alteração.
* Fazer commits pequenos e frequentes.
* Escrever mensagens de commit claras.
* Não alterar código de outro integrante sem comunicação.
* Resolver conflitos antes de realizar merges.
* Manter o projeto sempre compilando.
* Utilizar o arquivo `.gitignore` corretamente.
* Documentar alterações importantes.

---

# Fluxo Resumido

```
Criar funcionalidade
        ↓
Desenvolver na própria branch
        ↓
Testar
        ↓
Commit
        ↓
Push
        ↓
Merge para test
        ↓
Testes de integração
        ↓
Sistema funcionando?
      ↓         ↓
     Não       Sim
      ↓         ↓
 Corrigir    Merge para main
```

---

# Objetivo Final

Seguindo este fluxo de trabalho, a equipe garante:

* melhor organização do código;
* menor quantidade de conflitos durante o desenvolvimento;
* facilidade para identificar alterações de cada integrante;
* maior estabilidade do sistema;
* histórico de versões limpo e organizado;
* uma versão principal (`main`) sempre pronta para apresentação do TCC.
