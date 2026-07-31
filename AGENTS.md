# AGENTS.md

## Contexto do projeto

- Projeto: `developercielo/api-3.0-php`.
- Tipo: SDK/biblioteca PHP para integração com a [API 3.0 E-commerce da Cielo](https://docs.cielo.com.br/ecommerce-cielo/docs/sobre-api-ecommerce).
- Namespace principal: `Cielo\` (autoload PSR-0 em `src/`).
- Ponto de entrada: `Cielo\API30\Ecommerce\CieloEcommerce`.

## Stack e versoes

- PHP: `^8.2` (CI e PHPStan configurados para 8.3).
- Extensoes obrigatorias: `curl`, `json`.
- Composer: `^2.x`.
- Dependencias: `psr/log` (logging opcional via `LoggerInterface`).
- Qualidade: PHPUnit 12, PHPStan 2, PHP-CS-Fixer 3 (dev).

## Estrutura do codigo

- `src/Cielo/API30/Merchant.php`: credenciais do lojista (`MerchantId`, `MerchantKey`).
- `src/Cielo/API30/Environment.php`: interface de ambiente (URLs da API).
- `src/Cielo/API30/Ecommerce/Environment.php`: implementacao sandbox/producao.
- `src/Cielo/API30/Ecommerce/CieloEcommerce.php`: facade principal (create, capture, cancel, query, tokenize).
- `src/Cielo/API30/Ecommerce/Sale.php`: pedido/venda com builder fluente.
- `src/Cielo/API30/Ecommerce/Payment.php`: pagamento (credito, debito, pix, boleto, etc.).
- `src/Cielo/API30/Ecommerce/Customer.php`, `Address.php`, `CreditCard.php`, `RecurrentPayment.php`: modelos de dominio.
- `src/Cielo/API30/Ecommerce/CieloSerializable.php`: contrato de serializacao JSON (`JsonSerializable` + `populate`).
- `src/Cielo/API30/Ecommerce/Request/AbstractRequest.php`: base HTTP (cURL, headers, tratamento de resposta).
- `src/Cielo/API30/Ecommerce/Request/*Request.php`: requisicoes especificas (`CreateSale`, `UpdateSale`, `QuerySale`, etc.).
- `src/Cielo/API30/Ecommerce/Request/CieloRequestException.php`, `CieloError.php`: erros da API.
- `tests/Unit/*`: testes unitarios (namespace `TestApp\`).
- `tests/E2E/*`: testes end-to-end (quando existirem; exigem credenciais reais).

## Padroes de implementacao

- `CieloEcommerce` delega operacoes para classes `*Request`; nao colocar logica HTTP diretamente na facade.
- Modelos de dominio implementam `CieloSerializable` para montar payloads e desserializar respostas.
- Builders fluentes em `Sale` e `Payment` (`$sale->payment(15700)->creditCard(...)`).
- Requisicoes usam cURL com TLS 1.2, headers `MerchantId`/`MerchantKey` e `RequestId` unico.
- Logging via `Psr\Log\LoggerInterface` e opcional; quando presente, dados de cartao sao mascarados em `AbstractRequest`.
- Preserve compatibilidade retroativa: e um SDK publicado consumido por aplicacoes de pagamento.

## Diretrizes para alteracoes

- Evite quebrar assinaturas publicas sem justificativa clara e sem atualizar testes.
- Para novos meios de pagamento ou operacoes, siga o padrao existente: modelo + request + metodo em `CieloEcommerce`.
- Mantenha coesao por modulo (`Ecommerce`, `Request`, modelos de dominio).
- Consulte o manual oficial da Cielo para campos, codigos de erro e fluxos de autenticacao (debito, 3DS, etc.).
- O SDK monta transacoes; redirecionamento do usuario (debito, boleto, pix) fica a cargo da aplicacao consumidora.

## Qualidade e convencoes

- Arquivos em UTF-8 e quebra de linha `LF`.
- Indentacao: preferir `4 espacos` (codigo legado pode conter tabs em trechos antigos).
- Convencoes: classe `PascalCase`, metodo/variavel `camelCase`, constante `SCREAMING_SNAKE_CASE`.
- Analise estatica: `phpstan.neon` (nivel 5, PHP 8.3).
- Sempre adicionar/atualizar testes quando alterar comportamento publico.
- Toda alteracao deve terminar com analise estatica e testes antes de concluir a tarefa.

## Comandos relevantes

- Validacao completa local: `composer test` (PHPStan + PHPUnit, exclui grupo `payment`).
- PHPStan: `composer phpstan`
- Checar formato: `composer format:check`
- Corrigir formato: `composer format:fix`
- Lint padrao do projeto: `composer lint`
- PHPUnit (todos os testes, exceto grupo `payment`): `composer phpunit`
- Testes unitarios: `composer test:unit`
- Testes E2E: `composer test:e2e` (requer credenciais e ambiente configurados)
- Validar `composer.json`: `composer validate --strict`
- Auditoria de seguranca: `composer audit --no-dev`

## Seguranca e limites

- Nunca commitar credenciais: `MerchantId`, `MerchantKey`, tokens de cartao, dados de cartao reais.
- Nunca registrar CVV, numero completo de cartao ou chaves em logs, mensagens de erro ou dumps de debug.
- O SDK ja mascara numero de cartao em logs de debug; preserve esse comportamento ao alterar `AbstractRequest`.
- Evitar alterar `vendor/` e arquivos gerados automaticamente.
- Em erros/excecoes, evitar expor respostas brutas da API com dados sensiveis ao usuario final.
- Testes que chamam a API real devem usar `@group payment` e ficar fora da suite padrao.
- Validar entradas de modelos (valores em centavos, bandeiras, tipos de pagamento) ao adicionar novos campos.

## Testes e validacao final (obrigatorio)

- Ao finalizar qualquer alteracao, executar obrigatoriamente:
  - `composer phpstan`
  - `composer test:unit`
- Para alteracoes que impactam integracao HTTP ou fluxos de pagamento, rodar tambem `composer test:e2e` quando aplicavel.
- Se houver falha em qualquer comando, corrigir e rodar novamente ate passar.
- Nao considerar tarefa concluida sem evidenciar que analise estatica e testes passaram.

## Commits (obrigatorio)

Usar Conventional Commits em ingles (en-US):

- `<tipo>(<escopo>): <mensagem curta em en-US>`
- Tipos: `feat`, `fix`, `refactor`, `chore`, `docs`, `style`, `perf`, `test`, `build`, `ci`, `revert`

Exemplos:

- `feat(ecommerce): adicionar suporte a novo meio de pagamento`
- `fix(request): corrigir mascaramento de cartao nos logs de debug`
- `test(unit): cobrir serializacao de pagamento pix`
