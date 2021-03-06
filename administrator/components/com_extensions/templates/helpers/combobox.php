<?php
/**
 * @version     $Id: combobox.php 3219 2011-12-07 17:03:41Z johnbell $
 * @category	Nooku
 * @package     Nooku_Server
 * @subpackage  Extensions
 * @copyright   Copyright (C) 2011 - 2012 Timble CVBA and Contributors. (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * Listbox Template Helper
 *
 * @author      Stian Didriksen <http://nooku.assembla.com/profile/stiandidriksen>
 * @category	Nooku
 * @package     Nooku_Server
 * @subpackage  Extensions    
 */

class ComExtensionsTemplateHelperCombobox extends KTemplateHelperListbox
{
 	/**
     * Generates a list over positions
     *
     * The list is the array over positions coming from the application template merged with the module positions currently in use
     * that may not be defined in the xml
     *
     * @param   array   An optional array with configuration options
     * @return  string  Html
     */
    public function positions($config = array())
    {
        $config = new KConfig($config);
        $config->append(array(
            'position'    => 'left',
            'application' => 'site'
        ));

        //$positions  = $this->getService('com://admin/extensions.model.modules')->application($config->application)->getList()->getColumn('position');
        $positions = $this->getService('com://admin/extensions.model.positions')->getList()->getColumn('position');
        
		$template   = $this->getService('com://admin/extensions.model.templates')
                          ->application($config->application)
                          ->default(1)
                          ->getItem();
                          
        $positions  = array_unique(array_merge($template->positions, $positions));
		sort($positions);
	
        // @TODO combobox behavior should be in the framework
        JHTML::_('behavior.combobox');
        
        $html[] = '<input type="text" id="position" class="combobox" name="position" value="'.$config->position.'" />';
        $html[] = '<ul id="combobox-position" style="display:none;">';
        
        foreach($positions as $position) {
        	$html[] = '<li>'.$position.'</li>';
        }
        $html[] = '</ul>';

        return implode(PHP_EOL, $html);
    }
}