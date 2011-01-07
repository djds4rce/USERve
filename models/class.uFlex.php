<?php
// For Support visit http://sourceforge.net/projects/uflex/support
// ---------------------------------------------------------------------------
// 	  uFlex - An all in one authentication system PHP class
//    Copyright (C) 2010  Pablo Tejada
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.html.
// ---------------------------------------------------------------------------

/*Thought the Class Official name is userFlex the object is simply named uFlex*/
class uFlex{
	//Constants
	const version = 0.41;
	const salt = "sd2eq"; //IMPORTANT: Please change this value as it will make this copy unique and secured
	//End of constants\\\\
	var $id;       //Signed user ID
	var $sid;      //Current User Session ID
	var $username; //Signed username
	var $pass;     //Holds the user password hash
	var $signed;   //Boolean, true = user is signed-in
	var $data;     //Holds entire user database row
	var $console;  //Cotainer for errors and reports
	var $log;      //Used for traking errors and reports
	var $confirm;	 //Holds the hash for any type of comfirmation
	var $tmp_data; //Holds the temporary user information during registration
	var $opt = array( //Array of Internal options
	         "table_name" => "users",
					 "cookie_time" => "+30 days",
					 "cookie_name" => "auto",
					 "cookie_path" => "/",
					 "cookie_host" => false
					 );
	var $validations = array( //Array for default field validations
			"username" => array(
								"limit" => "3-15",
								"regEx" => "/^([a-zA-Z0-9_])+$/"
								),
			"password" => array(
								"limit" => "3-15",
								"regEx" => false
								),
			"email" => array(
								"limit" => "4-45",
								"regEx" => "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/",
								)
			);
	var $encoder = array(
			"a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
			"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
			0,2,3,4,5,6,7,8,9
		);

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Register A New User
-Takes two parameter the first been required

