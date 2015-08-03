##PHP библиотека для интернет эквайринга Сургутнефтегазбанка

==================================================

Как реализовать техническую интеграцию с нашим сервисом интернет-эквайринг:
* Вы можете воспользоваться нашим HTTP API.
* Если ваша серверная часть написана на PHP, то вы можете воспользоваться нашим модулем sngbecomm-php.
 
[sampleshop-php](https://github.com/Surgutneftegasbank/sampleshop-php) – это пример магазина, с модулем.
 
Надо подключить модуль

```
   $ composer require sngbecomm-php 
```

```php
  require_once("vendor/autoload.php");
```
    
###Быстрый старт:

В личном кабинете создайте psk. (make psk)
Также в личном кабинете можно посмотреть свой terminal id и alias

```php
    $sngb = new SNGBEcomm('sdfkjhb23y82ybvybvkwubyv28', '7000', '7000-alias', true);
```
 
Получить url, платежной страницы банка, на которую надо перенаправить пользователя.

```php
    
    //trackid – это ваш id операции платежа,
    //Amount это цена
    $additional_fields = array(
    // Номер заказа
    "udf1" => $id,
    // Наш номер тех. поддержки
    "udf2" => "8 800 xxx xxx 88"
    );
    $payment = $sngb->createPayment($trackId, $amount, Payment::$PURCHASE);
    $url = $payment->init($additional_fields);
```
 
Чтобы перейти на боевой сервер:

```php
  $sngb->setLiveMode(true);
```
 

###Обработка ошибок в процессе оплаты:

  ($error – это сообщение об ошибке, которое приходит на notification url)
```php
    $errormessage = $payment->isError($error, $result, $responsecode, $hashresponse); // переменные полученные из запроса
```
 
`$errormessage` если пустой, то все замечательно.
Если нет, то он хранит текст сообщение об ошибке.

### Notification callback
Создайте notification url в личном кабинете (callback от нашего сервиса после попытки оплаты клиента на платежной странице банка на ваш сервер)

Пример
```php

    $request = $app->request;

    $trackid = $request->params("trackid");

    // Получаем из бд нужную операцию платежа,
    // если конечно у нас есть trackid
    $payment_object = R::load('payment', $trackid);
    $action = $payment_object->action;
    $amount = $payment_object->amount;

    $error = $request->params('Error');
    $result = $request->params('result');
    $responsecode = $request->params('responsecode');
    $hashresponse = $request->params('udf5');

    $payment = $sngb->createPayment($trackId, $amount, $action);
    $errormessage = $payment->isError($error, $result, $responsecode, $hashresponse);

    $log = $app->getLog();
    $log->info("REQUEST BODY: " . $request->getBody());
    if ($errormessage) {
        // TODO обработать ошибку
    }
    else {
        // TODO обработать успешный результат
    }
```

Это базовое использование.
Все это находится еще в процессе доводки, и вы можете задавать любые вопросы.
