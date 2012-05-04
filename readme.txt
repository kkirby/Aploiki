Aploiki
-- Version 1 [work in progress]
-- Created by Kyle Kirby of Optimal Connection

+----------------------------------------------------------+
| This is a very simple documentation... Sorry             |
+----------------------------------------------------------+
| Aploiki is distributed under the CC-GNU GPL license.     |
| You may NOT remove or modify any 'powered by' or         |
| copyright lines in the code.                             |
| http://creativecommons.org/licenses/GPL/2.0/             |
+----------------------------------------------------------+

Be sure to check the change logs below after reading the manual.. Some things
have changed since it was written.

-- Changes for R1.4 --
mysql added query
added config error messages
added option to name module MODULE_NAME...

Templating
    - basics
        - menu now has to be set in base.phtml
        - themes
        
    Now when the base.php file isn't found, when it should be found, it will only alert the message
    to an admin.
    
    You no longer have to place the <style> </style> tags around @css in your base.phtml file. They will
    be added automatically if they are no provided already. If the @css gets replaced with nothing (just a
    blank result), and there is nothing else inside your <style></style> tags: then your <style></style> tags
    will be removed from your document automatically.
    
            
    -THEMES
        Themes will be placed in the /themes/ folder. Each theme will have its own folder inside the
        /themes/ folder. Inside of this folder, there MUST be an info.sid file. Inside this file
        should contain information about the theme in the following format:
            <title>Modern Blue</title>
            <description>A basic theme, take it or leave it.</description>
            <html>
            <link rel="stylesheet" href="@here/style.css" type="text/css">
            </html>
        The theme name goes inside the <title></title> tags. The theme description goes
        inside the <description></description> tags.
        
        You are provided with a mixture of options for a theme. One option is to do a plain 
        stylesheet theme for your site, that changes styles inside your base.phtml/php file.
        To do this, put @theme_html inside your base.phtml file. And then in your info.sid
        file, put the HTML that you want @theme_html to be replaced with inside the <html></html>
        tags. To have the HTML reference to the theme, simply place @here where you want the link
        to be placed.
        
        To do a complete redesign of the base.phtml per theme, simply put a base.phtml file inside
        your theme folder. If you want a dynamic theme, you can also put a base.php file inside your
        theme folder, and put !import_dynamic inside the base.phtml file. Simple!
        
        

Menu
    When setting up your menu, you now have a new option for your menu items array.
    This new option allows you to define wether or not a menu item is set to active
    for a specifc url.. I think examples are awesome, so I'm just going to provide example.
    
    in the menu item array:
        array('title'=>'My Page','page'=>'some_page','parameters'=>array('param1','param2'),'strict'=>true);
    
    The menu item will only be set to active if the person is viewing the page:
        mysite.com/some_page/param1/param2/
    so if there are extra, or any less params then the menu item won't be set to active.
    
    Not setting the index 'strict' at all will make the menu item active for the view some_page and
    any parameters that follow.
        


Modules
    In R1.3, if you tried to goto a page inside a module and it didn't have a function assocaited with it,
    then it wouldn't show a page (I think, I can't remember what it id).. But now AEYNIAS will revert to the
    main function in a module if the view isn't found. Fun fun!

