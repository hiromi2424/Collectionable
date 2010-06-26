# Collectionable Plugin #

## Introduction ##
This is a utility plugin for CakePHP. This helps managing find options and virtualFields.

## Setup ##
- Define $options(such a property name can be modified by configure) for Options Behavior
- Define $virtualFieldsCollection(such a property name can be modified by configure) for VirtualFields Behavior

## Sample code ##

### OptionsBehavior ###

Here is a simple Post Model.
	class Post extends AppModel {
		var $hasMany = array('Comment');
		var $hasOne = array('Profile');

		var $acsAs = array('Collectionable.options');
		var $defaultOption = true; // or string like 'default'

		var $options =array(
			'default' => array(
				'contain' => array(
					'Comment',
					'Profile',
				),
			'limit' => 10,
			),
			'published' => array(
				'condtiions' => array('Post.published' => true),
			),
			'recent' => array(
				'order' => ('Post.updated DESC'),
			),
			'rss' => array(
				'limit' => 15,
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
			$posts = $this->Post->find('all', $this->Post->options('index', 'rss')) // multiple merging at run time;
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

## Thanks ##
- [nojimage](http://github.com/nojimage) created [base of this plugin](http://github.com/nojimage/paging)