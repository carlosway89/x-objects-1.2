<?php
//! instantiate a management console for back-end management of a web application or website.
class management_console extends AttributedObject
{


	/*
	 * these values are used for the featureset bitmask, which allows you to enable or disable all kinds
	 * of features at runtime.  
	 */
	const F_HIDE_ID_COLUMN = 1;
	const F_HIDE_MASTER_DETAIL = 2;
	const F_HIDE_CASCADE_DELETE = 4;
	
	//! flag indicating whether the user is currently logged in
	private $isLoggedIn = false;
	
	
	// URL of the service being administered
	private $ServiceURL = null;
	
	// data services descriptor, saved as an array
	private $DataServices = array();
	
	// array of data records being managed (e.g. db objects from database)
	private $DataRecords = array();
	
	// header exceptions when displaying records of a specific service
	private $HeaderExceptions = array();
	
	// type exceptions are used to handle different types with excepted classes
	private $TypeExceptions = array();
		
	// this is used to display meaningful messages from the previous action
	private  $Message = '';
	
	// an object to hold state
	private $Object = null;
	
	// for searches
	private $SearchResult = null;
	
	// this record is used to save new records of any type
	private $NewRecord = null;
	
	// used to store feature set flags as on or off
	private $FeatureSet = 0;
	
	// columns used to print all records of a service type
	private $PrintAllColumns = null;
	
	/*
	 * Fields for which inline style has been enabled. this allows the 
	 * administrator to verify style settings applied to the field before
	 * they are live on the site, as they are being managed
	 */
	private $InlineStyledFields = array();
	
	//! static custom message for the home page
	private $HomePageMessage = '';
	
	//! the application configuration
	private $appConfig = null;
	
	//! show/hide application extensions
	private $enableExtensions = false;
	
	/*
	 * When constructing the AdminInterface, the user shall pass:
	 * $ServiceURL: what's the URL we are servicing? (e.g. besame.org)
	 */
	function __construct(  ) {
	
		// set debugging
		if ( Debugger::enabled() )
			$this->debug = true;
	
		// load app configuration
		$this->config = new RealXML( "management" );
		
		// set the services from configuration
		$this->DataServices = $this->config->xml()->services->service;
		
		// set home page message
		$this->HomePageMessage = defined( 'LANG_MANAGE_HOME_PAGE_MESSAGE' ) ? LANG_MANAGE_HOME_PAGE_MESSAGE : 'welcome to the management console';
		
		// set the user's logged in status
		$this->isLoggedIn = LoginService::instance()->is_logged_in();
			
	}
	
	//! magic set
	public function __set( $what, $val ) {
	
		$this->set( $what, $val );
		
	}
	
	//! magic get
	public function __get( $what ) {
	
		switch( $what ) {
		
			default:
			
				return $this->get( $what );
				
			break;
		
		}
	
	}

	
	/*
	 * setAsStyleEnabled( $Service, $Field): set the indicated service field as
	 * style-enabled, so style changes are displayed immediately in the management
	 * interface
	 * @Service: the service to enable styled fields
	 * @Fields: (array): list of fields to style
	 */
	 public function setAsStyleEnabled( $Service, $Fields ) {
	 	
	 	if ( ! is_array( $Fields ))
	 		throw new IllegalArgumentException('AdminInterface2::setAsStyleEnabled(): Argument 2 must be an array.');
	 	
	 	$this->InlineStyledFields[ $Service ] = $Fields;
	 	
	 }
	
