<?php
/*
 * Project: 	Platform
 * Module:		classes/ObjectList
 * 
 * Purpose:		instantiate a list of Objects with Pagination support
 *
 * Created By:	David Owen greenberg <davidg@tribalcomputing.net>
 * on:			30 Oct 2010
 */
class ObjectList implements DisplayableObject{
	
	private $Debug = false;
	
	// flag for enabling sorting and filtering
	private $SortAndFilter = false;
	protected $SortFilterControl = null;
	
	// the objects
	protected $Objects = array();
	
	// the paginator
	public $Paginator = null;
	
	// the optional header
	private $Header = null;
	
	function __construct( ) {

		if ( $this->Debug )
			Debugger::echoMessage('ObjectList::__construct(): creating a paginator');
			
		$this->Paginator = new Paginator();		
	}
	
	/// enable sorting and filtering for the object list
	public function enableSortAndFilter() {
		
		$this->SortAndFilter = true;
	}
	
	public function numObjects() { return count( $this->Objects );}
		
	protected function getHeader() { return $this->Header; }
	public function setHeader( $Header ) {
		
		if ( ! is_object( $Header)  || get_parent_class( $Header) != 'HTMLElement')
			throw new IllegalArgumentException( 'Exception from ObjectList::setHeader(): argument must be an object of (sub)type HTMLElement, but is actually ' . get_parent_class( $Header));

		$this->Header = $Header;	
		
		// set the number of pages for paginator
		$NumPages = ceil( $this->numObjects() / Paginator::DEF_ITEMS_PER_PAGE);
		
		$this->Paginator->setNumberOfPages ( $NumPages ) ;	
	}	
	
	public function getAsHTML() {
		
		
		// we need the keys to handle pagination and for sort/filter
		$Keys = array_keys( $this->Objects );
		
		$HTML = '';
		
		if ( $this->Header )
			$HTML .= $this->Header->getAsHTML();
		
		// if sorting and filtering, show a SortAndFilterControl
		if ( $this->SortAndFilter) {
			
			if ( $this->Debug)
				Debugger::echoMessage( get_class() . '::getAsHTML: displaying a sort and filter control for list');

			$this->SortFilterControl = new SortAndFilterControl( $this->Objects[$Keys[0]]->getDataSource());
			
			$HTML .= $this->SortFilterControl->getAsHTML();
			
		}
			
		
		// we need the keys to handle pagination
		$Keys = array_keys( $this->Objects );
		
		if ( $this->Debug ) {
			Debugger::echoMessage('ObjectList::getAsHTML(): Found ' . count($Keys) . ' keys to get HTML');
			Debugger::echoMessage('ObjectList::getAsHTML(): CurrentPage=' . $this->Paginator->getCurrentPage());
		
		}
			
		// set the loop based on paginator
		$First = $this->Paginator->getCurrentPage() * Paginator::DEF_ITEMS_PER_PAGE - Paginator::DEF_ITEMS_PER_PAGE;
		$Last = $this->Paginator->getCurrentPage() * Paginator::DEF_ITEMS_PER_PAGE - 1;
		
		if ( $this->Debug)
			Debugger::echoMessage('ObjectList::getAsHTML(): first=' . $First . ' last=' . $Last);
		for ( $Item = $First; $Item <= $Last && isset( $Keys[$Item] ) && isset( $this->Objects[$Keys[$Item]]); $Item++)
			if ( isset( $this->Objects[$Keys[$Item]]))
				$HTML .= $this->Objects[$Keys[$Item]]->getAsHTML();
		
		return $HTML;
	}
	
	public function getAsString() { return $this->getAsHTML(); }
	
	public function display() { echo $this->getAsHTML(); }
}
 
?>
