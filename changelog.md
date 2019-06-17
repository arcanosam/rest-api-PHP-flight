##########
Change Log
##########

Dev. Changes
=============

- API Versioning implementation
    - index.php
        - adding an array of directories each an API version
            - allowing to organize the api versioning in folders
    - create folder api/v00 and api/v01
        - moving just the php modules which implement resources
    - route.php
        - change URLs to accept an api version as named parameter  
        - modifying the 'name' parameter to resource_name for legibility
    - Controller.php
        - adding api version as named parameter
        - concatenate this new parameter with the resource requested
            - create a unique namespace to load
    - UserLogin.php
        - defining namespace for API Versioning
        - adding all Global Namespaces used for this class
    - Config.ini 
        - JWT Token auth enabled
            - composer required to install Firebase JWT
    - jwtToken.php
        - Adding namespace Firebase\JWT\JWT
    - Test.php
        - fixed
               
- TODO
    - Fix all others resources
    - Allow use of Database Postgres
    - Implement JWT generation and validation

Version 2.0.0
=============

Release Date: Not Released

-  General Changes

   -  

Version 1.2.0
=============

Release Date: Apr 12, 2019

-  General Changes

   - Test api class controller is updated for more efficient testing
   - Code optimized with PHP CS fixer
   - Documentation updated

-  Core 

    - Server configuration file modified

        - Added new config parameter ``SERVER_CACHE_ENABLE_FLAG`` for enable / disable caching feature
        - Config parameter ``LOCAL_CACHE_FLAG`` is changed to ``FILE_CACHE_FLAG``

   - Config class is updated according to the configuration file changes

   - Base model class updated for cache related issues

   - Server cache feature is updated 

Bug fixes for 1.2.0
-------------------

-  Memcache-compression constant related issue fix
-  Base model class function bug fix

   
Version 1.1.0
=============

Release Date: Mar 31, 2019

-  General Changes

   - Server configuration file modified slightly

        - Client update location configuration param updated for different platform
        - ``DB_TIMEZONE`` is changed to ``SERVER_TIMEZONE``
        - Added ``TEST_USER_ID`` param for bypassing secutiry check for test user

   - Project application initialize file ``app/config/initialize.php`` is changed according to changed configuration

   - Base model class bug fix for member functions

   - System library classes modified

   - Test api class controller is updated for more accurate server testing

- 


Version 1.0.1
==============

Release Date: Mar 14, 2019

-  General Changes

   -  API testing console form is updated for dynamic Base URL change
   -  Documentation Updated
   -  System library [JwtToken] function name is changed
   -  HTTP status code is changed for erroneous API request


Bug fixes for 1.0.1
====================

-  Model

   - Base model function toJsonHash modified for unset indexed array values 


Version 1.0.0
================

Release Date: March 08, 2019

First publicly released version.