Added a menu class
    To add items to the menu see the following example:
        To add one item: $_AEYNIAS['class']['menu']->add_item(
                                                            array(
                                                                'title'=>'New Item',
                                                                'page'='some_page'
                                                                )
                                                            );
        To add more than one item: $_AEYNIAS['class']['menu']->add_item(
                                                                array(
                                                                    array(
                                                                        'title'=>'New Item',
                                                                        'page'='some_page'
                                                                    ),
                                                                    array(
                                                                        'title'=>'New Item 2',
                                                                        'page'='some_page_2'
                                                                    )
                                                                )
                                                            );

    To remove items:
        There are two main removal options, a strict removal, or a loose removal. A
        strict removal will only remove an item if the function finds the EXACT
        array supplied in the menu array. A loose removal will match any of the
        items in an array.
        
            Strict removal:
                $_AEYNIAS['class']['menu']->remove_item(
                                                    array(
                                                            'title'=>'My specific find',
                                                            'page'=>'Only this item!!'
                                                        )
                                                        ,true                // States it a Strict find and remove.
                                                    );
                            
                                                    This will only remove a menu item, if and only if TITLE and PAGE are equal
                                                    to that of a menu item in your curreny menu array, or any item you add later
                                                    on.

            Loose removal:
                $_AEYNIAS['class']['menu']->remove_item(
                                                    array(
                                                            'title'=>'Any page',
                                                            'page'=>'stuff'
                                                        )
                                                    );

                                                    This will remove the first item it finds where either the title is 'Any Page' or
                                                    the page is 'stuff'.
            
            Multiple removals at once:
                $_AEYNIAS['class']['menu']->remove_item(
                                                    array(
                                                        array(
                                                                'title'=>'Any page',
                                                                'page'=>'stuff'
                                                            ),
                                                        array(
                                                                'title'=>'stuffasdfa3',
                                                            )
                                                    );              
    Have fun with this.. ha


MySQL Class Changes:
    Now when ever you use insert_row it will return either FALSE or the ID of the new item inserted.
    you should use === to check to see if it returns false.
    
Form Class Changes
    I have inverted the way insert_select works. Its more logical now. When you supply the array, 
    the array index is now considered the value of the select item. For example, this array:
        array('value1'=>'text1','value2'=>'text2');
    Would make this select:
        <select>
            <option value="value1">text1</option>
            <option value="value2">text2</option>
        </select>
    
    See, makes more sense :). It was the other way around.
-- Changes for R1.3 --
Friday September 12, 2008 3:30PM
Very excited about this release! Tons of new features that makes AEYNIAS a GREAT framework.
Hope you enjoy these features as much as I enjoyed adding them in.

$doc_url and $doc_root are no longer variables.. Infact all Variables that were once
valid are not. Eveything is now stored in an array.. The variable for that array is
$_AEYNIAS. To get the doc_url and doc_root that you have configed you can call:
$_AEYNIAS['config']['doc_url'] for the doc_url, and $_AEYNIAS['config']['doc_root'].
@doc_url is still valid for static pages. To see the list of all paramaters, add: 
print_r($_AEYNIAS); to the end of index.php (before the ?> of course..).

No longer are "views" called in the URL via mysite.com/views/my_view/, now all you have
to do is mysite.com/my_view/, makes for a better, and cleaner file structure.

The $action and $the_id have been depreciated for being to specific. The array is now set
in place for them: $_AEYNIAS['parameters']['extras']. You can have up to as many paramaters in
the URL as you want! Each one is added onto the array $_AEYNIAS['paramaters']['extras']. For example,
this URL: mysite.com/aeynias//blogs/kyles_blog/article1/, would produce the array:
$_AEYNIAS['parameters']['extras'] = array(
                                        'kyles_blog',
                                        'article1'
                                    );
This way nothing is specifc, and can work for anyone. Same principal applies for actions :).

$_AEYNIAS['parameters']['admin'] will be set to TRUE if the user is visiting an admin page.
$_AEYNIAS['parameters']['action'] will be set to TRUE if the user is visiting an action.

To see if a user is logged in as an admin, call $_AEYNIAS['authentication']['user']['logged_in'] for
users and $_AEYNIAS['authentication']['admin']['logged_in'], one or both will equal true or false..
(Admins are counted twice, once as a user and once as an admin.)


Rewrote the templating class.

    Rewriting the template class made a lot of new features possible and easy to incoporoate!

    Made the error message for "no action found" more elegant by incorportating it
    into the template.

    If a page is set to dynamic, but the dynamic counter-part cannot be found
    it will revert back to the static page, with an error saying the dynamic page
    cannot be found.

    Included #eval{} as a static page function. This evaluates the script inside of the brackets..
        Example: #eval{return 4+4}
        Replaces itself with 8.

        Example: #eval{$text = 'Ohai!';if($text == 'Ohai!'){return 'Hi!!';}else{return ':( bye';}}
        Replaces itself with Hi!!.

            Notes on #eval
                - Be _VERYYY_ careful with this new addition to AEYNIAS.
                - Quotations do NOT work, use ' ' instead.
            
    You can now make pages that don't pertain to the overall template, meaning,
    you can have a page where the base template is not applied to the text.
    To do this you can either call in the static page: !dont_use_template, or in the
    dynamic page call the function $this->dont_use_template(). If you want to feel awesome
    it would work to do #eval{$this->dont_use_template();} inside the static page. You
    can still define $page_text or just use echo, its up to you. If you plan to later
    add it to the template, use $page_text.. That way it will always look right.
    

