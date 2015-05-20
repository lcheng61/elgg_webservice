// configure an authorizer to be used
window.ENV = window.ENV || {};
window.ENV['simple-auth'] = {
	crossOriginWhitelist: ['http://m.lovebeauty.me/'],
	authorizer: 'authorizer:custom'
};
window.ENV['simple-auth-cookie-store'] = {
	cookieExpirationTime: 60
};

Ember.Application.initializer({
	name: 'authentication',
	before: 'simple-auth',
	initialize: function(container, application) {
		// register the custom authenticator and authorizer so Ember Simple Auth can find them
		container.register('authenticator:custom', App.CustomAuthenticator);
		container.register('authorizer:custom', App.CustomAuthorizer);
	}
});



window.App = Ember.Application.create({
	ready: function() {
		//console.log("Ember.TEMPLATES: ", Ember.TEMPLATES);
	}
});

function getView($el) {
	return Ember.View.views[$el.closest(".ember-view").attr("id")];
}


App.urls = {
	//var server = 'http://social.routzi.com/'
	//server: 'http://www.lovebeauty.me/',
	//api_key: 'badb0afa36f54d2159e599a348886a7178b98533',


	//dev server
	//var server = 'http://dev-lovebeauty.rhcloud.com/';
	//var api_key = '902a5f73385c0310936358c4d7d58b403fe2ce93';


	//product server
	server: 'http://m.lovebeauty.me/',
	api_key: '87573c9e87172e86b8a3e99bd73f1d9e9c19086b',


	get_token: 'services/api/rest/json/?method=auth.gettoken2',
	signout: 'services/api/rest/json/?method=user.logout',


	reset_password: 'services/api/rest/json/?method=user.request_lost_password',

	user_get_all: 'services/api/rest/json/?method=user.list.signup'

}


//defint the route maps.
App.Router.map(function() {
	this.route('about', {
		path: '/about'
	});
	this.route('users', {
		path: '/users'
	});

	this.route('newstory', {
		path: '/newstory'
	});

	// login route
	this.route('login');


	// this.resource('index',{path : '/'},function(){
	//       this.resource('story', { path:'/stories/:story_id' });
	//   });

	// this.resource('newstory' , {path : 'story/new'});
});



// App.IndexController = Ember.ArrayController.extend({
//     sortProperties : ['submittedOn'],
//     sortAscending : false
// });

//===========================================================================
//	Simple Auth
//===========================================================================

// the custom authenticator that authenticates the session against the custom server
App.CustomAuthenticator = SimpleAuth.Authenticators.Base.extend({
	tokenEndpoint: App.urls.server + App.urls.get_token + "&api_key=" + App.urls.api_key,
	signout: App.urls.server + App.urls.signout + "&api_key=" + App.urls.api_key,

	restore: function(data) {
		console.log("CustomAuthenticator restore is called");
		return new Ember.RSVP.Promise(function(resolve, reject) {
			if (!Ember.isEmpty(data.token)) {
				resolve(data);
			} else {
				reject();
			}
		});
	},

	authenticate: function(credentials) {
		console.log("CustomAuthenticator authenticate is called");
		var _this = this;
		return new Ember.RSVP.Promise(function(resolve, reject) {
			Ember.$.ajax({
				url: _this.tokenEndpoint,
				type: 'POST',
				data: 'username=' + credentials.identification + '&password=' + credentials.password
			}).then(function(response) {
				console.log("response = " + JSON.stringify(response));

				//Return status 0 means successful.
				if (response.status == 0) {
					App.user = response.result;
					Ember.run(function() {
						resolve(response.result);
					});
				} else {
					Ember.run(function() {
						reject(response.message);
					});
				}
			}, function(xhr, status, error) {
				var response = JSON.parse(xhr.responseText);
				Ember.run(function() {
					reject(response.error);
				});
			});
		});
	},

	//sign out function.
	invalidate: function() {
		console.log("CustomAuthenticator invalidate is called");

		//Clear the stored data.
		App.user = {};

		var _this = this;

		//Logout from server.
		return new Ember.RSVP.Promise(function(resolve) {
			Ember.$.ajax({
				url: _this.signout + '&auth_token=' + App.user.token,
				crossDomain: true
			}).always(function() {
				resolve();
			})
		});
	},
});

