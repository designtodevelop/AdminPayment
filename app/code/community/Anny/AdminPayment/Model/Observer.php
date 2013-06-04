<?php

class Anny_AdminPayment_Model_Observer
{
	const PAYMENT_METHOD_CODE = 'adminpayment_payment';

	const DEBUG_LOG_ENABLED = false;
	const DEBUG_LOG_FILE = 'adminpayment.log';

	public function getOrderStatusToForce()
	{
		$status = Mage::getStoreConfig('payment/adminpayment_payment/force_status');
		if($status)
		{
			$res = Mage::getSingleton('core/resource');
			$adapter = $res->getConnection('core_read');
			$select = $adapter->select()
				->from( array( 'status_table' => $res->getTableName('sales/order_status')))
				->join(
					array( 'state_table' => $res->getTableName('sales/order_status_state')),
					'status_table.status = state_table.status',
					array('state')
				)
				->where( 'status_table.status = ?', $status )
				->limit(1);

			if( $statusInfo = $adapter->query($select)->fetch())
			{
				return new Varien_Object($statusInfo);
			}
		}

		return false;
	}

	protected function forceOrderStatusOnOrder( $_order, $statusInfo )
	{
		$status = $statusInfo->getStatus();
		$state = $statusInfo->getState();

		try
		{
			$_order->setState( $state, $status )->save();

			$log = sprintf( "Order ID: %d\nState: %s\nStatus: %s",
				$_order->getIncrementId()
				$state,
				$status
			);

			Mage::log( $log, null, self::DEBUG_LOG_FILE, self::DEBUG_LOG_ENABLED );
		}
		catch( Exception $e )
		{
			Mage::log( $e->getMessage(), null, self::DEBUG_LOG_FILE, self::DEBUG_LOG_ENABLED );
		}
	}

	public function forceOrderStatus($observer)
	{
		$orderIds = $observer->getEvent()->getOrderIds();
		$statusInfo = $this->getOrderStatusToForce();

		foreach( $orderIds as $orderId )
		{
			$_order = Mage::getModel('sales/order')->load($orderId);
			$method = $_order->getPayment()->getMethod();
			if( $method == self::PAYMENT_METHOD_CODE && $statusInfo )
			{
				$this->forceOrderStatusOnOrder( $_order, $statusInfo );
			}
		}
	}
}