	/*
	 * cascadeDelete( $Id ): cascade delete an Id and all of its children
	 */
	private function cascadeDelete( $Id ) {
	
		// we need to do this in a try block since a data layer action might bonk out
		try {
			// load the primary object from the database
			$Object = new $_REQUEST['Service']( null,$Id, true);
			$Object->load();
			
			// get relationships, which are secondary objects 
			$Relationships = $Object->getRelationships();
			
			// for each existing relationship...
			foreach ( $Relationships as $Relationship => $Value)
			
				// if the relationship contains the same string as a particular service
				foreach ( $this->DataServices as $ServiceName => $ServiceDescr) 
				
					// if the name of the relationship matches a service description
					if ( strpos( $Relationship , $ServiceDescr) 
						// and the value is nonzero
						&& $Value) {
						
							//echo 'cascade deleting ' . $ServiceName . ' Id=' . $Value . ' <br>' . "\r\n";
							// load the child record from the database
							$Child = new $ServiceName( $Value, true);
							$Child->load();
							
							// delete it
							$Child->delete();
						}
			// finally delete the parent
			$Object->delete();
			
			return true;
			
		} catch ( Exception $e ) { throw $e; }
	
		return false;
			
	}
	
	
	/*
	 * process(): process any incoming data so that when the interface is displayed, it's ready
	 */
	public function process() {
	
		// if login data has been posted, process it
		if ( LoginService::dataPosted() ) {
		
			// attempt to login
			$service = new LoginService();
			
			$this->isLoggedIn = $service->login();
			
			return;
		}
	
		// processing hinges on the current action, as passed by the user
		switch ( isset($_REQUEST['Action']) ? $_REQUEST['Action'] : 'none') {
			
			// application configuration
			case 'appconfig':
			
			// if updated, save changes
			if ( isset( $_REQUEST['updated'])) {
			
				// load configuration
				$config = new AppConfiguration();
				
				// update from post
				$config->setAllFromPOST();
				
				// save changes
				$config->save();
			
				// set a message
				$this->Message = 'The app configuration was updated';	
			}
			
			break;
			
			// approve a new user account
			case 'approveuser':
			
			// if already approved, set as approved
			if ( isset( $_REQUEST['approved'])) {
				$user = new $_REQUEST['Service']( $_REQUEST['Id'], true);
				
				// set user type
				$user->set('Type', $_REQUEST['usertype']);
				
				// activate
				$user->activate();
				
				// set success message
				$this->Message = $_REQUEST['Service'] . ' ' . $_REQUEST['Id'] . ' has been activated.';
			}
			break;
			// unlock a user account
			case 'unlockuser':
			// load the record
			$record = new $_REQUEST['Service']( $_REQUEST['Id'], true);
			// lock it
			$record->unlock();
			break;
			
			// lock a user account
			case 'lockuser':
			// load the record
			$record = new $_REQUEST['Service']( $_REQUEST['Id'], true);
			// lock it
			$record->lock();
			break;
			// activate a record
			case 'activate':
			// load the record
			$record = new $_REQUEST['Service']( $_REQUEST['Id'], true);
			$record->activate();
			break;
		/*
		 * Delete the record, and all "children" meaning all records that are referenced by it
		 */
		case 'cascadedelete':
			// only delete if the user has already confirmed!
			if ( $_REQUEST['ConfirmDelete'] == 'yes')
				if ( $this->cascadeDelete( $_REQUEST['Id']) )
					$this->Message = 'Ok. '  . $_REQUEST['Service'] . ' Id=' . $_REQUEST['Id'] . ' has been <strong>cascade deleted</a>';
				else $this->Message = 'We were unable to cascade delete ' . $_REQUEST['Service'] . ' Id=' . $_REQUEST['Id'];
			break;
		/*
		 * process any search results, which are used to search for strings in all records
		 */
		case 'search':
		
			
			// create a new Search, which we'll use to find stuph
			Search::setResultType( $this->appConfig->getAppPrefix() . 'ResultSet' );
			$Search = new Search( array_keys( $this->appConfig->getManagedServices() ));
			
			// iterate over all available services
			foreach ($this->DataServices as $ServiceName => $ServiceDescr) {
				
				// find stuphh in that service table
				$this->SearchResult[$ServiceName] = $Search->search( $_REQUEST['SearchTerm'], $ServiceName);
			}
			
			break;
			
		case 'printall':
		
			// nothing to do here, all done as display method
		
		break;
			case 'view':
			// same as edit
			case 'edit':
				try {
					
					/*
					 * By default, we load data from the database table whose name matches the name of
					 * the service.  For example, if we are viewing a service called 'XYBluebeams' which is used 
					 * to track a type of metal akin to the fictitious "Reardon Steel" in the book Atlas Shrugged
					 * by Ayn Rand.  In this case, we just load up our view objects from the database table
					 * called XYBluebeams.  This keeps things real simple.
					 * 
					 */
					

					// the source records, before being modified
					$SourceRecords = array();
					
					// initialise a set to hold our results
					$ResultRecords = array();
				
					$Offset = (isset($_REQUEST['Page']) ? $_REQUEST['Page'] * self::MAX_RECORDS_PER_PAGE - self::MAX_RECORDS_PER_PAGE : 0);
					$Sortby = $this->appConfig->getSortKeyFor( $_REQUEST['Service'] );
					$Direction = $Sortby ? 'DESC' : null;

					
					/*
					 * Obtain up to 100 source records from the source table, using the specified
					 * offset, sorted by the specified column and in the given direction
					 */
					//$this->DataRecords = ObjectFactory::createAll($_REQUEST['Service']);
					$this->DataRecords = ObjectFactory::create( 
						$_REQUEST['Service'] ,						// create records of this type
						null,										// don't skip any 
						null,										// get all of them
						$Sortby,									// optional sort column
						$Direction,									// direction to sort
						null);										// no conditions
						
					if ( $this->debug)
						debugger::echoMessage( get_class(). '::' . __FUNCTION__ . ': retrieved ' . count( $this->DataRecords) . ' records to manage.');
					
					// set number of pages in paginator
					$this->paginator->setNumberOfPages( ceil ( count( $this->DataRecords) / $this->paginator->recordsPerPage()));

				} catch ( Exception $e ) { throw $e; }
			break;
			case 'delete':
				// only do all this if the user already confirmed
				if ( isset( $_REQUEST['ConfirmDelete']) && $_REQUEST['ConfirmDelete'] == 'yes') {
					// first retrieve an Object representing the record to delete
					try {
						// load the object
						$Object = new $_REQUEST['Service']($_REQUEST['Id'], true);
						
						// security check that we are not the current user
						$LS = new LoginService();
						if ( $Object->get('txtEmail') == $LS->getCurrentUser()) {
							$this->Message = 'Nice try... you can\'t delete yourself!';
						} elseif ( $Object->get('txtEmail') == SYSTEM_USER ) {
							$this->Message = 'Nice try.. you cannot delete the system account';
						} else { 							
							$Object->delete();
							$this->Message = 'Successfully deleted ' . $_REQUEST['Service'] . ' record with Id=' . $_REQUEST['Id'];
						}
						
					} catch (Exception $e) { throw $e; }					
				}
				break;
			case 'editdo':
				// first instantiate a new record to commit edit changes
				$this->Object = new $_REQUEST['Service'](null,$_REQUEST['Id'], true);
				
				/*
				 * handle file uploads.  First check if a file upload is present
				 */
				
				
				// now load in all the values from POST
				$this->Object->setAllFromPOST();
				
				
				
				// now try to commit the changes
				try { $this->Object->commit('update'); } catch (Exception $e) { throw $e; }
				$this->Message = 'Yep!  We\'ve saved your changes to the database';
				break;
			case 'createdo':
				if ( ! $this->createRecord()) $this->Message = 'Unable to create the record';
				else $this->Message = 'The record was created';
				break;
		}
	
	}
	