Menu
    Added a new array key to the menu items array, it is called parameteres. It is used
    to set parameters to the page..
    
    The Module Key in the array has been depreciated for being inaccurate.. It has been changed
    to Page.
    
        Example: $_AEYNIAS['config']['menu']['items'][] = array(
                                                            'title'=>'Home',
                                                            'page'=>'main',
                                                            'parameteres'=>
                                                                array(
                                                                    'action',
                                                                    'show_blogs',
                                                                    'kyles_blog'
                                                                )
                                                            );
        That would genereate a menu item with the URL: mysite.com/main/action/show_blog/kyles_blog/
    Because of this new addition, if a user is on the same page, but is viewing a different subpage
    (Parameteres, /action/show_blog/) than what the menu item directs to, then the menu item will not be
    set to active. It will only be set to active if the user is viewing the same page as the menu item,
    and all the parameteres are the same.
    
    You CAN change the menu at any time in ANY of your dynamic PHP pages. To add an item
    do something like this:
        $_AEYNIAS['config']['menu']['items'][] = array('title'=>'My New Item','module'=>'new_page');
        
    You need at least the page or action to be set in the array. The menu title will be derived
    from the module text if it is not set in the array.
    
    Menu Array Key Quick Reference
        Title       =>  The Menu's text to display.
        Action      =>  The action to goto. Example: logout, takes you to mysite.com/action/logout/, use this instead of module if you want an action..
        Page        =>  The Page to be sent to, the page in the url. Example: login, makes the url be mysite.com/login/
        Parameters  =>  Paramaters to send to the module. Example: array('param1','param2'), makes the url be like mysite.com/login/param1/param2/
        logged_in   =>  0 = Only display if user is logged out.
                        1 = Only display if user is logged in.
                        2 = Only display if user is an admin.
                        If only admins can see the menu item, the menu item will direct to an admin module instead of a normal module.
    
Login Page
    When ever a page requires a person to be logged in, it will redirect the person to the
    login page (this is nothing new), but before if a person navigated away from the login page
    with out logging in and then return later to login, it would still redirect them to the page
    they were trying to access earler. This has been fixed, and the redirect url expires
    after three minutes. If the user doesn't attempt to login within three minutes
    after the redirection to the login page, then the next time they login it will act normally,
    and just show "Successfully Logged In.".
    

Base.php
    Dynamic pages are now up for the use with base.phtml, just put inside base.phtml "!import_dynamic",
    it will then call on the base.php file inside of /pages/. If its not there, AEYNIAS will simply
    revert back to the static page and show an error.
    These varibles have been populated for use with base.php:
    $page_title = The Viewing Page's title.
    $page_css   = The Viewing Page's custom CSS.
    $menu       = The menu that has been parsed, with your parameters (inside config.php). You can of course
                make your own if you feel up to programming it in..
    $_AEYNIAS has been globalized.

