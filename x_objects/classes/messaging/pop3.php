<?php

class POP3
{
	public $port = 110;
	public $timeout = 10;
	public $error = null;
	private $fd = null;
	private $debug = false;
	
	public function __construct($host,$uname,$pass)
	{
		// toggle debugging
		$this->debug = (Debugger::enabled())?true:$this->debug;
		if ( $this->debug)
				echo __FILE__ . " " . __LINE__. " " . get_class() . "{} ". __FUNCTION__ . " host=$host,uname=$uname,pass=$pass<br>";
				
		$this->fd = fsockopen($host,$this->port,$errno,$errstr,$this->timeout);
		stream_set_blocking($this->fd,true);
		if( $errno )
		{
			echo 'Error: '.$errno.': '.$errstr;
			//exit(1);
		}
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			$this->_write('QUIT');
		}
		$this->pUSERPASS($uname,$pass);
	}
	
	private function _write($cmd)
	{
		fwrite($this->fd,$cmd."\r\n");
	}
	
	private function _read($stream=false)
	{
		$retval = null;
		if( ! $stream )
		{
			$retval = fgets($this->fd,1024);
			//$retval = preg_replace("/\r?\n/","\r\n",$retval);
		} else {
			while( ! feof($this->fd) )
			{
				$tmp = fgets($this->fd,1024);
				//$tmp = preg_replace("/\r?\n/","\r\n",$tmp);
				if( chop($tmp) == '.') break;
				$retval .= $tmp;
			}
		}
		return $retval;
	}
	
	private function _check($msg)
	{
		$stat = substr($msg,0,strpos($msg,' '));
		if($stat == '-ERR') return false;
		//if($stat == '+OK') return true;
		return true;
	}

	//login to server
	public function pUSERPASS($user, $password)
	{
		$this->_write('USER '.$user);
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			$this->_write('QUIT');
			return false;
		}
		$this->_write('PASS '.$password);
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			$this->_write('QUIT');
			return false;
		}
		return true;
	}
	
	public function pSTAT()
	{
		$retval = null;
		$this->_write('STAT');
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			$this->_write('QUIT');
			return false;
		}
		list(,$retval['number'],$retval['size']) = split(' ',$msg);
		return $retval;
	}
	
	//list messages on server
	public function pLIST()
	{
		$retval = array();
		$this->_write('LIST');
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			$this->_write('QUIT');
			if ( $this->debug)
				echo __FILE__ . " " . __LINE__. " " . get_class() . "{} ". __FUNCTION__ . " no messages in pLIST<br>";
			return $retval;
		}
		$msg = explode("\n",$this->_read(true));
		for($x = 0; $x < sizeof($msg); $x++ )
		{
			$tmp = explode(' ',$msg[$x]);
			$retval[$tmp[0]] = $tmp[0];
		}
		unset($retval['']);
		return $retval;
	}
	
	//retrive a message from server
	public function pRETR($num)
	{
		$this->_write('RETR '.$num);
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			$this->_write('QUIT');
			return false;
		}
		$msg = $this->_read(true);
		return $msg;
	}
	
	//marks a message deletion from the server
	//it is not actually deleted until the QUIT command is issued.
	//If you lose the connection to the mail server before issuing
	//the QUIT command, the server should not delete any messages
	public function pDELE($num)
	{
		$this->_write('DELE '.$num);
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			$this->_write('QUIT');
			return false;
		}
	}
	
	//This resets (unmarks) any messages previously marked for deletion in this session
	//so that the QUIT command will not delete them
	public function pRSET()
	{
		$this->_write('RSET');
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			return false;
		}
	}
	
	//This deletes any messages marked for deletion, and then logs you off of the mail server.
	//This is the last command to use. This does not disconnect you from the ISP, just the mailbox.
	public function pQUIT()
	{
		$this->_write('QUIT');
		$msg = $this->_read();
		if( ! $this->_check($msg) )
		{
			$this->error = $msg;
			return false;
		}		
	}
	
	public function __destruct()
	{
		fclose($this->fd);
	}
}

/*EXAMPLE:
$pop3 = new POP3();

$pop3->pUSERPASS('tieto','123456');
if($pop3->error){echo 'ERROR: '.$pop3->error; exit(0); }

$messages = $pop3->pLIST();

echo $pop3->pRETR(1);

$pop3->pQUIT();
*/
?>