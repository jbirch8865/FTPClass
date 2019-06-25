<?php
namespace ftpclass;

ini_set('upload_max_filesize','500M');
ini_set('post_max_size','500M');

class BadIniFile extends Exception {}
class BadFTPLogin extends Exception {}
class CannotDeleteFile extends Exception {}
class ErrorUploadingFile extends Exception {}

class FTP_Link {
	private $FTP_connection;
	private $FTP_hostname;
	private $FTP_username;
	private $FTP_password;
	private $FTP_connection_timeout;
	private $FTP_listening_port;

	function __construct() {
		try {
			$this->LoadIniConfigs();
			$this->FTP_connection = ftp_connect($this->FTP_hostname,$this->FTP_listening_port,$this->FTP_connection_timeout);
			ftp_login($this->FTP_connection,$this->FTP_username,$this->FTP_password);
		} catch (BadIniFile $e) {
			throw new BadIniFile("Error loading Ini configurations");
		} catch (Exception $e) {
			throw new BadFTPLogin("Error logging into the FTP server");
		}
    	}

	private function LoadIniConfigs()
	{
		try {
			$FTP_Configs = parse_ini_file('FTP_config.ini');
			$this->FTP_hostname = $FTP_Configs['hostname'];
			$this->FTP_username = $FTP_Configs['username'];
			$this->FTP_password = $FTP_Configs['password'];
			$this->FTP_connection_timeout = $FTP_Configs['timeoutperiod'];
			$this->FTP_listening_port = $FTP_Configs['ftpport'];
		} catch (Exception $e) {
			throw new BadIniFile("Error loading the ini file configs");
		}
	}

	private function Close_FTP_Connection()
	{
		try
		{
			ftp_close($this->FTP_connection);
			return true;
		} catch (Exception $e)
		{
			return false;
		}
	}

	private function Delete_File($FileToDelete)
	{
		try {
			return ftp_delete($this->FTP_connection,$FileToDelete);
		} catch (Exception $e) {
			throw new CannotDeleteFile("Error deleting file");
		}
	}

	private function List_Files($Directory = '.')
	{
		try {
			return ftp_nlist($this->FTP_connection,$Directory);
		} catch (Exception $e) {
			throw new Exception("Error loading directory");
		}
	}

	public function Upload_Single_File($FileName,$Source_Location,$Target_Location)
	{
		try {
			@$this->Delete_File($Target_Location.'/'.$FileName);
		} catch (CannotDeleteFile $e) {

		}
		try {
			ftp_put($this->FTP_connection,$Target_Location.'/'.$FileName,$Source_Location.'/'.$FileName, FTP_ASCII);
		} catch (Exception $e) {
			throw new ErrorUploadingFile("There was an error uploding this file");
		}
	}
}
?>
