<?php
/**
 * Storage API Class
 * 
 * The purpose of this class is abstract functions to save to a session table.
 * 
 * @author      Ryan Carney-Mogan
 * @category    Core_Classes
 * @version     1.0.3
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */
 
class StorageAPI
{

    protected $session_enabled = false;     # Whether the session has been started
    protected $storage_type = false;        # Type of storage (session, cache, DB)
    protected $itemid = "";                 # Unique ID to store the session

    public $data;                           # Information to be stored (in an array)

    /**
     *  Constructor sets up the storage type and loads any storage if it's available.
     */
    public function __construct($itemid = null)
    {
        # Load settings if unique ID is not NULL
        if (!is_null($itemid)) {
            $this->setID($itemid);
            $this->enableStorageType("session");
            
            # Load data if there are some
            if ($this->hasStoredData()) {
                $this->data = $this->getStoredData();
            }
        }
    }

   /**
    * Set ID
    * 
    * Sets the unique ID associated with this storage.
    * 
    * @param    (string)    $itemid     Unique ID
    */
    public function setID($itemid)
    {
        $this->itemid = $itemid;
    }

    /**
     * Set Storage Type
     * 
     * Define what storage type this API is based on passed in value.
     * 
     * @param   (string)    $storage_type       Type of storage to make this API
     */
    public function setStorageType($storage_type)
    {
        $this->storage_type = $storage_type;
    }

    /**
     * Enable Storage Type
     * 
     * Enables the storage type depending on the storage type defined in this instance.
     * 
     * @param   (string)    $storage_type   Storage type to enable for this instance.
     * @return  (boolean)                   Valid storage type returns true.
     */
    public function enableStorageType($storage_type)
    {
        switch ($storage_type) {
            // case "cache": $this->cache_enabled = true; break;
            case "session":
                if (!isset($_SESSION)) {
                    session_start();
                }
                $this->session_enabled = true;
                $this->setStorageType("session");
                break;
            default: return false;
        }
        return true;
    }

    /**
     * Disable Storage Type
     * 
     * Disables certain storage types from being enabled and used with this
     * particular instance.
     * 
     * @param   (string)    $storage_type       Type of storage to disable.
     * @return  (boolean)                       Return true if proper storage type was disabled.
     */
    public function disableStorageType($storage_type)
    {
        switch ($storage_type) {
            // case "cache": $this->cache_enabled = true; break;
            case "session": $this->session_enabled = false;
                break;
            default: return false;
        }
        return true;
    }

    /**
     * Has Stored Data
     * 
     * Finds whether the semi-persistant data exists given a unique storage key ($itemid).
     * 
     * @param   (string)    $itemid     Unique ID to lookup data.
     * @return  (boolean)
     */
    protected function hasStoredData($itemid = "")
    {
        # If Unique ID is empty then try setting this instance's unique ID.
        if ($itemid == "") {
            $itemid = $this->itemid;
        }
        
        # If Unique ID is still empty then return that there is no data
        if ($itemid == "") {
            return false;
        }
        
        # Figure out what storage type we're using and see if storage exists with data
        switch ($this->storage_type) {
            // case "cache": apc_fetch($itemid,"success"); break;
            case "session": $success = isset($_SESSION[$itemid]);
                break;
            default: $success = false;
        }
        
        # Return successful or not
        return $success;
    }

    /*
   * Get the semi-persistant data exists
   * @Params  ( string )
   * @Returns ( bool )
   */
    /**
     * Get Stored Data
     * 
     * Return the actual data stored by the Unique ID in this instance of storage.
     * 
     * @param   (string)    $itemid     Unique ID to lookup data.
     * @return  (various)               Returns data stored in the storage.
     */
    protected function getStoredData($itemid = "")
    {
        if ($itemid == "") $itemid = $this->itemid;
        switch ($this->storage_type) {
            // case "cache": return apc_fetch($itemid); break;
            case "session": return $_SESSION[$itemid];
                break;
            default: return null;
        }
        return null;
    }

    /**
     * Store
     * 
     * Stores the information in the storage instance.
     * 
     * @param   (string)    $info       Information to store
     */
    public function store($info = "")
    {
        if ($info == "") $info = $this->data;
        switch ($this->storage_type) {
            /*
  		case "cache":
  			if(!$this->cache_enabled) return;
  			apc_store($this->itemid,$info);
  		break;
  		*/
            case "session":
                if (!$this->session_enabled) return;
                $_SESSION[$this->itemid] = $info;
                break;
            default: break;
        }
        return;
    }

    /**
     * Clear
     * 
     * Unset the storage from its persistant data type.
     */
	public function clear()
	{
		if($this->session_enabled) {
            unset($_SESSION[$this->itemid]);
		}
	}

    /**
     * Remove
     * 
     * Removes an item from the data storage of this instance if it exists.
     * 
     * @param   (string)    $item   Index of item to delete.
     */
	public function remove($item)
	{
		if(isset($_SESSION) and isset($_SESSION[$this->itemid]) and isset($_SESSION[$this->itemid][$item])) {
			unset($_SESSION[$this->itemid][$item]);
        }
	}
}