	@info = array object (takes an associatve array, 
				the index being the fieldname(column in database) 
				and the value its content(value)
	+optional second parameter
	@activation = boolean(true/false)
		default = false 
Returns activation hash if second parameter @activation is true
Returns true if second parameter @activation is false
Returns false on Error
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function register($info){
  	        global $Activation;
              	$this->logger("registration");  //Index for Errors and Reports
		
		//Saves Registration Data in Class
		$this->tmp_data = $info;
				
		//Validate All Fields
		if(!$this->validateAll()) return false; //There are validations error
		
	//Built in actions for special fields
		//Hash Password
		if(isset($info['password'])){ 
			$this->hash_pass($info['password']);
			$info['password'] = $this->pass;
		}		
		//Check for Email in database
		if(isset($info['email']))
			if($this->check_field('email',$info['email'],"This Email is Already in Use")) return false;
			
		//Check for username in database
		if(isset($info['username']))
			if($this->check_field('username',$info['username'],"This Username is not available")) return false;
		
		//Check for errors
		if($this->has_error()) return false;
		
		//Set Registration Date
		$info['reg_date'] = time();		
		
		//User Activation
                
		if(!$Activation){//Activates user upon registration
			$info['activated'] = 1;			
		}
		
		//Prepare Info for SQL Insertion
		foreach($info as $index => $val){
			if(!preg_match("/2$/",$index)){ //Skips double fields
				$into[] = $index;
				$values[] = "'" . mysql_real_escape_string($val) . "'";
			}
		}		
		
		$into = implode(", ",$into);
		$values = implode(",",$values);
		
		//Prepare New User	Query
		$sql = "INSERT INTO users ($into) 
					VALUES($values)";
		//exit($sql);
                  
		 

		//Enter New user to Database
		if($this->check_sql($sql)){
			$this->report("New User \"{$info['username']}\" has been registered");
			$this->id = mysql_insert_id();
			if($Activation){
                          //Insert Validation Hash
                          $this->make_hash($this->id);
                          $this->save_hash();
                          user_reg_mail("new-registration",$this->confirm,$info['email']);
                          
                     }
			return true;
		}else{
			$this->error("New User Registration Failed");
			return false;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Similar to the register method function in structure
This Method validates and updates any field in the database
-Takes one parameter
	@info = array object (takes an associatve array, 
				the index being the fieldname(column in database) 
				and the value its content(value)
On Success returns true
On Failure return false	
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function update($info){
		$this->logger("update");  //Index for Errors and Reports
		
		//Saves Updates Data in Class
		$this->tmp_data = $info;
				
		//Validate All Fields
		if(!$this->validateAll()) return false; //There are validations error
		
	//Built in actions for special fields
		//Hash Password
		if(isset($info['password'])){ 
			$this->hash_pass($info['password']);
			$info['password'] = $this->pass;
		}		
		//Check for Email in database
		if(isset($info['email']))
			if($this->check_field('email',$info['email'],"This Email is Already in Use")) return false;		
		
		//Check for errors
		if($this->has_error()) return false;
				
		//Prepare Info for SQL Insertion
		foreach($info as $index => $val){
			if(!preg_match("/2$/",$index)){ //Skips double fields
				$value = "'".mysql_real_escape_string($val)."'";
				$set[] = "{$index}={$value}";
			}			
		}		
		
		$set = implode(", ",$set);
		
		//Prepare User Update	Query
		$sql = "UPDATE users SET $set 
					WHERE user_id='{$this->id}'";		
		//exit($sql);
		
		//Check for Changes
		if($this->check_sql_change($sql,true) ){
			$this->report("Information Updated");
			$_SESSION['uFlex']['update'] = true;
			return true;
		}else{
			$this->error("The Changes Could not be made");
			return false;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Adds validation to quene list for either the Registration or Update Method
Single Entry:
	Requires the first two parameters
		@name = string (name of the field to be validated)
		@limit    = string (range in the format of "5-10")
			*to make a field optional start with 0 (Ex. "0-10")
	Optional third paramenter
		@regEx = string (Regular Expresion to test the field)
Multiple Entry:
	Takes only the first argument
		@name = Array Object (takes an object in the following format:
			array(
				"username" => array(
									"limit" => "3-15",
									"regEx" => "/^([a-zA-Z0-9_])+$/"
									),
				"password" => array(
									"limit" => "3-15",
									"regEx" => false
								i	)
				);
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function addValidation($name,$limit=false,$regEx=false){
		$this->logger("registration");
		if(is_array($name)){
			if(!is_array($this->validations)) $this->validations = array(); //If is not an array yet, make it one
			$new = array_merge($this->validations,$name);
			$this->validations = $new;
			$this->report("New Validation Object added");
		}else{
			$this->validations[$name]['limit'] = $limit;
			$this->validations[$name]['regEx'] = $regEx;
			$this->report("The $name field has been added for validation");
		}
	}
	
///////////////////////////////////////////////////////////////////////////////////////////////////////	
/*
Activates Account with hash
Takes Only and Only the URL c parameter of the comfirmation page
	@hash = string
Returns true on account activation and false on failure
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function activate($hash){
		$this->logger("activation");
    
    if(!$this->check_hash($hash)) return false;
    
		$sql = "UPDATE users SET activated=1, confirmation='' WHERE confirmation='{$hash}' AND user_id='{$this->id}'";
		
		if($this->check_sql_change($sql,true)){
			$this->report("Account has been Activated");
			return true;
		}else{
			$this->error("Account could not be activated");
			return false;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Method to reset password, sents an email with a confirmation code to reset password
-Takes one parameter and is required
	@email = string(user email to reset password)
On Success it returns a hash which could then be use to construct the confirmation URL
On Failure it returns false
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function pass_reset($email){
		$this->logger("pass_reset");
                mysql_real_escape_string($email);
		$sql = "SELECT * FROM {$this->opt['table_name']} WHERE email='{$email}'";
		
		$user = $this->getRow($sql);
		
		if($user){
			$this->make_hash($user['user_id']);
			$this->id = $user['user_id'];
			$this->save_hash();
			
			$data = Array(
			         "email" => $email,
			         "username" => $user['username'],
			         "user_id" => $user['user_id'],
			         "hash" => $this->confirm
			         );
			return $data;
		}else{
			$this->error("We don't have an account with this email");
			return false;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Changes a Password with a Confirmation hash from the pass_reset method
*this is for users that forget their passwords to change the signed user password use ->update()
-Takes two parameters
	@hash = string (pass_reset method hash)
	@new = array (an array with indexes 'password' and 'password2')
					Example:
					array(
						[password] => pass123
						[password2] => pass123
					)
					*use ->addValidation('password', ...) to validate password
Returns true on a successfull password change
Returns false on error
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function new_pass($hash,$newPass){
		$this->logger("new_pass");
    
    if(!$this->check_hash($hash)) return false;
		
		if($newPass['password'] != $newPass['password2']){
			$this->form_error("password","Passwords did not matched");
			return false;
		}
		
		$this->tmp_data = mysql_real_escape_string($newPass);
		if(!$this->validateAll()) return false; //There are validations error
		
		$pass = $this->hash_pass($newPass['password']);
		
		$sql = "UPDATE users SET password='{$pass}', confirmation='', activated=1 WHERE confirmation='{$hash}' AND user_id='{$this->id}'";
		if($this->check_sql_change($sql)){
			$this->report("Password has been changed");
			return true;
		}else{
			//Error
			$this->error("Password could not be changed. The request can't be validated");
			return false;
		}		
	}

 /*////////////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*\
////////Private and Secondary Methods below this line\\\\\\\\\\\\\
 \*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\/////////////////////////////////////////////*/
	 function __construct($user=false,$pass=false,$auto=false){

		$this->logger("login");  //Index for Reports and Errors;
		session_start();
		$this->sid = session_id();
		$result = $this->login();
		if($result == false){
			$_SESSION['userData'] = array("username" => "Guess",
					  "user_id" => 0,
					  "password" => 0,
					  "signed" => false
						  );

			$this->update_from_session();
			$this->report("User is Guess");
		}else{
		if(!$auto and isset($_SESSION['uFlex']['remember'])){
          		unset($_SESSION['uFlex']['remember']);
			$this->setCookie();
			}
		}
                	return true;
	}
	 function login($user=false,$pass=false,$auto=false){
		//Session Login
                $this->logger("login");
		if(@$_SESSION['userData']['signed']){
			$this->report("User Is signed in from session");
			$this->update_from_session();
			if(isset($_SESSION['uFlex']['update'])){
				$this->report("Updating Session from database");	
				//Get User From database because its info has change during current session
				$update = $this->getRow("SELECT * FROM {$this->opt['table_name']} WHERE user_id='{$this->id}'");				
				$this->update_session($update);
				$this->log_login() ;  //Update last_login
			}						
			return true;
		}		
		if(isset($_COOKIE[$this->opt['cookie_name']]) and !$user and !$pass){
		//Cookies Login	
			$c = $_COOKIE[$this->opt['cookie_name']];
      $this->report("Attemping Login with cookies");
      if($this->check_hash($c,true)){
        $auto = true;
      }else{
        $this->error("Logging with cookies failed");
        $this->logout();
        return false;
      }
		}else{
		//Credentials Login
			if($user && $pass){
				$this->username =mysql_real_escape_string($user);
				$this->hash_pass(mysql_real_escape_string($pass));
				$this->report("Creadentials recieved");
			}else{
				$this->error("No Username or Password provided");
				return false;
			}
		}

		$this->report("Quering Database to autenticate user");
		//Query Database and check login
		$sql = "SELECT * FROM {$this->opt['table_name']} WHERE username='{$this->username}' AND password='{$this->pass}'";
		$user = $this->getRow($sql);
		if($user){
			//If Account is not Activated
			if($user['activated'] == 0){
				if($user['last_login'] == 0){
					//Account has not been activated
					$this->error("Your Account has not been Activated. Check your Email for instructions");
				}else{
					//Account has been deactivated
					$this->error("Your account has been deactivated. Please contact Administrator");
				}
				return false;
			}
			//Account is Activated and user is logged in
			$this->update_session($user);
			
			//If auto Remember User
			if($auto){
				$this->setCookie();
			}
                       elseif( isset($_SESSION['uFlex']['remember'])){
                                unset($_SESSION['uFlex']['remember']);
                                $this->setCookie();
 			}
			$this->log_login() ;//Update last_login
			//Done
			$this->report("User Logged in Successfully");
			return true;
		}else{
			if(isset($_COOKIE[$this->opt['cookie_name']])){
				$this->logout();
			}
			$this->error("Wrong Username or Password");
			return false;
		}
	}
	
	function logout(){
		$this->logger("login");
		$deleted = setcookie($this->opt['cookie_name'],"", time()-3600,"/"); //Deletes the Auto Coookie
		unset($_SESSION['userData']);
    $this->signed = 0;
    if(!$deleted){
      $this->report("The Autologin cookie could not be deleted");
    }
		$this->report("User Logged out");
	}
	
	private function log_login(){
		//Update last_login
		$time = time();
		$sql = "UPDATE users SET last_login='{$time}' WHERE user_id='{$this->id}'";
		if($this->check_sql($sql)) $this->report("Last Login updated");
	}
	
	function setCookie(){
		if($this->pass and $this->id){
      
      $code = $this->make_hash($this->id,$this->pass);
      
      if(!headers_sent()){
        //echo "PHP";
        setcookie($this->opt['cookie_name'],$code,strtotime($this->opt['cookie_time']),"/",$this->opt['cookie_host']);
      }else{
        //Headers have been sent use JavaScript to set cookie
        $time = intval($this->opt['cookie_time']);
        echo "<script>";
        echo '
          function setCookie(c_name,value,expiredays){
            var exdate=new Date();
            exdate.setDate(exdate.getDate()+expiredays);
            document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : "; expires="+exdate.toUTCString()); path=escape("'.$this->opt["cookie_path"].'");
          }
        ';
        echo "setCookie('{$this->opt['cookie_name']}','{$code}',{$time})";
        echo "</script>";
      }
			
			$this->report("Cookies have been updated for auto login");
		}else{
			$this->error("Info requiered to set the {$this->opt['cookie_name']} is not available");
		}
	}
	
	private function update_session($d){
		unset($_SESSION['uFlex']['update']);
		
		$_SESSION['userData'] = $d;
		$_SESSION['userData']['signed'] = 1;	
		
		$this->report("Session updated");
		$this->update_from_session();
	}
	
	private function update_from_session(){
		$d = $_SESSION['userData'];
		
		$this->id = $d['user_id'];
		$this->data = $d;
		$this->username = $d['username'];
		$this->pass = $d['password'];
		$this->signed = $d['signed'];
		
		$this->report("Session has been imported to the object");
	}
	
	function hash_pass($pass){
		$salt = uFlex::salt;
		$this->pass = md5($salt.$pass.$salt);
		return $this->pass;
	}
	
	function logger($log){
		$this->log = $log;
		unset($this->console['errors'][$log]);
		unset($this->console['form'][$log]);
		$this->report(">>Startting new $log request");
	}
	
	function report($str=false){
		$index = $this->log;
		if($str){
			$str = ucfirst($str);
			$this->console['reports'][$index][] = $str; //Strore Report
		}else{
			if($index){
				return $this->console['reports'][$index]; //Return the $index Reports Array
			}else{
				return $this->console['reports']; //Return the Full Reports Array
			}
		}
	}

	function error($str=false){
		$index = $this->log;
		if($str){
			$str = ucfirst($str); //Style String by making first character uppercase
			$this->console['errors'][$index][] = $str; //Store Error
			$this->report("Error: {$str}"); //Report The error
		}else{
			if($index){
				if(!isset($this->console['errors'][$index])) return false;
				return $this->console['errors'][$index]; //Return the $index Errors Array
			}else{
				return $this->console['errors']; //Return the Full Error Array
			}
		}
	}
	
	//Adds fields with errors to the console
	function form_error($field=false,$error=false){
		$index = $this->log;
		if($field){
			if($error){
				$this->console['form'][$index][$field] = $error;
				$this->error($error);
			}else{
				$this->console['form'][$index][] = $field;
			}
		}else{
			if($index){
				if(!isset($this->console['form'][$index])) return false;
				return $this->console['form'][$index]; //Return the $index Errors Array
			}else{
				return $this->console['form']; //Return the Full form Array
			}
		}
	}
	
	//Check for errors in the console
	function has_error($index = false){
		//Check for errors
		$index = $index ? $index : $this->log;
		$count = @count($this->console['errors'][$index]);
		if($count){
			$this->report("$count Error(s) Found!");
			return true;
		}else{
			$this->report("No Error Found!");
			return false;
		}
	}
	
	//Generates a unique comfirm hash
	function make_hash($uid,$hash=false){
	  $e_uid = $this->encode($uid);
    $e_uid_length = strlen($e_uid);
    $e_uid_length = str_pad($e_uid_length, 2,0, STR_PAD_LEFT);
    $e_uid_pos = rand(10,32-$e_uid_length-1);
    
    if(!$hash){
      $hash = md5(uniqid(rand(), true));
    }		
		//$code = substr($code, 0, $length);
    $code = $e_uid_pos . $e_uid_length;    
    $code .= substr($hash,0,$e_uid_pos - strlen($code));
    $code .= $e_uid;
    $code .= substr($hash,strlen($code));
    
    $this->confirm = $code;
    return $code;
	}
	
  //Validates a confirmation hash
  function check_hash($hash,$bypass=false){
    if(strlen($hash) != 32 || !preg_match("/^[0-9]{4}/",$hash)){
      $this->error("Confirmation hash is invalid");
      return;
    }
    
    $e_uid_pos = substr($hash,0,2);
    $e_uid_length = substr($hash,2,2);
    $e_uid = substr($hash,$e_uid_pos,$e_uid_length);
    
    $uid = $this->decode($e_uid);
    
    $sql = "SELECT * FROM {$this->opt['table_name']} WHERE user_id={$uid} AND confirmation='{$hash}'";
    
    //Bypass hash confirmation and get the user by partially matching its password
    if($bypass){
      preg_match("/^([0-9]{4})(.{2,".($e_uid_pos-4)."})(".$e_uid.")/",$hash, $exerpt);      
      $pass = $exerpt[2];
      $sql = "SELECT * FROM {$this->opt['table_name']} WHERE user_id={$uid} AND password LIKE '{$pass}%'";
    }
    
    $result = $this->getRow($sql);    
    
    if(!$result){
      $this->report("The user ID and the comfirmation hash did no matched");
      $this->error("Your identification could not be confirmed");
      return false;
    }
    if($this->signed and $this->id == $result['user_id']){
      $this->logout();
    }
    
    //Hash is valid import user's info to object
    $this->data = $result;
    $this->id = $result['user_id'];
    $this->username = $result['username'];
    $this->pass = $result['password'];
    
    $this->report("Hash successfuly validated");
    return true;
  }
  
  //Saves the confirmation hash in the database
  function save_hash(){
    if($this->confirm and $this->id){        
      $sql = "UPDATE users SET confirmation='{$this->confirm}', activated=0 WHERE user_id='{$this->id}'"; 
      if(!$this->check_sql_change($sql)){
        $this->error("Couldn't saved the confirmation code in the database.");
        return false;
      }else{
        $this->report("Confirmation hash has been saved");
      }
    }else{
      $this->report("Can't save Confirmation hash");
      return false;
    }
    return true;
  }
	//Test field in database for a value
	function check_field($field, $val, $err=false){
		$query = mysql_query("SELECT {$field} FROM {$this->opt['table_name']} WHERE {$field}='{$val}' ");
			if(mysql_num_rows($query) >= 1){
				if($err){
					$this->form_error($field,$err);
				}else{
					$this->form_error($field,"The $field $val exists in database");
				}
				$this->report("There was a match for $field = $val");
				return true;
			}else{
				$this->report("No Match for $field=$val");
				return false;
			}
	}
	
	//Executes SQL query and checks for success
	function check_sql($sql,$debug = false){
		$this->report("SQL: {$sql}"); //Log the SQL Query first
		if (!mysql_query($sql)){
			if($debug){
				$this->error(mysql_error());
			}
			return false;
		}else{
			return true;
		}
	}
	
	//Executes SQL query and returns an associate array of results
	function getRow($sql){
		$this->report("SQL: {$sql}"); //Log the SQL Query first
		$query = mysql_query($sql);
		if(mysql_error()){ $this->error(mysql_error()); }
		
		if(@mysql_num_rows($query)){
			while ($row = mysql_fetch_array($query, MYSQL_ASSOC)) {
				$rows[] = $row;
			}
		}else{
			$this->report("Query returned empty");
			return false;
		}
		return $rows[0];
	}
	
	//Executes SQL query and expects a change in database
	function check_sql_change($sql,$debug = false){
		$this->report("SQL: {$sql}"); //Log the SQL Query
		if (!mysql_query($sql)){
			if($debug){
				$this->error(mysql_error());
				return false; //die('Error: ' . mysql_error());
			}else{
				return false;
			}
		}else{
			$rows = mysql_affected_rows();
			if($rows > 0){
				//Good, Rows where affected
				$this->report("$rows row(s) where Affected");
				return true;
			}else{
				//Bad, No Rows where Affected
				$this->report("No row was Affected");
				return false;
			}
		}
	}
	
	//Validates All fields in ->tmp_data array
	private function validateAll(){
	  $info = $this->tmp_data;
		foreach($info as $field=>$val){
		  //Match double fields
      if(isset($info[$field.(2)])){
        if($val != $info[$field.(2)]){
          $this->form_error($field,"{$field} did not matched");
          //return false;
        }else{
          $this->report("{$field}s matched");
        }
      }
      
      $this->tmp_data[$field] = trim($val); //Trim white spaces at end and start
      
      //Validate field
			if(!isset($this->validations[$field])) continue;
			$opt = $this->validations[$field];
			$this->validate($field,$opt['limit'],$opt['regEx']);
		}
		return $this->has_error() ? false : true;		
	}
	
	//Validates field($name) in tmp_data
	private function validate($name,$limit,$regEx=false){
		$str = $this->tmp_data[$name];
		$l = explode("-",$limit);
		$min = intval($l[0]);
		$max = intval($l[1]);
		if(!$max || !$min){
			$this->error("Invalid second paramater for the $name validation");
			return false;
		}
		if(!$str){
			if(!isset($this->tmp_data[$name])){
				$this->error("missing index $name from the POST array");
			}
			if(strlen($str) == $min){
				$this->report("$name is blank and optional - skipped");
				return true;
			}			
			$this->form_error($name,"$name is required.");
			return false;
		}
		if(strlen($str) > $max){
			$this->form_error($name,"The $name is larger than $max characters.");
			return false;
		}
		if(strlen($str) < $min){
			$this->form_error($name,"The $name is too short. it should at least be $min characters long");
			return false;
		}
		if($regEx){
			preg_match_all($regEx,$str,$match);			
			if(count($match[0]) != 1){
				$this->form_error($name,"The $name \"{$str}\" is not valid");
				return false;
			}
		}
		
		$this->report("The $name is Valid");
		return true;
	}
	
	//NEW uFlex 0.41
	//Encoder
	function encode($d){
		$k=$this->encoder;preg_match_all("/[1-9][0-9]|[0-9]/",$d,$a);$n="";$o=count($k);foreach($a[0]as$i){if($i<$o){
			$n.=$k[$i];}else{$n.="1".$k[$i-$o];}}
		return $n;
	}
	//Decoder
	function decode($d){
		$k=$this->encoder;preg_match_all("/[1][a-zA-Z]|[2-9]|[a-zA-Z]|[0]/",$d,$a);$n="";$o=count($k);foreach($a[0]as$i){
			$f=preg_match("/1([a-zA-Z])/",$i,$v);if($f==true){	$i=$o+array_search($v[1],$k);}else{$i=array_search($i,$k);}$n.=$i;}
		return $n;
	}	
}

?>
