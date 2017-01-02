<?php
/**
 *  Ipay Controller  
 *  Access iPay88 API endpoints to start payment with various methods and collection payment.
 *  This class perform all action for iPay88 payment
 * 
 *  @package    PHP-Ipay
 *  @author     Subodh Kumar Prasad
 *  @copyright  (c) 2015 - Subodh
 *  @version    1.0
 *  @email      pdsubodh@gmail.com
 */

class IpayController extends Controller {

    const TRANSACTION_TYPE_PAYMENT = 'payment';
    const TRANSACTION_TYPE_RECURRING_SUBSCRIPTION = 'recurring_subscription';
    const TRANSACTION_TYPE_RECURRING_TERMINATION = 'recurring_termination';

    /*
     * iPay88 normal payment Method
     */
    public function actionPayment() {

        // Unique merchant transaction number / Order ID (Retry for same RefNo only valid for 30 mins). (length 20)
        $paymentParams['RefNo'] = 'TEST123';

        // (Optional) (int)
        $paymentParams['PaymentId'] = '2';

        // Payment amount with two decimals.
        $paymentParams['Amount'] = '1.00';

        // Product description. (length 100)
        $paymentParams['ProdDesc'] = 'This is a test product';

        // Customer name. (length 100)
        $paymentParams['UserName'] = 'Abc';

        // Customer email.  (length 100)
        $paymentParams['UserEmail'] = 'abc@xyz.com';

        // Customer contact.  (length 20)
        $paymentParams['UserContact'] = '*************';

        // (Optional) Merchant remarks. (length 100)
        $paymentParams['Remark'] = 'Here is the description';

        $paymentFields = Yii::app()->ipay->getPaymentFields($paymentParams, self::TRANSACTION_TYPE_PAYMENT);
        $transactionUrl = Yii::app()->ipay->getTransactionUrl(self::TRANSACTION_TYPE_PAYMENT);
        $this->render('Payment', array(
            'paymentFields' => $paymentFields,
            'transactionUrl' => $transactionUrl
        ));
    }
    
    

    /*
     * iPay88 recurring subscripton payments
     */
    public function actionRecurringSubscription() {
        // Unique merchant transaction number / Order ID (Retry for same RefNo only valid for 30 mins). (Length 20)
        $paymentParams['RefNo'] = '12345';

        // (Optional) (Date)
        $paymentParams['FirstPaymentDate'] = '22122016'; //ddmmyyyy
        // Payment amount with two decimals.
        $paymentParams['Amount'] = '1.00';

        // int
        $paymentParams['NumberOfPayments'] = '';

        // Frequency type; 1 - Monthly, 2 - Quarterly, 3 - Half-Yearly, 4 - Yearly. (int)
        $paymentParams['Frequency'] = '';

        // Product description. (length 100)
        $paymentParams['Desc'] = '';

        // Name printed on credit card. (Length 100)
        $paymentParams['CC_Name'] = '';

        //16-digit credit card number (Visa/Mastercard). (Length 16)
        $paymentParams['CC_PAN'] = '';

        // 3-digit verification code behind credit card. (Length 3)
        $paymentParams['CC_CVC'] = '';

        // Credit card expiry date. (mmyyyy)
        $paymentParams['CC_ExpiryDate'] = '';

        // Credit card issuing country. (Length 100)
        $paymentParams['CC_Country'] = '';

        // Credit card issuing bank. (Length 100)
        $paymentParams['CC_Bank'] = '';

        // Credit card holder IC / Passport number. (varchar 50)
        $paymentParams['CC_Ic'] = '';

        //Credit card holder email address. (Length 255)
        $paymentParams['CC_Email'] = '';

        // Credit card phone number. (Length 100)
        $paymentParams['CC_Phone'] = '';

        // (Optional) Remarks. (Length 100)
        $paymentParams['CC_Remark'] = '';

        // Subscriber name as printed in IC / Passport. (Length 100)
        $paymentParams['P_Name'] = '';

        // Subscriber email address. (Length 255)
        $paymentParams['P_Email'] = '';

        // Subscriber phone number. (Length 100)
        $paymentParams['P_Phone'] = '';

        // Subscriber address line 1. (Length 100)
        $paymentParams['P_Addrl1'] = '';

        // (Optional) Subscriber address line 2. (Length 100)
        $paymentParams['P_Addrl2'] = '';

        // Subscriber city. (Length 100)
        $paymentParams['P_City'] = '';

        // Subscriber state. (Length 100)
        $paymentParams['P_State'] = '';

        // Subscriber zip code. (Length 100)
        $paymentParams['P_Zip'] = '';

        // Subscriber country. (Length 100)
        $paymentParams['P_Country'] = '';

        $paymentFields = Yii::app()->ipay->getPaymentFields($paymentParams, self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION);
        $transactionUrl = Yii::app()->ipay->getTransactionUrl(self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION);
        $this->render('recurringSubscription', array(
            'paymentFields' => $paymentFields,
            'transactionUrl' => $transactionUrl
        ));
    }

