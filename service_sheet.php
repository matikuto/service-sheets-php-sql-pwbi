<?php

// php login

session_start();
if(!isset($_SESSION["sess_user"])){
 header("Location: login.php");
}
else
{
	

// php code to insert data into mysql database from input text
	
if(isset($_POST['insert']))
{
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $databaseName = "";
    
// get values form input text and number

    $tipo = $_POST['tipo'];
	$poliza = $_POST['poliza'];
	$remoto = $_POST['remoto'];
    $cliente = $_POST['cliente'];
	$cliente2 = $_POST['cliente'];
    $recibe = $_POST['recibe'];
	$fecha = $_POST['fecha'];
    $hora_de_llegada = $_POST['hora_de_llegada'];
    $hora_de_salida = $_POST['hora_de_salida'];
	$consultor_asignado = $_POST['consultor_asignado'];
    $actividad1 = $_POST['actividad1'];
    $horas_invertidas_1 = $_POST['horas_invertidas_1'];    
	$observaciones = $_POST['observaciones'];
	
    // connect to mysql database using mysqli

    $connect = mysqli_connect($hostname, $username, $password, $databaseName);
    
    // mysql query to insert data
	
	
 	$query = "INSERT INTO `hojas_de_servicio`(`tipo`, `poliza`, `remoto`, `cliente`, `recibe`, `fecha`, `hora_de_llegada`, `hora_de_salida`, `consultor_asignado`, `actividad1`, `horas_invertidas_1`, `observaciones`) VALUES ('$tipo','$poliza','$remoto','$cliente','$recibe','$fecha','$hora_de_llegada','$hora_de_salida','$consultor_asignado','$actividad1','$horas_invertidas_1','$observaciones')";

    $result = mysqli_query($connect,$query);
    
 
	//fetch the id back to the pdf
	
	 $last_id = mysqli_insert_id($connect);
	 $id = "".$last_id;

	 
// create pdf

require('fpdf/fpdf.php');
	 
	 
// sends information to the wrapped text box 
	
$cliente=array(
	array(
		"",
		"",
		"".$_POST['cliente'],
		""
	),

);

$observaciones=array(
	array(
		"",
		"",
		
		// this code replace all the linebreaks in the designated field because linebreaks "break" the wrapped cells
		
		"".$_POST['observaciones']=preg_replace("/\n|\r|\t/", "\t",$observaciones),
		""
	),

);

$terminos=array(
	array(
		"",
		"",
		"TEXT HERE",
		""
	),

);
$terminos2=array(
	array(
		"",
		"",
		"TEXT HERE",
		""
	),

);
	 
// pdf properties 

$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

//Cell(width , height , text , border , end line , [align] )
	
$pdf->Cell(47 ,1,'',0,0);
$pdf->Cell(96 ,7,'CIA COMPUTACION S.A. DE C.V.',0,0);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(50 ,5,'HOJA DE SERVICIO',0,1); //end of line
$pdf->Cell(189 ,1,'',0,1);
	
//set font to arial, regular, 12pt
	
$pdf->SetFont('Arial','',12);
	$pdf->Cell(47 ,1,'',0,0);
	$pdf->SetFont('Arial','',10);
$pdf->Cell(96 ,7,'[GONZALES PAJES #759]',0,0);

	$pdf->Cell(.1 ,1,'',0,0);
$pdf->Cell(25 ,7,'[ FECHA ]',0,0);

$pdf->Cell(34 ,7,''.$_POST[utf8_decode('fecha')],0,1);//end of line
	$pdf->Cell(47 ,1,'',0,0);
	$pdf->SetFont('Arial','',10);
$pdf->Cell(96 ,7,'[TELEFONO, 9318222]',0,0);
	$pdf->Cell(.1 ,1,'',0,0);
$pdf->Cell(25 ,7,'[					ID					 ]',0,0);

// id insert, once is generated in the database

$pdf->Cell(80 ,7,''.$id,0,1); //end of line
	$pdf->Cell(47 ,1,'',0,0);
$pdf->Cell(130 ,7,'[FAX, 9318222]',0,0);
$pdf->Cell(25 ,7,'',0,0);
$pdf->Cell(34 ,7,'',0,1); //end of line

$pdf->Cell(189 ,7,'',0,1); //dumb line
$pdf->SetFillColor(0,0,0);
$pdf->Cell(189 ,2,'',0,1,'',1); //dumb line
$pdf->SetFillColor(256,256,256);
$pdf->Cell(189 ,7,'',0,1); //dumb line

$pdf->SetFont('Arial','B',12);


// report


$pdf->Cell(130.3 ,10,'REPORTE',1,0,'L',1);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(10 ,5,'',0,0);

$pdf->SetFillColor(256,256,256);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(25 ,10,'LLEGADA',1,0,'',1);
$pdf->Cell(25 ,10,'SALIDA',1,1,'',1);
$pdf->SetTextColor(0,0,0);

$pdf->SetFont('Arial','',11);
$pdf->Cell(55.1 ,7,'CONSULTOR ASIGNADO',1,0);
$pdf->Cell(75.2 ,7,''.$_POST[utf8_decode('consultor_asignado')],1,0);
$pdf->Cell(10 ,5,'',0,0);
$pdf->Cell(25 ,7,''.$_POST['hora_de_llegada'],1,0);
$pdf->Cell(25 ,7,''.$_POST['hora_de_salida'],1,1,'l');
$pdf->Cell(55.1 ,7,'RECIBE',1,0);
$pdf->Cell(75.2 ,7,''.$_POST['recibe'],1,1,'L');
$pdf->Cell(55 ,7,'CLIENTE',1,0);
	 
// making wrapped textbox
	
foreach($cliente as $item){
	$cellWidth=75;//wrapped cell width
	$cellHeight=7;//normal one-line cell height
	
	//check whether the text is overflowing
	if($pdf->GetStringWidth($item[2]) < $cellWidth){
		//if not, then do nothing
		$line=1;
	}else{
		//if it is, then calculate the height needed for wrapped cell
		//by splitting the text to fit the cell width
		//then count how many lines are needed for the text to fit the cell
		
		$textLength=strlen($item[2]);	//total text length
		$errMargin=10;		//cell width error margin, just in case
		$startChar=0;		//character start position for each line
		$maxChar=0;			//maximum character in a line, to be incremented later
		$textArray=array();	//to hold the strings for each line
		$tmpString="";		//to hold the string for a line (temporary)
		
		while($startChar < $textLength){ //loop until end of text
			//loop until maximum character reached
			while( 
			$pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
			($startChar+$maxChar) < $textLength ) {
				$maxChar++;
				$tmpString=substr($item[2],$startChar,$maxChar);
			}
			//move startChar to next line
			$startChar=$startChar+$maxChar;
			//then add it into the array so we know how many line are needed
			array_push($textArray,$tmpString);
			//reset maxChar and tmpString
			$maxChar=0;
			$tmpString='';
			
		}
		//get number of line
		$line=count($textArray);
	}
	
	//write the cells
	$pdf->Cell(.1,($line * $cellHeight),$item[0],0,0); //adapt height to number of lines
	$pdf->Cell(.1,($line * $cellHeight),$item[1],0,0); //adapt height to number of lines
	
	//use MultiCell instead of Cell
	//but first, because MultiCell is always treated as line ending, we need to 
	//manually set the xy position for the next cell to be next to it.
	//remember the x and y position before writing the multicell
	$xPos=$pdf->GetX();
	$yPos=$pdf->GetY();
	$pdf->MultiCell($cellWidth,$cellHeight,utf8_decode($item[2]),1);
	
	//return the position for next cell next to the multicell
	//and offset the x with multicell width
	$pdf->SetXY($xPos + $cellWidth , $yPos);
	
	$pdf->Cell(.1,($line * $cellHeight),$item[3],0,1); //adapt height to number of lines
	
	
}






//make a dummy empty cell as a vertical spacer
	
$pdf->Cell(189 ,5,'',0,1); //end of line


// activities 


$pdf->SetFont('Arial','B',12);
$pdf->Cell(130.3 ,10,'ACTIVIDAD',1,0);
$pdf->Cell(10 ,10,'',0,0);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(50 ,10,'TIEMPO INVERTIDO',1,0);




$pdf->SetFont('Arial','',11);

// Numbers are right-aligned so we give 'R' after new line parameter
$pdf->Ln();
$pdf->Cell(130.3 ,7,''.$_POST['actividad1'],1,0);
$pdf->Cell(10 ,7,'',0,0);
$pdf->Cell(50 ,7,''.$_POST['horas_invertidas_1'],1,0);
$pdf->Ln();

// make a dummy empty cell as a vertical spacer
$pdf->Cell(189 ,5,'',0,1);//end of line


// observations 


$pdf->SetFont('Arial','B',12);

$pdf->Cell(130.3 ,10,'OBSERVACIONES',1,1);
$pdf->SetTextColor(0,0,0);


$pdf->SetFont('Arial','',11);
	
// making wrapped textbox

foreach($observaciones as $item){
	$cellWidth=130;//wrapped cell width
	$cellHeight=5;//normal one-line cell height
	
	//check whether the text is overflowing
	if($pdf->GetStringWidth($item[2]) < $cellWidth){
		//if not, then do nothing
		$line=1;
	}else{
		//if it is, then calculate the height needed for wrapped cell
		//by splitting the text to fit the cell width
		//then count how many lines are needed for the text to fit the cell
		
		$textLength=strlen($item[2]);	//total text length
		$errMargin=10;		//cell width error margin, just in case
		$startChar=0;		//character start position for each line
		$maxChar=0;			//maximum character in a line, to be incremented later
		$textArray=array();	//to hold the strings for each line
		$tmpString="";		//to hold the string for a line (temporary)
		
		while($startChar < $textLength){ //loop until end of text
			//loop until maximum character reached
			while( 
			$pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
			($startChar+$maxChar) < $textLength ) {
				$maxChar++;
				$tmpString=substr($item[2],$startChar,$maxChar);
			}
			//move startChar to next line
			$startChar=$startChar+$maxChar;
			//then add it into the array so we know how many line are needed
			array_push($textArray,$tmpString);
			//reset maxChar and tmpString
			$maxChar=0;
			$tmpString='';
			
		}
		//get number of line
		$line=count($textArray);
	}
	
	//write the cells
	$pdf->Cell(.1,($line * $cellHeight),$item[0],1,0); //adapt height to number of lines
	$pdf->Cell(.1,($line * $cellHeight),$item[1],1,0); //adapt height to number of lines
	
	//use MultiCell instead of Cell
	//but first, because MultiCell is always treated as line ending, we need to 
	//manually set the xy position for the next cell to be next to it.
	//remember the x and y position before writing the multicell
	$xPos=$pdf->GetX();
	$yPos=$pdf->GetY();
	$pdf->MultiCell($cellWidth,$cellHeight,utf8_decode($item[2]),1);
	
	//return the position for next cell next to the multicell
	//and offset the x with multicell width
	$pdf->SetXY($xPos + $cellWidth , $yPos);
	
	$pdf->Cell(.1,($line * $cellHeight),$item[3],1,1); //adapt height to number of lines
	
	
}


// signatures

$pdf->Cell(189 ,10,'',0,1);//dumb line
$pdf->SetFillColor(0,0,0);
$pdf->Cell(189 ,2,'',0,1,'',1);//dumb line
$pdf->Cell(189 ,7,'',0,1);//dumb line
$pdf->Image('images/logoviejo.png',12,8,40,0,'','png');


// making wrapped textbox
	
$pdf->SetFont('Arial','',8);
foreach($terminos2 as $item){
	$cellWidth=45;//wrapped cell width
	$cellHeight=4;//normal one-line cell height
	
	//check whether the text is overflowing
	if($pdf->GetStringWidth($item[2]) < $cellWidth){
		//if not, then do nothing
		$line=0;
	}else{
		//if it is, then calculate the height needed for wrapped cell
		//by splitting the text to fit the cell width
		//then count how many lines are needed for the text to fit the cell
		
		$textLength=strlen($item[2]);	//total text length
		$errMargin=10;		//cell width error margin, just in case
		$startChar=0;		//character start position for each line
		$maxChar=0;			//maximum character in a line, to be incremented later
		$textArray=array();	//to hold the strings for each line
		$tmpString="";		//to hold the string for a line (temporary)
		
		while($startChar < $textLength){ //loop until end of text
			//loop until maximum character reached
			while( 
			$pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
			($startChar+$maxChar) < $textLength ) {
				$maxChar++;
				$tmpString=substr($item[2],$startChar,$maxChar);
			}
			//move startChar to next line
			$startChar=$startChar+$maxChar;
			//then add it into the array so we know how many line are needed
			array_push($textArray,$tmpString);
			//reset maxChar and tmpString
			$maxChar=0;
			$tmpString='';
			
		}
		//get number of line
		$line=count($textArray);
	}
	
	//write the cells
	$pdf->Cell(.01,($line * $cellHeight),$item[0],0,0); //adapt height to number of lines
	$pdf->Cell(5,($line * $cellHeight),$item[1],0,0); //adapt height to number of lines
	
	//use MultiCell instead of Cell
	//but first, because MultiCell is always treated as line ending, we need to 
	//manually set the xy position for the next cell to be next to it.
	//remember the x and y position before writing the multicell
	$xPos=$pdf->GetX();
	$yPos=$pdf->GetY();
	$pdf->MultiCell($cellWidth,$cellHeight,$item[2],0);
	
	//return the position for next cell next to the multicell
	//and offset the x with multicell width
	$pdf->SetXY($xPos + $cellWidth , $yPos);
	
	$pdf->Cell(.1,($line * $cellHeight),$item[3],0,0); //adapt height to number of lines
	
	
}
$pdf->Cell(3.9 ,1,'',0,0,'R');
$pdf->SetFont('Arial','B',12);
$pdf->Cell(68 ,7,'NOMBRE Y FIRMA CLIENTE',1,0);
$pdf->Cell(68 ,7,'NOMBRE Y FIRMA CONSULTOR',1,1);

$pdf->Cell(54 ,5,'',0,0);
$pdf->Cell(68 ,30,'',1,0);
$pdf->Cell(68 ,30,'',1,0);

$pdf->Cell(189 ,35,'',0,1);//dumb line

$pdf->SetFont('Arial','',8);
	
// making wrapped textbox
	
foreach($terminos as $item){
	$cellWidth=122;//wrapped cell width
	$cellHeight=5;//normal one-line cell height
	
	//check whether the text is overflowing
	if($pdf->GetStringWidth($item[2]) < $cellWidth){
		//if not, then do nothing
		$line=1;
	}else{
		//if it is, then calculate the height needed for wrapped cell
		//by splitting the text to fit the cell width
		//then count how many lines are needed for the text to fit the cell
		
		$textLength=strlen($item[2]);	//total text length
		$errMargin=10;		//cell width error margin, just in case
		$startChar=0;		//character start position for each line
		$maxChar=0;			//maximum character in a line, to be incremented later
		$textArray=array();	//to hold the strings for each line
		$tmpString="";		//to hold the string for a line (temporary)
		
		while($startChar < $textLength){ //loop until end of text
			//loop until maximum character reached
			while( 
			$pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
			($startChar+$maxChar) < $textLength ) {
				$maxChar++;
				$tmpString=substr($item[2],$startChar,$maxChar);
			}
			//move startChar to next line
			$startChar=$startChar+$maxChar;
			//then add it into the array so we know how many line are needed
			array_push($textArray,$tmpString);
			//reset maxChar and tmpString
			$maxChar=0;
			$tmpString='';
			
		}
		//get number of line
		$line=count($textArray);
	}
	
	//write the cells
	$pdf->Cell(.01,($line * $cellHeight),$item[0],0,0); //adapt height to number of lines
	$pdf->Cell(5,($line * $cellHeight),$item[1],0,0); //adapt height to number of lines
	
	//use MultiCell instead of Cell
	//but first, because MultiCell is always treated as line ending, we need to 
	//manually set the xy position for the next cell to be next to it.
	//remember the x and y position before writing the multicell
	$xPos=$pdf->GetX();
	$yPos=$pdf->GetY();
	$pdf->MultiCell($cellWidth,$cellHeight,$item[2],0);
	
	//return the position for next cell next to the multicell
	//and offset the x with multicell width
	$pdf->SetXY($xPos + $cellWidth , $yPos);
	
	$pdf->Cell(.1,($line * $cellHeight),$item[3],0,1); //adapt height to number of lines
	
	
}


//make a dummy empty cell as a vertical spacer
	
$pdf->Cell(189 ,10,'',0,1);//end of line

$pdf->Output();

// sending email with the pdf

// can be any email
	
$to = ""; 
	
// the email should be in your server
	
$from = "no-reply@example.com"; 
$subject = "Hoja de servicio $id - ciacomputacion.com"; 
$message = utf8_decode("Cliente: $cliente2, Fecha: $fecha, Consultor: $consultor_asignado");

// a random hash will be necessary to send mixed content
	
$separator = md5(time());

// carriage return type (we use a PHP end of line constant)
	
$eol = PHP_EOL;

// attachment name 
	
$filename = "Service sheet $id.pdf";

// encode data (puts attachment in proper format)
$pdfdoc = $pdf->Output("", "S");
$attachment = chunk_split(base64_encode($pdfdoc));

// main header
$headers  = "From: ".$from.$eol;
$headers .= "MIME-Version: 1.0".$eol; 
$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

// no more headers after this, we start the body! //

$body = "--".$separator.$eol;
$body .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
$body .= utf8_decode("Cliente: $cliente2, Fecha: $fecha, Consultor: $consultor_asignado".$eol);

// message
$body .= "--".$separator.$eol;
$body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
$body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
$body .= $message.$eol;

// attachment
$body .= "--".$separator.$eol;
$body .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol; 
$body .= "Content-Transfer-Encoding: base64".$eol;
$body .= "Content-Disposition: attachment".$eol.$eol;
$body .= $attachment.$eol;
$body .= "--".$separator."--";

// send message
mail($to, $subject, $body, $headers);

// closing login and sql code
	
    mysqli_free_result($result);
    mysqli_close($connect);
}
	}
?>
