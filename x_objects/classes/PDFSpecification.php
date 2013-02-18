<?php
//! a PDFSpecification is a data object with details on how to render a specific PDF document
class PDFSpecification extends AttributedObject {

	//! specification as XML
	private $spec_xml = null;

	//! construct using a document name
	public function __construct( $key ) {

		// get xml
		$this->spec_xml = new RealXML( $key );
		
	}
	
	//! get the default data source
	public function getDefaultDataSource() {
	
		return new DataSource(
			'pdf_spec',
			DataSource::TABLE,
			'PDFSpecification',
			array( 'document_name','author','pdf_header_logo','pdf_header_logo_width','document_title', 'content_class'),
			'document_name',
			array( 'document_name'),
			'pdf_spec',
			array( 'document_name' => 'Document Name')
			
		
		);
		
	}
	
	//! get abstraction type
	public function getAbstractionType( $key ) {
		return null;
	}
	
	//! magic get
	public function __get( $what ) {
	
		switch( $what ) {
		
			case 'content_key':
			
				return (string) $this->spec_xml->xml()->content_key;
			
			break;
		
			default:
			
				return $this->get( $what );
				
			break;
		
		}
	}

}

?>