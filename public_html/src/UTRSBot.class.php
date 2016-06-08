<?php

require_once(__DIR__ . "/../includes/Peachy/Init.php");
require_once(__DIR__ . "/../src/unblocklib.php");
require_once(__DIR__ . "/../src/messages.php");

class UTRSBot
{
    
    private $objPeachy;
    private $taskname = "UTRS";
    private $config = "UTRSBot";
    private $userTemplate = "UTRS-unblock-user";
    private $adminTemplate = "UTRS-unblock-admin";
    private $oppTemplate = "UTRS-OPP";
    private $oppPage = "Wikipedia:WikiProject_on_open_proxies/Requests";
    private $userTempPtrn = "/\{\{UTRS-unblock-user\|(\d{0,5})?\|([a-zA-Z]{1,4} [0-9]{1,2}, [0-9]{1,4} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})?\}\}/";
    private $enabled;
    
    public function __construct()
    {
        global $CONFIG;
        
        $this->objPeachy = Peachy::newWiki($this->config);
        
        $this->objPeachy->set_taskname($this->taskname);
        
        $this->enabled = $CONFIG['bot']['enabled'];
        
        
    }
    
    public function notifyUser($username, $templateVars)
    {
        
        if ($this->enabled == "true") {
            $user = $this->objPeachy->initUser($username);
            
            $template = $this->objPeachy->initPage("Template:" . $this->userTemplate);
            
            $this->objPeachy->set_runpage("User:UTRSBot/notifyUser");
            
            if ($user->exists() && $template->get_exists()) {
                
                $page = $this->objPeachy->initPage("User_talk:" . $username);
                
                $content = "\n{{" . $this->userTemplate;
                
                foreach ($templateVars as $var) {
                    
                    $content .= "|" . $var;
                    
                }
                
                $content .= "}}--~~~~";
                
                $page->append($content, "User has submitted an unblock appeal on UTRS", false, true);
                
            }
        }
        
    }
    
    public function notifyAdmin($username, $templateVars)
    {
        
        if ($this->enabled == "true") {
            $user = $this->objPeachy->initUser($username);
            
            //Get Blocking Admin from API
            $blockinfo = $user->get_blockinfo();
            $admin     = $this->objPeachy->initUser($blockinfo['by']);
            $template  = $this->objPeachy->initPage("Template:" . $this->adminTemplate);
            
            $this->objPeachy->set_runpage("User:UTRSBot/notifyAdmin");
            
            if ($admin->exists() && $template->get_exists()) {
                
                $page = $this->objPeachy->initPage("User_talk:" . $blockinfo['by']);
                
                $content = "\n=={{utrs|" . $templateVars[0] . "}}==\n{{" . $this->adminTemplate;
                
                foreach ($templateVars as $var) {
                    
                    $content .= "|" . $var;
                    
                }
                
                $content .= "}}--~~~~";
                
                $page->append($content, "Notifing blocking admin for [[User:" . $username . "|" . $username . "]]'s UTRS Appeal #" . $templateVars[0], false, true);
                
            }
        }
    }
    
    public function notifyOPP($ip, $templateVars)
    {
        
        if ($this->enabled == "true") {
            $template = $this->objPeachy->initPage("Template:" . $this->oppTemplate);
            
            $this->objPeachy->set_runpage("User:UTRSBot/notifyOPP");
            
            if ($template->get_exists()) {
                
                $page = $this->objPeachy->initPage($this->oppPage);
                
                $content = "\n{{subst:" . $this->oppTemplate;
                
                foreach ($templateVars as $var) {
                    
                    $content .= "|" . $var;
                    
                }
                
                $content .= "}}";
                
                $page->append($content, "Proxy check requested for UTRS", false, true);
            }
        }
    }
    
    public function closeUserTemplate()
    {
        
        if ($this->enabled == "true") {
            $this->objPeachy->set_runpage("User:UTRSBot/closeAppeals");
            
            $result = $this->objPeachy->apiQuery(array(
                "action" => "query",
                "list" => "categorymembers",
                "cmtitle" => "Category:Requests for unblock on UTRS"
            ));
            
            //Filter out other nonsense
            $result = $result["query"]["categorymembers"];
            
            foreach ($result as $userPage) {
                
                //Get vars $page, $user
                $page = $this->objPeachy->initPage($userPage["title"]);
                $user = explode(":", $userPage["title"]);
                $user = $user[1];
                
                echo "Reviewing " . $user . "\n";
                
                //Get Page text
                $text = $page->get_text();
                
                //Get specific template
                $matches = array();
                $found   = preg_match($this->userTempPtrn, $text, $matches);
                
                if ($found === 1) {
                    
                    echo "Open UTRS Unblock template found" . "\n";
                    
                    //Find out if ticket is still open
                    $db = connectToDB();
                    
                    //SQL injection protection
                    if (is_numeric($matches[1])) {
                        $id = $matches[1];
                    } else {
                        //Leave loop, template appears to be bad
                        $this->botError("Appeal number invalid for " . $user);
                        break;
                    }
                    
                    echo "Appeal ID: " . $id . "\n";
                    
                    $query = $db->prepare("SELECT status FROM appeal WHERE appealID = :appealid;");
                    
                    $query->execute(array(
                        ":appealid" => $id
                    ));
                    
                    $row = $query->fetch(PDO::FETCH_ASSOC);
                    print_r($db->errorInfo());
                    
                    echo "Appeal status: " . $row["status"] . "\n";
                    
                    if ($row["status"] == "CLOSED") {
                        
                        echo "Appeal is closed" . "\n";
                        
                        //Create new template 
                        $new_template = "{{UTRS-unblock-user|" . $matches[1] . "|" . $matches[2] . "|closed}}";
                        echo "Old template: " . $matches[0] . "\n";
                        echo "New template: " . $new_template . "\n";
                        
                        //Replace template
                        $text = str_replace($matches[0], $new_template, $text);
                        
                        //Edit Page
                        $page->edit($text, "This appeal is now closed", false, true);
                        
                        echo "Page saved" . "\n\n";
                        
                    } elseif ($row === FALSE) {
                        $this->botError("Appeal number not found for " . $id);
                    } else {
                        echo "Appeal is still open, moving on..." . "\n\n";
                    }
                }
            }
        }
    }
    
    private function botError($error)
    {
        echo "ERROR - Exception thrown!\n";
        echo $error . "\n";
        echo "Script incomplete.\n";
        
        $body = "O Great Creators,\n\n";
        $body .= "The on-wiki UTRSBot has encountered an error. The error message received was:\n";
        $body .= $error . "\n";
        $body .= "Please check the database to resolve this issue and ensure that private data is removed on schedule.\n\n";
        $body .= "Thanks,\nUTRS";
        $subject = "URGENT: UTRSBot failure";
        
        mail('utrs-developers@googlegroups.com', $subject, $body);
    }
}
