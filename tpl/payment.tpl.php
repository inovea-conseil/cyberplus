<?php
/* Copyright (C) 2012      Mikael Carlavan        <mcarlavan@qis-network.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *     	\file       htdocs/public/cmcic/tpl/payment_form.php
 *		\ingroup    cmcic
 */
  
if (empty($conf->cyberplus->enabled)) 
    exit;

header('Content-type: text/html; charset=utf-8');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta name="robots" content="noindex,nofollow" />
    <title><?php echo $langs->trans('PaymentFormTitle'); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo DOL_URL_ROOT.$conf->css.'?lang='.$langs->defaultlang; ?>" />
    <style type="text/css">
        body{
            width : 50%; 
            margin: auto;
            text-align : center;
        }
        
        #logo{
            width : 100%;
            margin : 30px 0px 30px 0px;
        }       

        #payment-content{
            width : 100%;
            text-align : left;
        }
        
        #payment-table{
            width : 100%;
            text-align : left;
            border : 1px solid #000;            
        }

        #payment-table tr{
            width : 100%;
        }        
        
        .liste_total{
            text-align : left;
        }
        
        .payment-row-left{
            width : 40%;
            text-align : left;

        }
        
        .payment-row-right{
            width : 60%;
            text-align : right;
        } 
        
        .payment-button{
            text-align : right;  
        }                 
    </style>
</head>

<body>
    <div id="logo">
        <?php if (!empty($urlLogo)) { ?>    
            <img id="paymentlogo" title="<?php echo $societyName; ?>" src="<?php echo $urlLogo; ?>" />
        <?php } ?>        
    </div>
       
    <div id="payment-content">
        <h1><?php echo $welcomeTitle; ?></h1><br />
        
        <p><?php echo $welcomeText; ?></p> 
        <p><?php echo $descText; ?></p>
        
    <table id="payment-table">
  
            <tr class="liste_total">
                <td colspan="2"><?php echo ($isInvoice ? $langs->trans('InvoicePaymentInfo') : $langs->trans('OrderPaymentInfo') ); ?></td>
            </tr>
            <tr>
                <td class="payment-row-left"><?php echo $langs->trans('Creditor'); ?> :</td>
                <td class="payment-row-right"><strong><?php echo $creditorName; ?></strong></td>
            </tr>
            <tr>
                <td class="payment-row-left">
                    <?php echo ($isInvoice ? $langs->trans('InvoiceReference') : $langs->trans('OrderReference')); ?> :
                </td>
                <td class="payment-row-right"><strong><?php echo $item->ref; ?></strong></td>
            </tr>
            <tr>
                <td class="payment-row-left"><?php echo $langs->trans('TransactionReference'); ?> :</td>
                <td class="payment-row-right"><strong><?php echo $idTransaction; ?></strong></td>
            </tr>
            <tr>
                <td class="payment-row-left"><?php echo $langs->trans('CustomerName'); ?> :</td>
                <td class="payment-row-right"><strong><?php echo $customerName; ?></strong></td>
            </tr> 
            <tr>
                <td class="payment-row-left"><?php echo $langs->trans('CustomerEmail'); ?> :</td>
                <td class="payment-row-right"><strong><?php echo $customerEmail; ?></strong></td>
            </tr> 
            <tr class="liste_total">
                <td colspan="2">&nbsp;</td>
            </tr>                                                           
            <tr>
                <td class="payment-row-left"><?php echo ($isInvoice ? $langs->trans('InvoiceAmount') : $langs->trans('OrderAmount'));?> :</td>
                <td class="payment-row-right"><strong><?php echo price($totalObject); ?> <?php echo $currency; ?> TTC</strong></td>
            </tr>
            <tr>
                <td class="payment-row-left"><?php echo $langs->trans('AmountAlreadyPaid');?> :</td>
                <td class="payment-row-right"><strong><?php echo price($alreadyPaid); ?> <?php echo $currency; ?> TTC</strong></td>
            </tr>
            <tr>
                <td class="payment-row-left"><?php echo $langs->trans('AmountToPay');?> :</td>
                <td class="payment-row-right"><strong><?php echo price($amountTransaction); ?> <?php echo $currency; ?> TTC</strong></td>
            </tr>                    
            <tr class="liste_total">
                <td colspan="2" class="payment-button">
                    <form action="<?php echo $urlServer; ?>" method="post" id="PaymentRequest">
                    <?php foreach ($fields as $name => $value) { ?>
                    	<input type="hidden" name="vads_<?php echo $name; ?>" value="<?php echo $value; ?>" />
                    <?php } ?>
                    <input type="hidden" name="signature" value="<?php echo $signature; ?>" />
                    <input class="button" name="submit" type="submit" value="<?php echo $langs->trans('Continue'); ?>" />
                    </form>
                </td>
            </tr>
    </table>         
    </div>
    
</body>
</html>
