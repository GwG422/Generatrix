<style type='text/css'>
	a { color: #F2781D; }
	body { font-family: "Droid Sans"; }
	.topbar { background-color: #F2781D; height: 10px; }
	h1 { font-size: 80px; font-weight: bold; letter-spacing: -5px; margin-bottom: 10px; }
	h2 { font-size: 16px; }
	h3 { font-size: 16px; font-weight: bold; }
	.widget { background-color: #F1F1F1; border-top: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; }
	.widget { padding: 15px; }
	.cell { margin-bottom: 15px; font-size: 15px; }
	.cell small { color: #888888; }
	.cell .message a { color: #F2781D; font-size: 13px; text-decoration: none; }
	.botBar { background-color: #000000; padding: 40px 0px; }
	.footer { font-size: 13px; color: #FFFFFF; }
	.footer a { color: #F2781D; }
</style>

<?php
	function ghref($url) {
		return 'http://getgeneratrix.com' . $url;
	}
?>

<div class='topbar'>&nbsp;</div>

<div class='container'>

	<div class='span-24 height-3'>&nbsp;</div>

	<div class='span-24'>
		<div class='span-6'>&nbsp;</div>
		<div class='span-12 center'>
			<h1>Generatrix</h1>
			<h2>Php, Javascript, CSS and the whole shebang #!</h2>
		</div>
		<div class='span-6 last'>&nbsp;</div>
	</div>

	<div class='span-24 height-3'>&nbsp;</div>

	<div class='span-24'>
		<div class='span-18'>
			<div class='span-18 last'>
				<div class='widget'>
					<h3>Introduction to Generatrix</h3>
					<p>Generatrix is a MVC framework for PHP5 which is not inspired by existing frameworks but by our belief in Magic. Our idea was to create a framework, where everything works like magic. Some of the concepts might feel a little different if you've worked on other frameworks before, but trust me, whatever we have here, does work like magic.</p>
					<p>If you find a bug, please fork this and fix it up. We would be indebited to you for life. If you need our help, let me know. If you are using this for a college project, apply to us and we will recruit you. If you are using this for a commercial project, you can either pay us $500 a year or send us a thank you email. We value each of them equally. If you think it sucks, or just want to get friendly, or want to buy us a beer, or want to talk business, or if you just want to spam us, you can drop us an email via our <a href='http://vxtindia.com/contact'>contact page</a>.</p>
					<h4>So, what can this thing do for you?</h4>
          <p>There are a lot of features planned, but here is the list of features which are already working.</p>
          <h4>1. Database Mapping</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-database-mapping.jpg') ?>' /></p>
          <p>One major problem with databases is that everytime you change something in the DB, it's a pain to go and change the same details in the application as well. To fix this issue, we have created a file called databases.php which **automatically** creates classes for each table in your database so that you can directly start accessing them in your code. You would need the command line to do this. Once you are at the root of the app, you can do this - $ ./generatrix prepareModel  . As soon as you do this, the code will check if a file called app/model/databases.php is already present. If it is, it will give you the instructions to delete it. If not, we will create a new file. So if the name of your table is cars, you would now have a class classed 'cars' which you can use as - $cars = new cars($this->getDb);</p>
          <h4>2. Create new Controllers / Views automatically</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-new-controller.jpg') ?>' /></p>
          <p>To create a new view, you don't have to write any code. You can just type the following on the comand line - $ ./generatrix addPage test . It will automatically create two files - app/controllers/testController.php and app/view/testView.php with the basic code already present.</p>
          <h4>3. Caching Pages</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-caching-pages.jpg') ?>' /></p>
          <p>In the file app/settings/config.json, you can set cache-pages as 'true' and set the time to any number of seconds. Now, all your pages will automatically be cached in a file for that duration.</p>
          <h4>4. Caching Database Queries</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-caching-database.jpg') ?>' /></p>
          <p>In the occasional case that you need to cache database queries, you can set cache-db as 'true' in app/settings/config.json and your queries will start coming from a cache.</p>
          <h4>5. Better Debug Options</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-display.jpg') ?>' /></p>
          <p>Instead of using the regular var_dump and print_r, which don't display your objects and arrays properly, we have a new function called 'display()' which will highlight the error on the page so that you can see the problem clearly.  You can also change the call to display_system() etc to show the message in a different way. You can also stop debugging for production servers, but mentioning 'debug-values' as 'false' in app/settings/config.json . All errors are automatically caught and displayed with the display_error() call. The values output are different depending on if you are viewing them on the browser or CLI (<br> or '\n')</p>
          <h4>6. Automatic URL Rewriting</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-url-rewriting.jpg') ?>' /></p>
          <p>All URLs are automatically rewritten in Generatrix. By default a url like http://vxtindia.com/contacts/view would send the request to app/controllers/contactsController.php and call the funtion view() in that file. Once that gets executed, it will call app/views/contactsView.php and call the function view() there.</p>
          <h4>7. Custom URL Rewriting</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-custom-url-rewriting.jpg') ?>' /></p>
          <p>Since automatic URL rewriting is such a pain at times, we added a way to map your controllers and functions, so that you can choose which parameter in the url should be the controller and which should be the function that gets called automatically. You can do so by setting 'use-catch-all' to 'true' in app/settings/config.json and editing the file app/settings/mapping.php</p>
          <h4>8. Database Integration</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-database.jpg') ?>' /></p>
          <p>Unluckily we can only access one database right now. In case you need to access it, all you have to do is enter the details in app/settings/config.json . There will only be one common database object, which will only get created when you make a call to the database. If you don't, the object never initializes.</p>
          <h4>9. Database Prefixes</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-database-prefix.jpg') ?>' /></p>
          <p>You can also use database prefixes, as we have used 'cv_' in the example. If you do that, your class names won't show cv_ in the name. For example, if you are using Wordpress, and you want to access the wp_users table, you can set the 'database-prefix' value to 'wp_' and your class name would now only be 'user'.</p>
          <h4>10. Use jquery from Google's Servers</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-jquery.jpg') ?>' /></p>
          <p>If you want to use any of the javascript libraries hosted by google, you can just enter the version number for that file in app/settings/config.json and that file will be loaded automatically. You can use this to load Jquery, JqueryUI, Prototype, Scriptalicious, MooTools, Dojo, SWFObject, YUI or EXT Core.</p>
          <h4>11. EMail to your heart's content</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-mailer.jpg') ?>' /></p>
          <p>PHPMailer is included by default, so you can call it whenever you like without downloading and installing it!</p>
          <h4>12. Works with CLI as well</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-cli.jpg') ?>' /></p>
          <p>Any url that can run in the browser can also work in CLI!! I know that's way too cool. To give you an example, if you have your login page as http://vxtindia.com/user/login, you can see the exact same output on CLI by typing ./index.php user login . This works like a charm when you use **cronjobs**</p>
          <h4>13. Automatically Require Files</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-require.jpg') ?>' /></p>
          <p>All files in app/model, app/controllers, app/views and app/external are automatically included in the system. So you can call them without thinking twice.</p>
          <h4>14. Break Views in SubViews to reuse!</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-subviews.jpg') ?>' /></p>
          <p>Since only the view gets called automatically, you can break a page into subviews. You can reuse that subview in any of the views again.</p>
          <h4>15. Tell the Controller that you're using JSON</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-json.jpg') ?>' /></p>
          <p>The Controller has an option called is_html which if set to false means that you're writing a JSON or XML response. In that case, it doesn't try to include the DOCTYPE automatically. And well, this is the next point.</p>
          <h4>16. Set DOCTYPE</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-dtd.jpg') ?>' /></p>
          <p>For all your pages, you can set the doctype automatically by mentioning it the file app/settings/config.json</p>
          <h4>17. Automatically loads Blueprint-CSS</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-blueprint.jpg') ?>' /></p>
          <p>We love Blueprint so much that we load it automatically in the code for each page. So you can just start typing class='span-24' and it will work. If your page width is not 950px, you can also edit the values in the file public/style/generated.phpx . The .phpx extension is include in .htaccess so you don't have to worry about it. You can just change the variable $total_width to whatever value you like and it should work!</p>
          <h4>18. Controls for extra functionality</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-controls.jpg') ?>' /></p>
          <p>A control is piece of code which contains HTML, CSS and JS for the complete part. You can include any control by simply calling $this->loadControl(). The most popular is going to be Table Control</p>
          <h4>19. Create Tables of the fly</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-tables.jpg') ?>' /></p>
          <p>Using javascript from datatables.net, we convert tables into searchable, paginated data tables as soon as you use the control 'tables' by calling $this->loadControl('table', ...).</p>
          <h4>20. Database Interactions</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-database-calls.jpg') ?>' /></p>
          <p>All calls to the database returns Associative Arrays. When you select, you get a new row from each new row in the database. When you insert you get the insert_id as the response (again in an array). We can also log the timing required for each database call, if you're worried about your performance. Also doing simple selects, inserts, delets are easier that you can imagine!</p>
          <h4>21. Curl included by default</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-curl.jpg') ?>' /></p>
          <p>So you don't have to download a new version everytime! You can use the class Curl in the system.</p>
          <h4>22. Export Database</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-exportdb.jpg') ?>' /></p>
          <p>To export the complete database, you can just type the following on the command line - $ ./generatrix exportDb</p>
          <h4>23. Links, Redirection</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-links.jpg') ?>' /></p>
          <p>Writing relative links is always a problem, so we have made sure that you don't have to do that at all! To write the absolute url, you need to call the function href('/user/login') and the parameters are from the root of Generatrix and not of the server. Redirections works in a similar way. You only need to mention the path from the Generatrix root.</p>
          <h4>24. You love Timthumb, So do we.</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-timthumb.jpg') ?>' /></p>
          <p>A function call to image($path, $height, $width), returns the path using timthumb.php. For example, I have an image at http://vxtindia.com/test/something/vercingetorix.gif, and I need to display it in size 50x50, we can go - image('/test/something/vercingetorix.gif', 50, 50);</p>
          <h4>25. Include external Classes</h4>
          <p><img src='<?php echo ghref('/public/images/generatrix-external.jpg') ?>' /></p>
          <p>To include any external class in the code, just copy it to app/external and it will automatically be loaded. If you have a folder, you can move the folder to the external folder and write a file outside which access the values inside the new folder.</p>
				</div>
			</div>
		</div>
		<div class='span-6 last'>
			<div class='span-6 last'>
				<div class='widget'>
					<h3>Source Code</h3>
					You can download the source or fork the project on <a href='http://github.com/vercingetorix/Generatrix'>Github</a>
				</div>
				<br />
				<div class='widget'>
					<h3>Latest Commits</h3>
					<?php
			  
						$commits = $this->get('commits');
						foreach($commits as $commit) {
							$author = _g($commit, 'author');
							$url = _g($commit, 'url');
							$time = _g($commit, 'time');
							$message = _g($commit, 'message');
							?>
								<div class='cell'>
									<div class='name'><?php echo $author ?></div>
									<div class='message'><a href='http://github.com<?php echo $url ?>' target='_blank'><?php echo $message ?></a></div>
									<div class='name'><small><?php echo $time ?> ago</small></div>
								</div>
							<?php
						}
					?>
				</div>
			</div>
		</div>
	</div>

	<div class='span-24 height-2'>&nbsp;</div>

	<br clear='all' />
</div>

<div class='botBar'>
	<div class='container'>
		<div class='span-24'>
			<div class='footer'>
				Created by <a target='_blank' href='http://vxtindia.com'>Vercingetorix Technologies</a> on the <a target='_blank' href='http://getgeneratrix.com'>Generatrix Framework</a><br />
				Licenses : <a target='_blank' href='http://getgeneratrix.com/public/LICENSE.txt'>MIT License</a> ( <a target='_blank' href='http://en.wikipedia.org/wiki/MIT_License'>More Information</a> )
			</div>
		</div>
	</div>
	<br clear='all' />
</div>
