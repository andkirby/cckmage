<?php
/**
 * Class Kand_Cck_Helper_Data
 */
class Kand_Cck_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Collect text strings from HTML
     *
     * @param string $html
     * @return array
     * @throws Exception When parameter is not string
     */
    public function collectTexts($html)
    {
        if (empty($html)) {
            return array();
        }
        if (!is_string($html)) {
            throw new Exception('HTML parameter must a string.');
        }
        return array();
    }
}
