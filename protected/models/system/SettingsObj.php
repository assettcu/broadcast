<?php
/**
 * Settings Class
 * 
 * The purpose of this class is to load/save Settings information and to perform related tasks to
 * settings.
 * 
 * @author      Ryan Carney-Mogan
 * @category    Auditions_Classes
 * @version     1.0.1
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */

class SettingsObj extends FactoryObj
{

    /**
     *  Constructor sets up settings and its parts, pulls from DB if an ID is found.
     */
	public function __construct($settingparam=null)
	{
		parent::__construct("settingparam","settings",$settingparam);
	}

}

?>