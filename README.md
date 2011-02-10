# Collectionable Plugin #

## Introduction ##
This is a utility plugin for CakePHP. This helps managing find options, virtualFields and validations.

## Setup ##
- Define $options(such a property name can be modified by configure) for Options Behavior
- Define $virtualFieldsCollection(such a property name can be modified by configure) for VirtualFields Behavior
- Define 'Validation'(such a config name can be modified by configure) section into Configure for ConfigValidationBehavior
- Define $validate{PatternName}, like $validateAdd, same structure with $validate, for MultiValidationBehavior

## Sample code ##

### OptionsBehavior ###

Here is a simple Post Model.
	class Post extends AppModel {
		var $hasMany = array('Comment');
		var $hasOne = array('Status');

		var $acsAs = array('Collectionable.options');
		var $defaultOption = true; // or string like 'default'

		var $options =array(
			'default' => array(
				'contain' => array(
					'Comment',
					'Status',
				),
			'limit' => 10,
			),
			'published' => array(
				'condtiions' => array('Status.published' => true),
			),
			'recent' => array(
				'order' => ('Post.updated DESC'),
			),
			'rss' => array(
				'limit' => 15,
			),
			'unlimited' => array(
				'limit' => null,
			),
			'index' => array(
				// You can do sub merging
				'options' => array(
					'published',
					'recent',
				),
			),
		);
	}

You can use them by like:
	class PostsController extends AppController {
		function index() {
			$this->paginate = $this->Post->options('index');
			$this->set('posts', $this->paginate());
		}

		function rss() {
			$this->paginate = $this->Post->options('index', 'rss'); // multiple merging at run time;
			$this->set('posts', $this->paginate());
		}

		function all_in_one_page() {
			// you can use "options" attribute wihtin finding options
			$posts = $this->Post->find('all', array('options' => array('index', 'unlimited')));
			$this->set(compact('posts'));
		}
	}

To see more syntax, you would look at [the test case](http://github.com/hiromi2424/Collectionable/blob/master/tests/cases/behaviors/options.test.php) or [the code](http://github.com/hiromi2424/Collectionable/blob/master/models/behaviors/options.php).

### VirtualFieldsBehavior ###

This sample uses [MatchableBehavior](http://github.com/hiromi2424/MatchableBehavior).

	class User extends AppModel {
		var $hasMany = array('Post');
		var $actsAs = array('Collectionable.VirtualFields', 'Matchable');

		var $virtualFields = array(
			'full_name' => "CONCAT(User.first_name, ' ', User.last_name)",
		);
		var $virtualFieldsCollection = array(
			'posts_count' => 'COUNT(Post.id)',
			'amount_used' => 'SUM(Post.volume)',
		);
	}

You can use them by like:


	class UsersController extends AppController {
		function admin_index() {
			// Hey, you may feel like using OptionsBehavior :P
			$jointo = array('Post');
			$group = 'User.id';
			$virtualFields = array('posts_count', 'amount_used'); // key of collections
			$this->paginate = compact('jointo', 'group', 'virtualFields');
			$this->set('users', $this->paginate());
		}

		function profile() {
			$virtualFields = array('full_name' => false); // disable virtualFields
			$user = $this->User->find('first', compact('virtualFields'));
			$this->set(compact('user'));
		}
	}

### ConfigValidationBehavior ###


	class User extends AppModel {
		var $actsAs = array('Collectionable.ConfigValidation');

		var $validate = array(
			'nickname' => array(
				'required' => array(
					'rule' => array('notempty'),
				),
				'min' => array(
					'rule' => array('minlength'),
					'message' => 'I said more than %s!!',
				),
			),
			'email' => array(
				'required' => array(
					'rule' => array('notempty'),
				),
				'among' => array(
					'rule' => array('between'),
				),
			),
		);
	}

You can set validation parameters, messages from Configuration:


	Configure::write('Validation', array(
		'parameters' => array(
			'User' => array(
				'nickname' => array(
					'min' => 3,
				),
				'email' => array(
					'among' => array(16, 256)
				),
			),
		),
		'messages' => array(
			'default' => array(
				'required' => 'you need to enter.',
				'min' => '%s characters needed',
			),
			'User' => array(
				'email' => array(
					'required' => 'are you kidding me or misreading?',
				),
			),
		),
	));


Note that priority is "hard coded on your model" > "specifying Model and field" > "default".
But if you turn $overwrite property on, "specifying Model and field" forces to overwrite("default" does not).


### MultiValidationBehavior ###


	class User extends AppModel {

		var $actsAs = array('Collectionable.MultiValidation');

		var $validate = array(
			'password_raw' => array(
				'required' => array(
					'rule' => array('notempty'),
				),
				'minlength' => array(
					'rule' => array('minlength', 6),
				),
			),
		);

		// note that $validateprofile is invalid with 'profile'
		var $validateProfile = array(
			'nickname' => array(
				'required' => array(
					'rule' => array('notempty'),
				),
				'maxlength' => array(
					'rule' => array('maxlength', 40),
				),
			),
		);

		var $validateRequireEmail = array(
			'email' => array(
				'required' => array(
					'rule' => array('notempty'),
				),
				'email' => array(
					'rule' => array('email'),
				),
			),
		);

		var $validatePasswordConfirm = array(
			'password_confirm' => array(
				'required' => array(
					'rule' => array('notempty'),
				),
				'confirm_password' => array(
					'rule' => array('confirm_password'),
				),
			),
		);

		// You can set validation pattern on demand:
		function add($data, $validate = true, $options = array()) {
			$this->useValidationSet('requireEmail');
			$this->create();
			return $this->save($data, $validate, $options);
		}

		// You can dsiable default $validate with second argument as false:
		function edit($data, $validate = true, $options = array()) {
			$this->useValidationSet('profile', false);
			return $this->save($data, $validate, $options);
		}

		// You can specify two and more rule sets. these will be merged
		function resetEmail($data) {
			$this->useValidationSet(array('requireEmail', 'passwordConfirm'));
		}

		function confirm_password() {
			// confirm password
		}

	}


## Thanks ##
- [nojimage](http://github.com/nojimage) created [base of this plugin](http://github.com/nojimage/paging)


## License

Licensed under The MIT License.
Redistributions of files must retain the above copyright notice.


Copyright 2010 hiromi, https://github.com/hiromi2424

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
