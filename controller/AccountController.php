<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License BY-NC-ND.
 * NonCommercial — You may not use the material for commercial purposes.
 * NoDerivatives — If you remix, transform, or build upon the material,
 * you may not distribute the modified material.
 * See the full license at http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * See http://mventory.com/legal/licensing/ for other licensing options.
 *
 * @package MVentory/TradeMe
 * @copyright Copyright (c) 2014 mVentory Ltd. (http://mventory.com)
 * @license http://creativecommons.org/licenses/by-nc-nd/4.0/
 */

/**
 * TradeMe account controller
 *
 * @package MVentory/TradeMe
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_TradeMe_AccountController
  extends Mage_Adminhtml_Controller_Action
{
  protected function _construct() {
    $this->setUsedModuleName('MVentory_TradeMe');
  }

  /**
   * Obtaines a request token and sends URL to authorize endpoint
   *
   * @return null
   */
  public function authenticateAction () {
    $accountId = $this->getRequest()->getParam('account_id');
    $website = $this->getRequest()->getParam('website');

    if (!$accountId || !$website)
      return $this->_response(array(
        'error' => true,
        'message' => $this->__('Account ID or website are not specified')
      ));

    $auth = new MVentory_TradeMe_Model_Account($accountId, $website);

    return $this->_response(
      ($ajaxRedirect = $auth->authenticate())
        ? array('ajaxRedirect' => $ajaxRedirect)
          : array(
              'error' => true,
              'message' => $this->__(
                'An error occurred while obtaining request token'
              )
            )
    );
  }

  /**
   * Action for oAuth callback URL.
   * Redirects back to TradeMe config page if account is successfully authorised
   *
   * @return null
   */
  public function authoriseAction () {
    $request = $this->getRequest();

    $accountId = $request->getParam('account_id');
    $website = $request->getParam('website');

    $params = array(
      'section' => 'trademe',
      'website' => $website
    );

    if (!$accountId || !$website) {
      Mage::getSingleton('adminhtml/session')
        ->addError($this->__('Account ID or website are not specified'));

      $this->_redirect('adminhtml/system_config/edit', $params);

      return;
    }

    $auth = new MVentory_TradeMe_Model_Account($accountId, $website);

    $result = $auth->authorise($request->getParams());

    if ($result) {
      $accounts = Mage::helper('trademe')->getAccounts($website);
      $accountName = $accounts[$accountId]['name'];

      $message = '"' . $accountName . '" account is successfully authorized';

      Mage::getSingleton('adminhtml/session')->addSuccess($this->__($message));
    }
    else {
      $message = 'An error occurred while obtaining authorized Access Token';
      Mage::getSingleton('adminhtml/session')->addError($this->__($message));
    }

    $this->_redirect('adminhtml/system_config/edit', $params);
  }

  /**
   * Checks if specified account is used by products
   *
   * @return MVentory_TradeMe_AccountController
   */
  public function canremoveAction () {
    $request = $this->getRequest();

    $accountId = $request->getParam('account_id');
    $website = Mage::app()->getWebsite($request->getParam('website'));

    if (!($accountId && $website->getId()))
      return $this->_response(array(
        'error' => true,
        'message' => $this->__('Account ID or website are not specified')
      ));

    $accounts = Mage::helper('trademe')->getAccounts($website);

    if (!($accounts && isset($accounts[$accountId])))
      return $this->_response(array(
        'error' => true,
        'message' => $this->__('Specified account doesn\'t exist')
      ));

    $products = Mage::getModel('catalog/product')
      ->getCollection()
      ->addAttributeToFilter('type_id', 'simple')
      ->addAttributeToFilter('tm_current_listing_id', array('neq' => ''))
      ->addAttributeToFilter('tm_current_account_id', $accountId)
      ->addStoreFilter($website->getDefaultStore());

    return $this->_response(array('hasProducts' => $products->count() > 0));
  }

  /**
   * Removes specified account
   *
   * @return MVentory_TradeMe_AccountController
   */
  public function removeAction () {
    $request = $this->getRequest();

    $accountId = $request->getParam('account_id');
    $website = $request->getParam('website');

    if (!($accountId && $website))
      return $this->_response(array(
        'error' => true,
        'message' => $this->__('Account ID or website are not specified')
      ));

    $result = Mage::helper('trademe')->removeAccount(
      $accountId,
      Mage::app()->getWebsite($website)
    );

    if (!$result)
      return $this->_response(array(
        'error' => true,
        'message' => $this->__('An error occured while removing account')
      ));

    return $this->_response(array('ajaxRedirect' => $this->getUrl(
      'adminhtml/system_config/edit',
      array('section' => 'trademe', 'website' => $website)
    )));
  }

  protected function _response ($data) {
    $this
      ->getResponse()
      ->setBody(Mage::helper('core')->jsonEncode($data));

    return $this;
  }
}
