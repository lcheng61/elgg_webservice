 Purpose of this project
=========================
 This project is a test site for providing more useful web services to mobile devices using Elgg engine. The target deployment PaaS is Openshift. If you are interested in participating, drop me a line via GitHub.


Running on OpenShift
--------------------

Create an account at https://www.openshift.com

Delete an openshift app
        rhc app delete grace --confirm

Create a php application with mysql

        rhc app create -a grace -t php-5.3
	rhc app catridge add -a grace -c mysql-5.1

Make a note of the mysql, username, password, and host name as you will need to use these to complete the Piwik installation on OpenShift

Add this upstream elgg quickstart repo

	rm -rf grace
        git clone https://www.github.com/lcheng61/elgg_webservice
        cd elgg_webservice
        # add remote openshift target (copied from openshift) and name it - e.g., grace
	git remote add grace -f ssh://q543223453asdf@grace-domain.rhcloud.com/~/git/grace.git/
        # switch to the branch grace of the elgg_webservice
        git checkout grace
        # push the current elgg_webservice to the remote Openshift target namely grace forcibly.
	git push grace master --force

That's it for the code, you can now install your application at:

	http://grace-your_domain.rhcloud.com

When the installation asks you for your data directory, use the value of the
environment variable $OPENSHIFT_DATA_DIR. You can ssh to the openshift app to get the value




