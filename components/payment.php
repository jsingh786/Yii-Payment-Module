<?php

/**
 *  Ipay Class
 *
 *  A class to listen for and handle Instant Payment Notifications (IPN) from 
 *  the PayPal server.
 *
 *
 *  @package    Ipay88 Payment
 *  @author     Subodh Kr. Prasad
 *  @copyright  (c) 2015 - Subodh
 *  @version 1.0
 *  @email pdsubodh@gmail.com
 */
class Ipay extends CApplicationComponent {

    /**
     * Normal iPay88 payment method
     */
    const TRANSACTION_TYPE_PAYMENT = 'payment';

    /**
     * Normal iPay88 recurring payment subscription
     */
    const TRANSACTION_TYPE_RECURRING_SUBSCRIPTION = 'recurring_subscription';

    /**
     * Normal iPay88 recurring payment termination
     */
    const TRANSACTION_TYPE_RECURRING_TERMINATION = 'recurring_termination';

    /**
     * Merchant code assigned by iPay88
     */
    public $merchantCode;
    
    /**
     * Merchant Key assigned by iPay88
     */
    public $merchantKey;
    
    /**
     * Currency Code max length 5
     */
    public $currencyCode;
    
    /**
     * Merchant code assigned by iPay88
     */    
    public $responseUrl;
    
    /*
     * Response Url or Return Url after payment
     */
    public $paymentUrl;
    
    /*
     * Backend Url or Notify Url after payment (Send response by iPay88 server)
     */
    public $backendUrl;
    
    /*
     * Requery from iPay88 server regarding bill details
     */
    public $requeryUrl;
    
     /*
     * ipay88 Recurring Payment Url
     */
    public $recurringUrlSubscription;
    
     /*
     * ipay88 Recurring Payment Termination Url
     */
    public $recurringUrlTermination;
    
    /*
     * Payment methods, please view technical spec for latest update.
     */
    private $paymentMethods = array(
        54 => array('Alipay', 'USD'),
        8 => array('Alliance Online Transfer', 'MYR'),
        10 => array('AmBank', 'MYR'),
        21 => array('China Union Pay', 'MYR'),
        20 => array('CIMB Clicks', 'MYR'),
        39 => array('Credit Card', 'AUD'),
        37 => array('Credit Card', 'CAD'),
        41 => array('Credit Card', 'EUR'),
        35 => array('Credit Card', 'GBP'),
        42 => array('Credit Card', 'HKD'),
        46 => array('Credit Card', 'IDR'),
        45 => array('Credit Card', 'INR'),
        2 => array('Credit Card', 'MYR'),
        40 => array('Credit Card', 'MYR'), // For multi-currency only
        47 => array('Credit Card', 'PHP'),
        38 => array('Credit Card', 'SGD'),
        36 => array('Credit Card', 'THB'),
        50 => array('Credit Card', 'TWD'),
        25 => array('Credit Card', 'USD'),
        16 => array('FPX', 'MYR'),
        15 => array('Hong Leong Bank Transfer', 'MYR'),
        6 => array('Maybank2U', 'MYR'),
        23 => array('Meps Cash', 'MYR'),
        17 => array('Mobile Money', 'MYR'),
        32 => array('Payeasy', 'PHP'),
        65 => array('PayPal', 'AUD'),
        63 => array('PayPal', 'CAD'),
        66 => array('PayPal', 'EUR'),
        61 => array('PayPal', 'GBP'),
        67 => array('PayPal', 'HKD'),
        48 => array('PayPal', 'MYR'),
        56 => array('PayPal', 'PHP'),
        64 => array('PayPal', 'SGD'),
        62 => array('PayPal', 'THB'),
        68 => array('PayPal', 'TWD'),
        33 => array('PayPal', 'USD'),
        53 => array('Paysbuy (Credit Card only)', 'THB'),
        52 => array('Paysbuy (E-wallet & Counter Services only)', 'THB'),
        14 => array('RHB', 'MYR'),
    );
    
    /*
     * Details to be sent to IPay88 for payment request.
     */    
    private $paymentRequest = array(
        'MerchantCode', // Merchant code assigned by iPay88. (length 20)
        'PaymentId', // (Optional) (int)
        'RefNo', // Unique merchant transaction number / Order ID (Retry for same RefNo only valid for 30 mins). (length 20)
        'Amount', // Payment amount with two decimals.
        'Currency', // (length 5)
        'ProdDesc', // Product description. (length 100)
        'UserName', // Customer name. (length 100)
        'UserEmail', // Customer email.  (length 100)
        'UserContact', // Customer contact.  (length 20)
        'Remark', // (Optional) Merchant remarks. (length 100)
        'Lang', // (Optional) Encoding type:- ISO-8859-1 (English), UTF-8 (Unicode), GB2312 (Chinese Simplified), GD18030 (Chinese Simplified), BIG5 (Chinese Traditional)
        'Signature',
        'ResponseURL',
        'BackendURL',
    );
    