	//! display management console based on current state
	public function display( $HTMLClass = 'AdminInterface2' // user may pass an HTML block/component class for use with CSS;
	) {
	
		// if not logged in, display a login form
		if ( ! $this->isLoggedIn ) {
		
			LoginService::displayLoginForm( 'index.php' );
		
		} elseif ( LoginService::getMyUserType() != 'administrator' )
			new DivElement('class=error', 'Sorry, you must be an administrator to use the management console',true);
		else {
		
			// the wrapper div
			echo '<div id="AdminWrapper">' . "\r\n";
		
			// display left content.  Exception: if we are printing a report
			$PrintAll = isset($_REQUEST['Action']) ? ( $_REQUEST['Action'] == 'printall' || $_REQUEST['Action'] == 'printselected' ? true : false) : false;
			if ( ! $PrintAll) {
				echo '<div id="AdminLeftContent">' . "\r\n";
		
				// display a logo
				echo '<a href="index.php"><img class="adminlogo" border="0" width="150" src="' . LOGO_SRC . '" alt="logo"></img></a>' . "\r\n";
		
				/*
				* In this block of code, we are walking through each data service that was requested when the admin interface
				* was invoked, and then we show a link to manage that specific service
				*/
				echo '<table>' . "\r\n";
				foreach ($this->DataServices as $ServiceName => $ServiceDescr) {
					echo '<tr><td><a class="ManageServiceLink" title="click to manage records of this type" href="index.php?Service=' . $ServiceName . '&Action=view"><strong>Manage ' . $ServiceDescr . '</strong></a></td>' . "\r\n";
	
				}
				echo '</table><br><br>' . "\r\n";

				// provide links to logout or return to application
				new AnchorElement('class=link,href=index.php?Action%logout','logout',true);
				echo '<br>';
				
				// display a quickcreate ActionSelector
				if ( ENABLE_QUICK_CREATE ) {
					$QuickCreate = new ActionSelector('QuickCreate' , 'Create a new...', $this->DataServices  , 'createQuickly( document.getElementById(\'QuickCreate\').value );');
					$QuickCreate->set( 'id' , 'QuickCreate');
					$QuickCreate->display();
				}
				
				// if extensions are enabled show them
				if ( $this->enableExtensions ) {
					// link to manage application configuration
					new AnchorElement('href=index.php?Service%null&Action%appconfig,title=Application Configuration','Application Configuration', true);
					/*
					* This section is to display links to custom reporting
					*/
					new DivElement ('id=CustomReporting' , '<a target="CustomReporting" href="reporting.php" title="Custom Reporting">Custom Reports</a>', true);

					/*
					* This link is for using the Data Loader, for bulk updates
					*/
					new DivElement ('id=DataLoader' , '<a href="dataloader.php" title="Data Loader"><img src="../images/new_icon.gif" border="0"></img>Data Loader</a>' , true);
				}
				
				// display a search bar
				echo '<br><br><form class="Search" name="Search" id="Search" method="POST" action="index.php?Action=search">' . "\r\n";
				echo '<input type="text" name="SearchTerm" id="SearchTerm" class="Search"></input>' . "\r\n";
				//echo '<button type="submit" name="SubmitSearch" id="SubmitSearch" class="Search">Find it!</button>' . "\r\n";
				new InputElement('value=enter search term,onclick=this.value=\'\';,type=image,src=' . IMGURL . '/platform/images/search.png' ,null,true);
				echo '</form>' . "\r\n";
		
		
				// end the left panel
				echo '</div><!-- end AdminLeftContent -->' . "\r\n";
		
			// end if
			}
		
			// display right content, which is the main content
			$CSSModifier = $PrintAll ? 'PrintAllVersion' : '';
			echo '<div id="AdminRightContent' . $CSSModifier .  '">' . "\r\n";
		
		
			/*
			* This block of code is for displaying the results from searches, as a sequential list of hits with hyperlinks
			* to view actual records
			*/
			if ( isset($_REQUEST['Action']))
				if ( $_REQUEST['Action'] == 'search' ) 
					$this->displaySearchResults();
				elseif ( ! isset($_REQUEST['Service'])) $this->displayWelcomeBanner();
			
			/*
			* This block of code displays records or list of records, if the user is administerring
			* a specific service
			*/
			if ( isset($_REQUEST['Service'])) {

				$token = isset( $this->DataServices[$_REQUEST['Service']]) ? $this->DataServices[$_REQUEST['Service']] : 'Application';
				echo '<h2 class="AdminInterface">' . $token . ' Administration</h2>' . "\r\n";
			
			
				// view is depending on current action
				switch ( $_REQUEST['Action']) {

					
					
					// application configuration
					case 'appconfig':
				
					if ( ! isset( $_REQUEST['updated'])) {
					// load the current configuration
					$config = new AppConfiguration();
				
					// display it as a form for changes
					$config->displayAsForm();
					
					}
				
					break;
					// approve a new user
					case 'approveuser':
					if ( ! isset( $_REQUEST['approved'])) {
					
						// load the new user
						$user = new $_REQUEST['Service']( $_REQUEST['Id'], true);
					
						// create a notice of who is being approved
						$notice = new SpanElement('class=approveuser','Approve new user id=' . $_REQUEST['Id']);
						// create a label for type
						$typeLabel = new LabelElement( 'for=usertype','Select the type of user:');
					
						// create a select element for the user type
						$type = new SelectElement('name=usertype,id=usertype','Select user type', array( 'user', 'administrator'));
					
						// create a button to submit the form
						$button = new ButtonElement( 'class=submitapprove,type=submit','Approve User');
					
						// create a new form element and display it
						$form = new FormElement('action=index.php?Service%' . $_REQUEST['Service'] . '&approved&Id%' . $_REQUEST['Id'] . '&Action%approveuser,method=POST,class=approveuser', array( $notice,'<br>',$typeLabel, $type, '<br>',$button), true);
					}
					break;				
					// unlock user account
					case 'unlockuser':
					new DivElement('class=success', $_REQUEST['Service'] . ' ' . $_REQUEST['Id'] . ' has been unlocked.', true);
					break;
					// lock user account
					case 'lockuser':
					new DivElement('class=success', $_REQUEST['Service'] . ' ' . $_REQUEST['Id'] . ' has been locked.', true);
					break;
				
					case 'activate':
					new DivElement('class=success', $_REQUEST['Service'] . ' ' . $_REQUEST['Id'] . ' has been activated', true);
					break;
				/*
				* generic case is after having logged in... display the welcome banner
				*/
				case 'login':
				$this->displayWelcomeBanner();
				break;
			
				case 'printall':
				case 'printselected':

				// use the local function to print items
				$this->printServices( $_REQUEST['Action']);
						
				break;
			
				case 'delete':
				case 'cascadedelete':
					if ( ! isset($_REQUEST['ConfirmDelete']) ) {
					
						echo '<font class="Warning">Sure you really want to <strong>' . ($_REQUEST['Action'] == 'cascadedelete' ? 'cascade' : '') .  ' delete</strong> ' . $_REQUEST['Service'] . ' record Id=' . $_REQUEST['Id'] . '?</font>' . "\r\n";
						echo '<a href="index.php?Action=' . ( $_REQUEST['Action'] == 'cascadedelete' ? 'cascadedelete' : 'delete' ) . '&Id=' . $_REQUEST['Id'] . '&Service=' . $_REQUEST['Service'] . '&ConfirmDelete=yes"> [yes] </a>' . "\r\n";
						echo '<a href="index.php?Action=view&Service=' . $_REQUEST['Service'] . '"> [no] </a>' . "\r\n";
					}
				break;
			
				/*
				* VIEW: Viewing one or more Service records.  This includes list views as well 
				* as views of specific records.  Note that the master-detail case is handled
				* elsewhere
				*/
				case 'view':
			

					/*
					* In this block, if the user is not viewing a specific Id, then we should show 
					* them a sortable paginated list of the records
					*/
					if (!isset($_REQUEST['Id'])) {
					
						// if there are no records to show, just say so...
						if ( ! count( $this->DataRecords )) {
							new SpanElement( 'id=norecords', 'There are no records to display', true);
						} else {
					
						// display records as a list view
						$view = new AdminListView( $this->DataRecords, $_REQUEST['Service']);
						$view->display();
					
						}
						
					} else { 
						/*
						* in this block of code, we are viewing a specific record, so we:
						* 1) Load the DBObject from the database, based on the service type
						* 2) Use it's native display method to show it on the screen
						* 3) show a menu of commands related to the object view
						*/
						$Object = new $_REQUEST['Service']( $_REQUEST['Id'], true );
						// load it's data
						$foundSomething = false;
						try { 
							$foundSomething = $Object->load(); } catch (Exception $e) { 
						
							throw $e;
						}
					 
						// display the name of the object 
						echo '<h2>' . $Object->get('Name') . '</h2>' . "\r\n";
					 
						// display the control menu
						$this->displayItemMenu( $_REQUEST['Id'], $_REQUEST['Service'] , $Object->get('Type'));
					
						echo '<br><br>' ;
					
						// use the object's display method to show it's fields and values as a table
						if ( $foundSomething )
							$Object->viewAsHTML( $hideUnsetValues = false , $showFieldTypes = SHOW_FIELD_TYPES );
						else echo 'We were unable to load a view for this record.  Sometimes a master-detail view is not available for specific records...' . "\r\n";
					}
				break;
				case 'edit':
					// edit a specific record
					echo '<form accept-charset="UTF-8" enctype="multipart/form-data" id="EditRecord' . $_REQUEST['Id'] . '" name="EditRecordFrm" action="index.php?Service=' . $_REQUEST['Service'] . '&Id=' . $_REQUEST['Id'] . '&Action=editdo" method="POST">' . "\r\n";
					$this->DataRecords[$_REQUEST['Id']]->EditAsHTML();
					echo '<input type="submit" name="submiteditrecord" value="Make Changes"></input>' . "\r\n";
					echo '</form' . "\r\n";
					break;
				case 'editdo':
					$this->Object->display();
					// free up object
					$this->Object = null;
					break;
				case 'viewmd':
					try {
						$this->displayMasterDetail( $_REQUEST['Id'], $_REQUEST['Service'] , $_REQUEST['Type'] );
					} catch (Exception $e) { throw $e; }
					break;
				case 'create':
					// create a new data record to be saved
					$this->NewRecord = new $_REQUEST['Service']();
				
					// a simple header to describe the use of the form
					echo '<span class="create_record_header">Complete the form below and click the button to create and save a new ' . $_REQUEST['Service'] . '</span>' . "\r\n";
				
					echo '<form id="CreateRecord" name="CreateRecordFrm" action="index.php?Service=' . $_REQUEST['Service'] . '&Action=createdo" method="POST">' . "\r\n";
					// allow the user to edit the record
					$this->NewRecord->editAsHTML();
					echo '<input type="submit" name="submiteditrecord" value="Create ' . $_REQUEST['Service'] . '"></input>' . "\r\n";
					echo '</form>' . "\r\n";
					break;
				case 'search':
				break;
				default:
				$this->displayWelcomeBanner();
				break;
				}	
		
			/*
			* Otherwise... if there's no action to be taken (we are on the home page) then display the
			* home page message, which can be customized by the user
			*/
			} else {
				if ( ! isset($_REQUEST['Action'] ))
					echo $this->HomePageMessage;
			}
		
			// show buttons for the service group
			$this->displayServiceGroupButtons();
		
			echo '</div><!-- end AdminRightContent -->' . "\r\n";
			
			// display a footer
			new DivElement('class=AdminFooter','Powered by RealObjects.  (c) 2011 David Owen Greenberg, all rights reserved.',true);
			echo '</div><!-- end AdminWrapper -->' . "\r\n";
		}
	}

