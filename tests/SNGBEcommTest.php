<?php
/**
 * Created by PhpStorm.
 * User: ibodnar
 * Date: 09.08.15
 * Time: 21:15
 */

namespace SNGBE\Tests;

use PHPUnit_Framework_TestCase;
use SNGBE\Payment;
use SNGBE\SNGBEcomm;

class SNGBEcommTest extends PHPUnit_Framework_TestCase
{


    public function testCreatePayment()
    {

        $sngb = new SNGBEcomm('64fdfab72758601fbff4dd0ef54fa6e6d96338f5', '7000', '7000-alias', false, false);

        $this->assertEquals('64fdfab72758601fbff4dd0ef54fa6e6d96338f5', $sngb->getApiKey(), 'Не совпадает поле merchant');
        $this->assertEquals('7000', $sngb->getMerchant(), 'Не совпадает поле terminal alias');
        $this->assertEquals('7000-alias', $sngb->getTerminalAlias(), 'Не совпадает поле apiKey');
        $this->assertEquals(false, $sngb->isLiveMode(), 'Не совпадает поле liveMode');
        $this->assertEquals(false, $sngb->isVerifySslCerts(), 'Не совпадает поле verifySslCerts');

        $payment = $sngb->createPayment(2, 10, Payment::$PURCHASE);
        $url = $payment->init();
        $this->assertTrue(strpos($url, 'https://ecm.sngb.ru:443/ECommerce/hppaction?formAction') == 0, 'Неправильно сгенерирован урл оплаты');


        $this->assertEquals('Card Number Invalid', $payment->isError('CGW000029', 'NOT APPROVED', '03'), 'Не совпадает ответ по ошибке');
        $this->assertEquals('Card Number Missing', $payment->isError('CGW000030', 'NOT APPROVED', '03'), 'Не совпадает ответ по ошибке');

        $this->assertEquals('Операция оплаты не удалась. Причина: неправильный сервер обработки платежа.', $payment->isError(null, 'NOT APPROVED', '03'), 'Не должна была отвалидироваться сигнатура');
        $this->assertNull($payment->isError(null, 'CAPTURED', '00', '96ee854ccb918fbdbcdcc9f0cc350d96d97da255'), 'Не прошел платеж с правильной сигнатурой');
        $this->assertNotNull($payment->isError(null, 'NOT APPROVED', '03', '96ee854ccb918fbdbcdcc9f0cc350d96d97da255'), 'Прошел платеж с неправильной сигнатурой');


    }

    public function testGetBaseUrl()
    {
        $sngb = new SNGBEcomm('64fdfab72758601fbff4dd0ef54fa6e6d96338f5', '7000', '7000-alias', false, false);
        $this->assertEquals('https://ecm.sngb.ru/ECommerce', $sngb->getBaseUrl());
        $sngb->setLiveMode(true);
        $this->assertEquals('https://ecm.sngb.ru/Gateway', $sngb->getBaseUrl());
    }
}
