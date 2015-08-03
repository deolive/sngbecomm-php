<?php

namespace SNGBE;

/**
 * Class Payment
 *
 * @package SNGBE
 */
class Payment
{
    //Types of transaction
    static $PURCHASE = 1;
    static $CREDIT = 2;
    static $AUTHORIZATION = 4;
    static $CAPTURE = 5;
    static $VOID_CREDIT = 6;
    static $VOID_CAPTURE = 7;
    static $VOID_AUTHORIZATION = 9;

    //TODO: Дополнить список ошибок
    static $CGWERRORS = array(
        'CGW000029' => 'Card Number Invalid',
        'CGW000030' => 'Card Number Missing',
    );

    /**
     * @var SNGBEComm
     */
    private $settings;

    /**
     * @var int
     */
    private $action;


    public function __construct(SNGBEcomm $settings, $trackId, $amount, $action = null)
    {
        $this->settings = $settings;

        $this->trackId = $trackId;
        $this->price = sprintf('%.4f', $amount);
        $this->action = $action?$action:self::$PURCHASE;
        //$this->apiKey = $settings->getApiKey();
        //$this->terminalId = $settings->getMerchant();
        //$this->terminalAlias = $settings->getTerminalAlias();
    }

    /**
     * @param array $additionalFields
     *
     * @return string
     *
     * @throws PaymentException
     */
    public function init(array $additionalFields = array())
    {
        //TODO: сделать проверку на price по точке и минимальному значению
        $hash = $this->signature($this->trackId, $this->price, $this->action);

        $params = array(
            'merchant' => $this->settings->getMerchant(),
            'terminal' => $this->settings->getTerminalAlias(),
            'action' => $this->action,
            'amt' => $this->price,
            'trackid' => $this->trackId,
            'udf1' => array_key_exists('udf1', $additionalFields)?$additionalFields['udf1']: null,
            'udf2' => array_key_exists('udf2', $additionalFields)?$additionalFields['udf2']: null,
            'udf3' => array_key_exists('udf3', $additionalFields)?$additionalFields['udf3']: null,
            'udf4' => array_key_exists('udf4', $additionalFields)?$additionalFields['udf4']: null,
            'udf5' => $hash
        );

        $result = $this->curlpost($params, $this->paymentUrl());

        return $result;
    }

    /**
     * @param string $tranId
     * @param string $paymentId
     *
     * @return string
     *
     * @throws PaymentException
     */
    public function manage($tranId=null, $paymentId=null)
    {
        $price = $this->price;

        // Signature has another realiazation for manage transaction
        $salt = $this->settings->getMerchant() . $price . $this->trackId . $this->settings->getApiKey();
        $hash = sha1($salt);

        $params = array(
            'merchant' => $this->settings->getMerchant(),
            'terminal' => $this->settings->getTerminalAlias(),
            'action' => $this->action,
            'amt' => $price,
            'paymentid' => $paymentId,
            'trackid' => $this->trackId,
            'tranid' => $tranId,
            'udf5' => $hash,
        );
        $result = $this->curlpost($params, $this->manageTranUrl());

        return $result;
    }

    /**
     * @param string $error
     * @param string $result
     * @param string $responseCode
     * @param string $hashResponse
     * @return string
     */
    public function isError($error=null, $result=null, $responseCode=null, $hashResponse=null)
    {
        //TODO: Сделать возврат ошибок на EN и RU. И дать выбор языка через аргумент.
        if ($error) {
            if (array_key_exists($error, self::$CGWERRORS)) {
                return self::$CGWERRORS[$error];
            }

            return 'Оплата не удалась! Обратитесь в службу поддержки сайта.';
        }

        //TODO: Сделать конкретное сообщение по каждому аргументу
        if ($this->trackId == null or $this->price == null or $this->action == null) {
            return 'Вы забыли передать значения для функции проверки ошибок! Посмотрите в документацию.';
        }

        $hash = $this->signature();

        if ($hash != $hashResponse) {
            return 'Операция оплаты не удалась. Причина: неправильный сервер обработки платежа.';
        }

        if ($result=='CAPTURED' and $responseCode=='00') {
            return null;
        }

        if ($result=='CANCELED') {
            return 'Операция отмены оплаты';
        }

        if ($result=='NOT APPROVED') {
            switch ($responseCode) {
                case '04':
                    $outcome = 'Ошибка. Недействительный номер карты.';
                    break;
                case '14':
                    $outcome = 'Ошибка. Неверный номер карты.';
                    break;
                case '33':
                case '54':
                    $outcome = 'Ошибка. Истек срок действия карты.';
                    break;
                case 'Q1':
                    $outcome = 'Ошибка. Неверный срок действия карты или карта просрочена.';
                    break;
                case '51':
                    $outcome = 'Ошибка. Недостаточно средств.';
                    break;
                case '56':
                    $outcome = 'Ошибка. Неверный номер карты.';
                    break;
                default:
                    $outcome = 'Ошибка. Обратитесь в банк, выпустивший карту.';
            }

            return $outcome;
        }
    }

    /**
     * @param array  $params
     * @param string $url
     *
     * @return string
     */
    protected function curlpost(array $params, $url)
    {
        $postData = http_build_query($params);

        // POST
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url );
        curl_setopt ($ch, CURLOPT_POST, 1 );
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec ($ch);
        //$curl_errno = curl_errno($ch);
        //$curl_error = curl_error($ch);
        //if ($curl_errno > 0) {
            //echo "cURL Error ($curl_errno): $curl_error";
        //} else {
            //echo "Data received ";
        //}
        return $result;
    }

    /**
     * @return string
     */
    protected function paymentUrl()
    {
        return $this->settings->getBaseUrl() . '/PaymentInitServlet';
    }

    /**
     * @return string
     */
    protected function manageTranUrl()
    {
        return $this->settings->getBaseUrl() . '/PaymentTranServlet';
    }

    /**
     * Hash signature
     *
     * @return string
     */
    private function signature()
    {
        if ($this->trackId == null || $this->price==null) {
            return '0';
        }

        $apiKey = $this->settings->getApiKey();
        $merchant = $this->settings->getMerchant();

        if ($apiKey==null || $merchant==null) {
            return '0';
        }

        return sha1($merchant . $this->price . $this->trackId . $this->action . $apiKey);
    }
}
