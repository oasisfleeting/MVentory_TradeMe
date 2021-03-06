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
 * Fieldset for TradeMe account
 *
 * @package MVentory/TradeMe
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */
class MVentory_TradeMe_Block_Account
  extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
  protected function _getHeaderCommentHtml ($element) {
    $comment = $element->getComment()
                 ? '<div class="comment">' . $element->getComment() . '</div>'
                   : '';

    $accountId = $element->getGroup()->getName();

    $authButton = array(
      'id' => 'trademe_button_auth_' . $accountId,
      'label' => $this->__('Authorise'),
      'onclick' => 'javascript:trademe_auth_account(\''
                   . $accountId
                   . '\'); return false;'
    );

    $removeButton = array(
      'id' => 'trademe_button_remove_' . $accountId,
      'label' => $this->__('Remove'),
      'onclick' => 'javascript:trademe_remove_account(\''
                   . $accountId
                   . '\'); return false;'
    );

    $buttonBlock = $this
      ->getLayout()
      ->createBlock('adminhtml/widget_button');

    return '<div style="float:right">'
           .  '<div class="form-buttons trademe-account-buttons">'
           .    $buttonBlock->setData($authButton)->toHtml()
           .    $buttonBlock->setData($removeButton)->toHtml()
           .  '</div>'
           .  '<div style="clear: both"></div>'
           . '</div>'
           . $comment;
  }

}