    /*
     * Response after making payment through iPay88 payment gateway
     */
    public function actionResponse() {
        $rawPostData = file_get_contents('php://input');

        /* 1. Return response by payment Method 
         *  MerchantCode -
         *  PaymentId    - (Optional)
         *  RefNo        -
         *  Amount       -
         *  Currency     -
         *  Remark       - (Optional)
         *  TransId      - (Optional) IPay88 transaction Id.
         *  AuthCode     - (Optional) Bank's approval code.
         *  Status       - Payment status:- 1 - Success, 0 - Failed.
         *  ErrDesc      - (Optional) Payment status description.
         *  Signature    -
         */

        /* 2. Return response from recurring subscripton payments Method
         * - MerchantCode     -
         * - RefNo            -
         * - SubscriptionNo   - Unique iPay88 subscription number. 'SubscriptionNo' will be the 'RefNo' that will be returned back to merchant 'BackendURL' when its charged.
         * - FirstPaymentDate -
         * - Amount           -
         * - Currency         -
         * - NumberOfPayments -
         * - Frequency        -
         * - Desc             - (Optional)
         * - Status           - Subscription status:- 1 - Success, 0 - Failed.
         * - ErrDesc          - (Optional)
         */
        $resultData = array();
        if (strlen($rawPostData) > 0) {
            $rawPostArray = explode('&', $rawPostData);
            foreach ($rawPostArray as $keyval) {
                $keyval = explode('=', $keyval);
                if (count($keyval) == 2)
                    $resultData[$keyval[0]] = urldecode($keyval[1]);
            }
        }

        $this->render('response', array(
            'resultData' => $resultData,
        ));
    }

    
    /*
     * iPay88 recurring subscripton payments
     */
    public function actionRecurringTerminationRequest() {
        
        
        /* Return response for recurring termination request:
	 * - MerchantCode -
	 * - RefNo        -
	 * - Status       - Subscription status:- 1 - Success, 0 - Failed.
	 * - ErrDesc      - (Optional)
	 */
        
        // Unique merchant transaction number / Order ID. (length 20)
        $paymentParams['RefNo'] = 'UNI124';
        
        // SHA1 signature. (length 20) 
        $paymentParams['Signature'] = 'VI67TU+A+bD333TRvjOM+bokSzI=';                                
        $paymentFields = Yii::app()->ipay->getPaymentFields($paymentParams, self::TRANSACTION_TYPE_RECURRING_TERMINATION);
        $transactionUrl = Yii::app()->ipay->getTransactionUrl(self::TRANSACTION_TYPE_RECURRING_TERMINATION);
        
        
        $this->render('recurringTermination', array(
            'paymentFields' => $paymentFields,
            'transactionUrl' => $transactionUrl
        ));
    }
}
