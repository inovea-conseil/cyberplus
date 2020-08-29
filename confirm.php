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
 *     	\file       htdocs/public/cmcic/confirm.php
 *		\ingroup    cmcic
 */

define("NOLOGIN",1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK",1);	// We accept to go on this page from external web site.

$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/security.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/compta/paiement/class/paiement.class.php");
require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");

dol_include_once('/cyberplus/class/cyberplus.class.php');
dol_syslog('CyberPlus: confirmation page has been called'); 
// Security check
if (empty($conf->cyberplus->enabled)) 
    exit;

$langs->setDefaultLang('fr_FR');

$langs->load("main");
$langs->load("other");
$langs->load("dict");
$langs->load("bills");
$langs->load("companies");
$langs->load("errors");
$langs->load("cyberplus@cyberplus");



// Check module configuration
$error = false;
dol_syslog('CyberPlus: Check configuration'); 

// Check module configuration
if (empty($conf->global->API_KEY))
{
	$error = true;
	dol_syslog('CyberPlus: Configuration error : key is not defined');    
}

if (empty($conf->global->API_SHOP_ID))
{
	$error = true;
	dol_syslog('CyberPlus: Configuration error : society ID is not defined');    
} 
    
if ($error)
{
    exit;
}

// Controle signature
$data = $_POST;
$fields = array();
foreach ($data as $name => $value)
{
	if(substr($name, 0, 5) == 'vads_') 
	{
		$name = substr($name, 5);
		$fields[$name] = $value;
	}
}


ksort($fields); // 
$signature = '';
foreach ($fields as $name => $value)
{
	$signature .= $value.'+';
}	
$signature .= $conf->global->API_KEY;
$signature = sha1($signature);
$received = GETPOST('signature');

if (strcmp($signature, $received) != 0)
{
	$error = true;
	dol_syslog('CyberPlus: Received signature differs. Received : '.$received.', computed : '.$signature);
}

if ($error)
{
    exit;
}


$key = $fields['order_id'];
$cyberplus = new CyberPlus($db);
$result = $cyberplus->fetch('', $key);

if ($result <= 0)
{
	$error = true;
	dol_syslog('CyberPlus: Invoice/order with specified reference does not exist, confirmation payment email has not been sent');
	exit;
}

$isInvoice = ($cyberplus->type == 'invoice' ? true : false);

$item = ($isInvoice) ? new Facture($db) : new Commande($db);
$result = $item->fetch($cyberplus->fk_object);	
$item->fetch_thirdparty();

$referenceDolibarr = $item->ref;

$dateTransaction = $fields['trans_date'];
$referenceTransaction = $fields['trans_id'];
$referenceAutorisation = $fields['auth_number'];

$amountTransaction = $fields['effective_amount'];
$clientBankName = ''; 
$clientName = $item->thirdparty->name;

$substit = array(
	'__OBJREF__' => $referenceDolibarr,
	'__SOCNAM__' => $conf->global->MAIN_INFO_SOCIETE_NOM,
	'__SOCMAI__' => $conf->global->MAIN_INFO_SOCIETE_MAIL,
	'__CLINAM__' => $clientName,                
	'__AMOOBJ__' => $amountTransaction/100,
);
	
$vads_result = intval($fields['result']);
$success = ($vads_result == 0 ? true : false);
            
// Update DB
if ($success)
{
	dol_syslog('CyberPlus: Payment accepted');
	
    // If order, first convert it into invoice, then mark is as paid
    if (!$isInvoice)
    { 
        $item->fetch_lines();
        
        // Create invoice
        $invoice = new Facture($db);
        $result = $invoice->createFromOrder($item);
        
        $item = new Facture($db);
        $item->fetch($invoice->id);
        $item->fetch_thirdparty();                  
    }
    
	
	  
    // Set transaction reference 
    $item->setValueFrom('ref_int', $referenceTransaction);
    $id = $item->id;        
    
    $db->begin();
    
    $amount = $amountTransaction/100; // Convert to EUR
    
    // Creation of payment line
    $payment = new Paiement($db);
    $payment->datepaye     = dol_now();
    $payment->amounts      = array($id => price2num($amount));  
    //$payment->amount      = $amount;    
    $payment->paiementid   = $conf->global->BANK_ACCOUNT_PAYMENT_ID ? $conf->global->BANK_ACCOUNT_PAYMENT_ID : dol_getIdFromCode($db, 'CB', 'c_paiement');
    $payment->num_paiement = $referenceAutorisation;
    $payment->note         = '';


	// Fix agenda module
	$salesrep = $item->thirdparty->getSalesRepresentatives($user);			
				
	if (count($salesrep) > 0)
	{
		$val = array_shift($salesrep);
		$user->id = $val['id'];
	}
	else
	{
		$user->id = 1;
	}
	
    $paymentId = $payment->create($user, $conf->global->UPDATE_INVOICE_STATUT);

    if ($paymentId < 0)
    {
        dol_syslog('CyberPlus: Payment has not been created in the database');
    }
    else
    {
		if (!empty($conf->global->BANK_ACCOUNT_ID))
		{
			$payment->addPaymentToBank($user, 'payment', '(CustomerInvoicePayment)', $conf->global->BANK_ACCOUNT_ID, $clientName, $clientBankName);      
		}        
    }
                
    $db->commit(); 
    
    $subject = ($isInvoice ? $langs->transnoentities('InvoiceSuccessPaymentEmailSubject') : $langs->transnoentities('OrderSuccessPaymentEmailSubject'));         
    $message = ($isInvoice ? $langs->transnoentities('InvoiceSuccessPaymentEmailBody') : $langs->transnoentities('OrderSuccessPaymentEmailBody'));
    
    $subject = make_substitutions($subject, $substit);           
    $message = make_substitutions($message, $substit);        
          
}else{

    dol_syslog('CyberPlus: Payment refused');
    $message = '';
    
    switch($vads_result)
    {
    	case 17 : 
    		$message = $langs->transnoentities('ErrorPaymentCanceledEmail');
    	break;
    	case 5 : 
    		$message = $langs->transnoentities('ErrorPaymentUnauthorizedEmail');
    	break;  
    	default : 
    		$message = $langs->transnoentities('ErrorPaymentTechnicalErrorEmail');
    	break;  	
    }
    
    $subject = ($isInvoice ? $langs->transnoentities('InvoiceErrorPaymentEmailSubject') : $langs->transnoentities('OrderErrorPaymentEmailSubject'));         
    if ($vads_result != 17)
    {
    	$message .= ($isInvoice ? $langs->transnoentities('InvoiceErrorPaymentEmailBody') : $langs->transnoentities('OrderErrorPaymentEmailBody'));    
    }
    $subject = make_substitutions($subject, $substit);           
    $message = make_substitutions($message, $substit);    
}

if (!$error)
{
    //Get data for email  
	$sendto = $item->thirdparty->email;
  

    $from = $conf->global->MAIN_INFO_SOCIETE_MAIL;
             
	$message = str_replace('\n',"<br />", $message);
	
	$deliveryreceipt = 0;//$conf->global->DELIVERY_RECEIPT_EMAIL;
	$addr_cc = ($conf->global->CC_EMAIL ? $conf->global->MAIN_INFO_SOCIETE_MAIL: "");

	if (!empty($conf->global->CC_EMAILS)){
		$addr_cc.= (empty($addr_cc) ? $conf->global->CC_EMAILS : ','.$conf->global->CC_EMAILS);
	}

	$mail = new CMailFile($subject, $sendto, $from, $message, array(), array(), array(), $addr_cc, "", $deliveryreceipt, 1);
	$result = $mail->error;
            
    if (!$result)
    {
        $result = $mail->sendfile();
        if ($result){
            dol_syslog('CyberPlus: Confirmation payment email has been correctly sent');
        }else{
            dol_syslog('CyberPlus: Error sending confirmation payment email');
        }
    }
    else
    {
        dol_syslog('CyberPlus: Error in creating confirmation payment email');
    }    
}


$db->close();
?>