Modules
    Something I call modules have been added into the Framework. The purpose of Modules are for group of pages
    to be combined into the system, and have already been programmed.. A real life example of a module is
    someone can make a module of a blog system. All you have to do is download their module and put it inside of
    the modules folder. Now you have an entire blog system in your site with no programming at all!
    
    AEYNIAS first looks for a static page, and if it cant find that static page it then goes through all the
    modules you have installed and checks to see if there is a corresponding module. If the requested page
    matches a module then AEYNIAS will go on with displaying the module. If AEYNIAS cannot find a
    matching module, then AEYNIAS will display "Page not found.".
    
    To make a module, first make a new directoy inside of /path_to_aeynias/modules/.. The directory name
    will be what aeynias will respond to. Inside of your new folder make a file called main.php. This is
    where you will place the code exampled below.
    
    A module is simply a class with each page in its own function. To start off a module use this:
    
        class module_addon extends module {
            
        }
        
    The function main() is called when no paremeters are passed to the module.
        
    For each page, simple make a new function, called page_yourpage(), the page_ prefix is required. To assign a paremeter
    to a page simply call $this->associate_parameters(array('pages','two'),'function_two'); aeynias will then associcate
    the paremeters in the URL /pages/two/ to the function: page_function_two(). It is suggested you call this funciton when
    the class starts. eg.
    
        class module_addon extends module {
            function module_addon(){
                $this->associate_parameters(array('pages','two'),'function_two');
            }
        }
    
    It is also suggested that you call $this->me() when refrencing itself in a URL. A user should have the option
    to change the URL to the module by simply changing the folder name.
    
    To change the CSS/Page Title/Page Text do set these variables:
        $this->page_title = The Page Title.
        $this->page_css = The Page CSS.
        $this->page_text = The Page Text.
        
    Additonal Functions:
        $this->is_admin();                      When called, will check to see if the user is an admin. Will direct them
                                                to the login page if not.
                                                
        $this->is_logged_in();                  When called will check to see if a user is logged in, if not it will take
                                                them to the login page.
                                                
    
    Here is a quick example of how a module works:
        Folder structure /path_to_aeynias/modules/my_first_module/
        File: /path_to_aeynias/modules/my_first_module/main.php
            
            class module_addon extends module {
                function module_addon(){
                    global $_AEYNIAS;
                    $this->associate_parameters(array('page','one'),'one');
                    $this->associate_parameters(array('page','one'),'two');

                    $_AEYNIAS['config']['menu']['items'] = array_merge($_AEYNIAS['config']['menu']['items'],
                        array(
                            array(
                                'page' => $this->me(),
                                'title' => 'Main Page'
                            ),
                            array(
                                'page'=> $this->me(),
                                'title' => 'Page One',
                                'parameters' => array('page','one')
                            ),
                            array(
                                'page' => $this->me(),
                                'title' => 'Page Two',
                                'parameters' => array('page','two')
                            )
                        )
                    );
                }

                function main(){
                    $this->page_title = 'Main';
                    $this->page_text = 'This is the first page :)';
                }

                function page_one(){
                    $this->page_title = 'Page One';
                    $this->page_text = 'This is page One!!';
                }

                function page_two(){
                    $this->page_title = 'Page Two';
                    $this->page_text = 'This is page Two!!';
                }
            }
        
        This will make AEYNIAS show the module if the page is my_first_module. Just try it out and experiment :).
        
So thats it for R.3, I hope this helps everyone out there!

##########################
# Things to Come in R1.4 #
##########################
    - Rewriting Form Class (not sure all of what the entails, probably a lot of new features)
    - Themes via CSS
    - A Menu Class to make modifying the menu easier!
    - Probably more, can't think of anything else right now.

-- Changes for R1.2.1 --
 Fixed an issue when a menu item would not be set to active if it didn't require a person to be logged in.

-- Changes for R1.2 --
Released August 18, 2008 10PM EDT.

Added Admin pages, the file structure has been added:
pages/admin/dynmaic_views/
pages/admin/static_views/

Same principles apply for these as normal pages.. Theres no need to include !logged_in inside the
static page of the admin page, it is automatically assumed...

To navigate to the admin page, point your browser to mysite.com/admin/my_admin_page/
In the SQL, a new column has been added called 'admin', if set to 1 then the user is an
admin and has permission to goto an admin page..

Added in user options.

Fixed a lot of problems with the mysql class, made it more secure.


Added in a simple blog example, please note this is using an older version of AEYNIAS.. Its sorta a mixture of Revision 1.2 and 1.1... Yeah..

-- Changes for R1.1 --

Added actions to be apart of the main site, now you don't have to automatically add the mysql class
and others.

Redeveloped the MySQL class, all functions work the same.. Now if a function returns false, you can get the
error using $_AEYNIAS['class']['mysql']->error (this is most likely only be set becasue of a normal mysql_error()), if there
is anyhting wrong with the way you used the function, the script will exit with an error displayed.

update_sql now is update_row
insert_sql is now insert_row

added delete_row, you can assume how to use it. (delete_row($table,$where))

also, for quick_grab, to manually set it to output a multidimensional array, just do this:
quick_grab($table,$what,$where,$extra,'true');
    Use null for things you don't want set.. It is automatically assumed for each parameter.
The 'true' on the fifth parameter tells it to output a mutlidemaosdnfad array no matter what.
normally if theres only one row, then it just outputs an array of the row data.. Setting
this value to 'true' with make one row show up like this $array[0] = array('key'=>'value');


-------------------------

So basically, this is sorta a framework and an orginzation method with the ability to expand on.
This can be easily expanded on to make a fully functional Content Managment System.. The first, 
unrelesed version of AEYNIAS was actually a CMS with a huge template system.. Which pretty much
killed its speed.. ANYWHOO!!!

This guide is outdated, be sure to read the changes up above.. From R1.1 to Today.

