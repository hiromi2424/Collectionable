# Collectionable Plugin #

## Introduction ##
This is a utility plugin for CakePHP. This helps managing find options, virtualFields and validations.

## Setup ##
- Define $options(such a property name can be modified by configure) for Options Behavior
- Define $virtualFieldsCollection(such a property name can be modified by configure) for VirtualFields Behavior
- Define 'Validation'(such a config name can be modified by configure) section into Configure for ConfigValidationBehavior

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

## Thanks ##
- [nojimage](http://github.com/nojimage) created [base of this plugin](http://github.com/nojimage/paging)