	private function displayServiceGroupButtons() {
		
		// create a new record
		if ( ! isset( $_REQUEST['Id']) && ( isset( $_REQUEST['Action']) && ! $_REQUEST['Action'] == 'create') )
			new ButtonElement('class=newrecord,onclick=window.location%\'index.php?Service%' . $_REQUEST['Service'] . '&Action%create\'','New ' . $_REQUEST['Service'], true);
		// close window
		if ( isset( $_REQUEST['Id']))
			new ButtonElement('class=goBack,onclick=window.location%\'index.php?Service%' . $_REQUEST['Service'] .'&Action%view\'' , 'Back to ' . $_REQUEST['Service'] . ' management', true);
	
	}
	
	private function displayMasterDetail( $RecordId, $ServiceType, $Type ) {

		// hold the master record
		$Master = null;
		
		// flag to determine if we got a record
		$foundSomething = false;
		
		// class to use
		$Class = null;
		
		echo 'type=' . $Type;
		// set the class to use, based on exceptions
		/*
		 * If an Object TYPE field exception is set for this service name,
		 * and if the exception is set for this specific type for the record
		 * then set the class to be the exception.  Otherwise
		 * just append to the service base name "view" to create the classname
		 */
		if ( isset( $this->TypeExceptions[$_REQUEST['Service']])) {
			if ( $this->TypeExceptions[$_REQUEST['Service']][$Type]) {
				$Class = $this->TypeExceptions[$_REQUEST['Service']][$Type];
				
			} else { $Class = $_REQUEST['Service'] . 'View'; }
		} else { $Class = $_REQUEST['Service'] . 'View'; }
		
		// now handle other exceptions
		
		try {
			$Master = new $Class ( $_REQUEST['Id'], true);
			$foundSomething = $Master->load();
		} catch (Exception $e) { throw $e; }
	
		echo '<div id="MasterObject" class="MasterDetail">';
		echo '<h3>Master Record</h3>' . "\r\n"; 
		$this->displayItemMenu($_REQUEST['Id'], $_REQUEST['Service'] , $_REQUEST['Type']);
		echo '<br><br>' ;
		if ( $foundSomething )
			$Master->viewAsHTML( $hideUnsetValues = true , $showFieldTypes = SHOW_FIELD_TYPES );
		else echo 'Oops... a master detail view is unavailable for this specific record.  Sometimes specific records are placeholders in the database and don\'t have a master-detail view.' . "\r\n";
		echo '</div>';
		echo '<br><br>';
	
	}
	
