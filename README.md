# API-3.0-PHP

SDK API-3.0 PHP

## Principais recursos

* [x] Pagamentos por cartão de crédito.
* [x] Pagamentos recorrentes.
    * [x] Com autorização na primeira recorrência.
    * [x] Com autorização a partir da primeira recorrência.
* [x] Pagamentos por cartão de débito.
* [x] Pagamentos por pix.
* [x] Pagamentos por boleto.
* [x] Pagamentos por transferência eletrônica.
* [x] Cancelamento de autorização.
* [x] Consulta de pagamentos.
* [x] Tokenização de cartão.

## Limitações

Por envolver a interface de usuário da aplicação, o SDK funciona apenas como um framework para criação das transações. Nos casos onde a autorização é direta, não há limitação; mas nos casos onde é necessário a autenticação ou qualquer tipo de redirecionamento do usuário, o desenvolvedor deverá utilizar o SDK para gerar o pagamento e, com o link retornado pela Cielo, providenciar o redirecionamento do usuário.

## Dependências

* PHP >= 8.3

## Instalando o SDK

Se já possui um arquivo `composer.json`, basta adicionar a seguinte dependência ao seu projeto:

```json
"require": {
    "developercielo/api-3.0-php": "^2.0.0"
}
```

Adicionar o `repositories` no `composer.json`

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/edson-nascimento/API-3.0-PHP"
    }
],
```

Com a dependência adicionada ao `composer.json`, basta executar:

```bash
composer update
```

## Produtos e Bandeiras suportadas e suas constantes

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Ecommerce\CreditCard;
```

| Bandeira         | Constante              | Crédito à vista | Crédito parcelado Loja | Débito | Voucher |
|------------------|------------------------|-----------------|------------------------|--------|---------|
| Visa             | CreditCard::VISA       | Sim             | Sim                    | Sim    | *Não*   |
| Master Card      | CreditCard::MASTERCARD | Sim             | Sim                    | Sim    | *Não*   |
| American Express | CreditCard::AMEX       | Sim             | Sim                    | *Não*  | *Não*   |
| Elo              | CreditCard::ELO        | Sim             | Sim                    | *Não*  | *Não*   |
| Diners Club      | CreditCard::DINERS     | Sim             | Sim                    | *Não*  | *Não*   |
| Discover         | CreditCard::DISCOVER   | Sim             | *Não*                  | *Não*  | *Não*   |
| JCB              | CreditCard::JCB        | Sim             | Sim                    | *Não*  | *Não*   |
| Aura             | CreditCard::AURA       | Sim             | Sim                    | *Não*  | *Não*   |

## Status da transação

O status retornado em `Payment.Status` pode ser verificado usando as constantes de `TransactionStatus`:

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Ecommerce\TransactionStatus;
```

Exemplo de uso:

```php
if ((int) $sale->getPayment()->getStatus() === TransactionStatus::AUTHORIZED) {
    // apto a capturar
}
```

## Utilizando o SDK

Os exemplos abaixo assumem que o SDK já foi configurado conforme a seção [Configurando o SDK](#configurando-o-sdk). O comentário `// ...` indica a continuação desse setup.

### Configurando o SDK

```php
<?php
require 'vendor/autoload.php';

use Cielo\API30\Merchant;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\CieloEcommerce;

// Configure o ambiente
$environment = Environment::sandbox();

// Configure seu merchant
$merchant = new Merchant('MERCHANT ID', 'MERCHANT KEY');

// Crie uma instância de CieloEcommerce informando o merchant e o ambiente
$cieloEcommerce = new CieloEcommerce($merchant, $environment);
```

### Criando um pagamento com cartão de crédito

```php
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...

$sale = new Sale('123');
$sale->customer('Fulano de Tal');

$payment = $sale->payment(15700);
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", CreditCard::VISA)
        ->setExpirationDate("12/2018")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal");

try {
    $sale = $cieloEcommerce->createSale($sale);

    $paymentId = $sale->getPayment()->getPaymentId();

    $sale = $cieloEcommerce->captureSale($paymentId, 15700, 0);
    $sale = $cieloEcommerce->cancelSale($paymentId, 15700);
} catch (CieloRequestException $e) {
    $error = $e->getCieloError();
}
```

