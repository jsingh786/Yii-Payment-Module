<?php
/**
 *  Ipaybackend Controller  
 * 
 *  This class perform as backend process for iPay88.
 *  After making payment iPay88 send a call back to host server.
 *  This callback response return all payment related parameters with status.
 *  Here host server need to replay with RECEIVEOK to iPay server ensure payment transactions
 * 
 *  @package    PHP-Ipay
 *  @author     Subodh Kumar Prasad
 *  @copyright  (c) 2015 - Subodh
 *  @version    1.0
 *  @email      pdsubodh@gmail.com
 */
class IpaybackendController extends Controller {

    public function actionresponse() {
        $rawPostData = file_get_contents('php://input');
        
        /* Return response from normal payments: 
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
        
        /* Return response from recurring subscripton payments:
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
        
        
        /* Response from iPay88 after recurring payment is charged.
	 * - MerchantCode -
	 * - PaymentId    - Default to 2 (credit card MYR).
	 * - RefNo        - Unique transaction number returned from iPay88.
	 *                  This is the 'SubscriptionNo' returned to merchant after subscription of recurring payment.
	 *                  Eg:
	 *                    S00001701-1 (First recurring payment)
	 *                    S00001701-2 (Second recurring payment)
	 *                  The returned 'RefNo' will have a hyphen followed by a number to indicate the installment.
	 * - Amount       -
	 * - Currency     - Default to MYR.
	 * - Remark       - (Optional)
	 * - TransId      - (Optional) iPay88 transaction ID.
	 * - AuthCode     - (Optional) Bank's approval code.
	 * - Status       - Payment status:- 1 - Success, 0 - Failed.
	 * - ErrDesc      - (Optional)
	 * - Signature    -
	 */
        
        try {
            if (strlen($rawPostData) > 0) {
                $rawPostArray = explode('&', $rawPostData);
                $post = array();
                foreach ($rawPostArray as $keyval) {
                    $keyval = explode('=', $keyval);
                    if (count($keyval) == 2)
                        $post[$keyval[0]] = urldecode($keyval[1]);
                }
                //print_r($post); die;                
                if (count($post) > 0) {
                    //uncomment this line
                    if (isset($post['Status']) && $post['Status'] > 0) { //status:- 1 - Success, 0 - Failed.
                        $check = Yii::app()->ipay->checkiPay88Signature($post);

                        if ($check == 'success') {
                            $response = Yii::app()->ipay->requeryPayment($rawPostData);
                            $tokens = explode("\r\n\r\n", trim($response));
                            $res = trim(end($tokens));
                            if ($res != '00') {
                                throw new Exception("Ipay Error-5: Payment Fail due to requery response: <pre>" . print_r($res, true) . "</pre>");
                            } else {

                                //Check RefNo on payment
                                if (isset($post['RefNo']) && !empty($post['RefNo'])) {
                                    //Compare Payment amount with return parametrers and request amount sent during payment
                                    if ($cost == $post['Amount']) { //$cost = Database saved value and $post['Amount'] = payment returned value
                                        //Update Database according as per returned Value 
                                        //print_r($post);                                        
                                        //Don't delete below line. 
                                        echo "RECEIVEOK";
                                        die;
                                    } else {
                                        throw new Exception("Ipay Error-7: Paid Amount and Package Amount Mismatched<pre>" . print_r($post, true) . "</pre>");
                                    }
                                } else {
                                    throw new Exception("Ipay Error-6: Refid is missing: <pre>" . print_r($res, true) . "</pre>");
                                }
                            }
                        } else {
                            throw new Exception("Ipay Error-4: Response signature Mismatch: <pre>" . print_r($post, true) . "</pre>");
                        }
                    } else {
                        throw new Exception("Ipay Error-3: Payment Fail: <pre>" . print_r($post['ErrDesc'], true) . "</pre>");
                    }
                } else {
                    throw new Exception("Ipay Error-2: Missing Params Invalid Required Params: <pre>" . print_r($rawPostArray, true) . "</pre>");
                }
            } else {
                throw new Exception("Ipay Error-1: Missing Params");
            }
        } catch (exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        exit;
    }

}
