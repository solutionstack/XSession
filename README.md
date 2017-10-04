[![Build Status](https://travis-ci.org/solutionstack/XSession.svg?branch=master)](https://travis-ci.org/solutionstack/XSession)
[![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](https://github.com/dwyl/esta/issues)


# XSession
An OO-PHP class to easily handle sessions 

## Requirements
 - PHP 5.6+

## Usage
 Include or autoload the XSession.php file, then use as follows..
 
 ```php
     use solutionstack\XSession;

    //create A session
    $s = new XSession(string session_name, int session_lifetime_in_secs);
    
    //add session data as needed
    $s->put("foo", "bar"):
    $s->put("user_email", "mail@example.com");
    
 ```
 ## In other pages you need to use (check/resume) the session, just do..
 
 ```php
    //use the same session name used in starting the session
   $s = new XSession(string session_name);
   
  if($s->resume()) { //session was succesfully resumed
  
      //do stuff for authenticated users
      //also get previously set session values, or set new one
      $email = $s->get("user_email");
   }
 
 ```
 ## When done with the session, say in your logout routine,..
 
  ```php
    //use the same session name used in starting the session
   
   $s = new XSession(string session_name);
   $s->end();
   
 
 ```
 ### And Thats it.
 
 ### XSession uses ideas from the following project [SecureSessionHandler] 
 See  https://gist.github.com/eddmann/10262795    
 
 License
 ----

 MIT


 **Free Software, Hell Yeah!**
 