// the custom authorizer that authorizes requests against the custom server
App.CustomAuthorizer = SimpleAuth.Authorizers.Base.extend({
	authorize: function(jqXHR, requestOptions) {
		if (this.get('session.isAuthenticated') && !Ember.isEmpty(this.get('session.secure.token'))) {
			jqXHR.setRequestHeader('Authorization', 'Token: ' + this.get('session.secure.token'));
		}
	}
});

// use the provided mixins in the application route and login controller
App.ApplicationRoute = Ember.Route.extend(SimpleAuth.ApplicationRouteMixin, {

	actions: {
		invalidateSession: function() {
			console.log("ApplicationRoute invalidateSession is called");
			this.get('session').invalidate();
		},

		//When sign out sucessfully, redirect to main page.
		sessionInvalidationSucceeded: function() {
			console.log("sessionInvalidationSucceeded");
			var currentRoute = this.controllerFor('application').get('index');
			this.transitionTo(currentRoute); // or whatever route you want
		},
		sessionInvalidationFailed: function(error) {
			console.log(error);
			var currentRoute = this.controllerFor('application').get('index');
			this.transitionTo(currentRoute); // or whatever route you want
		}
	}
});

//===========================================================================
//Login
//===========================================================================

// make the login route only accessible when the session is not authenticated
App.LoginRoute = Ember.Route.extend(SimpleAuth.UnauthenticatedRouteMixin, {
	// clear a potentially stale error message from previous login attempts
	setupController: function(controller, model) {
		controller.set('errorMessage', null);
	}
});
App.LoginController = Ember.Controller.extend({
	rememberMe: false,

	// change the store's cookie expiration time depending on whether "remember me" is checked or not
	rememberMeChanged: function() {
		console.log("rememberMeis changed: " + this.get("rememberMe"));
		this.get('session.store').cookieExpirationTime = this.get('rememberMe') ? 1209600 : null;
		//this.set('session.store.cookieExpirationTime', this.get('rememberMe') ? 1209600 : null);

		console.log('session.store().cookieExpirationTime = ' + this.get('session.store').cookieExpirationTime);
		//console.log('session.store.cookieExpirationTime = ' + this.get('session.store.cookieExpirationTime'));
	}.observes('rememberMe'),

	actions: {
		// display an error when authentication fails
		authenticate: function() {
			var _this = this;
			var credentials = this.getProperties('identification', 'password');

			//Clear the password filed.
			_this.set('password', null);

			//Call authenticate method.
			this.get('session').authenticate('authenticator:custom', credentials).then(null, function(message) {
				_this.set('errorMessage', message);
			});
		}
	}
});


//===========================================================================
//   DataTable
//===========================================================================


//DataTable View. This is a wrap of JQuery DataTable.
App.DataTableView = Em.View.extend({
	classNames: ['display'],
	tagName: 'table',
	columnsBinding: 'controller.columns',
	didInsertElement: function() {
		this.onValueChanged();

		//this line is required for links used to make an ember transition
		this.$('a').click(function(e) {
			e.preventDefault();
		});

	},
	onValueChanged: function() {
		var value = this.get('value');
		var data = [];
		if (value !== null && value !== undefined) {
			//console.log('Got Value: ' + JSON.stringify(value));
			data = value;
		}
		var columns = this.get('columns');
		App.usersTable = this.$().dataTable({
			"aaData": data,
			"aoColumns": columns,
			"sEmptyTable": "Loading data from server"
		});
		return;
	}.observes('value')
});



//===========================================================================
//About
//===========================================================================

