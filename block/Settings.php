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
 * TradeMe settings block
 *
 * @package MVentory/TradeMe
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_TradeMe_Block_Settings extends Mage_Adminhtml_Block_Abstract
{

  /**
   * Render block HTML
   *
   * @return string
   */
  protected function _toHtml () {
    $trademe = array(
      'url' => array(
        'authenticate' => $this->_getUrl('trademe/account/authenticate'),
        'canremove' => $this->_getUrl('trademe/account/canremove'),
        'remove' => $this->_getUrl('trademe/account/remove')
      )
    );

    return Mage::helper('core/js')->getScript(
      'var trademe = ' . Mage::helper('core')->jsonEncode($trademe)
    );
  }

  protected function _getUrl ($route) {
    return $this->getUrl(
      $route,
      array('website' => $this->getRequest()->getParam('website', ''))
    );
  }
}
