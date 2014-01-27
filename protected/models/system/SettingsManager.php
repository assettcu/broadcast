<?php
/**
 * Settings Manager Class
 * 
 * This class will manage the various settings objects and functions associated with them.
 * 
 * @author      Ryan Carney-Mogan
 * @category    Auditions_Classes
 * @version     1.0.1
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */


class SettingsManager
{

	public $settings;		# Settings array
	public $settingobjs;	# Array of SettingObjs

    /**
     *  Constructor sets up flagged user and its parts, pulls from DB if an ID is found.
     */
	public function __construct()
	{
		$this->settings = array();
		$this->settingobjs = array();
		$this->load_all_settings();
	}

    /**
     * Load All Settings
     * 
     * Loads instance objects for each of the settings.
     * 
     * @return  (object[])
     */
	public function load_all_settings()
	{
	    # Reset this instance's settings array
	    $this->settings = array();
        
        # Create and execute lookup query
		$conn = Yii::app()->db;
		$query = "
			SELECT		settingparam
			FROM		{{settings}}
			WHERE		1=1;
		";
		$result = $conn->createCommand($query)->queryAll();
        
        # Return empty array if result set is empty
        if(!$result or empty($result)) {
            return $this->settings;
        }
        
        # Iterate through result set and create new Settings objects
		foreach($result as $row)
		{
			$setting = new SettingsObj($row["settingparam"]);
			$this->settings[$setting->settingparam] = $setting->settingval;
			$this->settingobjs[] = $setting;
		}
        # Return the array of groups
		return $this->settings;
	}

    /**
     * Get Setting
     * 
     * Returns the setting object based on field name.
     * 
     * @return (object)
     */
	public function get_setting($field)
	{
		if(isset($this->settingobjs) and count($this->settingobjs)>0)
		{
			foreach($this->settingobjs as $obj)
			{
				if($obj->settingparam == $field) return $obj;
			}
		}
		return null;
	}

	/**
     * Get Setting Value
     * 
     * Returns the setting value based on field name.
     * 
     * @return  (string)
     */
	public function get_setting_value($field)
	{
		if(isset($this->settingobjs) and count($this->settingobjs)>0)
		{
			foreach($this->settingobjs as $obj)
			{
				if($obj->settingparam == $field) return $obj->settingval;
			}
		}
		return null;
	}

}

?>