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
 * Block for system config hint
 *
 * @package MVentory/TradeMe
 * @author Anatoly A. Kazantsev <anatoly@mventory.com>
 */

class MVentory_TradeMe_Block_Setting_Hint
  extends Mage_Adminhtml_Block_Abstract
  implements Varien_Data_Form_Element_Renderer_Interface
{
  protected $_template = 'trademe/config/hint.phtml';

  /**
   * Render fieldset html
   *
   * @param Varien_Data_Form_Element_Abstract $element
   * @return string
   */
  public function render(Varien_Data_Form_Element_Abstract $element) {
    return $this->toHtml();
  }
}