App.AboutRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin, {
	model: function() {
		console.log("about route is called");

		//check if the user is login already.
		//console.log("isAuthenticated = " + this.get('session').isAuthenticated);
		//console.log("session.secure.token = " + this.get('session.secure.token'));
		//console.log("session.secure.is_seller = " + this.get('session.secure.is_seller'));
		//console.log("session.secure.is_admin = " + this.get('session.secure.is_admin'));

		return App.stories;
	}
});

//===========================================================================
//Users Route
//===========================================================================


App.UsersRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin, {
	setupController: function(controller, model) {
		controller.set('model', model);
		var columns = [{
			"sClass": "item",
			"sTitle": "User Name",
			"mData": "username"
		}, {
			"sClass": "item",
			"sTitle": "Email",
			"mData": "email"
		}, {
			"sClass": "item",
			"sTitle": "Creatation Time",
			"mData": "time"
		},{
			"sClass": "item",
			"sTitle": "Seller",
			"mData": "is_seller"
		}, {
			"sClass": "item",
			"sTitle": "Admin",
			"mData": "is_admin"
		},{
			"sClass": "item",
			"mData": null,
			"bSearchable": false,
			"bSortable": false,
			"mRender": function(data, type, full) {
				//if using an <a> tag element for ember transition, then preventDefault is required
				return '<a href=# onclick="getView($(this)).get(\'controller\').send(\'showDetails\',' + full.id + ')">Details</a>';
				/*return '<button onclick="getView($(this)).get(\'controller\').send(\'showDetails\',' + full.id + ')" >details</button>';*/
			}
		}];
		controller.set('columns', columns);
		controller.set('errorMessage', null);
	},
	model: function() {
		//console.log("Users route - model is called");
		//console.log("       signupOnly="+this.controllerFor("users").get("signupOnly"));

		return Ember.$.getJSON(App.urls.server + App.urls.user_get_all + "&api_key=" +
			App.urls.api_key + "&signup_only=" + this.controllerFor("users").get("signupOnly")).then(function(data) {
			//console.log("get user list: " + JSON.stringify(data));

			var return_data = {};

			if (data.status == 0) { //Got correct data from server.
				return_data = data.result.user;
			} else if (data.status == -20) {
				this.set('errorMessage', data.message);
				this.get('session').invalidate();
			}

			return return_data;
		});

	}
});


App.UsersController = Ember.ArrayController.extend({
	signupOnly: false,

	// change the store's cookie expiration time depending on whether "remember me" is checked or not
	signupOnlyChanged: function() {
		//Has to destroy the previous table.
		App.usersTable.fnDestroy();

		//Refresh the view to create a new table.
		this.get("target.router").refresh();

	}.observes('signupOnly'),

	actions: {
		showDetails: function(userId) {
			//this.transitionToRoute('details',userId);
		}
	}
});



//===========================================================================
//Index route, it is the default main page.
//===========================================================================

App.IndexRoute = Ember.Route.extend(SimpleAuth.AuthenticatedRouteMixin, {
	model: function() {
		//return {title:"mytitle", content:"my content"};
		return App.stories;
	}
});

// App.StoryRoute = Ember.Route.extend({
//     model : function(params){
//         //return {title: "my title " + params.story_id, content: "my contents"};
//         return App.stories[params.story_id];
//     }
// });




// App.NewstoryController = Ember.ObjectController.extend({

//  actions :{
//     save : function(){
//         var url = $('#url').val();
//         var tags = $('#tags').val();
//         var fullname = $('#fullname').val();
//         var title = $('#title').val();
//         var excerpt = $('#excerpt').val();
//         var submittedOn = new Date();

//         var story = {
//             url : url,
//             tags : tags,
//             fullname : fullname,
//             title : title,
//             content : excerpt,
//             submittedOn : submittedOn
//         };

//         //App.stories.push(story);

//         this.transitionToRoute('index');
//     }
//  }
// });