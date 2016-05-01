UNDERSTAND HOW TO HIAWATHA MONITOR WORKS
=========================================
Read the information at https://www.hiawatha-webserver.org/howto/monitor to understand what the Hiawatha Monitor is and how it works.


CONFIGURE YOUR WEBSERVER
=========================
Use the following Hiawatha configuration for this website.

UrlToolkit {
	ToolkitID = monitor
	RequestURI isfile Return
	Match ^/(css|files|fonts|images|js)(/|$) Return
	Match ^/(favicon.ico|robots.txt)$ Return
	Match [^?]*(\?.*)? Rewrite /index.php$1
}

VirtualHost {
	Hostname = monitor.domain.com
	WebsiteRoot = /var/www/monitor/public
	StartFile = index.php
	AccessLogfile = /var/www/monitor/logfiles/access.log
	ErrorLogfile = /var/www/monitor/logfiles/error.log
	ExecuteCGI = yes
#	UseFastCGI = PHP5 # Use if you use PHP as a FastCGI daemon
	TimeForCGI = 15
	UseToolkit = monitor
}


CONFIGURE PHP
==============
The Hiawatha Monitor requires the following PHP modules:
	php5-mysql and php5-xsl

Use the following PHP settings:
	allow_url_include = Off
	cgi.fix_pathinfo = 0 (when using FastCGI PHP), 1 (otherwise)
	date.timezone = <your timezone>
	magic_quotes_gpc = Off
	register_globals = Off


CONFIGURE YOUR DATABASE
========================
Open the website in your browser and follow the instructions on your screen. In case of an error, add /setup to the URL.


CONFIGURE CRON DAEMON
======================
Use the following crontab settings to fetch the information from the webservers and to send the daily reports:

	*/5 * * * * /path/to/monitor/website/database/fetch_webserver_logs
	0   0 * * * /path/to/monitor/website/database/delete_old_logs
	59 23 * * * /path/to/monitor/website/database/send_reports


USING THE HIAWATHA MONITOR
===========================
Login with username 'admin' and password 'monitor' and start adding webservers in the Webserver Administration page. Add "MonitorServer = <IP of monitor server> to the configuration file of your Hiawatha webservers.

Don't forget to change the admin password and to check out the settings in the Settings administration page. When you're done testing, set DEBUG_MODE in settings/website.conf to 'no' and remove the setup module from settings/public_pages.conf.