    /*
     * Details to be sent to iPay88 for recurring subscription payment request.
     */    
    private $recurringSubscriptionRequest = array(
        'MerchantCode', // Merchant code assigned by iPay88. (length 20)
        'RefNo', // Unique merchant transaction number / Order ID. (length 20)
        'FirstPaymentDate', // (ddmmyyyy)
        'Currency', // MYR only. (length 5)
        'Amount', // Payment amount with two decimals.
        'NumberOfPayments', // (int)
        'Frequency', // Frequency type; 1 - Monthly, 2 - Quarterly, 3 - Half-Yearly, 4 - Yearly. (int)
        'Desc', // Product description. (length 100)
        'CC_Name', // Name printed on credit card. (length 100)
        'CC_PAN', // 16-digit credit card number (Visa/Mastercard). (length 16)
        'CC_CVC', // 3-digit verification code behind credit card. (length 3)
        'CC_ExpiryDate', // Credit card expiry date. (mmyyyy)
        'CC_Country', // Credit card issuing country. (length 100)
        'CC_Bank', // Credit card issuing bank. (length 100)
        'CC_Ic', // Credit card holder IC / Passport number. (length 50)
        'CC_Email', // Credit card holder email address. (length 255)
        'CC_Phone', // Credit card phone number. (length 100)
        'CC_Remark', // (Optional) Remarks. (varchar 100)
        'P_Name', // Subscriber name as printed in IC / Passport. (length 100)
        'P_Email', // Subscriber email address. (length 255)
        'P_Phone', // Subscriber phone number. (length 100)
        'P_Addrl1', // Subscriber address line 1. (length 100)
        'P_Addrl2', // (Optional) Subscriber address line 2. (length 100)
        'P_City', // Subscriber city. (length 100)
        'P_State', // Subscriber state. (length 100)
        'P_Zip', // Subscriber zip code. (length 100)
        'P_Country', // Subscriber country. (varchar 100)
        'BackendURL', // Payment backend response page. (length 255)
        'Signature', // SHA1 signature. (length 100)
    );

