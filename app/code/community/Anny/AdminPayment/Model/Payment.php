<?php

class Anny_AdminPayment_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
	protected $_code = 'adminpayment_payment';

	protected $_formBlockType = 'adminpayment/form';
	protected $_infoBlockType = 'adminpayment/info';

	public function isAvailable($_quote=null)
	{
		if( is_null($_quote))
		{
			return false;
		}

		if( Mage::getDesign()->getArea() == 'adminhtml' || $this->getConfigData('active_in_frontend'))
		{
			return true;
		}

		return false;
	}
}