	/*
	 * displayItemMenu(): displays the item menu for a given list item
	 * $Id:					the Id for the item
	 * $Service:			the current service
	 */
	private function displayItemMenu( $Id , $Service , $Type) {
	
		echo '<button type="button" class="AdminInterface" id="ItemMenuEdit" onclick="window.location=\'index.php?Service=' . $Service . '&Action=edit&Id=' . $Id . '\'">edit</button>' . "\r\n";
		echo '<button type="button" class="AdminInterface" id="ItemMenuDelete" onclick="window.location=\'index.php?Service=' . $Service . '&Action=delete&Id=' . $Id . '\'">delete</button>' . "\r\n";
		$PrintLocation = 'print.php';
		echo '<button type="button" class="AdminInterface" id="ItemMenuPrint" onclick="window.open(\''  . $PrintLocation . '  \',\'toolbar=0,location=0,width=800,height=600\' )">Print</button>';
		
		if ( ! $this->FeatureSet & self::F_HIDE_CASCADE_DELETE)
			echo '<a href=index.php?Service=' . $Service . '&Action=cascadedelete&Id=' . $Id . '>[cascade delete]</a> ';
		if ( $this->FeatureSet & self::F_HIDE_MASTER_DETAIL != self::F_HIDE_MASTER_DETAIL )
			echo '<a href=index.php?Service=' . $Service . '&Action=viewmd&Id=' . $Id .  '&Type=' . $Type . '>[view Master/Detail]</a>' . "\r\n";
		else echo "\r\n";	
	}

