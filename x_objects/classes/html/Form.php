<?php 
/*
 * Project:			PlatformPHP
 * Module:			Form
 * Version:			1.2
 * ModDate:			June 3, 2010
 * Author:			David Owen Greenberg
 * Descr:			Object representation of an HTML Form
 */
class Form {

	// the Form var holds the key data about the Form itself
	private $Form = null;
	
	// fieldsets are arrays with sets of fields and a unique label
	private $Fieldsets = null;
	
	/*
	 * This variable allows the user to specify an optional include file to perform form validation
	 */
	private $ValidationFile;
	
	/*
	 * Here is a specification of how to create a new Form with proper syntax
	 * 
	 * FormData: An array of values about the form itself
	 * - name: the name of the form
	 * - id: html id element 
	 * - class: html class element
	 * - multipage: specifies whether form has multiple page support
	 */
	
	function __construct( $FormData, $Fieldsets, $ValidationFile = null ) {

		// save arguments as local 
		$this->Form = $FormData;	
		$this->Fieldsets = $Fieldsets;
		$this->ValidationFile = $ValidationFile;
		
	}
	
	public function displayAsHTML() {

		// start generating the form, using the spec set at construction 
		
		// create a div to wrap the entire form
		echo '<div id="FormWrapper">' . "\r\n";
		
		echo "\r\n" . '<form ';
		echo 'name="Form' . $this->Form['name'] .  '" id="Form' . $this->Form['id'] . 
			'" action="' . $this->Form['action'] .
			'" method="' . $this->Form['method'] . '">' . "\r\n";
		
		// actions for forms with multiple pages
		if ( $this->Form['multipage'] ) {
		
			// set next page
			echo '<input type="hidden" name="page" id="page" value="' . ($NewPage = $_REQUEST['page'] + 1) . '"></input>' . "\r\n";
		
			// if we had a previous page, include submitted POST vars again
			if ( isset($_REQUEST['page'])) {
  				foreach($_POST as $key=>$value){
    				if ($key!="SubmitContinue" && $key != 'page'){
      					$value=htmlentities(stripslashes(strip_tags($value)));
      					echo "\t<input type=\"hidden\" name=\"$key\" id=\"$key\" value=\"$value\">\n";
    				}
  				}
  			}
		}
		
		// for each fieldset, generate a container
		foreach ($this->Fieldsets as $Fieldset) {
		
		
			//skip if page doesn't match, but only for multi-page forms
			if ( $this->Form['multipage'] && $Fieldset['attributes']['page'] != $_REQUEST['page'])
				continue;
				
			// to be able to hide an optional fieldset
			if ( isset($Fieldset['attributes']['optional']) )
				echo '<div class="Form" id="' . $Fieldset['attributes']['id'] . '">The following section is optional.' .
					'<input type="radio" name="Hide' . $Fieldset['attributes']['id'] . '" id="' . $Fieldset['attributes']['id'] . '" value="show" checked>Show</input>' .
					'<input type="radio" name="Hide' . $Fieldset['attributes']['id'] . '" id="' . $Fieldset['attributes']['id'] . '" value="hide">Hide</input></div>' . "\r\n";
								
		
		
			echo '<fieldset name="' . $Fieldset['attributes']['name'] . '">' . "\r\n";
			echo '<legend>' . $Fieldset['attributes']['name'] . '</legend>' . "\r\n";
			
			// print a headline if one exists
			if ( isset( $Fieldset['attributes']['headline'] ) )
				echo $Fieldset['attributes']['headline'] . "\r\n";
			
			// begin table
			echo '<table class="Form" >' . "\r\n";
			
			// iterate through the fields and display them
			foreach ( $Fieldset['fields'] as $Fieldname => $Field ) { 
			
				// start table row
				if ( ! isset($Field['joinlast'])) {
					echo '<tr class="Form" id="TR' . $Fieldname . '">';
					echo '<td class="Form" id="LB' . $Fieldname . '">';
					echo '<label for="' . $Fieldname . '">' . isset($Field['label']) ? $Field['label'] : '' . '</label></td>' . "\r\n";
				}
				
				switch ( $Field['type']) {
				case 'textarea':
					echo '<td class="Form" id="TD' . $Fieldname . '">' . "\r\n";
					echo '<textarea name="' . $Fieldname . '" id="' . $Fieldname . '"></textarea>' . "\r\n";
					echo '</td>' . "\r\n";
					break;
				case 'enumradio':
					echo '<td class="Form" id="TD' . $Fieldname . '">' . "\r\n";
					foreach ( $Field['values'] as $Value)
						echo '<input type="radio" name="' . $Fieldname . '" id="' . $Fieldname . '" value="' . $Value . '">' . $Value . '</input>' . "\r\n";
					echo '</td>' . "\r\n";
					break;
				case 'rating':
					echo '<td class="Form" id="TD' . $Fieldname . '">' . "\r\n";
					for ( $I = 1 ; $I <= $Field['maxvalue']; $I++)
						echo '<input type="radio" name="' . $Fieldname . '" id="' . $Fieldname . '" value="' . $I . '">' . $I . '</input>' . "\r\n";
					echo '</td>' . "\r\n";
					break;
				case 'label':
					break;
				case 'yesnodk':
					echo '<td class="Form" id="TD' . $Fieldname . '">' . 
						'<input type="radio" name="' . $Fieldname . '" id="' . $Fieldname . '" value="1">Yes</input>' . 
						'<input type="radio" name="' . $Fieldname . '" id="' . $Fieldname . '" value="0">No</input>' .
						'<input type="radio" name="' . $Fieldname . '" id="' . $Fieldname . '" value="-1">Don\'t Know</td>' . "\r\n" ;
					break;
				case 'yesno':
					echo '<td class="Form" id="TD' . $Fieldname . '">' . 
						'<input type="radio" name="' . $Fieldname . '" id="' . $Fieldname . '" value="1">Yes</input>' . 
						'<input type="radio" name="' . $Fieldname . '" id="' . $Fieldname . '" value="0">No</input></td>' . "\r\n" ;
					
					break;
				case 'RadioComponent':
				case 'CheckboxComponent':
					$Component = new $Field['type']( $Fieldname );
					echo '<td class="Form" id="TD' . $Fieldname . '">' . "\r\n";
					$Component->displayAsHTML( $Fieldname , 'Form' , ( isset($Field['default']) ? $Field['default'] : null ));
					echo '</td>' . "\r\n";  
					break;
				case 'radio':
					echo '<td class="Form" id="TD'  . $Fieldname . '">' . "\r\n";
					foreach ( $Field['values'] as $Value)
						echo '<input type="radio" id="' . $tFieldname . '" name="' . $Fieldname . '" value="' . $Value . '">'  .   $Value   . '</input>' . "\r\n";
					echo '</td>' . "\r\n";
					break;
				case 'hidden':
					echo '<input type="hidden" name="' . $Fieldname . '" value="' . $Field['value'] . '"></input>' . "\r\n";
					break;
				case 'CountryComponent':
					// create a new country component and display it
					$CC = new CountryComponent();
					echo '<td class="Form" id="TD' . $Fieldname . '">' . "\r\n";
					$CC->displayAsHTML( $Fieldname , 'Form' , ( isset($Field['default']) ? $Field['default'] : null ) );
					echo '</td>' . "\r\n";
					break;
				case 'text':
					echo '<td class="Form" id="TD' . $Fieldname . '"><input type="text" name="' . $Fieldname . '"></input></td>' . "\r\n";
					break;
				case 'select':
					if ( ! isset($Field['joinlast']))
						echo '<td class="Form" id="TD' . $Fieldname . '">';
					echo '<select onchange="'  .$Field['onchange'] . '" name="' . $Fieldname . '" id="' . $Fieldname . '">' . "\r\n";
					foreach ( $Field['values'] as $Label => $Value)
						echo '<option value="' . $Value . '">' . $Label . '</option>' . "\r\n";
					echo '</select>' . "\r\n";
					if ( ! $Field['joinnext'] )
						echo '</td>' . "\r\n";
					break;
				case 'date':
					echo '<td class="Form" id="TD' . $Fieldname . '">' . "\r\n";
					echo '<script>DateInput(\'' . $Fieldname . '\', true, \'YYYY-MM-DD\' ';
					echo ')</script></td>' . "\r\n";
				}
				
				// end table row, but only if not joining next
				if ( ! isset($Field['joinnext']))
					echo '</tr>' . "\r\n";
				
				// set continue on pagebreak
				if ( isset($Field['pagebreak'] )) {
					echo '<tr class="Form" id="' . $this->Form['id'] . '">' .
						//'<td class="Form" id="' . $this->Form['id'] . '"></td>' .
						'<td class="Form" id="' . $this->Form['id'] . '">' .
						'<button ';
					echo 'id="FormSubmitButton" type="submit" name="FormSubmitButton">Submit</button></td>' . "\r\n";
					// for consistency
					echo '<td class="Form"></td></tr>' . "\r\n";
				}
				
			}
			
			// end the table
			echo '</table>' . "\r\n";
				
			echo '</fieldset>' . "\r\n";
		}
		
		// finish off rendering the form
		echo '</form>' . "\r\n";
		
		/*
		 * the following custom code will be invoked if a validation file was specified
		 */
		if ( $this->ValidationFile )
			require_once( $this->ValidationFile );
		
		// end the div
		echo '</div>' . "\r\n";
	}
	
	public function process() {
	
		
	}

}
?>