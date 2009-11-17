<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class IsotopeRunonce extends Frontend
{

	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->import('Database');
	}


	/**
	 * Run the controller
	 */
	public function run()
	{
		$this->insertDefaultAttributeTypes();
		$this->updateAttributes();
		$this->updateProductCategories();
		
		// Checkout method has been renamed from "login" to "member" to prevent a problem with palette of the login module
		$this->Database->execute("UPDATE tl_module SET iso_checkout_method='member' WHERE iso_checkout_method='login'");
		
		// Remove generateTeaser() callback on product description
		$this->Database->execute("UPDATE tl_product_attributes SET save_callback='' WHERE save_callback='ProductCatalog.generateTeaser'");
	}
	
	
	/**
	 * Checkboxes should be '' not '0'
	 */
	private function updateAttributes()
	{
		$arrFields = $this->Database->listFields('tl_product_attributes');
		foreach( $arrFields as $field )
		{
			$this->Database->execute("UPDATE tl_product_attributes SET " . $field['name'] . "='' WHERE " . $field['name'] . "='0'");
		}
	}
	
	
	private function insertDefaultAttributeTypes()
	{
		$objAttributeTypes = $this->Database->execute("SELECT type FROM tl_product_attribute_types");
		
		if($objAttributeTypes->numRows < 1)
		{
			$this->Database->execute("INSERT INTO `tl_product_attribute_types` (`id`, `pid`, `sorting`, `tstamp`, `type`, `attr_datatype`, `inputType`, `eval`, `name`) VALUES
	(1, 0, 128, 1218221789, 'text', 'varchar', 'text', '', ''),(2, 0, 256, 1218221789, 'integer', 'int', 'text', '', ''),(3, 0, 384, 1218221789, 'decimal', 'decimal', 'text', '', ''),(4, 0, 512, 1218221789, 'longtext', 'text', 'textarea', '', ''),(5, 0, 640, 1218221789, 'datetime', 'datetime', 'text', '', ''),(6, 0, 768, 1218221789, 'select', 'options', 'select', '', ''),(7, 0, 896, 1218221789, 'checkbox', 'options', 'checkbox', '', ''),(8, 0, 1024, 1218221789, 'options', 'options', 'radio', '', ''),(9, 0, 1152, 1218221789, 'file', 'varchar', 'fileTree', '', ''),(10, 0, 1280, 1218221789, 'media', 'varchar', 'imageManager', '', ''),(11, 0, 150, 1218221789, 'shorttext', 'varchar', 'text', '', '')");	
		}
	}
	
	
	private function updateProductCategories()
	{
		if ($this->Database->tableExists('tl_product_to_category'))
		{
			$this->Database->execute("CREATE TABLE IF NOT EXISTS `tl_product_categories` (`id` int(10) unsigned NOT NULL auto_increment,`pid` int(10) unsigned NOT NULL default '0',`tstamp` int(10) unsigned NOT NULL default '0',`page_id` int(10) unsigned NOT NULL default '0',PRIMARY KEY  (`id`),KEY `pid` (`pid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
			
			$this->Database->execute("INSERT INTO tl_product_categories (pid,tstamp,page_id) (SELECT product_id AS pid, tstamp, pid AS page_id FROM tl_product_to_category)");
			
			$this->Database->execute("DROP TABLE tl_product_to_category");
		}
	}
}


/**
 * Instantiate controller
 */
$objIsotope = new IsotopeRunonce();
$objIsotope->run();