	private function displaySearchResults() {
			foreach ($this->DataServices as $ServiceName => $ServiceDescr){
				echo '<br>Search results for <strong>' . $ServiceDescr . '</strong><BR><br>' . "\r\n";
				foreach ($this->SearchResult[$ServiceName] as $Hit) {
					echo $Hit[$this->getNameCol( $ServiceName)] . 
					' <a href="index.php?Service=' . $ServiceName . '&Action=view&Id=' . $Hit['Id'] . '">[view]</a>' .
					' | <a href="index.php?Service=' . $ServiceName . '&Action=edit&Id=' . $Hit['Id'] . '">[edit]</a>' .
					'<br>' . "\r\n";
				}
			}
	}
	
	/*
	 * createRecord(): code to create a new record in the database
	 */
	private function createRecord() {

		// instantiate the record
		$this->NewRecord = new $_REQUEST['Service']();
		
		// first, set the record's values from post
		$this->NewRecord->setAllFromPost();
		
		// next, try to commit the record
		try { $this->NewRecord->commit('insert'); } catch ( Exception $e ) { throw $e; }
		
		return true;
		
	}
	

	
	/*
	 * display the menu for actions related to viewing all records from a specific service group
	 */
	public function displayServiceGroupMenu() {

		$InnerHTML = '<button type="button" name="PrintServiceGroup" onclick="window.open(\'index.php?Service=' . $_REQUEST['Service'] . '&Action=printall\',\'printall\');">Print All</button>' . "\r\n";
		$InnerHTML .= '<button type="submit" onclick="if (!validateCheckboxes(\'selectItems\' )) { alert(\'Please check at least one item\'); return false; } else { document.printSelectedForm.submit(); }" name="PrintServiceGroupSelected">Print Selected</button>' . "\r\n";

		new DivElement( 'id=ServiceGroupMenu' , $InnerHTML , true);

		return true;
		
	}
	
