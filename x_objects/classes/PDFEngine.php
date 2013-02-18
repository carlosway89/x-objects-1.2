<?php


//! class of static members to render and manage PDF documents on the fly.
class PDFEngine {

	//! debug
	private static $debug = false;

	//! render a given PDF document
	public static function render( 
		$document, 				// the name of the document to render
		$title = null ) {		// optionally pass a title to the document
	
		// retrieve details on the specific document
		$spec = new PDFSpecification( $document );
	
		// create new PDF document
		$pdf = new TCPDF(
			$spec->page_orientation, 
			PDF_UNIT, 
			$spec->page_format, 
			true, 
			'UTF-8', 
			false
		);
	
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Caravan');
		$pdf->SetTitle( $title ? $title : $spec->doc_title);
		$pdf->SetSubject('Caravan Listing');
		$pdf->SetKeywords('Caravan,Listing');
		
		// set default header data
		$pdf->SetHeaderData(
			$spec->header_logo,
			$spec->header_logo_width,
			$title ? $title : $spec->doc_title, 
			$spec->document_header
		);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 8));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 8));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(10, 5, 10);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// ---------------------------------------------------------

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('helvetica', '', 8, '', true);

		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

		// Set some content to print	
		$html = x_object::create( $spec->content_key )->xhtml;
		
		if ( self::$debug ) {
			echo $html;
			exit;
		}
		
		// Print text using writeHTMLCell()
		//$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
		$pdf->writeHTML($html, true, false, true, false, '');
		// ---------------------------------------------------------

		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.
		$pdf->Output($document . '.pdf', 'I');

		
	}

}

?>