### Criando um pagamento e gerando o token do cartão de crédito

```php
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...

$sale = new Sale('123');
$sale->customer('Fulano de Tal');

$payment = $sale->payment(15700);
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", CreditCard::VISA)
        ->setExpirationDate("12/2018")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal")
        ->setSaveCard(true);

try {
    $sale = $cieloEcommerce->createSale($sale);

    $token = $sale->getPayment()->getCreditCard()->getCardToken();
} catch (CieloRequestException $e) {
    $error = $e->getCieloError();
}
```

### Criando um pagamento com cartão de crédito tokenizado

```php
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...

$sale = new Sale('123');
$sale->customer('Fulano de Tal');

$payment = $sale->payment(15700);
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", CreditCard::VISA)
        ->setCardToken("TOKEN-PREVIAMENTE-ARMAZENADO");

try {
    $sale = $cieloEcommerce->createSale($sale);

    $paymentId = $sale->getPayment()->getPaymentId();
} catch (CieloRequestException $e) {
    $error = $e->getCieloError();
}
```

### Criando um pagamento recorrente

```php
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\RecurrentPayment;
use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...

$sale = new Sale('123');
$sale->customer('Fulano de Tal');

$payment = $sale->payment(15700);
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
        ->creditCard("123", CreditCard::VISA)
        ->setExpirationDate("12/2018")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal");

$payment->recurrentPayment(true)->setInterval(RecurrentPayment::INTERVAL_MONTHLY);

try {
    $sale = $cieloEcommerce->createSale($sale);

    $recurrentPaymentId = $sale->getPayment()->getRecurrentPayment()->getRecurrentPaymentId();
} catch (CieloRequestException $e) {
    $error = $e->getCieloError();
}
```

### Criando transações com cartão de débito

```php
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...

$sale = new Sale('123');
$sale->customer('Fulano de Tal');

$payment = $sale->payment(15700);
$payment->setReturnUrl('https://localhost/test');
$payment->debitCard("123", CreditCard::VISA)
        ->setExpirationDate("12/2018")
        ->setCardNumber("0000000000000001")
        ->setHolder("Fulano de Tal");

try {
    $sale = $cieloEcommerce->createSale($sale);

    $paymentId = $sale->getPayment()->getPaymentId();
    $authenticationUrl = $sale->getPayment()->getAuthenticationUrl();
} catch (CieloRequestException $e) {
    $error = $e->getCieloError();
}
```

### Criando uma venda com Boleto

```php
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...

$sale = new Sale('123');
$sale->customer('Fulano de Tal')
     ->setIdentity('00000000001')
     ->setIdentityType('CPF')
     ->address()->setZipCode('22750012')
                ->setCountry('BRA')
                ->setState('RJ')
                ->setCity('Rio de Janeiro')
                ->setDistrict('Centro')
                ->setStreet('Av Marechal Camara')
                ->setNumber('123');

$sale->payment(15700)
     ->setType(Payment::PAYMENTTYPE_BOLETO)
     ->setAddress('Rua de Teste')
     ->setBoletoNumber('1234')
     ->setAssignor('Empresa de Teste')
     ->setDemonstrative('Desmonstrative Teste')
     ->setExpirationDate(date('d/m/Y', strtotime('+1 month')))
     ->setIdentification('11884926754')
     ->setInstructions('Esse é um boleto de exemplo');

try {
    $sale = $cieloEcommerce->createSale($sale);

    $paymentId = $sale->getPayment()->getPaymentId();
    $boletoURL = $sale->getPayment()->getUrl();

    printf("URL Boleto: %s\n", $boletoURL);
} catch (CieloRequestException $e) {
    $error = $e->getCieloError();
}
```

### Criando uma venda com PIX

```php
use Cielo\API30\Ecommerce\Sale;

// ...

$sale = new Sale('123');
$sale->customer('Fulano de Tal');

$payment = $sale->payment(15700);
$payment->pix();
$payment->setCapture(true);

$sale = $cieloEcommerce->createSale($sale);
```

### Tokenizando um cartão

