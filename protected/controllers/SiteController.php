<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

    public function actionRunOnce()
    {        
        $this->render('runonce');
    }
    
    public function actionFourwinds()
    {
        $this->renderPartial('fourwinds');
    }

    public function actionBroadcast()
    {
		
        $this->noGuest();
        $params = array();
        
        if(isset($_REQUEST["deptid"])) {
            $dept = new DepartmentObj($_REQUEST["deptid"]);
            if($dept->loaded) {
                $_SESSION["CURRENT_DEPARTMENT"] = $_REQUEST["deptid"];
            }
        }
        else if(isset($_SESSION["CURRENT_DEPARTMENT"])) {
            $dept = new DepartmentObj($_SESSION["CURRENT_DEPARTMENT"]);
        }
        else {
            Yii::app()->user->setFlash("error","Department ID was not set or found. Please select a department app.");
            $this->redirect('apps');
            exit;
        }
        // For Facebook, refresh page if status code present.  
		
		var_dump($_REQUEST);
		var_dump('**********************************');
		var_dump($_SESSION);
		var_dump('**********************************');
		$params["social"] = array();
		/*if(isset($_SESSION["facebook"])) {
			$facebook = $_SESSION["facebook"];
		}
		else $facebook = new FacebookClass();*/
		$facebook = new FacebookClass();
		$params["facebook"] = $facebook;
		array_push($params["social"], $params["facebook"]);
		if(isset($_REQUEST["code"])) {
			//$this-> refresh();
			//$this->redirect($facebook->getLoginUrl());
			//$this->redirect(Yii::app()->createUrl('broadcast?deptid=' . $_REQUEST["deptid"]));
		}
        $params["dept"]             = $dept;
        $params["fourwinds"]        = new FourwindsClass();
        $params["twitter"]          = new TwitterClass();
        $params["googleplus"]       = new GooglePlusClass();
        array_push($params["social"],$params["twitter"],$params["googleplus"],$params["fourwinds"]);
        

        if(isset($_POST["form-submitted"])) {
            # Load the mediums
            $mediums = array();
            foreach($params["social"] as $media) {
                if(isset($_POST["onoffswitch-".$media->appname])) {
                    $mediums[] = $media->appname;
                }
            }
            # Create a new Broadcast
            $broadcast = new BroadcastObj();
            $broadcast->deptid      = $dept->deptid;
            $broadcast->message     = $_POST["broadcast-message"];
            $broadcast->media       = implode(",",$mediums);
            $broadcast->status      = 1;
            $broadcast->created_by  = Yii::app()->user->name;
            $broadcast->approved_by = Yii::app()->user->name;
            if(!$broadcast->save()) {
                Yii::app()->user->setFlash("error","Error saving Broadcast: ".$broadcast->get_error());
                goto renderBroadcast;
            }
            
            # Loop through each submitted media and make a message
            $messages = array();
            foreach($params["social"] as $media) {
                if(isset($_POST["onoffswitch-".$media->appname])) {
                    
                    $msgobj = new MessageObj();
                    $msgobj->broadcastid       = $broadcast->broadcastid;
                    $msgobj->method            = $media->appname;
                    $msgobj->to_useraccount    = $media->username;
                    $msgobj->username          = Yii::app()->user->name;
                    $msgobj->message           = $_POST["broadcast-message"];
                    $msgobj->status            = $broadcast->status;
                    $msgobj->approved_by       = $broadcast->approved_by;
                    
                    if(!$msgobj->conditions_met()) {
                        $msgobj->meet_conditions();
                    }
                    
                    if(!$msgobj->save()){
                        Yii::app()->user->setFlash("error","Error saving Broadcast message: ".$msgobj->get_error());
                        goto renderBroadcast;
                    }
                    else {
                        $messages[] = $msgobj;
                    }
                }
            }
            if(!Yii::app()->user->hasFlash("error")) {
                foreach($messages as $msgobj) {
                    switch($msgobj->method) {
                        case "facebook":    $params["facebook"]->broadcast($msgobj); break;
                        case "twitter":     $params["twitter"]->broadcast($msgobj); break;
                        case "googleplus":  $params["googleplus"]->broadcast($msgobj); break;
                    }
                }
                Yii::app()->user->setFlash("success","Successfully broadcasted your message!");
            }
        }
        
        renderBroadcast:
        if($dept->loaded) {
            $params["broadcasts"] = $dept->load_broadcasts();
        }
        $this->render('broadcast',$params);
        
    }
    
    public function actionDeleteApp()
    {
        $this->noGuest();
        $dept = new DepartmentObj($_REQUEST["dept"]);
        if(!$dept->loaded) {
            Yii::app()->user->setFlash("warning","This department application was already removed or cannot be found.");
            $this->redirect(Yii::app()->createUrl('apps'));
            exit;
        }
        
        $dept->delete();
        Yii::app()->user->setFlash("success","Successfully deleted department application.");
        $this->redirect(Yii::app()->createUrl('apps'));
        exit;
    }
    
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
	    $params = array();
        if(!Yii::app()->user->isGuest) {
            $facebook = new FacebookClass();
            if(isset($_GET["code"]) and $facebook->has_clear_status()) {
                $this->redirect("index");
                exit;
            }
            $params["facebook"]   = $facebook;
            $params["twitter"]    = new TwitterClass();
            $params["google"]     = new GooglePlusClass();
            $params["social"] = array($params["facebook"],$params["twitter"],$params["google"]);
        }
        
		$this->render('index',$params);
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}
    
    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        # Force log out
        if(!Yii::app()->user->isGuest) Yii::app()->user->logout();
        
        # Force SSL
        $this->makeSSL();
        
        # Initialize variables and Login model
        $params = array();
        $model = new LoginForm;
        $redirect = (isset($_REQUEST["redirect"])) ? $_REQUEST["redirect"] : "index";
        $error = "";
        
        # Collect user input data
        if (isset($_POST['username']) and isset($_POST["password"])) {
            $model->username = $_POST["username"];
            $model->password = $_POST["password"];
            # Validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {
                $this->redirect($redirect);
            } else {
                Yii::app()->user->setFlash("error","Incorrect username and password.");
            }
        }
        
        $params["model"] = $model;
        
        # Display the login form
        $this->render('login',$params);
    }

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
    
    public function actionRegister()
    {
        $this->noGuest();
        
        if(isset($_POST["form-submitted"])) {
            # Create the department app
            $dept = new DepartmentObj();
            $dept->deptcode     = $_POST['deptcode'];
            $dept->deptname     = $_POST['deptname'];
            $dept->apphost       = $_POST['apphost'];
            $dept->reguser      = Yii::app()->user->name;
            
            if($dept->save()) {
                # Give user access to the department app
                $access = new AccessObj();
                $access->deptid = $dept->deptid;
                $access->username = Yii::app()->user->name;
                if($access->save()) {
                    Yii::app()->user->setFlash("success","Registration part 1 complete.");
                    $this->redirect('apps');
                    exit;
                }
                else {
                    Yii::app()->user->setFlash("error","Could not give you access to department: ".$access->get_error());
                }
            } 
            else {
                Yii::app()->user->setFlash("error","Could not register department: ".$dept->get_error());
            }
        }
        
        $this->render("register");
    }
    
    public function actionApps()
    {
        $this->noGuest();
        
        $user = new UserObj(Yii::app()->user->name);
        $depts = Functions::get_user_apps($user->username);
        
        $params["user"] = $user;
        $params["depts"] = $depts;
        
        $this->render("apps",$params);
    }
	
	/*
	 * Helper function for saving the department data of a new or existing user. 
	 */
	public function insertUserDepartmentData($department, $identikey, $is_new) {
		// check for an existing dept name.
		$conn = Yii::app()->db;
        $query = "
            SELECT      deptid
            FROM        {{departments}}
            WHERE       deptname = :this_department;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":this_department",$department);
        
		$dept_id = (int)$command->queryScalar();
		$dept_exists = (bool)$dept_id;
		if(!$dept_exists) {
			// The user will not have an associated department.  
			return false;
		}
		//Delete any previously saved department for this user
		if(!$is_new) $this->deleteOldUserDepartmentData($department, $identikey);
		//Save the dept_access entry using identikey and dept_id
		$conn = Yii::app()->db;
        $query = "
            
            INSERT INTO		{{dept_access}} (deptid, username)
            VALUES 			(:dept_id, :identikey);
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":dept_id",$dept_id);
        $command->bindParam(":identikey",$identikey);
        $result = $command->execute();
		
		return true;
	}
	/*
	 * Helper function for deleting old user department data.  After returning, a new dept_access entry is created in the caller function insertUserDepartmentData()
	 */
	public function deleteOldUserDepartmentData($department, $identikey) {
		$conn = Yii::app()->db;
        $query = "
            
            DELETE FROM		{{dept_access}}
            WHERE 			username = :identikey;
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":identikey",$identikey);
        $result = $command->execute();
	}
	/*
	 * Helper function for getting all current users (for use in the manage users table).
	 */
	public function getUsers() {
		$user = new UserObj(Yii::app()->user->name);
        $users = $user->getAllUsers();
		if(!$users) {
			Yii::app()->user->setFlash('error','You do not have permission to manage users.  Redirected to home page.');	
			$this->redirect(Yii::app()->homeUrl);
		}
		foreach ($users as &$u) {
			$uObj = new UserObj($u["username"]);
			$u["department"] = $uObj->getDepartmentName();
		}
		
		return $users;
	}
	
	public function actionManageUsers()
    {
		$this->noGuest();
		
		$users = $this->getUsers();
		$params = array(
			'users'=>$users,
			'identikey'=>'',
			'fullname'=>'',
			'email'=>'',
			'permission'=> 'null'
		);
		
		// Check if new user form was submitted.
		if(!empty($_POST)) {
			// add input field values to params
			$permission = (!isset($_POST['permission'])) ? 'null' : $_POST['permission'];
			$params = array(
				'users'=>$users,
				'identikey'=>$_POST['identikey'],
				'fullname'=>$_POST['fullname'],
				'email'=>$_POST['email'],
				'permission'=> $permission
			);
			if(empty($_POST['identikey']) || empty($_POST['fullname']) ||
				empty($_POST['email']) || !isset($_POST['permission'])) {
				Yii::app()->user->setFlash('error','Please make sure all required fields are entered before submitting.');	
			}
			// Check for valid email address
			elseif(!preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/', $_POST['email'])) {
				Yii::app()->user->setFlash('error','The email address you entered is not valid.  Please enter a valid email address.');	
			}
			// Check for identikey length
			elseif(strlen($_POST['identikey']) > 8) {
				Yii::app()->user->setFlash('error','Identikey Usernames must be no longer than 8 characters.');	
			}
			else {
				$existing_user = new UserObj($_POST["identikey"]);
				// If the user is not loaded it's a new user.
				if(!$existing_user->loaded) {
					//save new user!
					
					/*if(!empty($_POST['department'])) {
						if(!$this->insertUserDepartmentData($_POST["department"], $_POST["identikey"], true)) {
							$params["department"] = "No Department";
							Yii::app()->user->setFlash('warning','The department you entered was not found in the database, and was therefore not saved for this user.');	
						}
					}*/
					$params["department"] = "No Department";
					$newuser = new UserObj();
					$newuser->username = $_POST["identikey"];
					$newuser->email = $_POST["email"];
					$newuser->name = $_POST["fullname"];
					$newuser->permission = (int)$_POST["permission"];
					
					if($newuser->save()) {
						Yii::app()->user->setFlash('success','New user added to the database.');
						// Refresh the user list table
						$users = $this->getUsers();		
						$params['users'] = $users;
					}
					else Yii::app()->user->setFlash('error','There was an error saving to the database.'); 
				}
				// Update existing user.
				else {
					/*if(!empty($_POST['department'])) {
						if(!$this->insertUserDepartmentData($_POST["department"], $_POST["identikey"], false)) {
							$params["department"] = $existing_user->getDepartmentName();
							Yii::app()->user->setFlash('warning','The department you entered was not found in the database, and was therefore not saved for this user.');	
						}
					}*/
					$params["department"] = $existing_user->getDepartmentName(); 					
					$existing_user->username = $_POST["identikey"];
					$existing_user->email = $_POST["email"];
					$existing_user->name = $_POST["fullname"];
					$existing_user->permission = (int)$_POST["permission"];

					if($existing_user->save()) {
						Yii::app()->user->setFlash('success','Existing user info was updated in the database.');
						// Refresh the user list table
						$users = $this->getUsers();		
						$params['users'] = $users;
					}
					else Yii::app()->user->setFlash('error','There was an error updating user info in the database.'); 
				}
			}
		}
		
        $this->render("manage_users",$params);
    }
	public function actionTestFacebook() {
		$this->render("test_facebook");
	}
	
	public function actionMassEmail() {
		$dept = new DepartmentObj(5);
		$user = new UserObj(Yii::app()->user->name);
		$user_info = $user->getUserInfo()[0];
		$user_address = $user_info["email"];
		$user_full_name = $user_info["name"];
		// for are_hidden, pass a string '0' or '1' for false and true, to be casted to boolean later.
		$params = array(
			'data' => array(		
				'appkey' => '5F5C-EA02-368B',
				'user' => array(
					'username' => $user_info["username"],
					'user_fullname' => $user_full_name,
					'user_address' => $user_address
				),
				'metadata' => array(
					'receivers' => array(
						'thomas.dressler@colorado.edu',
						'thomas.dressler1@gmail.com',
						'dresslet@yahoo.com'
					),
					'subject' => 'CU Broadcast Mail',
					'are_hidden' => '0'
				),
				// 'message' will be added using the form and js
			),	
		);

		
		$this->render("mass_email",$params);
	}
	
    public function action_get_current_user_info()
	{
		$this->noGuest();
		
		if(!isset($_REQUEST["user_identikey"])) {
			return print "Sorry, Identikey was not successfully passed in.";
		}
		$user = new UserObj($_REQUEST["user_identikey"]);
		$dept = $user-> getDepartmentName();
		
		$return = get_object_vars($user);
		$return["department"] = $dept;
		return print json_encode($return);
	}
	
	public function action_lookup_user_adauth()
	{
		$this->noGuest();
		
		$ad = new ADAuth;

		$user_data = (object)$ad->lookup_user($_REQUEST["user_identikey"])[0];
		$user_data = get_object_vars($user_data);
		// look up in database
		$user = new UserObj($_REQUEST["user_identikey"]);
		if($user->loaded) {
			// Get permission level
			$user_data["permission_level"] = $user->permission;
			$user_data["is_existing"] = 'true';
		}
		
		return print json_encode($user_data);
	}
	
	public function action_delete_user_from_database() 
	{
		$this->noGuest();
		$user = new UserObj($_REQUEST["identikey"]);
		$user->delete();
		Yii::app()->user->setFlash("success","Successfully deleted the user: ".$_REQUEST["identikey"]);
        return true;
	}
	
    public function actionOauth2Callback()
    {
        $gplus = new GooglePlusClass();
        $gplus->create_permanent_access();
        
        Yii::app()->user->setFlash('success','Successfully signed into Google Plus.');
        $this->redirect("index");
        exit;
    }
    
    public function actionTwitterCallback()
    {
        $twitter = new TwitterClass();
        if($twitter->create_permanent_access()){
            Yii::app()->user->setFlash('success','Successfully signed into Twitter.');
        }
        
        $this->redirect("index");
        exit;
    }
    
    /**
     * AJAX FUNCTIONS BELOW
     */
     

    public function action_approve()
    {
        $this->noGuest();
        
        if(!isset($_POST["broadcastid"])) {
            return false;
        }
        
        $broadcast = new BroadcastObj($_POST["broadcastid"]);
        if(!$broadcast->loaded or $broadcast->status == 1){
            return false;
        }
        
        $broadcast->status = 1;
        if(!$broadcast->save()) {
            return false;
        }
        
        # The magic happens here
        $social = $broadcast->load_media_messages();
        foreach($social as $msgobj) {
            if(!$msgobj->loaded) continue;
            
            $msgobj->status = 1;
            $msgobj->approved_by = Yii::app()->user->name;
            if(!$msgobj->save()) {
                Yii::app()->user->setFlash("error","Error saving message object: ".$msgobj->get_error());
                return false;
            }
            switch($msgobj->method) {
                case "facebook":
                    $facebook = new FacebookClass();
                    $facebook->broadcast($msgobj); 
                break;
                case "twitter":
                    $twitter = new TwitterClass();
                    $twitter->broadcast($msgobj); 
                break;
                case "googleplus":
                    $google = new GooglePlusClass();
                    $google->broadcast($msgobj); 
                break;
                case "fourwinds":
                break;
                default:
                    Yii::app()->user->setFlash("warning-".$msgobj->method,"Could not send message of method type: ".$msgobj->method);
                    return false;
                break;
            }
            $msgobj->sent = 1;
            $msgobj->save();
        }
        
        # We're done, return success
        Yii::app()->user->setFlash("success","Successfully broadcasted your message!");
        return true;
    }

    public function action_register()
    {
        $this->noGuest();
        
        $deptobj = new DepartmentObj($_POST["deptid"]);
        if(!$deptobj->loaded) {
            return false;
        }
        
        $deptobj->regstatus = 1;
        if(!$deptobj->save()) {
            return false;
        }
        
        return true;
    }

    public function action_facebook_post()
    {
        $this->ajaxNoGuest();
    }
    
    public function action_disconnect()
    {
        $method = $_REQUEST["method"];
        if($method == "facebook") {
            $facebook = new FacebookClass();
            $facebook->disconnect();
            Yii::app()->user->setFlash("success","Disconnected from Facebook.");
        }
        else if($method == "twitter") {
            $twitter = new TwitterClass();
            $twitter->disconnect();
            Yii::app()->user->setFlash("success","Disconnected from Twitter.");
        }
        else if($method == "googleplus") {
            $google = new GooglePlusClass();
            $google->disconnect();
            Yii::app()->user->setFlash("success","Disconnected from Google Plus.");
        }
        else {
            Yii::app()->user->setFlash("warning","Could not disconnect from unknown method: ".$method);
        }
    }
    public function action_add_flash()
	{
			if($_REQUEST["type"] == "error") {
				Yii::app()->user->setFlash("error",$_REQUEST["message"]);
			}
			else Yii::app()->user->setFlash("success",$_REQUEST["message"]);
			return true;
	}
	
	
    /**
     * Important function for enforcing SSL on a page.
     * Mainly used for the Login page.
     */
    private function makeSSL()
    {
        if($_SERVER['SERVER_PORT'] != 443) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            exit();
        }
    }

    /**
     * Forces page to not be SSL.
     */
    private function makeNonSSL()
    {
        if($_SERVER['SERVER_PORT'] == 443) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            exit();
        }
    }
    
    /**
     * 
     */
    private function ajaxNoGuest()
    {
        if(Yii::app()->user->isGuest) {
            die(false);
        }
    }
    
    /**
     * Checks to see if a user is logged into the application.
     * If not then it will redirect to the login page with a warning.
     */
    private function noGuest()
    {
        if(Yii::app()->user->isGuest) {
            Yii::app()->user->setFlash("warning","You must be signed in to access this page.");
            $this->redirect(Yii::app()->createUrl('login')."?redirect=".urlencode("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
            exit;
        }
    }
}