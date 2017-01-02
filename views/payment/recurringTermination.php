<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<h3>IPay88: Payment Termination</h3>
<?php if (!empty($paymentFields)): ?>
        <form action="<?php echo $transactionUrl; ?>" method="post">
            <table>
                <?php foreach ($paymentFields as $key => $val): ?>
                    <tr>
                        <td><label><?php echo $key; ?></label></td>
                        <td><input type="text" readonly="readonly" name="<?php echo $key; ?>" value="<?php echo $val; ?>" /></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                <td colspan="2"><input type="submit" value="Terminate Now" name="Pay with IPay88" /></td>
                </tr>
            </table>
        </form>
    <?php endif; ?>