Aeynias has five basic parts to it
1) You have the main template.
2) The views, or pages.
3) The actions that have no outputted text.
4) The MySQL Database.
5) And simple User authenication.


Main Template:
For the main template to work, you need the base.phtml file inside of the directoy base.phtml.. There is one already created for you
and to save me time explaning everything just look in there..
/// Errors ///
Begin the error template
<errors>
	<error>%error<br/></error>	// Template for each error message (maybe <li>%error</li>)
	// The template for the overall error look (maybe <ol>%show_errors</ol>)
	<center>
	<div id="errors" class="error">
		<b>There were some errors.</b><br/><br/>
		%show_errors
	</div>
	</center>
	<br/><br/>
</errors>

*cough* same goes for praises:
<praises>
	<praise>%praise<br/></praise>
	<center>
	<div id="praises" class="praise">
		%show_praises
	</div>
	</center>
	<br/><br/>
</praises>

A praise is like a success message.

Custom HTML stuff:
@doc_url -- The url to get to your aeynias root (same as $_AEYNIAS['config']['doc_url'] in config.php).
@css -- Exta css per page
@text -- The pages text, defined by your static/dynamic pages, you'll see below.
@menu -- Your menu that you made inside of config.php (already parsed of course!)
%errors	-- Where to put the error messages, if there are any.
%praises -- Where to put the success/praise messages.. If there are any.
@page_title -- Where to put the page title.
#strip_html{@page_title} -- Strip the HTML from you page title, if you put any in (good for inside the <title> </title> tags)
#eval{} -- Evaluates the script in the brackets.. Use return to repalce the text with the return value.


Pages:
These are what the user visits to, and gives either static or dynamic text.
To access a page, you simply follow: mysite.com/aeynias/my_page/

To create a Static Page, just make a new document inside of the directory "/pages/static_views/"
called my_page.phtml (the .phtml is very important.).

Once you have made the .phtml document, you can easily add in a page title by putting this text:
@page_title = "My Page!!!"
This will then be added into your main template as the title.

After you define your page title, you can put any type of text you want.. Wether it be just text, or HTML.. no PHP is allowed, it wont be parsed.
But wait, theres more, since we have a simple user authenication system we can decided weither our pages required a user to be logged in!! To do so,
inside of your .phtml document simply add !logged_in and it will require the user to be logged in (and if they aren't redirect them to the login page)

If you want a dynamic page, IE to have PHP, you still need to make a document under static_views called my_page.phtml..
Once you have made that, you can either decided to have a static page title using @page_title ="My Title" or one set inside the PHP (or both)
Now, instead of putting in text and HTML inside of this phtml document, you put this text on a new line: !import_dynamic

Once you put in that text, make a new document under "/pages/dynamic_views/" called the exact same (before .phtml) but end it in .php so it would
be called my_page.php. To change the page title, use the variable $page_title, and to change the outtped page text, output the data to the variable
$page_text..

The variables $_AEYNIAS['config']['doc_root'] and $_AEYNIAS['config']['doc_url'] are defined for your use also, same as the ones defined in config.php

For more customization, the variables are added: $action and $the_id
They are from the URL as followed mysite.com/aeynias/views/my_page/$action/$the_id/

As an added bonus, you will also be able to have CSS per page, inside of the phtml document you can define css by using: @css="", see below for an example
@css = "
.text {font-size: 20px;}
body {background-color: blue;}
"
For addding in extra css for the php file, define the variable $page_css.
(please note, inorder for these to work, you have to add in the text: @css to your base.html file where you want these to go.)


Actions:
These are called like so: /action/my_action/
and that will correspond to my_action.php inside of /actions/. To populate a $_GET variablve with this, add on to the URL:
/actions/my_action/delete/ and this will populate $_GET['input'] with the text "delete".. Pretty simple.. These action PHP files
are seperate from the main framework, so no other variables are populated like normally.. I'll probably change this in an upcoming
release..

4) Mysql Database
Config your mysql database inside of config.php and run the aeynias.sql inside of a database you made... Then you can use the mysql class
(See documenation under the directory documenatation.)

5) Simple user authenication
If you made the database, you should have a users table.. To login to a user, see the login.php under /dynamic_views/
To see if a user is logged in, the variable $_AEYNIAS['authentication']['user']['logged_in'] is set. 0 for logged out, and 1 for logged in.. Simple nuff!


Sooo this is my simple intro to AEYNIAS.. enjoy :)