    /*
     * Get required payment fields
     */
    public function getPaymentFields($reqParams = null, $paymentType) {
        $retnParams = array();
        try {
            if (isset($reqParams) && (count($reqParams) > 0)) {

                if (isset($paymentType) && $paymentType != "") {
                    $paymentType = strtolower(trim($paymentType));
                    switch ($paymentType) {
                        case 'payment':
                            $retnParams = $this->__getPaymentField($reqParams, $paymentType);
                            break;
                        case 'recurring_subscription':
                            $retnParams = $this->__getRecurringSubscriptionField($reqParams, $paymentType);
                            break;
                        case 'recurring_termination':
                            $retnParams = $this->__getRecurringTerminationField($reqParams, $paymentType);
                            break;
                    }
                } else {
                    throw new Exception("Ipay: Payment method missing");
                }
            } else {
                throw new Exception("Ipay: Required Parameters missing");
            }
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $retnParams;
    }

    /*
     * Code for hex2bin 
     */
    public function _hex2bin($hexSource) {
        $bin = '';
        for ($i = 0; $i < strlen($hexSource); $i = $i + 2) {
            $bin .= chr(hexdec(substr($hexSource, $i, 2)));
        }
        return $bin;
    }

     /*
     * Get payment fields for normal payment fields 
     */
    public function __getPaymentField($reqParams, $paymentType) {
        $retnParams = array();
        foreach ($this->paymentRequest as $pymtKey) {
            if (isset($reqParams[$pymtKey])) {
                $retnParams[$pymtKey] = $reqParams[$pymtKey];
            } else {

                switch ($pymtKey) {
                    case 'MerchantCode':
                        $retnParams[$pymtKey] = $this->merchantCode;
                        break;
                    case 'Currency':
                        $retnParams[$pymtKey] = $this->currencyCode;
                        break;
                    case 'Lang':
                        $retnParams[$pymtKey] = 'UTF-8'; //(Optional) Encoding type:- ISO-8859-1 (English), UTF-8 (Unicode), GB2312 (Chinese Simplified), GD18030 (Chinese Simplified), BIG5 (Chinese Traditional)
                        break;
                    case 'Signature':
                        $retnParams[$pymtKey] = $this->__createSignature($retnParams, $paymentType); // SHA1 signature.
                        break;
                    case 'ResponseURL':
                        $retnParams[$pymtKey] = $this->responseUrl; // (Optional) Payment response page.
                        break;
                    case 'BackendURL':
                        $retnParams[$pymtKey] = $this->backendUrl; // (Optional) BackendURL but should security purpose
                        break;
                }
            }
        }

        return $retnParams;
    }

    /*
     * Get payment fields for recurring payment
     */
    public function __getRecurringSubscriptionField($reqParams, $paymentType) {
        $retnParams = array();
        foreach ($this->recurringSubscriptionRequest as $pymtKey) {
            if (isset($reqParams[$pymtKey])) {
                $retnParams[$pymtKey] = $reqParams[$pymtKey];
            } else {

                switch ($pymtKey) {
                    case 'MerchantCode':
                        $retnParams[$pymtKey] = $this->merchantCode;
                        break;
                    case 'Currency':
                        $retnParams[$pymtKey] = $this->currencyCode;
                        break;
                    case 'Lang':
                        $retnParams[$pymtKey] = 'UTF-8'; //(Optional) Encoding type:- ISO-8859-1 (English), UTF-8 (Unicode), GB2312 (Chinese Simplified), GD18030 (Chinese Simplified), BIG5 (Chinese Traditional)
                        break;
                    case 'Signature':
                        $retnParams[$pymtKey] = $this->__createSignature($retnParams, $paymentType); // SHA1 signature.
                        break;
                    case 'ResponseURL':
                        $retnParams[$pymtKey] = $this->responseUrl; // (Optional) Payment response page.
                        break;
                    case 'BackendURL':
                        $retnParams[$pymtKey] = $this->backendUrl; // (Optional) BackendURL but should security purpose
                        break;
                }
            }
        }

        return $retnParams;
    }

    /*
     * Get payment fields for recurring payment termination
     */
    public function __getRecurringTerminationField($reqParams, $paymentType) {
        $retnParams = array();
        foreach ($this->recurringSubscriptionRequest as $pymtKey) {
            if (isset($reqParams[$pymtKey])) {
                $retnParams[$pymtKey] = $reqParams[$pymtKey];
            } else {

                switch ($pymtKey) {
                    case 'MerchantCode':
                        $retnParams[$pymtKey] = $this->merchantCode;
                        break;
                }
            }
        }

        return $retnParams;
    }

    /*
     * Create signature for payment
     */
    public function __createSignature($signatureParams, $paymentType) {
        //echo "<pre>";
        //print_r($signatureParams);
        $signature = '';
        if (isset($signatureParams)) {
            $_signatureParams = array();
            if ($paymentType == self::TRANSACTION_TYPE_PAYMENT) {
                $_signatureParams = array('MerchantCode', 'RefNo', 'Amount', 'Currency');
            } else if ($paymentType == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
                $_signatureParams = array('MerchantCode', 'RefNo', 'FirstPaymentDate', 'Currency', 'Amount', 'NumberOfPayments', 'Frequency', 'CC_PAN');
            } else if ($paymentType == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
                $_signatureParams = array('MerchantCode', 'RefNo');
            }


            foreach ($_signatureParams as $val) {
                if (!isset($signatureParams[$val])) {
                    throw new Exception("Ipay: Missing required parameters for signature.");
                    return false;
                }
            }
        }

        // Make sure the order is correct.
        if ($paymentType == self::TRANSACTION_TYPE_PAYMENT) {
            $signature .= $this->merchantKey;
            $signature .= $signatureParams['MerchantCode'];
            $signature .= $signatureParams['PaymentId'];
            $signature .= $signatureParams['RefNo'];
            $signature .= preg_replace("/[^\d]+/", "", $signatureParams['Amount']);
            $signature .= $signatureParams['Currency'];
        } else if ($paymentType == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
            $signature .= $signatureParams['MerchantCode'];
            $signature .= $this->merchantKey;
            $signature .= $signatureParams['RefNo'];
            $signature .= $signatureParams['FirstPaymentDate'];
            $signature .= $signatureParams['Currency'];
            $signature .= $signatureParams['Amount'];
            $signature .= $signatureParams['NumberOfPayments'];
            $signature .= $signatureParams['Frequency'];
            $signature .= $signatureParams['CC_PAN'];
        } else if ($paymentType == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
            $signature .= $signatureParams['MerchantCode'];
            $signature .= $this->merchantKey;
            $signature .= $signatureParams['RefNo'];
        }


        // Hash the signature.
        return $signature = base64_encode($this->_hex2bin(sha1($signature)));
    }

    /*
     * Get url for respective payment redirection url
     */
    public function getTransactionUrl($paymentType) {
        if ($paymentType == self::TRANSACTION_TYPE_PAYMENT) {
            return $this->paymentUrl;
        } else if ($paymentType == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
            return $this->recurringUrlSubscription;
        } else if ($paymentType == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
            return $this->recurringUrlTermination;
        }
    }

    /*
     * Validate payment fields
     */
    public function validateField($field, $data) {
        switch ($field) {
            case 'MerchantCode':
            case 'RefNo':
            case 'UserContact':
                if (strlen($data) <= 20) {
                    return true;
                }
                break;
            case 'PaymentId':
            case 'NumberOfPayments':
                if (is_int($data)) {
                    return true;
                }
                break;
            case 'Amount':
                if (preg_match('^[0-9]+\.[0-9]{2}$^', $data)) {
                    return true;
                }
                break;
            case 'Currency':
                if (strlen($data) <= 5) {
                    return true;
                }
                break;
            case 'CC_Email':
            case 'P_Email':
            case 'BackendURL':
                if (strlen($data) <= 255) {
                    return true;
                }
                break;
            case 'ProdDesc':
            case 'UserName':
            case 'UserEmail':
            case 'Remark':
            case 'Desc':
            case 'CC_Name':
            case 'CC_Country':
            case 'CC_Bank':
            case 'CC_Phone':
            case 'CC_Remark':
            case 'P_Name':
            case 'P_Phone':
            case 'P_Addrl1':
            case 'P_Addrl2':
            case 'P_City':
            case 'P_State':
            case 'P_Zip':
            case 'P_Country':
                if (strlen($data) <= 100) {
                    return true;
                }
                break;
            case 'CC_Ic':
                if (strlen($data) <= 50) {
                    return true;
                }
                break;
            case 'Lang':
                if (in_array(strtoupper($data), array('ISO-8859-1', 'UTF-8', 'GB2312', 'GD18030', 'BIG5'))) {
                    return true;
                }
                break;
            case 'Signature':
                if (strlen($data) <= 40) {
                    return true;
                }
                break;
            case 'FirstPaymentDate':
                if (strlen($data) == 8) {
                    return true;
                }
                break;
            case 'CC_ExpiryDate':
                if (strlen($data) == 6) {
                    return true;
                }
                break;
            case 'Frequency':
                if (in_array((int) $data, array(1, 2, 3, 4))) {
                    return true;
                }
                break;
            case 'CC_PAN':
                if (ctype_digit($data) && strlen($data) == 16) {
                    return true;
                }
                break;
            case 'CC_CVC':
                if (ctype_digit($data) && strlen($data) == 3) {
                    return true;
                }
                break;
            case 'MerchantKey':
            case 'ResponseURL':
            case 'TransId':
            case 'AuthCode':
            case 'Status':
            case 'ErrDesc':
            case 'SubscriptionNo':
                return true;
        }

        return false;
    }

    /*
     * iPay88 payment signature validation
     */
    public function checkiPay88Signature($reqParams) {
        $status = 'fail';
        try {
            if (isset($reqParams) && count($reqParams) > 0) {
                $orginalKey = $this->merchantKey . $this->merchantCode;
                if (isset($reqParams['RefNo'])) {
                    $orginalKey .=$reqParams['RefNo'];
                }

                if (isset($reqParams['Amount'])) {
                    $orginalKey .=preg_replace("/[^\d]+/", "", $reqParams['Amount']);
                }
                $orginalKey .= $this->currencyCode;
                if (isset($reqParams['Status'])) {
                    $orginalKey .=$reqParams['Status'];
                }

                $orginalKeyGen = base64_encode($this->_hex2bin(sha1($orginalKey)));
                $returnKey = $this->merchantKey;
                if (isset($reqParams['MerchantCode'])) {
                    $returnKey .=$reqParams['MerchantCode'];
                }


                if (isset($reqParams['RefNo'])) {
                    $returnKey .=$reqParams['RefNo'];
                }
                if (isset($reqParams['Amount'])) {
                    $returnKey .=preg_replace("/[^\d]+/", "", $reqParams['Amount']);
                }
                if (isset($reqParams['Currency'])) {
                    $returnKey .=$reqParams['Currency'];
                }
                if (isset($reqParams['Status'])) {
                    $returnKey .=$reqParams['Status'];
                }


                $returnKeyGen = base64_encode($this->_hex2bin(sha1($returnKey)));
                if ($orginalKeyGen === $returnKeyGen) {
                    $status = 'success';
                }
            } else {
                throw new Exception("Ipay::checkiPay88Signature: Params missing");
            }
        } catch (exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $status;
    }

    /*
     * Curl hit to get bill deyails 
     */
    public function requeryPayment($rawPostData) {
        try {
            $result = '';
            if (is_callable('curl_init')) {
                if (isset($rawPostData) && $rawPostData != "") {
                    $ch = curl_init();
                    $url = $this->requeryUrl . '?' . $rawPostData;
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    curl_close($ch);
                } else {
                    throw new Exception("Ipay::requeryPayment: No request string");
                }
            } else {
                throw new Exception("Ipay::requeryPayment: Curl not enabled");
            }
        } catch (exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $result;
    }

}
