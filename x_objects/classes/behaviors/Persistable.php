<?php 
/*
 * @Project:	Platform
 * @Module:		classes/Persistable
 * 
 * @Created:	Oct 22, 2010
 * @Author(s):	David Greenberg
 * @
 * @Description:	interface to specify that an object may be retrieved from a data
 * source, such as a table, view or custom query
 */
interface Persistable {

	//! save mode dirty means only save what has actually changed
	const SAVE_MODE_DIRTY = 1;

	public function setDataSource ( $Source );	// set the data source for this object
	public function getDataSource ( );  // get the data source for this object
	public function getDefaultDataSource(); // gets the default data source for instance
	
	public function load(); 	// load the object from the data source
	public function commit();	// commit changes to the object to the data source
	public function save( $mode = self::SAVE_MODE_DIRTY );	// save changes, by default only what has changed
	
	public function isDirty( $key );	// is a given field "dirty"?  has it changed since the last save
	public function setDirty( $key );	// sets a given key as dirty
	public function cleanAll();			// declare all fields as clean, resetting the flags

}
?>