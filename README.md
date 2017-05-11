 Elgg on OpenShift
=========================
Elgg is an award-winning open source social networking engine that provides a robust framework on which to build all kinds of social environments, from a campus wide social network for your university, school or college or an internal collaborative platform for your organization through to a brand-building communications tool for your company and its clients.

More information can be found on the official elgg website at http://elgg.org

Running on OpenShift
--------------------

Create an account at https://www.openshift.com

Create a php application with mysql

	rhc app create elgg php-5.3 mysql-5.1

Make a note of the mysql, username, password, and host name as you will need to use these to complete the Piwik installation on OpenShift

Add this upstream elgg quickstart repo

	cd elgg 
	git remote add upstream -m master git://github.com/openshift-quickstart/elgg-openshift-quickstart.git
	git pull -s recursive -X theirs upstream master

Push back to your OpenShift repo

	git push

That's it for the code, you can now install your application at:

	http://elgg-lovebeauty.rhcloud.com

When the installation asks you for your data directory, use the value of the
environment variable $OPENSHIFT_DATA_DIR


Develop branching:

1. develop at github:

develop -> stage -> product
|
-> develop-mobile

1. git clone https://github.com/emagic/lovebeautyserver
2. git checkout develop
3. make change in your code, test it at your local server at least not breaking
4. git commit -a -m "message"
5. git push origin develop
6. go to 
=======



