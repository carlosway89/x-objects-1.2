<?php
/*! A class to create, design and display a tabbed pane view as a DIV
 * 
 * Project:			Platform
 * Module:			classes/TabbedPane
 * 
 * Purpose:			Create and display a tabbed pane of content for navigation
 * 
 * Created by:		David Owen Greenberg <david.o.greenberg@gmail.com>
 * On:				27 Oct 2010
 */
class TabbedPane {
	
	// holds the tabs themselves
	private $Tabs = array();
	
	// show an indicator when ajax is running
	private $enableAjaxIndicator = false;
	
	/// default content tab
	private $DefaultContent = null;

	/*! create a new TabbedPane() object.  This version does not 
	 * allow display upon construct, future versions might.
	 */	
	function __construct(  ) {
		
	}
	
	/*! display the tabbed pane, by outputting well-formatted HTML with
	 * classes for style.
	 */
	public function display() {
		
		// create a new unordered list and display it
		new UnorderedListElement( 'id=tabmenu' , $this->Tabs, true);
		
		if ( $this->enableAjaxIndicator )
			new AjaxLoaderIndicator('images/ajax-loader.gif');
		
		// show the content
		new DivElement( 'id=content,class=TabbedPaneContent' , $this->DefaultTab , true);	
		
	}
	
	/*! add a new tab to the pane, in which $Content is a ListItemElement
	 * 
	 */
	public function addTab ( 
		$Content, /// object of type ListItemElement which is the content for the tab
		$isDefault = false /// indicates whether or not this tab is the default view
		) {
		
		if ( ! is_object( $Content ) && get_class( $Content) == 'ListItemElement')
			throw new IllegalArgumentException ( 'Exception thrown from TabbedPane::addTab(): Argument must be an object of type ListItemElement');

		// repackage inner html around an anchor
		$AnchorClass = $isDefault ? 'class=active,' : '';
		$Content->setInnerHTML( new AnchorElement( $AnchorClass . 'id=' . $Content->get('content')  , $Content->getInnerHTML() , false ));
		$OnClickCode = 'TabbedPane.makeActive(\''  . $Content->get('content') . '\' )';
		$Content->JSHandler->setOnClick( $OnClickCode );
		if ( $isDefault )
			echo '<input type=hidden name=defaultab id=defaulttab value=' . $Content->get('content') . '></input>';
		
		$this->Tabs[$Content->get('content')] = $Content;		
		
	}
	
	/*! enable display of an ajax waiting indicator
	 */
	 public function enableAjaxIndicator() { $this->enableAjaxIndicator = true; } 
	
}
?>
