# PHP Library for Survease API

Join Survease platform here: https://www.survease.com/

### Installation
```
composer require survease/php-lib
```

### Example
```php
<?php

$httpClient = \Survease\Api\HttpClientFactory::make('apikey');

$client = new \Survease\Api\Client($httpClient);

// Add a single invitation to dispatch

$resource = $client->survey('surveyId')
    ->invitations()
    ->add(new \Survease\Api\DTO\InvitationRecipient('email@email.com', 'John', 'Snow', 'ru'));
    
$response = $client->makeRequest($resource);
    
if ($response->isSuccessful()) {
    echo "Well done";
}

```
