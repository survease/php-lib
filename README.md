# PHP Library for Survease API

Join Survease platform here: https://www.survease.com/

### Installation
```
composer require survease/php-lib
```

### Example
```php
<?php

$httpClient = \Survease\HttpClientFactory::make('apikey');

$client = new \Survease\Client($httpClient);

// Add a single invitation to dispatch

$response = $client->survey('surveyId')
    ->invitations()
    ->add(new \Survease\DTO\InvitationRecipient('email@email.com', 'John', 'Snow', 'ru'))
    ->dispatch();
    
if ($response->isSuccessful()) {
    echo "Well done";
}

```