	/*
	 * displayPaginationHeader(): an internal function to display the pagination header
	 * when viewing lists of records
	 */
	 private function displayPaginationHeader() {
	 	
		$this->paginator->display();	 	
	 	
	 }
	 
	 
	 /*
	  * printServices( $Action ): prints all or selected service items 
	  */
	 protected function printServices( $Action ) {
	
		// convienence token based on what is the action
		$Token = $Action == 'printall' ? 'All' : 'Selected';
		
		new DivElement( "id=$Action" . 'Header' , 'Report: ' . $Token . ' ' . $_REQUEST['Service'] . ' records.' , true);
		
		switch ( $Token ) {
			
			case 'All':
			$PrintableReport = new Report( $_REQUEST['Service']  , $this->PrintAllColumns );
			$PrintableReport->displayAsHTML();
			break;
			case 'Selected':
			$Ids = array();
			echo 'Printing records Ids=';
			foreach ( $_REQUEST as $key => $val)
				if (is_numeric($key) ) {
					array_push($Ids, $key);
					echo $key . ' ';
				}
					
			$PrintableReport = new Report( $_REQUEST['Service'] , $this->PrintAllColumns, $Ids);
			$PrintableReport->displayAsHTML();
			break;	
		}
		
				 	
	 }
	 
	 /*
	  * displayWelcomeBanner(): display the welcome banner, after initially logging in
	  */
	  private function displayWelcomeBanner() {
	  	
	  	$Banner = new DivElement('id=welcomebanner,class=AdminInterface' , $this->HomePageMessage, true);
			  	
	  }
}