```php
use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Request\CieloRequestException;

// ...

$card = new CreditCard();
$card->setCustomerName('Fulano de Tal');
$card->setCardNumber('0000000000000001');
$card->setHolder('Fulano de Tal');
$card->setExpirationDate('10/2020');
$card->setBrand(CreditCard::VISA);

try {
    $card = $cieloEcommerce->tokenizeCard($card);

    $cardToken = $card->getCardToken();
} catch (CieloRequestException $e) {
    $error = $e->getCieloError();
}
```

### Pagamento com autenticação 3DS (ExternalAuthentication)

Após a autenticação 3DS no front-end, envie os dados retornados no pagamento:

```php
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\ExternalAuthentication;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;

// ...

$sale = new Sale('123');
$payment = $sale->payment(15700);
$payment->setType(Payment::PAYMENTTYPE_CREDITCARD)
    ->setAuthenticate(true)
    ->creditCard('123', CreditCard::MASTERCARD)
    ->setExpirationDate('12/2035')
    ->setCardNumber('5502095822650000')
    ->setHolder('Fulano de Tal');

$payment->externalAuthentication(cavv: 'AAABB...', eci: '5', version: '2.2.0')
    ->setXid('Uk5Z...')
    ->setReferenceId('a24a5d87-b1a1-4aef-a37b-2f30b91274e6');

// ou via setExternalAuthentication
$external = (new ExternalAuthentication())
    ->setCavv('AAABB...')
    ->setEci('5')
    ->setVersion('2.2.0');
$payment->setExternalAuthentication($external);

$sale = $cieloEcommerce->createSale($sale);
```

Para o fluxo Data Only (3DS 2.2), informe `dataOnly: true`:

```php
$payment->externalAuthentication(cavv: '4', eci: '5', dataOnly: true, version: '2.2.0')
    ->setReferenceId('a24a5d87-b1a1-4aef-a37b-2f30b91274e6');
```

### Token 3DS para o front-end

Gere o token de acesso no back-end e repasse ao script 3DS no front-end:

```php
// ...

$token = $cieloEcommerce->create3DSAccessToken(
    clientId: 'SEU_CLIENT_ID_3DS',
    clientSecret: 'SEU_CLIENT_SECRET_3DS',
    establishmentCode: 1006993068,
    merchantName: 'Loja Exemplo Ltda',
    mcc: 5999,
);

$accessToken = $token->getAccessToken();
```

### Outros endpoints (RequestService)

Qualquer endpoint da API pode ser utilizado através dos métodos `apiQueryRequest()` e `apiRequest()` do `RequestService`.

```php
// ...

// Consulta BIN do cartão
$response = $cieloEcommerce->requestService()->apiQueryRequest('1/cardBin/539861');
$cardBin = $response->json();

// Zero Auth
$response = $cieloEcommerce->requestService()->apiRequest(
    method: 'POST',
    endpoint: '1/zeroauth',
    body: [
        'CardNumber' => '5502095822650000',
        'Holder' => 'Aline de Souza',
        'ExpirationDate' => '12/2035',
        'SecurityCode' => '123',
        'Brand' => 'Master',
    ],
);
$zeroAuth = $response->json();
```

### Autenticação 3DS - novo MPI Cielo (V3)

> [!NOTE]
> O novo MPI Cielo (V3) é exclusivo para clientes com certificação PCI.

```php
// ...

$threeDSecureService = $cieloEcommerce->getThreeDSecureService()
    ->setClientId($clientId)
    ->setClientSecret($clientSecret)
    ->setEstablishmentCode($establishmentCode)
    ->setMerchantName($merchantName)
    ->setMcc($mcc);

// AUTH: obter o access_token para o back-end
$token = $threeDSecureService->generateAccessTokenMpiV3();

// INIT: inicializar a sessão o token retornado deve ser enviado ao front-end para inicializar o script MPI.
$response = $cieloEcommerce->requestService()->mpiApiRequest(
    method: 'POST',
    endpoint: '/v3/3ds/init',
    headers: [
        'Authorization' => "Bearer {$token->getAccessToken()}"
    ],
    body: [
        "orderNumber" => "ORD-0000123",
        "currency" => "986",
        "amount" => "1000"
    ]
);
```

## Manual

Para mais informações sobre a integração com a API 3.0 da Cielo, vide o manual em: [Integração API 3.0](https://docs.cielo.com.br/ecommerce-cielo/docs/sobre-api-ecommerce)
